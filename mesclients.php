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
        header('Location:index.php');
    }

// Mode Debug
	$debug = false;

// Sécurisation POST & GET
//    foreach ($_GET as $key => $value) {
//        $sGET[$key]=htmlentities($value, ENT_QUOTES);
//    }
//    foreach ($_POST as $key => $value) {
//        $sPOST[$key]=htmlentities($value, ENT_QUOTES);
//    }
        	
// Récupération des variables de session d'Authent
    $user_id = $_SESSION['authent']['id']; 
    $nom = $_SESSION['authent']['nom'];
    $prenom = $_SESSION['authent']['prenom'];
    $nom = $_SESSION['authent']['nom'];

// Récupération des variables de session exercice
    $exercice_id = null;
    $exercice_annee = null;
    $exercice_mois = null;
    $exercice_treso = null;
    if(isset($_SESSION['exercice'])) {
        $exercice_id = $_SESSION['exercice']['id'];
        $exercice_annee = $_SESSION['exercice']['annee'];
        $exercice_mois = $_SESSION['exercice']['mois'];
        $exercice_treso = $_SESSION['exercice']['treso'];
    }

// Récupération des variables de session abodep
    $abodep_mois = null;
    if(isset($_SESSION['abodep'])) {
        $abodep_mois = $_SESSION['abodep']['mois'];
    }

// Lecture du POST de selection
	$affiche_modal_email = false;
    if (isset($_POST['selection']) ) { // J'ai un POST de selection
    	extract($_POST);
        $_SESSION['selection'] = $selection;
		$affiche_modal_email = true;
    } //else {
    	//$_SESSION['selection'] = null;
    //}

// Lecture du POST de selection
    if (isset($_POST['emailok']) && $_POST['emailok'] == 1 ) { // J'ai un POST de confirmation d'envoi d'email
        header('Location:client_envoiemail.php');
	}    
    
// Initialisation de la base
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
// Lecture BDD

	// Requette vérifier qu'il y a des paiements en base			
	$sql = "SELECT * FROM client WHERE
    		user_id = :userid
    		";
    $q = array('userid' => $user_id);	
    
	$req = $pdo->prepare($sql);
	$req->execute($q);
	$data = $req->fetchAll(PDO::FETCH_ASSOC);
    $count = $req->rowCount($sql);
	
	if ($count==0) { // Il n'y a rien en base sur l'année
        $affiche = false;         
    } else {
        // On affiche le tableau
        $affiche = true;
    }
	Database::disconnect();		
	
	// Converti le tableau $data en tableau indexé sur id $data2
	$data2=array();
    for ($i=0; $i<count($data); $i++) {
        $data2["ID" . $data[$i]['id']] = array($data[$i]['prenom'],$data[$i]['nom'],$data[$i]['email']); 
	} // for	
	
?>

<!DOCTYPE html>
<html lang="fr">
<?php require 'head.php'; ?>

