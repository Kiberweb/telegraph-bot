use thirtyfour::prelude::*;
use tokio;


#[tokio::main]
async fn main() -> WebDriverResult<()> {
    let caps = DesiredCapabilities::chrome();
    let driver = WebDriver::new("http://localhost:9515", caps).await?;

    driver.goto("https://telegra.ph/").await?;
    driver.execute(r#"
        document.querySelector('h1').innerText = arguments[0];
        document.querySelector('address').innerText = arguments[1];
        document.querySelector('.ql-editor').innerHTML = arguments[2];
    "#, vec![
        "Заголовок з Rust".into(),
        "Rust Author".into(),
        "<p>Текст статті...</p>".into()
    ]).await?;

    let button = driver.find(By::Id("_publish_button")).await?;
    button.click().await?;

    tokio::time::sleep(std::time::Duration::from_secs(2)).await;
    let url = driver.current_url().await?;
    println!("URL: {}", url);

    driver.quit().await?;
    Ok(())
}
