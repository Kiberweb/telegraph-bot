<?php

namespace App\Repository;

use App\Contract\ArticleContract;
use PDO;

class ArticleRepository
{
    public function __construct(private PDO $db) {}

    public function save(ArticleContract $article ): bool {
        $stmt = $this->db->prepare("INSERT INTO publications (title, author, content, url, date) VALUES (?,?,?,?,?)");
        return $stmt->execute($article->getArticleData());
    }
}