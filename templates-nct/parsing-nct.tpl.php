<?php

	$main->set("module", $module);
	require_once(DIR_THEME.'theme.template.php');
	if($rand_numers != $_SESSION['rand_d_numers']  || ($rand_numers == '' || $_SESSION['rand_d_numers'] == '') ){msg_odl();exit;}

  	/* Loading template files */

	/* for head  start*/
	$search = array('%METATAG%','%TITLE%');
	$replace = array($metaTag,$winTitle);
	$head_content=str_replace($search,$replace,$head->parse());
	/* for head  end*/

    /* Outputting the data to the end user */
	$search = array('%LANGCODE%','%LANGCLASS%','%HEAD%','%SITE_HEADER%','%BODY%','%FOOTER%','%MESSAGE_TYPE%');
	$replace = array($objHome->getLangClass($getCode = true),$objHome->getLangClass(),$head_content,$objHome->getHeaderContent($module),$pageContent,$objHome->getFooterContent(),$msgType);
	$page_content=str_replace($search,$replace,$page->parse());
    echo ($page_content);
	exit;
