<?php
	include 'db_config.php';
	$db = new PDO(PDO_DSN, DB_USERNAME, DB_PASSWORD);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$stmt = $db->query("SELECT id,name,face_id,img FROM users");// demomokuhyoutiテーブルを引っ張ってくる
	$target = $stmt->fetchAll(PDO::FETCH_ASSOC);
	print_r(json_encode($target));
?>