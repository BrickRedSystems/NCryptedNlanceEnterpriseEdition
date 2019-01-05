<?php

$reqAuth = false;
$module = 'content-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.content-nct.php";

$slug = isset($_GET["pageSlug"]) ? $_GET["pageSlug"] : 0;
$result = $db -> select("tbl_content", array("*"), array("pageSlug" => $slug));
if ($result -> affectedRows() == 0) {    
    redirectPage(SITE_URL);
}
else {
    $result = $result -> result();
}
$table = "tbl_content";
$objPost = new stdClass();
$mainObj = new Content($module, $result['pageId']);

$winTitle = $headTitle = $result['pageTitle_'.$_SESSION['lId']] . ' - ' . SITE_NM;


$pageContent = $mainObj -> getPageContent();
$metaTag = getMetaTags(array(
    "description" => $mainObj->metaDesc,
    "keywords" => $mainObj->metaKeyword,
    "author" => AUTHOR
));

require_once DIR_TMPL . "parsing-nct.tpl.php";
?>