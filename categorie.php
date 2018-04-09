<?php
require_once __DIR__ . '/include/init.php';

include __DIR__ . '/layout/top.php';

    $query = 'SELECT nom FROM categorie WHERE id = ' . $_GET['id'];
	$stmt = $pdo->query($query);
	$categorie = $stmt->fetch();
    $nom = $categorie['nom']; 
   
    $query = 'SELECT * FROM produit WHERE categorie_id = ' . $_GET['id'];
	$stmt = $pdo->query($query);
	$produits = $stmt->fetchAll();
	// $nom = $produit['nom'];
	// $description = $produit['description'];
	// $reference = $produit['reference'];
	// $prix = $produit['prix'];
	// $categorieId = $produit['categorie_id'];
	// $photoActuelle = $produit['photo'];
 
?>   
  <h2> <?= $nom; ?></h2>  

<div class="row">


<?php
foreach ($produits as $produit):    
    $src = (!empty($produit['photo']))
    ?  PHOTO_WEB.$produit['photo']
    : PHOTO_DEFAULT
    ;			
?>	
 <div class="col-sm-3">
  <div class="card">
    <img class="card-img-top" src="<?= PHOTO_WEB.$produit['photo']; ?> " style="height:150px; width:150px;">
    <div class="card-body">
      <h5 class="card-title text-center"><?=$produit['nom']; ?></h5>
      <p class="card-text text-center"><?=prixFr($produit['prix']); ?></p>
      <p class="card-text text-center">
        <a class="btn btn-primary" href="produit.php?id=<?=$produit['id'];?>"> Voir</a>
      </p>

    </div>
    </div>
    </div>
<?php
endforeach;
?>

 </div>
<?php
include __DIR__ . '/layout/bottom.php';
?>