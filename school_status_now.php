<?php include("../config.php"); ?>

<!DOCTYPE html>
<html>
<head>
<style>

/*定義說明文字*/
.readme {
    border-width:1px;
    margin:0px auto;
        border-style:solid;
    background-color: rgba(255, 217, 8, 1);
    width: 500px;
    height: 60px;
        vertical-align: middle;
        text-align: center;
        font-family:Microsoft JhengHei;
        font-size:small;
        float:;
}



/* 定義設備正常*/

.device_up { 
    border-width:1px; 
	border-style:solid;
	margin-left:2px;
	margin-right:2px;
	margin-top:2px;
	margin-bottom:2px;
    background-color: rgba(66, 244, 164, 1); 
    border-radius: 3%; 
    -webkit-border-radius: 3%; 
    -moz-border-radius: 3%;
    width: 200px;
    height: 36px;
	vertical-align: middle;
	text-align: center;
	font-family:Microsoft JhengHei;
	font-size:small;
	float:left;
}




/* 定義設備故障*/
.device_down {
    border-width:1px; 
	border-style:solid;
	margin-left:2px;
	margin-right:2px;
	margin-top:2px;
	margin-bottom:2px;
    background-color: rgba(244, 66, 66, 1); 
    border-radius: 3%; 
    -webkit-border-radius: 3%; 
    -moz-border-radius: 3%;
    width: 200px;
    height: 36px;
	vertical-align: middle;
	text-align: center;
	font-family:Microsoft JhengHei;
	font-size:small;
	float:left;
   }

</style>
</head>
<body>
<br><br>
<div class="readme">
說明：本頁面建置於市網中心，檢測各校與市網介接之L3路由設備
<br>檢測方式：每隔60秒ping 1 次，ping回應時間高於100ms即為ping loss
<br>異常定義：最近連續兩次ping皆loss則為異常，顯示紅色


</div>
<br><br>

<?php
$DBNAME = $config['db_name'];
$DBUSER = $config['db_user'];
$DBPASS = $config['db_pass'];
$DBHOST = $config['db_host']

try
{
    //查詢devices資料表中所有設備的id,名稱 
    $pdo      = new PDO("mysql:host=".$DBHOST.";dbname=".$DBNAME, $DBUSER, $DBPASS);
    $pdo->query("set names utf8");
    $sql      = "select device_id,sysName from devices order by sysName";
    $query    = $pdo->query($sql);
    $datalist = $query->fetchAll();
    foreach ($datalist as $datainfo)
    {
    //從device_id去比對device_perf中最後一筆ping的資料
        $pdo2      = new PDO("mysql:host=".$DBHOST.";dbname=".$DBNAME, $DBUSER, $DBPASS);
        $sql2 = "select devices.device_id,devices.features,devices.sysName,devices.hostname,device_perf.timestamp,device_perf.loss  from devices JOIN device_perf on devices.device_id=device_perf.device_id  where  device_perf.device_id =". $datainfo['device_id']. " order by device_perf.timestamp desc,devices.sysName  limit 1;";
        $query2    = $pdo2->query($sql2);
        $datalist2 = $query2->fetchAll();
             foreach ($datalist2 as $datainfo2)
             {
    //計算每一部裝置最近兩筆的ping資料
                  $pdo3      = new PDO("mysql:host=".$DBHOST.";dbname=".$DBNAME, $DBUSER, $DBPASS);
                  $STH_SELECT = $pdo3->query("SELECT sum(loss) as sum_loss from(SELECT loss FROM device_perf  where device_id =".$datainfo['device_id'] ." order by device_perf.timestamp limit 2) as subquery");
                  $row =  $STH_SELECT->fetch();
                  //echo $row['sum_loss'];
    //總和為0是正常
                  if ($row['sum_loss'] == 0){
                     $school_status="正常";
                     $div_class="device_up";
                  }
    //總和200代表連續兩次ping失敗
                  if ($row['sum_loss'] >= 200){
                     $school_status="異常";
                     $div_class="device_down";


                  }

                  //if ($datainfo2['loss'] == 100) $school_status="異常";
                  //if ($datainfo2['loss'] == 100) $div_class="device_down";
                  //if ($datainfo2['loss'] == 0) $school_status="正常";
                  //if ($datainfo2['loss'] == 0) $div_class="device_up";

                  echo "<div class=".  $div_class  .">";
                  //echo  $row['sum_loss'];
                  echo  $datainfo['sysName'].$school_status;
                  echo "<br>檢測時間：".$datainfo2['timestamp'];
                  //echo "<br>檢測IP：".$datainfo2['hostname'];
                  //echo "<br>電路編號：".$datainfo2['features'];
                  echo "</div>";
                  

             }

    }


}
catch(Exception $e)
{
    // 錯誤顯示
    echo $e->getMessage();
}

?>


</body>
</html>
