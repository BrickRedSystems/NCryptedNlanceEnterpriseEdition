<?php
$reqAuth = false;
$module = 'project_detail-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.project_detail-nct.php";


$css_array = array(
    "//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css",
    SITE_CSS . "scroll/jquery.mCustomScrollbar.css",
    
    
    SITE_PLUGIN."blueimp/css/jquery.fileupload.css",
    SITE_PLUGIN."blueimp/css/jquery.fileupload-ui.css",
    
    SITE_PLUGIN."rating/css/star-rating.min.css",  
);
$js_array = array(
    "//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js",
    SITE_PLUGIN . "scroll/jquery.mCustomScrollbar.concat.min.js",
    
    
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
    
    SITE_PLUGIN."rating/js/star-rating.min.js",    
    SITE_JS . "modules/$module.js"
);
$obj = new ProjectDetail($module, 0, $_REQUEST);

$winTitle =  $headTitle = ucwords($obj->proj['title']) .' - ' .SITE_NM;
$metaTag = getMetaTags(array(
    "description" => $winTitle,
    "keywords" => $headTitle,
    "author" => AUTHOR
));


extract($_REQUEST);
if (isset($action) && isset($method) && $action == "method" && method_exists($obj, $method)) {
    //dump_exit($method);
    if(isset($pageNo)){
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