<?php
require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->get('/:id_user/picture/:pagination', 'getPictures');

$app->post('/:id_user/picture', 'addPicture');
$app->get('/picture/:id_picture', 'getPicture');  //this one will call the getComment
$app->put('/picture/:id_picture', 'updatePicture');
$app->delete('/picture/:id_picture', 'deletePicture');

$app->get('/:id_picture/comment', 'getComment'); 
$app->post('/:id_user/:id_picture/comment', 'addComment');  //TODO: id_user opzionale, indicato solo per admin, else set picture owner
$app->delete('/:id_picture/comment', 'deleteComment');

$app->run();


function getPictures($id_user, $pagination = 0) {
	
	$offset = $pagination+10;
    $sql = "SELECT * FROM picture WHERE id_user = $id_user LIMIT $pagination, $offset";
	//echo $sql;
    try {
        $db = getConnection();
		$stmt = $db->query($sql);  
		$pictures = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
			echo '{"picture": ' . json_encode($pictures) . '}';
        } else {
            echo $_GET['callback'] . '(' . '{"picture": ' . json_encode($pictures) . '}' . ');';
        }

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function addPicture($id_user) {
    
    $app = \Slim\Slim::getInstance();
    $request = $app->request();
	$picture = json_decode($request->getBody());
        
    $sql = "INSERT INTO picture (`id_user`, `title_picture`, `lat_picture`, `lon_picture`, `source_picture`) VALUES (:id_user, :title_picture, :lat_picture, :lon_picture, :source_picture)";
    
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id_user", $id_user);
        $stmt->bindParam("title_picture", $picture->title_picture);
        $stmt->bindParam("lat_picture", $picture->lat_picture);
        $stmt->bindParam("lon_picture", $picture->lon_picture);
        $stmt->bindParam("source_picture", $picture->source_picture);
        $stmt->execute();
        $picture->id_picture = $db->lastInsertId();
        $db = null;
        echo json_encode($picture);
        
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }  
}


function getPicture($id_picture) {

    $sql = "SELECT * FROM picture WHERE id_picture = :id_picture" ;
    
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);  
		$stmt->bindParam("id_picture", $id_picture);
		$stmt->execute();
		$pictures = $stmt->fetchObject();  
		$db = null;
		
		// Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($pictures);
        } else {
            echo $_GET['callback'] . '(' . json_encode($pictures) . ');';
        }

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function updatePicture($id_picture) {
	
	$app = \Slim\Slim::getInstance();
    $request = $app->request();
	$picture = json_decode($request->getBody());

	$sql = "UPDATE picture SET title_picture=:title_picture, lat_picture=:lat_picture, lon_picture=:lon_picture, source_picture=:source_picture WHERE id_picture=:id_picture";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
        $stmt->bindParam("title_picture", $picture->title_picture);
        $stmt->bindParam("lat_picture", $picture->lat_picture);
        $stmt->bindParam("lon_picture", $picture->lon_picture);
        $stmt->bindParam("source_picture", $picture->source_picture);
		$stmt->bindParam("id_picture", $id_picture);
		$stmt->execute();
		$db = null;
		echo json_encode($picture); 
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function deletePicture($id_picture) {
	$sql = "DELETE FROM picture WHERE id_picture=:id_picture";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("id_picture", $id_picture);
		$stmt->execute();
		$db = null;
		
		echo 'true';
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}


function addComment($id_user, $id_picture){

	$app = \Slim\Slim::getInstance();
    $request = $app->request();
	$comment = json_decode($request->getBody());

	$sql = "INSERT INTO comment (`id_picture`, `id_user`, `text_comment`) VALUES (:id_picture, :id_user, :text_comment)";
	
	 try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id_picture", $id_picture);
		$stmt->bindParam("id_user", $id_user);
		$stmt->bindParam("text_comment", $comment->text_comment);
		$stmt->execute();
		$db = null;

  	    echo json_encode($comment);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

function getComment($id_picture) {

    $sql = "SELECT * FROM comment WHERE id_picture = :id_picture" ;
    
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);  
		$stmt->bindParam("id_picture", $id_picture);
		$stmt->execute();
		$comments = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

		
		// Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo '{"comments": ' . json_encode($comments) . '}';
        } else {
            echo $_GET['callback'] . '(' . '{"comments": ' . json_encode($comments) . '}' . ');';
        }

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function deleteComment($id_picture) {
	$sql = "DELETE FROM comment WHERE id_picture=:id_picture";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("id_picture", $id_picture);
		$stmt->execute();
		$db = null;
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}


/*
function getConnection() {
    $dbhost="localhost";
    $dbuser="root";
    $dbpass="EWJeE3NEwuCaF7Dq";
    $dbname="bioApp";
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);  
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}

*/
function getConnection() {
    $dbhost="62.149.150.190";
    $dbuser="Sql667604";
    $dbpass="08d6975f";
    $dbname="Sql667604_2";
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);  
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}
