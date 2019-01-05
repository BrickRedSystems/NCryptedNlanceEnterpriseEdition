<?php
class Profile
{
    public function __construct($module = "", $id = 0, $reqData = array())
    {
        global $js_variables;
        foreach ($GLOBALS as $key => $values) {
            $this->$key = $values;
        }
        $this->module   = $module;
        $this->id       = $id;
        $this->reqData  = $reqData;
        $this->dataOnly = (isset($this->reqData['dataOnly']) && $this->reqData['dataOnly'] == true) ? true : false;

        $this->sessUserId = (isset($reqData['userId']) && $reqData['userId'] > 0) ? $reqData['userId'] : $this->sessUserId;

        $this->table = "tbl_users";

        $this->user        = $this->userData($reqData['profileLink']);
        $this->isMe        = ($this->user['userId'] == $this->sessUserId) ? 1 : 0;
        $this->isDifferent = (isset($this->user['userType']) && $this->user['userType'] != $this->sessUserType) ? 1 : 0;
        $js_variables      = (isset($this->user['userType']) && $this->user['userType'] == "p") ? "var method ='reviews_rows'" : "var method ='project_rows'";
        if ($this->dataOnly) {
            $_SESSION['lId'] = (isset($this->reqData['lId']) && $this->reqData['lId'] > 0) ? $this->reqData['lId'] : 1;
            setLang();
        }
    }

    public function reviews_rows()
    {
        global $db;
        $html       = $qry       = $condition       = $sort_by       = null;
        $paramArray = array($this->user['userId']);
        $qry        = "SELECT IFNULL(f.averageRating, 0) AS averageRating, f.`review`, f.`addedDate`, CONCAT_WS(' ',u.`firstName`,u.`lastName`) AS fullName, u.`profilePhoto`, u.`profileLink` FROM tbl_feedbacks AS f LEFT JOIN tbl_users AS u ON f.`userFrom` = u.userId where f.userTo=? AND u.isActive='y'";

        ////////////////////////////
        // put pagination
        $limit_cond = null;

        $q         = $this->db->pdoQuery($qry . $condition . $sort_by, $paramArray);
        $totalRows = $q->affectedRows();
        $pageNo    = isset($this->reqData["pageNo"]) ? $this->reqData["pageNo"] : 0;
        $pager     = getPagerData($totalRows, LIMIT, $pageNo);
        //dump($pager);
        //dump($pageNo);
        if ($pageNo <= $pager->numPages) {
            $offset = $pager->offset;
            if ($offset < 0) {
                $offset = 0;
            }

            $limit = $pager->limit;

            $page       = $pager->page;
            $limit_cond = " LIMIT $offset, $limit";
            $qry        = $this->db->pdoQuery($qry . $condition . $sort_by . $limit_cond, $paramArray);

            if ($this->dataOnly) {
                return $qry->results();
            }

        }
        ////////////////////////////

        if (is_object($qry) && $qry->affectedRows() > 0) {
            $results = $qry->results();
            foreach ($results as $k => $v) {
                extract($v);
                $replace = array(
                    '%averageRating%' => renderStarRating($averageRating),
                    '%review%'        => filtering(nl2br($review)),
                    '%addedDate%'     => date(PHP_DATE_FORMAT, strtotime($addedDate)),
                    '%fullName%'      => ucwords(filtering($fullName)),
                    '%profileLink%'   => filtering($profileLink),
                    '%profilePhoto%'  => tim_thumb_image($profilePhoto, 'profile', 100, 100),
                );
                $html .= get_view(DIR_TMPL . $this->module . "/reviews_and_ratings_row-nct.tpl.php", $replace);
            }
        } else {
            $html .= get_view(DIR_TMPL . $this->module . "/no_reviews_and_ratings_row-nct.tpl.php", array('%text%' => This_user_has_not_received_any_reviews_yet));
        }

        return $html;
    }

