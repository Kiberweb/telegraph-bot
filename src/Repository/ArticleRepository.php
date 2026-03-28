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

    public function saveMany(array $articles): bool {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("INSERT INTO articles (title, author, content, url, created_at) VALUES (?,?,?,?,?)");
            foreach ($articles as $article) {
                $stmt->execute([
                    $article['title'],
                    $article['author'],
                    $article['content'],
                    $article['url'],
                    $article['created_at'],
                ]);
            }

            return $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function findAll(): array {
        return $this->db->query("SELECT title, author, content, url, created_at FROM articles")->fetchAll(PDO::FETCH_ASSOC);
    }
}