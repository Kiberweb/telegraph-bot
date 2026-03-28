<?php

namespace App\Repository;

use App\Contract\ArticleContract;
use PDO;

class ArticleRepository
{
    public function __construct(private PDO $db) {}

    public function save(ArticleContract $article): bool {
        $stmt = $this->db->prepare("INSERT INTO articles (title, author, content, url, created_at) VALUES (?,?,?,?,?)");
        $data = $article->getArticleData();
        return $stmt->execute([
            $data['title'],
            $data['author'],
            $data['content'],
            $data['url'],
            $data['created_at'],
        ]);
    }

    public function findAll(): array {
        return $this->db->query("SELECT title, author, content, url, created_at FROM articles")->fetchAll(PDO::FETCH_ASSOC);
    }
}