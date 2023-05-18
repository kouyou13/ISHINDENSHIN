<?php
include 'db_config.php';
// signupがPOSTされたときに下記を実行
if(isset($_POST['signup'])) {
	$db = new PDO(PDO_DSN, DB_USERNAME, DB_PASSWORD);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$username = $db->real_escape_string($_POST['name']);
	$face_id = $db->real_escape_string($_POST['face_id']);
	//$password = password_hash($password, PASSWORD_BCRYPT);

	// POSTされた情報をDBに格納する
	// $query = "INSERT INTO face (`username`,`grade`,`faceid`) VALUES('{$username}','{$grade}','{$faceid}')";
    $stmt = $db->exec("INSERT INTO users (`name`,`face_id`) VALUES('{$username}','{$face_id}');");
	$db = null;
	if($db->query($stmt)) {
		echo ("登録しました");
	} else {
		echo("エラーが発生しました");
	}
} 
?>