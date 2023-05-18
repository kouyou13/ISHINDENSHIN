<?php
include 'db_config.php';

$user_id = $_GET['user_id'];
$post_date = $_GET['select_date'];
$errorMessage = '';

$param = $post_date;

try
{
	$db = new PDO(PDO_DSN, DB_USERNAME, DB_PASSWORD);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$stmt = $db->query("SELECT * FROM demo_mokuhyoutasseiritu WHERE user_id = $user_id AND date = '$post_date'");
	$pasento = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$stmt = $db->query("SELECT * FROM users"); // usersテーブルを引っ張ってくる
	$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$stmt = $db->query("SELECT * FROM demojikkouti WHERE user_id = $user_id AND date = '$post_date'"); // 動画テーブルを引っ張ってくる
	$demojikkouti = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if(!empty($demojikkouti[0]['court_id'])){ //値があるかの判定
		$stmt = $db->query("SELECT img_url FROM court_img WHERE id = ".$demojikkouti[0]['court_id'].""); // 動画テーブルを引っ張ってくる
		$court_img = $stmt->fetch(PDO::FETCH_ASSOC)['img_url']; 
	}
	else{
		$errorMessage = "データがありません。日付を変更してください。";
	}
}
catch(PDOException $e)
{
	// echo $e->getMessage();
	$errorMessage = "データがありません。日付を変更してください。";
	// echo $errormessage;
	exit;
}
     
$db = null;
// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// 変数の初期化
$now_date = null;
$data = null;
$file_handle = null;
$split_data = null;
$message = array();
$message_array = array();
$success_message = null;
$error_message = array();
$clean = array();

session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
if( !empty($_POST['btn_submit']) ) {

	// 表示名の入力チェック
	if( empty($_POST['view_name']) ) {
		$error_message[] = '表示名を入力してください。';
	} else {
		$clean['view_name'] = htmlspecialchars( $_POST['view_name'], ENT_QUOTES);

		// セッションに表示名を保存
		$_SESSION['view_name'] = $clean['view_name'];
	}

	// メッセージの入力チェック
	if( empty($_POST['message']) ) {
		$error_message[] = 'ひと言メッセージを入力してください。';
	} else {
		$clean['message'] = htmlspecialchars( $_POST['message'], ENT_QUOTES);
		$clean['message'] = preg_replace( '/\\r\\n|\\n|\\r/', '<br>', $clean['message']);
	}
	if( empty($_POST['practice_id']) ) {
		$error_message[] = 'ひと言メッセージを入力してください。';
	} else {
		$clean['practice_id'] = htmlspecialchars( $_POST['practice_id'], ENT_QUOTES);
		$clean['practice_id'] = preg_replace( '/\\r\\n|\\n|\\r/', '<br>', $clean['practice_id']);
	}

	if( empty($error_message) ) {

		try
		{
			$db = new PDO(PDO_DSN, DB_USERNAME, DB_PASSWORD);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			// 操作1
			$now_date = date("Y-m-d H:i:s");
			$rensyu_id = $practice_id[$index] + 1;
			$db->exec("INSERT INTO message (practice_id,view_name, message, post_date) VALUES ( '$clean[practice_id]','$clean[view_name]', '$clean[message]', '$now_date')");
			$db = null;
		}
		catch(PDOException $e)
		{
			$error = $e->getMessage();
			echo $error;
			if( $res ) {
				$_SESSION['success_message'] = 'メッセージを書き込みました。';
			} else {
				$error_message[] = '書き込みに失敗しました。';
			}           
			exit;

		}
		header('Location: afterrensyu.php?user_id= '.$user_id.'&select_date='.$post_date.'');       
	}
}

}
?>

<!DOCTYPE html>
<html lang="ja">

	<script>
		var xhr1 = new XMLHttpRequest();

xhr1.open('GET', 'https://web2-17423.azurewebsites.net/ISINDENSIN/users.php');//選手名取ってくる
xhr1.send();
xhr1.onreadystatechange = function() {

	if(xhr1.readyState === 4 && xhr1.status === 200) {

		console.log( xhr1.responseText );
		const json1 = xhr1.responseText;
		const obj1 = JSON.parse(json1);
		document.getElementById('id01').innerHTML = obj1[0].name + "<br>";

		console.log(obj1[0].name);

	}
}

	</script>

	<script>

		var xhr = new XMLHttpRequest();

