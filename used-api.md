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

# Recherche des marchés
Pas d'API disponible. Possible de scrapper un site répertoire
* https://www.jours-de-marche.fr

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
  - https://open-meteo.com/en/docs/historical-weather-api?utm_source=chatgpt.com&latitude=46.2548&longitude=5.2157&hourly=&start_date=2020-01-01&daily=temperature_2m_mean,temperature_2m_max,temperature_2m_min,daylight_duration,precipitation_sum,rain_sum,snowfall_sum,precipitation_hours,wind_direction_10m_dominant,et0_fao_evapotranspiration,sunshine_duration&timezone=auto&end_date=2025-12-03&bounding_box=-90,-180,90,180

# Recherche population
Pour récupérer le nombre d'habitants dans un périmètre donné à partir de coordonnées GPS (latitude/longitude) en France, voici une approche technique en Python, en combinant plusieurs API et bibliothèques :


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

DOC (explorateur) : https://data.ofgl.fr/explore/dataset/ofgl-base-communes-consolidee/api/?disjunctive.agregat&disjunctive.reg_name&disjunctive.dep_name&disjunctive.epci_name&disjunctive.com_name&disjunctive.tranche_population&disjunctive.tranche_revenu_imposable_par_habitant&sort=exer&refine.exer=2024&refine.agregat=Autres+imp%C3%B4ts+et+taxes


# Chiffres importants
  - Population Française : 68M
  - Nb maraichers bio déclarés : 13671 => 1/4974 habitants
  - % Foyers imposables moyen: 45.29%
  - % Impot moyen revenu salaire : 34.35
  - % Impot moyen revenu retraite/pension : 26.14
  - Nb Amap référencées sur avenir bio : 1912 => 1/35564 habitants
