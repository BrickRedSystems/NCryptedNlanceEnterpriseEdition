<?php

	error_reporting(0);
	require_once('install_config.php');

	function pri($data = array(),$exit = true) {
	    echo "<pre>";
	    print_r($data);
	    (($exit)?exit():'');

	}
	function curl_get_contents($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }
	$content = '';
	$content .=  '<div class="verifyresponsedata">';
	if(PHP_VERSION >= 5.6 && PHP_VERSION <= 7.2){
		$installflagphp = 1;
		$content .=  'Your PHP version: '.PHP_VERSION.' - OK';
		$content .=  '<br>';
	} else {
		$installflagphp = 0;
		$content .=  'PHP version Requirement doesn\'t match! - Failed';
		$content .=  '<br>';
	}
		extract($_REQUEST);

	if(INSTALL_TYPE == 'local'){
		try {
			$db = new PDO("mysql:host=$db_host;dbname=$db_name",$db_username,$db_pass);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // <== add this line
			$checkConnection = 1;

			//echo 'Connected to Database<br/>';
			if( $db->getAttribute(constant("PDO::ATTR_SERVER_VERSION")) >= 5){
				$installflagmysql = 1;
				$content .= "Your MySQL server version: ".  $db->getAttribute(constant("PDO::ATTR_SERVER_VERSION"));
				$content .=  ' - OK';
				$content .=  '<br>';
			} else {
				$installflagmysql = 0;
				$content .=  'MySQL Server Version Requirement doesn\'t match! - Failed';

				$content .=  '<br>';
			}
		}
		catch(PDOException $e)
		{
			$checkConnection = 0;

			$installflagmysql = 0;
			$content .= "Connection failed: " . $e->getMessage() .' - Failed';
			$content .=  '<br>';
		}

		$apachefullversion = apache_get_version();
		$apachesplit = explode(' ', $apachefullversion, 2);
		$apacheversion = explode('/', $apachefullversion, 2);
		$apachever = explode(' ', $apacheversion[1], 2);

		if($apachever[0] >= 1 && $apachever[0] < 3){
			$installflagapache = 1;
			$content .= "Your Apache Server version: ". $apachever[0];
			$content .=  ' - OK';
			$content .=  '<br>';
		} else {
			$installflagapache = 0;
			$content .=  'Apache Server Version Requirement doesn\'t match! - Failed';

			$content .=  '<br>';
		}

		ini_set('max_execution_time', 200);

		$max_execution_time = ini_get("max_execution_time");
		if($max_execution_time >= 180 || $max_execution_time == 0){
			$installflagmax = 1;
			$content .=  'Max Execution Time: '.$max_execution_time.' - OK';
			$content .=  '<br>';
		} else {
			$installflagmax = 0;
			$content .=  'You have a less execution time! - Failed';
			$content .=  '<br>';
		}

	}else{

		try {
			$db = new PDO("mysql:host=$db_host;dbname=$db_name",$db_username,$db_pass);

			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$checkConnection = 1;

		}
		catch(PDOException $e)
		{
			$checkConnection = 0;
			$content .= "Connection failed: " . $e->getMessage() .' - Failed';
			$content .=  '<br>';
		}


		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

		$file = @file_get_contents($protocol. $_SERVER["SERVER_NAME"]."/phpinfo.php");

		if(strlen($file) <= 0 && $file == ''){
			$file = curl_get_contents($protocol.$_SERVER["SERVER_NAME"]."/phpinfo.php");
		}

		if(PHP_VERSION >= 5.6 && PHP_VERSION <6){
			$start = explode("<h2><a name=\"module_mysql\">mysql</a></h2>",$file,1000);
		}else if (PHP_VERSION >= 7 && PHP_VERSION <= 7.2){
			$start = explode("<h2><a name=\"module_mysqli\">mysqli</a></h2>",$file,1000);

		}else{

		}

		if(count($start) < 2){
		    $installflagmysql = 0;
		    $content .= "MySQL is not on this server - Failed";
		    $content .=  '<br>';

		} else {
		    $again = explode("<tr><td class=\"e\">Client API version </td><td class=\"v\">",$start[1],1000);
		    $last_time = explode(" </td></tr>",$again[1],1000);
		    //echo "MySQL Version: <b>".$last_time[0]."</b>";
		    $mysqlsplit = explode(' ', $last_time[0], 2);
		    $mysqlsplit_1 = explode('-', $mysqlsplit[0], 2);
		    $mysqlsplit_2 = explode('-', $mysqlsplit[1], 2);
		    //print_r($mysqlsplit);

		    if($mysqlsplit_1[0] >= 5){
		    	$installflagmysql = 1;
		    	$content .= "Your MySQL server version: ". $mysqlsplit_1[0];
		    	$content .=  ' - OK';
		    	$content .=  '<br>';
		    }else if($mysqlsplit_2[0] >= 5){
		        $installflagmysql = 1;
		    	$content .= "Your MySQL server version: ". $mysqlsplit_2[0];
		    	$content .=  ' - OK';
		    	$content .=  '<br>';
		    }  else {
		    	$installflagmysql = 0;

				$content .=  'MySQL Server Version Requirement doesn\'t match! - Failed';

		    	$content .=  '<br>';
		    }
		}

		$startapache = explode("<tr class=\"h\"><th colspan=\"2\">SAPI Modules</th></tr>",$file,1000);
		if(count($startapache) < 2){
		    	$installflagapache = 0;
			$content .= "Apache is not on this server - Failed";
		    	$content .=  '<br>';
		} else {

			if(PHP_VERSION >= 5.6 && PHP_VERSION <6){
				$againapache = explode("<tr><td class=\"e\">Apache 2.0 Filter </td><td class=\"v\">",$startapache[1],1000);
				$last_time_apache = explode(" </td></tr>",$againapache[1],3);


				//echo "Apache Version: <b>".$last_time_apache[1]."</b>";
				$apachesplit = explode(' ', $last_time_apache[1], 2);

				//print_r($apachesplit); die;
				$apachesplit = explode(' ', $apachesplit[1], 2);
				$apachesplit = explode(' ', $apachesplit[1], 2);

			}else if (PHP_VERSION >= 7 && PHP_VERSION <= 7.2){
				$againapache = explode("<td class=\"e\">Apache 2.0 Handler </td><td class=\"v\">",$startapache[1],1000);

				$last_time_apache = explode(" </td></tr>",$againapache[1],3);
				//echo "Apache Version: <b>".$last_time_apache[1]."</b>";
				$apachesplit = explode(' ', $last_time_apache[0],7);
				//print_r($apachesplit); die;
				$apachesplit = explode(' ', $apachesplit[6], 2);
				$apachesplit = explode(' ', $apachesplit[1], 2);


			}else{

			}

		    if($apachesplit[0] >= 1 && $apachesplit[0] < 3){
		    	$installflagapache = 1;
		    	$content .= "Your Apache Server version: ". $apachesplit[0];
		    	$content .=  ' - OK';
		    	$content .=  '<br>';
		    } else {
		    	$installflagapache = 0;

				$content .=  'Apache Server Version Requirement doesn\'t match! - Failed';

		    	$content .=  '<br>';
		    }

		}

			set_time_limit(0);


		$max_execution_time = ini_get("max_execution_time");
		if($max_execution_time >= 180 || $max_execution_time == 0){
			$installflagmax = 1;
			$content .=  'Max Execution Time: '.$max_execution_time.' - OK';
			$content .=  '<br>';
		} else {
			$installflagmax = 0;
			$content .=  'You have a less execution time! - Failed - '.$max_execution_time;
			$content .=  '<br>';
		}
	}

	$allow_url_fopen = ini_get('allow_url_fopen');
	if($allow_url_fopen == 1){
		if($allow_url_fopen == 1) $allow_url_fopen = 'Yes'; else $allow_url_fopen = 'No';
		$installflagallowurl = 1;
		$content .=  'Is URL FOPEN Allowed: '.$allow_url_fopen.' - OK';
		$content .=  '<br>';
	} else {
		$installflagallowurl = 0;
		$content .=  'Please turn this setting to ON! - Failed';
		$content .=  '<br>';
	}

	if( $installflagphp == 1 && $installflagmysql == 1 && $installflagapache == 1 && $installflagmax == 1 && $installflagallowurl == 1 && $checkConnection == 1){
		$ready = 1;
		$content .=  '<strong>Ready to Install!</strong>';
	} else {
		$ready = 0;
		$content .=  '<strong>Cannot INSTALL! Please configure the server as requested!</strong>';
	}



	$json['status'] = true;
	$json['ready'] = $ready;

	$content .=  '<div>';

	$json['content'] = $content;
	echo json_encode($json);
?>