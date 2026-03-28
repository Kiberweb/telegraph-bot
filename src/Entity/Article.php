<?php

namespace App\Entity;

use App\Contract\ArticleContract;

class Article {
    private array $articles = [];
    public string $title;
    public string $author;
    public ?string $content = NULL;
    public ?string $url = NULL;
    public ?string $createdAt = NULL;

    public function __construct() {}

    public function add(string $title, string $author, string $content = NULL, string $url = NULL, string $createdAt = NULL): self {
        $this->title = $title;
        $this->author = $author;
        $this->content = $content;
        $this->url = $url;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');

        return $this;
    }

    public function addArticles(array $articles): array {
        array_push($this->articles, $articles);
        return $this->articles;
    }

    public function getArticleData(): array {
        $items = get_object_vars($this);
        unset($items['articles']);
        return $items;
    }
}