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

    public function add(string $title, string $author, ?string $content = null, ?string $url = null, ?string $createdAt = null): self {
        $this->title = $title;
        $this->author = $author;
        $this->content = $content;
        $this->url = $url;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');

        return $this;
    }

    public function setUrl(?string $url): self {
        $this->url = $url;
        return $this;
    }

    public function addInArray(array $data): self {
        $this->title = $data['title'] ?? $this->title;
        $this->author = $data['author'] ?? $this->author;
        $this->content = $data['content'] ?? $this->content;
        $this->url = $data['url'] ?? $this->url;
        $this->createdAt = $data['created_at'] ?? $this->createdAt;
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

    public static function slugify(string $text): string {
        $ua = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'h', 'ґ' => 'g', 'д' => 'd', 'е' => 'e', 'є' => 'ie', 'ж' => 'zh', 'з' => 'z',
            'и' => 'y', 'і' => 'i', 'ї' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p',
            'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch',
            'ь' => '', 'ю' => 'iu', 'я' => 'ia',
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'H', 'Ґ' => 'G', 'Д' => 'D', 'Е' => 'E', 'Є' => 'Ye', 'Ж' => 'Zh', 'З' => 'Z',
            'И' => 'Y', 'І' => 'I', 'Ї' => 'Yi', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P',
            'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'Kh', 'Ц' => 'Ts', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shch',
            'Ю' => 'Yu', 'Я' => 'Ya'
        ];

        $text = strtr($text, $ua);
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        
        return $text . '-' . date('m-d');
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
