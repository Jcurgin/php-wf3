<?php
require_once __DIR__ . '/../include/init.php';
adminSecurity();

$errors = [];
$nom = '';

if (!empty($_POST)) { // si on a des données venant du formulaire
	// "nettoyage" des données venues du formulaire
	sanitizePost();
	// crée des variables à partir d'un tableau (les variables ont les noms des clés dans le tableau)
	extract($_POST);

	// test de la saisie du champ nom
	if (empty($_POST['nom'])) {
		$errors[] = 'Le nom est obligatoire';
	} elseif (strlen($_POST['nom']) > 50) {
		$errors[] = 'Le nom ne doit pas faire plus de 50 caractères';
	}

	// si le formulaire est correctement rempli
	if (empty($errors)) {
		if (isset($_GET['id'])) { // modification
			$query = 'UPDATE categorie SET nom = :nom WHERE id = :id';
			$stmt = $pdo->prepare($query);
			$stmt->bindValue(':nom', $_POST['nom']);
			$stmt->bindValue(':id', $_GET['id']);
			$stmt->execute();
		} else { // création
			// insertion en bdd
			$query = 'INSERT INTO categorie(nom) VALUES (:nom)';
			$stmt = $pdo->prepare($query);
			$stmt->bindValue(':nom', $_POST['nom']);
			$stmt->execute();
		}

		// enregistrement d'un message en session
		setFlashMessage('La catégorie est enregistrée');

		// redirection vers la page de liste
		header('Location: categories.php');
		die;
	}
} elseif (isset($_GET['id'])) {
	// en modification, si on n'a pas de retour de formulaire
	// on va chercher la catégorie en bdd pour affichage
	$query = 'SELECT * FROM categorie WHERE id = ' . $_GET['id'];
	$stmt = $pdo->query($query);
	$categorie = $stmt->fetch();
	$nom = $categorie['nom'];
}

include __DIR__ . '/../layout/top.php';

if (!empty($errors)) :
?>
	<div class="alert alert-danger">
		<h5 class="alert-heading">Le formulaire contient des erreurs</h5>
		<?= implode('<br>', $errors); // implode transforme un tableau en chaîne de caractères ?>
	</div>
<?php
endif;
?>
<h1>Edition catégorie</h1>

<form method="post">
	<div class="form-group">
		<label>Nom</label>
		<input type="text" name="nom" class="form-control" value="<?= $nom; ?>">
	</div>
	<div class="form-btn-group text-right">
		<button type="submit" class="btn btn-primary">Enregistrer</button>
		<a class="btn btn-secondary" href="categories.php">
			Retour
		</a>
	</div>
</form>
<?php
include __DIR__ . '/../layout/bottom.php';
?>