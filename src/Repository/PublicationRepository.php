<?php

namespace App\Repository;

use PDO;
use App\Contract\ArticleContract;

class PublicationRepository
{
    public function __construct(private PDO $db) {}

    public function save(ArticleContract $article ): bool {
        $stmt = $this->db->prepare("");
    }
}