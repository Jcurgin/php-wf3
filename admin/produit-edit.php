<?php
/*
Faire le formulaire d'édition de produit
 - nom : input text - obligatoire
 - description : textarea - obligatoire
 - reference : input text - obligatoire, 50 caractères max, unique
 - prix : input text - obligatoire
 - categorie : select - obligatoire
 Si le formulaire est bien rempli : INSERT en bdd et redirection vers la liste avec message de confirmation,
 sinon messages d'erreurs et champs pré-remplis avec les valeurs saisies
*/
 require_once __DIR__ . '/../include/init.php';
adminSecurity();

$nom = $description = $reference = $prix = $categorieId = $photoActuelle =	 '';
$errors = [];

if (!empty($_POST)) {

	sanitizePost();
	extract($_POST);
	$categorieId = $_POST['categorie'];

	if (empty($_POST['nom'])) {
		$errors[] = 'Le nom est obligatoire';
	}

	if (empty($_POST['description'])) {
		$errors[] = 'La description est obligatoire';
	}

	if (empty($_POST['reference'])) {
		$errors[] = 'La référence est obligatoire';
	} elseif (strlen($_POST['reference']) > 50) {
		$errors[] = 'La référence ne doit pas faire plus de 50 caractères';
	} else {
		$query = 'SELECT count(*) FROM produit WHERE reference = :reference';

		if(isset($_GET['id'])){
			$query .= 'AND id !=' . $_GET['id'];
		}
		$stmt = $pdo->prepare($query);
		$stmt->bindValue(':reference', $_POST['reference']);
		$stmt->execute();
		$nb = $stmt->fetchColumn();

		if ($nb != 0) {
			$errors[] = "Il existe déjà un produit avec cette référence";
		}
	}

	if (empty($_POST['prix'])) {
		$errors[] = 'Le prix est obligatoire';
	}

	if (empty($_POST['categorie'])) {
		$errors[] = 'La catégorie est obligatoire';
	}
			//si une image a été téléchargée
	if (!empty($_FILES['photo']['tmp_name'])){
		if($_FILES['photo']['size'] > 1000000){
			$errors[] = 'la photo ne doit pas faire plus de 1Mo';
		}
		$allowedMimeTypes = [
			'image/png',
			'image/jpeg',
			'image/gif'
		];
		
		if(!in_array($_FILES['photo']['type'], $allowedMimeTypes)){
			$errors[] = 'la photo doit être une image GIF, JPG ou PNG';
		} 	if(empty($errors)){
			if(!empty($_FILES['photo']['tmp_name'])){
				$originalName = $_FILES['photo']['name'];
				//on retrouve l'extension du fichier original à partir de son nom
				// 'ex: .png pour mon_fichier.png'
				$extension = substr($originalName, strrpos($originalName, '.'));
				//le nom que va avoir le fichier dans le répertoire photo
				$nomPhoto = $_POST['reference'] . $extension;
				//on la supprime
				if(!empty($photoActuelle)){
					unlink(PHOTO_DIR . $photoActuelle);
				}
				// enregistrement du fichier dans le repertoire photo
				move_uploaded_file($_FILES['photo']['tmp_name'], PHOTO_DIR . $nomPhoto);
			} else {
				$nomPhoto = $photoActuelle;
			}
		}
	}
		
	if (empty($errors)) {

		if (isset($_GET['id'])) { // modification
			$query = 'UPDATE produit SET nom = :nom, description = :description, reference = :reference, prix = :prix, categorie_id = :categorie, photo = :photo WHERE id = :id';
			$stmt = $pdo->prepare($query);
			$stmt->bindValue(':nom', $_POST['nom']);
			$stmt->bindValue(':description', $_POST['description']);
			$stmt->bindValue(':reference', $_POST['reference']);
			$stmt->bindValue(':prix', $_POST['prix']);
			$stmt->bindValue(':categorie', $_POST['categorie']);
			$stmt->bindValue(':photo',$nomPhoto);
			$stmt->bindValue(':id', $_GET['id']);
			$stmt->execute();

			// enregistrement d'un message en session
		setFlashMessage('Le produit est enregistrée');

		// redirection vers la page de liste
		header('Location: produits.php');
		die;
		} else {

		$query = <<<EOS
INSERT INTO produit(
	nom,
	description,
	reference,
	prix,
	categorie_id,
	photo
) VALUES (
	:nom,
	:description,
	:reference,
	:prix,
	:categorie_id,
	:photo
)
EOS;
			$stmt = $pdo->prepare($query);
			$stmt->bindValue(':nom', $_POST['nom']);
			$stmt->bindValue(':description', $_POST['description']);
			$stmt->bindValue(':reference', $_POST['reference']);
			$stmt->bindValue(':prix', $_POST['prix']);
			$stmt->bindValue(':categorie_id', $_POST['categorie']);
			$stmt->bindValue(':photo', $nomPhoto);
			$stmt->execute();

			setFlashMessage('Le produit est enregistré');
			header('Location: produits.php');
			die;
		}
	}
} elseif (isset($_GET['id'])) {
	// en modification, si on n'a pas de retour de formulaire
	// on va chercher la catégorie en bdd pour affichage
	$query = 'SELECT * FROM produit WHERE id = ' . $_GET['id'];
	$stmt = $pdo->query($query);
	$produit = $stmt->fetch();
	$nom = $produit['nom'];
	$description = $produit['description'];
	$reference = $produit['reference'];
	$prix = $produit['prix'];
	$categorieId = $produit['categorie_id'];
	$photoActuelle = $produit['photo'];
}

