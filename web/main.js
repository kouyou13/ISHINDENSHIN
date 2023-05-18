document.getElementById("day").onchange = function() { 
			    var date = document.getElementById("day").value;
			    //alert(date);
				$.ajax({
			    type: "GET", //　POSTでも可
			    url: "afterrensyu.php", //　送り先
			    data: { '日付': date }, //　渡したいデータ
			    dataType : "json", //　データ形式を指定
			    scriptCharset: 'utf-8' //　文字コードを指定
			})
			.then(
			    function( param ){　 //　paramに処理後のデータが入ってる
			        console.log( param ); //　帰ってきたら実行する処理
			    },
			    function( XMLHttpRequest, textStatus, errorThrown ){
			        console.log( errorThrown ); //　エラー表示
			});
						};