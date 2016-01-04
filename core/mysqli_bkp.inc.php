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
 * Sauvegarde de base de données
 */

class mysqli_bkp extends mysqli {

	public $return;
	public $bkp_errors;
	protected $mysql_data;
	protected $repertoire;
	protected $nom_fichier;
	protected $fichier;

	public function __construct($options = array()){
		$default = array(
			'mysql_data'	=> NULL,
			'repertoire'	=> '/',
			);
		$options = array_merge($default,$options);
		extract($options);

		$this->host				= $mysql_data['host'];
		$this->port				= $mysql_data['port'];
		$this->user				= $mysql_data['user'];
		$this->pass				= $mysql_data['pass'];
		$this->bdd				= $mysql_data['bdd'];
		$this->socket			= $mysql_data['socket'];
		$this->charset			= $mysql_data['charset'];
		$this->collation		= $mysql_data['collation'];
		$this->data_directory	= $mysql_data['data_directory'];

		@parent::__construct($this->host,$this->user,$this->pass,$this->bdd,$this->port,$this->socket);
		if($this->connect_error){
			$this->bkp_errors[] = 'Une erreur s\'est produite lors de la connexion aux bases de données';
			return;
		}
		
		$this->repertoire = $repertoire;
		if(!is_dir($this->repertoire)){
			$this->bkp_errors[] = 'Une erreur s\'est produite lors l\'accès au répertoire &laquo; '.htmlspecialchars($this->repertoire).' &raquo;';
			return;
		}

		$this->nom_fichier = $this->bdd.'.sql';
		$this->fichier = @fopen($this->repertoire.$this->nom_fichier,'w');
		if(!$this->fichier){
			$this->bkp_errors[] = 'Une erreur s\'est produite lors de l\'écriture du fichier &quot;'.htmlspecialchars($this->nom_fichier).'&quot;';
			return;
		}
		
		$this->generer();

		$this->bkp_errors = array();

	}

	protected function utf8toiso($string)	{
		$return = '';
		$encodage = mb_detect_encoding($string,'UTF-8,ISO-8859-1');
		if($encodage!='ISO-8859-1'){
			$return = mb_convert_encoding($string,'ISO-8859-1',$encodage);
			return $return;
		}else{
			return $string;
		}
	}

	protected function isotoutf8($string){
		$return = '';
		$encodage = mb_detect_encoding($string,'UTF-8,ISO-8859-1');
		if($encodage!='UTF-8'){
			$return = mb_convert_encoding($string,'UTF-8',$encodage);
			return $return;
		}else{
			return $string;
		}
	}

	protected function insert_clean($string){
		$s1 = array( "\\","'","\r","\n",);
		$s2 = array( "\\\\","''",'\r','\n',);
		return str_replace($s1,$s2,$string);
	}
	
	protected function generer(){

		$this->return['bdd'][] = $this->bdd;

		$sql = '--'."\n";
		$sql.= '-- Base de donnée : '.$this->bdd."\n";
		$sql.= '--'."\n";
		$sql.= '-- Sauvegardée avec MBackuper - https://github.com/benyounesmehdi/MBackuper'."\n";
		$sql.= '-- Une solution créée par Mehdi Benyounes (http://www.mehdi-benyounes.com), tous droits réservés'."\n";
		$sql.= '--'."\n";

		if(strrpos(strtolower($this->collation), 'utf8', -strlen($this->collation))!==false){
			fwrite($this->fichier,"\xEF\xBB\xBF".$sql);
		}else{
			fwrite($this->fichier,$sql);
		}

		if($this->charset!=NULL){
			$this->set_charset($this->charset);
		}

		// TABLES
		$result_tables = $this->query('SHOW TABLE STATUS');
		if($result_tables && $result_tables->num_rows){
			while($obj_table = $result_tables->fetch_object()){

				$this->return['tables'][$this->bdd][] = $obj_table->{'Name'};

				// DROP
				$sql = "\n\n";
				$sql.= 'DROP TABLE IF EXISTS `'. $obj_table->{'Name'}.'`'.";\n";

				// CREATE
				$result_create = $this->query('SHOW CREATE TABLE `'. $obj_table->{'Name'}.'`');
				if($result_create && $result_create->num_rows){
					$obj_create = $result_create->fetch_object();
					$sql.= $obj_create->{'Create Table'}.";\n";
					$result_create->free_result();
				}

				// INSERT
				$result_insert = $this->query('SELECT * FROM `'. $obj_table->{'Name'}.'`');
				if($result_insert && $result_insert->num_rows){
					$sql.= "\n";
					while($obj_insert = $result_insert->fetch_object()){
						$virgule = false;
						
						$sql.= 'INSERT INTO `'. $obj_table->{'Name'}.'` VALUES (';
						foreach($obj_insert as $val){
							$sql.= ($virgule ? ',' : '');
							if(is_null($val)){
								$sql.= 'NULL';
							}else{
								$sql.= '\''. $this->insert_clean($val).'\'';
							}
							$virgule = true;
						}
						
						$sql.= ')'.";\n";
						
					}
					$result_insert->free_result();
				}

				if(strrpos(strtolower($this->collation), 'utf8', -strlen($this->collation))!==false){
					fwrite($this->fichier,$this->isotoutf8($sql));
				}else{
					fwrite($this->fichier,$sql);
				}

			}
			$result_tables->free_result();
		}

		fclose($this->fichier);

		$zip = new ZipArchive();
		$zip->open($this->repertoire.str_replace('.sql',NULL,$this->nom_fichier).'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
		$zip->addFile($this->repertoire.$this->nom_fichier,$this->nom_fichier);
		$zip->close();
		unlink($this->repertoire.$this->nom_fichier);

	}

}

?>