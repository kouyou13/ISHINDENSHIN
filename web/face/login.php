<?php
	include 'db_config.php';
	if(!empty($_POST)){
		$face_id = $_POST['face_id'];
		try{
			$db = new PDO(PDO_DSN, DB_USERNAME, DB_PASSWORD);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$stmt = $db->query("SELECT (SELECT `face_id` FROM `users` WHERE face_id = '{$face_id}') = '{$face_id}'  as result");
			$db = null;
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			if($result['result'] == null){
				print(0);
			}else{
				print($result['result']);
				
			}
		}catch(PDOException $e){
	    	$error = $e->getMessage();
			print($error);
	    	exit;
		}
	}
?>