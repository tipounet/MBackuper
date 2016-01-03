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
 * Configuration de l'environnement
 */

ini_set('max_execution_time',500);
setlocale (LC_ALL,'fr_FR.utf8');
date_default_timezone_set('Europe/Paris');
mb_internal_encoding('UTF-8');
error_reporting(E_ALL & ~E_NOTICE);

session_start();

/**
 * Tableau d'erreurs
 */

$bkp_errors = array();

/**
 * Inclusion des fonctions
 */

require_once(REALPATH.'core/functions.inc.php');

/**
 * Définition des constantes
 */

$REALPATH_array = explode(DIRECTORY_SEPARATOR,REALPATH);
define('CURRENT_FOLDER',$REALPATH_array[count($REALPATH_array)-2]);
define('SSL',($_SERVER['HTTPS']=='on'?'s':NULL));

$uri = explode('/',$_SERVER['REQUEST_URI']);
$uri = array_filter($uri);
if($uri[count($uri)]!=='index.html'){
	$uri[count($uri)] = 'index.html';
}
define('URI','/'.implode('/',$uri));
define('URL','http'.SSL.'://'.$_SERVER['HTTP_HOST'].URI);

define('ARCHIVES_FOLDER',REALPATH.'archives/');
define('EXPORT_FOLDER',REALPATH.'exports/');
define('BKP_TIME',time());
define('BKP_FILE',date('Y-m-d',BKP_TIME).'_'.date('H',BKP_TIME).'h'.date('i',BKP_TIME).'_backup.zip');

/**
 * Suppression des sauvegardes de EXPORT_FOLDER
 */

if($_SESSION['CONNECT']&&$_POST['launch']!=='base_de_donnees'&&$_POST['launch']!=='repertoires'&&$_POST['launch']!=='generation'&&!isset($_POST['download'])){
	purge(EXPORT_FOLDER);
	purge(EXPORT_FOLDER.'base_de_donnees/');
	purge(EXPORT_FOLDER.'repertoires/');
}

/**
 * Inclusion du fichier de configuration
 */

if($_SERVER['HTTP_HOST']==='www.mbackuper.dev'){
	require_once(REALPATH.'config_localhost.inc.php');
}else{
	require_once(REALPATH.'config.inc.php');
}

/**
 * Actions spécifique aux tâches CRON
 */

$_TOKEN = hash('sha256',REALPATH.$_SERVER['HTTP_HOST'],false);
if(isset($_GET['token'])){
	if($_CRON['actif']===true){
		disconnect();
		if($_TOKEN===$_GET['token']){

			$curl_options = array(
				CURLOPT_FRESH_CONNECT => true,
				CURLOPT_FORBID_REUSE => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 60*5,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_URL => URL,
				CURLOPT_HEADER => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_VERBOSE => true,
			);

			$curl = curl_post($curl_options,array(
				'cron' => true,
				'launch' => 'base_de_donnees',
			));
			if($curl['curl_errno']==false){
				$curl_data['BDD'] = json_check_decode($curl['curl_exec']);
				$curl = curl_post($curl_options,array(
					'cron' => true,
					'launch' => 'repertoires',
				));
				if($curl['curl_errno']==false){
					$curl_data['REPERTOIRES'] = json_check_decode($curl['curl_exec']);
					$curl_data_json = json_encode($curl_data,JSON_FORCE_OBJECT|JSON_UNESCAPED_UNICODE);
					$curl = curl_post($curl_options,array(
						'cron' => true,
						'launch' => 'generation',
						'comment' => 'Généré via une tâche CRON.',
						'curl_data_json' => $curl_data_json,
					));
					if($curl['curl_errno']==false){
						header('Content-Type: text/plain; charset=utf-8');
						echo $curl['curl_exec'];
						exit;
					}else{
						echo_curl_error($curl['curl_error']);
					}
				}else{
					echo_curl_error($curl['curl_error']);
				}
			}else{
				echo_curl_error($curl['curl_error']);
			}

		}else{
			header('Content-Type: text/plain; charset=utf-8');
			text_plain_header_message();
			echo '# Le token est incorrect, impossible de continuer !'."\n\r";
			exit;
		}
	}else{
		header('Content-Type: text/plain; charset=utf-8');
		text_plain_header_message();
		echo '# L\'accès spécifique aux tâches CRON n\'est pas activé, impossible de continuer !'."\n\r";
		exit;
	}
}

