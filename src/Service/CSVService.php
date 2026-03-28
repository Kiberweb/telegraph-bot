<?php

namespace App\Service;

use App\Entity\Article;

class CSVService
{
    private string $path = __DIR__ . '/../../data/articles.csv';
    private bool $fileExists = false;
    private $handle;
    private array $headers = ['Title', 'Author', 'Content', 'Url', 'Date'];
    private array $data = [];

    public function __construct() {
        $this->fileExists = !file_exists($this->path);
        $this->handle = fopen($this->path, 'a');
    }

    public function add(array $data): self {
        $this->data[] = $data;
        return $this;
    }

    public function write(): void
    {
        foreach ($this->data as $row) {
            fputcsv($this->handle, $row);
        }
        fclose($this->handle);
    }

    public function getHeaders(): self {
        if ($this->fileExists) {
            fputcsv($this->handle, $this->headers);
        }
        return $this;
    }

    public function setHeaders(array $headers): self {
        $this->headers = $headers;
        return $this;
    }
}