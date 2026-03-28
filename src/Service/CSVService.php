<?php

namespace App\Service;

class CSVService
{
    private string $path = __DIR__ . '/../../data/articles.csv';
    private array $headers = ['Title', 'Author', 'Content', 'Url', 'Created At'];
    private array $data = [];

    public function __construct(?string $path = null) {
        if ($path) {
            $this->path = $path;
        }
    }

    public function add(array $data): self {
        $this->data[] = $data;
        return $this;
    }

    public function addRows(array $rows): self {
        foreach ($rows as $row) {
            $this->add($row);
        }
        return $this;
    }

    public function write(): void
    {
        $dir = dirname($this->path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $handle = fopen($this->path, 'w');
        if (!empty($this->headers)) {
            fputcsv($handle, $this->headers, ",", "\"", "\\");
        }
        foreach ($this->data as $row) {
            fputcsv($handle, $row, ",", "\"", "\\");
        }
        fclose($handle);
    }

    public function read(): array {
        $data = [];
        if (!file_exists($this->path)) {
            return $data;
        }
        if (($handle = fopen($this->path, "r")) !== FALSE) {
            $csvHeaders = fgetcsv($handle, 0, ",", "\"", "\\"); 
            while (($row = fgetcsv($handle, 0, ",", "\"", "\\")) !== FALSE) {
                if (count($this->headers) === count($row)) {
                    $data[] = array_combine($this->headers, $row);
                } else {
                    $data[] = $row;
                }
            }
            fclose($handle);
        }
        return $data;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function setPath(string $path): self {
        $this->path = $path;
        return $this;
    }

    public function setHeaders(array $headers): self {
        $this->headers = $headers;
        return $this;
    }
}
