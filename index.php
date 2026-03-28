<?php

require_once __DIR__ . "/vendor/autoload.php";

use App\Entity\Article;
use App\Database\DB;
use App\Repository\ArticleRepository;
use App\Repository\CSVRepository;
use App\Service\TelegraphService;

try {
    // Ініціалізація бази даних та репозиторіїв
    $db = DB::getConnection();
    $articleRepo = new ArticleRepository($db);
    $csvRepo = new CSVRepository(__DIR__ . '/data/articles.csv');

    // 1. ПІДКЛЮЧЕННЯ ДО БРАУЗЕРА
    echo "🚀 Підключення до Chromedriver (port 9515)..." . PHP_EOL;
    $service = new TelegraphService('http://localhost:9515');

    // 2. ІМПОРТ СТАТТЕЙ З CSV
    echo "📊 Читання даних з CSV-файлу..." . PHP_EOL;
    $importedData = $csvRepo->import();
    
    if (empty($importedData)) {
        echo "⚠️ CSV-файл порожній або не знайдений. Додайте статті в data/articles.csv" . PHP_EOL;
        // Для демонстрації додамо одну статтю вручну, якщо файл порожній
        $demo = [
            'title' => 'Пробний пост з CSV-структурою',
            'author' => 'Робот-Бот',
            'content' => 'Це тестовий контент для публікації.',
            'url' => '',
            'created_at' => ''
        ];
        $importedData = [$demo];
    }

    echo "📑 Знайдено статтей для публікації: " . count($importedData) . PHP_EOL;

    // 3. ПУБЛІКАЦІЯ ТА ЛОГУВАННЯ В БАЗУ
    foreach ($importedData as $row) {
        // Пропускаємо вже опубліковані (якщо є URL)
        if (!empty($row['Url']) && str_contains($row['Url'], 'telegra.ph/')) {
            echo "⏭ Пропускаємо (вже опубліковано): " . $row['Title'] . PHP_EOL;
            continue;
        }

        echo "✍️ Публікація: " . $row['Title'] . "..." . PHP_EOL;
        
        $article = new Article();
        $article->add(
            $row['Title'], 
            $row['Author'] ?: 'Анонім', 
            $row['Content']
        );

        try {
            // Публікація в Telegra.ph
            $url = $service->publish($article);
            $article->setUrl($url);
            
            // Зберігаємо в SQLite (це наш лог доданих постів)
            $articleRepo->save($article);
            
            echo "✅ Успішно! URL: $url" . PHP_EOL;
        } catch (\Throwable $e) {
            echo "❌ Помилка публікації '{$row['Title']}': " . $e->getMessage() . PHP_EOL;
        }
    }

    // 4. ОНОВЛЕННЯ CSV (Експорт актуальних даних з бази)
    echo "💾 Оновлення CSV-файлу актуальними даними з бази..." . PHP_EOL;
    $allFromDb = $articleRepo->findAll();
    $csvRepo->export($allFromDb);

    echo "🏁 Робота завершена. Всі публікації збережені в базі даних та експортовані в CSV." . PHP_EOL;

} catch (\Throwable $e) {
    echo "🛑 КРИТИЧНА ПОМИЛКА (" . get_class($e) . "): " . $e->getMessage() . PHP_EOL;
    echo "📂 Файл: " . $e->getFile() . " (рядок " . $e->getLine() . ")" . PHP_EOL;
}
