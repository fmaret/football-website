<?php


define("BDD_LOGIN", "edf");
define("BDD_MOTDEPASSE", "edf");
define("BDD_BASEDEDONNEES", "edf");
define("BDD_SERVEUR", "mariadb");

$mysqli=new mysqli(BDD_SERVEUR, BDD_LOGIN, BDD_MOTDEPASSE, BDD_BASEDEDONNEES);

/*Vérification de la connexion */
if ($mysqli->connect_errno){
   printf("Echec de la connexion : %s\n", $mysqli->connect_error);
   exit();
}
$mysqli->query("SET CHARACTER SET utf8");


function debut($titre, $liencss="monStyle.css"){
    $html="";
    $html.="<!DOCTYPE html>
<html>
    <head>
	<title>".$titre."</title>
	<link rel='stylesheet' href='".$liencss."'>	
    </head>
    <body>
    <header>
    <div class='onglets'>
      <a href='index.php'>Accueil</a>
      <a href=''>Classement</a>
      <a href=''>Paris</a>
      <a href=''>Mon compte</a>
      <a href=''>Groupes</a>
      <a href='connexion.php'>Connexion</a>
      <a href=''>Deconnexion</a>
      <a href=''>Inscription</a>
    </div>
    </header>
";
    return($html);
}

function fin(){
    $html="";
    $html.="
    </body>
</html>";
    return($html);
}

function importerBase($serveur, $login, $mdp, $bdd){
  $mysqli=new mysqli($serveur, $login, $mdp, $bdd);

  /*Vérification de la connexion */
  if ($mysqli->connect_errno){
    printf("Echec de la connexion : %s\n", $mysqli->connect_error);
    exit();
  }
  $mysqli->query("SET CHARACTER SET utf8");
  return $mysqli;

}

?>