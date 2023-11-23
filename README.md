# Animal Foods

Social Web Application for 95-882 Enterprise Web Development

## users table

![Screenshot of users table](./images/users.png)

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## submissions table

![Screenshot of submissions table](./images/submissions.png)

```sql
CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    animal VARCHAR(255) NOT NULL,
    food_name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    extra_info TEXT,
    media_link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```


```sql
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE submission_tags (
    submission_id INT NOT NULL,
    tag_id INT NOT NULL,
    FOREIGN KEY (submission_id) REFERENCES submissions(id),
    FOREIGN KEY (tag_id) REFERENCES tags(id)
);

```