/**
 * Traitement de $_REPERTOIRES
 */

$_REPERTOIRES = array_filter($_REPERTOIRES);
$_REPERTOIRES = array_unique($_REPERTOIRES);
$_REPERTOIRES_TMP = $_REPERTOIRES;
$_REPERTOIRES = array();
foreach($_REPERTOIRES_TMP as $folder){
	if($folder[strlen($folder)-1]==='/'){
		$folder = substr($folder,0,-1);
	}
	$_REPERTOIRES[] = $folder;
}

/**
 * Traitement des éléments à ignorer
 */

$_IGNORES_TMP = array_filter($_IGNORES);
$_IGNORES_TMP = array_unique($_IGNORES_TMP);

$_IGNORES_BASE = array(
	'/.git/',
	'',
	'/.idea/',
	'',
	'.~',
	'.DS_Store',
	'.AppleDouble',
	'.LSOverride',
	'.DocumentRevisions-V100',
	'.fseventsd',
	'.Spotlight-V100',
	'.TemporaryItems',
	'.Trashes',
	'.VolumeIcon.icns',
	'.AppleDB',
	'.AppleDesktop',
	'Network Trash Folder',
	'Temporary Items',
	'.apdisk',
	'',
	'Thumbs.db',
	'ehthumbs.db',
	'Desktop.ini',
	'$RECYCLE.BIN/',
	'.lnk',
	'.cab',
	'.msi',
	'.msm',
	'.msp',
	'',
	'dwsync.xml',
);

$_IGNORES = array_merge($_IGNORES_TMP,$_IGNORES_BASE);
$_IGNORES = array_filter($_IGNORES);
$_IGNORES = array_unique($_IGNORES);

/**
 * Actions relatives à la connexion
 */

if(isset($_POST['password'])){
	if($_POST['password']===$_PASSWORD){
		$_SESSION['CONNECT'] = true;
	}else{
		disconnect();
		header('Location: index.html?password=error');
		exit;
	}
}

/**
 * Actions spécifiques à la sauvegarde
 */

if($_POST['launch']==='init'||$_POST['launch']==='base_de_donnees'||$_POST['launch']==='repertoires'){
	$onload = ' onload="document.forms[\'launch_form\'].submit();"';
}

