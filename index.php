<?php

require_once __DIR__ . "vendor/autoload.php";

use App\Entity\Article;
use App\Database\DB;
use App\Repository\ArticleRepository;
use App\Service\TelegraphService;

try {
    $article = new Article();
    $firstArticle = $article->add(
        title: '',
        author: '',
        content: '',
    );
    $article->addArticles([
        [
            'title' => '',
            'author' => '',
            'content' => '',
        ],
        [
            'title' => '',
            'author' => '',
            'content' => '',
        ],
        $firstArticle->getArticleData(),
    ]);

} catch (\Exception $e) {
    echo '❌ Помилка: ' . $e->getMessage() . PHP_EOL;

    echo '🚀 Запуск брузера...' . PHP_EOL;
    $service = new TelegraphService();
}


$db = DB::getConnection();
$repository = new ArticleRepository($db);

//use Symfony\Component\Panther\Client;
//// add database
//$articles = [
//    ['title' => '', 'author' => '', 'content' => '', 'url' => '', 'date' => ''],
//    ['title' => '', 'author' => '', 'content' => '', 'url' => '', 'date' => ''],
//    ['title' => '', 'author' => '', 'content' => '', 'url' => '', 'date' => ''],
//    ['title' => '', 'author' => '', 'content' => '', 'url' => '', 'date' => ''],
//];
//
//$client = Client::createChromeClient();
//foreach ($articles as $article) {
//    $crawler = $client->request('GET', 'https://telegra.ph');
//    $client->waitFor('h1');
//    # article title
//    $client->executeScript(';');
//    # author
//    $client->executeScript('document.querySelector("address[data-label=\'Author\']").innerText = "' . $article['author'] . '";');
//    # content
//    $client->executeScript('div.ql-editor p[data-placeholder=\'Your story...\']").innerText = "' . $article['content'] . '";');
//    $client->click('#_publish_button');
//    $client->waitFor("#_edit_button");
//    $url = $client->getCurrentURL();
//    $date = date('Y-m-d H:i:s');

//    #CSV
//    $csvFile = 'result.csv';
//    $fileInfo = !file_exists($csvFile);
//    $handle = fopen($csvFile, 'a');
//    if ($fileInfo) {
//        fputcsv($handle, ['Title', 'Author', 'Content', '', 'Date']);
//    }
//    fputcsv($handle, [$article['title'], $article['author'], $article['content'], $url, $date]);
//    fclose($handle);
//    echo 'Опубліковано:' . $url . PHP_EOL;
//}
//$client->quit();

// composer require --dev friendsofphp/php-cs-fixer

