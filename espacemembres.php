<?php 
include('./fonctions/session_start.php');
include('./fonctions/connexion_bdd.php');
include('./fonctions/fonctions_account.php');
include('./templates/header.php');
supprFichiersCaptcha();
//verifier que session ouverte
if (ok_login())
{
}
else
{
	header('Location:./account/connexion.php?deconnexion=1'); //on déconnecte cet intrus!
}

?>

<!-- afficher ici le bandeau de présentation de GBAF -->

<div class="presentation_gbaf">
	<?php
	if (isset($bdd))
	{
		$req = $bdd->prepare('SELECT presentation FROM identite WHERE id= :id') or die(print_r($bdd->errorInfo()));
		$req->execute(array(
				'id'=>1
			));
		$presentation = $req->fetch();
		echo '<p id="idprez">'.$presentation['presentation'].'</p>';
		$req->closeCursor();
	}
	?>
</div>
<div class="bandeau">
</div>

<?php
include('./templates/colonnes_deco_gauche.php');
?>
<div class="col-contenu" >
	<div class="frm radius">
		<?php
		if (isset($bdd))
		{
			$donnees = $bdd->query('SELECT id, acteur, SUBSTR(description,1,110) as description_courte, logo FROM acteurs ORDER BY acteur ASC') or die(print_r($bdd->errorInfo()));	
			if (!is_null($donnees))
			{
				while ($ligne = $donnees->fetch())
				{
				?>
					<div class="container">
						<div class="logoresume">
							<div class="logo_acteur200x100">
							<?php
							echo '<img src=logos/'.$ligne['logo'].'200x100.png alt="logo moyen"/>';
							?>	
							</div>
							<div class="logo_acteur100x50">
							<?php
							echo '<img src=logos/'.$ligne['logo'].'100x50.png alt="logo petit"/>';
							?>	
							</div>
							<div class="resume_acteur">
							<?php
							echo '<div><h3>'.($ligne['acteur']).'</h3></div><div><p>';
							$description = $ligne['description_courte'];
							echo $description;
							echo '<a href="./posts_votes/details.php?acteur='.$ligne['id'].'" class="reda">&emsp;...</a></p></div>';
							?>
							</div><!--resume_acteur-->
						</div> <!--logoresume-->
						<div class="liresuite">
							<div>&emsp;</div>
							<div>
								<form method="get" action="./posts_votes/details.php" >
								<?php 
								echo'<input type="hidden" name="acteur" value='.$ligne['id'].' /><button type="submit" class="readmore">Lire la suite</button></form><br />';
									?>
							</div>
						</div>
					</div>
				<?php					
				}
			}
		}
		?>	
	</div>
</div>
<?php
include('./templates/colonnes_deco_droite.php');
?>

<p>
	<a href="./fonctions/connexion.php?deconnexion=1" style="text-decoration:none;">&emsp;</a>
</p>

<?php
include('./templates/footer.php');
?>