<?php
	//できた
	include 'db_config.php';

	$name = null; //ユーザー名
	$URL = null; //YouTubeのURL
	$tmp = null; 
	$PerSecX = 0.0; //x軸初速度(横軸)
	$PerSecZ = 0.0; //z軸初速度(縦軸)
	$PerSecY = 0.0; //y軸初速度(奥向き)
	$LengthNetX = 0.0; //ネット上の時のx軸
	$LengthNetZ = 0.0; //ネット上の時のz軸
	$LengthNetY = 0.0; //ネット上の時のy軸(高さ)
	$LengthX = 0.0; //落下地点のx軸[m]
	$LengthZ = 0.0; //落下地点のz軸[m]
	$LengthXpx = 0.0; //落下地点のx軸[px]
	$LengthZpx = 0.0; //落下地点のz軸[px]
	
	$today = date("Y-m-d"); //今日の日にち

	// if(!empty($_POST)){
	$name = $_POST['name']; // "1"; 
	$URL = $_POST['URL']; //"serve_1";
	$tmp1 = $_POST['ParsecX'];
	$PerSecX = (double)$tmp1;
	$tmp2 = $_POST['ParsecZ'];
	$PerSecZ = (double)$tmp2;
	$tmp3 = $_POST['lengthNetY'];
	$LengthNetY = (double)$tmp3;
	$LengthNetY *= 100.0;
	$tmp4 = $_POST['lengthX'];
	$LengthX = (double)$tmp4;
	$tmp5 = $_POST['lengthZ'];
	$LengthZ = (double)$tmp5;
	$tmp6 = $_POST['lengthXpx'];
	$LengthXpx = (double)$tmp6;
	$tmp7 = $_POST['lengthZpx'];
	$LengthZpx = (double)$tmp7;
	$Speed = pow($PerSecX*$PerSecX+$PerSecZ*$PerSecZ,0.5); //速度[m/s]
	
	$user_id = 0;
	$court_id = 1;
	
	if($name == 'Anzai'){
		$user_id = 1;
		// $court_id = 2;
	}
	else if($name == 'Nakamura'){		
		$user_id = 2;
		// $court_id = 13;
	}
	else if($name == 'Tsuji'){
		$user_id = 3;
		// $court_id = 5;
	}
	else if($name == 'Kinoshita'){
		$user_id = 4;
		// $court_id = 5;
	}
	else if($name == 'Hashidume'){
		$user_id = 5;
		// $court_id = 7;
	}
	else if($name == 'Manual'){ //マニュアル用?
		$user_id = 6;
		// $court_id = 7;
	}

	try{
		$db = new PDO(PDO_DSN, DB_USERNAME, DB_PASSWORD);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$stmt = $db->query("SELECT * FROM demomokuhyouti WHERE user_id = $user_id");// demomokuhyoutiテーブルを引っ張ってくる
		$target = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach($target as $t)//実行値のあたいを配列
		{
			$court_id[] = $t['court_id'];
		}
		$db->exec("INSERT INTO `demojikkouti`(`user_id`, `movie_URL`, `court_id`, `speed`, `top`, `x_px`, `y_px`, `date`) VALUES ($user_id, '$URL', $court_id, $Speed, $LengthNetY, $LengthXpx, $LengthZpx, '$today')");
	// $db = null;
	}
	catch(PDOException $e)
	{
    	$error = $e->getMessage();
		echo $error;
    	exit;
	}
	// }
?>