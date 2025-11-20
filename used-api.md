# Recherche adresses :

* base url : https://annuaire-back.agencebio.org/adresses?
* payload :
  - lieu



# Recherche professionnels bio :

* base url : https://annuaire-back.agencebio.org/operateurs?
* payload :
  - userPage
  - typesProfessionnels = ferme
  - activites = 18 (maraichage)
  - inseecode
  - commune 
  - dist
  - lat
  - lng

# Recherche du nombre d'exploitations sur une commune (données 2010 et 2020) : 

* Base URL : https://tabular-api.data.gouv.fr/api/resources/75d07d6b-a31d-4ac0-9f1f-7faaa3ecb162/data/?
Doc tabular-api : https://www.data.gouv.fr/dataservices/api-tabulaire-data-gouv-fr-beta/
* Payload : 
  - geocode_commune__exact => code insee
  - page_size
  - page

# Recherche des amaps sur une commune
Pas d'API disponible. Possibilité de scrapper un site répertoire 
* https://www.avenir-bio.fr/annuaire_amap.php

# Recherche sur les sols

# Recherche sur les alertes vigilances eaux (restrictions)

* Base URL : https://api.vigieau.beta.gouv.fr/api/zones
* Payload :
  - lon
  - lat

# Recherche concernant le climat
Doc disponible API Meteo France:
  - https://confluence-meteofrance.atlassian.net/wiki/spaces/OpenDataMeteoFrance/pages/854261785/API+Donn+es+Climatologiques
  - https://confluence-meteofrance.atlassian.net/wiki/spaces/OpenDataMeteoFrance/pages/621510657/Donn+es+climatologiques+de+base

# Recherche population
Pour récupérer le nombre d'habitants dans un périmètre donné à partir de coordonnées GPS (latitude/longitude) en France, voici une approche technique en Python, en combinant plusieurs API et bibliothèques :

---

### **1. Identifier les communes dans un rayon donné**
Utilisez l’**API Géo (Découpage Administratif)** pour trouver les communes autour d’un point GPS. L’API ne permet pas directement de rechercher par latitude/longitude, mais vous pouvez utiliser une bibliothèque comme `geopy` pour convertir les coordonnées en code postal ou nom de commune, puis interroger l’API.

#### **Exemple de code :**
```python
import requests
from geopy.geocoders import Nominatim

# 1. Convertir latitude/longitude en nom de commune
def get_commune_name(lat, lon):
    geolocator = Nominatim(user_agent="geoapi")
    location = geolocator.reverse((lat, lon), exactly_one=True)
    if location:
        return location.raw.get('address', {}).get('village') or \
               location.raw.get('address', {}).get('town') or \
               location.raw.get('address', {}).get('city')
    return None

# 2. Récupérer les communes autour d'un point (exemple : rayon de 10 km)
def get_nearby_communes(lat, lon, radius_km=10):
    # Utiliser une API de géocodage inverse ou une bibliothèque comme `geopy` pour trouver les communes dans un rayon
    # Ici, on utilise Overpass API (OpenStreetMap) pour trouver les communes dans un rayon
    overpass_url = "https://overpass-api.de/api/interpreter"
    query = f"""
    [out:json];
    (
      way["admin_level"="8"]["boundary"="administrative"][name](around:{radius_km*1000},{lat},{lon});
      relation["admin_level"="8"]["boundary"="administrative"][name](around:{radius_km*1000},{lat},{lon});
    );
    out body;
    >;
    out skel qt;
    """
    response = requests.get(overpass_url, params={'data': query})
    data = response.json()
    communes = set()
    for element in data.get('elements', []):
        if 'tags' in element and 'name' in element['tags']:
            communes.add(element['tags']['name'])
    return list(communes)

# 3. Récupérer la population de chaque commune via l'API INSEE ou API Géo
def get_population(commune_name):
    # Utiliser l'API INSEE ou API Géo pour obtenir le code INSEE de la commune
    # Puis récupérer la population via l'API Données Locales INSEE
    # Exemple simplifié : on suppose que vous avez déjà le code INSEE
    code_insee = "75056"  # Exemple : Paris
    url = f"https://geo.api.gouv.fr/communes/{code_insee}"
    response = requests.get(url)
    data = response.json()
    return data.get('population', 0)

# Exemple d'utilisation
lat, lon = 48.8566, 2.3522  # Coordonnées de Paris
communes = get_nearby_communes(lat, lon)
total_population = 0
for commune in communes:
    population = get_population(commune)
    total_population += population
    print(f"{commune}: {population} habitants")
print(f"Population totale dans le périmètre : {total_population}")
```

