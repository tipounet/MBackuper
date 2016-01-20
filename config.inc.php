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
 */

$_PASSWORD = 'dc76e9f0c0006e8f919e0c515c66dbba3982f785';		/* Utilisez <?php echo sha1("MON_MOT_DE_PASSE"); ?> pour générer votre mot de passe avec le hachage "SHA1" */

/**
 * Données de connexion à la base de données
 */

$_BASE_DE_DONNEES = array(
	'host'				=> 'localhost',							// Nom d'hôte
	'port'				=> '3306',								// Numéro de port
	'user'				=> '',									// Nom d'utilisateur
	'pass'				=> '',									// Mot de passe
	'bdd'				=> '',									// Nom de la base de donnée
	'socket'			=> NULL,								// « Socket »
	'charset'			=> 'utf8',								// Codages de caractères
	'collation'			=> 'utf8_general_ci',					// Interclassement
	'data_directory'	=> NULL,								// Emplacement de la base de donnée
);

/**
 * Le(s) répertoire(s) à sauvegarder
 */

$_REPERTOIRES = array(											// Répertoire(s) (ex : '../rep/' ou '../rep/rep/' ou '../../rep/' ou '../../rep/rep/' ...
	'../repertoire/',
);

/**
 * Le(s) répertoire(s) et/ou fichier(s) à ignorer
 */

$_IGNORES = array(												// Chemin(s) (ex : '/fichier.ext' ou '/rep/fichier.ext' ou '/rep/' ou '/rep/rep/' ou '/rep/rep/fichier.ext' ...
	'',
);

/**
 * Options relatives aux tâches CRON
 */

$_CRON = array(
	'actif'				=> false,								// Activer ou non l'accès spécifique aux tâches CRON
	'email'				=> 'email@domaine.com',					// Réception des notifications avec les liens de téléchargement et de suppression de l'archives
	'max_archives'		=> 0,									// Nombre d'archive maximum à conserver (les plus anciennes seront supprimées)
);
