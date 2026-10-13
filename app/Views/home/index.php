<h1><?= e($message) ?></h1>
<p>Le catalogue vous permet de parcourir, rechercher et gérer les produits.</p>
<a class="button-link" href="/products">Voir le catalogue</a>
<?php if ($csrfToken === null): ?>
    <p>Le contrôleur prépare les données avec le modèle, puis la vue les affiche.</p>
    <a class="button-link" href="/login">Se connecter</a>
    <a class="button-link secondary-button" href="/register">Créer un utilisateur</a>
<?php else: ?>
    <form action="/logout" method="post">
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
        <button class="secondary-button" type="submit">Se déconnecter</button>
    </form>
<?php endif; ?>
