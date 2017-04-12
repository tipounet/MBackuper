
# MBackuper, sauvegardez vos projets web en un click !

MBackuper est une solution PHP de sauvegarde de projet web. Facile à installer et à utiliser, cet outil a été conçu afin de permettre un gain de temps lors de la gestion de backups.

Il y a deux façons d’utiliser MBackuper :

- via l’interface :
  - Accès à MBackuper : http://www.domaine.com/MBackuper/index.html
  - Connexion
  - Vérification de la configuration
  - Ajout d’un commentaire (facultatif)
  - Démarrage de la sauvegarde
    - Sauvegarde de la base de données
    - Sauvegarde de(s) répertoire(s) [récursif]
  - Téléchargement de la sauvegarde
  - Déconnexion (suppression de la sauvegarde)

- via des tâches CRON :
  - http://www.domaine.com/MBackuper/cron_{TOKEN}.php
  - Démarrage de la sauvegarde
    - ...
  - Notification par email avec les liens de 
    - téléchargement de l’archive
    - suppression de l’archive

> La sauvegarde est générée au format .ZIP (elle contient un fichier « infos.html » avec un récapitulatif des informations relatives à la sauvegarde).

*Note* :
Si vous utiliser un serveur autre que apache HTTPD il faudra adapter les ré-écritures d'url faite dans le fichier .htaccess . 

## Installation

Pour installer MBackuper, suivez la procédure suivante :

  - Téléchargez MBackuper : https://github.com/benyounesmehdi/MBackuper
  - Dézippez le fichier « *MBackuper-master.zip* »
  - Changez le nom du dossier « *MBackuper-master* » en « *MBackuper* »
  - Uploadez le dossier « *MBackuper* » sur votre serveur web
  - Donnez aux dossiers « *MBackuper/archives/* » et « *MBackuper/exports/* » (et ses sous-dossiers) les droits d’accès 777
  - Configurez MBackuper en éditant le fichier « *config.inc.php* » :
    - mot de passe principal
    - données de connexion à la base de données (voir point suivant)
    - le(s) répertoire(s) à sauvegarder (chemins relatifs)
    - le(s) répertoire(s) et/ou fichier(s) à ignorer
    - options relatives aux tâches CRON
  - Accédez à MBackuper : http://www.domaine.com/MBackuper/index.html

## Configuration base(s) de données

Il est possible de sauvegarder plusieurs de données en même temps (dans des fichiers séparés).

__Limitation__ : deux schéma différent d'une même base doivent être considéré comme des bases différents l'accès s'entendant par schéma. 

__Exemple pour une base :__
```php
$_BASE_DE_DONNEES = [
    // Dupliquer le tableau si dessous pour sauvegarder plusieurs base de données
    [
        'name' => 'db1',
        'host' => 'localhost',                           // Nom d'hôte
        'port' => '3306',                                // Numéro de port
        'user' => 'root',                                // Nom d'utilisateur
        'pass' => '',                                    // Mot de passe
        'bdd' => 'test',                                 // Nom de la base de donnée
        'socket' => NULL,                                // « Socket »
        'charset' => 'utf8',                             // Codages de caractères
        'collation' => 'utf8_general_ci',                // Interclassement
        'data_directory' => '/tmp/save'                  // Emplacement de la base de donnée
    ]
];
```

exemple pour deux bases
```php
$_BASE_DE_DONNEES = [
    // Dupliquer le tableau si dessous pour sauvegarder plusieurs base de données
    [
        'name' => 'db1',
        'host' => 'localhost',                           // Nom d'hôte
        'port' => '3306',                                // Numéro de port
        'user' => 'root',                                // Nom d'utilisateur
        'pass' => '',                                    // Mot de passe
        'bdd' => 'test',                                 // Nom de la base de donnée
        'socket' => NULL,                                // « Socket »
        'charset' => 'utf8',                             // Codages de caractères
        'collation' => 'utf8_general_ci',                // Interclassement
        'data_directory' => '/tmp/save'                  // Emplacement de la base de donnée
    ], [
        'name' => 'db2',
        'host' => 'localhost',                           // Nom d'hôte
        'port' => '3306',                                // Numéro de port
        'user' => 'root',                                // Nom d'utilisateur
        'pass' => '',                                    // Mot de passe
        'bdd' => 'autre',                                 // Nom de la base de donnée
        'socket' => NULL,                                // « Socket »
        'charset' => 'utf8',                             // Codages de caractères
        'collation' => 'utf8_general_ci',                // Interclassement
        'data_directory' => '/tmp/save'                  // Emplacement de la base de donnée
    ]
];
```
## Auteur

MBackuper est un projet développé par **Mehdi Benyounes**, www.mehdi-benyounes.com

 - gestion de projets digitaux
 - design graphique
 - développement web
 - référencement
 - et bien plus...


### Mots clés :

- *sauvegarder*
- *sauvegarde*
- *backup*
- *backuper*
- *archiver*
- *bases de données*
- *BDD*
- *MySQL*
- *FTP*
- *fichiers*
- *répertoires*
- *dossiers*
- *tâches CRON*
- *automatique*
- *automatiquement*
- *site internet*
- *site web*
- *projet web*
