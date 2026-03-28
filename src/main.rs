mod model;
mod repository;
mod service;

use std::str::FromStr;
use sqlx::sqlite::{SqliteConnectOptions, SqlitePool};
use crate::service::TelegraphService;
use crate::repository::PublicationRepository;

#[tokio::main]
async fn main() -> Result<(), Box<dyn std::error::Error>> {
    let connection_options = SqliteConnectOptions::from_str("sqlite://database/database.db")?
        .create_if_missing(true);
    let pool = SqlitePool::connect_with(connection_options).await?;
    let repository = PublicationRepository::new(pool);
    repository.init().await?;
    let service = TelegraphService::new(repository);

    println!("--- Експорт з БД в CSV ---");
    service.db_to_csv("db_export.csv").await?;

    println!("--- Створення постів з CSV ---");
    service.csv_to_telegraph("db_export.csv").await?;

    Ok(())
}

