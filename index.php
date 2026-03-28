<?php

require_once __DIR__ . "/vendor/autoload.php";

use App\Entity\Article;
use App\Database\DB;
use App\Repository\ArticleRepository;
use App\Repository\CSVRepository;

try {
    $db = DB::getConnection();
    $articleRepo = new ArticleRepository($db);
    $csvRepo = new CSVRepository(__DIR__ . '/data/articles.csv');

    // Example: Save an article to DB
    $article = new Article();
    $article->add("Title example", "Author Name", "Content of the article", "");
    $articleRepo->save($article);

    // 1. Get from base and save to CSV
    $allArticles = $articleRepo->findAll();
    $csvRepo->export($allArticles);
    echo "✅ Дані з бази успішно збережені в CSV" . PHP_EOL;

    // 2. Read from CSV (return values)
    $importedArticles = $csvRepo->import();
    echo "📊 Дані прочитані з CSV:" . PHP_EOL;
    print_r($importedArticles);

} catch (\Exception $e) {
    echo '❌ Помилка: ' . $e->getMessage() . PHP_EOL;
}