---

### **2. Utiliser l’API INSEE pour les données de population**
L’**API INSEE (Données Locales)** permet de récupérer la population légale par commune. Vous devez d’abord obtenir le code INSEE de chaque commune (via l’API Géo ou un géocodage inverse), puis interroger l’API INSEE pour la population.

#### **Exemple de requête API INSEE :**
```python
def get_insee_population(code_insee):
    # Remplacez par votre jeton API INSEE
    headers = {"Authorization": "Bearer VOTRE_JETON_INSEE"}
    url = f"https://api.insee.fr/series/DL/POP/G2020/{code_insee}"
    response = requests.get(url, headers=headers)
    data = response.json()
    return data.get('population', 0)
```

---

### **3. Bibliothèques utiles**
- **`geopy`** : Pour convertir des coordonnées GPS en noms de communes.
- **`requests`** : Pour interroger les API.
- **Overpass API (OpenStreetMap)** : Pour trouver les communes dans un rayon donné.

---

### **4. Limites et améliorations possibles**
- **Précision du périmètre** : L’approche par rayon peut inclure des communes partiellement dans le périmètre. Pour une précision accrue, utilisez des outils SIG (QGIS, PostGIS) ou des bibliothèques comme `shapely` pour calculer les intersections exactes.
- **Données à jour** : Vérifiez que les données de population sont bien millésimées (INSEE publie des mises à jour annuelles).
- **Performance** : Pour de grands périmètres, optimisez les requêtes API et utilisez du cache.

---

### **Résumé des étapes :**
1. Convertir les coordonnées GPS en noms de communes.
2. Trouver toutes les communes dans un rayon donné.
3. Récupérer la population de chaque commune via l’API INSEE ou API Géo.
4. Sommer les populations pour obtenir le total dans le périmètre.

---
**Besoin d’aide pour adapter ce code à un cas précis ou pour obtenir un jeton API INSEE ?** Ou souhaitez-vous un exemple plus détaillé pour une zone spécifique ?

# Recherche tourisme

* base url : https://api.insee.fr/melodi/data/DS_TOUR_CAP?
* Exemple URL pour Tours : https://api.insee.fr/melodi/data/DS_TOUR_CAP?maxResult=200&GEO=2025-COM-37261
* payload : 
  - GEO => "annee-COM-code_insee"


# Recherche imposition

* Base url : https://tabular-api.data.gouv.fr/api/resources/1859baa1-873c-4ffb-8f92-953c2b2eae2b/data/?
* Exemple URL pour Tours : https://tabular-api.data.gouv.fr/api/resources/1859baa1-873c-4ffb-8f92-953c2b2eae2b/data/?page_size=20&page=1&DGFiP+-+D%C3%A9partement+des+%C3%A9tudes+statistiques+fiscales__contains=370&Unnamed%3A+1__contains=261
* Payload :
  - DGFiP+-+Département+des+études+statistiques+fiscales => Département (avec 0 à la fin)
  - Unnamed%3A+1__contains => Commune (code insee)
  - Unnamed: 2 => Libellé de la commune
  - Unnamed: 3 => Revenu fiscal de référence par tranche (en euros) - on peut mettre "Total" 
  - Unnamed: 4 => Nombre de foyers fiscaux
  - Unnamed: 5 => Revenu fiscal de référence des foyers fiscaux
  - Unnamed: 6 => Impôt net (total)
  - Unnamed: 7 => Nombre de foyers fiscaux imposés
  - Unnamed: 8 => Revenu fiscal de référence des foyers fiscaux imposés
  - Unnamed: 9 => Traitements et salaires / nb de foyers concernés
  - Unnamed: 10 => Traitements et salaires / Montant
  - Unnamed: 11 => Retraites et pensions / nb de foyers concernés
  - Unnamed: 12 => Retraites et pensions / Montant
* Doc api : https://tabular-api.data.gouv.fr/api/resources/1859baa1-873c-4ffb-8f92-953c2b2eae2b/swagger/

# Recherche taxe de séjour

https://data.ofgl.fr/api/records/1.0/search/?rows=40&sort=exer&refine.agregat=Autres+imp%C3%B4ts+et+taxes&refine.exer=2024&refine.com_name=Tours&start=0&fields=exer,com_name,lbudg,type_de_budget,agregat,montant,ptot,euros_par_habitant&dataset=ofgl-base-communes&timezone=Europe%2FBerlin&lang=fr