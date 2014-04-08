<!-- 
© Copyright : Aboo / www.aboo.fr : Frédéric MEYROU : tous droits réservés
-->
<?php
// Dépendances
	require_once('lib/fonctions.php');
    include_once('lib/database.php');

// Vérification de l'Authent
    session_start();
    require('lib/authent.php');
    if( !Authent::islogged()){
        // Non authentifié on repart sur la HP
        header('Location:../index.php');
    }

// Récupération des variables de session
	include_once('lib/var_session.php');
		
// Mode Debug
	$debug = false;

// Sécurisation POST & GET
    foreach ($_GET as $key => $value) {
        $sGET[$key]=htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    foreach ($_POST as $key => $value) {
        $sPOST[$key]=htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
	
// Initialisation de la base
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
// Vérification du GET
    $id = null;
    if ( !empty($sGET['id'])) {
        $id = $sGET['id'];
    }   
        
// Lecture et validation du POST
    if ( !empty($sPOST)) {

        // Init base
        $pdo = Database::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // keep track validation errors
        $montantError = null;
		$commentaireError = null;
                        
        // keep track post values
        $id = $sPOST['id']; 
        $montant = $sPOST['montant']; 
        $commentaire = $sPOST['commentaire'];
		$type = $sPOST['type'];
		
		// validate input
		$valid = true;
		
		if (empty($montant) || $montant < 0 || $montant == null) {
			$montantError= "Veuillez entrer un montant positif.";
			$valid = false;
		}
           
        // Modif des données en base et redirection vers appelant
        if ($valid) {
            $sql = "UPDATE depense SET montant=?,commentaire=?, type=? WHERE id = ?";
            $q = $pdo->prepare($sql);
            $q->execute(array($montant, $commentaire, $type, $id));
            Database::disconnect();        
            header("Location: depense.php");
        }       
    } else {
        // Lecture des infos ds la base
        $pdo = Database::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT * FROM depense where id = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($id));
        $data = $q->fetch(PDO::FETCH_ASSOC);
        $id = $data['id'];   		
		$montant = $data['montant'];
        $commentaire = $data['commentaire'];
		$type = $data['type'];     
        Database::disconnect();                
    }
    
?>

<!DOCTYPE html>
<html lang="fr">
<?php require 'head.php'; ?>

<body>

    <?php $page_courante = "journal.php"; require 'nav.php'; ?>
        
    <div class="container">

        <div class="page-header">           
            <h2>Dépenses & Charges : Modification d'une dépense</h2>
        </div>
                
        <!-- Affiche les informations de debug -->
        <?php 
 		if ($debug) {
		?>
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
        <?php       
        }   
        ?>  
                
		<div class="row">
 			 <div class="col-md-4 col-md-offset-2">

		        <!-- Formulaire -->  
	            <form class="form-horizontal" action="depense_update.php" method="post">
	                
	                <button type="button" class="btn btn-info">Mois courant : <?php echo NumToMois($abodep_mois); ?></button>
      		            	            
		            <?php function Affiche_Champ(&$champ, &$champError, $champinputname, $champplaceholder, $type) { ?>
		            <div class="form-group <?php echo !empty($champError)?'has-error':'';?>">
		                <label class="control-label"><?php echo "$champplaceholder" ?></label>
		                <div class="controls">
		                    <input name="<?php echo "$champinputname" ?>" class="form-control" type="<?php echo "$type" ?>" value="<?php echo !empty($champ)?$champ:'';?>">
		                    <?php if (!empty($champError)): ?>
		                        <span class="help-inline"><?php echo $champError;?></span>
		                    <?php endif; ?>
		                </div>
		            </div>
		            <?php } ?>
		       		
		       		<input type="hidden" name="id" value="<?php echo $id; ?>">
		       		
		            <div class="form-group">
		            		<label class="control-label">Type</label>
		                    <select name="type" class="form-control">
				            <?php
				                foreach ($Liste_Depense as $d) {
				            ?>
				                <option value="<?php echo TypeDepenseToNum($d);?>"<?php echo (TypeDepenseToNum($d)==$type)?'selected':'';?>> 
				                	<?php echo $d;?>
				                </option>    
				            <?php
				                } // foreach   
				            ?>
		                    </select>
		            </div>	
        		    <?php Affiche_Champ($montant, $montantError, 'montant','Montant €', 'text' ); ?>
		       		<?php Affiche_Champ($commentaire, $commmentaireError, 'commentaire','Commentaire', 'text' ); ?>
		                                                
		            <div class="form-actions">
		              <button type="submit" class="btn btn-warning"><span class="glyphicon glyphicon-check"></span> Mise à jour</button>
		              <a class="btn btn-primary" href="depense.php"><span class="glyphicon glyphicon-eject"></span> Retour</a>
		            </div>
	            </form>
	            
	   		 </div> <!-- /col -->    			
	    </div> <!-- /row -->     			
    
    <?php require 'footer.php'; ?>    
    </div> <!-- /container -->

        
  </body>
</html>