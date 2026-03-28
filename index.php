<?php


require_once('vendor/autoload.php');

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

// --- Вхідні дані ---
$data = [
    'title' => 'Автоматизація на PHP',
    'author' => 'Yaroslav Developer',
    'content' => 'Це текст статті, створений за допомогою PHP та WebDriver.'
];

$csvFile = 'publications.csv';
$serverUrl = 'http://localhost:9515';

// --- 3.1 Відкриття сторінки ---
$capabilities = DesiredCapabilities::chrome();
$driver = RemoteWebDriver::create($serverUrl, $capabilities);

try {
    $driver->get('https://telegra.ph/');

    // Чекаємо, поки з'явиться поле заголовку (завантаження сторінки)
    $driver->wait(10)->until(
        WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::tagName('h1'))
    );

    // --- 3.2 Заповнення форми ---
    // Використовуємо JS для заповнення, оскільки Telegraph використовує ContentEditable елементи
    $driver->executeScript("
        document.querySelector('h1').innerText = arguments[0];
        document.querySelector('address').innerText = arguments[1];
        document.querySelector('.ql-editor').innerHTML = '<p>' + arguments[2] + '</p>';
    ", [$data['title'], $data['author'], $data['content']]);

    // --- 3.3 Публікація ---
    $publishButton = $driver->findElement(WebDriverBy::id('_publish_button'));
    $publishButton->click();

    // Чекаємо зміни URL (збереження статті)
    $driver->wait(10)->until(function ($driver) {
        return strpos($driver->getCurrentURL(), 'telegra.ph/') !== false &&
            $driver->getCurrentURL() !== 'https://telegra.ph/';
    });

    // --- 3.4 Отримання результату ---
    $resultUrl = $driver->getCurrentURL();
    $currentDate = date('Y-m-d H:i:s');

    // --- 3.5 Збереження даних у CSV ---
    $fileHandle = fopen($csvFile, 'a');

    // Якщо файл порожній, додаємо заголовок
    if (filesize($csvFile) === 0) {
        fputcsv($fileHandle, ['Title', 'Author', 'URL', 'Date']);
    }

    fputcsv($fileHandle, [
        $data['title'],
        $data['author'],
        $resultUrl,
        $currentDate
    ]);

    fclose($fileHandle);

    echo "Статтю опубліковано: $resultUrl\n";

} catch (Exception $e) {
    echo "Помилка: " . $e->getMessage() . "\n";
} finally {
    $driver->quit();
}



//
//require_once __DIR__ . "/vendor/autoload.php";
//
//use App\Entity\Article;
//use App\Database\DB;
//use App\Repository\ArticleRepository;
//use App\Repository\CSVRepository;
//use App\Service\TelegraphService;
//
//try {
//    $db = DB::getConnection();
//    $articleRepo = new ArticleRepository($db);
//    $csvRepo = new CSVRepository(__DIR__ . '/data/articles.csv');
//
//    $article = new Article();
//
//    $article->add(
//        "Секрети продуктивної роботи вдома у 2026 році",
//        "Олександр Гік",
//        "Віддалена робота стала стандартом. Для підтримки продуктивності важливо мати чіткий розклад, ергономічне робоче місце та робити регулярні перерви на відпочинок."
//    );
//
//    echo "🚀 Запуск браузера для першої публікації...\n";
//    $service = new TelegraphService();
//    $url = $service->publish($article);
//    $article->url = $url;
//    echo '✅ Опубліковано: ' . $url . PHP_EOL;
//
//    $articleRepo->save($article);
//
//    // Додаємо список інших статтей для масової публікації
//    $article->addArticles([
//        [
//            'title' => 'Топ 5 місць для подорожей весною',
//            'author' => 'Мандрівниця Олена',
//            'content' => 'Цього року трендовими напрямками стали затишні будиночки в Карпатах, узбережжя Португалії та квітуча Японія.',
//            'url' => '',
//            'created_at' => date('Y-m-d H:i:s'),
//        ],
//        [
//            'title' => 'Як почати займатися йогою самостійно',
//            'author' => 'Йога Майстер',
//            'content' => 'Для старту достатньо лише килимка та 15 хвилин вільного часу. Починайте з базових асан та фокусуйтеся на диханні.',
//            'url' => '',
//            'created_at' => date('Y-m-d H:i:s'),
//        ]
//    ]);
//
//    if ($article->length()) {
//        echo "🚀 Публікація додаткових статтей..." . PHP_EOL;
//        foreach ($article->getAllArticles() as $data) {
//            $newArticle = new Article();
//            $newArticle->add($data['title'], $data['author'], $data['content']);
//
//            $url = $service->publish($newArticle);
//            $newArticle->url = $url;
//            $articleRepo->save($newArticle);
//
//            echo '✅ Опубліковано: ' . $url . PHP_EOL;
//        }
//    }
//
//    echo '💾 Усі дані збережено в SQLite.' . PHP_EOL;
//
//    $allArticles = $articleRepo->findAll();
//    $csvRepo->export($allArticles);
//    echo '✅ Дані з бази успішно експортовані в CSV' . PHP_EOL;
//
//    $importedArticles = $csvRepo->import();
//    echo '📊 Перевірка CSV (останні 3 записи):' . PHP_EOL;
//    print_r(array_slice($importedArticles, -3));
//
//} catch (\Throwable $e) {
//    echo '❌ Помилка (' . get_class($e) . '): ' . $e->getMessage() . PHP_EOL;
//    echo '📂 Файл: ' . $e->getFile() . ' (рядок ' . $e->getLine() . ')' . PHP_EOL;
//}
