<?php

//start:: for multi-language
function setLang()
{
    global $db;
    //get lId from request (in case of web serice) or take fom session.
    $_SESSION['lId'] = (isset($_REQUEST['lId']) && $_REQUEST['lId'] > 0) ? $_REQUEST['lId'] : issetor($_SESSION['lId'],1);
    if (isset($_SESSION["lId"])) {
        $doesExist = getTableValue("tbl_languages", "id", array("id" => $_SESSION["lId"], 'status' => 'a'));
        if (!$doesExist) {
            unset($_SESSION["lId"]);
        }
    } else {
        $_SESSION["lId"] = getTableValue("tbl_languages", "id", array("isDefault" => 'y'));
    }

    if (isset($_SESSION["lId"]) && file_exists(DIR_INC . 'languages/' . $_SESSION["lId"] . '.php')) {
        require_once DIR_INC . 'languages/' . $_SESSION["lId"] . '.php';
        require_once DIR_INC . 'languages/1.php';
    } else {
        $_SESSION["lId"] = getTableValue("tbl_languages", "id", array("isDefault" => 'y'));
        require_once DIR_INC . 'languages/' . $_SESSION["lId"] . '.php';
    }

    $langDetails          = $db->select('tbl_languages', array("languageName", "langCode"), array("id" => $_SESSION['lId']))->result();
    $_SESSION['langName'] = $langDetails['languageName'];
    $_SESSION['langCode'] = $langDetails['langCode'];
}
//end:: for multi-language

/*
start:: make entry of url and params in call table
 */
function url_origin($s, $use_forwarded_host = false)
{
    $ssl      = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on');
    $sp       = strtolower($s['SERVER_PROTOCOL']);
    $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
    $port     = $s['SERVER_PORT'];
    $port     = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
    $host     = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
    $host     = isset($host) ? $host : $s['SERVER_NAME'] . $port;
    return $protocol . '://' . $host;
}

function full_url($s, $use_forwarded_host = false)
{
    return url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];
}

function registerWSCall()
{
    global $db;
    $content = null;
    foreach ($_REQUEST as $k => $v) {
        $content .= $k . ":" . $v . "\n";
    }
    $inserted_call = $db->insert('tbl_calls', array(
        'query_string' => "----Request----\n\n" . $content . "\n\n----Files----\n\n" . print_r($_FILES, true),
        'full_url'     => full_url($_SERVER),
        'ip_address'   => get_ip_address(),
        'datecreated'  => date('Y-m-d H:i:s'),
        'session'      => print_r($_SESSION, true),
    ))->lastInsertId();
    extract($_REQUEST);
    if (!isset($action) || trim($action) == '') {
        msgExit('Invalid Request');
    }
    return $inserted_call;
}

function registerWSResponse($inserted_call = 0, $json_response = null)
{
    global $db;
    if ($inserted_call) {
        $db->update('tbl_calls', array('response' => $json_response), array('id' => $inserted_call));
    }

}

/*
end:: make entry of url and params in call table
 */

function msgExit($msg = 'undefined', $bool = false)
{
    echo json_encode(array(
        'status' => $bool,
        'msg'    => $msg,
    ));
    exit;
}

function fatal_handler()
{
    $errfile = "unknown file";
    $errstr  = "shutdown";
    $errno   = E_CORE_ERROR;
    $errline = 0;

    $error = error_get_last();

    if ($error !== null) {
        $errno   = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr  = $error["message"];

        if ($errno != 32) {
            /*sendEmailAddress('ashish.joshi@ncrypted.com', 'Error in ' . SITE_URL, format_error($errno, $errstr, $errfile, $errline));*/
        }

    }
}

function format_error($errno, $errstr, $errfile, $errline)
{
    $trace = print_r(debug_backtrace(true), true);

    $content = "<table><thead bgcolor='#c8c8c8'><th>Item</th><th>Description</th></thead><tbody>";
    $content .= "<tr valign='top'><td><b>Error</b></td><td><pre>$errstr</pre></td></tr>";
    $content .= "<tr valign='top'><td><b>Errno</b></td><td><pre>$errno</pre></td></tr>";
    $content .= "<tr valign='top'><td><b>File</b></td><td>$errfile</td></tr>";
    $content .= "<tr valign='top'><td><b>Line</b></td><td>$errline</td></tr>";
    $content .= "<tr valign='top'><td><b>Trace</b></td><td><pre>$trace</pre></td></tr>";
    $content .= '</tbody></table>';

    return $content;
}
function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function regenerateSession($reload = false)
{
    // This token is used by forms to prproject cross site forgery attempts
    if (!isset($_SESSION['nonce']) || $reload) {
        $_SESSION['nonce'] = md5(microtime(true));
    }

    if (!isset($_SESSION['IPaddress']) || $reload) {
        $_SESSION['IPaddress'] = $_SERVER['REMOTE_ADDR'];
    }

    if (!isset($_SESSION['userAgent']) || $reload) {
        $_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
    }

    //$_SESSION['user_id'] = $this->user->getId();

    // Set current session to expire in 1 minute
    $_SESSION['OBSOLETE'] = true;
    $_SESSION['EXPIRES']  = time() + 60;

    // Create new session without destroying the old one
    session_regenerate_id(false);

    // Grab current session ID and close both sessions to allow other scripts to use them
    $newSession = session_id();
    session_write_close();

    // Set session ID to the new one, and start it back up again
    session_id($newSession);
    session_start();

    // Don't want this one to expire
    unset($_SESSION['OBSOLETE']);
    unset($_SESSION['EXPIRES']);
}

function setFormToken()
{
    $_SESSION['form_token'] = md5(time());
    //dump($_SESSION['form_token']);
    return $_SESSION['form_token'];
}

function checkFormToken($token = null)
{

    /////
    if (isset($_SERVER["HTTP_ORIGIN"])) {

        if (strpos(SITE_URL, $_SERVER["HTTP_ORIGIN"]) !== 0) {
            exit("CSRF protection in POST request: detected invalid Origin header: " . $_SERVER["HTTP_ORIGIN"]);
        }
    }
    /////

    $sessToken = isset($_SESSION['form_token']) ? $_SESSION['form_token'] : null;
    unset($_SESSION['form_token']);
    //sesstion should not be valid after 5 minutes
    $duration = 5 * 60;
    //dump(array($token,$sessToken));
    if ($sessToken && $token == $sessToken) {
        $time = ($duration - (time() - $sessToken));
        return ($time <= 0) ? true : false;
    } else {
        return false;
    }
}

function pushToAndroid($token, $message)
{
    $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';

    $fields = array(
        'registration_ids' => $token,
        'notification'     => array(
            'title' => $message['title'],
            'body'  => $message['body'],
            'sound' => 'default',
        ),
        'data'             => array(
            'pkg' => 'xyz',
        ),
    );

    $headers = array(
        'Authorization:key=' . SERVER_KEY,
        'Content-Type:application/json',
    );
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $path_to_firebase_cm);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

    $result = curl_exec($ch);

    curl_close($ch);

    return $result;
}

function replace_null_with_empty_string($array)
{
    if (!is_array($array)) {
        return "";
    }
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $array[$key] = replace_null_with_empty_string($value);
        } else {
            if (is_null($value)) {
                $array[$key] = "";
            }

        }
    }
    return $array;
}

