<?
	include 'db_config.php';
	try {
	//接続
	$db=new PDO(PDO_DSN,DB_USERNAME,DB_PASSWORD);
	$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
	
	//取得
	$stmt=$db->query("SELECT * FROM percent");// usersテーブル
	$percent=$stmt->fetchAll(PDO::FETCH_ASSOC);
	
	$db=null;
	}
	
	catch(PDOException $e)
	{
	echo $e->getMessage();
	exit;
	}
	
	$json=json_encode($percent);
	print($json);
	
	
?>