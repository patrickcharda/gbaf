<?php
include('./../fonctions/session_start.php');
include('./../fonctions/connexion_bdd.php');
include('./../fonctions/fonctions_account.php');
include('./../fonctions/fonctions_posts_votes.php');
include('./../templates/header.php');

/*
afficher le détail d'un acteur
*/


if (!ok_login())
{
	header('Location:../account/connexion.php?deconnexion=1'); //on déconnecte cet intrus!
}

?>

<main class="main">
	<div class="top_main">
		<div class="row">
			<div class="col--3"></div>
			<div class="col--18 white">
				<div class="row">
					<div class="col-1"><span class="invisible">&emsp;</span></div>
					<div class="col-10 frm radius">
<?php
if (isset($_GET['acteur']))
{
	$id= (int)($_GET['acteur']); //on verifie que l'argument passé en url est un chiffre...
	if ($id>0 && $id<1000) //un chiffre "raisonnable"
	{
		$_SESSION['id_acteur']=$id;
	}
	else
	{
		header('Location:./../espacemembres.php');
	}

}
if (isset($_SESSION['id_acteur']))
{
	if (isset($bdd))
	{
		//on vérifie que l'id existe
		$reponse = $bdd->query('SELECT count(*) FROM acteurs WHERE id='.$id.'') or die(print_r($bdd->errorInfo()));
		$data = $reponse->fetch();
		if ($data['count(*)'] == 0) //l'id n'existe pas, on renvoit sur l'espace membres
		{
			$reponse->closeCursor();
			header('Location:./../espacemembres.php'); 
		}
		$reponse->closeCursor(); //l'id existe bien

		$reponse = $bdd->query('SELECT id, acteur, description, logo FROM acteurs WHERE id='.$id.'') or die(print_r($bdd->errorInfo()));	
		if (!is_null($reponse))
		{
			while ($data = $reponse->fetch())
			{
				$_SESSION['logo_acteur']=$data['logo'];
				echo '<p><img src=./../logos/'.$data['logo'].'.png /></p><br />';
				echo '<hgroup id="details_acteur"><p><h2>'.$data['acteur'].'</h2></p>';
				echo '<p><em><a href=\'./details.php?acteur='.$data['id'].'\'>'.$data['acteur'].'</a></em></p><br />';
				echo '<p>'.nl2br(htmlspecialchars($data['description'])).'</p></hgroup>';
			}			
			$reponse->closeCursor();
		}
		$nb_posts=nombre_de('posts','id_acteur',$id);
		$nb_votes=nombre_de('votes','id_acteur',$id);
		$info_likes = infos_likes($id);
		$lienUp=null;
		$lienDown=null;
		$lienReset=null;
		if ($info_likes['deja_vote'])
		{
			//echo '<p>';
			$_SESSION['id_vote']=$info_likes['id_vote'];
			if ($info_likes['vote_content']) //vote égal 1
			{
				$lienUp='<a href=\'change_vote.php?vote=1\' class="notextdeco"><strong><img src=\'./images/Likes33.jpg\' alt=\'image pouce levé\' /></strong></a>'.$info_likes['positifs'];
				$lienDown='<a href=\'change_vote.php?vote=0\' class="notextdeco"><img src=\'./images/DislikesOff33.jpg\' alt=\'image pouce baissé\' /></a>'.$info_likes['negatifs'];
				$lienReset='<a href=\'change_vote.php?vote=2\' class="notextdeco"><img src="./images/reset23x23.png" alt="reset"></a>';
			}
			else
			{
				$lienUp='<a href=\'change_vote.php?vote=1\' class="notextdeco"><img src=\'./images/LikesOff33.jpg\' alt=\'image pouce levé\' /></a>'.$info_likes['positifs'];
				$lienDown='<a href=\'change_vote.php?vote=0\' class="notextdeco"><strong><img src=\'./images/Dislikes33.jpg\' alt=\'image pouce baissé\' /></strong></a>'.$info_likes['negatifs'];
				$lienReset='<a href=\'change_vote.php?vote=2\' class="notextdeco"><img src="./images/reset23x23.png" alt="reset"></a>';
			}
		}
		else
		{
			$lienUp='<a href=\'change_vote.php?vote=3\' class="notextdeco"><img src=\'./images/Likes33.jpg\' alt=\'image pouce levé\' /></a>'.$info_likes['positifs'];
			$lienDown='<a href=\'change_vote.php?vote=4\' class="notextdeco"><img src=\'./images/Dislikes33.jpg\' alt=\'image pouce baissé\' /></a>'.$info_likes['negatifs'];
		}
		
		
		$infos_user_comment=null;
		if (!is_null($nb_posts)) 
		{
			//afficher tous les commentaires et infos associées pour l'acteur choisi
			$tous_les_commentaires='<div id="tous_les_commentaires">';
			if ($nb_posts > 0)
			{
				$reponse = $bdd->query('SELECT p.id p_id, p.post p_post, a.id a_id,a.prenom a_prenom, a.nom a_nom, DATE_FORMAT(p.date_add,\'%d/%m/%Y\') date_ajout FROM posts p INNER JOIN account a ON a.id=p.id_account WHERE p.id_acteur='.$id.'') or die(print_r($bdd->errorInfo()));
				while ($data = $reponse->fetch())
				{
					$tous_les_commentaires .= '<div class="un_commentaire"><p class="entete_commentaire">'.$data['a_prenom'].'<br /> '.$data['date_ajout'].'</p>';
					$tous_les_commentaires .= '<p>'.$data['p_post'].'</p>';
					if ($data['a_id'] == $_SESSION['id'])
					{
						
						$_SESSION['post_id']=$data['p_id'];
						$infos_user_comment['post_id']=$data['p_id'];
						$infos_user_comment['post_content']=$data['p_post'];
						$_SESSION['infos_user_comment']=$infos_user_comment;
						//print_r($infos_user_comment);
						//echo '<br>';
						//print_r($_SESSION['infos_user_comment']);
						$tous_les_commentaires .= '<form action="form_commentaires.php" method="post">';
						$tous_les_commentaires .= '<p><input type="submit" id="modif_commentaire" value="Modifier votre commentaire" name="modif_commentaire" /></p></form>';

					}
					$tous_les_commentaires .= '</div>';
				}
				$tous_les_commentaires .= '</div>';
				$reponse->closeCursor();
			}
		}

		$btn_ajout_commentaire='0';
		if (is_null($infos_user_comment))
		{
			$btn_ajout_commentaire = '<form action="form_commentaires.php" method="post" id="ajout_commentaire">';
			$btn_ajout_commentaire .= '<input type="submit" form="ajout_commentaire" id="btn_ajout_commentaire" value="Commenter" name="ajout_commentaire"/>';
			$btn_ajout_commentaire .= '</form>';
		}

			echo '<div class="white row"><strong>';
				echo '<div class="col-2">';
				if ($nb_posts > 1)
				{
					echo $nb_posts.' commentaires';
				}
				else
				{
					echo $nb_posts.' commentaire';
				}
				echo '</div>';
				echo '<div class="col-6 alignright">';
				if ($btn_ajout_commentaire !='0')
				{
					echo $btn_ajout_commentaire;
				}
				echo '</div>';
				echo '<div class="col-4 alignright">';
				if ($nb_votes > 1)
				{
					echo $nb_votes.' votes '.$lienUp.' &nbsp; '.$lienReset.' &nbsp; '.$lienDown;
				}
				else
				{
					echo $nb_votes.' vote '.$lienUp.' &nbsp; '.$lienReset.' &nbsp; '.$lienDown;
				}
				
				echo '</div>';
			echo '</div></strong>';
			echo '<div class="synthese">&emsp;</div>';
			echo $tous_les_commentaires;

	}
	else
	{
		header('Location:../espacemembres.php');
	}
}
?>
</div>
					<div class="col-1"><span class="invisible">&emsp;</span></div>
				</div>
			</div>
			<div class="col--3"><span class="invisible">&emsp;</span></div>
		</div>
	</div>
</main>

<p><a href="./fonctions/connexion.php?deconnexion=1" style="text-decoration:none;">&emsp;</a></p>


<?php
include('./../templates/footer.php');
?>	
					


