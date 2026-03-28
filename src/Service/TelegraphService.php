<?php

namespace App\Service;

class TelegraphService
{
    public function __construct(
        public string $title,
        public string $author,
        public string $content,
        public ?string $url = NULL,
        public ?string $createdAt = NULL,
    ) {
        $this->createdAt = $createdAt = ?? date('Y-m-d H:i:s');
    }
}