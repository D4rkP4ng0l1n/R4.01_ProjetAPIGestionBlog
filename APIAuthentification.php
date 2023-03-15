<?php
	
    // Utilisateur : Jules - Mdp : Shrek - Role : moderator
    // Utilisateur : Kevin - Mdp : Oui - Role : publisher
    // Utilisateur : Loic - Mdp : Mcdo - Role : publisher 


	// Import des fonctions pour la création de token
	include('jwt_utils.php');

	// Connexion au serveur MySQL
    try {
        $linkpdo = new PDO("mysql:host=127.0.0.1;dbname=blog", "root","");
    } catch (Exception $e) {
        die('Erreur : ' . $e->getMessage());
    }

    /// Paramétrage de l'entête HTTP (pour la réponse au Client)
    header("Content-Type:application/json");

    /// Identification du type de méthode HTTP envoyée par le client
    $http_method = $_SERVER['REQUEST_METHOD'];
    
    switch ($http_method) {
    /// Cas de la méthode POST
    case "POST" :
        /// Récupération des données envoyées par le Client
        $postedData = file_get_contents('php://input');
        $dataDecode = json_decode($postedData);
        $req = $linkpdo->prepare('SELECT * FROM utilisateur WHERE nom = :user');
        $req->execute(array('user'=>$dataDecode->user));
        $result = $req->fetch();
        if($req->rowCount() > 0 && password_verify($dataDecode->pwd, $result['motDePasse'])) {
            $headers = array('alg'=>'HS256', 'typ'=>'JWT');
            $payload = array('user'=>$result['nom'], 'role'=> $result['role'], 'exp'=>(time() + 3600));
            $jwt = generate_jwt($headers, $payload);
            deliver_response(201, "Generation du token termine", $jwt);
        }
        else {
               deliver_response(404, "Utilisateur non trouve", NULL);
        }
        break;
    }
    
    /// Envoi de la réponse au Client
    function deliver_response($status, $status_message, $data) {
            /// Paramétrage de l'entête HTTP, suite
            header("HTTP/1.1 $status $status_message");
            /// Paramétrage de la réponse retournée
            $response['status'] = $status;
            $response['status_message'] = $status_message;
            $response['token'] = $data;
            /// Mapping de la réponse au format JSON
            $json_response = json_encode($response);
            echo $json_response;
    }

?>