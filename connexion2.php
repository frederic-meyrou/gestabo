<!-- 
© Copyright : Aboo / www.aboo.fr : Frédéric MEYROU : tous droits réservés
-->
<?php
	session_start();

	// Dépendances
	include 'database.php';

	// Sécurisation POST & GET
    foreach ($_GET as $key => $value) {
        $sGET[$key]=htmlentities($value, ENT_QUOTES);
    }
    foreach ($_POST as $key => $value) {
        $sPOST[$key]=htmlentities($value, ENT_QUOTES);
    }

	// Mode Debug
	$debug = true;
	
	// Init BDD
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);        

	$email = null;
	$password = null;
    // Le Formulaire est rempli
	if (isset($sPOST['email']) && isset($sPOST['password'])) {
	    $email = $sPOST['email'];
	    $password = $sPOST['password'];
	    // Lecture dans la base
	    $sql = "SELECT * FROM user WHERE email = ? AND password = ?";
	    $q = $pdo->prepare($sql);
	    $q->execute(array($email,$password));
	    $data = $q->fetch(PDO::FETCH_ASSOC);
	    $count = $q->rowCount($sql);

	    if ($count==1) {
	        // On a bien l'utilisateur dans la base, on charge ses infos dans la session      
	        $_SESSION['authent'] = array(
	            'id' => $data['id'],
	            'email' => $email,
	            'password' => $password,
	            'nom' => $data['nom'],
	            'prenom' => $data['prenom'],
	            'expiration' => $data['expiration'],
	            'admin' => $data['administrateur']
	            );        
	        if ($_SESSION['authent']['admin']==1) {
		        // Cas ou l'utilisateur est Admin, redirection vers page admin
				Database::disconnect();     	
		        header('Location:admin/user.php');            
	        } else {
		        // On charge les infos de session mois en cours si déjà enregistré
				if ($data['mois_encours'] != null) {
					$_SESSION['abodep']['mois'] = $data['mois_encours'];
				}	        	         	
		        // On charge les infos exercice de session si déjà enregistré
				if ($data['exerciceid_encours'] != null) {
			        $sql2 = "SELECT * FROM exercice where id = ?";
			        $q = $pdo->prepare($sql2);
			        $q->execute(array($data['exerciceid_encours']));
			        $data2 = $q->fetch(PDO::FETCH_ASSOC);
					$_SESSION['exercice'] = array(
		                'id' => $data['exerciceid_encours'],
		                'annee' => $data2['annee_debut'],
		                'mois' => $data2['mois_debut'],
		                'treso' => $data2['montant_treso_initial']
	                );         
				}	
				Database::disconnect();     	
		        // Redirection vers la home sécurisé            
		        header('Location:home.php');
	        }
	    } else {
	        //Utilisateur inconnu
	        $error_unknown = "Compte $email inconnu ou mot de passe invalide!";
	    }  
	}
?>

<!DOCTYPE html>
<html lang="fr">
<?php require 'head.php'; ?>

<body>
    
    <script src="bootstrap/js/jquery-2.0.3.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>

    <!-- Affiche la navigation -->
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">      
      <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <!-- Marque -->
          <a class="navbar-brand" href="aboo.php">Aboo</a>
      </div>     
      <!-- Liens -->
      <div class="collapse navbar-collapse" id="TOP">
        <ul class="nav navbar-nav">
          <li><a href="connexion.php"><span class="glyphicon glyphicon-off"></span> Connexion</a></li>
          <li class="active" data-toggle="modal" data-target="#myModal"><a href="connexion2.php"><span class="glyphicon glyphicon-off"></span> Connexion 2</a></li>   
          <li><a href="deconnexion.php"><span class="glyphicon glyphicon-off"></span> De-connexion</a></li>                                     
        </ul>
      </div><!-- /.navbar-collapse -->
    </nav>    
    
    <div class="container">

        <!-- Affiche les informations de debug -->
        <?php 
 		if ($debug) {
		?>
		<div class="span10 offset1">
        <div class="alert alert-danger alert-dismissable fade in">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <strong>Informations de Debug : </strong><br>
            POST:<br>
            <pre><?php var_dump($_POST); ?></pre>
            GET:<br>
            <pre><?php var_dump($_GET); ?></pre>
        </div>
       </div>
        <?php       
        }   
        ?>  
        
			<!-- Modal Login-->
			<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			  <div class="modal-dialog">
			    <div class="modal-content">
			      <div class="modal-header">
			        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			        <h4 class="modal-title" id="myModalLabel">Aboo</h4>
			      </div><!-- /.modal-header -->
			      <div class="modal-body">
			      	<form id="login" action="connexion2.php" method="POST">
			        	<h2>Connectez-vous!</h2>
				        <input name="email" id="email" type="email" class="form-control" value="<?php if(!empty($email)){ echo $email; } ?>" placeholder="Email" required autofocus><br/>
				        <input name="password" id="password" type="password" class="form-control" value="" value="<?php if(!empty($password)){ echo $password; } ?>" placeholder="Mot de passe" required>
				           <div class="error"><?php if(isset($error_unknown)){ echo $error_unknown; } ?></div>
		      		</form>
		      		<script type="text/javascript">
						document.getElementById('email').focus(); 
					</script>
			      </div><!-- /.modal-body -->
			      <hr>
			      <div class="modal-body">		      
			          <h4> 
			           	<span class="glyphicon glyphicon-user"></span> <a href="inscription.php"> Vous souhaitez créer un compte ?</a><br/>
					    <span class="glyphicon glyphicon-lock"></span> <a href="oubli.php"> Vous avez oublié votre mot de passe ?</a>
					  </h4>
			      </div><!-- /.modal-body -->					    
				  <div class="modal-footer">
			        <button type="button" class="btn btn-lg btn-success" data-dismiss="modal"><span class="glyphicon glyphicon-log-in"></span> Connexion</button>
			        <button type="button" class="btn btn-lg btn-primary" data-dismiss="modal"><span class="glyphicon glyphicon-chevron-up"></span> Retour</button>
			      </div><!-- /.modal-footer -->
			    </div><!-- /.modal-content -->
			  </div><!-- /.modal-dialog -->

			</div><!-- /.modal -->
      
        <!-- Affiche les informations de debug -->
        <?php 
 		if ($debug) {
		?>
		<div class="span10 offset1">
        <div class="alert alert-danger alert-dismissable fade in">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <strong>Informations de Debug : </strong><br>
            POST:<br>
            <pre><?php var_dump($_POST); ?></pre>
            GET:<br>
            <pre><?php var_dump($_GET); ?></pre>
            email:<br>
            <pre><?php var_dump($email); ?></pre>
            password:<br>
            <pre><?php var_dump($password); ?></pre>
            
            
        </div>
       </div>
        <?php       
        }   
        ?>        
      
      <?php if(isset($error_unknown)){ ?> 
      <div class="alert alert alert-fail alert-dismissable fade in">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <strong><?php echo "$error_unknown" ?>.</strong>
      </div>
      <?php  
      } ?>
      
      <!--<form class="form-signin" action="connexion.php" method="post">
        <h2 class="form-signin-heading">Connectez-vous</h2>

        <input name="email" type="text" class="form-control" placeholder="Email" required autofocus>
        <input name="password" type="password" class="form-control" placeholder="Mot de passe" required>
           <div class="error"><?php if(isset($error_unknown)){ echo $error_unknown; } ?></div>

        <button class="btn btn-lg btn-primary btn-block" type="submit">Connexion</button>
      </form>-->
   

    </div> <!-- /container -->
</body>
</html>