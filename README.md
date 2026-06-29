# 🧑‍🌾 Installation-scoring

Installation-scoring est un outil permettant de définir un score d'installation à partir des coordonnées géographiques d'un éventuel projet d'installation en maraîchage bio.

Le score est calculé à l'aide de différentes sources de données : 

* [data.gouv.fr](https://data.gouv.fr) pour ce qui concerne la population et l'imposition des foyers d'une commune.
* [agencebio.org](https://agencebio.org) et son annuaire pour recenser les maraichers bio installés sur une commune. C'est leur bilan de synthèse sur la consommation bio qui m'ont aiguillé pour la réalisation d'un scoring d'installation.
* [overpass-api](https://wiki.openstreetmap.org/wiki/Overpass_API) basée sur la communauté `openstreetmap` pour remonter les restaurants et marchés d'une commune.
* [avenir-bio.fr](https://www.avenir-bio.fr) pour remonter les amaps situées sur une commune.

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
git clone git@github.com:sanabuk/installation-scoring.git
cd installation-scoring
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

### 📧 Gestion des mails

Le lien des rapports étant envoyé par mail, en développement, je recommande l'utilisation d'un service tel que [mailtrap](https://mailtrap.io) ou autre. Là encore il vous faudra mettre à jour votre fichier `.env`

---

## 🗄️ Base de données

De base, le projet utilise SQLite mais si vous souhaitez utiliser un autre SGBD, vous pouvez modifier cela dans le fichier `.env`. Le projet utilise le système de Jobs/Queues de Laravel. Vous aurez donc besoin d'exécuter un :

```bash
php artisan migrate
```

afin de créer les tables nécessaires. J'ai pris le parti pris de ne pas supprimer la création des tables de base d'un projet Laravel de base (Users...).

De base, le projet contient 2 fichiers csv que vous retrouvez dans le dossier `/storage/app/private`.

L'un contenant les informations concernant l'imposition sur le revenu des foyers selon une commune. Ce fichier s'appelle `incoming_tax_2023.csv`. Vous pouvez télécharger ces informations à cette adresse : [https://www.data.gouv.fr/datasets/limpot-sur-le-revenu-par-collectivite-territoriale-ircom/](https://www.data.gouv.fr/datasets/limpot-sur-le-revenu-par-collectivite-territoriale-ircom/). J'ai transformé le fichier .xls de base en fichier .csv avec les informations dont j'avais besoin.

L'autre fichier `amap.csv` reprend les amaps répertoriées par le site [**avenir-bio.fr**](https://www.avenir-bio.fr). Ce fichier a été généré par une commande artisan que vous pouvez utiliser pour mettre le fichier à jour.

1. 🛠️ Commande Artisan spécifique

### ▶️ Exécution de la commande

```bash
php artisan scrap:amap
```

### 📄 Résultat

* Cette commande vous regénère un fichier à jour **`amap.csv`** dans le dossier `/storage/private` du projet. Selon votre connexion elle pourra prendre plus ou moins de temps à se terminer (< 1 minute).

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

```bash
php artisan queue:work
```
⚠️ N'oubliez pas de lancer cette commande pour que le job généré lors d'une demande de scoring soit traité 😉



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

## 🚧 Work In Progress

Des ensembles Service/Scraper/DTO sont en place mais pas encore utilisés concrètement (Taxes percues par les communes, exploitations toutes activités confondues présentes sur la commune, Vigilance eau).

Les sujets sur lesquels il faut encore travailler :

1. Technique : 
    - [/] Calcul scoring Global ~~/Demande/Concurrence~~
    - [/] Tests à mettre en place
    - [X] Envoi mail lorsque le scoring est généré
    - [ ] Templating mail
    - [ ] Mise en page pour génération de PDF (laravel-pdf de spatie est toutefois en place)

2. More data : 
    - [X] Historique climat
    - [ ] Recensement des commerces semis gros type biocoop etc
    - [ ] Peut être creuser les données sur le tourisme

Le fichier [used-api.md](./used-api.md) contient également des informations complémentaires sur les datas envisageables.

---

## 🧑‍💻 Auteur / Contribution

N'hésitez pas à adapter ce projet selon vos besoins spécifiques (tests, seeders, front-end, CI/CD, etc.).

Bon développement ! 🚀
