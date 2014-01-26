<!-- 
© Copyright : Aboo / www.aboo.fr : Frédéric MEYROU : tous droits réservés
-->
<?php
// Dépendances
    require_once('lib/fonctions.php');
    include_once('lib/database.php');
    include_once('lib/calcul_bilan.php');

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


// Calcul du Bilan
    $TableauBilanMensuel = CalculBilanMensuel($user_id, $exercice_id, $exercice_treso);
    $TableauBilanAnnuel = CalculBilanAnnuel($user_id, $exercice_id, $TableauBilanMensuel);        
    
    if ($TableauBilanAnnuel==null) { // Il n'y a rien en base sur l'année (pas de dépenses et pas de recettes)
        $affiche = false;         
    } else {
            // On affiche le tableau
            $affiche = true;
            $infos = true;
    }

?>

<!DOCTYPE html>
<html lang="fr">
<?php require 'head.php'; ?>

<body>

    <?php $page_courante = "bilan.php"; require 'nav.php'; ?>
        
    <div class="container">
     
       
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
            Tableau:<br>            
            <pre><?php var_dump($TableauBilanMensuel); ?></pre>
            <br>
            Tableau:<br>            
            <pre><?php var_dump($TableauBilanAnnuel); ?></pre>
            <br>                                                      
        </div>
        <?php       
        }   
        ?> 

        <!-- Affiche les 12 Mois -->
        <div class="page-header">          
            <h2>Tableau de bord : <button type="button" class="btn btn-lg btn-info"><?php echo "$exercice_annee - " . ($exercice_annee +1); ?></button></h2>  
        </div>
        
        <?php    
        function AffichePanel($mois_relatif) {
            global $exercice_treso;
            global $exercice_mois;          
            global $TableauBilanMensuel;
                       
            $num_mois = MoisAnnee($mois_relatif, $exercice_mois);
        ?>
              <div class="panel panel-success">
                <div class="panel-heading">
                  <h3 class="panel-title"><?php echo $mois_relatif . ' : ' .  NumToMois($num_mois); ?></h3>
                </div>
                <div class="panel-body">
                    <li>CA : <?php echo number_format($TableauBilanMensuel[$mois_relatif]['CA'],2,',','.') . ' €'; ?></li>
                    <li>Dépenses : <?php echo number_format($TableauBilanMensuel[$mois_relatif]['DEPENSE'],2,',','.') . ' €'; ?></li>
                    <li>Solde brut : <?php echo number_format($TableauBilanMensuel[$mois_relatif]['SOLDE'],2,',','.') . ' €'; ?></li> 
                    <li>Ventilation : <?php echo number_format($TableauBilanMensuel[$mois_relatif]['VENTIL'],2,',','.') . ' €'; ?></li>                   
                    <li>Salaire : <?php echo number_format($TableauBilanMensuel[$mois_relatif]['SALAIRE'],2,',','.') . ' €'; ?></li>
                    <li>Report Salaire : <?php echo number_format($TableauBilanMensuel[$mois_relatif]['REPORT_SALAIRE'],2,',','.') . ' €'; ?></li>
                    <li>Tréso avant salaire : <?php echo number_format($TableauBilanMensuel[$mois_relatif]['TRESO'],2,',','.') . ' €'; ?></li>
                    <li>A trésoriser : <?php echo number_format($TableauBilanMensuel[$mois_relatif]['REPORT_TRESO'],2,',','.') . ' €'; ?></li>
                    <li>Encaissement : <?php echo number_format($TableauBilanMensuel[$mois_relatif]['ENCAISSEMENT'],2,',','.') . ' €'; ?></li>                    
                    <li>Paiements : <?php echo number_format($TableauBilanMensuel[$mois_relatif]['PAIEMENT'],2,',','.') . ' €'; ?></li>
                    <li>Paiements échus : <?php echo number_format($TableauBilanMensuel[$mois_relatif]['ECHUS'],2,',','.') . ' €'; ?></li>
                </div>
              </div>
        <?php    
        }   
        ?> 

        <div class="row">
            <div class="col-sm-4">
              <!-- Mois 1 -->  
              <?php AffichePanel(1); ?>
              <!-- Mois 4 -->
              <?php AffichePanel(4); ?>
              <!-- Mois 7 -->
              <?php AffichePanel(7); ?>
              <!-- Mois 10 -->
              <?php AffichePanel(10); ?>
            </div><!-- /.col-sm-4 -->
            
            <div class="col-sm-4">
              <!-- Mois 2 -->  
              <?php AffichePanel(2); ?>
              <!-- Mois 5 -->
              <?php AffichePanel(5); ?>
              <!-- Mois 8 -->
              <?php AffichePanel(8); ?>
              <!-- Mois 11 -->
              <?php AffichePanel(11); ?>
            </div><!-- /.col-sm-4 -->
            
            <div class="col-sm-4">
              <!-- Mois 3 -->  
              <?php AffichePanel(3); ?>
              <!-- Mois 6 -->
              <?php AffichePanel(6); ?>
              <!-- Mois 9 -->
              <?php AffichePanel(9); ?>
              <!-- Mois 12 -->
              <?php AffichePanel(12); ?>
            </div><!-- /.col-sm-4 -->
        </div>
        <hr>

        <!-- Affiche la table en base sous condition -->
        <div class="span10">
            <?php 
            if ($affiche) {
            ?>
            <!-- Affiche les sommmes -->        
            <p>
                <button type="button" class="btn btn-primary">Exercice : <?php echo "$exercice_annee - " . ($exercice_annee +1); ?></button>
                <button type="button" class="btn btn-info">Total dépenses : <?php echo number_format($TableauBilanAnnuel['DEPENSE'],2,',','.'); ?> €</button>
                <button type="button" class="btn btn-info">Total recettes : <?php echo number_format($TableauBilanAnnuel['CA'],2,',','.'); ?> €</button>
                <button type="button" class="btn btn-info">Solde brut : <?php echo number_format($TableauBilanAnnuel['SOLDE'],2,',','.'); ?> €</button>    
                <button type="button" class="btn btn-info">Total encaissements : <?php echo number_format($TableauBilanAnnuel['ENCAISSEMENT'],2,',','.'); ?> €</button>
                <button type="button" class="btn btn-info">Total echus : <?php echo number_format($TableauBilanAnnuel['ECHUS'],2,',','.'); ?> €</button>   
                <button type="button" class="btn btn-info">Total paiements : <?php echo number_format($TableauBilanAnnuel['PAIEMENT'],2,',','.'); ?> €</button>                                                  
                <button type="button" class="btn btn-info">Salaire : <?php echo number_format($TableauBilanAnnuel['SALAIRE'],2,',','.'); ?> €</button>   
                <button type="button" class="btn btn-info">Ventillation : <?php echo number_format($TableauBilanAnnuel['VENTIL'],2,',','.'); ?> €</button>   
                <button type="button" class="btn btn-info">Dernier report Salaire : <?php echo number_format($TableauBilanAnnuel['REPORT_SALAIRE'],2,',','.'); ?> €</button>    
                <button type="button" class="btn btn-info">Trésorerie finale : <?php echo number_format($TableauBilanAnnuel['REPORT_TRESO'],2,',','.'); ?> €</button>   
            </p>            
                      
            </div>  <!-- /row -->
            <?php   
            } // if
            ?>
        </div>  <!-- /span -->                  
             
    </div> <!-- /container -->

    <?php require 'footer.php'; ?>
        
  </body>
</html>