    public function project_rows($project_status = 'open')
    {
        global $db;
        $html       = $qry       = $condition       = $sort_by       = null;
        $paramArray = array($this->user['userId']);

        $project_status = (isset($this->reqData['status'])) ? $this->reqData['status'] : $project_status;
        switch ($project_status) {
            case 'open':
                if ($this->user['userType'] == 'c') {
                    $qry = "SELECT p.id, p.title, COUNT(b.id) AS bids, p.slug, p.`description`, p.budget AS budget, p.`isFeatured`, p.`slug`, u.`profileLink`, u.`profilePhoto`, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName FROM tbl_projects AS p LEFT JOIN tbl_users AS u ON p.userId = u.userId LEFT JOIN tbl_bids AS b ON p.`id` = b.`projectId` WHERE p.`isActive` = 'y' AND u.`isActive` = 'y' AND p.`jobStatus` = 'open' AND p.userId=? GROUP BY p.id ORDER BY p.`id` DESC  ";
                } else {
                    $qry = "SELECT p.id, p.title, COUNT(b.id) AS bids, p.slug, p.`description`, p.budget AS budget, p.`isFeatured`, p.`slug`, u.`profileLink`, u.`profilePhoto`, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName FROM tbl_projects AS p LEFT JOIN tbl_users AS u ON p.providerId = u.userId LEFT JOIN tbl_bids AS b ON p.`id` = b.`projectId` WHERE p.`isActive` = 'y' AND u.`isActive` = 'y' AND p.`jobStatus` = 'open' AND p.providerId=? GROUP BY p.id ORDER BY p.`id` DESC  ";
                }
                $est_or_total = Est_Budget;
                break;
            case 'completed':
                if ($this->user['userType'] == 'c') {
                    $qry = "SELECT p.id, p.title, COUNT(b.id) AS bids, p.slug, p.`description`, p.price AS budget, p.`isFeatured`, p.`slug`, u.`profileLink`, u.`profilePhoto`, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName FROM tbl_projects AS p LEFT JOIN tbl_users AS u ON p.userId = u.userId LEFT JOIN tbl_bids AS b ON p.`id` = b.`projectId` WHERE p.`isActive` = 'y' AND u.`isActive` = 'y' AND p.`jobStatus` = 'completed' AND p.userId=? GROUP BY p.id ORDER BY p.`id` DESC  ";
                } else {
                    $qry = "SELECT p.id, p.title, COUNT(b.id) AS bids, p.slug, p.`description`, p.price AS budget, p.`isFeatured`, p.`slug`, u.`profileLink`, u.`profilePhoto`, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName FROM tbl_projects AS p LEFT JOIN tbl_users AS u ON p.providerId = u.userId LEFT JOIN tbl_bids AS b ON p.`id` = b.`projectId` WHERE p.`isActive` = 'y' AND u.`isActive` = 'y' AND p.`jobStatus` = 'completed' AND p.providerId=? GROUP BY p.id ORDER BY p.`id` DESC  ";
                }
                $est_or_total = Total_Budget;
                break;
        }

        ////////////////////////////
        // put pagination
        $limit_cond = null;

        $q         = $this->db->pdoQuery($qry . $condition . $sort_by, $paramArray);
        $totalRows = $q->affectedRows();
        $pageNo    = isset($this->reqData["pageNo"]) ? $this->reqData["pageNo"] : 0;
        $pager     = getPagerData($totalRows, LIMIT, $pageNo);
        //dump($pager);
        //dump($pageNo);
        if ($pageNo <= $pager->numPages) {
            $offset = $pager->offset;
            if ($offset < 0) {
                $offset = 0;
            }

            $limit = $pager->limit;

            $page       = $pager->page;
            $limit_cond = " LIMIT $offset, $limit";
            $qry        = $this->db->pdoQuery($qry . $condition . $sort_by . $limit_cond, $paramArray);

        }
        ////////////////////////////

        if (is_object($qry) && $qry->affectedRows() > 0) {
            $results = $qry->results();
            foreach ($results as $k => $v) {
                $replace = array(
                    '%profilePhoto%' => tim_thumb_image($v['profilePhoto'], 'profile', 75, 75),
                    '%profileLink%'  => filtering($v['profileLink']),
                    '%fullName%'     => ucwords(filtering($v['fullName'])),
                    '%slug%'         => $v['profileLink'] . "/" . $v['slug'],
                    '%title%'        => ucfirst(filtering($v['title'])),
                    '%isFeatured%'   => ($v['isFeatured'] == 'n') ? 'hide' : null,
                    '%desc%'         => filtering(String_crop($v['description'], 230)),
                    '%est_or_total%' => $est_or_total,
                    '%budget%'       => CURRENCY_SYMBOL . filtering($v['budget']),
                    '%bids%'         => filtering($v['bids']),
                    '%pid%'          => $v['id'],
                    '%like_icon%'    => is_my_fav($v['id'], 'project') ? 'fa-heart' : 'fa-heart-o',
                );
                if ($this->isDifferent) {
                    $html .= get_view(DIR_TMPL . $this->module . "/project_row_with_heart-nct.tpl.php", $replace);
                } else {
                    $html .= get_view(DIR_TMPL . $this->module . "/project_row-nct.tpl.php", $replace);
                }

            }
        } else {
            $html .= get_view(DIR_TMPL . $this->module . "/no_project_row-nct.tpl.php", array('%status%' => constant(ucwords($project_status))));
        }

        return $html;
    }

