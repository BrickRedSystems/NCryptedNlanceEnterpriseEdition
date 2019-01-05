<?php
class Dashboard {
    function __construct($module = "", $id = 0, $reqData = array()) {
        global $js_variables;
        foreach ($GLOBALS as $key => $values) {
            $this -> $key = $values;
        }
        $this -> module = $module;
        $this -> id = $id;
        $this -> reqData = $reqData;
        $this -> table = "tbl_users";
        $this->sessUserId = (isset($reqData['userId']) && $reqData['userId'] >0)?$reqData['userId']:$this->sessUserId;
        $this -> dataOnly = (isset($this->reqData['dataOnly']) && $this->reqData['dataOnly']==true)?true:false;
        $this -> user = $this -> userData();
        $js_variables = (isset($this -> user['userType']) && $this -> user['userType'] == "p") ? "var method ='reviews_rows'" : "var method ='project_rows'";
        if($this->dataOnly){
            $_SESSION['lId'] = (isset($this->reqData['lId']) && $this->reqData['lId'] >0)?$this->reqData['lId']:1;  
            setLang();  
        }
    }

    public function get_contact() {
        $replace = array(
            '%email%' => filtering($this -> user['email']),
            '%contact_no%' => ($this -> user['contactCode']!=null && $this -> user['contactNo']!=null)?filtering("+" . $this -> user['contactCode'] . " - " . $this -> user['contactNo']):Not_Available,
        );
        return get_view(DIR_TMPL . $this -> module . "/contact-nct.tpl.php", $replace);
    }

    public function notification_rows($count = false) {

        if ($count) {
            $totUnread = $this -> db -> pdoQuery("SELECT n.* FROM tbl_notification AS n WHERE n.toUserId=? AND n.isReaded='n' ORDER BY n.id DESC", array($this -> sessUserId)) -> affectedRows();
            return $totUnread;
        }

        //$results = $this -> db -> pdoQuery("SELECT nt.`type`, nt.color, n.* FROM tbl_notification AS n LEFT JOIN tbl_notification_types AS nt ON n.`typeId` = nt.id WHERE n.toUserId=? ORDER BY n.id DESC ", array($this -> sessUserId)) ;
        $results = $this->db->pdoQuery("SELECT nt.color, n.*, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName, u.profileLink, u.profilePhoto FROM tbl_notification AS n LEFT JOIN tbl_users AS u ON n.fromUserId = u.userId LEFT JOIN tbl_notification_types AS nt ON n.`typeId` = nt.id WHERE n.`toUserId` = ? ORDER BY n.id DESC LIMIT 5 ", array($this->sessUserId));

        if($this->dataOnly){
            $allNotifications = $results->results();
            foreach ($allNotifications as $key => $value) {
                $allNotifications[$key]['notification'] = filtering(get_user_notification($value['id']));
            }
            return $allNotifications;
        }else{
            $results = $results->results();
        }


        $html = null;
        if (!empty($results)) {
            foreach ($results as $k => $v) {
                extract($v);

                $noti_text = filtering(get_user_notification($id));
            
                $replace = array(
                    '%class%' => ($isReaded == 'n') ? 'unread' : null,
                    '%notification%' => filtering(get_user_notification($id)),
                    '%createdDate%' => date(PHP_DATE_FORMAT, strtotime($createdDate)),
                    '%notiLink%' => get_user_notification($id, $getLink = true),
                    '%title%' => $noti_text,
                    '%color%' => $color,
                );
                $html .= get_view(DIR_TMPL . $this -> module . "/noti_row-nct.tpl.php", $replace);
            }
        }
        else {
            $html = get_view(DIR_TMPL . $this -> module . "/no_noti_row-nct.tpl.php");
        }
        return $html;
    }

    public function get_review() {
        $results = $this -> db -> pdoQuery("SELECT AVG(f.averageRating) AS average, count(f.id) AS total FROM tbl_feedbacks AS f LEFT JOIN tbl_users AS u ON f.`userfrom` = u.userid WHERE f.userto = ? AND u.isactive = 'y' ", array('userTo' => $this -> user['userId'])) -> result();
        $replace = array(
            '%stars%' => renderStarRating($results['average']),
            '%total%' => $results['total'],
            '%profileLink%' => SITE_URL.$this->user['profileLink'],
        );
        return get_view(DIR_TMPL . $this -> module . "/review-nct.tpl.php", $replace);
    }

    public function total_money($userId, $userType = NULL) {        
        global $db;
        if ($userType == 'c') {
            //total spent
            $total = $db -> pdoQuery("SELECT IFNULL(SUM(ph.totalAmount) + SUM(ph.adminCommission), 0) AS totalAmount FROM tbl_payment_history AS ph WHERE ph.`userId` = ? AND (ph.paymentType = 'featured' OR ph.paymentType = 'project payment' )", array($userId)) -> result();
        }
        elseif ($userType == 'p') {
            //total earned
            $total = $db -> pdoQuery("SELECT IFNULL(SUM(m.price), 0) AS totalAmount FROM tbl_milestone AS m INNER JOIN tbl_projects AS p ON m.projectId = p.id WHERE p.providerId = ? AND m.status = 'paid'", array($userId)) -> result();
        }
        else {
            return "0.00";
        }
        return abs($total['totalAmount']);
    }

