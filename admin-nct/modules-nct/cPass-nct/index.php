<?php

$reqAuth = true;
require_once("../../../includes-nct/config-nct.php");
require_once("class.cPass-nct.php");

$objPost = new stdClass();

$winTitle = 'Change Password - ' . SITE_NM;
$headTitle = 'Change Password';
$metaTag = getMetaTags(array("description" => "Admin Panel",
    "keywords" => 'Admin Panel',
    'author' => AUTHOR));

$module = 'cPass-nct';
$breadcrumb = array($headTitle);
chkPermission($module);

extract($_POST);

$objPost->opasswd = isset($opasswd) ? filtering($opasswd, 'input') : '';
$objPost->passwd = isset($passwd) ? filtering($passwd, 'input') : '';
$objPost->cpasswd = isset($cpasswd) ? filtering($cpasswd, 'input') : '';
$objPost->passvalue = isset($passvalue) ? filtering($passvalue, 'input') : '';

$objUser = new cPass();

if (isset($_POST["submitChange"])) {
    
    if ($objPost->opasswd != "" && $objPost->passwd != "" && $objPost->cpasswd != "") {
        $changeReturn = $objUser->submitProcedure();
        switch ($changeReturn) {
            case 'wrongPass' : $toastr_message = disMessage(array('from' => 'admin', 'type' => 'error', 'var' => 'Please enter correct current password.'));
                break;
            case 'passNotmatch' : $toastr_message = disMessage(array('from' => 'admin', 'type' => 'error', 'var' => 'Password and confirm password does not match. Please check.'));
                break;
            case 'succChangePass' : {
                    $_SESSION["toastr_message"] = disMessage(array('from' => 'admin', 'type' => 'suc', 'var' => 'Your password has been changed successfully.'));
                    redirectPage(SITE_ADM_MOD . $module);
                    break;
                }
        }
    }
}

$pageContent = $objUser->getPageContent();
require_once(DIR_ADMIN_TMPL . "parsing-nct.tpl.php");

?>