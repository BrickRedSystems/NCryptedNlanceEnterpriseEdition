<?php
class Bid
{
    public function __construct($module = "", $id = 0, $reqData = array())
    {
        global $js_variables;
        foreach ($GLOBALS as $key => $values) {
            $this->$key = $values;
        }
        $this->module  = $module;
        $this->id      = $id;
        $this->reqData = $reqData;
        $this->table   = "tbl_bids";

        $this->dataOnly   = (isset($this->reqData['dataOnly']) && $this->reqData['dataOnly'] == true) ? true : false;
        $this->sessUserId = (isset($reqData['userId']) && $reqData['userId'] > 0) ? $reqData['userId'] : $this->sessUserId;

        //session user details
        $this->user = $this->userData();
        $this->bid  = $this->bidData();
        //as provider is this my bid
        $this->isMine = ($this->bid['userId'] == $this->sessUserId) ? 1 : 0;

        if($this->dataOnly){
            $_SESSION['lId'] = (isset($this->reqData['lId']) && $this->reqData['lId'] >0)?$this->reqData['lId']:1;  
            setLang();  
        }

    }

    public function getPageContent()
    {
        extract($this->bid);

        if ($jobStatus == "open" || $jobStatus == "reopened") {
            if ($this->isMine) {
                $accept_or_modify_bid = '<a href="javascript:void(0);" class="btn btn_blue_new btn_light_hover" data-ele="openPlaceBidModal"> '.Edit_Bid.'</a>';
            } else {
                $accept_or_modify_bid = '<a href="javascript:void(0);" class="btn btn_blue_new btn_light_hover" data-operation="acceptBid" data-info="' . $id . '"> '.Accept_Bid.'</a>';
            }
            $textarea = get_view(DIR_TMPL . $this->module . "/textarea-nct.tpl.php");
        } elseif ($isAccepted) {
            $accept_or_modify_bid = '<a href="javascript:void(0);" class="btn btn_blue_new btn_light_hover"> '.Accepted.'</a>';
            $textarea             = null;
        } else {
            $accept_or_modify_bid = null;
            $textarea             = null;
        }

        $replace = array(
            //bread crums
            '%title%'                => filtering($title),
            '%projectLink%'          => SITE_URL . $customer['profileLink'] . '/' . $slug . '/',
            '%customerBreadcrums%'   => (!$this->isMine) ? null : 'hide',
            '%providerBreadcrums%'   => ($this->isMine) ? null : 'hide',

            //about
            '%profileLink%'          => SITE_URL . $customer['profileLink'],
            '%profilePhoto%'         => tim_thumb_image($provider['profilePhoto'], 'profile', 75, 75),
            '%fullName%'             => $provider['fullName'],
            '%location%'             => $provider['location'],
            '%completed%'            => $provider['completed'],
            '%ongoing%'              => $provider['ongoing'],
            '%providerCreatedDate%'  => date(PHP_DATE_FORMAT, strtotime($provider['createdDate'])),

            //last bid whitebox
            '%bidDetail%'            => filtering($bidDetail),
            '%price%'                => $price,
            '%escrow%'               => ($escrow == 'n') ? 'hide' : null,
            '%duration%'             => $duration,
            '%postedOn%'             => date(PHP_DATE_FORMAT, strtotime($createdTime)),
            '%accept_or_modify_bid%' => $accept_or_modify_bid,

            //other past bids
            '%past%'                 => $this->pastBidDetails(),

            //textarea
            '%textarea%'             => $textarea,
        );

        return get_view(DIR_TMPL . $this->module . "/" . $this->module . ".tpl.php", $replace);
    }

