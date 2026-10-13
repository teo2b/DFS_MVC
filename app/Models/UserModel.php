<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class UserModel
{
    /**
     * @return array{user_id: int|string, user_login: string, user_password: string}|null
     */
    public function findByEmail(string $email): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT user_id, user_login, user_password
            FROM "2026_M1"."user"
            WHERE user_mail = :email
            LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public function existsByLoginOrEmail(string $login, string $email): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT 1
            FROM "2026_M1"."user"
            WHERE user_login = :login
                OR user_mail = :email
            LIMIT 1'
        );
        $statement->execute([
            'login' => $login,
            'email' => $email,
        ]);

        return $statement->fetchColumn() !== false;
    }

    public function create(string $login, string $passwordHash, string $email): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO "2026_M1"."user"
                (user_login, user_password, user_mail)
            VALUES
                (:login, :password, :email)
            RETURNING user_id'
        );
        $statement->execute([
            'login' => $login,
            'password' => $passwordHash,
            'email' => $email,
        ]);

        return (int) $statement->fetchColumn();
    }
}
