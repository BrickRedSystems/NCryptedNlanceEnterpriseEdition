<?php 
	require_once '../../includes-nct/config-nct.php';
	$reqAuth = true; 
	$_SESSION['random_number'] = md5(rand(1, 50));
	
	if($sessUserId == 0){
		redirectPage(SITE_URL);
	}
	else{	
		if(isset($_SESSION['random_number']) && $_SESSION['random_number']!=''){?>
		    <br /><br /><center><h1> <font color="#009900">{membership_plan_has_been_successfully_purchased}</font></h1><center>     
		  <?php   unset($_SESSION['random_number']);
			$_SESSION["msgType"] = disMessage(array(
                    'type' => 'suc',
                    'var' => succ_membership_plan_purchase
        	));
        	redirectPage(SITE_MEM_PLANS);  
       	}
		else{
			redirectPage(SITE_URL); 
		}
	}   
?> 