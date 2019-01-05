<?php

$sqlSettings = $db -> select("tbl_site_settings", array(
    "constant",
    "value"
)) -> results();
foreach ($sqlSettings as $conskey => $consval) {
	if($consval['constant'] == 'MAX_FILE_SIZE')
		define($consval["constant"], 1024 * 1024 * (int)$consval["value"]);
	else	
    	define($consval["constant"], $consval["value"]);
}



define("SALT_FOR_ENCRYPTION", "NCrypted");

$host = $_SERVER['HTTP_HOST'];
$request_uri = $_SERVER['REQUEST_URI'];
$canonical_url = "http://" . $host . $request_uri;
$_SESSION['DIR_URL'] = DIR_URL;
define('CANONICAL_URL', $canonical_url);

//Nlance App Server Key
defined('SERVER_KEY') or define('SERVER_KEY', 'AIzaSyAuDo32eQ1uvgzE1vixUFdaoUwdlGAGR10');

define('YEAR', date("Y"));

define('MEND_SIGN', '<font color="#FF0000">*</font>');

define("KEY", 'vR4o]M3p`3~^].%L9');

define('AUTHOR', 'NCrypted');
define('ADMIN_NM', 'Administrator');
define('REGARDS', SITE_NM);
define('CURRENT_YEAR', date('Y'));

define('LIMIT', 8);
//limit for infinite scroll

define("SITE_INC", SITE_URL . "includes-nct/");
define("SITE_LNG", SITE_INC . "languages/");

define("DIR_INC", DIR_URL . "includes-nct/");
define("SITE_MOD", DIR_URL . "modules-nct/");
define("DIR_MOD", DIR_URL . "modules-nct/");

define("SITE_UPD", SITE_URL . "upload-nct/");
define("DIR_UPD", DIR_URL . "upload-nct/");

define('SITE_THEME', SITE_URL . 'themes-nct/');
define("DIR_THEME", DIR_URL . "themes-nct/");
define('SITE_CSS', SITE_THEME . 'css-nct/');
define("SITE_JS", SITE_THEME . "javascript-nct/");
define("SITE_PLUGIN", SITE_JS . "plugins-nct/");
define("DIR_CSS", DIR_THEME . "css-nct/");
define('SITE_IMG', SITE_THEME . 'images-nct/');
define("DIR_IMG", DIR_THEME . "images-nct/");
define("DIR_FONT", DIR_INC . "fonts-nct/");
define("SITE_THUMB", SITE_URL . "thumb/");
define("SITE_CROP", SITE_INC . "crop.php");


define("SITE_SEARCH_PROJECTS", SITE_URL . "search/projects/");
define("SITE_SEARCH_FEATURED_PROJECTS", SITE_URL . "search/projects/?isFeatured=y");
define("SITE_SEARCH_SKILLS_PROJECTS", SITE_URL . "search/projects/?skills=");
define("SITE_SEARCH_CATEGORY_PROJECTS", SITE_URL . "search/projects/?category=");
define("SITE_SEARCH_SUB_CATEGORY_PROJECTS", SITE_URL . "search/projects/?subcategory=");
define("SITE_SEARCH_EXPERIENCE_PROJECTS", SITE_URL . "search/projects/?level=");
define("SITE_SEARCH_PROVIDERS", SITE_URL . "search/providers/");
define("SITE_SEARCH_CATEGORY_PROVIDERS", SITE_URL . "search/providers/?category=");
define("SITE_SEARCH_SUB_CATEGORY_PROVIDERS", SITE_URL . "search/providers/?subcategory=");
define("SITE_SEARCH_EXPERIENCE_PROVIDERS", SITE_URL . "search/providers/?level=");
define("SITE_SEARCH_SKILLS_PROVIDERS", SITE_URL . "search/providers/?skills=");
define("SITE_SEARCH", SITE_SEARCH_PROJECTS);



define("SITE_REGISTER", SITE_URL . "sign-up/");
define("SITE_LOGIN", SITE_URL . "login/");
define("SITE_REACTIVATE", SITE_URL . "reactivate/");
define("SITE_FORGOT", SITE_URL . "forgot-password/");
define("SITE_LOGOUT", SITE_URL . "logout/");
define("SITE_ACC_SETTINGS", SITE_URL . "account-settings/");
define("SITE_DASHBOARD", SITE_URL . "dashboard/");
define("SITE_CONTENT", SITE_URL . "content/");
define("SITE_CONTACTUS", SITE_URL . "contact-us/");
define("SITE_USERTYPE", SITE_URL . "select-user-type/");
define("SITE_EDIT_PROFILE", SITE_URL . "edit-profile/");
define("SITE_WALLET", SITE_URL . "wallet/");
define("SITE_FINANCIAL_INFO", SITE_URL . "financial-information/");
define("SITE_PROVIDERS", SITE_URL . "my-providers/");
define("SITE_PROJECTS", SITE_URL . "my-projects/");
define("SITE_REVIEWS", SITE_URL . "my-reviews/");
define("SITE_FAVORITES", SITE_URL . "my-favorites/");
define("SITE_MESSAGES", SITE_URL . "messages/");
define("SITE_TOP_SKILLS", SITE_URL . "top-skills/");

define("SITE_MEM_PLANS", SITE_URL . "membership-plans/");
define("SITE_BUY_MEMBERSHIP", SITE_URL . "buy-membership/");
define("SITE_MEMBERSHIP_NOTIFY", SITE_URL . "notify-membership/");
define("SITE_MEMBERSHIP_SUCCESS", SITE_URL . "success-membership/");
define("SITE_MEMBERSHIP_CANCEL", SITE_URL . "cancel-membership/");

