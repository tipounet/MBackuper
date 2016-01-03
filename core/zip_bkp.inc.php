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
 * Génération d'une archive zip à partir d'un répertoire (fichiers et dossier de façon récursive)
 */

class zip_bkp {

	public $return;
	public $bkp_errors;
	protected $folders_data;
	protected $repertoire;
	protected $nom_fichier;
	protected $ignore;

	public function __construct($options = array()){
		$default = array(
			'folders_data'	=> NULL,
			'repertoire'	=> '/',
		);
		$options = array_merge($default,$options);
		extract($options);

		$this->folders_data = $folders_data;
		if(count($this->folders_data)==0){
			$this->bkp_errors[] = 'Il n\'y a aucun répertoire à sauvegarder, veuillez éditier le fichier &laquo; config.inc.php &raquo;';
			return;
		}else{
			foreach ($this->folders_data as $folder){
				if(realpath($folder)==NULL){
					$this->bkp_errors[] = 'Il semblerait que le répertoire &laquo; '.$folder.' &raquo; n\'existe pas, veuillez éditier le fichier &laquo; config.inc.php &raquo;';
					return;
				}
			}
		}

		$this->repertoire = $repertoire;
		if(!is_dir($this->repertoire)){
			$this->bkp_errors[] = 'Une erreur s\'est produite lors l\'accès au répertoire &laquo; '.htmlspecialchars($this->repertoire).' &raquo;';
			return;
		}

		$this->nom_fichier = $nom_fichier;
		$this->ignore = $ignore;
		if(count($this->ignore)!=0){
			$regexp = NULL;
			foreach($this->ignore as $value){
				$regexp .= $value.'|';
			}
			$this->regexp = '/^'.preg_quote(str_replace(array('/','/'),DIRECTORY_SEPARATOR,substr($regexp,0,-1)),DIRECTORY_SEPARATOR=='/'?'/':'\\').'$/';
		}else{
			$this->regexp = NULL;
		}

		$this->generer();

		$this->bkp_errors = array();

	}

	protected function generer(){

		foreach ($this->folders_data as $folder){

			if($this->nom_fichier==NULL){
				$nom_fichier = $folder.'.zip';
				$nom_fichier = str_replace(array('../','/'),array(NULL,'-'),$nom_fichier);
			}else{
				$nom_fichier = $this->nom_fichier;
			}

			$this->return['folders'][] = $nom_fichier;

			$zip = new ZipArchive();
			$zip->open($this->repertoire.$nom_fichier, ZipArchive::CREATE | ZipArchive::OVERWRITE);

			$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(realpath($folder)), RecursiveIteratorIterator::LEAVES_ONLY);

			foreach ($files as $name => $file){
				if(!$file->isDir()){
					$filePath = $file->getRealPath();
					$relativePath = substr($filePath,strlen(realpath($folder))+1);
					if(preg_match('/^'.CURRENT_FOLDER.preg_quote(DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR=='/'?'/':'\\').'.*\.zip/',$relativePath)==false){
						if($this->regexp!==NULL){
							$relativePath_array = explode(DIRECTORY_SEPARATOR,$relativePath);
							if(preg_match($this->regexp,$relativePath_array[count($relativePath_array)-1])==false){
								$zip->addFile($filePath,$relativePath);
							}
						}else{
							$zip->addFile($filePath,$relativePath);
						}
					}
				}
			}
			$zip->close();

		}

	}

}