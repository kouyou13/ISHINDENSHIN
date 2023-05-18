<?php
// DBとの接続
include_once 'db_config.php';
?>
<!DOCTYPE HTML>
<html lang="ja">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>会員登録機能</title>

<!-- Bootstrap読み込み（スタイリングのため） -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css">
</head>
<body>
<div class="col-xs-6 col-xs-offset-3">

<?php
// signupがPOSTされたときに下記を実行
if(isset($_POST['signup'])) {
	$db = new PDO(PDO_DSN, DB_USERNAME, DB_PASSWORD);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$face_id = $_POST['face_id'];
	$name = $_POST['name'];
	$dataURL = $_GET['img'];
	$ext = 'image/png';
	
	//$password = password_hash($password, PASSWORD_BCRYPT);

	// POSTされた情報をDBに格納する
	// $query = "INSERT INTO face (`username`,`grade`,`faceid`) VALUES('{$username}','{$grade}','{$faceid}')";
	try{
    $stmt = $db->prepare("INSERT INTO users (`name`,`face_id`,`img`,`ext`) VALUES(?,?,?,?)");
	$stmt->execute([$name, $face_id,$dataURL,$ext]);
	$db = null;
	header("location:index.html");
	exit();
	} catch (\Exception $e) {
	  echo 'era-';
}} ?>

		<form method="post">
			<h1>会員登録フォーム</h1>
			<div class="form-group">
				<input type="text" class="form-control" name="name" placeholder="名前" required />
			</div>
			<div class="form-group">
				<input type="text" class="form-control" name="face_id" value ="<?php print($_GET["face_id"]);?>"　placeholder="face_id" required />
			</div>

			<button type="submit" class="btn btn-default" name="signup">会員登録する</button><br>
			<a href="index.html">ログインはこちら</a>
		</form>
	</div>
</body>