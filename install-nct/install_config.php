<?php
	//live - local
	if (strpos($_SERVER["SERVER_NAME"], '192.168.100.128') !== false OR strpos($_SERVER["SERVER_NAME"], 'localhost') !== false)
	{
		define("INSTALL_TYPE", 'local'); //

	}else{
		define("INSTALL_TYPE", 'live'); //
	}
