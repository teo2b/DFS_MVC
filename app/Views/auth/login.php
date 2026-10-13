<section class="auth-card" aria-labelledby="login-title">
    <p class="eyebrow">Espace utilisateur</p>
    <h1 id="login-title">Connexion</h1>
    <p class="intro">Connectez-vous avec votre adresse courriel et votre mot de passe.</p>

    <?php if ($error !== null): ?>
        <p class="form-error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <form action="/login" method="post">
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

        <label for="email">Adresse courriel</label>
        <input
            id="email"
            name="email"
            type="email"
            autocomplete="email"
            value="<?= e($email) ?>"
            required
            autofocus
        >

        <label for="password">Mot de passe</label>
        <input
            id="password"
            name="password"
            type="password"
            autocomplete="current-password"
            required
        >

        <button type="submit">Se connecter</button>
    </form>

    <p class="auth-footer">Pas encore de compte ? <a href="/register">Créer un utilisateur</a></p>
</section>
