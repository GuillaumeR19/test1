<?php
	//connexion à la base
try
	{
		$bdd = new PDO('mysql:host=localhost;dbname=jointure;charset=utf8', 'root', '',array(
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
			)
		);
	}
	catch (Exception $e)
	{
		die('Erreur : ' . $e->getMessage());
	}
?>


    <pre>
	<?php
	//print_r($_FILES);
	print_r($_FILES['fichier']);
	?>
</pre>
    <?php
	//1 Ko = 1024 octets
	//1 Mo = 1048576 octets
	//1 Go = 1 073 741 824 octets
	//1 To = 1 099 511 627 776 octets
	$maxfilesize = 1048576; //1 Mo
	if($_FILES['fichier']['error'] === 0 AND $_FILES['fichier']['size'] < $maxfilesize){
		//pas d'erreur et le fichier n'est pas trop volumineux
		//on teste l'extension
		$extensions_autorisees = array('jpg', 'jpeg', 'png', 'gif');
		$fileInfo = pathinfo($_FILES['fichier']['name']);
		$extension = $fileInfo['extension'];
		if(in_array($extension, $extensions_autorisees)){
			//extension valide
			echo 'c\'est bon';
			//transférer définitivement le fichier sur le serveur
			//on renomme le fichier

			//création de la miniature
			//on crée une copie de l'image
			//test de l'extension
			if($extension == 'jpg' OR $extension == 'jpeg'){
				//jpeg ou pjg
				$newImage = imagecreatefromjpeg($_FILES['fichier']['tmp_name']);	
			}
			elseif($extension == 'png'){
				//png
				$newImage = imagecreatefrompng($_FILES['fichier']['tmp_name']);
			}
			else{
				//fichier gif
				$newImage = imagecreatefromgif($_FILES['fichier']['tmp_name']);
			}
			
			//largeur
			$imageWidth = imagesx($newImage);
			//hauteur
			$imageHeight = imagesy($newImage);

			//echo $imageWidth;
			//je décide de la largeur des miniatures
			$newWidth = 200;
			//on calcule la nouvelle hauteur
			$newHeight = ($imageHeight * $newWidth) / $imageWidth;

			//on crée la nouvelle image
			$miniature = imagecreatetruecolor($newWidth, $newHeight);

			imagecopyresampled($miniature, $newImage, 0, 0, 0, 0, $newWidth, $newHeight, $imageWidth, $imageHeight);
			$nom = md5(uniqid(rand(), true));

			//on teste l'extension
			if($extension == 'jpg' OR $extension == 'jpeg'){
				imagejpeg($miniature, 'Miniature/thumbnails/' . $nom . '.' . $extension);
			}
			elseif($extension == 'png'){
				imagepng($miniature, 'Miniature/thumbnails/' . $nom . '.' . $extension);
			}
			else{
				imagegif($miniature, 'Miniature/thumbnails/' . $nom . '.' . $extension);
			}

			move_uploaded_file($_FILES['fichier']['tmp_name'], 'Miniature/'.$nom.'.'.$extension);
		    
            $reponse = $bdd->prepare('INSERT INTO image(nom, taille) VALUES (:nom, :taille)');
            $reponse->bindValue(':nom', $nom. '.' . $extension, PDO::PARAM_STR);
            $reponse->bindValue(':taille', $_FILES['fichier']['size'], PDO::PARAM_INT);
            $reponse->execute(); 
            
            
            //création d'un fichier log
            // $contenu = file_put_contents('log.txt', $nom. '.' . $extension.' . Ce fichier fait '. $_FILES['fichier']['size'] . ' octets.');
            $contenu = 'date : ' . date('Y-m-d') . '-- taille : ' . $_FILES['fichier']['size'] . ' -- nom : ' . $nom . '.'. $extension;
            file_put_contents('log-' . $nom. '.txt', $contenu);
            
            $reponse->closeCursor();
            } 
            
		else{
			//extension non autorisée
			echo 'pas bonne extension';
		}
	}
	else{//problème:
		if($_FILES['fichier']['error'] > 0){
			//erreur lors du transfert
			echo 'erreur de transfert';
		}
		else{
			//fichier trop volumineux
			echo 'fichier trop gros';
		}
		echo 'c\'est pas bon';
	}

	//pour tester l'extension du fichier
	$fileInfo = pathinfo($_FILES['fichier']['name']);
	print_r($fileInfo);
	?>
