<?

/******************************************************/
/*                                                    */
/*   MBackuper                                        */
/*   github.com/benyounesmehdi/MBackuper              */
/*                                                    */
/*   Copyright Mehdi Benyounes, mehdi-benyounes.com   */
/*                                                    */
/******************************************************/

define('REALPATH',str_replace('index.php',NULL,realpath(__FILE__)));
require_once(REALPATH.'core/app.inc.php');

?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Cache-Control" content="no-cache">
    <title>MBackuper - Mehdi Benyounes</title>
    <meta name="description" lang="fr" content="MBackuper, solution PHP de sauvegarde de projet web (fichiers et bases de données)">
    <meta name="keywords" lang="fr" content="Mehdi Benyounes,Mehdi,Benyounes">
    <meta name="author" lang="fr" content="Mehdi Benyounes - www.mehdi-benyounes.com">
    <meta name="robots" content="noindex, nofollow, noarchive, noodp">
    <meta name='copyright' content='Mehdi Benyounes - www.mehdi-benyounes.com'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <link rel="stylesheet" type="text/css" href="template/css/fonts.css">
    <link rel="stylesheet" type="text/css" href="template/css/styles.css">
    <link rel="shortcut icon" href="template/img/favicon.ico">
	<script type="text/javascript" charset="utf-8">
		<? if($_POST['launch']==='generation'){ ?>
			function warning_onleave(){
				return "ATTENTION, si vous quittez ou si vous actualisez la page la sauvegarde sera supprimée ! Si le téléchargement de la sauvegarde est en cours, vous risquez alors d'obtenir une erreur et vous devrez recommencer.";
			}
			function download_archive(){
				window.onbeforeunload = null;
				setTimeout(function(){
					document.forms['download_form'].submit();
					setTimeout(function(){
						window.onbeforeunload = warning_onleave;
					},10);
				},10);
			}
			window.onbeforeunload = warning_onleave;
		<? } ?>
		var stateObj = {foo:'bar'};
		history.pushState(stateObj, 'MBackuper - Mehdi Benyounes','index.html');
	</script>
