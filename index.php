<?php

//phpinfo();

session_start();

$connect = new PDO('mysql:host=localhost:3306;dbname=megabonnesmeufs_db', 'Joeil', 'joeil92250');

if (!$connect) {
        die('Could not connect : ' . mysql_error());
}

shell_exec("sudo bash /var/www/bonnesmeufs/usage_ram.sh");
shell_exec("sudo bash /var/www/bonnesmeufs/usage_cpu.sh");
shell_exec("sudo bash usage_hdd.sh");

if ($_POST) {
	//var_dump($_POST);

	if (isset($alert_success)) unset($alert_success);
	if (isset($alert_error)) unset($alert_error);

	if($_FILES) {
		shell_exec("sudo chmod o+w /home/" .  $_SESSION['user']['username'] . "/uploads/");

		if (file_exists("/home/". $_SESSION['user']['username'] ."/uploads/" . $_FILES["file"]["name"])) {
			$alert_error = "Le fichier existe déjà !";
		}
		else {
			move_uploaded_file($_FILES["file"]["tmp_name"], "/home/" . $_SESSION['user']['username'] . "/uploads/" . $_FILES["file"]["name"]);
			$alert_success = "Le fichier a été téléchargé avec succès !";
	        }
	}

	if ($_SESSION['user']) {
		$username = $_SESSION['user']['username'];
		$password = $_SESSION['user']['password'];
		$dns = $_SESSION['user']['dns'];
	}

	if ($_POST['deconnection-user']) unset($_SESSION['user']);

	if ($_POST['connection-user']) {
		$sql = "SELECT id, username, password, dns FROM users WHERE id = :id";

		$prepareSQL = $connect->prepare($sql);
		$prepareSQL->bindParam(':id', $_POST['users'], PDO::PARAM_INT);

		if ($prepareSQL->execute()) $_SESSION['user'] = $prepareSQL->fetch(PDO::FETCH_ASSOC);

		$sql = "SELECT * FROM usage_machine";

		$prepareSQL = $connect->prepare($sql);

		if ($prepareSQL->execute()) $_SESSION['usage'] = $prepareSQL->fetchAll(PDO::FETCH_ASSOC);
	}

	if ($_POST['backups-folder']) {
		$getBackups = shell_exec("./getBackups.sh $username $password $dns 2>&1");

		$filename = $username . "-backup.tar.gz";

		header('Content-type: application/x-tar');
		header('Content-disposition: attachment; filename="' . $filename . '"');

		readfile('/etc/users-stockage/' . $username . '/backups/' . $filename);
	}

        if ($_POST['backups-bdd']) {
		shell_exec("sudo bash getBackups.sh $username $password $dns");

                $filename = $username . "-bdd_backup.sql";

                header('Content-type: application/octet-stream');
                header('Content-disposition: attachment; filename="' . $filename . '"');

                readfile('/etc/users-stockage/' . $username . '/backups/' . $filename);
        }

	if ($_POST['newbdd']) {
        	$newbdd = $_POST['newbdd'];
		$newbdd = shell_exec("./createDB.sh $newbdd $username $password 2>&1");

		$alert_success = "La BDD " . $newbdd . " a été crée avec succès";
	}

	if ($_POST['newpassword']) {
    		$newPassword = $_POST['newpassword'];
		// Change the system password
		$command = 'echo \'' . $newPassword . '\n' . $newPassword . '\' | sudo passwd ' . $username . ' 2>&1';
		$changePassword = shell_exec($command);
		// Change the MySQL password
		$mysqlCommand = 'mysqladmin -u ' . $username . ' -p' . $password . ' password ' . $newPassword;
    		$mysqlOutput = shell_exec($mysqlCommand);

		$sql = "UPDATE users SET password = :newPassword WHERE id = :id";

		$prepareSQL = $connect->prepare($sql);
		$prepareSQL->bindParam(':newPassword', $newPassword, PDO::PARAM_STR);
		$prepareSQL->bindParam(':id', $_SESSION['user']['id'], PDO::PARAM_INT);

		if ($prepareSQL->execute()) {
			$_SESSION['user']['password'] = $newPassword;
			$alert_success = "Mot de passe modifié avec succès";
		} else {
			$alert_error = "Une erreur est survenue, merci de réessayer";
		}

	}

	if ($_POST['create-user']) {
		$username = $_POST['username'];
		$password = $_POST['password'];
	        $dns = $_POST['dns'];

	        $result = shell_exec("./createUser.sh $username $password $dns 2>&1");
	        $dbResult = shell_exec("./createDB.sh $dns $username $password 2>&1");

		$sql = "INSERT INTO users(username, password, dns) VALUES (:username, :password, :dns)";

		$prepareSQL = $connect->prepare($sql);
		$prepareSQL->bindParam(':username', $username,PDO::PARAM_STR);
		$prepareSQL->bindParam(':password', $password, PDO::PARAM_STR);
		$prepareSQL->bindParam(':dns', $dns, PDO::PARAM_STR);

		if ($prepareSQL->execute()) {
			$alert_success = "Compte crée avec succès";
		} else {
			$alert_error = "Une erreur est survenue merci de réessayer";
		}
    	}
}

