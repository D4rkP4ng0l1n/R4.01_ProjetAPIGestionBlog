<!DOCTYPE HTML>
<html>
	<head>
		<title> Client Blog </title>
	</head>
	<body>
		<?php
			// Import des fonctions pour les tokens
			include('jwt_utils.php');

			$api = 'http://localhost/Projet-APIGestionBlog/R4.01_ProjetAPIGestionBlog/APIBlog.php';
			$apiAuth = 'http://localhost/Projet-APIGestionBlog/R4.01_ProjetAPIGestionBlog/APIAuthentification.php';

            // Démarage de la session et vérification de la validité du jwt s'il est stocké
            session_start();

            // unset($_SESSION['jwt']);
            // unset($_SESSION['user']);
            if(isset($_SESSION['jwt'])) {
                // echo $_SESSION['jwt'];
                // echo $_SESSION['user'];
            }
            
			// Execution après validation du form d'ajout d'article
			if (isset($_POST['contenuNewArticle'])) {
				////////////////// Cas de la méthode POST //////////////////
				/// Déclaration des données à envoyer au Serveur
				$data = array("auteur" => $_SESSION['user'], "contenu" => $_POST['contenuNewArticle']); //MODIFIER AUTEUR PAR LE NOM DE COMPTE
				$data_string = json_encode($data);
				/// Envoi de la requête
				$result = file_get_contents($api,null,stream_context_create(array('http' => array('method' => 'POST', 'content' => $data_string,'header' => array('Content-Type: application/json'."\r\n".'Content-Length: '.strlen($data_string)."\r\n".'Authorization: Bearer '.$_SESSION['jwt'])))));
				header('Location: index.php');
			}

			// Execution après validation du form de modification d'un article
			if (isset($_POST['modif'])) {
				////////////////// Cas de la méthode PUT //////////////////
				/// Déclaration des données à envoyer au Serveur
				$data = array("contenu" => $_POST['modif']);
				$data_string = json_encode($data);
				/// Envoi de la requête
				$result = file_get_contents($api.'?id='.$_POST['id'],null,stream_context_create(array('http' => array('method' => 'PUT', 'content' => $data_string,'header' => array('Content-Type: application/json'."\r\n".'Content-Length: '.strlen($data_string)."\r\n".'Authorization: Bearer '.$_SESSION['jwt'])))));
				header('Location: index.php');
			}

			// Execution après clic sur le bouton supprimer
			if (isset($_GET['deleteId'])) {
				////////////////// Cas de la méthode DELETE //////////////////
				$result = file_get_contents($api.'?id='.$_GET['deleteId'],false,stream_context_create(array('http' => array('method' => 'DELETE', 'header' => 'Authorization: Bearer '.$_SESSION['jwt']))));
				header('Location: index.php');
			}

			// Execution après clic sur le bouton liker
			if (isset($_GET['likeId'])) {
				////////////////// Cas de la méthode POST //////////////////
				/// Déclaration des données à envoyer au Serveur
				$data = array("auteur" => $_SESSION['user'], "IdArticle" => $_GET['likeId'], "eval" => "Like");
				$data_string = json_encode($data);
				/// Envoi de la requête
				$result = file_get_contents($api,null,stream_context_create(array('http' => array('method' => 'POST', 'content' => $data_string,'header' => array('Content-Type: application/json'."\r\n".'Content-Length: '.strlen($data_string)."\r\n".'Authorization: Bearer '.$_SESSION['jwt'])))));
				header('Location: index.php');
			}
			
			// Execution après clic sur le bouton disliker
			if (isset($_GET['dislikeId'])) {
				////////////////// Cas de la méthode POST //////////////////
				/// Déclaration des données à envoyer au Serveur
				$data = array("auteur" => $_SESSION['user'], "IdArticle" => $_GET['dislikeId'], "eval" => "Dislike");
				$data_string = json_encode($data);
				/// Envoi de la requête
				$result = file_get_contents($api,null,stream_context_create(array('http' => array('method' => 'POST', 'content' => $data_string,'header' => array('Content-Type: application/json'."\r\n".'Content-Length: '.strlen($data_string)."\r\n".'Authorization: Bearer '.$_SESSION['jwt'])))));
				header('Location: index.php');
			}

			// Chargement de la page d'un utilisateur après le clic sur le bouton se connecter
            if (isset($_POST['connectionButton'])) {
                if(!empty($_POST['user']) && !empty($_POST['pwd'])) {
                    $data = array('user' => $_POST['user'], 'pwd' => $_POST['pwd']);
                    $dataEncode = json_encode($data);
                    $result = file_get_contents($apiAuth, null, stream_context_create(array('http' => array('ignore_errors' => true, 'method' => 'POST', 'content' => $dataEncode, 'header' => array('Content-Type: application/json'."\r\n".'Content-Length: '.strlen($dataEncode)."\r\n".'Authorization: Bearer '.$_SESSION['jwt'])))));
					$decodeResult = json_decode($result);
					if($decodeResult->status == 404) {
						echo "Nom d'utilisateur ou mot de passe incorrect";
					} else {
						$_SESSION['jwt'] = $decodeResult->token;
						if(isset($_SESSION['jwt'])) {
							$_SESSION['user'] = $_POST['user'];
						}
						//header('Location: index.php');
					}
                } else {
                    echo "Nom d'utilisateur ou mot de passe vide";
                }
            }

			//chargement d'une page pour modifier le contenu d'un article après le clic sur le bouton modifier
			if (isset($_GET['putId'])) {
				////////////////// Cas de la méthode GET //////////////////
				$result = file_get_contents($api.'?id='.$_GET['putId'],false,stream_context_create(array('http' => array('method' => 'GET', 'header' => 'Authorization: Bearer '.$_SESSION['jwt']))));
				$result = json_decode($result, true);

				foreach ($result['data'] as $res) {
					echo "<h5> Modifier l'article : </h5>";
					echo "<form method='post' action='index.php'>";
					    echo "<textarea name='modif'>".htmlspecialchars($res['contenu'])."</textarea>";
					    echo "<input type='hidden' name='id' value='".$_GET['putId']."'>";
					    echo "<input type='submit' value='Valider'>";
					echo "</form>";
				}
			//chargement de la page par défaut
			} else {
				//affichage du form de connexion
				echo "<form method='post' action='index.php'>";
				    echo "Utilisateur <input type='text' name='user'> </br>";
				    echo "Mot de passe <input type='password' name='pwd'> </br>";
				    echo "<input type='submit' name='connectionButton' value='Se connecter'>";
				echo "</form>";

				//affichage du form d'ajout d'article
				echo "<h5> Ajouter un article : </h5>";
				echo "<form method='post' action='index.php'>";
				    echo "<textarea name='contenuNewArticle' value=''> </textarea>" ;
				    echo "<input type='submit' value='Valider'>";
				echo "</form>";

				//affichage des articles
				////////////////// Cas de la méthode GET //////////////////
				if(isset($_SESSION['jwt'])) {
					$result = file_get_contents($api, false, stream_context_create(array('http' => array('method' => 'GET', 'header' => 'Authorization: Bearer '.$_SESSION['jwt']))));
				} else {
					$result = file_get_contents($api, false, stream_context_create(array('http' => array('method' => 'GET'))));
				}
				$result = json_decode($result, true);
				$alreadyDisplayed = array(); // Tableau pour stocker les ID déjà affichés

				echo "<table>";
				echo "<tbody>";
				foreach ($result['data'] as $res) {
					$id = $res['IdArticle'];
    				if (!in_array($id, $alreadyDisplayed)) { // Si l'ID n'a pas déjà été affiché
    					// Tableaux pour stocker les utilisateur qui ont liké/disliké
    					$likes = array();
				        $dislikes = array();
				        foreach ($result['data'] as $res2) {
				            if ($res2['IdArticle'] == $id) {
				                if ($res2['evaluation'] == 1) {
				                    $likes[] = $res2['nom'];
				                } else if ($res2['evaluation'] == -1) {
				                    $dislikes[] = $res2['nom'];
				                }
				            }
				        }
						echo "<tr>";
							echo "<td>".htmlspecialchars($res['auteur'])."</td>";
							echo "<td>".htmlspecialchars($res['contenu'])."</td>";
							$id = $res['IdArticle'];
							echo "<td><a href='index.php?putId=$id'>Modifier</a></td>";
							echo "<td><a href='index.php?deleteId=$id'>Supprimer</a></td>";
							if($result['authorization'] == 'publisher') {
								echo "<td><a href='index.php?likeId=$id'>Liker</a></td>";
								echo "<td><a href='index.php?dislikeId=$id'>Disliker</a></td>";
							}
							if($result['authorization'] != 'anonymous') {
								if (!empty($likes)) {
									$first = true;
									foreach ($likes as $like) {
										if (!$first) {
											echo ", ";
										}
										echo htmlspecialchars($like);
										$first = false;
									}
								}
								echo "</td>";
								echo "<td>Dislikes : ".count($dislikes)."<br>";
								if (!empty($dislikes)) {
									$first = true;
									foreach ($dislikes as $dislike) {
										if (!$first) {
											echo ", ";
										}
										echo htmlspecialchars($dislike);
										$first = false;
									}
							}
							echo "<td>Likes : ".count($likes)."<br>";
				        }
				        echo "</td>";
						echo "</tr>";
						$alreadyDisplayed[] = $id; // ajouter l'ID à la liste des articles déjà affichés
					}
				}
				echo "</tbody>";
				echo "</table>";
			}
		?>
	</body>
</html>