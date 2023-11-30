import pandas as pd
from sqlalchemy import create_engine, text
from surprise import SVD, Dataset, Reader, accuracy
from surprise.model_selection import train_test_split
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

engine = create_engine('mysql+mysqlconnector://root:@localhost/animal_foods')

def fetch_interaction_data(engine):
    query = """
    SELECT votes.user_id, submissions.id as submission_id, 
           COUNT(CASE WHEN votes.vote_type = 'upvote' THEN 1 ELSE 0 END) as likes,
           COUNT(favorites.id) as favorites
    FROM submissions
    LEFT JOIN votes ON submissions.id = votes.submission_id
    LEFT JOIN favorites ON submissions.id = favorites.submission_id
    GROUP BY votes.user_id, submissions.id
    """
    with engine.connect() as conn:
        return pd.read_sql(query, conn)

def fetch_tags_data(engine):
    query = """
    SELECT submission_id, GROUP_CONCAT(tags.name SEPARATOR ', ') AS tags
    FROM submission_tags
    JOIN tags ON submission_tags.tag_id = tags.id
    GROUP BY submission_id
    """
    with engine.connect() as conn:
        return pd.read_sql(query, conn)

def create_interaction_matrix(data):
    # Combine likes and favorites into a single score
    data['interaction'] = data['likes'] + data['favorites']
    interaction_matrix = data.pivot_table(index='user_id', columns='submission_id', values='interaction', fill_value=0)
    return interaction_matrix

def preprocess_tags(data):
    # Create a DataFrame where each row represents a submission and each column represents a tag
    # Each cell is 1 if the submission has the tag, otherwise 0
    tag_matrix = data['tags'].str.get_dummies(sep=', ')
    return tag_matrix

def matrix_factorization_recommendations(interaction_matrix):
    reader = Reader(rating_scale=(0, interaction_matrix.max().max()))
    data = Dataset.load_from_df(interaction_matrix.stack().reset_index(name='interaction'), reader)
    trainset, testset = train_test_split(data, test_size=0.25)

    algo = SVD()
    algo.fit(trainset)

    # Predictions and evaluation
    predictions = algo.test(testset)
    accuracy.rmse(predictions)

    # Generate user-specific recommendations
    recommendations = {}
    for user_id in interaction_matrix.index:
        user_predictions = [algo.predict(user_id, iid) for iid in interaction_matrix.columns if interaction_matrix.loc[user_id, iid] == 0]
        user_recommendations = sorted(user_predictions, key=lambda x: x.est, reverse=True)[:5]
        recommendations[user_id] = [(pred.iid, pred.est) for pred in user_recommendations]

    return recommendations

def tfidf_tag_recommendations(tags_data, tag_matrix):
    tfidf_vectorizer = TfidfVectorizer()
    tfidf_matrix = tfidf_vectorizer.fit_transform(tags_data['tags'])

    # Compute cosine similarity for each submission
    cosine_sim = cosine_similarity(tfidf_matrix, tfidf_matrix)

    recommendations = {}
    for idx, row in tags_data.iterrows():
        submission_id = row['submission_id']
        sim_scores = list(enumerate(cosine_sim[idx]))
        sim_scores = sorted(sim_scores, key=lambda x: x[1], reverse=True)[1:6]  # Top 5 similar submissions
        recommendations[submission_id] = [tags_data.iloc[i[0]].submission_id for i in sim_scores]

    return recommendations

def combined_recommendations(user_recommendations, tag_recommendations):
    combined = {}
    for user_id, user_recs in user_recommendations.items():
        combined[user_id] = []
        for submission_id, score in user_recs:
            # Combine with tag recommendations for top items
            if submission_id in tag_recommendations:
                for tag_submission_id in tag_recommendations[submission_id]:
                    combined[user_id].append((tag_submission_id, score))
        # Limit to top 5 unique recommendations
        combined[user_id] = list(set(combined[user_id]))[:5]
    return combined

def store_recommendations(engine, recommendations):
    with engine.connect() as conn:
        for user_id, recs in recommendations.items():
            user_id = int(user_id)
            delete_sql = text('DELETE FROM user_recommendations WHERE user_id = :user_id')
            conn.execute(delete_sql, {'user_id': user_id})

            for rec, score in recs:
                try:
                    rec = int(rec)
                    sql = text('INSERT INTO user_recommendations (user_id, recommended_submission_id, score) VALUES (:user_id, :rec, :score)')
                    params = {'user_id': user_id, 'rec': rec, 'score': score}
                    conn.execute(sql, params)
                except ValueError:
                    print(f"Skipping invalid recommendation: user_id={user_id}, rec={rec}")
                except Exception as e:
                    print("Error occurred:", e)
            conn.commit()

# Main execution flow
interaction_data = fetch_interaction_data(engine)
tags_data = fetch_tags_data(engine)
interaction_matrix = create_interaction_matrix(interaction_data)
tag_matrix = preprocess_tags(tags_data)

user_recommendations = matrix_factorization_recommendations(interaction_matrix)
tag_recommendations = tfidf_tag_recommendations(tags_data, tag_matrix)
combined_recs = combined_recommendations(user_recommendations, tag_recommendations)

store_recommendations(engine, combined_recs)
