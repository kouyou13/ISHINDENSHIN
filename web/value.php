<?php
	include 'db_config.php';
     try
     {
        $db = new PDO(PDO_DSN, DB_USERNAME, DB_PASSWORD);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


     $stmt = $db->query("SELECT * FROM value"); // 実行値テーブルを引っ張ってくる
     $value = $stmt->fetchAll(PDO::FETCH_ASSOC);
     $db = null;
     }
     


     catch(PDOException $e)
     {
      echo $e->getMessage();
      exit;
     }

     $json = json_encode($value);
     print ($json);

?>