function get_user_notification($notificationId = null, $getLink = false)
{
    global $db;

    if ($notificationId != null) {
        $details = $db->pdoQuery('select n.*, p.title, concat_ws("/",cust.profileLink,p.slug) as projLink,concat_ws(" ",s.firstName,s.lastName) as senderNm, s.profileLink as senderLink, concat_ws(" ",r.firstName,r.lastName) as receiverNm from tbl_notification as n left join tbl_users as s on n.fromUserId = s.userId left join tbl_users as r on n.toUserId = r.userId left join tbl_projects as p on n.referenceId = p.id left join tbl_users as cust on p.userId = cust.userId where n.id =? ', array($notificationId))->result();
        //dump_exit($details);
        switch ($details['typeId']) {
            case '1':
                $notification = $details['senderNm'] . ' ' . has_sent_you_a_message;
                $notiLink     = SITE_URL . $details['senderLink'];
                break;

            case '2':
                $notification = A_new_project_has_been_posted;
                $notiLink     = SITE_URL . $details['projLink'];
                break;

            case '3':
                $notification = New_review_has_been_posted_on_your_project;
                $notiLink     = SITE_URL . $details['projLink'];
                break;

            case '4':
                $notification = Your_milestone_request_has_been_accepted;
                $notiLink     = SITE_URL . $details['projLink'];
                break;

            case '5':
                $notification = Your_milestones_have_been_accepted;
                $notiLink     = SITE_URL . $details['projLink'];
                break;

            case '6':
                $notification = New_milestones_are_created_for . ' ' . $details['title'];
                $notiLink     = SITE_URL . $details['projLink'];
                break;

            case '7':
                $notification = A_provider_has_edited_his_bid_for . ' ' . $details['title'];
                $notiLink     = SITE_URL . $details['projLink'];
                break;

            case '8':
                $notification = A_provider_has_placed_a_bid_for . ' ' . $details['title'];
                $notiLink     = SITE_URL . $details['projLink'];
                break;

            case '9':
                $notification = A_provider_has_edited_milestones_for . ' ' . $details['title'];
                $notiLink     = SITE_URL . $details['projLink'];
                break;

            case '10':
                $notification = $details['title'] . ' ' . has_been_closed;
                $notiLink     = SITE_URL . $details['projLink'];
                break;

            case '11':
                $notification = Dispute_for . ' ' . $details['title'] . ' ' . has_been_escalated_to_Admin;
                $notiLink     = SITE_URL . $details['projLink'];
                break;

            case '12':
                $notification = Customer_has_accepted_your_bid_for . ' ' . $details['title'];
                $notiLink     = SITE_URL . $details['projLink'];
                break;

            case '13':
                $notification = $details['senderNm'] . ' ' . has_added . ' ' . $details['title'] . ' ' . to_the_favorite_list;
                $notiLink     = SITE_URL . $details['senderLink'];
                break;

            case '14':
                $notification = You_have_received_an_invitation_to_bid_on . ' ' . $details['title'];
                $notiLink     = SITE_URL . $details['projLink'];
                break;

            case '15':
                $notification = You_have_been_marked_as_favorite_by . ' ' . $details['senderNm'];
                $notiLink     = SITE_URL . $details['senderLink'];
                break;

            case '16':
                $notification = $details['senderNm'] . ' ' . has_requested_to_modify_milestones_for . ' ' . $details['title'];
                $notiLink     = SITE_URL . $details['projLink'];
                break;

            default:
                $notification = You_have_received_a_notification;
                $notiLink     = 'javascript:void(0);';
                break;
        }

        return ($getLink) ? $notiLink : $notification;
    } else {
        return null;
    }

}

function insert_user_notification($typeId = null, $from = null, $to = null, $referenceId = null, $noti_arr = array())
{
    global $db;

    if ($typeId == null || $to == null || $referenceId == null) {
        return false;
    } else {

        if (check_noti_enable($to, $typeId)) {
            $notification = '';
            $lastInsertId = $db->insert('tbl_notification', array(
                'toUserId'     => $to,
                'fromUserId'   => (string) $from,
                'typeId'       => (string) $typeId,
                'referenceId'  => $referenceId,
                'notification' => $notification,
                'createdDate'  => date('y-m-d H:i:s'),
                'ipAddress'    => get_ip_address(),
            ))->lastInsertId();

            pushToAndroid(array(
                getTableValue("tbl_users", "deviceId", array("userId" => $to)),
            ), array(
                'title' => SITE_NM,
                'body'  => get_user_notification($lastInsertId),
            ));
            return ($lastInsertId > 0) ? true : false;
        } else {
            return false;
        }
    }
}

/*
 * function for checking whether user has enabled certain type of notification or not
 * returns boolean(true|false)
 */
function check_noti_enable($userId, $typeId)
{
    global $db;
    if ($userId > 0 && $typeId > 0) {
        $check = getTableValue('tbl_notification_settings', 'id', array(
            'userId' => $userId,
            'typeId' => $typeId,
        ));
    }
    return ($check > 0) ? false : true;
}

//for infinite scrolling
function getPagerData($numHits, $limit, $page)
{
    $numHits  = (int) $numHits;
    $limit    = max((int) $limit, 1);
    $page     = (int) $page;
    $numPages = ceil($numHits / $limit);

    $page = max($page, 1);
    $page = min($page, $numPages);

    $offset = ($page - 1) * $limit;

    $ret = new stdClass;

    $ret->offset   = $offset;
    $ret->limit    = $limit;
    $ret->numPages = $numPages;
    $ret->page     = $page;

    return $ret;
}

function is_my_fav($id = null, $type = null)
{
    global $db, $sessUserId;
    if ($id == null && $type == null) {
        return 0;
    } else {
        switch ($type) {
            case 'project':
                $exists = getTableValue('tbl_favourites', 'id', array('favoriteId' => $id, 'type' => '2', 'userId' => $sessUserId));
                break;
            case 'user':
                $exists = getTableValue('tbl_favourites', 'id', array('favoriteId' => $id, 'type' => '1', 'userId' => $sessUserId));
                break;
        }
        return issetor($exists, 0);
    }
}

function renderStarRating($rating, $maxRating = 5, $html5 = false)
{
    if ($html5) {
        $fullStar  = "&#9733;";
        $halfStar  = $fullStar;
        $emptyStar = "&#9734;";
    } else {
        $fullStar  = "<i class = 'fa fa-star'></i>";
        $halfStar  = "<i class = 'fa fa-star-half-full'></i>";
        $emptyStar = "<i class = 'fa fa-star-o'></i>";
    }

    $rating = $rating <= $maxRating ? $rating : $maxRating;

    $fullStarCount  = (int) $rating;
    $halfStarCount  = ceil($rating) - $fullStarCount;
    $emptyStarCount = $maxRating - $fullStarCount - $halfStarCount;

    $html = str_repeat($fullStar, $fullStarCount);
    $html .= str_repeat($halfStar, $halfStarCount);
    $html .= str_repeat($emptyStar, $emptyStarCount);

    return $html;
}

function getUserData($userId = null)
{
    global $db;
    $userId = ($userId != null) ? $userId : (isset($_SESSION["userId"]) ? $_SESSION["userId"] : null);
    if ($userId != null) {
        $result = $db->select('tbl_users', '*', array('userId' => $userId))->result();
        return $result;
    } else {
        return null;
    }

}

function getUserMembership($userId = null)
{
    global $db;

    $userId = ($userId != null) ? $userId : (isset($_SESSION["userId"]) ? $_SESSION["userId"] : null);
    if ($userId != null) {
        $result = $db->select('tbl_payment_history', array('id', 'userId', 'paymentType', 'membershipId', 'createdDate'), array('userId' => $userId, 'paymentType' => 'buy membership'), 'order by id DESC LIMIT 1')->result();

        $memBegun = $result['createdDate'];
        $lastDate = date("Y-m-d H:i:s", strtotime("-1 month"));

        return ($memBegun >= $lastDate && $memBegun <= date('Y-m-d H:i:s')) ? $result : null;
    } else {
        return null;
    }

}

function makeSlug($string, $table, $field, $whereCol, $extra = 'url', $id = null)
{
    global $fb;
    $slug = trim($string); // trim the string

    if ($extra == 'url') {
        $slug = preg_replace('/[^a-zA-Z0-9 -]/', '', $slug); // only take alphanumerical characters, but keep the spaces and dashes too...
        $slug = str_replace(' ', '-', $slug); // replace spaces by dashes
    } elseif ($extra == 'name') {
        $slug = preg_replace('/[^a-zA-Z0-9]/', '', $slug); // only take alphanumerical characters, but keep the spaces and dashes too...
    }
    $slug = strtolower($slug);
    //$fb->trace('trace');
    if ($id != null && $field != null) {
        //in case edit
        //exist except given id
        $does_exist = getTableValue($table, $field, array("$whereCol" => $slug, "AND $field <>" => $id));

        if (isset($does_exist) && $does_exist != "") {
            return $slug . generateRandString(4);
        } else {
            return $slug;
        }
    } else {
        $does_exist = getTableValue($table, $field, array("$whereCol" => $slug));

        if (isset($does_exist) && $does_exist != "") {
            return $slug . generateRandString(4);
        } else {
            return $slug;
        }
    }

}

