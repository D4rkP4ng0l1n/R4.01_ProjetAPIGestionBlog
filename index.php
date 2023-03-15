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
            //unset($_SESSION['jwt']);
            //unset($_SESSION['user']);
            if(isset($_SESSION['jwt'])) {
                echo $_SESSION['jwt'];
                echo $_SESSION['user'];
            }
            
			// Execution après validation du form d'ajout d'article
			if (isset($_POST['contenuNewArticle'])) {
				////////////////// Cas de la méthode POST //////////////////
				/// Déclaration des données à envoyer au Serveur
				$data = array("auteur" => "Jules", "contenu" => $_POST['contenuNewArticle']); //MODIFIER AUTEUR PAR LE NOM DE COMPTE
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

			// Chargement de la page d'un utilisateur après le clic sur le bouton se connecter
            if (isset($_POST['connectionButton'])) {
                if(!empty($_POST['user']) && !empty($_POST['pwd'])) {
                    $data = array('user' => $_POST['user'], 'pwd' => $_POST['pwd']);
                    $dataEncode = json_encode($data);
                    $result = file_get_contents($apiAuth, null, stream_context_create(array('http' => array('method' => 'POST', 'content' => $dataEncode, 'header' => array('Content-Type: application/json'."\r\n".'Content-Length: '.strlen($dataEncode)."\r\n".'Authorization: Bearer '.$_SESSION['jwt'])))));
                    $decodeResult = json_decode($result);
                    //print_r($decodeResult);
                       $_SESSION['jwt'] = $decodeResult->token;
                       if(isset($_SESSION['jwt'])) {
                           $_SESSION['user'] = $_POST['user'];
                       }
                       header('Location: index.php');
                } else {
                    echo "Nom d'utilisateur ou mot de passe incorrect";
                }
            }

			//chargement d'une page pour modifier le contenu d'un article après le clic sur le bouton modifier
			if (isset($_GET['putId'])) {
				////////////////// Cas de la méthode GET //////////////////
				$result = file_get_contents($api.'?id='.$_GET['putId'],false,stream_context_create(array('http' => array('method' => 'GET', 'header' => 'Authorization: Bearer '.$_SESSION['jwt']))));
				$result = json_decode($result, true);

				foreach ($result['data'] as $res) {
					echo "<h5>Modifier l'article:</h5>";
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
				echo "Utilisateur <input type='text' name='user'>";
				echo "Mot de passe <input type='password' name='pwd'>";
				echo "<input type='submit' name='connectionButton' value='Se connecter'>";
				echo "</form>";

				//affichage du form d'ajout d'article
				echo "<h5>Ajouter un article :</h5>";
				echo "<form method='post' action='index.php'>";
				echo "<textarea name='contenuNewArticle' value=''> </textarea>" ;
				echo "<input type='submit' value='Valider'>";
				echo "</form>";

				//affichage des articles
				////////////////// Cas de la méthode GET //////////////////
				$result = file_get_contents($api,false,stream_context_create(array('http' => array('method' => 'GET', 'header' => 'Authorization: Bearer '.$_SESSION['jwt']))));
				$result = json_decode($result, true);
				foreach ($result['data'] as $res) {
						echo "<table>";
						echo "<tbody>";
						echo "<tr>";
						echo "<td>".htmlspecialchars($res['auteur'])."</td>";
						echo "<td>".htmlspecialchars($res['contenu'])."</td>";
						$id = $res['IdArticle'];
						echo "<td><a href='index.php?putId=$id'>Modifier</a></td>";
						echo "<td><a href='index.php?deleteId=$id'>Supprimer</a></td>";
						echo "<tr>";
						echo "</tbody>";
						echo "</table>";
				}
			}
		?>
	</body>
</html>