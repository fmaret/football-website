<?php

require_once("../outils.php");
require __DIR__ . "/vendor/autoload.php";
use Goutte\Client;

$mysqli=importerBase("mariadb", "football", "football", "football");

$html=debut("Web Scraping", "../monStyle.css");


$client = new Client();
$crawler = $client->request('GET', 'https://www.lequipe.fr/Football/ligue-1/page-classement-equipes/general');
//$crawler = $client->request('GET', "https://www.lemonde.fr");

/*
$crawler->filter('div.article > a')->each(function ($node) use ($out) {
  echo "okok<br>";
  //$url = $node->attr('href');
  //echo $url."<br>";
  //$nom = trim($node->filter('.article__title')->text());
  //print_r([$nom,$url]);
});
*/


//$requete="DELETE FROM equipes";
//$mysqli->query($requete);


//equipes
$a=$crawler->filter('table.table--teams > tbody > tr');
//$a=$a->filter('td.table__col--points > a');
$a->each(function ($node) use ($out){
	//on doit recharger mysqli sinon ça considere que c'est NULL
	$mysqli=importerBase("mariadb", "football", "football", "football");
	//parfois il y a un petit 1 ou 2 pour montrer que l'equipe est en forme ou pas recemment on veut donc l'enlever et donc limiter la taille de la liste à 9
	$listeAttributs=explode(" ",$node->text());
	if (count($listeAttributs)!=10){ //c'est ici qu'on retire la forme de l'equipe
		unset($listeAttributs[2]); //la forme est au deuxieme indice
		$listeAttributs=array_values($listeAttributs); //pour reindexer la liste, sinon la valeur 2 est NULL
	}
	$nom=$listeAttributs[1];
	$points=$listeAttributs[2];
	//echo $points."<br>";
	$joues=$listeAttributs[3];
	$gagnes=$listeAttributs[4];
	$nuls=$listeAttributs[5];
	$perdus=$listeAttributs[6];
	$butsMarques=$listeAttributs[7];
	$butsEncaisses=$listeAttributs[8];
	$diffButs=$listeAttributs[9];
	
	$requete="SELECT nom FROM equipes WHERE nom='".$nom."'";
	$resultat=$mysqli->query($requete);
	
    if($resultat->num_rows==0){//si l'equipe n'est pas dans la bdd on l'ajoute
  		$requete="INSERT INTO equipes VALUES ('$nom', '$points', '$joues', '$gagnes', '$nuls', '$perdus', '$butsMarques', '$butsEncaisses', '$diffButs')";
  		$mysqli->query($requete);
  	}
  	else{ //si l'équipe existe on met à jour tout sauf le nom
  		$requete="UPDATE equipes SET points='$points', joues='$joues', gagnes='$gagnes', nuls='$nuls', perdus='$perdus', butsMarques='$butsMarques', butsEncaisses='$butsEncaisses', diffButs='$diffButs' WHERE nom='$nom'";
  		$mysqli->query($requete);
  	}
});

/*
//Maintenant on va essayer de recuperer les liens pour acceder a la page de chaque equipe (20 liens)

$a=$crawler->filter('table.table--teams > tbody > tr > td.table__col--name > a');


$a->each(function ($node) use ($out){
	$lienequipe=trim($node->attr('href'));
	//on doit refaire client car dans la fonction each on perd les infos d'avant
	$client = new Client();
	$crawler = $client->request('GET', 'https://www.lequipe.fr'.$lienequipe);
	$b=$crawler->filter('section.effectifclub .category-expand-list > table > tr');
	// $b contients toutes les lignes pour chaque equipe on va donc ajouter chaque joueur a la bdd des joueurs
	//pour chaque joueur il faut recuperer son lien (comme pour les equipes)
	$b->each(function ($node) use ($out){
		//on doit recharger mysqli sinon ça considere que c'est NULL
	    $mysqli=importerBase("mariadb", "football", "football", "football");
		$texte=trim($node->text());
		$texteSplit=explode(" ", $texte);
		$clientjoueur=new Client();
		//$crawlerjoueur=$clientjoueur->request('GET', 'https://www.lequipe.fr'.$lienjoueur);
		echo $texteSplit[0]."<br>";
		echo $texteSplit[1]."<br>";
		echo $texteSplit[2]."<br>";
		echo $texteSplit[3]."<br>";
		echo "<br>";

	});
});

*/


/*
//test pour avoir les infos d'un seul joueur
$clientjoueur=new Client();
$crawlerjoueur=$clientjoueur->request('GET', 'https://www.lequipe.fr/Football/FootballFicheJoueur61356.html');
$c=$crawlerjoueur->filter('section.titre.data-content');
echo $c->text();
*/


//importation du calendrier

$journees=38;
$numerosJournees[]="1re";
for ($i=2;$i<=$journees;$i++){
	$numerosJournees[]=$i."e";
}