    public function editBid()
    {

        ///////////////////
        if (!isset($dataOnly) || !$dataOnly) {
            if (!checkFormToken($this->reqData['token'])) {
                $response['status']   = 0;
                $response['msg']      = Security_token_for_this_action_is_invalid_Please_refresh_and_try_again;
                $response['newToken'] = setFormToken();
                echo json_encode($response);
                exit;
            }
        }
///////////////////
        //check if bidding time is expired
        if (new DateTime() > new DateTime($this->bid['biddingDeadline'])) {
            echo json_encode(array(
                'status' => 0,
                'msg'    => toastr_Due_date_for_bidding_on_this_project_has_passed,
            ));
            exit;
        } else {

            extract($this->reqData);
            //php validations
            $duration    = (isset($duration) && $duration >= 1) ? $duration : 0;
            $price       = (isset($price) && $price >= 1) ? $price : 0;
            $escrow      = issetor($escrow, 'n');
            $bidDetail   = issetor($bidDetail, null);
            $createdTime = date('Y-m-d H:i:s');
            if ($duration == 0 || $price == 0 || trim($bidDetail) == null) {
                echo json_encode(array(
                    'status' => 0,
                    'msg'    => toastr_fill_all_required_details_before_proceed,
                ));
                exit;
            }

            //make entry in tbl_bids
            $lastInsId = $this->db->insert('tbl_bids', array(
                'projectId'   => $this->bid['projectId'],
                'userId'      => $this->sessUserId,
                'duration'    => $duration,
                'price'       => $price,
                'escrow'      => $escrow,
                'bidDetail'   => $bidDetail,
                'createdTime' => $createdTime,
            ))->lastInsertId();

            if ($lastInsId > 0) {

                //update this provider's(ie my) other bids to isFinal=>n
                $this->db->pdoQuery('UPDATE tbl_bids SET isFinal="n" WHERE projectId=? AND userId=? AND id!=?', array(
                    $this->bid['projectId'],
                    $this->sessUserId,
                    $lastInsId,
                ));

                //send mail & notification to customer
                $_SESSION['sendMailTo'] = issetor($this->bid['customer']['userId'], 0);
                $array                  = generateEmailTemplate('bid_edited', array(
                    'greetings'    => ucfirst($this->bid['customer']['firstName']),
                    'providerName' => $this->sessFirstName,
                    'projectName'  => $this->bid['title'],
                    'projectLink'  => SITE_URL . $this->bid['customer']['profileLink'] . '/' . $this->bid['slug'] . '/',
                    'date'         => date(PHP_DATE_FORMAT),
                ));
                //echo '<br/>'.$array['message'];exit;
                sendEmailAddress($this->bid['customer']['email'], $array['subject'], $array['message']);

                //send notification
                insert_user_notification($typeId = 7, $from = $this->sessUserId, $to = $this->bid['customer']['userId'], $referenceId = $this->bid['projectId'], array('%projectName%' => $this->bid['title']));

                echo json_encode(array(
                    'status'   => 1,
                    'msg'      => toastr_Your_new_bid_is_sent_to_customer,
                    'redirect' => SITE_BID . $lastInsId,
                ));
                exit;

            } else {

            }
        }
    }

    public function place_bid_modal()
    {

        //check if project is still open or repoen
        if ($this->bid['jobStatus'] != "open" && $this->bid['jobStatus'] != "reopened") {
            echo json_encode(array(
                'status' => 0,
                'msg'    => toastr_freezed_for_bidding,
            ));
            exit;
        } else {

            extract($this->bid);
            //check if bidding time is expired
            if (new DateTime() > new DateTime($biddingDeadline)) {
                echo json_encode(array(
                    'status' => 0,
                    'msg'    => toastr_Due_date_for_bidding_on_this_project_has_passed,
                ));
                exit;
            } else {
                return get_view(DIR_TMPL . $this->module . "/edit_bid_modal-nct.tpl.php", array(
                    '%duration%'   => $duration,
                    '%price%'      => $price,
                    '%escrowY%'    => ($escrow == "y") ? 'checked="checked"' : null,
                    '%escrowN%'    => ($escrow == "n") ? 'checked="checked"' : null,
                    '%bidDetail%'  => filtering($bidDetail),
                    '%tokenValue%' => setFormToken(),
                ));
            }

        }

    }