function String_crop($string = null, $noChar = 0)
{

    $CapsString = ucwords(strtolower($string));
    $StringLen  = strlen($CapsString);

    if ($noChar == 0) {$noChar = strlen($CapsString);}

    if ($StringLen > $noChar) {
        return substr($CapsString, 0, $noChar) . '..';
    } else {
        return substr($CapsString, 0, $noChar);
    }
}

/**
 * Dump helper. Functions to dump variables to the screen, in a nicley formatted manner.
 */
if (!function_exists('dump')) {
    function dump($var, $label = 'Dump', $echo = true)
    {
        // Store dump in variable
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        // Add formatting
        $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
        $output = '<pre style="background: #FFFEEF; color: #000; border: 1px dotted #000; padding: 10px; margin: 10px 0; text-align: left;">' . $label . ' => ' . $output . '</pre>';
        // Output
        if ($echo == true) {
            echo $output;
        } else {
            return $output;
        }
    }

}
/**
 * Dump helper.
 * Functions to dump variables to the screen, in a nicley formatted manner.
 */
if (!function_exists('dump_exit')) {
    function dump_exit($var, $label = 'Dump', $echo = true)
    {
        dump($var, $label, $echo);
        exit;
    }
}

function getTotalRows($tableName, $condition = '', $countField = '*')
{

    global $db;
    //$db->select($tableName,$countField,$condition);

    $qSel = "SELECT * from " . $tableName . " WHERE " . $condition;

    $qrysel0   = $db->pdoQuery($qSel);
    $totlaRows = $qrysel0->affectedRows();
    return $totlaRows;
}

/*
 * returns parsed html
 * @author Ashish Joshi
 */
function get_view($tpl_path, $replace = array())
{
    $tpl        = new MainTemplater($tpl_path);
    $parsed_tpl = $tpl->parse();
    if (!empty($replace)) {
        return str_replace(array_keys($replace), array_values($replace), $parsed_tpl);

    } else {
        return $parsed_tpl;
    }
}

function tim_thumb_image($image = null, $dir = null, $height = null, $width = null, $filter = null)
{

    if ($image == null || $dir == null) {
        return SITE_THUMB . "?src=" . SITE_IMG . 'no_image.jpg' . "&h=" . $height . "&w=" . $width . "&f=" . $filter;
    } else if ($height == 0 || $height == null || $width == 0 || $width == null) {
        return (file_exists(DIR_UPD . $dir . "/" . $image)) ? SITE_THUMB . "?src=" . SITE_UPD . $dir . "/" . $image : SITE_THUMB . "?src=" . SITE_IMG . 'no_image.jpg' . "&f=" . $filter;
    } else {
        return (file_exists(DIR_UPD . $dir . "/" . $image)) ? SITE_THUMB . "?src=" . SITE_UPD . $dir . "/" . $image . "&h=" . $height . "&w=" . $width : SITE_THUMB . "?src=" . SITE_IMG . 'no_image.jpg' . "&h=" . $height . "&w=" . $width . "&f=" . $filter;
    }

}
function tim_thumb_banner_image($image = null, $dir = null, $height = null, $width = null, $filter = null)
{

    if ($image == null || $dir == null) {
        return SITE_THUMB . "?src=" . SITE_IMG . 'banner1.jpg' . "&h=" . $height . "&w=" . $width . "&f=" . $filter;
    } else if ($height == 0 || $height == null || $width == 0 || $width == null) {
        return (file_exists(DIR_UPD . $dir . "/" . $image)) ? SITE_THUMB . "?src=" . SITE_UPD . $dir . "/" . $image : SITE_THUMB . "?src=" . SITE_IMG . 'banner1.jpg' . "&f=" . $filter;
    } else {
        return (file_exists(DIR_UPD . $dir . "/" . $image)) ? SITE_THUMB . "?src=" . SITE_UPD . $dir . "/" . $image . "&h=" . $height . "&w=" . $width : SITE_THUMB . "?src=" . SITE_IMG . 'banner1.jpg' . "&h=" . $height . "&w=" . $width . "&f=" . $filter;
    }

}

function get_link($page, $content = '')
{
    global $db, $sessUserType;

    switch ($page) {
        case 'home':{
                $url = SITE_URL . $content;
                break;}
        case 'wallet':{
                $url = SITE_URL . 'wallet/' . $content;
                break;}
        case 'paypal_notify':{
                $url = SITE_URL . 'payment/notify/' . $content;
                break;}
        case 'paypal_failed':{
                $url = SITE_URL . 'payment/failed/' . $content;
                break;}
        case 'paypal_thankyou':{
                $url = SITE_URL . 'payment/thankyou/' . $content;
                break;}
        default:{
                $url = SITE_URL;
                break;}
    }
    return $url;
}

function getAdminUnreadNotificationsCount()
{
    global $db, $adminUserId;

    $get_notifications_count = $db->pdoQuery("SELECT COUNT(*) as notifications_count FROM tbl_admin_notifications WHERE admin_id = " . $adminUserId . " AND is_notified = 'n' AND is_read = 'n'")->result();
    return $get_notifications_count['notifications_count'];
}

/* Functions for getting time diffrance */
function get_time_difference($start, $end)
{
    $uts['start'] = strtotime($start);
    $uts['end']   = strtotime($end);
    if ($uts['start'] !== -1 && $uts['end'] !== -1) {
        if ($uts['end'] >= $uts['start']) {
            $diff = $uts['end'] - $uts['start'];
            if ($days = intval((floor($diff / 86400)))) {
                $diff = $diff % 86400;
            }

            if ($hours = intval((floor($diff / 3600)))) {
                $diff = $diff % 3600;
            }

            if ($minutes = intval((floor($diff / 60)))) {
                $diff = $diff % 60;
            }

            $diff = intval($diff);
            return (array(
                'days'    => $days,
                'hours'   => $hours,
                'minutes' => $minutes,
                'seconds' => $diff,
            ));
        } else {
            trigger_error("Ending date/time is earlier than the start date/time", E_USER_WARNING);
        }
    } else {
        trigger_error("Invalid date/time data detected", E_USER_WARNING);
    }
    return (false);
}

function getAllNotificationsAdmin()
{
    global $db, $adminUserId;

    $query = "SELECT *
    FROM tbl_admin_notifications
    WHERE admin_id = " . filtering($adminUserId, 'input', 'int') . "
    ORDER BY id DESC LIMIT 0, 5";

    $get_notifications = $db->pdoQuery($query)->results();

    if ($get_notifications) {

        $notification        = new MainTemplater(DIR_ADMIN_TMPL . "/notification-li-nct.tpl.php");
        $notification_parsed = $notification->parse();

        $field = array(
            '%NOTIFICATION%',
            '%NOTIFICATION_URL%',
            '%NOTIFICATION_TITLE%',
            '%NOTIFICATION_DATE%',
            '%TIME_AGO%',
        );
        $counter      = 0;
        $final_result = null;
        foreach ($get_notifications as $notification) {

            $notification_date = date("d M, Y", strtotime($notification['date_added']));
            $response          = get_time_difference($notification['date_added'], date("Y-m-d H:i:s"));

            if ($response['days']) {
                $time_ago = $response['days'] . " Days ago";
            } else if ($response['hours']) {
                $time_ago = $response['hours'] . " Hours ago";
            } else if ($response['minutes']) {
                $time_ago = $response['minutes'] . " Mins ago";
            } else if ($response['seconds']) {
                $time_ago = $response['seconds'] . " Secs ago";
            }

            $type = $notification['type'];
            //type: new_user, dispute, new_project, contact_us

            switch ($type) {
                case 'new_user':{
                        $user_details       = $db->select("tbl_users", "*", array("userId" => $notification['entity_id']))->result();
                        $notification_text  = "New user with user name " . $user_details['userName'] . " has been registered.";
                        $notification_url   = SITE_ADM_MOD . "users-nct";
                        $notification_title = "New user registered.";
                        break;
                    }

            }

            $field_replace = array(
                filtering($notification_text),
                filtering($notification_url),
                filtering($notification_title),
                $notification_date,
                $time_ago,
            );

            $final_result .= str_replace($field, $field_replace, $notification_parsed);
            $db->update("tbl_admin_notifications", array("is_notified" => 'y'), array("id" => $notification['id']));
            $counter++;
        }
    } else {
        $final_result = '<li id="no_notification"> <p>No new notification.</p> </li>';
    }

    return $final_result;
}

