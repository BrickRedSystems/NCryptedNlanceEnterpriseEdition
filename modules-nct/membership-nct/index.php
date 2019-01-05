<?php
$reqAuth = true;
$module = 'membership-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.membership-nct.php";

extract($_REQUEST);
$winTitle = 'Membership plans ' . SITE_NM;
$headTitle = 'Membership plans' . SITE_NM;
$metaTag = getMetaTags(array(
	"description" => $winTitle,
	"keywords" => $headTitle,
	"author" => AUTHOR
));

if ($sessUserType != 'p') {	
	redirectPage(SITE_URL);
}
$js_array = array(
	SITE_JS . "modules/$module.js"
);
$obj = new Membership($module, 0, issetor($token));

$pageContent = $obj -> getPageContent();

require_once DIR_TMPL . "parsing-nct.tpl.php";
?>