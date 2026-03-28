<?php

namespace App\Entity;

use App\Contract\ArticleContract;

class Article implements ArticleContract {
    private array $articles = [];
    public ?string $title = null;
    public ?string $author = null;
    public ?string $content = null;
    public ?string $url = null;
    public ?string $createdAt = null;

    public function __construct() {}

    public function add(string $title, string $author, string $content = null, string $url = null, string $createdAt = null): self {
        $this->title = $title;
        $this->author = $author;
        $this->content = $content;
        $this->url = $url;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');

        return $this;
    }

    public function length(): int {
        return count($this->articles);
    }
    public function addArticles(array $articles, $self = true): self|array {
        $this->articles = array_merge($this->articles, $articles);
        return ($self) ? $this : $this->articles;
    }

    public function getAllArticles(): array {
        return $this->articles;
    }

    public function getArticleData(): array {
        return [
            'title' => $this->title,
            'author' => $this->author,
            'content' => $this->content,
            'url' => $this->url,
            'created_at' => $this->createdAt,
        ];
    }
}