<?php
$reqAuth = true;
$module = 'post_project-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.post_project-nct.php";

$winTitle = $headTitle = Post_a_Project.' - ' . SITE_NM;

$metaTag = getMetaTags(array(
	"description" => $winTitle,
	"keywords" => $headTitle,
	"author" => AUTHOR
));

$css_array = array(
	SITE_JS . "plugins-nct/tagmanager/tagmanager.css",
	"//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css",
	
	SITE_PLUGIN."blueimp/css/jquery.fileupload.css",
	SITE_PLUGIN."blueimp/css/jquery.fileupload-ui.css"	
);
$js_array = array(
	SITE_JS . "plugins-nct/tagmanager/tagmanager.js",
	"//twitter.github.io/typeahead.js/releases/latest/typeahead.bundle.js",
	"//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js",
	
	SITE_PLUGIN."blueimp/js/vendor/jquery.ui.widget.js",
	"//blueimp.github.io/JavaScript-Templates/js/tmpl.min.js",
	"//blueimp.github.io/JavaScript-Load-Image/js/load-image.all.min.js",
	"//blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js",
	SITE_PLUGIN."blueimp/js/jquery.iframe-transport.js",
	SITE_PLUGIN."blueimp/js/jquery.fileupload.js",
	SITE_PLUGIN."blueimp/js/jquery.fileupload-process.js",
	SITE_PLUGIN."blueimp/js/jquery.fileupload-image.js",
	SITE_PLUGIN."blueimp/js/jquery.fileupload-audio.js",
	SITE_PLUGIN."blueimp/js/jquery.fileupload-video.js",
	SITE_PLUGIN."blueimp/js/jquery.fileupload-validate.js",
	SITE_PLUGIN."blueimp/js/jquery.fileupload-ui.js",	
	SITE_JS . "modules/$module.js"
);

if ($sessUserType != "c") {
	$msgType = $_SESSION["msgType"] = disMessage(array(
		'type' => 'err',
		'var' => The_page_you_are_trying_to_access_does_not_exist
	));
	redirectPage(SITE_URL);
}

$obj = new PostProject($module, 0, $_REQUEST);
extract($_REQUEST);
if (isset($action) && isset($method) && $action == "method" && method_exists($obj, $method)) {
	$response['status'] = 1;
	$response['msg'] = 'undefined';
	$response['html'] = $obj->{$method}();
	echo json_encode($response);
	exit ;
}
$pageContent = $obj->getPageContent();
require_once DIR_TMPL . "parsing-nct.tpl.php";
?>