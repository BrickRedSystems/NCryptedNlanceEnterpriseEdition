<?php
class Login {
    function __construct($module = "", $id = 0, $token = "", $reffToken = "") {
        foreach ($GLOBALS as $key => $values) {
            $this -> $key = $values;
        }
        $this -> module = $module;
        $this -> id = $id;

    }

    public function getPageContent() {
        
        $pureSiteNm = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', SITE_NM)));
        $replace = array(
            '%userName%' => (isset($_COOKIE[$pureSiteNm . "userName"]) && $_COOKIE[$pureSiteNm . "userName"] != '') ? $_COOKIE[$pureSiteNm . "userName"] : NULL,
            '%password%' => (isset($_COOKIE[$pureSiteNm . "password"]) && $_COOKIE[$pureSiteNm . "password"] != '') ? $_COOKIE[$pureSiteNm . "password"] : NULL,
            '%remember_me%' => (isset($_COOKIE[$pureSiteNm . "rememberme"]) && $_COOKIE[$pureSiteNm . "rememberme"] == 'y') ? 'checked="checked"' : NULL,
            '%tokenValue%' => setFormToken()
        );
        return get_view(DIR_TMPL . $this -> module . "/" . $this -> module . ".tpl.php", $replace);
    }

    public function getForgetPage() {
        return get_view(DIR_TMPL . $this -> module . "/forget-nct.tpl.php",array(
            '%tokenValue%' => setFormToken()));
    }
    
    public function getReactivatePage() {
        return get_view(DIR_TMPL . "login-nct/reactivate-nct.tpl.php",array(
            '%tokenValue%' => setFormToken()));
    }

}
?>
