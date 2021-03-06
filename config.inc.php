<?php

/******************************************************/
/*                                                    */
/*   MBackuper                                        */
/*   github.com/benyounesmehdi/MBackuper              */
/*                                                    */
/*   Copyright Mehdi Benyounes, mehdi-benyounes.com   */
/*                                                    */
/******************************************************/

/**
 * Mot de passe principal
 * pour générer votre mot de passe avec le hachage "PASSWORD_BCRYPT"
 * Utilisez <?php echo password_hash ('MON_MOT_DE_PASSE',PASSWORD_BCRYPT); ?>
 */
$_PASSWORD = '$2y$10$MoOH.a7ErhGeyd8T6DqQV.iye0fAAmmQ33WkedIdfEe1foYBy.M7C';

/**
 * Données de connexion à la base de données
 */

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

/**
 * Le(s) répertoire(s) à sauvegarder
 */

$_REPERTOIRES = array(											// Répertoire(s) (ex : '../rep/' ou '../rep/rep/' ou '../../rep/' ou '../../rep/rep/' ...
	'../repertoire/',
);

/**
 * Le(s) répertoire(s) et/ou fichier(s) à ignorer
 */

$_IGNORES = array(                                                // Chemin(s) (ex : '/fichier.ext' ou '/rep/fichier.ext' ou '/rep/' ou '/rep/rep/' ou '/rep/rep/fichier.ext' ...
    '',
);

/**
 * Options relatives aux tâches CRON
 */

$_CRON = array(
    'actif' => false,                                // Activer ou non l'accès spécifique aux tâches CRON
    'email' => 'email@domaine.com',                    // Réception des notifications avec les liens de téléchargement et de suppression de l'archives
    'max_archives' => 0,                                    // Nombre d'archive maximum à conserver (les plus anciennes seront supprimées)
);
