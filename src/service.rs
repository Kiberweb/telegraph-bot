use thirtyfour::prelude::*;
use chrono::Utc;
use serde_json::to_value;
use tokio;
use csv::{ReaderBuilder, WriterBuilder};
use crate::model::Publication;
use std::fs::OpenOptions;
use std::path::Path;
use crate::repository::PublicationRepository;

pub struct TelegraphService {
    repo: PublicationRepository,
}

impl TelegraphService {
    pub fn new(repo: PublicationRepository) -> Self {
        Self { repo }
    }

    #[allow(dead_code)]
    pub async fn publication(&self, publication: &mut Publication) -> WebDriverResult<()> {
        self.publish_one(publication).await
    }

    pub async fn publish_one(&self, publication: &mut Publication) -> WebDriverResult<()> {
        let caps = DesiredCapabilities::chrome();
        let driver = WebDriver::new("http://localhost:9515", caps).await?;

        driver.goto("https://telegra.ph/").await?;

        driver.execute(r#"
        document.querySelector('h1[data-label="Title"]').innerText = arguments[0];
        document.querySelector('address[data-label="Author"]').innerText = arguments[1];
        document.querySelector('.ql-editor').innerHTML += '<p>' + arguments[2] + '</p>';
    "#, vec![
            to_value(&publication.title)?,
            to_value(&publication.author)?,
            to_value(&publication.content)?
        ]).await?;

        let button = driver.find(By::Id("_publish_button")).await?;
        button.wait_until().clickable().await?;
        button.click().await?;

        tokio::time::sleep(std::time::Duration::from_secs(3)).await;

        let current_url = driver.current_url().await?.to_string();

        if current_url == "https://telegra.ph/"  {
            return Err(WebDriverError::RequestFailed("Кнопка натиснута, але перехід не відбувся".into()));
        }

        publication.url = current_url;
        publication.created_at = Utc::now();

        self.repo.save(&publication).await.expect("Failed to save to DB");
        let csv_path = "publication.csv";
        self.save_to_csv(csv_path, &publication)
            .map_err(|e| {
                WebDriverError::RequestFailed(format!("CSV Error: {}", e))
            })?;

        println!("Успішно опубліковано: {}", publication.url);
        tokio::time::sleep(std::time::Duration::from_millis(500)).await;
        driver.quit().await?;
        Ok(())
    }

    pub async fn db_to_csv(&self, path: &str) -> Result<(), Box<dyn std::error::Error>> {
        let publications = self.repo.fetch_all().await?;

        let file = OpenOptions::new()
            .write(true)
            .create(true)
            .truncate(true)
            .open(path)?;

        let mut wtr = WriterBuilder::new()
            .has_headers(true)
            .from_writer(file);

        for publ in publications {
            wtr.serialize(publ)?;
        }

        wtr.flush()?;
        println!("Дані з БД експортовано в {}", path);
        Ok(())
    }

    pub async fn csv_to_telegraph(&self, path: &str) -> Result<(), Box<dyn std::error::Error>> {
        let mut rdr = ReaderBuilder::new()
            .has_headers(true)
            .from_path(path)?;

        for result in rdr.deserialize() {
            let publication: Publication = result?;
            // Оскільки ми створюємо новий пост, очищаємо URL та оновлюємо дату
            let mut new_pub = Publication::new(&publication.title, &publication.author, &publication.content);

            println!("Публікація з CSV: {}", new_pub.title);
            self.publish_one(&mut new_pub).await
                .map_err(|e| format!("WebDriver Error: {}", e))?;
        }

        Ok(())
    }

    pub fn save_to_csv(&self, path: &str, publication: &Publication) -> Result<(), Box<dyn std::error::Error>> {
        let file_exists = Path::new(path).exists();
        let file = OpenOptions::new()
            .write(true)
            .create(true)
            .append(true)
            .open(path)?;

        let mut wtr = WriterBuilder::new()
            .has_headers(!file_exists)
            .from_writer(file);

        wtr.serialize(publication)?;
        wtr.flush()?;
        Ok(())
    }
}