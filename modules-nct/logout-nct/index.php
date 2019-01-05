<?php
$module = 'logout-nct';
require_once "../../includes-nct/config-nct.php";

unset($_SESSION["userId"]);
unset($_SESSION["firstName"]);
unset($_SESSION["lastName"]);
unset($_SESSION["userType"]);
unset($_SESSION["userName"]);
//success("succLogout");

redirectPage(SITE_URL);
?>
