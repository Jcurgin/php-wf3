<?php
require_once __DIR__ . '/include/init.php';


if(isset($_POST['commander'])){
$query = <<<EOS
INSERT INTO commande(
	utilisateur_id,
	montant_total
) VALUES (
	:utilisateur_id,
	:montant_total

)
EOS;
			$stmt = $pdo->prepare($query);
			$stmt->bindValue(':utilisateur_id', $_SESSION['utilisateur']['id']);
			$stmt->bindValue(':montant_total', getTotalPanier());
            $stmt->execute();
            
            $commande_id = $pdo->lastInsertId();
          
$query = <<<EOS
INSERT INTO detail_commande(
	commande_id,
    produit_id,
    prix,
    quantite
) VALUES (
	:commande_id,
    :produit_id,
    :prix,
    :quantite

)
EOS;
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':commande_id', $commande_id);

            foreach($_SESSION['panier'] as $produitId => $detail){
                $stmt->bindValue(':produit_id', $produitId);
                $stmt->bindValue(':prix', $detail['prix']);
                $stmt->bindValue(':quantite', $detail['quantite']);
                $stmt->execute();
            }
            setFlashMessage('Commande confirmée');
            
            $_SESSION['panier'] = [];
}

if(isset($_POST['modifier-quantite'])){
    modifierQuantitePanier($_POST['produit-id'], $_POST['quantite']);
    setFlashMessage('la quantité a été modifié');
}

if(empty($_SESSION['panier'])){
    setFlashMessage('Votre panier est vide');
}

include __DIR__ . '/layout/top.php';

?>
<h1> Mon panier</h1>

<?php
if(!empty($_SESSION['panier'])){

?>

<table class="table table-bordered">
  <thead>
    <tr>
      <th scope="col">Nom du produit</th>
      <th scope="col">Prix unitaire</th>
      <th scope="col">Quantité</th>
      <th scope="col">Prix total</th>
    </tr>
  </thead>
  <tbody>
  
  <?php
    foreach($_SESSION['panier'] as $produitId => $detail):
    ?>

    <tr>
      <td><?=$detail['nom']?></td>
      <td><?=prixFr($detail['prix'])?></td>
      <td> 
      <form method="post">
      <input type="number"  min="0" value="<?=$detail['quantite'];?>"name="quantite">
      <input type="hidden" value="<?=$produitId;?>" name="produit-id" > 
      <button type="submit" class="btn btn-primary" name="modifier-quantite"> Modifier</button>
      </form>
       </td>
      <td><?=prixFr($detail['quantite']*$detail['prix'])?></td>
    </tr>
   

   <?php
 endforeach;
   ?> 
   <tr>
     <th colspan="3">Total</th>
     <td><?= prixFr(getTotalPanier());?></td>     
     </tr>  
  </tbody>
</table>


    <form method="post">
        <p class="text-right">
            <button type="submit" name="commander" class="btn btn-primary">
                Valider la commande
            </button>
        </p>
    </form>
<?php
  }
?>
<?php
   if(!isUserConnected()):
?>
    <div class="alert alert-info">
        Vous devez vous connecter ou vous inscrire pour valider la commande
    </div>

<?php
   endif;
?>
<?php
include __DIR__ . '/layout/bottom.php';
?>