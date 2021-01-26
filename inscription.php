<?php
require_once("outil.php");
$html=debut("Inscription");
$mysqli=importerBase("mariadb","login","login","login");

$html.="<form action='' method='POST'>
username <input type='text' name='username'><br>
password <input type='text' name='password'><br>
<input type='submit' value='ok'><br>
</form>";

$username=$_POST['username'];
$pass=$_POST['password'];
$pass=password_hash($pass,PASSWORD_DEFAULT);

$requete="SELECT * FROM login WHERE username='$username'";
$resultat=$mysqli->query($requete);
if($resultat->num_rows ==0){
  $requete="INSERT INTO login VALUES ('$username','$pass')";
  $resultat=$mysqli->query($requete); }

$html.=fin();
echo $html;

?>