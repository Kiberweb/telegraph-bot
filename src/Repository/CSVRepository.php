<?php

namespace App\Repository;

use App\Service\CSVService;

class CSVRepository
{
    private CSVService $csvService;

    public function __construct(?string $path = null) {
        $this->csvService = new CSVService($path);
    }

    public function export(array $data): void {
        $this->csvService->addRows($data)->write();
    }

    public function import(): array {
        return $this->csvService->read();
    }
}