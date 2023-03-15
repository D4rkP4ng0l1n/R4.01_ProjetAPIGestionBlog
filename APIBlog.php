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

	switch ($http_method){
		/// Cas de la méthode GET
		case "GET" :
			//Cas avec un id renseigné dans la requête
			if (!empty($_GET['id'])) {
				if (!ctype_digit($_GET['id'])) {
					deliver_response(400, "Id reçu incorrect", NULL);
				}
				$req = $linkpdo->prepare('SELECT * FROM article WHERE idArticle = :idArticle');
				$req->execute(array('idArticle' => $_GET['id']));
				$matchingData = $req->fetchAll();
				if (empty($matchingData)) {
	           		deliver_response(404, "Article non trouvé", NULL);
	        	}
	        //cas par défaut
			} else {
				$req = $linkpdo->prepare('SELECT * FROM article');
				$req->execute();
				$matchingData = $req->fetchAll();
			}
            /// Envoi de la réponse au Client
            deliver_response(200, "Données reçues", $matchingData);
            break;
		/// Cas de la méthode POST
		case "POST" :
			/// Récupération des données envoyées par le Client
			$postedData = file_get_contents('php://input');
			$data = json_decode($postedData);
			/// Traitement
			if (empty($data->contenu) || empty($data->auteur)) {
	        	deliver_response(400, "Données reçues incomplètes", NULL);
	    	} else {
                $req = $linkpdo->prepare('INSERT INTO article (auteur, contenu) VALUES (:auteur, :contenu)');
			    $req->execute(array('auteur' => $data->auteur, 'contenu' => $data->contenu));

			    $req = $linkpdo->prepare('SELECT * FROM article');
			    $req->execute(array());
			    $matchingData = $req->fetchAll();

			    /// Envoi de la réponse au Client
			    deliver_response(201, "Article ajoutée", $matchingData);
            }
			break;
		/// Cas de la méthode PUT
		case "PUT" :
			/// Récupération des données envoyées par le Client
			$postedData = file_get_contents('php://input');
			$data = json_decode($postedData);
			if (empty($data->contenu) || empty($_GET['id'])) {
	        	deliver_response(400, "Données incomplètes", NULL);
	    	} else {
                /// Traitement
                $req = $linkpdo->prepare('UPDATE article SET contenu = :contenu WHERE idArticle = :idArticle');
                $req->execute(array('contenu' => $data->contenu, 'idArticle' => $_GET['id']));

                $req = $linkpdo->prepare('SELECT * FROM article');
                $req->execute(array());
                $matchingData = $req->fetchAll();

                /// Envoi de la réponse au Client
                deliver_response(200, "Article modifié", $matchingData);
            }
			break;
		/// Cas de la méthode DELETE
		case "DELETE" :
			/// Récupération de l'identifiant de la ressource envoyé par le Client
			if (!empty($_GET['id'])){
				$req = $linkpdo->prepare('DELETE FROM article WHERE idArticle = :idArticle');
				$req->execute(array('idArticle' => $_GET['id']));
                if($req->rowCount() > 0) {
                    deliver_response(200, "Article supprimé", NULL);
                } else {
                    deliver_response(404, "Article innexistant", NULL);
                }
                /// Envoi de la réponse au Client
			} else {

            }
            break;
		default :
			deliver_response(405, "Cette requête n'est pas implémentée dans l'API", NULL);
		}

	/// Envoi de la réponse au Client
	function deliver_response($status, $status_message, $data){
		/// Paramétrage de l'entête HTTP, suite
		header("HTTP/1.1 $status $status_message");
		/// Paramétrage de la réponse retournée
		$response['status'] = $status;
		$response['status_message'] = $status_message;
		$response['data'] = $data;
		/// Mapping de la réponse au format JSON
		$json_response = json_encode($response);
		echo $json_response;
	}
?>