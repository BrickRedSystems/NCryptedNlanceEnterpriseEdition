<?php
$reqAuth = false;
$module = 'registration-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.registration-nct.php";

extract($_REQUEST);

$winTitle =$headTitle= Signup.' - ' . SITE_NM;
$metaTag = getMetaTags(array(
	"description" => $winTitle,
	"keywords" => $headTitle,
	"author" => AUTHOR
));

$obj = new Registration($module, 0, issetor($token));

if (isset($_REQUEST['userType'])) {

///////////////////
    if (!checkFormToken($_REQUEST['token'])) {
        $_SESSION["msgType"] = disMessage(array(
            'type' => 'err',
            'var'  => Security_token_for_this_action_is_invalid_Please_refresh_and_try_again,
        ));
        redirectPage(SITE_USERTYPE);
    }
///////////////////

    $userType = (isset($_REQUEST['userType']) && strtolower($_REQUEST['userType']) == 'customer') ? 'c' : 'p';
    if ($userType != null) {
        $db->update('tbl_users', array('userType' => $userType), array('userId' => $sessUserId));
        $_SESSION['userType'] = $userType;
        redirectPage(SITE_URL);
    }
}

$pageContent = $obj -> getselectUserType();

require_once DIR_TMPL . "parsing-nct.tpl.php";
?>