    public function get_contact()
    {
        $replace = array(
            '%email%'      => filtering($this->user['email']),
            '%contact_no%' => filtering("+" . $this->user['contactCode'] . " - " . $this->user['contactNo']),
        );
        return get_view(DIR_TMPL . $this->module . "/contact-nct.tpl.php", $replace);
    }

    public function get_review()
    {
        $results = $this->db->pdoQuery("SELECT AVG(f.averageRating) AS average, count(f.id) AS total FROM tbl_feedbacks AS f LEFT JOIN tbl_users AS u ON f.`userfrom` = u.userid WHERE f.userto = ? AND u.isactive = 'y' ", array('userTo' => $this->user['userId']))->result();
        $replace = array(
            '%stars%' => renderStarRating($results['average']),
            '%total%' => $results['total'],
        );

        if ($this->dataOnly) {
            return array('total_review' => $results['total'], 'average' => $results['average']);
        }
        return get_view(DIR_TMPL . $this->module . "/review-nct.tpl.php", $replace);
    }

    public function total_money($userId, $userType = null)
    {
        global $db;
        if ($userType == 'c') {
            //total spent
            $total = $db->pdoQuery("SELECT IFNULL(SUM(ph.totalAmount) + SUM(ph.adminCommission), 0) AS totalAmount FROM tbl_payment_history AS ph WHERE ph.`userId` = ? AND (ph.paymentType = 'featured' OR ph.paymentType = 'project payment' )", array($userId))->result();
        } elseif ($userType == 'p') {
            //total earned
            $total = $db->pdoQuery("SELECT IFNULL(SUM(m.price), 0) AS totalAmount FROM tbl_milestone AS m INNER JOIN tbl_projects AS p ON m.projectId = p.id WHERE p.providerId = ? AND m.status = 'paid'", array($userId))->result();
        } else {
            return "0.00";
        }

        return abs($total['totalAmount']);
    }

    public function getPageContent()
    {
        $user = $this->user;
        // note... if both profile type are different then hide contact details else show them
        $exp = str_replace(array(
            ' ',
        ), array(
            '_',
        ), ucwords($user['experience']));
        $replace = array(
            //top bar
            '%profilePhoto%'           => tim_thumb_image($user['profilePhoto'], 'profile', 300, 300),
            '%level_hide%'             => ($user['userType'] == 'p') ? null : 'hide',
            '%level%'                  => ($user['userType'] == 'p') ? constant($exp) : null,
            '%profileLink%'            => $user['profileLink'],
            '%userId%'                 => $user['userId'],
            '%edit_icon%'              => ($this->isMe) ? null : 'hide',
            '%heart_hide%'             => (($this->isMe || $user['userType'] == 'c') || $this->sessUserType == 'p') ? 'hide' : null,
            '%heart_icon%'             => ($user['isFavorite']) ? 'fa-heart' : 'fa-heart-o',
            '%fullName%'               => ucwords($user['fullName']),
            '%userType%'               => ($user['userType'] == 'p') ? Provider : Customer,
            '%earned_or_spent_number%' => CURRENCY_SYMBOL . $this->total_money($user['userId'], $user['userType']),
            '%earned_or_spent_text%'   => ($user['userType'] == 'p') ? Earned : Spent,
            '%comp_proj%'              => $user['completed'],
            '%ongoing_proj%'           => $user['ongoing'],
            '%review%'                 => ($user['userType'] == 'p') ? $this->get_review() : null,
            '%inviteProviderBtn%'      => $this->get_inviteProviderBtn(),

            //contact
            '%contact%'                => ($this->isDifferent && (int) $this->sessUserId > 0) ? $this->get_contact() : null,

            //about me
            '%about%'                  => (trim($user['aboutMe']) != null) ? nl2br(filtering($user['aboutMe'])) : user_profile_no_about_text_message,

            //verifications
            '%f_check%'                => ($user['facebook_verify'] == '1') ? 'fa-check-circle' : 'fa-times-circle',
            '%f_connected%'            => ($user['facebook_verify'] == '1') ? Connected : null,
            '%f_link%'                 => ($this->isMe && $user['facebook_verify'] == '0') ? "<a href='javascript:void(0)' onclick='verify(\"facebook\")' class='verify-link'>" . Verify . "</a>" : null,

            '%g_check%'                => ($user['google_verify'] == '1') ? 'fa-check-circle' : 'fa-times-circle',
            '%g_connected%'            => ($user['google_verify'] == '1') ? Connected : null,
            '%g_link%'                 => ($this->isMe && $user['google_verify'] == '0') ? "<a href='javascript:void(0)' onclick='verify(\"google\")' class='verify-link'>" . Verify . "</a>" : null,

            '%l_check%'                => ($user['linkedin_verify'] == '1') ? 'fa-check-circle' : 'fa-times-circle',
            '%l_connected%'            => ($user['linkedin_verify'] == '1') ? Connected : null,
            '%l_link%'                 => ($this->isMe && $user['linkedin_verify'] == '0') ? "<a href='javascript:void(0)' onclick='verify(\"linkedin\")' class='verify-link'>" . Verify . "</a>" : null,

            //project section
            '%projects_or_reviews%'    => ($user['userType'] == 'p') ? $this->reviews_section() : $this->projects_section(),
        );

        return get_view(DIR_TMPL . $this->module . "/" . $this->module . ".tpl.php", $replace);
    }

