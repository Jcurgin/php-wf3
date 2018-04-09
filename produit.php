<?php
require_once __DIR__ . '/include/init.php';

$query = 'SELECT * FROM produit WHERE id = ' . $_GET['id'];
$stmt = $pdo->query($query);
$produit = $stmt->fetch();

$src = (!empty($produit['photo']))
?  PHOTO_WEB.$produit['photo']
: PHOTO_DEFAULT
;			

if(!empty($_POST)){
    ajoutPanier($produit, $_POST['quantite']);
    setFlashMessage('le produit a été ajouté au papnier');
}

include __DIR__ . '/layout/top.php';
?>
<h1> <?= $produit['nom']; ?></h1>

<div class="row">
    <div class="col-3">
    <img class="card-img-top" src="<?= $src; ?> " style="height:150px; width:150px;">
    <p><?=prixFr($produit['prix']); ?></p>
    <form class="form-inline" method="post">
    <label>Qté</label>
    <select name="quantite" class="form-control">
        <?php
        for($i = 1;$i<=10;$i++ ):
        ?>
                <option value="<?=$i;?>"><?=$i;?></option>
        <?php
        endfor;
        ?>
    </select>
    <button type="submit" class="btn btn-primary">Ajouter au panier</button>
    </form>
    </div>
    <div class="col-9">
    <p class="card-title"><?=$produit['description']; ?></p>

    </div>
</div>


<?php
include __DIR__ . '/layout/bottom.php';
?>