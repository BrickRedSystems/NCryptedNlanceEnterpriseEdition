<?php 

$page = new MainTemplater(DIR_ADMIN_TMPL."main-nct.tpl.php");

$head = new MainTemplater(DIR_ADMIN_TMPL."head-nct.tpl.php");

$site_header= new MainTemplater(DIR_ADMIN_TMPL."header-nct.tpl.php");

$footer=new MainTemplater(DIR_ADMIN_TMPL."footer-nct.tpl.php");

$page->body= '';
$page->right='';
$page->head='';
$page->header='';
$page->footer='';

