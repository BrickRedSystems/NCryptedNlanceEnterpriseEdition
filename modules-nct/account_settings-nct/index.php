<?php
$reqAuth = true;
$module = 'account_settings-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.account_settings-nct.php";

extract($_REQUEST);

$winTitle = $headTitle = Account_Settings.' - ' . SITE_NM;

$metaTag = getMetaTags(array(
	"description" => $winTitle,
	"keywords" => $headTitle,
	"author" => AUTHOR
));

$css_array = array(
	SITE_CSS."/toggle/bootstrap-toggle.min.css"
);
$js_array = array(
	SITE_PLUGIN."/toggle/bootstrap-toggle.min.js",
	SITE_JS . "modules/$module.js"
);
$obj = new Accountsettings($module, $_REQUEST);

$pageContent = $obj -> getPageContent();

require_once DIR_TMPL . "parsing-nct.tpl.php";
?>