if($_POST['launch']==='base_de_donnees'||$_POST['launch']==='repertoires'||$_POST['launch']==='generation'){

	require_once(REALPATH.'core/mysqli_bkp.inc.php');
	require_once(REALPATH.'core/zip_bkp.inc.php');

	if($_POST['launch']==='base_de_donnees'){

		$mysqli_bkp = new mysqli_bkp(
			array(
				'mysql_data'	=> $_BASE_DE_DONNEES,
				'repertoire'	=> EXPORT_FOLDER.'base_de_donnees/',
			)
		);
		ob_end_clean();
		$_SESSION['BDD'] = $mysqli_bkp->return;
		$bkp_errors = array_merge($bkp_errors,$mysqli_bkp->bkp_errors);

		/**
		 * Actions spécifiques aux tâches CRON
		 */

		if($_CRON['actif']===true && isset($_POST['cron'])){
			header('Content-Type: application/json; charset=utf-8');
			$return = array(
				'data' => $mysqli_bkp->return,
				'errors' => $bkp_errors,
			);
			echo json_encode($return,JSON_FORCE_OBJECT|JSON_UNESCAPED_UNICODE);
			session_destroy();
			exit;
		}

	}elseif($_POST['launch']==='repertoires'){

		$zip_bkp = new zip_bkp(
			array(
				'folders_data'	=> $_REPERTOIRES,
				'repertoire'	=> EXPORT_FOLDER.'repertoires/',
				'nom_fichier'	=> NULL,
				'ignore'		=> $_IGNORES,
			)
		);
		$_SESSION['REPERTOIRES'] = $zip_bkp->return;
		$bkp_errors = array_merge($bkp_errors,$zip_bkp->bkp_errors);

		/**
		 * Actions spécifiques aux tâches CRON
		 */

		if($_CRON['actif']===true && isset($_POST['cron'])){
			header('Content-Type: application/json; charset=utf-8');
			$return = array(
				'data' => $zip_bkp->return,
				'errors' => $bkp_errors,
			);
			echo json_encode($return,JSON_FORCE_OBJECT|JSON_UNESCAPED_UNICODE);
			session_destroy();
			exit;
		}

	}elseif($_POST['launch']==='generation'){

		/**
		 * Actions spécifiques aux tâches CRON
		 */

		if($_CRON['actif']===true && isset($_POST['cron'])){
			$repertoire = ARCHIVES_FOLDER;
			$curl_data = json_check_decode($_POST['curl_data_json']);
			$_SESSION['BDD'] = $curl_data['BDD']['data'];
			$_SESSION['REPERTOIRES'] = $curl_data['REPERTOIRES']['data'];
		}else{
			$repertoire = EXPORT_FOLDER;
		}

		if($_POST['comment']==NULL){
			$comment = 'Aucun.';
		}else{
			$comment = ucfirst($_POST['comment']);
		}

		$folders_infos = NULL;
		$folders_size = 0;
		foreach($_SESSION['REPERTOIRES']['folders'] as $folder){
			$folder_size = filesize(EXPORT_FOLDER.'repertoires/'.$folder);
			$folders_size += $folder_size;
			$folders_infos .= '<strong>Chemin :</strong> repertoires/'.$folder.'<br><strong>Poids :</strong> '.(readable_filesize($folder_size)).'<br><br>';
		}

		$file_info_content = file_get_contents('template/html/infos.html');
		$search = array(
			'{$bkp_url}',
			'{$bkp_fichier}',
			'{$bkp_date}',
			'{$bkp_poids}',

			'{$bkp_comment}',

			'{$bkp_data[poids]}',
			'{$bkp_data}',

			'{$bkp_bdd[fichier]}',
			'{$bkp_bdd[poids]}',
			'{$bkp_bdd[tables]}',

		);
		$replace = array(
			URL,
			BKP_FILE,
			date('d/m/Y',BKP_TIME).' à '.date('H',BKP_TIME).'h'.date('i',BKP_TIME),
			readable_filesize($folders_size+filesize(EXPORT_FOLDER.'base_de_donnees/'.$_SESSION['BDD']['bdd'][0].'.zip')),

			$comment,

			readable_filesize($folders_size),
			$folders_infos,

			'base_de_donnees/'.$_SESSION['BDD']['bdd'][0].'.zip',
			readable_filesize(filesize(EXPORT_FOLDER.'base_de_donnees/'.$_SESSION['BDD']['bdd'][0].'.zip')),
			implode('<br>',$_SESSION['BDD']['tables'][$_BASE_DE_DONNEES['bdd']]),
		);
		$file_info_content = str_replace($search,$replace,$file_info_content);
		$file_info = fopen(EXPORT_FOLDER.'infos.html','w');
		fwrite($file_info,"\xEF\xBB\xBF".$file_info_content);
		fclose($file_info);

		$zip_generation_bkp = new zip_bkp(
			array(
				'folders_data'	=> array('exports'),
				'repertoire'	=> $repertoire,
				'nom_fichier'	=> BKP_FILE,
				'ignore'		=> array(
					'.htaccess'
				),
			)
		);

		if(count($bkp_errors)==0){
			$bkp_errors = $zip_generation_bkp->bkp_errors;
		}

		unlink(EXPORT_FOLDER.'infos.html');

		unset($_SESSION['BDD']);
		unset($_SESSION['REPERTOIRES']);

		/**
		 * Actions spécifiques aux tâches CRON
		 */

		if($_CRON['actif']===true && isset($_POST['cron'])){
			header('Content-Type: text/plain; charset=utf-8');

			$bkp_errors = array_merge($curl_data['BDD']['errors'],$curl_data['REPERTOIRES']['errors']);
			$bkp_errors = array_filter($bkp_errors);
			$bkp_errors = array_unique($bkp_errors);

			ob_start();
			text_plain_header_message();
			if(count($bkp_errors)==0){
				echo '# La sauvegarde a été réalisée avec succès !'."\n\r";
				echo 'Vous pouvez télécharger l\'archive ici : '.str_replace('index.html','archive_'.md5(BKP_FILE).'.zip',URL)."\n\r";
				echo 'Pour supprimer l\'archive rendez-vous ici (attendez que le téléchargement soit terminé) : '.str_replace('index.html','archive_'.md5(BKP_FILE).'.zip?del',URL)."\n\r";
			}else{
				echo '# La sauvegarde n\'a pas pu être réalisée :'."\n\r";
				foreach($bkp_errors as $error){
					$error[0] = strtolower($error[0]);
					echo '- '.html_entity_decode($error)."\n";
				}
				echo "\r";
			}
			$message = (ob_get_contents());
			ob_end_clean();

			echo $message;
			$message .= "\n\r\n\r";

			$headers = 'From: MBackuper <mbackuper@'.$_SERVER['HTTP_HOST'].'>'."\r\n";
			$headers .= 'Sender: mbackuper@'.$_SERVER['HTTP_HOST'].''."\r\n";
			$headers .= 'Reply-To: noreply@'.$_SERVER['HTTP_HOST'].''."\r\n";
			$headers .= 'Return-Path: '.$_CRON['email'].''."\r\n";
			$headers .= 'Date: '.date('r')."\r\n";
			$headers .= "Mime-Version: 1.0"."\r\n";
			$headers .= "Content-Type: text/plain; charset=utf-8"."\r\n";

			if(mail($_CRON['email'],"=?UTF-8?B?".base64_encode('Tâche CRON réalisée')."?=",$message,$headers)==true){
				echo 'Le lien de téléchargement à été envoyé par email à '.$_CRON['email']."\n\r";
			}

			clean_folder(ARCHIVES_FOLDER,$_CRON['max_archives']);
			session_destroy();
			exit;
		}

	}

	$bkp_errors = array_filter($bkp_errors);
	$bkp_errors = array_unique($bkp_errors);

}

