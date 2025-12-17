# 🧑‍🌾 Installation-scoring

Installation-scoring est un outil permettant de définir un score d'installation à partir des coordonnées géographiques d'un éventuel projet d'installation en maraîchage bio.

Le score est calculé selon différentes données : 

* les données de data.gouv concernant la population et l'imposition des foyers d'une commune.
* les données de openstreetmap permettant de remonter les restaurants et marchés d'une commune.
* les données de avenir-bio.fr pour remonter les amaps d'une communes.

---

## ✅ Prérequis

Le projet est développé à partir du framework Laravel 12. Avant de commencer, assurez-vous d'avoir les éléments suivants installés sur votre machine :

* PHP **>= 8.2**
* Composer
* Node.js & npm
* Git

---

## 📦 Installation du projet

1. **Cloner le dépôt**

```bash
git clone <url-du-repository>
cd <nom-du-projet>
```

2. **Installer les dépendances JS**

```bash
npm install
```

3. **Installer les dépendances PHP**

```bash
composer install
```

4. **Copier le fichier d'environnement**

```bash
cp .env.example .env
```

5. **Générer la clé de l'application**

```bash
php artisan key:generate
```

---

## ⚙️ Configuration du fichier `.env`

### 🔑 Clé API OpenRouteService

Le projet nécessite une clé API **OpenRouteService**.

1. Créez un compte gratuit sur le site :
   👉 [https://openrouteservice.org/](https://openrouteservice.org/)

2. Générez une clé API depuis votre tableau de bord.

3. Ajoutez la clé dans votre fichier `.env` :

```env
OPEN_ROUTE_SERVICE_API_KEY=your_api_key_here
```

⚠️ **Important** : sans cette clé, le projet ne fonctionnera pas correctement.

L'API OpenRouteservice permet de récupérer gratuitement les polygons isochrones (à 5, 10 et 15 minutes) autour de l'emplacement interrogé.

---

## 🗄️ Base de données

1. 🛠️ Commande Artisan spécifique

Le projet inclut une commande Artisan personnalisée permettant de récupérer les AMAPs depuis le site **avenir-bio.fr**.

### ▶️ Exécution de la commande

```bash
php artisan scrap:amap
```

### 📄 Résultat

* Cette commande génère un fichier **`amap.csv`** dans le dossier `/storage/private`
* Le fichier contient la liste des AMAPs répertoriées sur le site **avenir-bio.fr**
* Le fichier est automatiquement créé lors de l'exécution de la commande

---

## ▶️ Lancer le serveur de développement

```bash
npm run dev
```
Permet de lancer Vite et le front

```bash
php artisan serve
```

L'application sera accessible à l'adresse :
👉 [http://localhost:8000](http://localhost:8000)



---

## 📌 Notes supplémentaires

* Assurez-vous que les permissions sur les dossiers `storage` et `bootstrap/cache` sont correctes :

```bash
chmod -R 775 storage bootstrap/cache
```

* En cas de problème, consultez les logs dans :

```text
storage/logs/laravel.log
```

---

## 🧑‍💻 Auteur / Contribution

N'hésitez pas à adapter ce fichier selon les besoins spécifiques du projet (tests, seeders, front-end, CI/CD, etc.).

Bon développement ! 🚀
