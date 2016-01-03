<?

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

$_PASSWORD = 'root';

/**
 * Données de connexion à la base de données
 */

$_BASE_DE_DONNEES = array(
	'host'				=> '',									// Nom d'hôte
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
 * Le(s) répertoire(s)
 */

$_REPERTOIRES = array(
	'repertoire/',												// Répertoire (ex : 'rep/' ou 'rep/rep/' ou '../rep/' ou '../rep/rep/' ...
);

/**
 * Tâches CRON
 */

$_CRON = array(
	'actif'				=> true,								// Activer ou non l'accès spécifique aux tâches CRON
	'email'				=> 'email@domaine.com',					// Réception des notifications avec les liens de téléchargement et de suppresion des archives
	'max_archives'		=> 1,									// Nombre d'archive maximum à conserver
);