/**
 * Téléchargement de la sauvegarde
 */

if(isset($_POST['download'])){
	download(EXPORT_FOLDER.$_POST['download'],$_POST['download']);
}

/**
 * Téléchargement / suppression de la sauvegarde spécifiques aux tâches CRON
 */

if($_CRON['actif']===true && isset($_GET['archive'])){

	disconnect();

	$dir = opendir(ARCHIVES_FOLDER);
	$archives_md5 = array();
	$archives_names = array();
	while($archive = readdir($dir)){
		if($archive!='.' && $archive!='..' && $archive!='.htaccess'){
			$archives_md5[] = md5($archive);
			$archives_names[] = $archive;
		}
	}
	closedir($dir);

	if(in_array($_GET['archive'],$archives_md5)==true){
		$archive_key = array_search($_GET['archive'],$archives_md5);
		$archive_name = $archives_names[$archive_key];
		if(isset($_GET['del'])){
			header('Content-Type: text/plain; charset=utf-8');
			unlink(ARCHIVES_FOLDER.$archive_name);
			text_plain_header_message();
			echo '# L\'archive a bien été supprimée !'."\n\r";
			exit;
		}else{
			download(ARCHIVES_FOLDER.$archive_name,$archive_name);
		}
	}else{
		header('Content-Type: text/plain; charset=utf-8');
		text_plain_header_message();
		echo '# Il semblerai que l\'archive n\'existe pas ou plus !'."\n\r";
		exit;
	}
}

/**
 * Déconnexion
 */

if($_POST['disconnect']==='true'){
	disconnect();
	header('Location: index.html');
	exit;
}