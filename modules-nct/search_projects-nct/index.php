<?php
$reqAuth = false;
$module = 'search_projects-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.search_projects-nct.php";

$winTitle = $headTitle = ucwords(Search_projects).' - ' . SITE_NM;

$metaTag = getMetaTags(array(
    "description" => $winTitle,
    "keywords" => $headTitle,
    "author" => AUTHOR
));
$css_array = array(
    SITE_PLUGIN . "ionRangeSlider/css/ion.rangeSlider.css",
    SITE_PLUGIN . "ionRangeSlider/css/ion.rangeSlider.skinModern.css",
);
$js_array = array(
    SITE_JS . "jquery.infinitescroll.js",
    SITE_PLUGIN . "ionRangeSlider/js/ion.rangeSlider.js",
    SITE_JS . "modules/$module.js"
);
$obj = new Searchprojects($module, 0, $_REQUEST);
extract($_REQUEST);
if (isset($action) && isset($method) && $action == "method" && method_exists($obj, $method)) {
    if (isset($_REQUEST['pageNo'])) {
        echo $obj -> {$method}();
        exit ;
    }

    $response['status'] = 1;
    $response['msg'] = 'undefined';
    $response['html'] = $obj -> {$method}();
    echo json_encode($response);
    exit ;
}
$pageContent = $obj -> getPageContent();
require_once DIR_TMPL . "parsing-nct.tpl.php";
?>