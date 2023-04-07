<?php

//phpinfo();

$connect = new PDO('mysql:host=localhost:3306;dbname=megabonnesmeufs_db', 'Joeil', 'joeil92250');

if (!$connect) {
	die('Could not connect : ' . mysql_error());
}

if ($_POST) {
	$username = $_POST['username'];
	$password = $_POST['password'];
	$dns = $_POST['dns'];

	$result = shell_exec("/var/www/bonnesmeufs/createUser.sh $username $password $dns");
	$dbResult = shell_exec("/var/www/bonnesmeufs/createDB.sh $dns $username $password");
	$backups =  shell_exec('tar -cvzf backups_`date +%d-%m-%y`.tar.gz backups');

}

?>


<!DOCTYPE html>
<html>
<head>
<title>Megabonnesmeufs - accueil</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
</head>
<body class="w-75 m-auto">

<h1 class="text-center">Bienvenue sur Megabonnesmeufs, le site de référence!</h1>
<form method="post">
	<div class="row">
		<div class="col">
        		<label for="username">Utilisateur</label>
        		<input type="text" class="form-control" name="username" id="username">
		</div>
		<div class="col">
		        <label for="password">Mot de passe</label>
		        <input type="password" name="password" class="form-control" id="password">
		</div>
	</div>
        <label for="dns">DNS</label>
        <input type="text" name="dns" class="form-control" id="dns">
	<input type="submit" class="my-3 btn btn-primary" value="Envoyer">
</form>

<pre>
	<?php
		echo shell_exec('du -a -h /var/www/bonnesmeufs | sort -hr');
		echo $backups;
		echo $result;
		echo $dbResult;
	?>
</pre>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
</html>