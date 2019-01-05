<?php

class Login extends Home {

    function __construct() {
        parent::__construct();
    }

    public function loginSubmit() {
        $uName = $this -> objPost -> uName;
        $uPass = $this -> objPost -> uPass;

        $qrysel = $this -> db -> select("tbl_admin", array(
            "id",
            "uPass",
            "isActive"
        ), array("uName" => $uName)) -> result();

        if (!empty($qrysel) > 0 && ($qrysel['isActive'] != 'd' && $qrysel['isActive'] != 't')) {
            $fetchUser = $qrysel;
            $adm_id = $fetchUser['id'];
            if ($fetchUser["uPass"] == md5($uPass)) {
                $_SESSION["adminUserId"] = (int)$fetchUser["id"];
                $_SESSION["uName"] = $uName;
                $sess_id = session_id();

                if (isset($_SESSION['req_uri_adm']) && $_SESSION['req_uri_adm'] != '') {
                    $url = $_SESSION['req_uri_adm'];
                    unset($_SESSION['req_uri_adm']);
                    unset($_SESSION['loginDisplayed_adm']);
                    redirectPage($url);
                }
                else {
                    redirectPage(SITE_ADM_MOD . 'home-nct/');
                }
            }
            else {
                return 'invaildUsers';
            }
        }
        else if ($qrysel['isActive'] == 'd') {
            $_SESSION["toastr_message"] = disMessage(array(
                'type' => 'err',
                'var' => 'This user is not apprroved by the admin.'
            ));
            redirectPage(SITE_ADM_MOD . 'login-nct/');
        }
        else {
            $_SESSION["toastr_message"] = disMessage(array(
                'type' => 'err',
                'var' => 'Please enter valid user name or password.'
            ));
            redirectPage(SITE_ADM_MOD . 'login-nct/');
        }
    }

    public function forgotProdedure() {

        $uEmail = isset($this -> objPost -> uEmail) ? $this -> objPost -> uEmail : '';
        $uName = isset($this -> objPost -> uName) ? $this -> objPost -> uName : '';

        $qrysel = $this -> db -> select("tbl_admin", array("id,uEmail,uName,uPass"), array("uEmail" => $uEmail)) -> result();
        if (!empty($qrysel) > 0) {
            $fetchUser = $qrysel;
            $to = $fetchUser["uEmail"];
            $uName = $fetchUser["uName"];
            $pass = genrateRandom();

            $this -> db -> update("tbl_admin", array("uPass" => md5($pass)), array("id" => (int)$fetchUser["id"]));

            $arrayCont = array(
                'greetings' => $firstName,
                'username' => $uName,
                'pass' => $pass,
            );

            $array = generateEmailTemplate('admin_forgot_pass', $arrayCont);
            //echo $array['message'];exit;
            sendEmailAddress($to, $array['subject'], $array['message']);

            return 'succForgotPass';
        }
        else {
            return 'wrongUsername';
        }
    }

    public function changePasswordProcedure() {

        global $adminUserId;
        $opasswd = isset($this -> objPost -> opasswd) ? $this -> objPost -> opasswd : '';
        $passwd = isset($this -> objPost -> passwd) ? $this -> objPost -> passwd : '';
        $cpasswd = isset($this -> objPost -> cpasswd) ? $this -> objPost -> cpasswd : '';

        $qrysel = $this -> db -> select("adminuser", "password", "id=" . $adminUserId . "");
        $fetchUser = mysql_fetch_array($qrysel);
        if ($fetchUser["password"] != $opasswd) {
            return 'wrongPass';
        }
        else if ($passwd != $cpasswd) {
            return 'passNotmatch';
        }
        else {
            $value = new stdClass();
            $value -> password = $cpasswd;
            $value -> isForgot = 'n';
            $qryUpd = $this -> db -> update("adminuser", $value, "id=" . $adminUserId . "", '');
            return 'succChangePass';
        }
    }

    public function getPageContent() {
        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this -> module . "/" . $this -> module . ".tpl.php");
        $main_content -> breadcrumb = $this -> getBreadcrumb();
        $final_result = $main_content -> parse();
        return $final_result;
    }

}
?>