    public function getPageContent() {
        $user = $this -> user;
        //mark all notifications as read
        $this->db->update('tbl_notification',array('isReaded'=>'y'),array('toUserId'=>$this->sessUserId));
        
        // note... if both profile type are different then hide contact details else show them
        $exp = str_replace(array(
            ' ',
        ), array(
            '_',
        ), ucwords($user['experience']));
        $replace = array(
            //top bar
            '%profilePhoto%' => tim_thumb_image($user['profilePhoto'], 'profile', 300, 300),
            '%level_hide%' => ($user['userType'] == 'p') ? null : 'hide',
            '%level%' => ($user['userType'] == 'p') ? constant($exp) : null,
            '%profileLink%' => $user['profileLink'],
            '%edit_icon%' => null,
            '%fullName%' => $user['fullName'],
            '%userType%' => ($user['userType'] == 'p') ? Provider : Customer,
            '%earned_or_spent_number%' => CURRENCY_SYMBOL . $this -> total_money($user['userId'], $user['userType']),
            '%earned_or_spent_text%' => ($user['userType'] == 'p') ? Earned : Spent,
            '%hide_earned_only%' => ($user['userType'] == 'p') ? 'hide' : null,
            '%comp_proj%' => $user['completed'],
            '%ongoing_proj%' => $user['ongoing'],
            '%review%' => ($user['userType'] == 'p') ? $this -> get_review() : null,


            //contact
            '%contact%' => ($this->sessUserId>0)?$this -> get_contact():null,

            //verifications
            '%f_check%' => ($user['facebook_verify'] == '1') ? 'fa-check-circle' : 'fa-times-circle',
            '%f_connected%' => ($user['facebook_verify'] == '1') ? Connected : null,
            '%f_link%' => ($user['facebook_verify'] == '0' || $user['facebook_verify'] == null) ? "<a href='javascript:void(0)' onclick='verify(\"facebook\")' class='verify-link'>".Verify."</a>" : null,

            '%g_check%' => ($user['google_verify'] == '1') ? 'fa-check-circle' : 'fa-times-circle',
            '%g_connected%' => ($user['google_verify'] == '1') ? Connected : null,
            '%g_link%' => ($user['google_verify'] == '0' || $user['google_verify'] == null) ? "<a href='javascript:void(0)' onclick='verify(\"google\")' class='verify-link'>".Verify."</a>" : null,

            '%l_check%' => ($user['linkedin_verify'] == '1') ? 'fa-check-circle' : 'fa-times-circle',
            '%l_connected%' => ($user['linkedin_verify'] == '1') ? Connected : null,
            '%l_link%' => ($user['linkedin_verify'] == '0' || $user['linkedin_verify'] == null) ? "<a href='javascript:void(0)' onclick='verify(\"linkedin\")' class='verify-link'>".Verify."</a>" : null,

            //proj_section
            '%proj_section_headline%' => ($user['userType'] == 'p') ? Dashboard_Looking_for_Opportunities_as_Projects : Dashboard_Looking_for_Some_Good_Freelancers,
            '%redirect_link%' => ($user['userType'] == 'p') ? SITE_SEARCH : SITE_SEARCH_PROVIDERS,

            //notifications
            '%totalUnreadNoti%' => $this -> notification_rows(true),
            '%notifications%' => $this -> notification_rows()
        );

        return get_view(DIR_TMPL . $this -> module . "/" . $this -> module . ".tpl.php", $replace);
    }

    public function userData() {
        $doesExist = getTableValue($this -> table, 'userId', array(
            'userId' => $this -> sessUserId,
            'isActive' => 'y'
        ));
        if (isset($doesExist) && $doesExist > 0) {
            $arr = array();
            $arr = $this -> db -> pdoQuery("SELECT u.*, CONCAT_WS(' ',u.firstName,u.lastName) AS fullName FROM tbl_users AS u WHERE u.userId=?", array($this -> sessUserId)) -> result();
            if ($arr['userType'] == 'c') {
                $arr['ongoing'] = getTableValue('tbl_projects', 'count("id")', array(
                    'userId' => $arr['userId'],
                    'jobStatus' => 'progress',
                    'isActive' => 'y'
                ));
                $arr['completed'] = getTableValue('tbl_projects', 'count("id")', array(
                    'userId' => $arr['userId'],
                    'jobStatus' => 'completed',
                    'isActive' => 'y'
                ));
            }
            else {
                $arr['ongoing'] = getTableValue('tbl_projects', 'count("id")', array(
                    'providerId' => $arr['userId'],
                    'jobStatus' => 'progress',
                    'isActive' => 'y'
                ));
                $arr['completed'] = getTableValue('tbl_projects', 'count("id")', array(
                    'providerId' => $arr['userId'],
                    'jobStatus' => 'completed',
                    'isActive' => 'y'
                ));
            }
            if($this->dataOnly){
                $exp = str_replace(array(
                    ' ',
                ), array(
                    '_',
                ), ucwords($arr['experience']));
                $arr['experience'] = constant($exp);
            }
            return $arr;
        }
        else {
            
            if($this->dataOnly){
                return array();
            }else{
                $_SESSION["msgType"] = disMessage(array(
                    'type' => 'err',
                    'var' => toastr_url_not_found
                ));
                redirectPage(SITE_URL);
            }
            
        }
    }

}
?>
