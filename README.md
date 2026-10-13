# Architecture PHP MVC

Squelette MVC natif, sans framework ni dépendance externe. Le répertoire `public/` est la racine web ; toutes les requêtes applicatives passent par son contrôleur frontal `index.php`.

## Structure

```text
app/
  Controllers/   Contrôleurs : traitent les requêtes et coordonnent modèle et vue
  Core/          Composants partagés : routeur, rendu des vues et connexion PDO
  Models/        Modèles : données et logique métier
  Views/         Vues PHP et gabarit HTML
public/
  .htaccess      Réécriture Apache vers le contrôleur frontal
  index.php      Point d’entrée et déclaration des routes
compose.yaml     Services Docker pour PHP/Apache et PostgreSQL
bootstrap.php    Autoload PSR-4 minimal pour le namespace App\
database/migrations/ Migrations PostgreSQL pour les utilisateurs, produits et catégories
```

## Démarrage

### Avec Docker

Prérequis : Docker avec Docker Compose.

À la racine du projet, copier `.env.example` vers `.env`, puis lancer :

```sh
docker compose up --build
```

Ouvrir ensuite [http://localhost:8080](http://localhost:8080). PostgreSQL est disponible depuis l’application sous le nom d’hôte `db` ; la base et l’utilisateur sont initialisés à partir des variables `POSTGRES_*` du fichier `.env`. Le port `5432` est également publié sur la machine : un client PostgreSQL tel que pgAdmin peut se connecter avec l’hôte `localhost`, le port `5432`, et les identifiants définis dans `.env`. Les identifiants de l’exemple sont réservés au développement local : changez-les avant tout déploiement.

Pour ouvrir un terminal PostgreSQL dans le conteneur :

```sh
docker compose exec db psql -U mvc_user -d mvc_app
```

Remplacez `mvc_user` et `mvc_app` par les valeurs `POSTGRES_USER` et `POSTGRES_DB` de votre fichier `.env`. Les données sont conservées dans le volume Docker `postgres_data`, même après `docker compose down`. Pour supprimer aussi les données de la base, utiliser `docker compose down -v`.

La connexion PDO est accessible depuis le code avec `App\Core\Database::connection()`. Exemple dans un modèle :

```php
$statement = \App\Core\Database::connection()->query('SELECT 1');
$result = $statement->fetchColumn();
```

### Catalogue de produits

La migration `database/migrations/002_products_categories.sql` crée les tables `products` et `categories` dans le schéma PostgreSQL `"2026_M1"` et ajoute les six catégories de l’énoncé. Pour l’appliquer à une base existante :

```powershell
Get-Content -Raw database\migrations\002_products_categories.sql | docker compose exec -T db psql -v ON_ERROR_STOP=1 -U mvc_user -d mvc_app
```

Remplacez `mvc_user` et `mvc_app` par les valeurs de votre configuration si elles sont différentes. La migration peut être réexécutée sans dupliquer les catégories.

L’interface MVC du catalogue est disponible sur `/products`. La consultation, la recherche par texte (`?q=...`) et le filtre par catégorie sont publics ; la création, la modification et la suppression depuis l’interface nécessitent une session utilisateur. Les formulaires valident les champs et protègent les actions avec un jeton CSRF. Les images facultatives sont limitées à JPEG, PNG ou WebP de 5 Mo maximum et sont conservées dans le volume Docker `product_uploads`.

### Panier

Le panier MVC est disponible sur `/cart` et reste lié à la session PHP, que le visiteur soit connecté ou non. Il permet d’ajouter un produit depuis le catalogue, de modifier sa quantité, de retirer un article ou de vider le panier. Le serveur relit toujours le produit, le prix et le stock depuis PostgreSQL ; un prix ou un stock transmis par le navigateur n’est jamais utilisé.

Le total est calculé en centimes : les montants des produits et la livraison forfaitaire de 5,00 € sont traités comme des prix hors taxes, puis une TVA de 20 % est appliquée avant d’afficher le total TTC. La livraison n’est facturée que si le panier n’est pas vide. Les quantités sont vérifiées par rapport au stock actuel.

### Connexion utilisateur

La page de connexion est disponible sur `/login` (adresse courriel et mot de passe) et la création d’utilisateur sur `/register` (pseudo, adresse courriel et mot de passe). Les deux pages utilisent la table `"2026_M1"."user"`. Le formulaire d’inscription crée un hash avec `password_hash()` ; la connexion le vérifie avec `password_verify()`. La migration `database/migrations/001_user_compte_id_sequence.sql` attribue automatiquement un identifiant unique à `user_compte_id`. La page d’accueil affiche le pseudo de l’utilisateur connecté ; son formulaire de déconnexion invalide la session.

Pour arrêter les conteneurs, utiliser `Ctrl+C`, ou lancer `docker compose down` dans un autre terminal.

### Sans Docker

Prérequis : PHP 8.0 ou supérieur et Apache avec `mod_rewrite` activé. Configurer le document root du serveur web sur `public/`, puis ouvrir `/`.

La route `GET /` affiche la page d’accueil. Les routes se déclarent dans `public/index.php` et les classes `App\...` sont chargées automatiquement depuis `app/`.

Pour ajouter une page, créer un modèle si nécessaire, une action de contrôleur et sa vue, puis enregistrer la route dans le contrôleur frontal.
