<!-- 
© Copyright : Aboo / www.aboo.fr : Frédéric MEYROU : tous droits réservés
-->
<?php
	require_once('fonctions.php');
// Vérification de l'Authent
    session_start();
    require('authent.php');
    if( !Authent::islogged()){
        // Non authentifi� on repart sur la HP
        header('Location:index.php');
    }

// Récupère l'annee de l'exercice à supprimer en GET
	if ( !empty($_GET['annee'])) {
		$annee = $_REQUEST['annee'];
	} else {
		// Redirection vers conf puisque on a rien � afficher
		header('Location:conf.php');
	}
	
// Récupération des variables de session d'Authent
    $user_id = $_SESSION['authent']['id']; 
	
// Initialisation de la base
    include_once 'database.php';
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    

// Lecture dans la base de l'exercice 
    $sql = "SELECT * FROM exercice WHERE user_id = ? AND annee_debut = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($user_id,$annee));
    $data = $q->fetch(PDO::FETCH_ASSOC);
    $count = $q->rowCount($sql);
    if ($count==0) { // Pas d'exercice ds la BDD
        Database::disconnect();
        // Redirection
        header('Location:conf.php');                
    } else {
		$id = $data['id'];
		$mois = $data['mois_debut'];
		$treso = $data['montant_treso_initial'];
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
          <a class="navbar-brand" href="home.php">Aboo</a>
      </div>     
      <!-- Liens -->
      <div class="collapse navbar-collapse" id="TOP">
        <ul class="nav navbar-nav">
          <li><a href="journal.php"><span class="glyphicon glyphicon-th-list"></span> Recettes & Dépenses</a></li>
          <li><a href="bilan.php"><span class="glyphicon glyphicon-calendar"></span> Bilan</a></li>
          <li><a href="paiements.php"><span class="glyphicon glyphicon-euro"></span> Paiements</a></li>
          <li><a href="mesclients.php"><span class="glyphicon glyphicon-star"></span> Clients</a></li>                           
          <li class="dropdown">
	        <!-- Affiche le nom de l'utilisateur à droite de la barre de Menu -->
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></span> <?php echo ucfirst($prenom) . ' ' . ucfirst($nom); ?><b class="caret"></b></a>
            <ul class="dropdown-menu">
              <li class="active"><a href="conf.php"><span class="glyphicon glyphicon-wrench"></span> Configuration</a></li>
              <li><a href="deconnexion.php"><span class="glyphicon glyphicon-off"></span> Deconnexion</a></li>  
            </ul> 
          </li>
          <li><a href="deconnexion.php"><span class="glyphicon glyphicon-off"></span></a></li>      
        </ul>
      </div><!-- /.navbar-collapse -->
    </nav>
        
    <div class="container">

        <div class="span10 offset1">
            <div class="row">
                <h3>Consultation de l'exercice</h3>
            </div>    
            <div class="row">
               
                <table class="table table-striped table-bordered table-hover success">
                      <thead>
                        <tr>
						
                          <th>Années exercice</th>
                          <th>Mois de démarrage</th>
                          <th>Montant de trésorerie de départ</th>
                        </tr>
                      </thead>
                      <tbody>
                      <?php 
                                echo '<tr>';
                                echo '<td>'. $annee . ' - ' . ($annee + 1) . '</td>';
                                echo '<td>'. NumToMois($mois) . '</td>';
                                echo '<td>'. $treso . '</td>';
                                echo '</tr>';
                      ?>
                      </tbody>
                </table>
            </div> 	<!-- /row -->
 		  	<a class="btn btn-success" href="conf.php">Retour</a>
        </div>  <!-- /span -->        			

    </div> <!-- /container -->
  </body>
</html>