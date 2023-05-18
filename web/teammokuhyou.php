<?php
include 'db_config.php';  

$errorMessage = "";
$today =$_GET['date'];
$tomorrow = date("Y-m-d", strtotime($today.'+1 day'));
$face_id=$_GET['face_id'];
$db = new PDO(PDO_DSN, DB_USERNAME, DB_PASSWORD);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $db->query("SELECT * FROM team_mokuhyouti WHERE date='".$today."'");// チームの目標値テーブルを引っ張ってくる
$teammokuhyouti = $stmt->fetch(PDO::FETCH_ASSOC);
	$id = $teammokuhyouti['id'];
	$teammokuhyou_speed = $teammokuhyouti['teammokuhyou_speed'];
	$teammokuhyou_top = $teammokuhyouti['teammokuhyou_top'];
	$teammeichu_point = $teammokuhyouti['teammeichu_point'];
	$date = $teammokuhyouti['date'];     


	$stmt = $db->query("SELECT * FROM demo_mokuhyoutasseiritu WHERE date='".$today."'"); // 実行値テーブルを引っ張ってくる
	$mokuhyoutasseiritu = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach($mokuhyoutasseiritu as $index => $m)//実行値のあたいを配列
	{
		$id[$index] = $m['id'];
		$user_id[$index] = $m['user_id'];
		$maxspeed[$index] = $m['max_speed'];
		$besttop[$index] = $m['best_top'];
		$meichu_point[$index] = $m['meichu_point'];
		$date[$index] = $m['date'];
	}
	
	
	$stmt = $db->query("SELECT COUNT( * ) as users FROM demo_mokuhyoutasseiritu WHERE date='".$today."'"); // 実行値テーブルを引っ張ってくる
	$usercount = $stmt->fetch(PDO::FETCH_ASSOC);
	$sum_users = $usercount['users'];
	
	$teammokuhyouspeed_point = 0;
	$mokuhyoutop_point = 0;
	$mokuhyoutotal_point = 0;
	if($teammokuhyou_speed < 10){
		$mokuhyouspeed_point = 0;
	}else if($teammokuhyou_speed >= 10 && $teammokuhyou_speed < 11.5){
		$mokuhyouspeed_point = 1;
	}else if($teammokuhyou_speed >= 11.5 && $teammokuhyou_speed < 13){
		$mokuhyouspeed_point = 2;
	}else if($teammokuhyou_speed >= 13 && $teammokuhyou_speed < 14.5){
		$mokuhyouspeed_point = 3;
	}else if($teammokuhyou_speed >= 14.5 && $teammokuhyou_speed < 16){
		$mokuhyouspeed_point = 4;
	}else{
		$mokuhyouspeed_point = 5;
	}
	if($teammokuhyou_top >= 293 || $teammokuhyou_top < 243){
		$mokuhyoutop_point = 0;
	}else if($teammokuhyou_top >= 283 && $teammokuhyou_top < 293){
		$mokuhyoutop_point = 1;
	}else if($teammokuhyou_top >= 273 && $teammokuhyou_top < 283){
		$mokuhyoutop_point = 2;
	}else if($teammokuhyou_top >= 263 && $teammokuhyou_top < 273){
		$mokuhyoutop_point = 3;
	}else if($teammokuhyou_top >= 253 && $teammokuhyou_top < 263){
		$mokuhyoutop_point = 4;
	}else if($teammokuhyou_top >= 243 && $teammokuhyou_top < 253){
		$mokuhyoutop_point = 5;
	}
	$mokuhyoutotal_point = $mokuhyouspeed_point + $mokuhyoutop_point + 5;//3つ足した値をmokuhyoutotalとして格納


	$stmt = $db->query("SELECT AVG(max_speed) from demo_mokuhyoutasseiritu WHERE date='".$today."';"); // 平均速度取ってくる
	$heikinsokudo = $stmt->fetch(PDO::FETCH_ASSOC);
	$ave_speed = $heikinsokudo['AVG(max_speed)'];
	
	//$ave_speed=$sum_speed/$sum_users;//全ユーザの平均速度の算出
	
	$stmt = $db->query("SELECT AVG(best_top) from demo_mokuhyoutasseiritu WHERE date='".$today."';"); // 平均高さ取ってくる
	$heikintakasa = $stmt->fetch(PDO::FETCH_ASSOC);
	$ave_top = $heikintakasa['AVG(best_top)'];
	
	
	$stmt = $db->query("SELECT AVG(meichu_point) from demo_mokuhyoutasseiritu WHERE date='".$today."';"); // 命中ポイント取ってくる
	$heikinmeichu = $stmt->fetch(PDO::FETCH_ASSOC);
	$ave_meichu = $heikinmeichu['AVG(meichu_point)'];
	
	
	$total = 0;
	$teamspeed_point = 0;
	$teamtop_point = 0;
	$teamcourse_point =0;
	$team_mokuhyoutasseiritu =0;

	if($ave_speed < 10){
		$teamspeed_point = 0;
	}else if($ave_speed >= 10 && $ave_speed < 11.5){
		$teamspeed_point = 1;
	}else if($ave_speed >= 11.5 && $ave_speed < 13){
		$teamspeed_point = 2;
	}else if($ave_speed >= 13 && $ave_speed < 14.5){
		$teamspeed_point = 3;
	}else if($ave_speed >= 14.5 && $ave_speed < 16){
		$teamspeed_point = 4;
	}else if($ave_speed >=16){
		$teamspeed_point = 5;
	}

	if($ave_top >= 293 || $ave_top < 243){
		$teamtop_point = 0;
	}else if($ave_top >= 283 && $ave_top < 293){
		$teamtop_point = 1;
	}else if($ave_top >= 273 && $ave_top < 283){
		$teamtop_point = 2;
	}else if($ave_top >= 263 && $ave_top < 273){
		$teamtop_point = 3;
	}else if($ave_top >= 253 && $ave_top < 263){
		$teamtop_point = 4;
	}else if($ave_top >= 243 && $ave_top < 253){
		$teamtop_point = 5;
	}



	//meichupoint
	if($ave_meichu >= 33){
		$teamcourse_point = 0;
	}else if(27.5 <=$ave_meichu && $ave_meichu < 33){
		$teamcourse_point = 1;
	}else if(16.5 <=$ave_meichu && $ave_meichu < 27.5){
		$teamcourse_point = 2;
	}else if(11 <=$ave_meichu && $ave_meichu < 16.5){
		$teamcourse_point = 3;
	}else if(5.5 <=$ave_meichu && $ave_meichu < 11){
		$teamcourse_point = 4;
	}else if(0 <=$ave_meichu && $ave_meichu < 5.5){
		$teamcourse_point = 5;
	}


	$total = $teamspeed_point + $teamtop_point + $teamcourse_point;//3つ足した値をtotalとして格納



	$team_mokuhyoutasseiritu = $total/$mokuhyoutotal_point*100;

	$stmt=$db->query("SELECT * FROM team_hyouka WHERE team_mokuhyoupasento=$team_mokuhyoutasseiritu AND team_point=$total AND date='$today'");
  	$temp=$stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach($temp as $t){
		$team_hyouka[] = $t['id'];
	}
	if(empty($team_hyouka[0])){
		$stmt = $db->prepare("INSERT INTO team_hyouka (`team_mokuhyoupasento`,`team_point`,`date`) VALUES(?,?,?)");
		$stmt->execute([$team_mokuhyoutasseiritu,$total,$today]);
	}



	$next_teammokuhyouspeed = 0;//次の速度
	$next_teamkuhyoukatop = 0;//次の高さ
	$next_teammokuhyoumeichu=0;     
	if ($mokuhyoutotal_point<=$total)
	{
		$next_teammokuhyouspeed =$ave_speed *1.03;
		$next_teammokuhyoutop =$ave_top; 
		$next_teammokuhyoumeichu=5;          
	}else{
		$next_teammokuhyouspeed=$teammokuhyou_speed; 
		$next_teammokuhyoutop=$teammokuhyou_top;
		$next_teammokuhyoumeichu=5;
	}

	$stmt=$db->query("SELECT * FROM team_mokuhyouti WHERE teammokuhyou_speed=$next_teammokuhyouspeed AND teammokuhyou_top=$next_teammokuhyoutop AND teammeichu_point=$next_teammokuhyoumeichu AND date=$tomorrow");
  	$temp=$stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach($temp as $s){
		$team_mokuhyouti[] = $s['id'];
	}
	if(empty($team_mokuhyouti[0])){
		$stmt = $db->prepare("INSERT INTO team_mokuhyouti (`teammokuhyou_speed`,`teammokuhyou_top`,`teammeichu_point`,`date`) VALUES(?,?,?,?)");
		$stmt->execute([$next_teammokuhyouspeed,$next_teammokuhyoutop,$next_teammokuhyoumeichu,$tomorrow]);
	}
	?>
      <?php
        
          //ユーザ毎のデータをDBから抽出
          try {
            //目標値
          	$stmt = $db->prepare("SELECT `team_point`, `date` FROM `team_hyouka` ORDER BY `date` ASC;");
            $stmt->execute();
            $rows = $stmt->fetchAll();
            
            /* 
             * array_map
             * 第二パラメータの配列の全要素を第一パラメータの関数に入力した新たな配列を生成する
             */
            
            $team_dates = array_map(
              function ($v) {
                  return $v['date'];
              }, $rows
            );
			
            $teamtotal = array_map(
              function ($v) {
                  return (int)$v['team_point'];
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

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
	  <link rel="stylesheet" href="team.css">
      <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>今日のチームの目標達成率</title>
</head>

<body>
	
<div class="head">
      <img src="img/rogo.png" width="400" height="90" border="0" alt="ロゴ">
</div>
<a><?php if(empty($mokuhyoutasseiritu)) echo"データがありません。日付を変更してください。";?></a>
<div class="percent">
	<div class="goal">
	 <p>目標達成率：<?php if(!empty($team_mokuhyoutasseiritu)) echo floor($team_mokuhyoutasseiritu);?></p>
   </div>
</div>
<div class="all">
    <div class="ue1">
		<p>今日のチームの目標</p>
		<p>目標値合計点数：<?php if(!empty($mokuhyoutotal_point)) echo $mokuhyoutotal_point;?></p>
		<p>目標速度：<?php if(!empty($teammokuhyou_speed)) echo floor($teammokuhyou_speed);?>m/s</p>
		<p>目標の高さ：<?php if(!empty($teammokuhyou_top)) echo floor($teammokuhyou_top);?>cm</p>   
    </div>
        
    <div class="ue2">
		<p>今日のチーム評価</p>
        <p>チーム全体の点数：<?php if(!empty($total)) echo $total;?></p>
		<p>平均速度：<?php if(!empty($ave_speed)) echo floor($ave_speed);?>m/s</p>
		<p>高さの平均：<?php if(!empty($ave_top)) echo floor($ave_top);?>cm</p>
		<p>命中ポイントの平均：<?php if(!empty($ave_meichu)) echo floor($ave_meichu);?></p>
    </div>
    <div class="sita1">
        <hr>
        <p>次回のチーム目標値</p>
        <p>次回の目標平均速度:<?php if(!empty($next_teammokuhyouspeed)) echo floor($next_teammokuhyouspeed);?>m/s</p>
        <p>次回の目標平均高さ:<?php if(!empty($next_teammokuhyoutop)) echo floor($next_teammokuhyoutop);?>cm</p>
                <hr><p><br>チームスコアの推移</p>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.bundle.min.js"></script>
              <canvas id="line-chart" width="100px" height="50px"></canvas>
		
      <script>
        
			  var context = document.getElementById('line-chart').getContext('2d');
			  var line_chart = new Chart(context, {
			    type:'line', // グラフのタイプを指定
			    data:{
			      labels:<?php echo json_encode($team_dates); ?>, // グラフ下部のラベル
			      datasets:[{
              label:'チームスコア',  // データのラベル
				  	  data:<?php echo json_encode($teamtotal); ?>, // グラフ化するデータの数値
			          fill:false, // グラフの下部を塗りつぶさない
			          borderColor:'rgb(50,144,229)'
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
	</div>
</div>
<div class = botton>
		<form action="https://web2-17423.azurewebsites.net/ISHINDENSHIN/web.php?face_id=<?php echo $face_id;?>">
		<input id="submit_button" type="submit" name="all"value="選手一覧に戻る">
		<input type='hidden' name='face_id' value=<?php  $face_id;?>>
    	<input type='hidden' name='select_date' value=<?php echo $today;?>></form>
		<br>
</div>

</body>
</html>