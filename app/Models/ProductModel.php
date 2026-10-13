<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class ProductModel
{
    /**
     * @return list<array<string, mixed>>
     */
    public function all(?string $search = null, ?int $categoryId = null): array
    {
        $sql = 'SELECT p.product_id, p.category_id, c.name AS category_name, p.name,
                    p.description, p.price, p.promotional_price, p.stock, p.image_path,
                    p.published_on, p.created_at, p.updated_at
                FROM "2026_M1".products p
                JOIN "2026_M1".categories c ON c.category_id = p.category_id';
        $conditions = [];
        $parameters = [];

        if ($search !== null && $search !== '') {
            $conditions[] = '(p.name ILIKE :search_name OR p.description ILIKE :search_description)';
            $parameters['search_name'] = '%' . $search . '%';
            $parameters['search_description'] = '%' . $search . '%';
        }
        if ($categoryId !== null) {
            $conditions[] = 'p.category_id = :category_id';
            $parameters['category_id'] = $categoryId;
        }
        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY p.created_at DESC, p.product_id DESC';

        $statement = Database::connection()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT p.product_id, p.category_id, c.name AS category_name, p.name,
                p.description, p.price, p.promotional_price, p.stock, p.image_path,
                p.published_on, p.created_at, p.updated_at
            FROM "2026_M1".products p
            JOIN "2026_M1".categories c ON c.category_id = p.category_id
            WHERE p.product_id = :id'
        );
        $statement->execute(['id' => $id]);
        $product = $statement->fetch();

        return $product === false ? null : $product;
    }

    public function categoryExists(int $id): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT 1 FROM "2026_M1".categories WHERE category_id = :id'
        );
        $statement->execute(['id' => $id]);

        return $statement->fetchColumn() !== false;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO "2026_M1".products
                (category_id, name, description, price, promotional_price, stock, image_path, published_on)
            VALUES
                (:category_id, :name, :description, :price, :promotional_price, :stock, :image_path, :published_on)
            RETURNING product_id'
        );
        $statement->execute($data);

        return (int) $statement->fetchColumn();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $statement = Database::connection()->prepare(
            'UPDATE "2026_M1".products SET
                category_id = :category_id,
                name = :name,
                description = :description,
                price = :price,
                promotional_price = :promotional_price,
                stock = :stock,
                image_path = :image_path,
                published_on = :published_on,
                updated_at = CURRENT_TIMESTAMP
            WHERE product_id = :id'
        );
        $statement->execute($data);
    }

    public function delete(int $id): void
    {
        $statement = Database::connection()->prepare(
            'DELETE FROM "2026_M1".products WHERE product_id = :id'
        );
        $statement->execute(['id' => $id]);
    }
}
