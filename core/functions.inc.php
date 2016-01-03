<?

/******************************************************/
/*                                                    */
/*   MBackuper                                        */
/*   github.com/benyounesmehdi/MBackuper              */
/*                                                    */
/*   Copyright Mehdi Benyounes, mehdi-benyounes.com   */
/*                                                    */
/******************************************************/

function text_plain_header_message(){
	echo " \n";
	echo '====================================================='."\n";
	echo '====================================================='."\n";
	echo "\n";
	echo '   MBackuper : github.com/benyounesmehdi/MBackuper   '."\n";
	echo '   Copyright Mehdi Benyounes : mehdi-benyounes.com   '."\n";
	echo "\n";
	echo '====================================================='."\n";
	echo '====================================================='."\n\r\n\r";
}

/**
 * Déconnexion
 */
function disconnect(){
	unset($_SESSION['CONNECT']);
	session_destroy();
}

/**
 * Suppression de fichiers
 * @param $folder
 */
function purge($folder){
	$dir = opendir($folder);
	while($fichier = readdir($dir)){
		if($fichier!='.' && $fichier!='..' &&  $fichier!='.htaccess' && $fichier!='base_de_donnees' && $fichier!='repertoires'){
			unlink($folder.$fichier);
		}
	}
	closedir($dir);
}

/**
 * Suppression de fichiers selon leur nombre
 * @param $folder
 * @param $max
 */
function clean_folder($folder,$max){
	$dir = opendir($folder);
	$fichiers = array();
	while($fichier = readdir($dir)){
		if($fichier!='.' && $fichier!='..' && $fichier!='.htaccess'){
			$fichiers[] = $fichier;
		}
	}
	closedir($dir);
	if(count($fichiers)>=$max){
		rsort($fichiers);
		for($i=$max; $i < count($fichiers); $i++){
			unlink($folder.$fichiers[$i]);
		}
	}
}

/**
 * Afficher le contenu d'un fichier (même volumineux)
 * @param $file
 * @param bool $retbytes
 * @return bool|int
 */
function fread_chunk($file,$retbytes=true){
	$return = '';
	$content = 0;
	$file = fopen($file,'rb');
	if($file==false){
		return false;
	}
	while(!feof($file)){
		$return = fread($file,(1024*1024)*1);
		echo $return;
		ob_flush();
		flush();
		if($retbytes==true){
			$content += strlen($return);
		}
	}
	$fileclose = fclose($file);
	if($retbytes==true && $fileclose==true){
		return $content;
	}
	return $fileclose;
}

/**
 * Téléchargement (zip)
 * @param $target
 * @param $filename
 */
function download($target,$filename){
	header('Content-Type: application/zip');
	header('Content-Transfer-Encoding: binary');
	header('Content-Description: File Transfer');
	header('Content-Length: '.filesize($target));
	header('Content-Disposition: attachment; filename="'.$filename.'"');
	header('Content-Type: application/force-download');
	header('Content-Type: application/octet-stream');
	header('Content-Type: application/download');
	fread_chunk($target);
	exit;
}

/**
 * Json vers array (après vérification)
 * @param $json
 * @return mixed|string
 */
function json_check_decode($json){
	json_decode($json,true);
	if(json_last_error()===JSON_ERROR_NONE){
		return json_decode($json,true);
	}else{
		return 'Une erreur inattendue s\'est produite. Erreur : json_check_decode()';
	}
}

/**
 * CURL, formater les "postfields" en URL
 * @param $array
 * @return null|string
 */
function curl_urlify_postfields($array){
	$string = NULL;
	foreach($array as $key=>$value){
		$string .= $key.'='.urlencode($value).'&';
	}
	$string = rtrim($string,'&');
	return $string;
}

/**
 * CURL, lancer
 * @param $options
 * @return array
 */
function curl_action($options){
	$curl = curl_init();
	curl_setopt_array($curl,$options);
	$curl_exec = curl_exec($curl);
	$curl_errno = curl_errno($curl);
	$curl_error = curl_error($curl) ;
	$curl_getinfo = curl_getinfo($curl);
	curl_close($curl);
	return array(
		'curl_exec'=> $curl_exec,
		'curl_errno'=> $curl_errno,
		'curl_error'=> $curl_error,
		'curl_getinfo'=> $curl_getinfo,
	);
}

