<?php	
	require_once '../../includes-nct/config-nct.php';			
	$reqAuth = true; 
	if($sessUserId == 0){
		redirectPage(SITE_URL);
	}
	else{	
		if(isset($_SESSION['random_number']) && $_SESSION['random_number']!=''){?>
        	<br /><br /><center><h1> <font color="#FF0000">Beginning to cancel transaction......</font></h1><center>	
			<?php   unset($_SESSION['random_number']);
			 $_SESSION["msgType"] = disMessage(array(
                    'type' => 'suc',
                    'var' => "Successfully cancel transaction."
        	));
        	redirectPage(SITE_URL.'membership-plans/');  
        }
		else{
			redirectPage(SITE_URL); 
		}
	}
?>