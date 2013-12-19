<?php
// Vérification de l'Authent
    session_start();
    require('authent.php');
    if( !Authent::islogged()){
        // Non authentifié on repart sur la HP
        header('Location:index.php');
    }

// Dépendances
	require_once('fonctions.php');

// Mode Debug
	$debug = false;

// Sécurisation POST & GET
    foreach ($_GET as $key => $value) {
        $sGET[$key]=htmlentities($value, ENT_QUOTES);
    }
    foreach ($_POST as $key => $value) {
        $sPOST[$key]=htmlentities($value, ENT_QUOTES);
    }
        	
// Récupération des variables de session d'Authent
    $user_id = $_SESSION['authent']['id']; 
    $nom = $_SESSION['authent']['nom'];
    $prenom = $_SESSION['authent']['prenom'];
    $nom = $_SESSION['authent']['nom'];

// Récupération des variables de session exercice
    if(isset($_SESSION['exercice'])) {
        $exercice_id = $_SESSION['exercice']['id'];
        $exercice_annee = $_SESSION['exercice']['annee'];
        $exercice_mois = $_SESSION['exercice']['mois'];
        $exercice_treso = $_SESSION['exercice']['treso'];	
    } else { // On a pas de session on retourne vers la gestion d'exercice
    	header("Location: conf.php");    	
    }

// Récupération des variables de session abodep
    $abodep_mois = null;
    if(!empty($_SESSION['abodep']['mois'])) {
        $abodep_mois = $_SESSION['abodep']['mois'];
    } else { // On a pas de session avec le mois on retourne d'ou on vient
    	header("Location: abodep.php");
    }
	
// Initialisation de la base
    include_once 'database.php';
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
// Lecture du POST (Choix du mois)
    $montant = null;
	$commentaire = null;
	$montantError = null;	
    if (isset($sPOST['montant']) ) { // J'ai un POST
        $montant = $sPOST['montant'];
		$commentaire = $sPOST['commentaire'];
		$type = $sPOST['type'];    
		
		// validate input
		$valid = true;
		
		if (empty($montant) || $montant < 0 || $montant == null) {
			$montantError= "Veuillez entrer un montant positif.";
			$valid = false;
		}

		// insert data
		if ($valid) {
			$sql = "INSERT INTO depense (user_id,exercice_id,type,montant,mois,commentaire) values(?, ?, ?, ?, ?, ?)";
			$q = $pdo->prepare($sql);		
			$q->execute(array($user_id, $exercice_id, $type, $montant, $abodep_mois, $commentaire));
		}
		
		// Réinitialise pour le formulaire		
		header("Location: dep.php");
    } // If POST
	
	
// Lecture dans la base des depenses (sur user_id et exercice_id et mois) 
    $sql = "SELECT * FROM depense WHERE
    		(user_id = :userid AND exercice_id = :exerciceid AND mois = :mois)
    		";
// Requette pour calcul de la somme	
    $sql2 = "SELECT SUM(montant) FROM depense WHERE
    		(user_id = :userid AND exercice_id = :exerciceid AND mois = :mois)
    		";	
    $q = array('userid' => $user_id, 'exerciceid' => $exercice_id, 'mois' => $abodep_mois);
    $req = $pdo->prepare($sql);
    $req->execute($q);
    $data = $req->fetchAll(PDO::FETCH_ASSOC);
	$req = $pdo->prepare($sql2);
    $req->execute($q);
    $data2 = $req->fetch(PDO::FETCH_ASSOC);
    $count = $req->rowCount($sql);
    if ($count==0) { // Il n'y a rien à afficher
        $affiche = false;              
    } else {   
    		// Calcul des sommes
    		$total_depenses= !empty($data2["SUM(montant)"]) ? $data2["SUM(montant)"] : 0;
	        // On affiche le tableau
	        $affiche = true;
    }
	Database::disconnect();
	$infos = true;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Aboo</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" media="screen">
    <link href="bootstrap/css/aboo.css" rel="stylesheet">
    <link rel='stylesheet' id='google_fonts-css'  href='http://fonts.googleapis.com/css?family=PT+Sans|Lato:300,400|Lobster|Quicksand' type='text/css' media='all' />
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>

