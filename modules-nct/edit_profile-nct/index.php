<?php
$reqAuth = true;
$module = 'edit_profile-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.edit_profile-nct.php";

extract($_REQUEST);

$winTitle = $headTitle = ucwords(Edit_Profile).' - ' . SITE_NM;

$metaTag = getMetaTags(array(
	"description" => $winTitle,
	"keywords" => $headTitle,
	"author" => AUTHOR
));

$css_array = array(
	SITE_JS . "plugins-nct/tagmanager/tagmanager.css",
	SITE_CSS . "cropper.min.css"
);
$js_array = array(
	SITE_JS . "plugins-nct/tagmanager/tagmanager.js",
	"//twitter.github.io/typeahead.js/releases/latest/typeahead.bundle.js",
	SITE_JS . "cropper.min.js",
	SITE_JS . "modules/$module.js"
);
//dump_exit($_POST);


$obj = new EditProfile($module,$_REQUEST);

$pageContent = $obj -> getPageContent();

require_once DIR_TMPL . "parsing-nct.tpl.php";
?>