<?php

require_once __DIR__ . "/vendor/autoload.php";

use App\Entity\Article;
use App\Database\DB;
use App\Repository\ArticleRepository;
use App\Repository\CSVRepository;
use App\Service\TelegraphService;

try {
    $db = DB::getConnection();
    $articleRepo = new ArticleRepository($db);
    $csvRepo = new CSVRepository(__DIR__ . '/data/articles.csv');

    $article = new Article();
    $article->add("Title example One", "Author Name", "Content of the article", "");

    echo "🚀 Запуск браузера...\n";
    $service = new TelegraphService();
    $url = $service->publish($article);
    $article->url = $url;
    echo '✅ Опубліковано: ' . $url . PHP_EOL;

    $articleRepo->save($article);

    $article->addArticles([
        [
            'title' => 'Title example Two',
            'author' => 'Some author',
            'content' => 'Content of the article two ...',
            'url' => '',
            'created_at' => '',
        ],
        [
            'title' => 'Title example Three',
            'author' => 'Some author',
            'content' => 'Content of the article two ...',
            'url' => '',
            'created_at' => '',
        ],
        $article->getArticleData(),
    ]);

    if ($article->length()) {
        $articleRepo->saveMany($article->getAllArticles());
    }

    echo '💾 Дані збережено в SQLite.' . PHP_EOL;

    $allArticles = $articleRepo->findAll();
    $csvRepo->export($allArticles);
    echo '✅ Дані з бази успішно збережені в CSV' . PHP_EOL;
    $importedArticles = $csvRepo->import();
    echo '📊 Дані прочитані з CSV:' . PHP_EOL;
    print_r($importedArticles);

} catch (\Exception $e) {
    echo '❌ Помилка: ' . $e->getMessage() . PHP_EOL;
}