define("SITE_CREDIT_PLANS", SITE_URL . "credit-plans/");
define("SITE_BUY_CREDIT", SITE_URL . "buy-credit/");
define("SITE_CREDIT_NOTIFY", SITE_URL . "notify-credit/");
define("SITE_CREDIT_SUCCESS", SITE_URL . "success-credit/");
define("SITE_CREDIT_CANCEL", SITE_URL . "cancel-credit/");

define("SITE_PROJECT_POST", SITE_URL . "project/post/");
define("SITE_PROJECT_EDIT", SITE_URL . "project/edit/");
define("SITE_PROJECT_REPOST", SITE_URL . "project/repost/");
define("SITE_BID", SITE_URL . "bid/");
define("SITE_BID_EDIT", SITE_URL . "bid/edit/");

$sessUserId      = (isset($_SESSION["userId"]) && (int) $_SESSION["userId"] > 0 ? (int) $_SESSION["userId"] : 0);
define("SITE_HOME_POST_PROJECT", ($sessUserId > 0) ? SITE_PROJECT_POST : SITE_LOGIN . '?path=' . SITE_PROJECT_POST);

define('SITE_HYBRIDAUTH', DIR_INC . 'hybridauth-master/hybridauth/');
define("DIR_HYBRIDAUTH", DIR_INC . "hybridauth-master/hybridauth/");

//define("SITE_THEME_CSS", SITE_URL . "themes-nct/css-nct/");
define('SITE_THEME_FONTS', SITE_URL . 'fonts/');
define('SITE_THEME_IMG', SITE_URL . 'images/');
define('SITE_THEME_JS', SITE_URL . 'js/');

define('DIR_THEME_IMG', DIR_THEME . 'images-nct/');

define('SITE_LOGO_URL', SITE_IMG.SITE_LOGO);
define("SITE_LOGO_FAVICON", SITE_IMG.SITE_FAVICON);
define("DIR_LOGO_FAVICON", DIR_IMG.SITE_FAVICON);

define("DIR_FUN", DIR_URL . "includes-nct/functions-nct/");
define("DIR_TMPL", DIR_URL . "templates-nct/");
define("DIR_CACHE", DIR_UPD . "cache-nct/");

define('USER_DEFAULT_AVATAR', 'default_profile_pic.png');
define('PRODUCT_DEFAULT_IMAGE', SITE_THEME_IMG . 'product-default-image.jpg');
define("SITE_UPD_HOMEIMG", SITE_URL."upload-nct/slider/");
define("DIR_UPD_HOMEIMG", DIR_URL."upload-nct/slider/");

/* Start ADMIN SIDE */
define("SITE_ADMIN_URL", SITE_URL . "admin-nct/");
define("SITE_ADM_CSS", ADMIN_URL . "themes-nct/css-nct/");
define("SITE_ADM_IMG", ADMIN_URL . "themes-nct/images-nct/");
define("SITE_ADM_INC", ADMIN_URL . "includes-nct/");
define("SITE_ADM_MOD", ADMIN_URL . "modules-nct/");
define("SITE_ADM_JS", ADMIN_URL . "includes-nct/javascript-nct/");
define("SITE_ADM_UPD", ADMIN_URL . "upload-nct/");
define("SITE_JAVASCRIPT", SITE_URL . "includes-nct/javascript-nct/");
define("SITE_ADM_PLUGIN", ADMIN_URL . "includes-nct/plugins-nct/");
define("SITE_ADM_JAVA", SITE_ADMIN_URL . "includes-nct/javascript-nct/");

define("DIR_ADMIN_URL", DIR_URL . "admin-nct/");
define("DIR_ADMIN_THEME", DIR_ADMIN_URL . "themes-nct/");
define("DIR_ADMIN_TMPL", DIR_ADMIN_URL . "templates-nct/");
define("DIR_ADM_INC", DIR_ADMIN_URL . "includes-nct/");
define("DIR_ADM_MOD", DIR_ADMIN_URL . "modules-nct/");
define("DIR_ADM_PLUGIN", DIR_ADM_INC . "plugins-nct/");
/* End ADMIN SIDE */

define("NMRF", '<div class="no-results">No more results found.</div>');
define("LOADER", '<img alt="Loading.." src=" ' . SITE_THEME_IMG . 'ajax-loader-transparent.gif" class="lazy-loader" />');

define("PHP_DATE_FORMAT", 'Y-M-d');
define("PHP_DATETIME_FORMAT", 'Y M, d H:i');
define("PHP_DATE_FORMAT_MONTH", 'M Y');
define("PHP_DATE_FORMAT_MONTH_YEAR", 'M Y');
define("MYSQL_DATE_FORMAT", '%b %d, %Y');
define("BOOTSTRAP_DATEPICKER_FORMAT", 'yyyy/mm/dd');
define("BOOTSTRAP_DATETIMEPICKER_FORMAT", 'M d, yyyy H:i');
define("BOOTSTRAP_DATEPICKER_YEAR_FORMAT", 'yyyy');

/* Start Paypal Settings */
define('PAYPAL_CURRENCY_CODE', 'USD');
define('DEFAULT_CURRENCY_CODE', 'USD');
define('CURRENCY_SYMBOL', '$');

define('RETURN_URL', SITE_URL . 'payment_successful');
define('CANCEL_RETURN_URL', SITE_URL . 'transaction_cancelled');
define('NOTIFY_URL', SITE_URL . 'notify/');
/* End Paypal Settings */