xhr.open('GET', 'https://web2-17423.azurewebsites.net/ISINDENSIN/value.php');//値とってくる
xhr.send();
xhr.onreadystatechange = function() {

	if(xhr.readyState === 4 && xhr.status === 200) { 
		console.log( xhr.responseText );

		const json = xhr.responseText;
		const obj = JSON.parse(json);
		console.log(obj);
		console.log(document.getElementById('speedvalue'));
		document.getElementById('speedvalue').innerHTML = "速度："+obj[0].speed +"m/s"+"<br>";
		console.log(document.getElementById('rotation_id'));
		document.getElementById('rotation_id').innerHTML = "・回転数："+obj[0].rotations+"deg/s"+"<br>";         
		console.log(obj[0].speed);
		console.log(obj[0].rotations); 
	}
}
let c = {name:"jjkj",faceid:"ddd"};
console.log(c);


	</script>




	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<link rel="stylesheet" href="afterrensyu.css">
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
		<title>サーブを見る</title>
		<div class="uenoba">
	   <div class="header">
		   <div class="total">
			   <div class="total-child">目標達成率：<?php if(!empty($pasento[0]['pasento'])) echo $pasento[0]['pasento'];?>%</div>
			   <!--目標達成率-->
		   </div>
	   </div>
		</div>
	</head>

	<body>
		<form method = "get">
		<input type="hidden" name="user_id" value="<?php echo $user_id;?>">
		<input type="date"　 id="day" name="select_date" value="<?php echo $select_date = $post_date; ?>"> <input type="submit" name="date_change" value="日付を変更">
		</form>
		<div><a><?php echo $errorMessage; ?></a></div>
	   </br>
	   <ul class="nav nav-tabs" id="myTab" role="tablist">
		   <li class="nav-item">
			   <a class="nav-link active" data-toggle="tab" href="#tab0" role="tab" aria-controls="home" aria-expanded="true">動画1</a>
		   </li>
		   <li class="nav-item">
			   <a class="nav-link" data-toggle="tab" href="#tab1" role="tab" aria-controls="profile">動画2</a>
		   </li>
		   <li class="nav-item">
			   <a class="nav-link" data-toggle="tab" href="#tab2" role="tab" aria-controls="profile">動画3</a>
		   </li>
		   <li class="nav-item">
			   <a class="nav-link" data-toggle="tab" href="#tab3" role="tab" aria-controls="profile">動画4</a>
		   </li>
	   </ul>
	   <div class="tab-content" id="myTabContent">
		   <?php foreach($demojikkouti as $index => $d) { ?>
		   <div class="tab-pane fade<?php if ($index == 0) { echo ' show active';}?>" id="tab<?php echo $index;?>" role="tabpanel" aria-labelledby="<?php echo $index;?>-tab">
			   <div class="tab-pane-header">
				   <div class="video">
		　　　　　　　　　<iframe width="300" height="200" controls src="<?php echo $d['movie_URL'];?>"  frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
				   </div>
				   <div class="result">
					   <h5 class="speed" id="speedvalue"><!--速度表示--></h5></br>
				   </div>
				   <h4 class="will">サーブ結果</h4>
				   <p>速度：<?php echo $d['speed']; ?>m/s </p>
				   <p>ネット上の高さ：<?php echo $d['top']; ?>cm <br></br></p>

				   <h4 class="will">サーブの落下予測地点</h4>
				   <h8 class="will"><img src="img/bule-circle.png" width="20" height="20">:落下予測地点</br><img src="img/red-circle.png" width="20" height="20">:目標点</h8>
				   <h4> </h4>
				   <div style="position: relative;"><img src="<?php echo $court_img;?>" width="300" height="185" border="0" alt="バレーコート"　rotate="90">
					   <div style="position: absolute; top :<?php echo $d['x_px']?>px; left :<?php echo $d['y_px']?>px;"><img src="img/bule-circle.png" width="20" height="20" border="0" alt="落下予測地点"></div>
				   </div>
			   </div>
		<?php
		try
		{
			$db = new PDO(PDO_DSN, DB_USERNAME, DB_PASSWORD);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$stmt = $db->query("SELECT view_name,message,post_date FROM message WHERE practice_id =" .$d["id"]."  ORDER BY post_date DESC");
			$message_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
			$db = null;
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();
			exit;
		}
		?>
	   <div class="footer">
		   <div class="box26">
			   <span class="box-title">コメント</span>
			   <p>

			   <?php if( empty($_POST['btn_submit']) && !empty($_SESSION['success_message']) ): ?>
			   <p class="success_message"><?php echo $_SESSION['success_message']; ?></p>
			   <?php unset($_SESSION['success_message']); ?>
			   <?php endif; ?>

			   <?php if( !empty($error_message) ): ?>
			   <ul class="error_message">
				   <?php foreach( $error_message as $value ): ?>
				   <li>・<?php echo $value; ?></li>
				   <?php endforeach; ?>
			   </ul>
			   <?php endif; ?>
			   <form method = "post">
		　　　　	<input type="hidden" name="practice_id" value="<?php echo $d["id"]?>">
				   <div>
					   <label for="view_name">表示名</label></br>
		　　　　　　　　　<input id="view_name" type="text" name="view_name" value="<?php if( !empty($_SESSION['view_name']) ){ echo $_SESSION['view_name']; } ?>">
				   </div>
				   <div>
					   <label for="message">ひと言メッセージ</label></br>
		　　　　　　　　　<textarea id="message" name="message"></textarea>
				   </div>
				   <input type="submit" name="btn_submit" value="書き込む">
			   </form>

			   <hr>

			   <section>
				   <?php if( !empty($message_array) ){ ?>
				   <?php foreach( $message_array as $value ){ ?>
				   <article>
					   <div class="info">
						   <h2><?php echo $value['view_name']; ?></h2>
						   <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
					   </div>
					   <p><?php echo $value['message']; ?></p>
				   </article>
				   <?php } ?>
				   <?php } ?>
			   </section></p>
		   </div>
	   </div>
		   </div>
           <?php } ?>
	   </div>

	   </div>
	   <!-- Optional JavaScript -->
	   <!-- jQuery first, then Popper.js, then Bootstrap JS -->
	   <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	   <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
	   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>

	   
	   <script>
		   $(document).ready(function() {
			   // executes when HTML-Document is loaded and DOM is ready


			   if (location.hash !== '') $('a[href="' + location.hash + '"]').tab('show');
			   return $('a[data-toggle="tab"]').on('shown', function(e) {
				   return location.hash = $(e.target).attr('href').substr(1);
			   });


			   // document ready  
		   });
	   </script> 
	</body>
</html>