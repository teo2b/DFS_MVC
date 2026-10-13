<?php

declare(strict_types=1);

namespace App\Models;

final class HomeModel
{
    public function getWelcomeMessage(): string
    {
        return 'Votre application PHP MVC est prête.';
    }
}
