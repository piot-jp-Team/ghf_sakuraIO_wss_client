<?php

    require __DIR__ . '/vendor/autoload.php';

    $module_id = "xxxxyyyyzzzz";    
    $keplv=0;
	//下記の行はsakura.ioのwebsocketURLに置き換えて下さい
    \Ratchet\Client\connect('wss://api.sakura.io/ws/v1/xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx')->then(function($conn) use (&$keplv, &$module_id) {
        $conn->on('message', function($msg) use ($conn, &$keplv, &$module_id) {
            if(strpos($msg,'channels') !== false){
				$obj = json_decode($msg);
			    print $obj->{'module'}."\n";
					//下記の行は使用するmysqlに置き換えて下さい
				$mysqli = new mysqli('localhost', 'db_user', 'db_password', 'db_name');
				if ($mysqli->connect_error) {
				    echo $mysqli->connect_error;
				} else {
				    $mysqli->set_charset("utf8");
				}
				$cnt=count($obj->{'payload'}->channels);
				for ($i = 0; $i < $cnt; $i++){
					$dt = date('Y-m-d',  strtotime(substr($obj->{'payload'}->channels[$i]->{'datetime'},0,19)));
					$tm = date('H:i:s',  strtotime(substr($obj->{'payload'}->channels[$i]->{'datetime'},0,19)));
					$dtm = date('Y-m-d H:i:s',  strtotime(substr($obj->{'payload'}->channels[$i]->{'datetime'},0,19)));
					$sql = "insert into sensdatas (sensoer_id, sddate, sdtime, sddatetime, sddvalue, sdivalue, ctgain, ctoffset, sdflug, project_id, created_at, updated_at) ";
					$sql .= "select ss.id as sensor_id , '". $dt ."' as sddate, '". $tm ."' as sdtime , '". $dtm ."' as sddatetime, ";
					$sql .= "(". $obj->{'payload'}->channels[$i]->{'value'} ." * ss.ctgain + ss.ctoffset) as sddvalue, ". $obj->{'payload'}->channels[$i]->{'value'} ." as sdivalue, ";
					$sql .= "ss.ctgain, ss.ctoffset, 0 as sdflug , ss.project_id , ";
					$sql .= "now() as created_at, now() as updated_at ";
					$sql .= "from sensors as ss ";
					$sql .= "inner join sensunits as unt on ss.sensunit_id = unt.id ";
					$sql .= "inner join shieldmodules as mdl on unt.shield_id = mdl.id ";
					$sql .= "where mdl.module_id = '". $obj->{'module'} ."' and ss.address = ". $i .";";
					print $sql."\n";
					$result = $mysqli->query($sql);
					if (!$result) {
					    print('Insertクエリーが失敗しました。'.$mysqli->error."\n");
					}
				}
				$close_flag = $mysqli->close();;
				if ($close_flag){
				    print('DB切断に成功しました。'."\n");
				}
			}else{
				if(strpos($msg,'"is_online":')>0){
					echo "{$msg}\n";
				}
				if(strpos($msg,'"keepalive"')>0){
					file_put_contents("keepalive.txt", $msg);
				}
			}

		});

		$conn->on('close', function($code = null, $reason = null) {
			echo "Connection closed ({$code} - {$reason})\n";
		});

		$conn->send('{"type": "channels", "module": "'. $module_id .'", "payload": {"channels": [{"channel": 1, "type": "i", "value": 3 }]}}');//何もしませんがデータを送信してみてます。

	}, function ($e) {
		echo "Could not connect: {$e->getMessage()}\n";
	});

