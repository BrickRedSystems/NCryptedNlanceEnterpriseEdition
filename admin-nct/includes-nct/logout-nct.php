<?php
require_once("../../includes-nct/config-nct.php");	
if(isset($_SESSION["adminUserId"]) && $_SESSION["adminUserId"] != "") {

	unset($_SESSION["adminUserId"]);
	unset($_SESSION["sessCataId"]);	
	$_SESSION["sessCataId"] = $_SESSION["adminUserId"] = '';
	$toastr_message = array('from'=>'admin','type'=>'suc','var'=>'succLogout');
	/*$qry = "UPDATE tbl_admin SET where uName = ?";
	$db->pdoQuery(array('admin'));	*/
	//$db->update("tbl_admin",array("sess_id "=>0),array("id"=>$_SESSION["adminUserId"]));
}
redirectPage(SITE_ADM_MOD.'login-nct/');
?>
