<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class CategoryModel
{
    /**
     * @return list<array{category_id: int|string, name: string}>
     */
    public function all(): array
    {
        $statement = Database::connection()->query(
            'SELECT category_id, name
            FROM "2026_M1".categories
            ORDER BY name'
        );

        return $statement->fetchAll();
    }
}
