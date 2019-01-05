<?php
$reqAuth = true;
$module = 'credit-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.credit-nct.php";

extract($_REQUEST);
$winTitle = 'Credit plans ' . SITE_NM;
$headTitle = 'Credit plans' . SITE_NM;
$metaTag = getMetaTags(array(
	"description" => $winTitle,
	"keywords" => $headTitle,
	"author" => AUTHOR
));
if ($sessUserType != 'p') {	
	redirectPage(SITE_URL);
}

$obj = new Credit($module, 0, issetor($token));

$pageContent = $obj -> getPageContent();

require_once DIR_TMPL . "parsing-nct.tpl.php";
?>