for ($i=0;$i<$journees;$i++){
	$client = new Client();
	$crawler = $client->request('GET', 'https://www.lequipe.fr/Football/ligue-1/page-calendrier-resultats/'.$numerosJournees[$i].'-journee');
	$a=$crawler->filter('div.grid.grid--noborder > div.grid__item');
	$a->each(function ($node) use ($out){
		$mysqli=importerBase("mariadb", "football", "football", "football");
		$texte=$node->text();
		$texteSplit=explode(" ",$texte);
		if (count($texteSplit)==5){
			$equipe1=$texteSplit[0];
			$equipe2=$texteSplit[4];
			$buts1=$texteSplit[1];
			$buts2=$texteSplit[3];

			$requete="SELECT * FROM matchs WHERE equipe1='".$equipe1."' AND equipe2='".$equipe2."'";
			$resultat=$mysqli->query($requete);
			
		    if($resultat->num_rows==0){//si le match n'est pas dans la bdd on l'ajoute
		  		$requete="INSERT INTO matchs (equipe1, equipe2, journee, buts1, buts2) VALUES ('$equipe1', '$equipe2','".($i+1)."', '$buts1', '$buts2')";
		  		$mysqli->query($requete);
		  		//echo "<br> 1 <br>";
		  	}
		  	else{ //si le match existe on met à jour tout sauf les 2 equipes
		  		$requete="UPDATE matchs SET journee='".($i+1)."', buts1='$buts1', buts2='$buts2', WHERE equipe1='$equipe1' AND equipe2='$equipe2'";
		  		$mysqli->query($requete);
		  		//echo "<br> 2 <br>";
		  	}

			echo "<br>".$requete."<br>";
		}

		else{
			$equipe1=$texteSplit[0];
			$equipe2=$texteSplit[2];
			
			$requete="SELECT * FROM matchs WHERE equipe1='".$equipe1."' AND equipe2='".$equipe2."'";
			$resultat=$mysqli->query($requete);
			
		    if($resultat->num_rows==0){//si le match n'est pas dans la bdd on l'ajoute
		  		$requete="INSERT INTO matchs (equipe1, equipe2, journee) VALUES ('$equipe1', '$equipe2', '".($i+1)."')";
		  		$mysqli->query($requete);
		  		//echo "<br> 3 <br>";
		  	}
		  	else{ //si le match existe on met à jour tout sauf les 2 equipes
		  		$requete="UPDATE matchs SET journee='".($i+1)."', WHERE equipe1='$equipe1' AND equipe2='$equipe2'";
		  		$mysqli->query($requete);
		  		//echo "<br> 4 <br>";
		  	}

			echo "<br>".$requete."<br>";
		}
		//$resultat=$mysqli->query($requete);
		//echo $mysqli->error;
		

		//print_r($texteSplit);
	});
}


//importation joueurs

$client = new Client();
$crawler = $client->request('GET', 'https://www.msn.com/fr-fr/sport/football/ligue-1/statistiques-joueurs');
$a=$crawler->filter('tr.rowlink');
$a->each(function ($node) use ($out){
	$mysqli=importerBase("mariadb", "football", "football", "football");
	$b=$node->filter("td");
	//echo $b->text();
	
	/*for ($j=2;$j<10;$j++){
		$b=$b->nextAll();
		echo "<br>".$b->text();
	}*/
	$b=$b->nextAll();
	$nom=$b->text();
	$b=$b->nextAll();
	$equipe=$b->text();
	$b=$b->nextAll()->nextAll();
	$matchs=$b->text();
	$b=$b->nextAll()->nextAll();
	$tempsDeJeu=($b->text());
	if (strlen($tempsDeJeu)>3){
		$tempsDeJeu=substr($tempsDeJeu, 0, 1).substr($tempsDeJeu, 3);
	}
	echo "<br>";
	$b=$b->nextAll();
	$buts=$b->text();
	$b=$b->nextAll();
	$passesDecisives=$b->text();

	//il faut detecter si le joueur existe deja dans la base

	$requete="SELECT * FROM joueurs WHERE nom='".$nom."'";
	$r=$mysqli->query($requete);
	
    if($r->num_rows==0){//si le joueur n'est pas dans la bdd on l'ajoute
  		$requete="INSERT INTO joueurs (nom, equipe, matchs, buts, passesDecisives, tempsDeJeu) VALUES ('".$nom."','".$equipe."','".$matchs."','".$buts."','".$passesDecisives."','".$tempsDeJeu."')";
  		$mysqli->query($requete);
  	}
  	else{ //si le joueur existe et que les valeurs ont changé on met à jour tout sauf le nom
  		//$requete="SELECT * from joueurs WHERE nom='$nom'";
  		$l=$r->fetch_object();
  		if ($equipe!=$l->equipe or $matchs!=$l->matchs or $buts!=$l->buts or $passesDecisives != $l->passesDecisives or $tempsDeJeu != $l->tempsDeJeu){
	  		$requete="UPDATE joueurs SET matchs='".$matchs."', buts='".$buts."', passesDecisives='".$passesDecisives."', tempsDeJeu='".$tempsDeJeu."' WHERE nom='".$nom."'";
	  		echo $requete;
	  		$mysqli->query($requete);
	  		/*
	  		echo "<br>$equipe";
	  		echo "<br>$l->equipe";
	  		echo "<br>$matchs";
	  		echo "<br>$l->matchs";
	  		echo "<br>$buts";
	  		echo "<br>$l->buts";
	  		echo "<br>$passesDecisives";
	  		echo "<br>$l->passesDecisives";
	  		echo "<br>$tempsDeJeu";
	  		echo "<br>$l->tempsDeJeu";
	  		*/
	  	}
  	}

	//echo "<br>".$requete;
	//echo "<br>".$tempsDeJeu;

	
});


$html.=fin();

echo $html;


?>