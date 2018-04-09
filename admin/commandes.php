<?php

require_once __DIR__ . '/../include/init.php';
adminSecurity();


if(isset($_POST['modifier-statut'])){
    echo 'ici';     
    $query = 'UPDATE commande SET  statut = :statut, date_statut = now() WHERE id = :id';
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':statut', $_POST['statut']);
    $stmt->bindValue(':id', $_POST['commandeId']);
    $stmt->execute();

    setFlashMessage( ' le statut a été modifié');
}  
$query = <<<EOS
SELECT  concat_ws(' ',utilisateur.nom, utilisateur.prenom) AS username, commande.montant_total, commande.id, commande.date_statut, commande.statut, commande.date_commande
FROM commande
JOIN utilisateur ON utilisateur.id =  commande.utilisateur_id
EOS;
    $stmt = $pdo->query($query);
    $commandes = $stmt->fetchAll();

;

include __DIR__ . '/../layout/top.php';

?>

<h2>Gestion commandes</h2>


<table class="table table-bordered">
  <thead>
    <tr>
      <th scope="col">id</th>
      <th scope="col">client</th>
      <th scope="col">montant commande</th>
      <th scope="col">date commande</th>
      <th scope="col">statut</th>
      <th scope="col">date statut</th>
    </tr>
  </thead>
  <tbody>

  <?php
    foreach($commandes as $commande):
    ?>

      <tr>
      <td><?= $commande['id'];?></td>
      <td><?= $commande['username'];?></td>
      <td><?= prixFr($commande['montant_total']);?></td>
      <td><?= dateFr($commande['date_commande']);?></td>
      <td>
      <form method="post">
      <input type="hidden" value="<?=$commande['id'];?>" name="commandeId" >
      <select name="statut" id="">
         <option value="en cours" <?php if ($commande['statut'] == 'en cours') {echo 'selected';} ?>>En cours</option>
         <option value="envoyé" <?php if ($commande['statut'] == 'envoyé') {echo 'selected';} ?>>Envoyé</option>
         <option value="livré" <?php if ($commande['statut'] == 'livré') {echo 'selected';} ?>>Livré</option>
      </select>
      <button type="submit" class="btn btn-primary" name="modifier-statut"> Modifier</button>
      </form>
      </td>
      <td><?= dateFr($commande['date_statut']);?></td>
    </tr>
   
    <?php
    endforeach;
    ?>
 
  </tbody>
</table>
<?php
include __DIR__ . '/../layout/bottom.php';
?>