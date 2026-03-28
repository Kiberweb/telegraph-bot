<?php

namespace App\Service;

use App\Entity\Article;
use Symfony\Component\Panther\Client;

class TelegraphService
{
    private Client $client;

    public function __construct() {
        $this->client = Client::createChromeClient();
    }

    public function publish(Article $article): string {
        $crawler = $this->client->request('GET', 'https://telegra.ph');
        $this->client->waitFor('h1');

        $this->client->executeScript("
            document.querySelector('h1').innerText = arguments[0];
            document.querySelector('address').innerText = arguments[1];
            document.querySelector('.ql-editor').innerHTML += arguments[2];
        ", [
            $article->title,
            $article->author,
            '<p>' . str_replace(PHP_EOL, '</p><p>', $article->content . '</p>'),
        ]);

        $this->client->click('#_publish_button');
        $this->client->waitFor('#_edit_button');

        return $this->client->getCurrentURL();
    }

    public function __destruct() {
        $this->client->quit();
    }
}