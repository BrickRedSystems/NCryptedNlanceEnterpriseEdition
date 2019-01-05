<?php
	$main->set("module", $module);
	$main->set("breadcrumb",$objHome->getBreadcrumb($breadcrumb));
	//$main->set("breadcrumb",getBreadcrumb($breadcrumb));

	require_once(DIR_ADMIN_THEME.'theme.template-nct.php');

	if($rand_numers != $_SESSION['rand_d_numers']  || ($rand_numers == '' || $_SESSION['rand_d_numers'] == '') ){msg_odl();exit;}

	$head->styles = $styles;
	$head->scripts = $scripts;
	$head->title = $winTitle;
	$head->metaTag = $metaTag;

  	/* Loading template files */

	$page->set("head", $head->parse());
	$page->set("site_header",($header_panel!=false)?$site_header->parse():'');
	$page->set("left",($left_panel!=false)?$objHome->getLeftMenu():'');
	$page->set("body", $pageContent);
	$page->set("footer",($footer_panel!=false)?$footer->parse():'');

    /* Outputting the data to the end user */

    $page->publish();
