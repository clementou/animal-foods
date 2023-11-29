import pandas as pd
from sqlalchemy import create_engine, text
from sklearn.mixture import GaussianMixture
from sklearn.preprocessing import MinMaxScaler
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

interaction_data = fetch_interaction_data(engine)
tags_data = fetch_tags_data(engine)
interaction_matrix = create_interaction_matrix(interaction_data)
tag_matrix = preprocess_tags(tags_data)

# Combine interaction and tag matrices
combined_matrix = interaction_matrix.join(tag_matrix, how='left').fillna(0)

def cluster_users(data):
    data.columns = data.columns.astype(str)
    scaler = MinMaxScaler()
    scaled_data = scaler.fit_transform(data)
    num_samples = scaled_data.shape[0]
    num_clusters = min(num_samples, 5)
    if num_clusters < 2:
        print("Insufficient data for clustering")
        return data, None

    gmm = GaussianMixture(n_components=num_clusters, random_state=42)
    gmm.fit(scaled_data)
    data['cluster'] = gmm.predict(scaled_data)
    return data, gmm

def generate_recommendations(user_data, gmm_model, interaction_matrix, num_recommendations=5):
    recommendations = {}

    if gmm_model is None:
        print("No GMM model provided.")
        return recommendations

    # Get the centroids of the clusters
    centroids = gmm_model.means_

    for user_id, cluster in user_data['cluster'].items():
        user_vector = interaction_matrix.loc[user_id].values.reshape(1, -1)
        centroid = centroids[cluster].reshape(1, -1)
        
        # Compute the similarity between the user's vector and all item vectors
        similarities = cosine_similarity(user_vector, interaction_matrix.values)[0]
        
        # Get the item indices sorted by similarity (excluding already interacted items)
        interacted_items = set(interaction_matrix.columns[interaction_matrix.loc[user_id] > 0])
        similar_items = sorted(((i, sim) for i, sim in enumerate(similarities) if interaction_matrix.columns[i] not in interacted_items), key=lambda x: x[1], reverse=True)
        
        # Store the top N recommendations and their scores
        top_n_items = similar_items[:num_recommendations]
        recommendations[user_id] = [(interaction_matrix.columns[i], score) for i, score in top_n_items]

    return recommendations

user_clusters, gmm_model = cluster_users(combined_matrix)
recommendations = generate_recommendations(user_clusters, gmm_model, interaction_matrix)

def store_recommendations(engine, recommendations):
    with engine.connect() as conn:
        for user_id, recs in recommendations.items():
            user_id = int(user_id)

            # Delete old recommendations for the user
            delete_sql = text('DELETE FROM user_recommendations WHERE user_id = :user_id')
            conn.execute(delete_sql, {'user_id': user_id})

            for rec, score in recs:
                try:
                    rec = int(rec)
                    # Insert new recommendation with the user_id, submission_id, and score
                    sql = text('INSERT INTO user_recommendations (user_id, recommended_submission_id, score) VALUES (:user_id, :rec, :score)')
                    params = {'user_id': user_id, 'rec': rec, 'score': score}
                    conn.execute(sql, params)
                except ValueError:
                    print(f"Skipping invalid recommendation: user_id={user_id}, rec={rec}")
                except Exception as e:
                    print("Error occurred:", e)
            # Commit after all inserts for the user
            conn.commit()

store_recommendations(engine, recommendations)