<body>
    
    <script src="bootstrap/js/jquery-2.0.3.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>

    <!-- Affiche la navigation -->
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">      
      <!-- Marque -->
      <div class="navbar-header">
        <a class="navbar-brand" href="home.php">Aboo</a>
      </div>     
      <!-- Liens -->
      <div class="collapse navbar-collapse" id="TOP">
        <ul class="nav navbar-nav">
          <li class="active"><a href="abodep.php">Recettes & Dépenses</a></li>
          <li><a href="meusuel.php">Bilan Mensuel</a></li>
          <li><a href="bilan.php">Bilan Annuel</a></li>
          <li><a href="encaissements.php">Encaissements</a></li>
          <li><a href="paiements.php">Paiements</a></li>
          <li><a href="conf.php">Configuration</a></li>
          <li><a href="deconnexion.php">Deconnexion</a></li>                    
           <!--<li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Menu Dropdown <b class="caret"></b></a>
            <ul class="dropdown-menu">
              <li><a href="#">Action</a></li>
              <li class="divider"></li>
              <li><a href="#">Action</a></li>
            </ul>
          </li>-->
        </ul>
          <!-- Affiche le nom de l'utilisateur à droite de la barre de Menu -->
          <p class="navbar-text navbar-right"><span class="glyphicon glyphicon-user"></span> <?php echo ucfirst($prenom) . ' ' . ucfirst($nom); ?></p>
      </div><!-- /.navbar-collapse -->
    </nav>
        
    <div class="container">
        <h2>Affichage et Modification des Dépenses</h2>
        <br>
                      
        <!-- Affiche les informations de debug -->
        <?php 
 		if ($debug) {
		?>
		<div class="span10 offset1">
        <div class="alert alert alert-danger alert-dismissable fade in">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <strong>Informations de Debug : </strong><br>
            SESSION:<br>
            <pre><?php var_dump($_SESSION); ?></pre>
            POST:<br>
            <pre><?php var_dump($_POST); ?></pre>
            GET:<br>
            <pre><?php var_dump($_GET); ?></pre>
        </div>
       </div>
        <?php       
        }   
        ?>  
        
		<!-- Affiche la table des dépenses en base sous condition -->
		<div class="span10">
			<?php 
 			if ($affiche) {
			?>
            <div class="row">
                <h3>Liste des dépenses du mois courant : <button type="button" class="btn btn-info"><?php echo NumToMois($abodep_mois); ?></button></h3>
		
			<table class="table table-striped table-bordered table-hover success">
				<thead>
					<tr>
					  <th>Type</th>
  					  <th>Montant</th>
					  <th>Commentaire</th>
					  <th>Action</th>					  			  
					</tr>
				</thead>
                
				<tbody>
				<?php		 
					foreach ($data as $row) {
						echo '<tr>';
						echo '<td>' . NumToTypeDepense($row['type']) . '</td>';
						echo '<td>' . $row['montant'] . ' €</td>';
						echo '<td>' . $row['commentaire'] . '</td>';
					   	echo '<td width=90>';
				?>		
						<div class="btn-group btn-group-sm">
							  	<a href="dep_update.php?id=<?php echo $row['id']; ?>" class="btn btn-default btn-sm btn-warning glyphicon glyphicon-edit" role="button"> </a>
							  	<a href="dep_delete.php?id=<?php echo $row['id']; ?>" class="btn btn-default btn-sm btn-danger glyphicon glyphicon-trash" role="button"> </a>
						</div>

					   	</td>						
						</tr>
			<?php
				   } // Foreach	
			} // If Affiche
			?>
                </tbody>
            </table>
            <!-- Affiche les sommmes -->        
			<p>
				<button type="button" class="btn btn-info">Total dépenses : <?php echo $total_depenses; ?> €</button>
			</p>             
			</div> 	<!-- /row -->

		<!-- Affiche le formulaire inline ajout depense -->			
            <div class="row">
                <h3>Ajout d'une dépense :</h3>
	            <form class="form-inline" role="form" action="dep.php" method="post">
		            <?php function Affiche_Champ(&$champ, &$champError, $champinputname, $champplaceholder, $type) { ?>
		            		<div class="form-group <?php echo !empty($champError)?'has-error':'';?>">
		                    	<input name="<?php echo "$champinputname" ?>" id="<?php echo "$champinputname" ?>" type="<?php echo "$type" ?>" class="form-control" value="<?php echo !empty($champ)?$champ:'';?>" placeholder="<?php echo "$champplaceholder" ?>">		            
		                    <?php if (!empty($champError)): ?>
		                     		<span class="help-inline"><?php echo $champError;?></span>
		                    <?php endif; ?>		            
		       				</div>
		            <?php } ?>
		            <div class="form-group">
		                    <select name="type" class="form-control">
				            <?php
				                foreach ($Liste_Depense as $d) {
				            ?>
				                <option value="<?php echo TypeDepenseToNum($d);?>"><?php echo $d;?></option>    
				            <?php 
				                } // foreach   
				            ?>
		                    </select>
		            </div>		      		            
		       		<?php Affiche_Champ($montant, $montantError, 'montant','Montant €', 'text' ); ?>
		       		<?php Affiche_Champ($commentaire, $commentaireError, 'commentaire','Commentaire', 'text' ); ?>

	              	<button type="submit" class="btn btn-success btn-sm">Ajout</button>
	            </form>
            </div> 	<!-- /row -->		
            <script type="text/javascript">
				document.getElementById('montant').focus();
			</script>
			<!-- Affiche le bouton retour -->
			<br>        
			<p>
				<a href="abodep.php" class="btn btn-success">Retour</a>
			</p>
			
        </div>  <!-- /span -->        			
    
    </div> <!-- /container -->
  </body>
</html>