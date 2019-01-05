<?php
$reqAuth = true;
$module = 'my_projects-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.my_projects-nct.php";

extract($_REQUEST);

$winTitle = 'My projects - ' . SITE_NM;
$headTitle = $winTitle;
$metaTag = getMetaTags(array(
    "description" => $winTitle,
    "keywords" => $headTitle,
    "author" => AUTHOR
));

$js_array = array(SITE_JS . "modules/$module.js");
$obj = new myProjects($module, 0, $_REQUEST);
extract($_REQUEST);

if (isset($action) && isset($method) && $action == "method" && method_exists($obj, $method)) {
    if ($method == 'project_rows') {
        echo $obj -> {$method}($_REQUEST['extra']);
        exit ;
    }

    $response['status'] = 1;
    $response['msg'] = 'undefined';
    $response['html'] = $obj -> {$method}($_REQUEST['extra']);
    echo json_encode($response);
    exit ;
}
$pageContent = $obj -> getPageContent();

require_once DIR_TMPL . "parsing-nct.tpl.php";
?>