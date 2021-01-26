<?php
require_once("outil.php");
$html=debut("Login");
$mysqli=importerBase("mariadb","football","football","utilisateurs");

$html.="<form action='' method='POST'>
username <input type='text' name='username'><br>
password <input type='password' name='password'><br>
<input type='submit' value='ok'><br>
</form>";

$username=$_POST['username'];
$pass=$_POST['password'];

$requete="SELECT pass FROM login WHERE username='$username'";
$resultat=$mysqli->query($requete);

//if(!empty($_POST['username')){
	$ligne=$resultat->fetch_object();
//}

if(password_verify($pass,$ligne->pass)){
  echo "login successful";
}
else {echo "login unsuccessful";}

$html.=fin();
echo $html;

?>

