import pandas as pd
from sqlalchemy import create_engine, text
from surprise import SVD, Dataset, Reader, accuracy
from surprise.model_selection import train_test_split
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

engine = create_engine("mysql+mysqlconnector://root:@localhost/animal_foods")


def fetch_interaction_data(engine):
    """Fetch interaction data from the database.

    Args:
        engine (sqlalchemy.engine.base.Engine): Database engine.

    Returns:
        pandas.DataFrame: DataFrame with user_id, submission_id, likes, and favorites.
    """

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
    """Fetch tags data from the database.

    Args:
        engine (sqlalchemy.engine.base.Engine): Database engine.

    Returns:
        pandas.DataFrame: DataFrame with submission_id and concatenated tags.
    """

    query = """
    SELECT submission_id, GROUP_CONCAT(tags.name SEPARATOR ', ') AS tags
    FROM submission_tags
    JOIN tags ON submission_tags.tag_id = tags.id
    GROUP BY submission_id
    """
    with engine.connect() as conn:
        return pd.read_sql(query, conn)


def create_interaction_matrix(data):
    """Create an interaction matrix from the interaction data.

    Args:
        data (pandas.DataFrame): DataFrame containing interaction data.

    Returns:
        pandas.DataFrame: Pivot table with user_id as rows, submission_id as columns.
    """

    data["interaction"] = data["likes"] + data["favorites"]
    interaction_matrix = data.pivot_table(
        index="user_id", columns="submission_id", values="interaction", fill_value=0
    )
    return interaction_matrix


def matrix_factorization_recommendations(interaction_matrix, num_recommendations=5):
    """Generate user-specific recommendations using Matrix Factorization.

    Args:
        interaction_matrix (pandas.DataFrame): DataFrame of user-item interactions.
        num_recommendations (int): Number of recommendations to generate per user.

    Returns:
        dict: Dictionary of recommendations for each user.
    """

    reader = Reader(rating_scale=(0, interaction_matrix.max().max()))
    data = Dataset.load_from_df(
        interaction_matrix.stack().reset_index(name="interaction"), reader
    )
    trainset, testset = train_test_split(data, test_size=0.25)

    algo = SVD()
    algo.fit(trainset)

    # Predictions and evaluation
    predictions = algo.test(testset)
    accuracy.rmse(predictions)

    # Generate user-specific recommendations
    recommendations = {}
    for user_id in interaction_matrix.index:
        user_predictions = [
            algo.predict(user_id, iid)
            for iid in interaction_matrix.columns
            if interaction_matrix.loc[user_id, iid] == 0
        ]
        user_recommendations = sorted(
            user_predictions, key=lambda x: x.est, reverse=True
        )[:num_recommendations]
        recommendations[user_id] = [
            (pred.iid, pred.est) for pred in user_recommendations
        ]

    return recommendations


def tfidf_tag_recommendations(tags_data, num_recomendations=5):
    """Generate tag-based recommendations using TF-IDF.

    Args:
        tags_data (pandas.DataFrame): DataFrame with submission_id and tags.
        num_recommendations (int): Number of recommendations to generate per item.

    Returns:
        dict: Dictionary of recommendations based on tag similarity.
    """

    tfidf_vectorizer = TfidfVectorizer()
    tfidf_matrix = tfidf_vectorizer.fit_transform(tags_data["tags"])

    cosine_sim = cosine_similarity(tfidf_matrix, tfidf_matrix)
    recommendations = {}
    for idx, row in tags_data.iterrows():
        submission_id = row["submission_id"]
        sim_scores = list(enumerate(cosine_sim[idx]))
        sim_scores = sorted(sim_scores, key=lambda x: x[1], reverse=True)[
            1 : num_recomendations + 1
        ]
        recommendations[submission_id] = [
            tags_data.iloc[i[0]].submission_id for i in sim_scores
        ]

    return recommendations


def combined_recommendations(user_recommendations, tag_recommendations, num_combined=5):
    """Combine user and tag-based recommendations.

    Args:
        user_recommendations (dict): Dictionary of user-specific recommendations.
        tag_recommendations (dict): Dictionary of tag-based recommendations.
        num_combined (int): Number of combined recommendations to generate per user.

    Returns:
        dict: Dictionary of combined recommendations for each user.
    """

    combined = {}
    for user_id, user_recs in user_recommendations.items():
        combined[user_id] = []
        for submission_id, score in user_recs:
            if submission_id in tag_recommendations:
                for tag_submission_id in tag_recommendations[submission_id]:
                    combined[user_id].append((tag_submission_id, score))
        combined[user_id] = list(set(combined[user_id]))[:num_combined]
    return combined


def store_recommendations(engine, recommendations):
    """Store the generated recommendations in the database.

    Args:
        engine (sqlalchemy.engine.base.Engine): Database engine.
        recommendations (dict): Dictionary of recommendations to store.
    """

    with engine.connect() as conn:
        for user_id, recs in recommendations.items():
            user_id = int(user_id)
            delete_sql = text(
                "DELETE FROM user_recommendations WHERE user_id = :user_id"
            )
            conn.execute(delete_sql, {"user_id": user_id})

            for rec, score in recs:
                try:
                    rec = int(rec)
                    sql = text(
                        "INSERT INTO user_recommendations (user_id, recommended_submission_id, score) VALUES (:user_id, :rec, :score)"
                    )
                    params = {"user_id": user_id, "rec": rec, "score": score}
                    conn.execute(sql, params)
                except Exception as e:
                    print("Error occurred:", e)
            conn.commit()


# Main execution flow
interaction_data = fetch_interaction_data(engine)
tags_data = fetch_tags_data(engine)
interaction_matrix = create_interaction_matrix(interaction_data)

user_recommendations = matrix_factorization_recommendations(interaction_matrix)
tag_recommendations = tfidf_tag_recommendations(tags_data)
combined_recs = combined_recommendations(user_recommendations, tag_recommendations)

store_recommendations(engine, combined_recs)
