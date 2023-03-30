<?php
	///Connexion au serveur MySQL
	try {
		$linkpdo = new PDO("mysql:host=127.0.0.1;dbname=blog", "root","");
	}
	catch (Exception $e) {
		die('Erreur : ' . $e->getMessage());
	}

	/// Librairies éventuelles (pour la connexion à la BDD, etc.)
	include('jwt_utils.php');

	/// Paramétrage de l'entête HTTP (pour la réponse au Client)
	header("Content-Type:application/json");

	/// Identification du type de méthode HTTP envoyée par le client
	$http_method = $_SERVER['REQUEST_METHOD'];

    /// Identification du token envoyé par le client
    $bearer_token = get_bearer_token();

	// Récupération du role de l'utilisateur si il est connecté
	if($bearer_token != null && is_jwt_valid($bearer_token)) {		
		$authorization = getAuthorization($bearer_token); 
	} else {
		$authorization = "anonymous";
	}

	switch ($http_method){
		/// Cas de la méthode GET
		case "GET" :
			//Cas avec un id renseigné dans la requête
			if (!empty($_GET['id'])) {
				if($authorization == 'anonymous') {
					deliver_response(403, "Operation non autorisée pour un utilisateur anonyme", NULL, $authorization);
				} else {
					if (!ctype_digit($_GET['id'])) {
						deliver_response(400, "Id reçu incorrect", NULL, $authorization);
					}
					$req = $linkpdo->prepare('SELECT article.*, evaluer.evaluation, evaluer.nom FROM article LEFT JOIN evaluer ON article.IdArticle = evaluer.IdArticle WHERE article.IdArticle = :IdArticle');
					$req->execute(array('IdArticle' => $_GET['id']));
					$matchingData = $req->fetchAll();
					if (empty($matchingData)) {
						deliver_response(404, "Article non trouvé", NULL, $authorization);
					}
				}
	        //cas par défaut
			} else {
				if($authorization != 'anonymous') {
					$req = $linkpdo->prepare('SELECT article.*, evaluer.evaluation, evaluer.nom FROM article LEFT JOIN evaluer ON article.IdArticle = evaluer.IdArticle ORDER BY article.datePublication');
				} else {
					$req = $linkpdo->prepare('SELECT auteur, contenu, datePublication FROM article');
				}
				$req->execute();
				$matchingData = $req->fetchAll();
				/// Envoi de la réponse au Client
				deliver_response(200, "Données reçues", $matchingData, $authorization);
			}
            break;
		/// Cas de la méthode POST
		case "POST" :
			if($authorization == 'publisher') {
				/// Récupération des données envoyées par le Client
				$postedData = file_get_contents('php://input');
				$data = json_decode($postedData);
				/// Traitement
				//cas ajout article
				if(isset($data->contenu) && isset($data->auteur)) {
					if (empty($data->contenu) || empty($data->auteur)) {
		        		deliver_response(400, "Données reçues incomplètes (Auteur et contenu)", $data->contenu, $authorization);
		    		} else {
	                	$req = $linkpdo->prepare('INSERT INTO article (auteur, contenu) VALUES (:auteur, :contenu)');
				    	$req->execute(array('auteur' => $data->auteur, 'contenu' => $data->contenu));

				    	$req = $linkpdo->prepare('SELECT * FROM article');
				    	$req->execute(array());
				    	$matchingData = $req->fetchAll();

						/// Envoi de la réponse au Client
						deliver_response(201, "Article ajoutée", $matchingData, $authorization);
					}
				//cas like/dislike article
				} else {
					if (empty($data->IdArticle) || empty($data->auteur) || empty($data->eval)) {
						deliver_response(400, "Données reçues incomplètes (Auteur, contenu et évaluation)", NULL, $authorization);
					} else {
						$req = $linkpdo->prepare('SELECT * FROM evaluer WHERE IdArticle = :IdArticle AND nom = :nom');
						$req->execute(array('IdArticle' => $data->IdArticle, 'nom' => $data->auteur));

						//cas like
						if($data->eval == "Like") {
							//Ajout du like si l'article n'avait pas été liké ou disliké
							if($req->rowCount() == 0) {
								//Nous avons décidés de faire un update dans un POST pour éviter au client de devoir accéder à la base de données
								$req = $linkpdo->prepare('INSERT INTO evaluer (evaluation, nom, IdArticle) VALUES (:evaluation, :nom, :IdArticle)');
								$req->execute(array('evaluation' => 1, 'nom' => $data->auteur, 'IdArticle' => $data->IdArticle));
							} else {
								$result = $req->fetch();
								$result['evaluation'];
								//cas où on change le dislike en like
								if($result['evaluation'] == -1) {
									$req = $linkpdo->prepare('UPDATE evaluer SET evaluation = :evaluation WHERE IdArticle = :IdArticle AND nom = :nom');
									$req->execute(array('evaluation' => 1, 'IdArticle' => $data->IdArticle, 'nom' => $data->auteur));
								//cas où l'utilisateur retire son like
								} else {
									//même raisonement que pour le insert plus haut
									$req = $linkpdo->prepare('DELETE FROM evaluer WHERE IdArticle = :IdArticle  AND nom = :nom');
									$req->execute(array('IdArticle' => $data->IdArticle, 'nom' => $data->auteur));
								}
							}
						}
						
						//cas dislike
						if($data->eval == "Dislike") {
							//Ajout du dislike si l'article n'avait pas été liké ou disliké
							if($req->rowCount() == 0) {
								//Nous avons décidés de faire un update dans un POST pour éviter au client de devoir accéder à la base de données
								$req = $linkpdo->prepare('INSERT INTO evaluer (evaluation, nom, IdArticle) VALUES (:evaluation, :nom, :IdArticle)');
								$req->execute(array('evaluation' => -1, 'nom' => $data->auteur, 'IdArticle' => $data->IdArticle));
							} else {
								$result = $req->fetch();
								$result['evaluation'];
								//cas où on change le like en dislike
								if($result['evaluation'] == 1) {
									$req = $linkpdo->prepare('UPDATE evaluer SET evaluation = :evaluation WHERE IdArticle = :IdArticle AND nom = :nom');
									$req->execute(array('evaluation' => -1, 'IdArticle' => $data->IdArticle, 'nom' => $data->auteur));
								//cas où l'utilisateur retire son like
								} else {
									//même raisonement que pour le insert plus haut
									$req = $linkpdo->prepare('DELETE FROM evaluer WHERE IdArticle = :IdArticle  AND nom = :nom');
									$req->execute(array('IdArticle' => $data->IdArticle, 'nom' => $data->auteur));
								}
							}
						}

						if($data->eval == "Like" || $data->eval == "Dislike") {
							$req = $linkpdo->prepare('SELECT * FROM evaluer');
							$req->execute(array());
							$matchingData = $req->fetchAll();
	
							/// Envoi de la réponse au Client
							deliver_response(201, "Evaluation ajouté", $matchingData, $authorization);
						} else {
							deliver_response(400, "Evaluation reçue incorrecte", NULL, $authorization);
						}
					}
				}
			} else {
				deliver_response(403, "Operation non autorisée pour un utilisateur non publisher", NULL, $authorization);
			}
		break;
		/// Cas de la méthode PUT
		case "PUT" :
			if ($authorization == 'publisher') {
				/// Récupération des données envoyées par le Client
				$postedData = file_get_contents('php://input');
				$data = json_decode($postedData);
				if (empty($data->contenu) || empty($_GET['id'])) {
					deliver_response(400, "Données incomplètes", NULL, $authorization);
				} else {
					$nom = getNom($bearer_token);
					$verifOK = 0;
					$req = $linkpdo->prepare('SELECT count(*) FROM article WHERE IdArticle = :IdArticle AND auteur = :auteur');
					$req->execute(array('IdArticle' => $_GET['id'], 'auteur' => $nom));
					$result = $req->fetch();
					$verifOK = $result[0];
					if($verifOK > 0) {
						/// Traitement
						$req = $linkpdo->prepare('UPDATE article SET contenu = :contenu WHERE idArticle = :idArticle');
						$req->execute(array('contenu' => $data->contenu, 'idArticle' => $_GET['id']));

						$req = $linkpdo->prepare('SELECT * FROM article');
						$req->execute(array());
						$matchingData = $req->fetchAll();

						/// Envoi de la réponse au Client
						deliver_response(200, "Article modifié", $matchingData, $authorization);
					}
					else {
						deliver_response(403, "Seul l'auteur de l'article peut le modifier", NULL, $authorization);
					}
				}
			} else {
				deliver_response(403, "Operation non autorisée pour un utilisateur non publisher", NULL, $authorization);
			}
			break;
		/// Cas de la méthode DELETE
		case "DELETE" :
				/// Récupération de l'identifiant de la ressource envoyé par le Client
				if ($authorization != 'anonymous') {
					if (!empty($_GET['id'])) {
						$nom = getNom($bearer_token);
						$verifOK = 0;
						if($authorization == 'publisher') {
							$req = $linkpdo->prepare('SELECT count(*) FROM article WHERE IdArticle = :IdArticle AND auteur = :auteur');
							$req->execute(array('IdArticle' => $_GET['id'], 'auteur' => $nom));
							$result = $req->fetch();
							$verifOK = $result[0];
						}
						if($authorization == 'moderator' || $verifOK > 0) {
							// Suppression des likes/dislikes
							$req = $linkpdo->prepare('DELETE FROM evaluer WHERE IdArticle = :IdArticle');
							$req->execute(array('IdArticle' => $_GET['id']));
	
							// Suppression de l'article
							$req = $linkpdo->prepare('DELETE FROM article WHERE IdArticle = :IdArticle');
							$req->execute(array('IdArticle' => $_GET['id']));
							if($req->rowCount() > 0) {
								deliver_response(200, "Article supprimé", NULL, $authorization);
							} else {
								deliver_response(404, "Article innexistant", NULL, $authorization);
							}
						} else {
							$req = $linkpdo->prepare('SELECT count(*) FROM article WHERE IdArticle = :IdArticle');
							$req->execute(array('IdArticle' => $_GET['id']));
							if($req->rowCount() >= 1) {
								deliver_response(403, "Operation non autorisée, ce n'est pas votre article", NULL, $authorization);
							} else {
								deliver_response(404, "Article innexistant", NULL, $authorization);
							}
						}
					}
					else {
						deliver_response(400, "Données reçues incomplètes", NULL, $authorization);
					}
				} else {
					deliver_response(403, "Operation non autorisée pour un utilisateur anonyme", NULL, $authorization);
				}
            break;
		default :
			deliver_response(405, "Cette requête n'est pas implémentée dans l'API", NULL, $authorization);
		}

	/// Envoi de la réponse au Client
	function deliver_response($status, $status_message, $data, $authorization){
		/// Paramétrage de l'entête HTTP, suite
		header("HTTP/1.1 $status $status_message");
		/// Paramétrage de la réponse retournée
		$response['status'] = $status;
		$response['status_message'] = $status_message;
		$response['data'] = $data;
		$response['authorization'] = $authorization;
		/// Mapping de la réponse au format JSON
		$json_response = json_encode($response);
		echo $json_response;
	}

	/// Fonction pour décoder un token JWT
	function decodeJWT($token) {
		$token_parts = explode('.', $token);
		$decoded_claims = base64_decode($token_parts[1]);
		$claims = json_decode($decoded_claims, true);
		return $claims;
	}

	/// Fonction pour récupérer le role d'un utilisateur
	function getAuthorization($token) {
		if(is_jwt_valid($token)) {
			$decoded_token = decodeJWT($token);
			$authorization = $decoded_token['role'];
		} else {
			$authorization = 'anonymous';
		}
		return $authorization;
	}

	function getNom($token) {
		if(is_jwt_valid($token)) {
			$decoded_token = decodeJWT($token);
			return $decoded_token['user'];
		}
	}

?>