    public function projData($id = null)
    {
        if ($id > 0) {
            $result = $this->db->pdoQuery('select p.id,p.title, p.slug, u.profileLink from tbl_projects as p left join tbl_users as u on p.userId=u.userId where p.id =?',array($id))->result();
            return $result;
        }
    }

    public function inviteProvider()
    {
        if (!isset($this->dataOnly) || !$this->dataOnly) {
            if (!checkFormToken($this->reqData['token'])) {
                $response['status']   = 0;
                $response['msg']      = Security_token_for_this_action_is_invalid_Please_refresh_and_try_again;
                $response['newToken'] = setFormToken();
                echo json_encode($response);
                exit;
            }
        }


        if ($this->dataOnly) {
            if(!isset($this->reqData['projectIds']) || trim($this->reqData['projectIds']) == null){
                return array(
                    'status' => 0,
                    'msg'    => Please_select_a_project_first,
                    'data'   => array());
            }
            $projectIds = explode(',', $this->reqData['projectIds']);
        } else {
            if(!isset($this->reqData['projectIds']) || empty($this->reqData['projectIds'])){
                echo json_encode(array(
                    'status' => 0,
                    'msg'    => Please_select_a_project_first,
                ));
                exit;
            }
            $projectIds = $this->reqData['projectIds'];
        }

        $user = $this->db->select('tbl_users', array('profileLink', 'email', 'firstName'), array(
            'userId',
            $this->reqData['suserId'],
        ))->result();

        if (!empty($projectIds)) {
            foreach ($projectIds as $key => $value) {
                //check if exist
                $check = getTableValue('tbl_project_invitation', 'id', array(
                    'projectId'  => $value,
                    'providerId' => $this->reqData['suserId'],
                ));
                if ($check > 0) {
                    $this->db->delete('tbl_project_invitation', array('id' => $check));
                } else {
                    $this->db->insert('tbl_project_invitation', array(
                        'projectId'   => $value,
                        'providerId'  => $this->reqData['suserId'],
                        'createdDate' => date('Y-m-d H:i:s'),
                        'accepted'    => 'n',
                    ));
                    $proj  = $this->projData($value);
                    $_SESSION['sendMailTo'] = issetor($this->reqData['userToInvite'],0); 

                    $array = generateEmailTemplate('inviteProvider', array(
                        'greetings'   => ucfirst($user['firstName']),
                        'fromName'    => $this->sessFirstName,
                        'projectName' => $proj['title'],
                        'projectLink' => SITE_URL . $proj['profileLink'] . '/' . $proj['slug'],
                        'date'        => date('d M, Y'),
                    ));

                    sendEmailAddress($user['email'], $array['subject'], $array['message']);

                    //send notification
                    insert_user_notification($typeId = 14, $from = $this->sessUserId, $to = $this->reqData['suserId'], $referenceId = $value, array('%userName%' => getTableValue('tbl_users', 'userName', array('userId' => $this->sessUserId,
                    )), '%projectName%' => $proj['title']));
                }

                if ($this->dataOnly) {
                    return array(
                        'status' => 1,
                        'msg'    => Your_invitation_has_been_sent,
                        'data'   => array());
                } else {
                    echo json_encode(array(
                        'status' => 1,
                        'msg'    => Your_invitation_has_been_sent,
                    ));
                    exit;
                }

            }
        } else {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => Please_select_a_project_first,
                    'data'   => array());
            }