</head>
<body<? echo $onload; ?>>
<h1><a href="https://github.com/benyounesmehdi/MBackuper" title="MBackuper" target="_self"><img src="template/img/logo.png" width="64" height="40" alt="MB"/>ackuper</a></h1>
<p><em>Solution PHP de sauvegarde de projet web</em></p>
<br>
<br>
<?
if(!isset($_SESSION['CONNECT'])){
    ?>
    <h2 class="h">Connexion</h2>
    <p>
        Veuillez saisir le mode de passe :
        <br>
        <br>
    </p>
    <form action="index.html" method="post">
        <input type="password" name="password" value="">
        <input type="submit" value="Entrer">
    </form>
    <br>
	<br>
    <? if($_GET['password']==='error'){ ?>
        <pre>Le mot de passe que vous avez indiqué est incorrect, veuillez essayer à nouveau !</pre>
    <? } ?>
    <?
}else{
    if(!isset($_POST['launch'])){
		?>
        <h2 class="h">Récapitulatif de la configuration (&laquo; <em>config.inc.php</em> &raquo;) :</h2>
        <p>
            <strong>Données de connexion à la base de données :</strong>
            <br>
            <br>
            <?
            $return = NULL;
            $return .= 'Hôte :'."\t\t\t".$_BASE_DE_DONNEES['host']."\n";
            $return .= 'Port :'."\t\t\t".$_BASE_DE_DONNEES['port']."\n";
            $return .= 'Utilisateur :'."\t\t".$_BASE_DE_DONNEES['user']."\n";
            $return .= 'Mot de passe :'."\t\t".'*********'/*$_BASE_DE_DONNEES['pass']*/."\n";
            $return .= 'Base de donnée :'."\t".$_BASE_DE_DONNEES['bdd']."\n";
            $return .= 'Socket :'."\t\t".($_BASE_DE_DONNEES['socket']==NULL ? 'aucun':$_BASE_DE_DONNEES['socket'])."\n";
            $return .= 'Codages de caractères :'."\t".$_BASE_DE_DONNEES['charset']."\n";
            $return .= 'Interclassement :'."\t".$_BASE_DE_DONNEES['collation']."\n";
            $return .= 'Emplacement de la BDD :'."\t".($_BASE_DE_DONNEES['data_directory']==NULL ? 'aucun':$_BASE_DE_DONNEES['data_directory'])."\n";
            ?>
            <pre><? echo $return; ?></pre>
            <br>
            <br>
            <strong>Le(s) répertoire(s) à sauvegarder :</strong>
            <br>
            <br>
            <?
            $return = NULL;
            foreach($_REPERTOIRES as $key=>$value){
                $return .= $value.'/'."\n";
            }
            ?>
            <pre><? echo ($return==NULL ? 'Aucun répertoire':$return); ?></pre>
            <br>
            <br>
            <strong>Le(s) répertoire(s) et/ou fichier(s) à ignorer :</strong>
            <br>
            <br>
            <?
            $return = NULL;
            foreach($_IGNORES_TMP as $key=>$value){
                $return .= $value."\n";
            }
            ?>
            <pre><? echo ($return==NULL ? 'Aucun répertoire ou fichier':$return); ?></pre>
            <br>
            <br>
            <strong>Tâches CRON :</strong>
            <br>
            <br>
            <?
            $return = NULL;
            $return .= 'Accès :'."\t\t\t".($_CRON['actif']==true ? 'activé':'désactivé')."\n";
            $return .= 'Archive(s) max. :'."\t".$_CRON['max_archives']."\n";
            $return .= 'URL [confidentiel] :'."\t".str_replace('index.html','cron_'.$_TOKEN.'.php',URL)."\n";
            $return .= 'Email :'."\t\t\t".$_CRON['email']."\n";
            ?>
            <pre><? echo $return; ?></pre>
            <br>
            <br>
            Si la configuration est correcte saisissez un commentaire et cliquez sur &laquo; Lancer la sauvegarde &raquo;.
            <br>
            Dans le cas contraire, veuillez modifier le fichier &laquo; <em>config.inc.php</em> &raquo;.
            <br>
            <br>
        </p>
        <form action="index.html" method="post">
            <input type="hidden" name="launch" value="init">
			<input type="text" name="comment" value="" placeholder="Commentaire (facultatif)" class="comment">
            <input type="submit" value="Lancer la sauvegarde" style="float: left;">
        </form>
        <form action="index.html" method="post" style="float: left;">
            <input type="hidden" name="disconnect" value="true">
            <input type="submit" value="Quitter">
        </form>
        <br>
		<br>
		<br>
        <?
	}elseif($_POST['launch']==='init'){
        echo html_bkping('base_de_donnees',$_POST['comment']);
    }elseif($_POST['launch']==='base_de_donnees'){
        if(count($bkp_errors)==0){
            echo html_bkping('repertoires',$_POST['comment']);
        }else{
            echo html_errors($bkp_errors);
        }
    }elseif($_POST['launch']==='repertoires'){
        if(count($bkp_errors)==0){
            echo html_bkping('generation',$_POST['comment']);;
        }else{
            echo html_errors($bkp_errors);
        }
    }elseif($_POST['launch']==='generation'){
        if(count($bkp_errors)==0){
            ?>
                <h2 class="h">La sauvegarde a été réalisée avec succès !</h2>
                <p>
                    Vous pouvez maintenant télécharger la sauvegarde en cliquant sur le bouton &laquo; Télécharger &raquo; :
                    <br>
                    <br>
                </p>
                <form id="download_form" action="index.html" method="post" onsubmit="return false">
                    <input type="hidden" name="download" value="<? echo BKP_FILE ?>">
                    <input type="submit" value="Télécharger" style="float: left;" onclick="download_archive();">
                </form>
                <form action="index.html" method="post" style="float: left;">
                    <input type="submit" value="Supprimer">
                </form>
                <form action="index.html" method="post" style="float: left;">
                    <input type="hidden" name="disconnect" value="true">
                    <input type="submit" value="Quitter">
                </form>
                <br>
				<br>
				<br>
            <?
        }else{
            echo html_errors($bkp_errors);
        }
    }
}
?>
<p class="footer"><a href="https://github.com/benyounesmehdi/MBackuper" title="MBackuper" target="_self">MBackuper</a>, copyright <a href="http://www.mehdi-benyounes.com/" title="Site officiel de Mehdi Benyounes" target="_self">Mehdi Benyounes</a>, tous droits réservés</p>
</body>
</html>