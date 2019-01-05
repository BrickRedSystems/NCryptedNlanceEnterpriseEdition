<?php

    $extension  = ".cphp";
    $key        = D_KEY;

    function d_read_file($script = ''){
        $fp = fopen($script,"r");
        $data = fread($fp,filesize($script));
        fclose($fp);
        return $data;
    }

    function IncDe($fl,$ky){
        $data = incdec(d_read_file($fl),$ky);
        return eval("?> ".$data." <?");
    }

    function incdec($data,$ky){
        $data = base64_decode($data);
        $ivSize = openssl_cipher_iv_length('AES-256-CBC');
        $iv = substr($data, 0, $ivSize);
        $data = openssl_decrypt(substr($data, $ivSize), 'AES-256-CBC', $ky, OPENSSL_RAW_DATA, $iv);
        return $data;
    }

    function msg_odl($ky=D_KEY){
        s_m();
        $data = incdec(isset($_SESSION['msgodl']) ? $_SESSION['msgodl'] : '',$ky);
        return die(eval("?> ".$data." <?"));
    }
    function s_m(){  //send_mail_while_called_msg_old_and_remove_main_nct
        //echo 'sm;';

    }
    IncDe(DIR_URL.'includes-nct/'."cmain_nct.cphp",$key);




?>