    public function sendMessage()
    {
        extract($this->reqData);

        $desc = (isset($description) && trim($description) != null) ? trim($description) : null;
        if ($desc != null) {
            $to = ($this->sessUserId == $this->bid['customer']['userId']) ? $this->bid['provider']['userId'] : $this->bid['customer']['userId'];
            $insArr = array(
                'senderId'    => $this->sessUserId,
                'receiverId'  => $to,
                'type'        => 'bid',
                'subject'     => $this->bid['provider']['fullName'] . toastr_customer_has_requested_moderation_in_your_bid,
                'description' => $desc,
                'createdDate' => date('Y-m-d H:i:s'),
                'projectId'   => $this->bid['projectId'],
                'readStatus'  => check_noti_enable($to, 1) ? 'n' : 'y'
            );
            $lastId = $this->db->insert("tbl_messages", $insArr)->lastInsertId();
            if ($lastId > 0) {

                if ($this->dataOnly) {
                    return array(
                        'status' => 1,
                        'msg'    => toastr_bid_modify_request_sent_successfully,
                    );
                } else {
                    echo json_encode(array(
                        'status' => 1,
                        'msg'    => toastr_bid_modify_request_sent_successfully,
                        'html'   => $this->pastBidDetails(),
                    ));
                    exit;
                }
            }
        } else {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => toastr_Please_describe_your_need_for_requesting_changes_in_the_bid,
                );
            } else {
                echo json_encode(array(
                    'status' => 0,
                    'msg'    => toastr_Please_describe_your_need_for_requesting_changes_in_the_bid,
                ));
                exit;
            }
        }
    }

    public function pastBidDetails()
    {
        //AND m.`senderId` = p.`userId` AND m.`receiverId` = b.`userId`
        $pastBids = $this->bid['pastBids'];
        $pastMsgs = $this->db->pdoQuery("SELECT m.*, m.createdDate AS createdTime FROM tbl_messages AS m LEFT JOIN tbl_projects AS p ON m.`projectId` = p.`id` LEFT JOIN tbl_bids AS b ON m.`projectId` = b.`projectId` AND b.`isNulled`='n' WHERE m.`type` = 'bid' AND m.`projectId` = ?  AND b.`id` = ?  AND (m.senderId = ? OR m.`receiverId`=?)", array(
            $this->bid['projectId'],
            $this->reqData['bidId'],
            $this->sessUserId,
            $this->sessUserId,
        ))->results();

        $merged = array_merge($pastBids, $pastMsgs);

        if ($this->dataOnly) {
            return $merged;
            exit;
        }
        //dump($merged);
        $this->usortByArrayKey($merged, 'createdTime', $asc = SORT_DESC);

        $html = null;
        if (!empty($merged)) {
            foreach ($merged as $k => $v) {
                extract($v);
                if (isset($v['type'])) {
                    //use messgae template

                    $replace = array(
                        '%class%'       => ($v['senderId'] == $this->bid['userId']) ? 'bid-detail-cell-margin' : 'bid-detail-cell',
                        '%msg%'         => filtering($v['description']),
                        '%msgSentDate%' => date(PHP_DATE_FORMAT, strtotime($v['createdTime'])),
                    );
                    $html .= get_view(DIR_TMPL . $this->module . "/past_msg_row-nct.tpl.php", $replace);

                } else {
                    //use bid template
                    $replace = array(
                        '%bidDetail%'   => filtering($v['bidDetail']),
                        '%price%'       => $v['price'],
                        '%escrow%'      => ($v['escrow'] == 'y') ? null : 'hide',
                        '%duration%'    => $v['duration'],
                        '%createdTime%' => date(PHP_DATE_FORMAT, strtotime($v['createdTime'])),
                    );
                    $html .= get_view(DIR_TMPL . $this->module . "/past_bid_row-nct.tpl.php", $replace);

                }
            } //end foreach
            $html = '<ul class="bid-detail-row" data-ele="past">' . $html . '</ul>';
        } else {
            $html .= '<ul class="bid-detail-row" data-ele="past"></ul>' . get_view(DIR_TMPL . $this->module . "/no_past_data-nct.tpl.php");

        }
        return $html;
        /*sort by createdTime
    $merged = array_map(array($this, 'array_to_obj'), $merged);
    $this->sortObjectsByProperties($merged, array(
    array('property'=>'createdTime', 'order'=>'DESC', 'comparer'=>'date')
    ));*/

    }

    public function userData()
    {
        $doesExist = getTableValue('tbl_users', 'userId', array(
            'userId'   => $this->sessUserId,
            'isActive' => 'y',
        ));
        if (isset($doesExist) && $doesExist > 0) {
            $arr = array();
            $arr = $this->db->pdoQuery("SELECT u.*, CONCAT_WS(' ',u.firstName,u.lastName) AS fullName FROM tbl_users AS u WHERE u.userId=? ", array($this->sessUserId))->result();
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
            return $arr;
        } else {
            $_SESSION["msgType"] = disMessage(array(
                'type' => 'err',
                'var'  => toastr_url_not_found,
            ));
            redirectPage(SITE_URL);
        }
    }

    public function bidData()
    {
        $doesExist = $this->db->pdoQuery("SELECT b.id FROM tbl_bids AS b LEFT JOIN tbl_projects AS p ON b.`projectId` = p.`id` LEFT JOIN tbl_users AS u ON b.`userId` = u.`userId` WHERE b.isActive = 'y' AND b.isNulled = 'n' AND p.`isActive` = 'y' AND u.`isActive` = 'y' AND b.id = ?  GROUP BY b.userId ORDER BY b.id LIMIT 1", array($this->reqData['bidId']))->result();

        if (isset($doesExist['id']) && $doesExist['id'] > 0) {
            $result             = $this->db->pdoQuery("SELECT b.*,p.userId as customerId, p.title, p.slug, p.biddingDeadline, p.jobStatus FROM tbl_bids AS b LEFT JOIN tbl_projects AS p ON b.`projectId` = p.`id` LEFT JOIN tbl_users AS u ON b.`userId` = u.`userId`  WHERE b.isActive = 'y' AND p.`isActive` = 'y' AND u.`isActive` = 'y' AND b.id = ? GROUP BY b.userId ORDER BY b.id LIMIT 1", array($this->reqData['bidId']))->result();
            $result['customer'] = $this->db->pdoQuery("SELECT u.*, CONCAT_WS(' ',u.firstName,u.lastName) AS fullName,CONCAT_WS( ', ', tc.`cityName`, s.`stateName`, c.`countryName` ) AS location FROM tbl_users AS u LEFT JOIN tbl_country AS c ON u.`countryCode` = c.`CountryId` LEFT JOIN tbl_state AS s ON u.`state` = s.`StateID` LEFT JOIN `tbl_city` AS tc ON u.`city` = tc.`CityId` WHERE u.userId=? ", array($result['customerId']))->result();
            $result['provider'] = $this->db->pdoQuery("SELECT u.*, CONCAT_WS(' ',u.firstName,u.lastName) AS fullName,CONCAT_WS( ', ', tc.`cityName`, s.`stateName`, c.`countryName` ) AS location FROM tbl_users AS u LEFT JOIN tbl_country AS c ON u.`countryCode` = c.`CountryId` LEFT JOIN tbl_state AS s ON u.`state` = s.`StateID` LEFT JOIN `tbl_city` AS tc ON u.`city` = tc.`CityId` WHERE u.userId=? ", array($result['userId']))->result();

            //check if any user other then customer or provider is accessing this page
            if ($this->sessUserId != $result['customer']['userId'] && $this->sessUserId != $result['provider']['userId']) {
                $_SESSION["msgType"] = disMessage(array(
                    'type' => 'err',
                    'var'  => toastr_url_not_found,
                ));
                redirectPage(SITE_URL);
            }

            $result['provider']['ongoing'] = getTableValue('tbl_projects', 'count("id")', array(
                'providerId' => $result['userId'],
                'jobStatus'  => 'progress',
                'isActive'   => 'y',
            ));
            $result['provider']['completed'] = getTableValue('tbl_projects', 'count("id")', array(
                'providerId' => $result['userId'],
                'jobStatus'  => 'completed',
                'isActive'   => 'y',
            ));
            $result['pastBids'] = $this->db->pdoQuery("SELECT b.*,p.userId as customerId FROM tbl_bids AS b LEFT JOIN tbl_projects AS p ON b.`projectId` = p.`id` LEFT JOIN tbl_users AS u ON b.`userId` = u.`userId`  WHERE b.isActive = 'y' AND b.isNulled = 'n' AND p.`isActive` = 'y' AND u.`isActive` = 'y' AND b.id != ? AND b.projectId=? AND b.userId=? ORDER BY b.id DESC ", array(
                $this->reqData['bidId'],
                $result['projectId'],
                $result['userId'],
            ))->results();
            return $result;
        } else {
            $_SESSION["msgType"] = disMessage(array(
                'type' => 'err',
                'var'  => toastr_url_not_found,
            ));
            redirectPage(SITE_URL);
        }
    }

    public function usortByArrayKey(&$array, $key, $asc = SORT_ASC)
    {
        $sort_flags = array(
            SORT_ASC,
            SORT_DESC,
        );
        if (!in_array($asc, $sort_flags)) {
            throw new InvalidArgumentException('sort flag only accepts SORT_ASC or SORT_DESC');
        }

        $cmp = function (array $a, array $b) use ($key, $asc, $sort_flags) {
            if (!is_array($key)) {
//just one key and sort direction
                if (!isset($a[$key]) || !isset($b[$key])) {
                    throw new Exception('attempting to sort on non-existent keys');
                }
                if ($a[$key] == $b[$key]) {
                    return 0;
                }

                return ($asc == SORT_ASC xor $a[$key] < $b[$key]) ? 1 : -1;
            } else {
//using multiple keys for sort and sub-sort
                foreach ($key as $sub_key => $sub_asc) {
                    //array can come as 'sort_key'=>SORT_ASC|SORT_DESC or just 'sort_key', so need to detect which
                    if (!in_array($sub_asc, $sort_flags)) {
                        $sub_key = $sub_asc;
                        $sub_asc = $asc;
                    }
                    //just like above, except 'continue' in place of return 0
                    if (!isset($a[$sub_key]) || !isset($b[$sub_key])) {
                        throw new Exception('attempting to sort on non-existent keys');
                    }
                    if ($a[$sub_key] == $b[$sub_key]) {
                        continue;
                    }

                    return ($sub_asc == SORT_ASC xor $a[$sub_key] < $b[$sub_key]) ? 1 : -1;
                }
                return 0;
            }
        };
        usort($array, $cmp);
    }

}
