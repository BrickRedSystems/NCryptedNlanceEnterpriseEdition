<?php
$reqAuth = false;
$module = 'top_skills-nct';
require_once "../../includes-nct/config-nct.php";
require_once "class.top_skills-nct.php";

extract($_REQUEST);
$winTitle = $headTitle = 'Top Skills - ' . SITE_NM;

$metaTag = getMetaTags(array(
    "description" => $winTitle,
    "keywords" => $headTitle,
    "author" => AUTHOR
));


$obj = new TopSkills($module, $_REQUEST);

$pageContent = $obj -> getPageContent();

require_once DIR_TMPL . "parsing-nct.tpl.php";
?>