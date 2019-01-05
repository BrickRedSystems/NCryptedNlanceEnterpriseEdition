<?php

    $reqAuth = false;
    $module  = 'registration-nct';
    require_once "../../includes-nct/config-nct.php";
    require_once "class.registration-nct.php";
    extract($_REQUEST);
    $activationCode = isset($activationcode) ? $activationcode : null;

    if ($activationCode != null) {
        $selUser = $db->pdoQuery('SELECT userId,status,isActive,userType FROM tbl_users WHERE activationCode = ? LIMIT 1', array($activationCode));

        if ($selUser->affectedRows() > 0) {
            $fetchUser = $selUser->result();
            if ($fetchUser['isActive'] == 'y') {
                if ($fetchUser['status'] == 'd') {
                    $_SESSION["msgType"] = disMessage(array(
                        'type' => 'err',
                        'var'  => Your_account_has_been_deactivated_by_admin,
                    ));
                } else {
                    $_SESSION["msgType"] = disMessage(array(
                        'type' => 'err',
                        'var'  => You_have_already_activated_your_account,
                    ));
                }
            } else {
                $id = $fetchUser['userId'];
                $db->update('tbl_users', array(
                    'isActive' => 'y',
                    'status'   => 'a',
                ), array("userId" => $id));
                $_SESSION["msgType"] = disMessage(array(
                    'type' => 'suc',
                    'var'  => email_Verification_completed,
                ));
            }
        } else {
            $_SESSION["msgType"] = disMessage(array(
                'type' => 'err',
                'var'  => Verification_failed,
            ));
        }
    }else{
         $_SESSION["msgType"] = disMessage(array(
            'type' => 'err',
            'var'  => Something_went_wrong,
        ));
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?php echo SITE_NM; ?></title>
        <meta name="viewport" content="width=device-width" />
        <script src='//code.jquery.com/jquery-1.11.2.min.js'></script>
        <script src='<?php echo SITE_JS; ?>mdetect.js'></script>
        <script>
            (function ($, MobileEsp) {
                // On document ready, redirect to the App on the App store.
                $(function () {
                    if (typeof MobileEsp.DetectIos !== 'undefined' && ( MobileEsp.DetectIos() || MobileEsp.DetectAndroid()))
                    {
                        // Add an iframe to twitter://, and then an iframe for the app store
                        // link. If the first fails to redirect to the Twitter app, the
                        // second will redirect to the app on the App Store. We use jQuery
                        // to add this after the document is fully loaded, so if the user
                        // comes back to the browser, they see the content they expect.
                        window.location = "nlance://dev.ncryptedprojects.com/nlance_v3/deeplink/login";
                    }else{
                        window.location = "<?php echo SITE_URL.'login/'; ?>";
                    }
                });
            })(jQuery, MobileEsp);
        </script>
        <style type="text/css">
            .twitter-detect {
                display: none;
            }
        </style>
    </head>
    <body>
    </body>
</html>