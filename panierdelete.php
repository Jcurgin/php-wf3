<?php
require_once __DIR__ . '/include/init.php';

unset($_SESSION['panier']);

setFlashMessage('Votre panier est vide');

header('Location: panier.php');
die;