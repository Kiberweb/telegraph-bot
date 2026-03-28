use sqlx::{SqlitePool, Error as SqlxError};
use crate::model::Publication;

pub struct PublicationRepository {
    pool: SqlitePool
}
impl PublicationRepository {
    pub fn new(pool: SqlitePool) -> Self {
        Self { pool }
    }

    pub async fn init(&self) -> Result<(), SqlxError> {
        sqlx::query(r#"
            CREATE TABLE IF NOT EXISTS publication (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                author TEXT NOT NULL,
                content TEXT NOT NULL,
                url TEXT NOT NULL,
                created_at DATETIME NOT NULL
            );
        "#)
            .execute(&self.pool)
            .await?;
        Ok(())
    }

    pub async fn save(&self, publication: &Publication) -> Result<(), SqlxError> {
        sqlx::query(r#"
            INSERT INTO publication (title, author, content, url, created_at)
            VALUES (?, ?, ?, ?, ?)
        "#)
            .bind(&publication.title)
            .bind(&publication.author)
            .bind(&publication.content)
            .bind(&publication.url)
            .bind(&publication.created_at)
            .execute(&self.pool)
            .await?;

        Ok(())
    }

    pub async fn fetch_all(&self) -> Result<Vec<Publication>, SqlxError> {
        sqlx::query_as::<_, Publication>("SELECT * FROM publication")
            .fetch_all(&self.pool)
            .await
    }
}