/**
 * CURL, post avec options
 * @param $options
 * @param $postfields
 * @return array
 */
function curl_post($options,$postfields){
	$options[CURLOPT_POST] = count($postfields);
	$options[CURLOPT_POSTFIELDS] = curl_urlify_postfields($postfields);
	return curl_action($options);
}

/**
 * CURL, affichage d'erreur
 * @param $curl_error
 */
function echo_curl_error($curl_error){
	echo 'Erreur curl : '.$curl_error;
	exit;
}

/**
 * Formatage de filesize()
 * @param $bytes
 * @return float|string
 */
function readable_filesize($bytes){
	$bytes = floatval($bytes);
	$arBytes = array(
		array(
			'u' => 'To',
			'v' => pow(1024, 4)
		),
		array(
			'u' => 'Go',
			'v' => pow(1024, 3)
		),
		array(
			'u' => 'Mo',
			'v' => pow(1024, 2)
		),
		array(
			'u' => 'Ko',
			'v' => 1024
		),
		array(
			'u' => 'o',
			'v' => 1
		),
	);
	foreach($arBytes as $arItem){
		if($bytes >= $arItem['v']){
			$result = $bytes / $arItem['v'];
			$result = str_replace('.', ',' , strval(round($result, 2))).' '.$arItem['u'];
			break;
		}
	}
	return $result;
}

/**
 * BKP, génération de la sauvegarde par étape
 * @param $launch
 * @param $comment
 * @return string
 */
function html_bkping($launch,$comment){
	if($launch==='base_de_donnees'){
		$h2 = '1). Sauvegarde de la base de données en cours.';
		$pre = 'Veuillez patienter, en fonction de la taille de la base de donnée cela peut prendre plusieurs minutes...';
	}elseif($launch==='repertoires'){
		$h2 = '2). Sauvegarde de(s) répertoire(s) en cours.';
		$pre = 'Veuillez patienter, en fonction du nombre et du poids des fichiers cela peut prendre plusieurs minutes...';
	}elseif($launch==='generation'){
		$h2 = '3). Génération de la sauvegarde.';
		$pre = 'Votre archive est bientôt prête ! Veuillez patienter, la génération peut durer plusieurs minutes...';
	}
	ob_start();
	?>
	<h2 class="h"><? echo $h2; ?></h2>
	<p><pre><img src="data:image/gif;base64,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" width="67" height="7" alt=""/> <? echo $pre; ?></pre></p>
	<form id="launch_form" action="index.html" method="post" style="display: none;">
		<input type="hidden" name="launch" value="<? echo $launch; ?>">
		<input type="hidden" name="comment" value="<? echo $comment; ?>">
		<input type="submit" value="Lancer la sauvegarde">
	</form>
	<?
	$return = (ob_get_contents());
	ob_end_clean();
	return $return;
}

/**
 * BKP, affichage d'erreur
 * @param $bkp_errors
 * @return null|string
 */
function html_errors($bkp_errors){
	ob_start();
	?>
	<h2 class="h">La sauvegarde n'a pas pu être réalisée :</h2>
	<p>
		<?
		$return = NULL;
		foreach($bkp_errors as $error){
			$return .= $error."\n";
		}
		?>
		<pre><? echo $return; ?></pre>
		<br>
		<br>
		Avant d'essayer à nouveau, il est conseillé de vérifier et corriger le fichier &laquo; <em>config.inc.php</em> &raquo;.
		<br>
		<br>
	</p>
	<form action="index.html" method="post" style="float: left;">
		<input type="hidden" name="launch" value="init">
		<input type="submit" value="Relancer la sauvegarde">
	</form>
	<form action="index.html" method="post">
		<input type="submit" value="Retour">
	</form>
	<?
	$return = (ob_get_contents());
	ob_end_clean();
	return $return;
}