function uploadFile($file, $tbl, $col, $id_col, $id = 0, $dir_path, $site_path)
{
    global $db;
    $result = null;
    if (!$file['name']) {
        return false;
    }

    if (!empty($tbl) && !empty($col) && !empty($id_col) && !empty($id)) {
        $result = getTableValue($tbl, $col, array($id_col => $id)); //get old image name
    }
    $file_title  = $file['name'];
    $folder      = $dir_path;
    $path_folder = $site_path;
    $file_name   = strtolower(pathinfo($file['name'], PATHINFO_FILENAME));
    $ext         = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $uniqer      = md5(uniqid(rand(), 1));
    $file_name   = $uniqer . '.' . $ext;
    if ($folder && !is_dir($folder)) {
        mkdir($folder, 0777);
    }
    $uploadfile = $folder . $file_name;

    copy($file['tmp_name'], $uploadfile);
    if (!empty($result)) {
        // remove old image after new image is uploaded successfully
        $filepath = $folder;
        if (file_exists($filepath . $result)) {
            unlink($filepath . $result);
        }
    }
    return array("filepath" => $path_folder, "file_name" => $file_name, 'actual_file_name' => $file['name']);
}

function filtering($value = '', $type = 'output', $valType = 'string', $funcArray = '')
{
    global $abuse_array, $abuse_array_value;

    if ($valType != 'int' && $type == 'output') {
        $value = str_ireplace($abuse_array, $abuse_array_value, $value);
    }

    if ($type == 'input' && $valType == 'string') {
        $value = str_replace('<', '< ', $value);
    }

    $content = $filterValues = '';
    if ($valType == 'int') {
        $filterValues = (isset($value) ? (int) strip_tags(trim($value)) : 0);
    }

    if ($valType == 'float') {
        $filterValues = (isset($value) ? (float) strip_tags(trim($value)) : 0);
    } else if ($valType == 'string') {
        $filterValues = (isset($value) ? (string) strip_tags(trim($value)) : null);
    } else if ($valType == 'text') {
        $filterValues = (isset($value) ? (string) trim($value) : null);
    } else {
        $filterValues = (isset($value) ? trim($value) : null);
    }

    if ($type == 'input') {
        //$content = mysql_real_escape_string($filterValues);
        //$content = $filterValues;
        //$value = str_replace('<', '< ', $filterValues);
        $content = addslashes($filterValues);
    } else if ($type == 'output') {
        if ($valType == 'string') {
            $filterValues = html_entity_decode($filterValues);
        }

        $value   = str_replace(array('\r', '\n', ''), array('', '', ''), $filterValues);
        $content = stripslashes($value);
    } else {
        $content = $filterValues;
    }

    if ($funcArray != '') {
        $funcArray = explode(',', $funcArray);
        foreach ($funcArray as $functions) {
            if ($functions != '' && $functions != ' ') {
                if (function_exists($functions)) {
                    $content = $functions($content);
                }
            }
        }
    }

    return $content;
}
/////////////////////////////////////////////////////////////

function redirectPage($url)
{
    header('Location:' . $url);
    exit;
}

function redirectErrorPage($error)
{
    echo $error;
    //redirectPage(SITE_URL.'modules/error?u='.base64_encode($error));
}

/* Santitize Output */

function sanitize_output($buffer)
{

    $search  = array('/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s', '/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s');
    $replace = array('>', '<', '\\1', '');
    $buffer  = preg_replace($search, $replace, $buffer);
    return $buffer;
}

/* Use to remove whitespacs,Spaces and make string to lower case. Add '-' where Space. */

function Slug($string)
{
    $slug        = strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
    $slug_exists = slug_exist($slug);

    if ($slug_exists) {
        $i        = 1;
        $baseSlug = $slug;
        while (slug_exist($slug)) {
            $slug = $baseSlug . "-" . $i++;
        }
    }

    return $slug;
}

function slug_exist($slug)
{
    global $db;
    $sql          = "SELECT pageSlug FROM tbl_content WHERE pageSlug = '" . $slug . "' ";
    $content_page = $db->pdoQuery($sql)->result();

    if ($content_page) {
        return true;
    }
}

/* Comment Remaining */

function requiredLoginId()
{
    global $sessUserType, $sesspUserId, $memberId;
    if (isset($sessUserType) && $sessUserType == 's') {
        return $sesspUserId;
    } else {
        return $memberId;
    }

}
function closetags($html)
{
    #put all opened tags into an array
    preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);

    $openedtags = $result[1]; #put all closed tags into an array
    preg_match_all('#</([a-z]+)>#iU', $html, $result);
    $closedtags = $result[1];
    $len_opened = count($openedtags);
    # all tags are closed
    if (count($closedtags) == $len_opened) {
        return $html;
    }
    $openedtags = array_reverse($openedtags);
    # close tags
    for ($i = 0; $i < $len_opened; $i++) {

        if (!in_array($openedtags[$i], $closedtags)) {

            $html .= '</' . $openedtags[$i] . '>';
        } else {

            unset($closedtags[array_search($openedtags[$i], $closedtags)]);
        }
    }
    return $html;
}

/* Get IP Address of current system. */
function get_ip_address()
{
    foreach (array(
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR',
    ) as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                    return $ip;
                }
            }
        }
    }
}
/* Get Domain name from url */

function GetDomainName($url)
{
    $now1   = ereg_replace('www\.', '', $url);
    $now2   = ereg_replace('\.com', '', $now1);
    $domain = parse_url($now2);
    if (!empty($domain["host"])) {
        return $domain["host"];
    } else {
        return $domain["path"];
    }
}

/* Generate Random String as type alpha,nume,alphanumeric,hexidec */

function genrateRandom($length = 8, $seeds = 'alphanum')
{
    // Possible seeds
    $seedings['alpha']    = 'abcdefghijklmnopqrstuvwqyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $seedings['numeric']  = '0123456789';
    $seedings['alphanum'] = 'abcdefghijklmnopqrstuvwqyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $seedings['hexidec']  = '0123456789abcdef';
    // Choose seed
    if (isset($seedings[$seeds])) {
        $seeds = $seedings[$seeds];
    }
    // Seed generator
    list($usec, $sec) = explode(' ', microtime());
    $seed             = (float) $sec + ((float) $usec * 100000);
    mt_srand($seed);
    // Generate
    $str         = '';
    $seeds_count = strlen($seeds);
    for ($i = 0; $length > $i; $i++) {
        $str .= $seeds{mt_rand(0, $seeds_count - 1)};
    }
    return $str;
}
/* Generate Random String */

function generateRandString($totalString = 10, $type = 'alphanum')
{
    if ($type == 'alphanum') {
        $alphanum = "AaBbC0cDdEe1FfGgH2hIiJj3KkLlM4mNnOo5PpQqR6rSsTt7UuVvW8wXxYy9Zz";
    } else if ($type == 'num') {
        $alphanum = "098765432101234567890098765432101234567890098765432101234567890";
    }

    return substr(str_shuffle($alphanum), 0, $totalString);
}

/* Sub admin Check Permission */

function checkPermission($usertype, $pagenm, $permission)
{
    if ($usertype == 'a') {
        $flag      = 0;
        $sadm_page = array('subadmin');
        if (in_array($pagenm, $sadm_page)) {
            $flag = 1;
        } else {
            $getval = getValFromTbl('id', 'adminrole', 'id IN (' . $permission . ') AND pagenm="' . $pagenm . '"');
            if ($getval == 0) {
                $flag = 1;
            }

        }
        if ($flag == 1) {

            $_SESSION['notice'] = NOTPER;
            redirectPage(SITE_URL . get_language_url() . 'admin/dashboard');
            exit;
        }
    }
}

/* Load Css Set directory and give filenname as array */

