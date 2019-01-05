<?php
$reqAuth = false;
$module  = 'unsubscribe-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.unsubscribe-nct.php";

extract($_REQUEST);
$winTitle = $headTitle = 'Unsubscribe ' . SITE_NM;

$metaTag = getMetaTags(array(
    "description" => $winTitle,
    "keywords"    => $headTitle,
    "author"      => AUTHOR,
));

$subscriberId = issetor($subscriberId) ? $subscriberId : "";
if($subscriberId != ""){
    $subscriberId = base64_decode($subscriberId);
    $subscriberId = str_replace('nct_','', $subscriberId);
    $db->pdoQuery("DELETE FROM  tbl_subscribers WHERE id = ?",array($subscriberId));
}else{
    $msgType = $_SESSION["msgType"] = disMessage(array(
        'type' => 'err',
        'var'  => Something_went_wrong,
    ));
    redirectPage(SITE_URL);
}


$obj = new Unsubscribe($module, 0, issetor($token));

$pageContent = $obj->getPageContent();

require_once DIR_TMPL . "parsing-nct.tpl.php";