if (!$_SESSION['user']) {
        $sql = "SELECT id, username FROM users";
        $prepareSQL = $connect->prepare($sql);
        $prepareSQL->execute();
        $users = $prepareSQL->fetchAll(PDO::FETCH_ASSOC);
}

?>


<!DOCTYPE html>
<html>
<head>
<title>Megaserver-j - accueil</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
</head>
<body class="w-75 m-auto">

<h1 class="text-center">Megaserver-j V2</h1>
<p class="text-center lead">Le retour du site de référence !</p>

<?php if ($alert_success) { ?>n
    <div class="alert alert-success text-center">
       <strong><?= $alert_success ?></strong>
    </div>
<?php } ?>

<?php if ($alert_error) { ?>
    <div class="alert alert-danger text-center">
        <strong><?= $alert_error ?></strong>
    </div>
<?php } ?>

<?php if (!$_SESSION['user']) { ?>
    <div class="row">
        <div class="col">
            <h2>Créer un utilisateur</h2>
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
                <input type="submit" name="create-user" class="my-3 btn btn-primary" value="Envoyer">
            </form>
        </div>
        <div class="col">
		<h2>Se connecter</h2>
            <form method="post">
                <label for="users">Selectionner un utilisateur</label>
                <select class="form-control" name="users">
                    <?php
                    for ($i = 0; $i < count($users); $i++) {
                        echo "<option value='"  .$users[$i]['id'] . "'>" . $users[$i]['username'] . "</option>";
                    }
                    ?>
                </select>
		<input type="submit" name="connection-user" class="my-3 btn btn-primary" value="Se connecter">
            </form>
        </div>
    </div>

<?php } ?>

<?php if ($_SESSION['user']) { ?>

<h1>Dashboard <?= $_SESSION['user']['username'] ?></h1>

    <form class="text-end" method="post">
        <input type="submit" class="btn btn-danger" name="deconnection-user" value="Se déconnecter">
    </form>

<div class="border-top my-3">
	<div class="row">
        	<div class="col">
			<h2>Changer mon mot de passe</h2>
			<form method="post">
                        	<label for="newpassword">Nouveau mot de passe</label>
                        	<input type="password" name="newpassword" class="form-control" id="newpassword">
        			<input type="submit" class="my-3 btn btn-primary" value="Changer mon mot de passe">
			</form>
		</div>
		<div class="col">
        		<h2>Créer une nouvelle base de donnée</h2>
			<form method="post" id="form-bdd">
	                        <label for="newbdd">Nom de la base de données</label>
        	                <input type="text" name="newbdd" class="form-control" id="newbdd">
			        <input type="submit" class="my-3 btn btn-primary" value="Ajouter une base de donnée">
			</form>
		</div>
 	</div>
</div>

<h2>Envoyer un fichier</h2>
<form method="POST" enctype="multipart/form-data">
	<div class="mb-3">
	  <input class="form-control" type="file" id="formFile" name="file">
	</div>
	<input type="submit" class="my-3 btn btn-primary" name="file" value="Envoyer">
</form>

<div class="border-top my-3">
	<h2>Télécharger mes backups</h2>
		<form method="post" id="form-backups">
		        <input type="submit" class="my-3 btn btn-primary" name="backups-folder" value="Backups de mes fichiers">
			<input type="submit" class="my-3 btn btn-primary" name="backups-bdd" value="Backups de ma base de données">
		</form>
</div>

<p class="read">Espace disque utilisé : <?= shell_exec("du -a -h /home/" . $_SESSION['user']['username'] ." | sort -hr | awk '{print $1}' | head -1"); ?></p>

<div class="text-center">
        <h2>Dashboard</h2>
        <div class="row">
                <?php for ($i = 0; $i < count($_SESSION['usage']); $i++) { ?>

                <div class="col">
                        <div class="card">
                                <div class="card-header">
                                        Usage <?= $_SESSION['usage'][$i]['title'] ?>
                                </div>
                                <div class="card-body">
                                        <?= $_SESSION['usage'][$i]['content'] ?>
                                </div>
                        </div>
                </div>
                <?php } ?>
        </div>
</div>

<?php } ?>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
</html>