// pour construire le select des catégories :
$query = 'SELECT * FROM categorie';
$stmt = $pdo->query($query);
$categories = $stmt->fetchAll();

include __DIR__ . '/../layout/top.php';

if (!empty($errors)) :
?>
	<div class="alert alert-danger">
		<h5 class="alert-heading">Le formulaire contient des erreurs</h5>
		<?= implode('<br>', $errors); ?>
	</div>
<?php
endif;
?>
<h1>Edition produit</h1>
<!-- 
	l'attribtu enctype est obligatoire pour un formulaire qui contien un téléchargement de fichier
-->	
<form method="post" enctype="multipart/form-data">
	<div class="form-group">
		<label>Nom</label>
		<input type="text" name="nom" class="form-control" value="<?= $nom; ?>">
	</div>
	<div class="form-group">
		<label>Description</label>
		<textarea name="description" class="form-control"><?= $description; ?></textarea>
	</div>
	<div class="form-group">
		<label>Référence</label>
		<input type="text" name="reference" class="form-control" value="<?= $reference; ?>">
	</div>
	<div class="form-group">
		<label>Prix</label>
		<input type="text" name="prix" class="form-control" value="<?= $prix; ?>">
	</div>
	<div class="form-group">
		<label>Categorie</label>
		<select name="categorie" class="form-control">
			<option value=""></option>
			<?php
			foreach ($categories as $categorie) :
				$selected = ($categorie['id'] == $categorieId)
					? 'selected'
					: ''
				;
			?>
				<option value="<?= $categorie['id']; ?>" <?= $selected; ?>><?= $categorie['nom']; ?></option>
			<?php
			endforeach;
			?>
		</select>
	</div>
	<div class="form-group">
	<label>Photo </label>
	<input type="file" name="photo">
	</div>

	<?php
	 if(!empty($photoActuelle)) :
		echo '<p>Actuellement: <br><img src="' . PHOTO_WEB.$photoActuelle.'" height="150px"></p>';

	 endif;		
	?>
	<input type="hidden" name="photoActuelle" value="<?=$photoActuelle; ?>">
	<div class="form-btn-group text-right">
		<button type="submit" class="btn btn-primary">Enregistrer</button>
		<a class="btn btn-secondary" href="produits.php">
			Retour
		</a>
	</div>
	
</form>

<?php
include __DIR__ . '/../layout/bottom.php';
?>