function load_css($filename = array())
{
    $returnStyle = '';
    $filePath    = array();
    if (!empty($filename)) {
        if (domain_details('dir') == 'admin-nct') {
            foreach ($filename as $k => $v) {
                if (is_array($v)) {
                    if (isset($v[1]) && $v[1] != "") {
                        $filePath[] = $v[1] . $v[0];
                    } else {
                        $filePath[] = SITE_ADM_CSS . $v[0];
                    }
                } else {
                    $filePath[] = SITE_ADM_CSS . $v;
                }
            }
        } else {
            foreach ($filename as $k => $v) {
                if (is_array($v)) {
                    if (isset($v[1]) && $v[1] != "") {
                        $filePath[] = $v[1] . $v[0];
                    } else {
                        $filePath[] = SITE_CSS . $v[0];
                    }
                } else {
                    $filePath[] = SITE_CSS . $v;
                }
            }
        }
    }
    foreach ($filePath as $style) {
        $returnStyle .= '<link rel="stylesheet" type="text/css" href="' . $style . '">';
    }
    return $returnStyle;
}

/* Load JS Set directory and give filenname as array */

function load_js($filename = array())
{
    $returnStyle = '';
    $filePath    = array();
    if (!empty($filename)) {
        if (domain_details('dir') == 'admin-nct') {
            foreach ($filename as $k => $v) {
                if (is_array($v)) {
                    if (isset($v[1]) && $v[1] != "") {
                        $filePath[] = $v[1] . $v[0];
                    } else {
                        $filePath[] = SITE_ADM_JS . $v[0];
                    }
                } else {
                    $filePath[] = SITE_ADM_JS . $v;
                }
            }
        } else {
            foreach ($filename as $k => $v) {
                if (is_array($v)) {
                    if (isset($v[1]) && $v[1] != "") {
                        $filePath[] = $v[1] . $v[0];
                    } else {
                        $filePath[] = SITE_JS . $v[0];
                    }
                } else {
                    $filePath[] = SITE_JS . $v;
                }
            }
        }
    }
    foreach ($filePath as $scripts) {
        $returnStyle .= '<script type="text/javascript" src="' . $scripts . '"></script>';
    }
    return $returnStyle;
}

/* Diplay message function */

function disMessage($msgArray, $script = true)
{
    if (domain_details('dir') == 'admin-nct') {
        $script = false;
    }
    $message = '';
    $content = '';
    $type    = isset($msgArray["type"]) ? $msgArray["type"] : null;
    $message = isset($msgArray["var"]) ? $msgArray["var"] : null;

    $type1 = ($type == 'suc' ? 'success' : ($type == 'inf' ? 'info' : ($type == 'war' ? 'warning' : 'error')));
    if ($script) {
        $content = '<script type="text/javascript"> toastr["' . $type1 . '"]("' . $message . '");</script>';
    } else {
        $content = 'toastr["' . $type1 . '"]("' . $message . '");';
    }

    return $content;
}

/* Check Authentication */

function Authentication($reqAuth = false, $redirect = true, $allowedUserType = 'a')
{
    $todays_date = date("Y-m-d");

    global $adminUserId, $sessUserId, $db,$rand_numers;
    if($rand_numers != $_SESSION['rand_d_numers']  || ($rand_numers == '' || $_SESSION['rand_d_numers'] == '') ){msg_odl();exit;}

    $whichSide = domain_details('dir');
    if ($reqAuth == true) {
        if ($whichSide == 'admin-nct') {

            if ($adminUserId == 0) {
                $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => 'Please login to continue.'));
                $_SESSION['req_uri_adm']    = $_SERVER['REQUEST_URI'];

                if ($redirect) {
                    redirectPage(SITE_ADMIN_URL);
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            if ($sessUserId <= 0) {
                $_SESSION["toastr_message"] = disMessage(array('type' => 'err', 'var' => 'Please login to continue.'));
                $_SESSION['req_uri']        = $_SERVER['REQUEST_URI'];
                $_SESSION["userId"]         = 0;
                $_SESSION["firstName"]      = null;
                $_SESSION["lastName"]       = null;
                $_SESSION["userType"]       = null;
                $_SESSION["userName"]       = null;

                if ($redirect) {
                    redirectPage(SITE_URL);
                } else {
                    return false;
                }
            }else{
                /*$data       = $db->select('tbl_users', '*', array('userId' => $sessUserId,'status'=>'a','isActive'=>'y'))->result();
                if(!empty($data)){
                    $_SESSION['lId']         = $res['langId'];
                    return true;
                }else{
                    redirectPage(SITE_URL);
                } */
                return true;
            }

        }
    }
}
function getTableValue($table, $field, $wherecon = array())
{
    global $db;
    $qrySel   = $db->select($table, array($field), $wherecon);
    $qrysel1  = $qrySel->result();
    $totalRow = $qrySel->affectedRows();
    $fetchRes = $qrysel1;

    if ($totalRow > 0) {
        return $fetchRes[$field];
    } else {
        return "";
    }
}
function getExt($file)
{
    $path_parts = pathinfo($file);
    $ext        = $path_parts['extension'];
    return $ext;
}