<body>

    <?php $page_courante = "mesclients.php"; require 'nav.php'; ?>
        
    <div class="container">
        <h2>Gestion de mes clients</h2>       
        <br>
		<p>
			<a href="client_create.php" class="btn btn-primary"><span class="glyphicon glyphicon-plus-sign"></span> Création d'un client</a>	
  			<a href="#" class="btn btn-primary"><span class="glyphicon glyphicon-list-alt"></span> Export Excel</a>
  			<a href="#" class="btn btn-primary"><span class="glyphicon glyphicon-briefcase"></span> Export PDF</a>
		</p>
				             
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
            DATA:<br>
            <pre><?php var_dump($data); ?></pre> 
            DATA2:<br>
            <pre><?php var_dump($data2); ?></pre>             
        </div>
        <?php       
        }   
        ?> 

		<!-- Affiche la table en base sous condition -->
		<div class="span10">
			<?php 
 			if ($affiche) {
			?>
			<form class="form-horizontal" action="mesclients.php" method="post">			
				<table class="table table-striped table-bordered table-hover success">
		              <thead>
		                <tr>
						  <th><span class="glyphicon glyphicon-ok-sign"></span></th>		                	
						  <th>Prénom</th>
		                  <th>Nom</th>
                          <th>eMail</th>
		                  <th>Téléphone Fixe</th>
		                  <th>Téléphone Mobile</th>
		                  <th>Age</th>
		                  <th>Actions</th>
		                </tr>
		              </thead>
		              <tbody>
		              <?php	
	 				  foreach ($data as $row) {
					  ?>		
						<tr>
							<td width=30>			
								<label class="checkbox-inline">
							    	<input name="selection[]" type="checkbox" value="<?php echo $row['id']; ?>" >
							  	</label>
							</td>					
					  <?php 							   
							echo '<td>'. ucfirst($row['prenom']) . '</td>';
						   	echo '<td>'. ucfirst($row['nom']) . '</td>';
							echo '<td>'. $row['email'] . '</td>';
							echo '<td>'. $row['telephone'] . '</td>';
							echo '<td>'. $row['mobile'] . '</td>';
							echo '<td>'. $row['age'] . '</td>';
						   	echo '<td width=130>';
					  ?>	
							<div class="btn-group btn-group-sm">
								  	<a href="client_details.php?id=<?php echo $row['id']; ?>" class="btn btn-default btn-sm btn-info glyphicon glyphicon-star" role="button"> </a>
								  	<a href="client_update.php?id=<?php echo $row['id']; ?>" class="btn btn-default btn-sm btn-warning glyphicon glyphicon-edit" role="button"> </a>
                                    <!-- Le bonton Delete active la modal et modifie le champ value à la volée pour passer l'ID a supprimer en POST -->
                                    <a href="#" id="<?php echo $row['id']; ?>"
                                       onclick="$('#DeleteInput').val('<?php echo $row['id']; ?>'); $('#modalDelete').modal('show'); "
                                       class="btn btn-default btn-sm btn-danger glyphicon glyphicon-trash" role="button"> </a>									  	
							</div>
							
						   	</td>						
						</tr>
					  <?php								                             
					  } // Foreach
					  ?>
				      </tbody>
	            </table>
			</div> 	<!-- /row -->

		    <!-- Bouton de Submit de selection -->
		  	<button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-list-alt"></span> Envoi d'un eMail de relance</button>				
			</form>

            <!-- Modal Delete -->
            <div class="modal fade" id="modalDelete" tabindex="-1" role="dialog" aria-labelledby="DeleteModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                    <form class="form-horizontal" action="client_delete.php" method="post">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h3 class="modal-title" id="DeleteModalLabel">Suppression d'un client :</h3>
                      </div><!-- /.modal-header -->
                      <div class="modal-body">
                          <strong>
                           <p class="alert alert-danger">Confirmez-vous la suppression ?</p>
                           <input id="DeleteInput" type="hidden" name="id" value=""/>
                          </strong>
                      </div><!-- /.modal-body -->                                         
                      <div class="modal-footer">
                        <div class="form-actions">                              
                            <button type="submit" class="btn btn-danger pull-right"><span class="glyphicon glyphicon-trash"></span> Suppression</button>
                            <button type="button" class="btn btn-primary pull-left" data-dismiss="modal"><span class="glyphicon glyphicon-chevron-up"></span> Retour</button>                                  
                        </div>
                      </div><!-- /.modal-footer -->
                    </form>                   
                </div><!-- /.modal-content -->
              </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->

            <!-- Modal eMail -->
            <div class="modal fade" id="modalEmail" tabindex="-1" role="dialog" aria-labelledby="EmailModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                    <form class="form-horizontal" action="mesclients.php" method="post">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h3 class="modal-title" id="EmailModalLabel">Envoi d'un eMail de relance :</h3>
                      </div><!-- /.modal-header -->
                      <div class="modal-body">
					  <div class="panel panel-success">
						  <div class="panel-heading">
						    <h3 class="panel-title">Liste des client à relancer</h3>
						  </div>
						  <div class="panel-body">
						  	<table class="table table-condensed table-bordered">
	                      	<?php
						    for ($i=0; $i<count($selection); $i++) {
								echo '<tr>';
						        echo '<p><td>' . $data2['ID'.$selection[$i]][0] . '</td><td>' . $data2['ID'.$selection[$i]][1] . '</td><td>' . $data2['ID'.$selection[$i]][2] . '</td></p>';
								echo '</tr>';
							} // for
							?>
							</table>                      	
						  </div>
					  </div>					        
                      <strong>
                       <p class="alert alert-warning">Confirmez-vous l'envoi ?</p>
                       <input id="emailok" type="hidden" name="emailok" value="1"/>
                      </strong>
                      </div><!-- /.modal-body -->                                         
                      <div class="modal-footer">
                        <div class="form-actions">                              
                            <button type="submit" class="btn btn-danger pull-right"><span class="glyphicon glyphicon-envelope"></span> Envoyer</button>
                            <button type="button" class="btn btn-primary pull-left" data-dismiss="modal"><span class="glyphicon glyphicon-chevron-up"></span> Retour</button>                                  
                        </div>
                      </div><!-- /.modal-footer -->
                    </form>                   
                </div><!-- /.modal-content -->
              </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->            
            
			<?php 	
			} // if
			?>
        </div>  <!-- /span -->        	        	             
    
    </div> <!-- /container -->

    <?php require 'footer.php'; ?>
    
	<?php 
	if ($affiche_modal_email) { // Modal conditionné par PHP
	?>	
	    <script>
		    $(document).ready(function(){ // Le DOM est chargé
				$('#modalEmail').modal('show');	
			});
		</script>
	<?php	 									
	} // endif
	?>      
  </body>
</html>