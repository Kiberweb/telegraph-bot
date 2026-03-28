use sqlx::FromRow;
use chrono::{DateTime, Utc};
use serde::{Serialize, Deserialize};

#[derive(Serialize, Deserialize, Clone, Debug, FromRow)]
pub struct Publication {
    #[serde(skip)]
    #[allow(dead_code)]
    pub id: Option<i64>,
    pub title: String,
    pub author: String,
    pub content: String,
    #[serde(default, skip_serializing_if = "String::is_empty")]
    pub url: String,
    #[serde(rename = "date")]
    pub created_at: DateTime<Utc>,
}

impl Publication {
    pub fn new (title: &str, author: &str, content: &str) -> Self {
        Self {
            id: None,
            title: title.to_string(),
            author: author.to_string(),
            content: content.to_string(),
            url: String::new(),
            created_at: Utc::now(),
        }
    }
}