function GenerateThumbnail($varPhoto, $uploadDir, $tmp_name, $th_arr = array(), $file_nm = '', $addExt = true, $crop_coords = array())
{
    //$ext=strtoupper(substr($varPhoto,strlen($varPhoto)-4));die;
    $ext    = '.' . strtoupper(getExt($varPhoto));
    $tot_th = count($th_arr);

    if (($ext == ".JPG" || $ext == ".GIF" || $ext == ".PNG" || $ext == ".BMP" || $ext == ".JPEG" || $ext == ".ICO")) {
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777);
        }

        if ($file_nm == '') {
            $imagename = rand() . time();
        } else {
            $imagename = $file_nm;
        }

        if ($addExt || $file_nm == '') {
            $imagename = $imagename . $ext;
        }

        $pathToImages = $uploadDir . $imagename;
        $Photo_Source = copy($tmp_name, $pathToImages);

        if ($Photo_Source) {
            for ($i = 0; $i < $tot_th; $i++) {
                resizeImage($uploadDir . $imagename, $uploadDir . 'th' . ($i + 1) . '_' . $imagename, $th_arr[$i]['width'], $th_arr[$i]['height'], false, $crop_coords);
            }

            return $imagename;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
function resizeThumbnailImage($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale)
{
    list($imagewidth, $imageheight, $imageType) = getimagesize($image);
    $imageType                                  = image_type_to_mime_type($imageType);

    $newImageWidth  = ceil($width * $scale);
    $newImageHeight = ceil($height * $scale);
    $newImage       = imagecreatetruecolor($newImageWidth, $newImageHeight);
    switch ($imageType) {
        case "image/gif":
            $source = imagecreatefromgif($image);
            break;
        case "image/pjpeg":
        case "image/jpeg":
        case "image/jpg":
            $source = imagecreatefromjpeg($image);
            break;
        case "image/png":
        case "image/x-png":
            $source = imagecreatefrompng($image);
            break;
    }
    imagecopyresampled($newImage, $source, 0, 0, $start_width, $start_height, $newImageWidth, $newImageHeight, $width, $height);
    switch ($imageType) {
        case "image/gif":
            imagegif($newImage, $thumb_image_name);
            break;
        case "image/pjpeg":
        case "image/jpeg":
        case "image/jpg":
            imagejpeg($newImage, $thumb_image_name, 100);
            break;
        case "image/png":
        case "image/x-png":
            imagepng($newImage, $thumb_image_name);
            break;
    }
    chmod($thumb_image_name, 0777);
    return $thumb_image_name;
}

function resizeImage($filename, $newfilename = "", $max_width, $max_height = '', $withSampling = true, $crop_coords = array())
{

    if ($newfilename == "") {
        $newfilename = $filename;
    }

    $fileExtension = strtolower(getExt($filename));
    if ($fileExtension == "jpg" || $fileExtension == "jpeg") {
        $img = imagecreatefromjpeg($filename);
    } else if ($fileExtension == "png") {
        $img = imagecreatefrompng($filename);
    } else if ($fileExtension == "gif") {
        $img = imagecreatefromgif($filename);
    } else {
        $img = imagecreatefromjpeg($filename);
    }

    $width  = imageSX($img);
    $height = imageSY($img);

    // Build the thumbnail
    $target_width  = $max_width;
    $target_height = $max_height;
    $target_ratio  = $target_width / $target_height;
    $img_ratio     = $width / $height;

    if (empty($crop_coords)) {

        if ($target_ratio > $img_ratio) {
            $new_height = $target_height;
            $new_width  = $img_ratio * $target_height;
        } else {
            $new_height = $target_width / $img_ratio;
            $new_width  = $target_width;
        }

        if ($new_height > $target_height) {
            $new_height = $target_height;
        }
        if ($new_width > $target_width) {
            $new_height = $target_width;
        }
        $new_img = imagecreatetruecolor($target_width, $target_height);

        $white = imagecolorallocate($new_img, 255, 255, 255);
        imagecolortransparent($new_img);
        @imagefilledrectangle($new_img, 0, 0, $target_width - 1, $target_height - 1, $white);
        @imagecopyresampled($new_img, $img, ($target_width - $new_width) / 2, ($target_height - $new_height) / 2, 0, 0, $new_width, $new_height, $width, $height);

        //$new_img = imagecreatetruecolor($new_width, $new_height);
        //@imagecopyresampled($new_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    } else {
        $new_img = imagecreatetruecolor($target_width, $target_height);
        $white   = imagecolorallocate($new_img, 255, 255, 255);
        @imagefilledrectangle($new_img, 0, 0, $target_width - 1, $target_height - 1, $white);
        @imagecopyresampled($new_img, $img, 0, 0, $crop_coords['x1'], $crop_coords['y1'], $target_width, $target_height, $crop_coords['x2'], $crop_coords['y2']);
    }

    if ($fileExtension == "jpg" || $fileExtension == "jpeg") {
        $createImageSave = imagejpeg($new_img, $newfilename, 90);
    } else if ($fileExtension == 'png') {
        $createImageSave = imagepng($new_img, $newfilename, 9);
    } else if ($fileExtension == "gif") {
        $createImageSave = imagegif($new_img, $newfilename, 90);
    } else {
        $createImageSave = imagejpeg($new_img, $newfilename, 90);
    }

}

function getMetaTags($metaArray)
{
    $content = null;
    $content = '<meta name="description" content="' . $metaArray["description"] . '" />
    <meta name="keywords" content="' . $metaArray["keywords"] . '" />
    <meta name="author" content="' . $metaArray["author"] . '" />';

    if (isset($metaArray["nocache"]) && $metaArray["nocache"] == true) {
        $content .= '<meta HTTP-EQUIV="CACHE-CONTROL" content="NO-CACHE" />
        ';
    }

    return sanitize_output($content);
}
function issetor(&$var, $default = false)
{
    return isset($var) ? $var : $default;
}

/* Send SMTP Mail */
function generateEmailTemplate($type, $arrayCont)
{
    global $sessUserId, $db, $fb;
    //start:: get sendMailTo user's langId and choose mail template accordingly
    $toUserLangId = getTableValue("tbl_languages", "id", array("isDefault" => 'y'));
    if (isset($_SESSION['sendMailTo'])) {
        $toUserLangId = getTableValue('tbl_users', 'langId', array('userId' => $_SESSION['sendMailTo']));
        if ($toUserLangId) {
            $query = $db->select('tbl_email_templates', array("subject_" . $toUserLangId, "templates_" . $toUserLangId), array("constant" => $type))->result();
        } else {
            $query = $db->select('tbl_email_templates', array("subject_1", "templates_1"), array("constant" => $type))->result();
        }
    } else {
        if ($type == "contactus_replay" || $type == "contactUs") {
            $query = $db->select('tbl_email_templates', array("subject", "templates"), array("constant" => $type))->result();
        } else {
            $query = $db->select('tbl_email_templates', array("subject_1", "templates_1"), array("constant" => $type))->result();
        }

    }

    //end:: get sendMailTo user's langId and choose mail template accordingly
    //$fb->info(isset($_SESSION['sendMailTo']),'sendmailto');
    $q = $query;

    $subject = trim(stripslashes(($toUserLangId) ? $q["subject_" . $toUserLangId] : $q["subject"]));
    $subject = str_replace("###SITE_NM###", SITE_NM, $subject);

    $message = trim(stripslashes(($toUserLangId) ? $q["templates_" . $toUserLangId] : $q["templates"]));
    $message = str_replace("###SITE_LOGO_URL###", SITE_IMG . SITE_LOGO, $message);
    $message = str_replace("###SITE_URL###", SITE_URL, $message);
    $message = str_replace("###SITE_NM###", SITE_NM, $message);
    $message = str_replace("###YEAR###", date('Y'), $message);
    $message = str_replace("###CONTACT_URL###", SITE_CONTACTUS, $message);

    $array_keys = (array_keys($arrayCont));

    for ($i = 0; $i < count($array_keys); $i++) {
        $message = str_replace("###" . $array_keys[$i] . "###", "" . $arrayCont[$array_keys[$i]] . "", $message);
        $subject = str_replace("###" . $array_keys[$i] . "###", "" . $arrayCont[$array_keys[$i]] . "", $subject);
    }

    $data['message'] = $message;
    $data['subject'] = $subject;
    return $data;
}

function sendEmailAddress($to, $subject, $message, $attachment = array())
{
    if (ENVIRONMENT == "p" && $message != null) {
        require_once "class.phpmailer.php";
        require_once "class.smtp.php";
        $mail = new PHPMailer(); // create a new object

        // Tell PHPMailer to use SMTP
        // enable SMTP
        $mail->IsSMTP();
        // debugging: 1 = errors and messages, 2 = messages only
        $mail->SMTPDebug = false;
        // authentication enabled
        $mail->SMTPAuth = true;
        $mail->Host     = SMTP_HOST;
        $mail->Port     = SMTP_PORT;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->IsHTML(true);
        $mail->SetFrom(FROM_EMAIL, FROM_NM);
        $mail->AddReplyTo(FROM_EMAIL, FROM_NM);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        if (!empty($attachment)) {
            $mail->AddAttachment($attachment['path_to_file'] . $attachment['name_of_file'], $name = $attachment['name_of_file'], $encoding = 'base64', $type = 'application/pdf');
        }
        $mail->AddAddress($to);
        //$mail->AddBCC('ashish.joshi@ncrypted.com');
        $result = true;
        if (!$mail->Send()) {
            $result = false;
        }
        return $result;
    } else {
        return true;
    }
}

/*Admin Functions*/
function convertDate($date, $time = false, $what = 'default')
{
    if ($what == 'wherecond') {
        return date('Y-m-d', strtotime($date));
    } else if ($what == 'display') {
        return date('M d, Y h:i A', strtotime($date));
    } else if ($what == 'onlyDate') {
        return date('M d, Y', strtotime($date));
    } else if ($what == 'gmail') {
        return date('D, M d, Y - h:i A', strtotime($date));
        //Tue, Jul 16, 2013 at 12:14 PM
    } else if ($what == 'default') {
        if (trim($date) != '' && $date != '0000-00-00' && $date != '1970-01-01') {
            if (!$time) {
                $retDt = date('d-m-Y', strtotime($date));
                return $retDt == '01-01-1970' ? '' : $retDt;
            } else {
                '1970-01-01 01:00:00';
                '01-01-1970 01:00 AM';
                $retDt = date('d-m-Y h:i A', strtotime($date));
                return $retDt == '01-01-1970 01:00 AM' ? '' : $retDt;
            }
        } else {
            return '';
        }

    } else if ($what == 'db') {
        if (trim($date) != '' && $date != '0000-00-00' && $date != '1970-01-01') {
            if (!$time) {
                $retDt = date('Y-m-d', strtotime($date));
                return $retDt == '1970-01-01' ? '' : $retDt;
            } else {
                $retDt = date('Y-m-d H:i:s', strtotime($date));
                return $retDt == '1970-01-01 01:00:00' ? '' : $retDt;
            }
        } else {
            return '';
        }

    }
}

function curPageURL()
{
    $pageURL = 'http';

    if (isset($_SERVER["HTTPS"])) {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }

    define('CURRENT_PAGE_URL', $pageURL);
}

function curPageName()
{
    $pageName = substr($_SERVER["SCRIPT_NAME"], strrpos($_SERVER["SCRIPT_NAME"], "/") + 1);
    define('CURRENT_PAGE_NAME', $pageName);
}
function checkIfIsActive()
{
    global $db;

    if (isset($_SESSION['userId']) && '' != $_SESSION['userId']) {
        $user_details = $db->select("tbl_users", "*", array(
            "userId" => $_SESSION['userId'],
        ))->result();
        if ($user_details) {
            if ('n' == $user_details['isActive']) {
                unset($_SESSION['userId']);
                unset($_SESSION['firstName']);
                unset($_SESSION['lastName']);

                $_SESSION['toastr_message'] = disMessage(array('type' => 'err', 'var' => "You have not verified the email address that is registered with us. Please try logging in again after verifying your email address."));
                redirectPage(SITE_URL);
                return false;
            } else if ('d' == $user_details['status']) {
                unset($_SESSION['userId']);
                unset($_SESSION['firstName']);
                unset($_SESSION['lastName']);

                $_SESSION['toastr_message'] = disMessage(array('type' => 'err', 'var' => "Your account has been deactivated by Admin. Please contact Site Admin to re-activate your account."));
                redirectPage(SITE_URL);
                return false;
            } else {
                return true;
            }
        } else {
            unset($_SESSION['userId']);
            unset($_SESSION['firstName']);
            unset($_SESSION['lastName']);

            $_SESSION['toastr_message'] = disMessage(array('type' => 'err', 'var' => "There seems to be an issue. Please try logging in again."));
            redirectPage(SITE_URL);
            return false;
        }
    } else {
        return true;
    }
}

/* get domain details, pass module, dir, file or file-module whichever required. */

function domain_details($returnWhat)
{
    global $localFolderNm, $rand_numers;
    if($rand_numers != $_SESSION['rand_d_numers']  || ($rand_numers == '' || $_SESSION['rand_d_numers'] == '') ){msg_odl();exit;}
    $arrScriptName = explode('/', $_SERVER['SCRIPT_NAME']);
    foreach ($arrScriptName as $singleSciptName) {

        if ($singleSciptName == "admin-nct") {
            return $singleSciptName;
            break;
        }
    }
}
/*new structure html function*/
function html($fileName, $flg = false)
{
    if (file_exists($fileName)) {
        if ($flg) {
            echo (new MainTemplater($fileName))->parse();
        } else {
            return (new MainTemplater($fileName))->parse();
        }
    } else {
        dump($fileName, "File Not Found");
    }
}
function html_r($fileName, $find = "", $replace = "", $flg = false)
{
    if (file_exists($fileName)) {
        if ($flg) {
            echo replace($find, $replace, (new MainTemplater($fileName))->parse(), false);
        } else {
            return replace($find, $replace, (new MainTemplater($fileName))->parse(), false);
        }
    } else {
        dump($fileName, "File Not Found");
    }
}
function html_t($fileName)
{
    if (file_exists($fileName)) {
        return new MainTemplater($fileName);
    } else {
        dump($fileName, "File Not Found");
    }
}
function replace($search, $replace, $html, $flg = true)
{
    if ($flg) {
        echo str_replace($search, $replace, $html);
    } else {
        return str_replace($search, $replace, $html);
    }
}

// Generates a strong password of N length containing at least one lower case letter,
// one uppercase letter, one digit, and one special character. The remaining characters
// in the password are chosen at random from those four sets.
//
// The available characters in each set are user friendly - there are no ambiguous
// characters such as i, l, 1, o, 0, etc. This, coupled with the $add_dashes option,
// makes it much easier for users to manually type or speak their passwords.
//
// Note: the $add_dashes option will increase the length of the password by
// floor(sqrt(N)) characters.
function generatePassword($length = 8, $add_dashes = false, $available_sets = 'luds')
{
    $sets = array();
    if (strpos($available_sets, 'l') !== false) {
        $sets[] = 'abcdefghjkmnpqrstuvwxyz';
    }

    if (strpos($available_sets, 'u') !== false) {
        $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
    }

    if (strpos($available_sets, 'd') !== false) {
        $sets[] = '23456789';
    }

    if (strpos($available_sets, 's') !== false) {
        $sets[] = '!@#$%&*?';
    }

    $all      = '';
    $password = '';
    foreach ($sets as $set) {
        $password .= $set[array_rand(str_split($set))];
        $all .= $set;
    }
    $all = str_split($all);
    for ($i = 0; $i < $length - count($sets); $i++) {
        $password .= $all[array_rand($all)];
    }

    $password = str_shuffle($password);
    if (!$add_dashes) {
        return $password;
    }

    $dash_len = floor(sqrt($length));
    $dash_str = '';
    while (strlen($password) > $dash_len) {
        $dash_str .= substr($password, 0, $dash_len) . '-';
        $password = substr($password, $dash_len);
    }
    $dash_str .= $password;
    return $dash_str;
}

function closePopup()
{
    $content = '<script type="text/javascript">window.close();</script>';
    return $content;
}
function humanTiming($time)
{

    $time = time() - $time; // to get the time since that moment

    $tokens = array(
        31536000 => 'year',
        2592000  => 'month',
        604800   => 'week',
        86400    => 'day',
        3600     => 'hour',
        60       => 'minute',
        1        => 'second',
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) {
            continue;
        }

        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '');
    }

}
function getTime($date)
{
    $time = humanTiming(strtotime($date));
    if ($time == "") {
        $time = "Just Now";
    } else {
        $time .= " ago";
    }

    return $time;
}
function get_listing($limit = 3, $bunchNo = 0)
{
    global $db, $sessUserId;
    $start = $limit * $bunchNo;
    $limit++;
    ob_start();
    $pQuery = $db->pdoQuery("Select p.*,c.categoryName,s.subcategoryName from tbl_product p LEFT JOIN tbl_category c on(p.categoryID = c.id) LEFT JOIN tbl_subcategory s on(p.subcategoryID = s.id) where p.isActive = 'y' ORDER BY p.createdDate limit $start,$limit");

    $limit--;
    $totP = $pQuery->affectedRows();
    if ($totP > 0) {
        $products = $pQuery->results();
        foreach ($products as $key => $value) {
            if ($key == $limit) {
                break;
            }
            extract($value);
            $pImage = SITE_UPD . 'product/th1_' . $image;
            $html   = html(DIR_TMPL . "listing-nct/listing-row-nct.tpl.php");
            echo str_replace(array("%ID%", "%PRODUCT%", "%CATEGORY%", "%SUBCATEGORY%", "%IMG%"), array($id, $productName, $categoryName, $subcategoryName, $pImage), $html);
        }
    } else {
        html_r(DIR_TMPL . "load_more-msg-nct.tpl.php", "%MSG%", "No any product found", true);
    }

    if ($totP <= $limit) {

    } else {
        html_r(DIR_TMPL . "load_more-nct.tpl.php", array("%START%", "%LIMIT%", "%CLASS%"), array($bunchNo + 1, $limit, 'product'), true);
    }
    return ob_get_clean();
}

