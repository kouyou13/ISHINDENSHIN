<?php
include 'db_config.php';
$face_id=$_GET['face_id'];
if(empty($_GET["select_date"])){
	$post_date=date('Y-m-d');
}
else if(!empty($_GET["select_date"])){
	$post_date=$_GET["select_date"];
}

try{
  $db=new PDO(PDO_DSN,DB_USERNAME,DB_PASSWORD);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$stmt = $db->query("SELECT * FROM users");
	$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
 } catch(PDOException $e) {
  echo $e->getMessage();
  exit;
}

$user_index = array_search($face_id, $users);
/* 
 * array_search
 * 第2パラメータの配列から値が第1パラメータである要素の最初のインデックスを返す
 */
$user_id = $users[$user_index]['id'];
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="styles.css">
  <title>選手一覧ページ</title>
</head>
<body>
  <!-- ▼使用ライブラリの読み込み▼ -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.bundle.min.js"></script>
  <!-- ▲ここまで▲ -->
  
  <div class="search"> 
    <img src="img/ishindenshin.png" alt="システム名" width="300px" height="150px">
    <!--アプリに飛ぶ-->
    <input type="button" class="start" onclick="location.href='ishindenshin://'" value="練習開始！">
    <ul>
      <li>選手名検索<br>
        <div class="wrapper">
          <div class="search-area">
            <form> <input type="text" id="search-text" placeholder="選手名を入力"> </form>
            <div class="search-result">
              <div class="search-result__hit-num"></div>
              <div id="search-result__list"></div>
            </div>
          </div>
          
          <script>
            $(function () {
            		searchWord = function(){
            		var searchText = $(this).val(), // 検索ボックスに入力された値
            		targetText;
            
            		$('.target-area li').each(function() {
            				targetText = $(this).text();
            
            				// 検索対象となるリストに入力された文字列が存在するかどうかを判断
            				if (targetText.indexOf(searchText) != -1) {
            				$(this).removeClass('hidden');
            				} else {
            				$(this).addClass('hidden');
            				}
            				});
            		};
            
            		// searchWordの実行
            		$('#search-text').on('input', searchWord);
            		});
          </script>
          <script>
            $(function () {
            		searchWord = function(){
            		var searchText = $(this).val(), // 検索ボックスに入力された値
            		targetText;
            
            		$('.target-area li').each(function() {
            				targetText = $(this).text();
            
            				// 検索対象となるリストに入力された文字列が存在するかどうかを判断
            				if (targetText.indexOf(searchText) != -1) {
            				$(this).removeClass('hidden');
            				} else {
            				$(this).addClass('hidden');
            				}
            				});
            		};
            
            		// searchWordの実行
            		$('#search').on('input', searchWord);
            		});
          </script>
      </li>
      <li>日付選択<br>
        <form action="web.php" method="get"> <input type="hidden" name="face_id" value="<?php echo $face_id;?>"> <input type="date" id="day" name="select_date" value="<?php echo $select_date = $post_date; ?>"> <input id="submit_button" name="date_change" type="submit" value="変更"> </form>
      </li><br> </ul> <br>
    <form action="teammokuhyou.php?date=<?php echo $post_date;?>" method="post"> <input id="submit_button" type="submit" name="team" value="チーム評価"><input type="hidden" name="face_id" value="<?php echo $face_id;?>"></form> <br>
    <form action="mokuhyoutasseiritu.php?date=<?php $date=$post_date; echo $date;?>&user_id=<?php echo $user_id;?>" method="post"> <input id="submit_button" type="submit" name="team_target" value="目標達成率"><input type="hidden" name="face_id" value="<?php echo $face_id;?>"></form> <br> </div>
    <div class="main">
      
      <!--ここ以下をユーザーの数分まわす-->
      <?php foreach($users as $user) {
        
          //ユーザ毎のデータをDBから抽出
          try {
            //目標値
          	$stmt = $db->prepare("SELECT mokuhyou_total,date FROM demomokuhyouti WHERE user_id = ? ORDER BY `date` ASC;");
            $stmt->bindValue(1, $user['id'], PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            
          	$target_values = array_map(
              function ($v) {
                  return (int)$v['mokuhyou_total'];
              }, $rows
            );
            /* 
             * array_map
             * 第二パラメータの配列の全要素を第一パラメータの関数に入力した新たな配列を生成する
             */
            
            $target_dates = array_map(
              function ($v) {
                  return $v['date'];
              }, $rows
            );
            
            //達成率
            $stmt = $db->prepare("SELECT total, date FROM demo_mokuhyoutasseiritu WHERE user_id = ? ORDER BY `date` ASC;");
            $stmt->bindValue(1, $user['id'], PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            
            $measurements = array_map(
              function ($v) {
                  return (int)$v['total'];
              }, $rows
            );
            
          	// $measurements = $stmt->fetchColumn();
            /*
             * PDOStatement::fetchColumn()
             * …実行したSQLの結果から指定列のデータのみを取り出す
             * 引数は0から始まる整数値で何番目のカラムか指定、省略すると最初のやつ
             * データ一個のとき配列にならんからやめた
            */
          
           } catch(PDOException $e) {
            echo $e->getMessage();
            exit;
          }
      ?>
      <ul class="target-area">
        <li class="list_item" data-percent="success">
          <div class="player">
            <div class="name"><?php echo $user['name']; ?></div>
            <div hidden id="success_id<?php echo $user['id']; ?>"></div>
            <div class="pictures">
              <div>
                <img width="200" height="300" src="data:image/png;base64,<?php echo base64_encode($user['img']); ?>" />
              </div>
              <canvas id="line-chart-user<?php echo $user['id']; ?>" width="100px" height="50px"></canvas>
		
      <script>
        
			  var context = document.getElementById('line-chart-user<?php echo $user['id']; ?>').getContext('2d');
			  var line_chart = new Chart(context, {
			    type:'line', // グラフのタイプを指定
			    data:{
			      labels:<?php echo json_encode($target_dates); ?>, // グラフ下部のラベル
			      datasets:[{
              label:'目標値',  // データのラベル
				  	  data:<?php echo json_encode($target_values); ?>, // グラフ化するデータの数値
			          fill:false, // グラフの下部を塗りつぶさない
			          borderColor:'rgb(50,144,229)'
            }, // 線の色
					  
					  {
              label:'サーブポイント',  // データのラベル
			        data:<?php echo json_encode($measurements); ?>, // グラフ化するデータの数値
			        fill:false, // グラフの下部を塗りつぶさない
			        borderColor:'rgb(255, 52, 84)'
            }, // 線の色
			      ]},
			    options:{
			      scales:{
			        xAxes:[{
                //x軸を時系列データとして扱う
                type: 'time',
                time: {
                  unit: 'day'
                }
              }],
              yAxes:[{
			          ticks:{
			            min:0, // グラフの最小値
			          }
			        }]
			      },
			      elements:{
			        line:{
			          tension: 0 // 線グラフのベジェ曲線を無効にする
			        }
			      }
				  }
          });
				  </script>         
              <form action="afterrensyu.php?user_id=<?php echo $user['id']; ?>&select_date=<?php $select_date=$post_date; echo $select_date;?>" method="post"> <input id="submit_button" type="submit" onclick="location:href='afterrensyu.php://'" name="serve01" value="サーブを見る"></form>
              </div>
            <hr>
        </li>
      </ul>
      <!--ここまで-->
      <?php } ?> </div>
    </div>
    <script type="text/javascript" src="main.js"></script>
</body>
</html>