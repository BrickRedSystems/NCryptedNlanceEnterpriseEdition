<?php
	require_once('install_config.php');
	require_once('sql_import.php');

	set_time_limit(0);


	$site_url = $_POST['site_url'];
	$dir_name = $_POST['dir_name'];
	$db_host = $_POST['db_host'];
	$db_username = $_POST['db_username'];
	$db_pass = $_POST['db_pass'];
	$db_name = $_POST['db_name'];

	/* Local */
	error_reporting(E_ALL);
	define("DB_HOST", $db_host); // localhost
	define("DB_USER", $db_username); // root
	define("DB_PASS", $db_pass); //
	define("DB_NAME", $db_name); // netube

	define("PROJECT_DIRECTORY_NAME", $dir_name); // netube
	$dir_name_temp = "";
	if(INSTALL_TYPE == 'local'){
		if(isset($dir_name) && $dir_name != ""){
			$dir_name_temp = $dir_name.'/';
		}
		define('SITE_URL', 'http://' . $_SERVER["SERVER_NAME"] . '/'.$dir_name_temp);
		define('ADMIN_URL', SITE_URL . 'admin/');
		define('DIR_URL', $_SERVER["DOCUMENT_ROOT"] . '/'.$dir_name_temp);

		$rootfile = $_SERVER["DOCUMENT_ROOT"] . '/'.$dir_name_temp.'demo.txt';
		if(!file_exists($rootfile)){
			$handle = fopen($rootfile, 'w') or die('Cannot open file:  '.$rootfile);
			chmod($rootfile, 0777);
		}
	}else{
		define('SITE_URL', 'http://' . $_SERVER["SERVER_NAME"] . '/');
		define('ADMIN_URL', SITE_URL . 'admin/');
		define('DIR_URL', $_SERVER["DOCUMENT_ROOT"] . '/');

		$rootfile = $_SERVER["DOCUMENT_ROOT"] . '/demo.txt';
		if(!file_exists($rootfile)){
			$handle = fopen($rootfile, 'w') or die('Cannot open file:  '.$rootfile);
			chmod($rootfile, 0777);
		}
	}

	if(file_exists($rootfile)){
		// Create Database now using the above details by the user
		try {
			$servername = DB_HOST;
			$username = DB_USER;
			$password = DB_PASS;

			$db = new PDO("mysql:host=$servername;dbname=$db_name",$username,$password);

			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // <== add this line
			echo 'Connected to Database<br/>';

			/*$sql = file_get_contents('../database-nct/nlance3.2.sql');

			$qr = $db->exec($sql);*/
            $pdoclass = new PDODbImporter();

        	$sqlfile = DIR_URL.'database-nct/nlance3.2.sql';

        	$result = $pdoclass->importSQL($sqlfile,$db);

				$template_path = DIR_URL.'includes-nct/';
				$tfp = fopen($template_path."/database-nct.php","wb");
				$template_content = '
				<?php
					define("ENVIRONMENT", "p");//d- development, p- production
					define("DB_HOST", "'.$db_host.'");
					define("DB_USER", "'.$db_username.'");
					define("DB_PASS", "'.$db_pass.'");
					define("DB_NAME", "'.$db_name.'");
					define("PROJECT_DIRECTORY_NAME", "'.$dir_name.'");
					define("SITE_URL", "http://".$_SERVER["SERVER_NAME"]."/'.$dir_name_temp.'");
					define("ADMIN_URL", SITE_URL."admin-nct/");
					define("DIR_URL", $_SERVER["DOCUMENT_ROOT"]."/'.$dir_name_temp.'");
					define("D_KEY", "5c84348d4fac7b70a0df87b79fcb634f66443dfd21c23298565b400676a02b57");
					if(ENVIRONMENT == "d" ){
				        error_reporting(E_ALL | E_STRICT);

				    }else{
				        error_reporting(0);
				    }
				?>';
				fwrite($tfp,$template_content);
				fclose($tfp);
				if(INSTALL_TYPE == 'local'){
					header('Location: '.SITE_URL);
				}else{
					echo "<script type='text/javascript'>window.location.href = '".SITE_URL."';</script>";
				}
				exit;
		}
		catch(PDOException $e)
		{
			unlink($rootfile);
			echo "Connection failed: " . $e->getMessage();
			header('Location: '.SITE_URL.'/install');
		}
	}else{
			header('Location: '.SITE_URL.'/install');
	}
?>