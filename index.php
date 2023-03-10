<!DOCTYPE HTML>
<html>
    <head>
        <title> Client API Blog </title>
        <?php
            include('jwt_utils.php');

            try {
                $linkpdo = new PDO("mysql:host=127.0.0.1;dbname=blog", "root","");
            } catch (Exception $e) {
                die('Erreur : ' . $e->getMessage());
            }

            if (isset($_POST['connectionButton'])) {
                if(!empty($_POST['user']) && !empty($_POST['pwd'])) {
                    $data = array('user' => $_POST['user'], 'pwd' => $_POST['pwd']);
                    $dataEncode = json_encode($data);
                    $result = file_get_contents('http://localhost/Projet-APIGestionBlog/APIAuthentification.php', null, stream_context_create(array('http' => array('method' => 'POST', 'content' => $dataEncode, 'header' => array('Content-Type: application/json'."\r\n".'Content-Length: '.strlen($dataEncode)."\r\n")))));
                    $decodeResult = json_decode($result);
                    print_r($decodeResult);
                } else {
                    echo "Nom d'utilisateur ou mot de passe incorrect";
                }
            }
        ?>
    </head>
    <body>
        <form method="post" action="index.php">
            Utilisateur <input type="text" name="user">
            Mot de passe <input type="password" name="pwd">
            <input type="submit" name="connectionButton" value="Se connecter">
        </form>
    </body>
</html>