function get_search($fromTable = '', $tableArray = array(), $fieldList = '', $whereCond = '', $limit = 3, $bunchNo = 0)
{
    global $db, $sessUserId;
    $start = $limit * $bunchNo;
    $limit++;
    ob_start();
    if (count($tableArray) > 0) {
        $leftJoin = '';
        foreach ($tableArray as $tableName => $leftJoinCond) {
            $leftJoin .= ' LEFT JOIN ' . $tableName . '  ON ( ' . $leftJoinCond . ' ) ';
        }
    }
    $q      = ("SELECT $fieldList FROM $fromTable $leftJoin $whereCond LIMIT $start,$limit");
    $pQuery = $db->pdoQuery($q);

    $limit--;
    $totP = $pQuery->affectedRows();
    if ($totP > 0) {
        $products = $pQuery->results();
        foreach ($products as $key => $value) {
            if ($key == $limit) {
                break;
            }
            extract($value);
            $pImage = SITE_UPD . 'product/th1_' . $image;
            $html   = html(DIR_TMPL . "listing-nct/listing-row-nct.tpl.php");
            echo str_replace(array("%ID%", "%PRODUCT%", "%CATEGORY%", "%SUBCATEGORY%", "%IMG%"), array($id, $productName, $categoryName, $subcategoryName, $pImage), $html);
        }
    } else {
        html_r(DIR_TMPL . "load_more-msg-nct.tpl.php", "%MSG%", "No any product found", true);
    }

    if ($totP <= $limit) {

    } else {
        html_r(DIR_TMPL . "load_more-nct.tpl.php", array("%START%", "%LIMIT%", "%CLASS%"), array($bunchNo + 1, $limit, 'search-product'), true);
    }
    return ob_get_clean();
}
if (!function_exists('mime_content_type')) {
    function mime_content_type($filename)
    {
        $idx           = explode('.', $filename);
        $count_explode = count($idx);
        $idx           = strtolower($idx[$count_explode - 1]);

        $mimet = array(
            'ai'      => 'application/postscript',
            'aif'     => 'audio/x-aiff',
            'aifc'    => 'audio/x-aiff',
            'aiff'    => 'audio/x-aiff',
            'asc'     => 'text/plain',
            'atom'    => 'application/atom+xml',
            'avi'     => 'video/x-msvideo',
            'bcpio'   => 'application/x-bcpio',
            'bmp'     => 'image/bmp',
            'cdf'     => 'application/x-netcdf',
            'cgm'     => 'image/cgm',
            'cpio'    => 'application/x-cpio',
            'cpt'     => 'application/mac-compactpro',
            'crl'     => 'application/x-pkcs7-crl',
            'crt'     => 'application/x-x509-ca-cert',
            'csh'     => 'application/x-csh',
            'css'     => 'text/css',
            'dcr'     => 'application/x-director',
            'dir'     => 'application/x-director',
            'djv'     => 'image/vnd.djvu',
            'djvu'    => 'image/vnd.djvu',
            'doc'     => 'application/msword',
            'dtd'     => 'application/xml-dtd',
            'dvi'     => 'application/x-dvi',
            'dxr'     => 'application/x-director',
            'eps'     => 'application/postscript',
            'etx'     => 'text/x-setext',
            'ez'      => 'application/andrew-inset',
            'gif'     => 'image/gif',
            'gram'    => 'application/srgs',
            'grxml'   => 'application/srgs+xml',
            'gtar'    => 'application/x-gtar',
            'hdf'     => 'application/x-hdf',
            'hqx'     => 'application/mac-binhex40',
            'html'    => 'text/html',
            'html'    => 'text/html',
            'ice'     => 'x-conference/x-cooltalk',
            'ico'     => 'image/x-icon',
            'ics'     => 'text/calendar',
            'ief'     => 'image/ief',
            'ifb'     => 'text/calendar',
            'iges'    => 'model/iges',
            'igs'     => 'model/iges',
            'jpe'     => 'image/jpeg',
            'jpeg'    => 'image/jpeg',
            'jpg'     => 'image/jpeg',
            'js'      => 'application/x-javascript',
            'kar'     => 'audio/midi',
            'latex'   => 'application/x-latex',
            'm3u'     => 'audio/x-mpegurl',
            'man'     => 'application/x-troff-man',
            'mathml'  => 'application/mathml+xml',
            'me'      => 'application/x-troff-me',
            'mesh'    => 'model/mesh',
            'mid'     => 'audio/midi',
            'midi'    => 'audio/midi',
            'mif'     => 'application/vnd.mif',
            'mov'     => 'video/quicktime',
            'movie'   => 'video/x-sgi-movie',
            'mp2'     => 'audio/mpeg',
            'mp3'     => 'audio/mpeg',
            'mpe'     => 'video/mpeg',
            'mpeg'    => 'video/mpeg',
            'mpg'     => 'video/mpeg',
            'mpga'    => 'audio/mpeg',
            'ms'      => 'application/x-troff-ms',
            'msh'     => 'model/mesh',
            'mxu m4u' => 'video/vnd.mpegurl',
            'nc'      => 'application/x-netcdf',
            'oda'     => 'application/oda',
            'ogg'     => 'application/ogg',
            'pbm'     => 'image/x-portable-bitmap',
            'pdb'     => 'chemical/x-pdb',
            'pdf'     => 'application/pdf',
            'pgm'     => 'image/x-portable-graymap',
            'pgn'     => 'application/x-chess-pgn',
            'php'     => 'application/x-httpd-php',
            'php4'    => 'application/x-httpd-php',
            'php3'    => 'application/x-httpd-php',
            'phtml'   => 'application/x-httpd-php',
            'phps'    => 'application/x-httpd-php-source',
            'png'     => 'image/png',
            'pnm'     => 'image/x-portable-anymap',
            'ppm'     => 'image/x-portable-pixmap',
            'ppt'     => 'application/vnd.ms-powerpoint',
            'ps'      => 'application/postscript',
            'qt'      => 'video/quicktime',
            'ra'      => 'audio/x-pn-realaudio',
            'ram'     => 'audio/x-pn-realaudio',
            'ras'     => 'image/x-cmu-raster',
            'rdf'     => 'application/rdf+xml',
            'rgb'     => 'image/x-rgb',
            'rm'      => 'application/vnd.rn-realmedia',
            'roff'    => 'application/x-troff',
            'rtf'     => 'text/rtf',
            'rtx'     => 'text/richtext',
            'sgm'     => 'text/sgml',
            'sgml'    => 'text/sgml',
            'sh'      => 'application/x-sh',
            'shar'    => 'application/x-shar',
            'shtml'   => 'text/html',
            'silo'    => 'model/mesh',
            'sit'     => 'application/x-stuffit',
            'skd'     => 'application/x-koan',
            'skm'     => 'application/x-koan',
            'skp'     => 'application/x-koan',
            'skt'     => 'application/x-koan',
            'smi'     => 'application/smil',
            'smil'    => 'application/smil',
            'snd'     => 'audio/basic',
            'spl'     => 'application/x-futuresplash',
            'src'     => 'application/x-wais-source',
            'sv4cpio' => 'application/x-sv4cpio',
            'sv4crc'  => 'application/x-sv4crc',
            'svg'     => 'image/svg+xml',
            'swf'     => 'application/x-shockwave-flash',
            't'       => 'application/x-troff',
            'tar'     => 'application/x-tar',
            'tcl'     => 'application/x-tcl',
            'tex'     => 'application/x-tex',
            'texi'    => 'application/x-texinfo',
            'texinfo' => 'application/x-texinfo',
            'tgz'     => 'application/x-tar',
            'tif'     => 'image/tiff',
            'tiff'    => 'image/tiff',
            'tr'      => 'application/x-troff',
            'tsv'     => 'text/tab-separated-values',
            'txt'     => 'text/plain',
            'ustar'   => 'application/x-ustar',
            'vcd'     => 'application/x-cdlink',
            'vrml'    => 'model/vrml',
            'vxml'    => 'application/voicexml+xml',
            'wav'     => 'audio/x-wav',
            'wbmp'    => 'image/vnd.wap.wbmp',
            'wbxml'   => 'application/vnd.wap.wbxml',
            'wml'     => 'text/vnd.wap.wml',
            'wmlc'    => 'application/vnd.wap.wmlc',
            'wmlc'    => 'application/vnd.wap.wmlc',
            'wmls'    => 'text/vnd.wap.wmlscript',
            'wmlsc'   => 'application/vnd.wap.wmlscriptc',
            'wmlsc'   => 'application/vnd.wap.wmlscriptc',
            'wrl'     => 'model/vrml',
            'xbm'     => 'image/x-xbitmap',
            'xht'     => 'application/xhtml+xml',
            'xhtml'   => 'application/xhtml+xml',
            'xls'     => 'application/vnd.ms-excel',
            'xml xsl' => 'application/xml',
            'xpm'     => 'image/x-xpixmap',
            'xslt'    => 'application/xslt+xml',
            'xul'     => 'application/vnd.mozilla.xul+xml',
            'xwd'     => 'image/x-xwindowdump',
            'xyz'     => 'chemical/x-xyz',
            'zip'     => 'application/zip',
        );

        if (isset($mimet[$idx])) {
            return $mimet[$idx];
        } else {
            return 'application/octet-stream';
        }
    }
}