            echo json_encode(array(
                'status' => 0,
                'msg'    => Please_select_a_project_first,
            ));
            exit;
        }
    }

    public function invite_modal()
    {
        if (!($this->sessUserId > 0)) {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => Please_login_to_perform_this_action,
                    'data'   => array(),
                );
            } else {

                echo json_encode(array(
                    'status'   => 0,
                    'msg'      => Please_login_to_perform_this_action,
                    'redirect' => SITE_LOGIN . "?path=" . $this->reqData['origin'],
                ));
                exit;
            }
        }

        $html    = null;
        $results = $this->db->select('tbl_projects', array('id','title'), array('jobStatus' => 'open', 'userId' => $this->sessUserId));

        if ($this->dataOnly) {
            return array('status' => 1,
                'msg'                 => 'Success',
                'data'                => $results->results());
        } else {
            $results = $results->results();
        }

        if (!empty($results)) {
            foreach ($results as $k => $v) {
                $replace = array(
                    "%VALUE%"         => $v['id'],
                    "%SELECTED%"      => null,
                    "%DISPLAY_VALUE%" => ucwords($v['title']),
                );
                $html .= get_view(DIR_TMPL . $this->module . "/select_option-nct.tpl.php", $replace);
            }
        } else {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => You_do_not_have_any_open_projects_currently,
                );
            } else {

                echo json_encode(array(
                    'status' => 0,
                    'msg'    => You_do_not_have_any_open_projects_currently,
                ));
                exit;
            }
        }
        $replace = array(
            '%userId%'          => $this->reqData['suserId'],
            '%project_options%' => $html,
            '%tokenValue%'      => setFormToken(),
        );
        return get_view(DIR_TMPL . $this->module . "/modal_invite-nct.tpl.php", $replace);
    }

    public function get_inviteProviderBtn()
    {
        if ($this->sessUserType == 'p' || $this->user['userType'] == 'c') {
            return null;
        }

        $replace = array('%userId%' => $this->user['userId']);
        return get_view(DIR_TMPL . $this->module . "/invite-provider-btn.tpl.php", $replace);
    }

    public function projects_section()
    {
        $replace = array('%rows%' => $this->project_rows());
        return get_view(DIR_TMPL . $this->module . "/project-nct.tpl.php", $replace);
    }

    public function reviews_section()
    {
        $replace = array('%rows%' => $this->reviews_rows());
        return get_view(DIR_TMPL . $this->module . "/reviews_and_ratings-nct.tpl.php", $replace);
    }

    public function userData($profileLink)
    {
        $doesExist = getTableValue($this->table, 'userId', array(
            'profileLink' => $profileLink,
            'isActive'    => 'y',
            'status'      => 'a',
        ));
        if (isset($doesExist) && $doesExist > 0) {
            $arr = array();
            $arr = $this->db->pdoQuery("SELECT IFNULL(f.id,0) as isFavorite, u.*, CONCAT_WS(' ',u.firstName,u.lastName) AS fullName FROM tbl_users AS u left join tbl_favourites as f on f.favoriteId = u.userId and f.type='1' and f.userId=? WHERE u.profileLink=? ", array($this->sessUserId, $profileLink))->result();
            if ($arr['userType'] == 'c') {
                $arr['ongoing'] = getTableValue('tbl_projects', 'count("id")', array(
                    'userId'    => $arr['userId'],
                    'jobStatus' => 'progress',
                    'isActive'  => 'y',
                ));
                $arr['completed'] = getTableValue('tbl_projects', 'count("id")', array(
                    'userId'    => $arr['userId'],
                    'jobStatus' => 'completed',
                    'isActive'  => 'y',
                ));
            } else {
                $arr['ongoing'] = getTableValue('tbl_projects', 'count("id")', array(
                    'providerId' => $arr['userId'],
                    'jobStatus'  => 'progress',
                    'isActive'   => 'y',
                ));
                $arr['completed'] = getTableValue('tbl_projects', 'count("id")', array(
                    'providerId' => $arr['userId'],
                    'jobStatus'  => 'completed',
                    'isActive'   => 'y',
                ));
            }
            $arr['earned_or_spent_amount'] = $this->total_money($arr['userId'], $arr['userType']);
            if ($this->dataOnly) {
                $exp = str_replace(array(
                    ' ',
                ), array(
                    '_',
                ), ucwords($arr['experience']));
                $arr['experience'] = constant($exp);
            }

            return $arr;

        } else {
            if ($this->dataOnly) {
                $arr['langCode'] = getTableValue('tbl_languages', 'langCode', array('id' => $_SESSION['lId']));
                return array();
            } else {
                $_SESSION["msgType"] = disMessage(array(
                    'type' => 'err',
                    'var'  => toastr_url_not_found,
                ));
                redirectPage(SITE_URL);
            }

        }
    }

}
