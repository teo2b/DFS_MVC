<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Application MVC') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="/">DFS Boutique</a>
        <nav aria-label="Navigation principale">
            <a href="/products">Produits</a>
            <?php
            $cartCount = 0;
            foreach (($_SESSION['cart'] ?? []) as $quantity) {
                if (is_int($quantity) && $quantity > 0) {
                    $cartCount += $quantity;
                }
            }
            ?>
            <a href="/cart">Panier (<?= $cartCount ?>)</a>
            <?php if (isset($_SESSION['user_login']) && is_string($_SESSION['user_login'])): ?>
                <span><?= e($_SESSION['user_login']) ?></span>
                <form action="/logout" method="post">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                    <button class="nav-button" type="submit">Déconnexion</button>
                </form>
            <?php else: ?>
                <a href="/login">Connexion</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <?= $content ?>
    </main>
</body>
</html>
