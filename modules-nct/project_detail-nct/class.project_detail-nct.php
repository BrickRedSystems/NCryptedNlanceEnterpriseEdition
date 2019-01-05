<?php
class ProjectDetail
{
    public function __construct($module = "", $id = 0, $reqData = array())
    {
        global $js_variables, $sessFirstName;
        foreach ($GLOBALS as $key => $values) {
            $this->$key = $values;
        }
        $this->module        = $module;
        $this->id            = $id;
        $this->reqData       = $reqData;
        $this->sessFirstName = $sessFirstName;
        $this->table         = "tbl_projects";

        //for web service
        $this->dataOnly   = (isset($this->reqData['dataOnly']) && $this->reqData['dataOnly'] == true) ? true : false;
        $this->sessUserId = (isset($reqData['userId']) && $reqData['userId'] > 0) ? $reqData['userId'] : $this->sessUserId;

        $this->user = $this->userData($reqData['profileLink']);
        $this->proj = $this->projectData($reqData['slug']);

        //my project
        $this->isMe        = ((int) $this->proj['userId'] == $this->sessUserId) ? 1 : 0;
        $this->isDifferent = ($this->user['userType'] != $this->sessUserType) ? 1 : 0;
        //$js_variables = ($this->user['userType'] == "p") ? "var method ='reviews_rows'" : "var method ='project_rows'";
        if ($this->dataOnly) {
            $_SESSION['lId'] = (isset($this->reqData['lId']) && $this->reqData['lId'] > 0) ? $this->reqData['lId'] : 1;
            setLang();
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

        if (!$this->isMe) {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => toastr_This_project_does_not_belong_to_you,
                    'data'   => array(),
                );
            } else {
                echo json_encode(array(
                    'status' => 0,
                    'msg'    => toastr_This_project_does_not_belong_to_you,
                ));
                exit;
            }
        }

        if ($this->proj['jobStatus'] != "open" && $this->proj['jobStatus'] != "reopened") {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => toastr_To_invite_a_provider_project_status_must_be_either_Open_or_Reopened,
                    'data'   => array());
            } else {
                echo json_encode(array(
                    'status' => 0,
                    'msg'    => toastr_To_invite_a_provider_project_status_must_be_either_Open_or_Reopened));
                exit;
            }
        }

        $html    = null;
        $results = $this->db->select('tbl_users', array('userId', 'firstName', 'lastName'), array('userType' => 'p', 'isActive' => 'y'));

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
                    "%VALUE%"         => $v['userId'],
                    "%SELECTED%"      => null,
                    "%DISPLAY_VALUE%" => ucwords($v['firstName'] . ' ' . $v['lastName']),
                );
                $html .= get_view(DIR_TMPL . $this->module . "/select_option-nct.tpl.php", $replace);
            }

        } else {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => There_are_no_providers_currently,
                );
            } else {
                echo json_encode(array(
                    'status' => 0,
                    'msg'    => There_are_no_providers_currently,
                ));
                exit;
            }
        }
        $replace = array(
            '%provider_options%' => $html,
        );
        return get_view(DIR_TMPL . $this->module . "/modal_invite-nct.tpl.php", $replace);
    }

    public function inviteProvider()
    {
        $providerIds = $this->reqData['providerIds'];

        if ($this->dataOnly) {
            $providerIds = explode(',', $this->reqData['providerIds']);
        }

        if (!empty($providerIds)) {
            foreach ($providerIds as $key => $value) {
                //check if exist
                $check = getTableValue('tbl_project_invitation', 'id', array(
                    'projectId'  => $this->proj['id'],
                    'providerId' => $value,
                ));
                if ($check > 0) {
                    $this->db->delete('tbl_project_invitation', array('id' => $check));
                } else {
                    $this->db->insert('tbl_project_invitation', array(
                        'projectId'   => $this->proj['id'],
                        'providerId'  => $value,
                        'createdDate' => date('Y-m-d H:i:s'),
                        'accepted'    => 'n',
                    ));

                    $user = $this->db->select('tbl_users', array('profileLink', 'email', 'firstName'), array(
                        'userId' => $value,
                    ))->result();

                    $_SESSION['sendMailTo'] = issetor($value, 0);
                    $array                  = generateEmailTemplate('inviteProvider', array(
                        'greetings'   => ucfirst($user['firstName']),
                        'fromName'    => $this->sessFirstName,
                        'projectName' => $this->proj['title'],
                        'projectLink' => SITE_URL . $this->user['profileLink'] . '/' . $this->proj['slug'],
                        'date'        => date(PHP_DATE_FORMAT),
                    ));

                    sendEmailAddress($user['email'], $array['subject'], $array['message']);

                    //send notification
                    insert_user_notification($typeId = 14, $from = $this->sessUserId, $to = $value, $referenceId = $this->proj['id'], array('%userName%' => getTableValue('tbl_users', 'userName', array('userId' => $this->sessUserId,
                    )), '%projectName%' => $this->proj['title']));
                }
            }
            if ($this->dataOnly) {
                return array(
                    'status' => 1,
                    'msg'    => Your_invitation_has_been_sent,
                );
            } else {
                echo json_encode(array(
                    'status' => 1,
                    'msg'    => Your_invitation_has_been_sent,
                ));
                exit;
            }
        } else {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => Please_select_a_project_first,
                );
            } else {
                echo json_encode(array(
                    'status' => 0,
                    'msg'    => Please_select_a_project_first,
                ));
                exit;
            }
        }

    }

    public function requestNewMil()
    {
        extract($this->reqData);
        // send mail & notification
        $_SESSION['sendMailTo'] = issetor($this->proj['provider']['userId'], 0);
        $array                  = generateEmailTemplate('requestNewMil', array(
            'greetings'   => ucfirst($this->proj['provider']['firstName']),
            'custName'    => $this->sessFirstName,
            'projectName' => $this->proj['title'],
            'projectLink' => SITE_URL . $this->reqData['profileLink'] . '/' . $this->proj['slug'],
            'date'        => date(PHP_DATE_FORMAT),
        ));

        //echo '<br/>'.$array['message'];exit;
        sendEmailAddress($this->proj['provider']['email'], $array['subject'], $array['message']);

        //send notification
        insert_user_notification($typeId = 16, $from = $this->sessUserId, $to = $this->proj['provider']['userId'], $referenceId = $this->proj['id'], array('%projectName%' => $this->proj['title']));

        if ($this->dataOnly) {
            return array(
                'status' => 1,
                'msg'    => toastr_Your_request_has_been_sent_to_provider,
            );
        } else {
            echo json_encode(array(
                'status'   => 1,
                'msg'      => toastr_Your_request_has_been_sent_to_provider,
                'redirect' => $origin,
            ));
            exit;
        }
    }

    public function panel_reviews()
    {
        $result = $this->db->select('tbl_feedbacks', array(
            'averageRating',
            'review',
            'addedDate',
        ), array(
            'jobId' => $this->proj['id'],
        ));

        if ($this->dataOnly) {
            return $result->result();
        } else {
            $result = $result->result();
        }

        if (!empty($result)) {
            $replace = array(
                '%averageRating%' => number_format($result['averageRating'], 1),
                '%review%'        => $result['review'],
                '%addedDate%'     => date(PHP_DATE_FORMAT, strtotime($result['addedDate'])),
                '%tokenValue%'    => setFormToken(),
            );
            return get_view(DIR_TMPL . $this->module . "/reviews_and_ratings/panel_reviews-nct.tpl.php", $replace);
        } else {
            return get_view(DIR_TMPL . $this->module . "/reviews_and_ratings/no_reviews-nct.tpl.php");
        }

    }

    public function rateThisProvider()
    {
        extract($this->reqData);

        if (!isset($dataOnly) || !$dataOnly) {
            if (!checkFormToken($token)) {
                $response['status']   = 0;
                $response['msg']      = Security_token_for_this_action_is_invalid_Please_refresh_and_try_again;
                $response['newToken'] = setFormToken();
                echo json_encode($response);
                exit;
            }
        }

        //check if null
        $review          = issetor($review, null);
        $workOnTime      = issetor($workOnTime, 0);
        $communication   = issetor($communication, 0);
        $reliability     = issetor($reliability, 0);
        $professionalism = issetor($professionalism, 0);
        $wouldWorkAgain  = issetor($wouldWorkAgain, 0);

        //calculate average
        $averageRating = ($workOnTime + $communication + $reliability + $professionalism + $wouldWorkAgain) / 5;
        $averageRating = $averageRating;

        //check if already reviewed
        $check = getTableValue('tbl_feedbacks', 'id', array(
            'jobId'    => $this->proj['id'],
            'userFrom' => $this->sessUserId,
            'userTo'   => $this->proj['provider']['userId'],
        ));

        if ($check > 0) {
            //update entry
            $lastInsertId = $this->db->update('tbl_feedbacks', array(
                'averageRating'   => (string) $averageRating,
                'review'          => $review,
                'workOnTime'      => $workOnTime,
                'communication'   => $communication,
                'reliability'     => $reliability,
                'professionalism' => $professionalism,
                'wouldWorkAgain'  => $wouldWorkAgain,
                'ip'              => get_ip_address(),
                'addedDate'       => date('Y-m-d H:i:s'),
                'updatedTime'     => date('Y-m-d H:i:s'),
            ), array(
                'jobId'    => $this->proj['id'],
                'userFrom' => (string) $this->sessUserId,
                'userTo'   => $this->proj['provider']['userId'],
            ))->affectedRows();
        } else {
            //mk entry
            $lastInsertId = $this->db->insert('tbl_feedbacks', array(
                'averageRating'   => (string) $averageRating,
                'review'          => $review,
                'workOnTime'      => $workOnTime,
                'communication'   => $communication,
                'reliability'     => $reliability,
                'professionalism' => $professionalism,
                'wouldWorkAgain'  => $wouldWorkAgain,
                'jobId'           => $this->proj['id'],
                'userFrom'        => (string) $this->sessUserId,
                'userTo'          => $this->proj['provider']['userId'],
                'ip'              => get_ip_address(),
                'addedDate'       => date('Y-m-d H:i:s'),
                'updatedTime'     => date('Y-m-d H:i:s'),
            ))->lastInsertId();

        }

        if ($lastInsertId > 0) {
            $_SESSION['sendMailTo'] = issetor($this->proj['provider']['userId'], 0);
            $array                  = generateEmailTemplate('reviews', array(
                'greetings'       => ucfirst($this->proj['provider']['firstName']),
                'fromName'        => $this->sessFirstName,
                'projectName'     => $this->proj['title'],
                'projectLink'     => SITE_URL . $this->reqData['profileLink'] . '/' . $this->proj['slug'],
                'averageRating'   => renderStarRating($averageRating, 5, true),
                'review'          => $review,
                'workOnTime'      => renderStarRating($workOnTime, 5, true),
                'communication'   => renderStarRating($communication, 5, true),
                'reliability'     => renderStarRating($reliability, 5, true),
                'professionalism' => renderStarRating($professionalism, 5, true),
                'wouldWorkAgain'  => renderStarRating($wouldWorkAgain, 5, true),
                'date'            => date(PHP_DATE_FORMAT),
            ));
            //echo '<br/>'.$array['message'];exit;
            sendEmailAddress($this->proj['provider']['email'], $array['subject'], $array['message']);

            //send notification
            insert_user_notification($typeId = 3, $from = $this->sessUserId, $to = $this->proj['provider']['userId'], $referenceId = $this->proj['id']);

            if ($this->dataOnly) {
                return array(
                    'status' => 1,
                    'msg'    => toastr_Your_reviews_are_received,
                );
            } else {
                $this->toastrExit(1, toastr_Your_reviews_are_received);
            }
        } else {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => Some_problem_occurred_while_posting_this_review,
                );
            } else {
                $this->toastrExit(0, Some_problem_occurred_while_posting_this_review);
            }
        }
    }

    public function review_modal()
    {
        //check if already provided review
        $result = $this->db->select('tbl_feedbacks', '*', array('jobId' => $this->proj['id']))->result();

        if ($this->dataOnly) {
            return $result;
        }

        if (!empty($result)) {
            extract($result);
        }

        $replace = array(
            '%workOnTime%'      => issetor($workOnTime, null),
            '%communication%'   => issetor($communication, null),
            '%reliability%'     => issetor($reliability, null),
            '%professionalism%' => issetor($professionalism, null),
            '%wouldWorkAgain%'  => issetor($wouldWorkAgain, null),
            '%review%'          => isset($review) ? filtering(nl2br($review)) : null,
        );
        return get_view(DIR_TMPL . $this->module . "/reviews_and_ratings/reviews_and_ratings_modal.tpl.php", $replace);
    }

    public function downloadFile()
    {
        $filename  = issetor($this->reqData['filename'], '');
        $dir       = issetor($this->reqData['type'], '');
        $file_real = DIR_UPD . $dir . '/' . $filename;
        $info      = new SplFileInfo($file_real);
        $extension = $info->getExtension();
        $mimeType  = mime_content_type($file_real);

        if (!file_exists($file_real)) {
            header("HTTP/1.1 404 Not Found");
            return;
        }

        $size = filesize($file_real);
        $time = date('r', filemtime($file_real));
        $fm   = fopen($file_real, 'rb');

        if (!$fm) {
            header("HTTP/1.1 505 Internal server error");
            return;
        }

        $begin = 0;
        $end   = $size - 1;
        $cnlen = (($end - $begin) + 1);

        header("Content-type: octet/stream");
        header("Content-Type: $mimeType");
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Accept-Ranges: bytes');
        //give name to downloaded content
        header("Content-disposition: attachment; filename=$filename;");
        header('Content-Length:' . $cnlen);
        header("Content-Transfer-Encoding: binary");
        header("Last-Modified: $time");

        $cur = $begin;
        fseek($fm, $begin, 0);

        while (!feof($fm) && $cur <= $end && (connection_status() == 0)) {
            print fread($fm, min(1024 * 16, ($end - $cur) + 1));
            $cur += 1024 * 16;
        }
        exit;
    }

    public function uploadFile()
    {

        if (!isset($_SESSION['uploadedFiles'])) {
            $_SESSION['uploadedFiles'] = array();
        }

        if ($this->dataOnly) {
            require_once '../themes-nct/javascript-nct/plugins-nct/blueimp/server/php/UploadHandler.php';
        } else {
            require_once '../../themes-nct/javascript-nct/plugins-nct/blueimp/server/php/UploadHandler.php';
        }

        if ($this->dataOnly) {
            $options = array(
                'script_url'     => SITE_URL . $this->reqData['profileLink'] . "/" . $this->reqData['slug'] . "?action=method&method=uploadFile", //path of file where object of UploadHandler.php is made
                'upload_dir'     => DIR_UPD . "attachments/",
                'upload_url'     => SITE_UPD . "attachments/",
                'type'           => 'message_attachments',
                'print_response' => false,
                'max_file_size'  => MAX_FILE_SIZE);
        } else {
            $options = array(
                'script_url'     => SITE_URL . $this->reqData['profileLink'] . "/" . $this->reqData['slug'] . "?action=method&method=uploadFile", //path of file where object of UploadHandler.php is made
                'upload_dir'     => DIR_UPD . "attachments/",
                'upload_url'     => SITE_UPD . "attachments/",
                'type'           => 'message_attachments',
                'print_response' => true,
                'max_file_size'  => MAX_FILE_SIZE);
        }

        $upload_handler = new UploadHandler($options);

        //START:: to get names of uploaded files
        $arr = $upload_handler->get_response();
        $arr = json_decode(json_encode($arr), true);
        if (isset($arr['files'][0]['name'])) {
            array_push($_SESSION['uploadedFiles'], $arr['files'][0]['name']);
        } else {
            $_SESSION['uploadedFiles'] = array_filter($_SESSION['uploadedFiles'], function ($v) {
                return $v != $this->reqData['file'];
            });
        }

        //START:: for message attachments
        $this->fb->info($_SESSION, 'session::  ');
        $attachmentId = md5(time());

        $originalAttachmentId = $attachmentId;

        //check if attachment id is already there
        $check = getTableValue('tbl_messages', 'id', array('attachmentId' => $attachmentId));
        if ($check == null) {
            $to     = ($this->sessUserId == $this->proj['userId']) ? $this->proj['provider']['userId'] : $this->proj['userId'];
            $insArr = array(
                'senderId'     => $this->sessUserId,
                'receiverId'   => $to,
                'type'         => 'workroom',
                'subject'      => $this->proj['title'] . " - " . Workroom_conversation,
                'description'  => A_new_attachment_sent,
                'createdDate'  => date('Y-m-d H:i:s'),
                'attachmentId' => $attachmentId,
                'projectId'    => $this->proj['id'],
                'readStatus'   => check_noti_enable($to, 1) ? 'n' : 'y',
            );
            if ($this->dataOnly) {
                foreach ($arr['files'] as $k) {
                    $to     = ($this->sessUserId == $this->proj['userId']) ? $this->proj['provider']['userId'] : $this->proj['userId'];
                    $insArr = array(
                        'senderId'     => $this->sessUserId,
                        'receiverId'   => $to,
                        'type'         => 'workroom',
                        'subject'      => $this->proj['title'] . " - " . Workroom_conversation,
                        'description'  => A_new_attachment_sent,
                        'createdDate'  => date('Y-m-d H:i:s'),
                        'attachmentId' => $attachmentId,
                        'projectId'    => $this->proj['id'],
                        'readStatus'   => check_noti_enable($to, 1) ? 'n' : 'y',
                    );
                    $attachmentId = md5($attachmentId);
                    $lastId       = $this->db->insert("tbl_messages", $insArr)->lastInsertId();
                }
            } else {
                $lastId = $this->db->insert("tbl_messages", $insArr)->lastInsertId();
            }
        }

        if ($this->dataOnly) {
            foreach ($arr['files'] as $k) {
                $this->db->insert('tbl_message_attachments', array(
                    'attachmentId' => $originalAttachmentId,
                    'fileName'     => $k['name'],
                    'createdDate'  => date('Y-m-d H:i:s'),
                ));
                $originalAttachmentId = md5($originalAttachmentId);
            }
        } else {
            foreach ($_SESSION['uploadedFiles'] as $k => $v) {

                $this->db->insert('tbl_message_attachments', array(
                    'attachmentId' => $attachmentId,
                    'fileName'     => $v,
                    'createdDate'  => date('Y-m-d H:i:s'),
                ));
            }
        }

        if ($this->dataOnly) {
            return issetor($arr['files'], array());
        }
        unset($_SESSION['uploadedFiles']);

        //END:: for message attachments

        exit;
        //END:: to get names of uploaded files
    }

    public function sendWorkroomMessage()
    {
        extract($this->reqData);

        if (!isset($dataOnly) || !$dataOnly) {
            if (!checkFormToken($token)) {
                $response['status']   = 0;
                $response['msg']      = Security_token_for_this_action_is_invalid_Please_refresh_and_try_again;
                $response['newToken'] = setFormToken();
                echo json_encode($response);
                exit;
            }
        }

        $desc = (isset($description) && trim($description) != null) ? trim($description) : null;        

        if ($desc != null) {
            $to     = ($this->sessUserId == $this->proj['userId']) ? $this->proj['provider']['userId'] : $this->proj['userId'];
            $insArr = array(
                'senderId'    => $this->sessUserId,
                'receiverId'  => $to,
                'type'        => 'workroom',
                'subject'     => $this->proj['title'] . " - " . Workroom_conversation,
                'description' => $desc,
                'createdDate' => date('Y-m-d H:i:s'),
                'projectId'   => $this->proj['id'],
                'readStatus'  => check_noti_enable($to, 1) ? 'n' : 'y',

            );
            
            $lastId = $this->db->insert("tbl_messages", $insArr)->lastInsertId();
            if ($lastId > 0) {

                if ($this->dataOnly) {
                    return array(
                        'status' => 1,
                        'msg'    => 'Success',
                        'data'   => $this->workroomMessages(),
                    );
                } else {
                    echo json_encode(array(
                        'status' => 1,
                        'msg'    => 'undefined',
                        'html'   => $this->workroomMessages(),
                    ));
                }

                $notifiedName = getTableValue("tbl_users", "userName", array("userId" => $this->sessUserId));

                $receiverNotificationID = ($this->sessUserId == $this->proj['userId']) ? $this->proj['provider']['userId'] : $this->proj['userId'];
                $receiverDeviceId       = getTableValue("tbl_users", "deviceId", array("userId" => $receiverNotificationID));

                pushToAndroid(array(
                    getTableValue("tbl_users", "deviceId", array("userId" => ($this->sessUserId == $this->proj['userId']) ? $this->proj['provider']['userId'] : $this->proj['userId'])),
                ), array(
                    'title' => SITE_NM,
                    'body'  => $notifiedName . has_sent_you_a_message,
                ));
                exit;
            }
        } else {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => Please_write_message_before_sending,
                );
            } else {
                echo json_encode(array(
                    'status' => 0,
                    'msg'    => Please_write_message_before_sending,
                ));
                exit;
            }
        }
    }

    public function updateMsgs()
    {
        $this->db->update('tbl_messages', array('readStatus' => 'y'), array(
            'receiverId'   => $this->sessUserId,
            'type'         => 'workroom',
            'projectId'    => $this->proj['id'],
            'attachmentId' => '',
        ));
        $this->toastrExit(1);
    }

    public function updateAttachments()
    {
        $this->db->update('tbl_messages', array('readStatus' => 'y'), array(
            'receiverId' => $this->sessUserId,
            'type'       => 'workroom',
            'projectId'  => $this->proj['id'],
        ))->customWhere(array('attachmentId <>' => ''));
        $this->toastrExit(1);
    }

    public function workroomMessages($count = false)
    {

        if ($count) {
            $Msgs = $this->db->pdoQuery("SELECT m.*, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName FROM tbl_messages AS m LEFT JOIN tbl_users AS u ON m.senderId = u.userId WHERE m.readStatus = 'n' AND m.`type` = 'workroom' AND m.`projectId` = ?  AND m.`receiverId`=? ORDER BY m.id ASC ", array(
                $this->proj['id'],
                $this->sessUserId,
            ))->results();
            return count($Msgs);
        } else {
            $Msgs = $this->db->pdoQuery("SELECT m.*, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName FROM tbl_messages AS m LEFT JOIN tbl_users AS u ON m.senderId = u.userId WHERE m.`type` = 'workroom' AND m.`projectId` = ?  AND ((m.senderId = ? AND m.`receiverId`=?) OR (m.senderId = ? AND m.`receiverId`=?)) ORDER BY m.id ASC ", array(
                $this->proj['id'],
                $this->proj['userId'],
                $this->proj['provider']['userId'],
                $this->proj['provider']['userId'],
                $this->proj['userId'],
            ));

            if ($this->dataOnly) {
                return $Msgs->results();
            } else {
                $Msgs = $Msgs->results();
            }

            $html = null;
            if (!empty($Msgs)) {
                foreach ($Msgs as $k => $v) {
                    extract($v);
                    $replace = array(
                        '%firstName%' => ucwords($fullName),
                        '%class%'     => ($this->sessUserId == $senderId) ? 'my-chat' : 'other-chat',
                        '%desc%'      => nl2br(filtering($description)),
                        '%time%'      => date(PHP_DATETIME_FORMAT, strtotime($createdDate)),
                    );
                    $html .= get_view(DIR_TMPL . $this->module . "/workroom/workroom_row-nct.tpl.php", $replace);
                }
            } else {
                $btn = '
                <p>
                    ' . Please_send_a_message_to_start_conversation . '
                </p>';
                $replace = array('%msg%' => $btn);
                $html .= get_view(DIR_TMPL . $this->module . "/workroom/no_workroom_row-nct.tpl.php", $replace);
            }
            return $html;
        }
    }

    public function workroomAttachments($count = false)
    {

        if ($count) {
            $msgs = $this->db->pdoQuery("SELECT m.*, CONCAT_WS(' ', u.firstname, u.lastname) AS fullname, u.profileLink, u.profilePhoto FROM tbl_messages AS m LEFT JOIN tbl_users AS u ON m.senderid = u.userid WHERE m.readStatus = 'n' AND m.`type` = 'workroom' AND m.attachmentid > 0 AND m.`projectid` = ? AND  m.`receiverid` = ?  ORDER BY m.id ASC ", array(
                $this->proj['id'],
                $this->sessUserId,
            ))->results();
            return count($msgs);
        } else {
            $msgs = $this->db->pdoQuery("SELECT m.*, CONCAT_WS(' ', u.firstname, u.lastname) AS fullname, u.profileLink, u.profilePhoto  FROM tbl_messages AS m LEFT JOIN tbl_users AS u ON m.senderid = u.userid WHERE m.`type` = 'workroom' AND m.attachmentid != '' AND m.`projectid` = ? AND ( ( m.senderid = ? AND m.`receiverid` = ? ) OR ( m.senderid = ? AND m.`receiverid` = ? ) ) ORDER BY m.id ASC ", array(

                $this->proj['id'],
                $this->proj['userId'],
                $this->proj['provider']['userId'],
                $this->proj['provider']['userId'],
                $this->proj['userId'],
            ));
            $html = null;

            if ($this->dataOnly) {
                return $msgs->results();
            } else {
                $msgs = $msgs->results();
            }

            if (!empty($msgs)) {
                foreach ($msgs as $k => $v) {

                    $attachments = $this->db->pdoQuery("SELECT ma.* FROM tbl_message_attachments AS ma LEFT JOIN tbl_messages AS m ON ma.`attachmentId` = m.`attachmentId` WHERE m.`type` = 'workroom' AND m.attachmentid = ? AND m.`projectid` = ? AND ( ( m.senderid = ? AND m.`receiverid` = ? ) OR ( m.senderid = ? AND m.`receiverid` = ? ) ) ORDER BY m.id ASC ", array(
                        $v['attachmentId'],
                        $this->proj['id'],
                        $this->proj['userId'],
                        $this->proj['provider']['userId'],
                        $this->proj['provider']['userId'],
                        $this->proj['userId'],
                    ))->results();
                    $multipleAttachments = null;
                    foreach ($attachments as $key => $val) {
                        extract($val);
                        if (file_exists(DIR_UPD . 'attachments/' . $fileName)) {
                            $multipleAttachments .= get_view(DIR_TMPL . $this->module . "/workroom/attachment_sub_row-nct.tpl.php", array(
                                '%time%'     => date(PHP_DATETIME_FORMAT, strtotime($createdDate)),
                                '%fileName%' => $fileName,
                                '%path%'     => SITE_URL . $this->user['profileLink'] . '/' . $this->proj['slug'] . '/?action=method&method=downloadFile&type=attachments&filename=' . $fileName,
                            ));
                        }
                    }

                    $replace = array(
                        '%multipleAttachments%' => $multipleAttachments,
                        '%firstName%'           => ucwords($v['fullname']),
                        '%profileLink%'         => SITE_URL . $v['profileLink'],
                        '%profilePhoto%'        => tim_thumb_image($v['profilePhoto'], 'profile', 134, 134),
                    );
                    $html .= get_view(DIR_TMPL . $this->module . "/workroom/attachment_row-nct.tpl.php", $replace);
                }
            } else {
                $btn = '
                <p>
                    ' . There_are_no_attachments_for_this_conversation . '
                </p>';
                $replace = array('%msg%' => $btn);
                $html .= get_view(DIR_TMPL . $this->module . "/workroom/no_attachment_row-nct.tpl.php", $replace);
            }
            return $html;
        }
    }

    public function panel_workroom()
    {
        if ($this->proj['jobStatus'] != 'progress') {
            echo json_encode(array(
                'status'   => 0,
                'msg'      => This_project_does_not_meet_criteria_to_perform_this_action,
                'redirect' => $this->reqData['origin'],
            ));
            exit;
        } else {
            $replace = array(
                '%unreadMessages%'    => $this->workroomMessages($count = true),
                '%unreadAttachments%' => $this->workroomAttachments($count = true),
                '%messages%'          => $this->workroomMessages(),
                '%attachments%'       => $this->workroomAttachments(),
                '%tokenValue%'        => setFormToken(),
            );
            return get_view(DIR_TMPL . $this->module . "/workroom/workroom-nct.tpl.php", $replace);
        }
    }

    public function raiseDispute()
    {
        //check if escrow
        if ($this->proj['jobStatus'] != 'progress') {
            echo json_encode(array(
                'status'   => 0,
                'msg'      => We_can_not_accept_dispute_for_this_project_as_the_status_of_project_does_not_meet_required_criteia,
                'redirect' => $this->reqData['origin'],
            ));
            exit;
        } else {

            //check if escrow
            $bid = $this->db->pdoQuery('SELECT b.* FROM tbl_bids AS b WHERE b.userId = ? AND b.`projectId` = ? AND b.`isActive`="y" AND b.`isNulled`="n" AND b.isFinal="y" GROUP BY b.userId ORDER BY b.id DESC', array(
                $this->proj['provider']['userId'],
                $this->proj['id'],
            ))->result();

            if ($bid['escrow'] == 'y') {
                $affectedRows = $this->db->update($this->table, array('jobStatus' => 'dispute'), array('id' => $this->proj['id']))->affectedRows();
                if ($affectedRows == null) {
                    if ($this->dataOnly) {
                        return array(
                            'status' => 0,
                            'msg'    => toastr_something_went_wrong,
                        );
                    } else {
                        $this->toastrExit(0, toastr_something_went_wrong);
                    }
                } else {
                    if ($this->dataOnly) {
                        return array(
                            'status' => 1,
                            'msg'    => You_have_raised_a_dispute_Please_try_to_negotiate_first_mutually,
                        );
                    } else {
                        //Start : Mail Sent to user

                        if ($this->sessUserId == $this->proj['userId']) {

                            $array = generateEmailTemplate('user_raised_dipsute', array(
                                'greetings'    => ucfirst($this->proj['provider']['firstName']),
                                'userName'     => $this->sessFirstName,
                                'projectTitle' => $this->proj['title'],
                                'projectLink'  => SITE_URL . $this->reqData['profileLink'] . '/' . $this->proj['slug'],
                                'date'         => date(PHP_DATE_FORMAT),
                            ));
                            sendEmailAddress($this->proj['provider']['email'], $array['subject'], $array['message']);
                        } else {

                            $array = generateEmailTemplate('user_raised_dipsute', array(
                                'greetings'    => ucfirst($this->user['firstName']),
                                'userName'     => $this->sessFirstName,
                                'projectTitle' => $this->proj['title'],
                                'projectLink'  => SITE_URL . $this->reqData['profileLink'] . '/' . $this->proj['slug'],
                                'date'         => date(PHP_DATE_FORMAT),
                            ));
                            sendEmailAddress($this->user['email'], $array['subject'], $array['message']);
                        }
                        //END : Mail Sent to user
                        echo json_encode(array(
                            'status'   => 1,
                            'msg'      => You_have_raised_a_dispute_Please_try_to_negotiate_first_mutually,
                            'redirect' => $this->reqData['origin'],
                        ));

                        exit;
                    }

                }
            } else {

                if ($this->dataOnly) {
                    return array(
                        'status' => 0,
                        'msg'    => toastr_We_can_not_accept_dispute_for_this_project_as_the_escrow_was_not_enabled_for_this_project,
                    );
                } else {
                    $this->toastrExit(0, toastr_We_can_not_accept_dispute_for_this_project_as_the_escrow_was_not_enabled_for_this_project);
                }
            }
        }

    }

    public function sendDisputeMessage()
    {
        extract($this->reqData);

        if (!isset($dataOnly) || !$dataOnly) {
            if (!checkFormToken($token)) {
                $response['status']   = 0;
                $response['msg']      = Security_token_for_this_action_is_invalid_Please_refresh_and_try_again;
                $response['newToken'] = setFormToken();
                echo json_encode($response);
                exit;
            }
        }

        $desc = (isset($description) && trim($description) != null) ? trim($description) : null;
        if ($desc != null) {
            $to     = ($this->sessUserId == $this->proj['userId']) ? $this->proj['provider']['userId'] : $this->proj['userId'];
            $insArr = array(
                'senderId'    => $this->sessUserId,
                'receiverId'  => $to,
                'type'        => 'dispute',
                'subject'     => $this->proj['title'] . " - Dispute",
                'description' => $desc,
                'createdDate' => date('Y-m-d H:i:s'),
                'projectId'   => $this->proj['id'],
                'readStatus'  => check_noti_enable($to, 1) ? 'n' : 'y',

            );

            $lastId = $this->db->insert("tbl_messages", $insArr)->lastInsertId();
            if ($lastId > 0) {

                if ($this->dataOnly) {
                    return array(
                        'status' => 1,
                        'msg'    => 'undefined',
                        'html'   => $this->panel_dispute_row(),
                    );
                } else {
                    echo json_encode(array(
                        'status' => 1,
                        'msg'    => 'undefined',
                        'html'   => $this->panel_dispute_row(),
                    ));
                    exit;
                }
            }
        } else {
            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => Please_write_message_before_sending,
                );
            } else {
                echo json_encode(array(
                    'status' => 0,
                    'msg'    => Please_write_message_before_sending,
                ));
                exit;
            }
        }
    }

    public function panel_dispute_row()
    {
        $html     = null;
        $pastMsgs = $this->db->pdoQuery("SELECT m.* FROM tbl_messages AS m WHERE m.`type` = 'dispute' AND m.`projectId` = ?  AND ((m.senderId = ? AND m.`receiverId`=?) OR (m.senderId = ? AND m.`receiverId`=?)) ORDER BY m.id ASC ", array(
            $this->proj['id'],
            $this->proj['userId'],
            $this->proj['provider']['userId'],
            $this->proj['provider']['userId'],
            $this->proj['userId'],
        ));

        if ($this->dataOnly) {
            return $pastMsgs->results();
        } else {
            $pastMsgs = $pastMsgs->results();
        }

        //dump_exit($pastMsgs);
        if (!empty($pastMsgs)) {
            foreach ($pastMsgs as $k => $v) {
                extract($v);
                $replace = array(
                    '%class%' => ($this->sessUserId == $senderId) ? 'my-chat' : 'other-chat',
                    '%desc%'  => nl2br(filtering($description)),
                    '%time%'  => date(PHP_DATETIME_FORMAT, strtotime($createdDate)),
                );
                $html .= get_view(DIR_TMPL . $this->module . "/panel_dispute_row-nct.tpl.php", $replace);
            }
        } else {
            if ($this->proj['jobStatus'] != 'dispute') {
                $btn = '
                <div class="form-group">
                    <button type="button" class="btn btn-primary" data-do="raiseDispute">
                        ' . Open_Dispute . '
                    </button>
                </div>';
            } else {
                $btn = '
                <p>
                    ' . Please_send_a_message_to_start_conversation . '
                </p>';
            }
            $replace = array('%msg%' => $btn);
            $html .= get_view(DIR_TMPL . $this->module . "/no_panel_dispute_row-nct.tpl.php", $replace);
        }
        return $html;
    }

    public function panel_dispute()
    {
        //for textarea
        if ($this->proj['jobStatus'] != 'dispute') {
            $pastMsgs = $this->db->pdoQuery("SELECT m.* FROM tbl_messages AS m WHERE m.`type` = 'dispute' AND m.`projectId` = ?  AND ((m.senderId = ? AND m.`receiverId`=?) OR (m.senderId = ? AND m.`receiverId`=?)) ORDER BY m.id ASC ", array(
                $this->proj['id'],
                $this->proj['userId'],
                $this->proj['provider']['userId'],
                $this->proj['provider']['userId'],
                $this->proj['userId'],
            ))->results();
            if ($this->proj['jobStatus'] == 'progress' && !empty($pastMsgs)) {
                $textarea = get_view(DIR_TMPL . $this->module . "/panel_dispute_textarea-nct.tpl.php", array('%tokenValue%' => setFormToken()));
            } else {
                $textarea = null;
            }
        } else {
            $textarea = get_view(DIR_TMPL . $this->module . "/panel_dispute_textarea-nct.tpl.php", array('%tokenValue%' => setFormToken()));
        }

        $replace = array(
            '%row%'      => $this->panel_dispute_row(),
            '%textarea%' => $textarea,
        );
        return get_view(DIR_TMPL . $this->module . "/panel_dispute-nct.tpl.php", $replace);
    }
    public function advanceMilPay()
    {

        extract($this->reqData);
        //check if project is in progress or not
        if ($this->proj['jobStatus'] != 'progress') {
            $this->toastrExit(0, toastr_The_status_of_project_does_not_meet_required_constraints_for_this_action);
        }
        $id = (isset($id) && $id > 0) ? $id : 0;
        if ($id == 0) {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => toastr_No_Such_Milestone_found,
                );
            } else {
                $this->toastrExit(0, toastr_No_Such_Milestone_found);
            }
        } else {
            //check if already paid this milestone
            $isPaid = getTableValue('tbl_payment_history', 'id', array(
                'milestoneId'   => $id,
                'paymentType'   => 'project payment',
                'projectId'     => $this->proj['id'],
                'paymentStatus' => 'completed',
            ));

            if ($isPaid > 0) {
                if ($this->proj['milestones'][0]['id'] == $id) {
                    $this->toastrExit(1, Payment_for_this_milestone_has_been_transferred);
                } else {
                    $this->toastrExit(0, Payment_for_this_milestone_is_already_completed);
                }
            } else {
                $affectedRows = 0;

                $milDetail = $this->milDetail($id);
                if ($this->proj['bid']['escrow'] == 'y') {

                    //check if customer has enough balance
                    $custBal = $this->user['walletamount'];

                    //if provider has set escrow then deduct escrow commission first
                    $milPrice         = $milDetail['price'];
                    $escrowCommission = ($milPrice * ESCROW_COMMISSION) / 100;
                    $balToAdd         = $milPrice - $escrowCommission;
                    //if customer does not have sufficient amount during confimation of milestones other then first mil then give message else deduct from customer wallet
                    //$this->fb->info(array('custBal'=>$custBal,'baltoAdd'=>$balToAdd,'isFirstMil'=>($this->proj['milestones'][0]['id'] == $id)));
                    if ((float) $custBal < (float) $milPrice && $this->proj['milestones'][0]['id'] != $id) {

                        if ($this->dataOnly) {
                            return array(
                                'status' => 0,
                                'msg'    => Insufficient_wallet_amount_You_must_have_at_least_PART1 . " " . $milPrice . " " . Insufficient_wallet_amount_You_must_have_at_least_PART2,
                            );
                        } else {
                            echo json_encode(array(
                                'status' => 0,
                                'msg'    => Insufficient_wallet_amount_You_must_have_at_least_PART1 . " " . $milPrice . " " . Insufficient_wallet_amount_You_must_have_at_least_PART2,
                                'pp'     => $this->proj['price'],
                            ));
                            exit;
                        }
                    } else {

                        //deduct from customer if not first milestone
                        if ($this->proj['milestones'][0]['id'] != $id) {
                            $this->db->pdoQuery('UPDATE tbl_users SET walletamount = walletamount - ' . $milPrice . ' WHERE userId = ? ', array(
                                $this->proj['userId'],
                            ));
                        }

                        $this->db->insert('tbl_payment_history', array(
                            'userId'        => $this->proj['userId'],
                            'paymentType'   => 'project payment',
                            'paymentStatus' => 'completed',
                            'totalAmount'   => (string) $balToAdd,
                            'createdDate'   => date('Y-m-d H:i:s'),
                            'projectId'     => $this->proj['id'],
                            'milestoneId'   => $id,
                        ));
                        echo json_encode(array(
                            'status'   => 1,
                            'msg'      => Payment_for_this_milestone_has_been_transferred,
                            'redirect' => $origin,
                        ));
                        exit;
                        //$this->toastrExit(1, Payment_for_this_milestone_has_been_transferred);

                        //send mail & notification to provider about advance payment received
                    }

                } else {
                    $this->toastrExit(0, Escrow_is_not_set_for_this_project_and_hence_no_advance_payment_is_required);

                }
            }

        }
    }

    public function payMil()
    {

        extract($this->reqData);
        //check if project is in progress or not
        if ($this->proj['jobStatus'] != 'progress') {
            $this->toastrExit(0, toastr_The_status_of_project_does_not_meet_required_constraints_for_this_action);
        }

        $id = (isset($mid) && $mid > 0) ? $mid : 0;
        if ($id == 0) {
            $this->toastrExit(0, toastr_No_Such_Milestone_found);
        } else {
            //check if already paid this milestone
            $isPaid = getTableValue('tbl_milestone', 'id', array(
                'id'     => $id,
                'status' => 'paid',
            ));
            $checkAdvancePay = getTableValue('tbl_payment_history', 'id', array('projectId' => $this->proj['id'], 'milestoneId' => $id));

            if ($isPaid > 0) {
                if ($this->proj['milestones'][0]['id'] == $id) {
                    $this->toastrExit(1, Payment_for_this_milestone_has_been_transferred);
                } else {
                    $this->toastrExit(0, Payment_for_this_milestone_is_already_completed);
                }

            } else {
                $affectedRows = 0;
                //check if escrow
                $bid = $this->db->pdoQuery('SELECT b.* FROM tbl_bids AS b WHERE b.userId = ? AND b.`projectId` = ? AND b.`isActive`="y" AND b.`isNulled`="n" AND b.isFinal="y" GROUP BY b.userId ORDER BY b.id DESC', array(
                    $this->proj['provider']['userId'],
                    $this->proj['id'],
                ))->result();

                $milDetail = $this->milDetail($id);
                if ($bid['escrow'] == 'y') {

                    //check if customer has enough balance
                    $custBal = $this->user['walletamount'];

                    //if provider has set escrow then deduct escrow commission first
                    $milPrice         = $milDetail['price'];
                    $escrowCommission = ($milPrice * ESCROW_COMMISSION) / 100;
                    $balToAdd         = $milPrice - $escrowCommission;

                    //add to provider
                    $affectedRows = $this->db->pdoQuery('UPDATE tbl_users SET walletamount = walletamount + ' . $balToAdd . ' WHERE userId = ? ', array($this->proj['provider']['userId']))->affectedRows();

                    //deduct from customer if not first milestone && advance payment is not done for this mil
                    $checkAdvancePay = getTableValue('tbl_payment_history', 'id', array('projectId' => $this->proj['id'], 'milestoneId' => $id));

                    if ($this->proj['milestones'][0]['id'] != $id && !$checkAdvancePay) {

                        if ((float) $custBal < (float) $milPrice && $this->proj['milestones'][0]['id'] != $id) {

                            if ($this->dataOnly) {
                                return array(
                                    'status' => 0,
                                    'msg'    => Insufficient_wallet_amount_You_must_have_at_least_PART1 . " " . $milPrice . " " . Insufficient_wallet_amount_You_must_have_at_least_PART2,
                                );
                            } else {
                                echo json_encode(array(
                                    'status' => 0,
                                    'msg'    => Insufficient_wallet_amount_You_must_have_at_least_PART1 . " " . $milPrice . " " . Insufficient_wallet_amount_You_must_have_at_least_PART2,
                                    'pp'     => $this->proj['price'],
                                ));
                                exit;
                            }
                        } else {

                            //deduct from customer
                            $this->db->pdoQuery('UPDATE tbl_users SET walletamount = walletamount - ' . $milPrice . ' WHERE userId = ? ', array(
                                $this->proj['userId'],
                            ));
                        }
                    }

                    //make entry for escrow in payment history
                    $this->db->insert('tbl_payment_history', array(
                        'userId'        => $this->proj['provider']['userId'],
                        'paymentType'   => 'escrow',
                        'membershipId'  => 0,
                        'paymentStatus' => 'completed',
                        'totalAmount'   => (string) $escrowCommission,
                        'ipAddress'     => get_ip_address(),
                        'balanceAdded'  => (string) $balToAdd,
                        'createdDate'   => date('Y-m-d H:i:s'),
                        'projectId'     => $this->proj['id'],
                        'milestoneId'   => $mid,
                    ));

                } else {
                    //don't deduct escrow commission
                    $balToAdd = $milDetail['price'];

                    //check if customer has enough balance
                    $custBal = $this->user['walletamount'];

                    if ((float) $custBal < (float) $balToAdd) {

                        if ($this->dataOnly) {
                            return array(
                                'status' => 0,
                                'msg'    => Insufficient_wallet_amount_You_must_have_at_least_PART1 . " " . $balToAdd . " " . Insufficient_wallet_amount_You_must_have_at_least_PART2,
                            );
                        } else {
                            echo json_encode(array(
                                'status' => 0,
                                'msg'    => Insufficient_wallet_amount_You_must_have_at_least_PART1 . " " . $balToAdd . " " . Insufficient_wallet_amount_You_must_have_at_least_PART2,
                                'pp'     => $this->proj['price'],
                            ));
                            exit;
                        }
                    } else {

                        $checkAdvancePay = getTableValue('tbl_payment_history', 'id', array('projectId' => $this->proj['id'], 'milestoneId' => $id));

                        if ($checkAdvancePay) {
                            $this->toastrExit(0, Payment_for_this_milestone_is_already_completed);
                        }
                        //deduct from customer
                        $this->db->pdoQuery('UPDATE tbl_users SET walletamount = walletamount - ' . $balToAdd . ' WHERE userId = ? ', array(
                            $this->proj['userId'],
                        ));

                        $this->db->insert('tbl_payment_history', array(
                            'userId'        => $this->proj['userId'],
                            'paymentType'   => 'project payment',
                            'paymentStatus' => 'completed',
                            'totalAmount'   => (string) $balToAdd,
                            'createdDate'   => date('Y-m-d H:i:s'),
                            'projectId'     => $this->proj['id'],
                            'milestoneId'   => $mid,
                        ));

                        //add to provider
                        $affectedRows = $this->db->pdoQuery('UPDATE tbl_users SET walletamount = walletamount + ' . $balToAdd . ' WHERE userId = ? ', array($this->proj['provider']['userId']))->affectedRows();
                    }
                }
                //check if added to provider no matter escrow is yes or no
                if ($affectedRows > 0) {
                    $this->db->update('tbl_milestone', array('status' => 'paid'), array('id' => $id));
                    $_SESSION['sendMailTo'] = issetor($this->proj['provider']['userId'], 0);
                    $array                  = generateEmailTemplate('mil_completion_accepted', array(
                        'greetings'     => ucfirst($this->proj['provider']['firstName']),
                        'custName'      => $this->sessFirstName,
                        'projectName'   => $this->proj['title'],
                        'projectLink'   => SITE_URL . $this->reqData['profileLink'] . '/' . $this->proj['slug'],
                        'confirmDate'   => date(PHP_DATE_FORMAT),
                        'dueDate'       => date(PHP_DATE_FORMAT, strtotime($milDetail['milestone_date'])),
                        'completedDate' => date(PHP_DATE_FORMAT, strtotime($milDetail['pay_request_date'])),
                        'balToAdd'      => CURRENCY_SYMBOL . $balToAdd,
                        'description'   => $milDetail['description'],
                    ));
                    //echo '<br/>'.$array['message'];exit;
                    sendEmailAddress($this->proj['provider']['email'], $array['subject'], $array['message']);

                    //send notification
                    insert_user_notification($typeId = 4, $from = $this->sessUserId, $to = $this->proj['provider']['userId'], $referenceId = $this->proj['id']);

                    //check if all milestones are completed
                    $proj      = $this->projectData($this->reqData['slug']);
                    $UnpaidMil = array_filter($proj['milestones'], function ($val) {
                        return ($val['status'] != 'paid') ? 1 : 0;
                    });

                    if (empty($UnpaidMil)) {
                        $this->db->update($this->table, array('jobStatus' => 'completed'), array('id' => $this->proj['id']));
                    }

                    if ($this->dataOnly) {
                        return array(
                            'status' => 1,
                            'msg'    => Payment_for_this_milestone_has_been_transferred,
                        );
                    } else {
                        echo json_encode(array(
                            'status'   => 1,
                            'msg'      => Payment_for_this_milestone_has_been_transferred,
                            'redirect' => $origin,
                        ));
                        exit;
                    }
                } else {
                    $this->toastrExit(0, toastr_something_went_wrong);
                }

            }

        }
    }

    public function reopenProject()
    {
        extract($this->reqData);

        //check previous status
        if ($this->proj['jobStatus'] != "accepted") {
            $this->toastrExit(0, toastr_The_status_of_project_does_not_meet_required_constraints_for_this_action);
        }

        //update tbl_bids
        $affectedRows = $this->db->update('tbl_bids', array(
            'isNulled' => 'y',
            'isFinal'  => 'n',
        ), array('projectId' => $this->proj['id']))->affectedRows();

        //update tbl_milestone
        $affectedRows = $this->db->update('tbl_milestone', array(
            'isNulled' => 'y',
            'isFinal'  => 'n',
        ), array('projectId' => $this->proj['id']))->affectedRows();

        //delete all past messages related to bids on this project
        $this->db->delete('tbl_messages', array('projectId' => $this->proj['id']));

        //update tbl_projects
        $affectedRowsp = $this->db->update($this->table, array(
            'jobStatus'  => 'reopened',
            'providerId' => 0,
            'price'      => 0,
        ), array('id' => $this->proj['id']))->affectedRows();
        if ($affectedRowsp > 0) {
            //TODO:: send mail & notification

            if ($this->dataOnly) {
                return array(
                    'status' => 1,
                    'msg'    => toastr_project_reopened,
                );
            } else {
                echo json_encode(array(
                    'status'   => 1,
                    'msg'      => toastr_project_reopened,
                    'redirect' => $origin,
                ));
                exit;
            }
        }
    }

    public function editMilestones()
    {
        global $db;

        extract($this->reqData);

        if (!isset($dataOnly) || !$dataOnly) {
            if (!checkFormToken($token)) {
                $response['status']   = 0;
                $response['msg']      = Security_token_for_this_action_is_invalid_Please_refresh_and_try_again;
                $response['newToken'] = setFormToken();
                echo json_encode($response);
                exit;
            }
        }

        if ($this->dataOnly) {
            $milestone_date = explode(',', $milestone_date);
            $milestone_date = is_string($milestone_date) ? array($milestone_date) : $milestone_date;
            $price          = explode(',', $price);
            $price          = is_string($price) ? array($price) : $price;
            $description    = explode(',', $description);
            $description    = is_string($description) ? array($description) : $description;
        }

        if (!empty($price) && !empty($milestone_date) && !empty($description)) {

            //check if milestone total is equal to project price
            if (array_sum($price) != $this->proj['price']) {
                $this->toastrExit(0, Milestone_total_must_be_same_as_the_price_of_project);
            }

            //mk array first
            $dataArray = array();
            for ($i = 0; $i < count($price); $i++) {
                if ($price[$i]) {
                    $dataArray[] = array(
                        'price'          => $price[$i],
                        'milestone_date' => date('Y-m-d H:i:s', strtotime($milestone_date[$i])),
                        'description'    => trim($description[$i]),
                        'projectId'      => $this->proj['id'],
                        'created_date'   => date('Y-m-d H:i:s'),
                    );
                }
            }
            //update old milestones to nulled
            $this->db->update('tbl_milestone', array('isNulled' => 'y'), array('projectId' => $this->proj['id']));

            // use insertBatch function to insert multiple row at once and get all last insert id in array
            $q = $this->db->insertBatch('tbl_milestone', $dataArray, true)->getAllLastInsertId();

            // print array last insert id
            //PDOHelper::PA($q);            
            if (count($q) == count($price)) {
                // send mail & notification to customer
                $_SESSION['sendMailTo'] = issetor($this->user['userId'], 0);
                $array                  = generateEmailTemplate('mil_edited', array(
                    'greetings'    => $this->sessFirstName,
                    'providerName' => ucfirst($this->proj['provider']['firstName']),
                    'projectName'  => $this->proj['title'],
                    'projectLink'  => SITE_URL . $this->reqData['profileLink'] . '/' . $this->proj['slug'],
                    'date'         => date(PHP_DATE_FORMAT),
                ));
                //echo '<br/>'.$array['message'];exit;
                sendEmailAddress($this->user['email'], $array['subject'], $array['message']);

                //send notification
                insert_user_notification($typeId = 9, $from = $this->sessUserId, $to = $this->proj['provider']['userId'], $referenceId = $this->proj['id'], array('%projectName%' => $this->proj['title']));

                $this->toastrExit(1, All_milestones_are_sent_for_approval_to_customer);
            }
        } else {
            $this->toastrExit(0, toastr_fill_all_required_details_before_proceed);
        }

    }

    public function edit_mil_modal()
    {
        extract($this->proj);

        $row       = null;
        $mil_total = 0;
        foreach ($milestones as $k => $v) {

            $row .= get_view(DIR_TMPL . $this->module . "/modal_edit_milestones_row-nct.tpl.php", array(
                '%price%'          => $v['price'],
                '%milestone_date%' => date('Y/m/d', strtotime($v['milestone_date'])),
                '%description%'    => $v['description'],
            ));
            $mil_total += $v['price'];
        }

        if ($this->isMe) {
            if (in_array($jobStatus, array(
                'open',
                'reopened',
            ))) {
                $priceToShow = $budget;
            } else {
                $priceToShow     = $price;
                $adminCommission = (ADMIN_COMMISSION * $price) / 100;
                $priceToShow     = $priceToShow + $adminCommission;
            }

        } else {
            $priceToShow = (in_array($jobStatus, array(
                'open',
                'reopened',
            ))) ? $budget : $price;
        }

        $replace = array(
            '%row%'             => $row,
            '%milestone_total%' => $mil_total,
            '%project_price%'   => $priceToShow,
            '%tokenValue%'      => setFormToken(),
        );
        return get_view(DIR_TMPL . $this->module . "/modal_edit_milestones-nct.tpl.php", $replace);
    }

    public function acceptMil()
    {
        extract($this->reqData);

        //check previous status
        if ($this->proj['jobStatus'] != "accepted") {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => toastr_The_status_of_project_does_not_meet_required_constraints_for_this_action,
                );
            } else {
                echo json_encode(array(
                    'status' => 0,
                    'msg'    => toastr_The_status_of_project_does_not_meet_required_constraints_for_this_action,
                ));
                exit;
            }
        }

        //change to mil_accepted
        $affectedRows           = $this->db->update($this->table, array('jobStatus' => 'milestone_accepted', 'hiredDate' => date('Y-m-d H:i:s')), array('id' => $this->proj['id']))->affectedRows();
        $_SESSION['sendMailTo'] = issetor($this->proj['provider']['userId'], 0);
        $array                  = generateEmailTemplate('mil_accepted', array(
            'greetings'   => ucfirst($this->proj['provider']['firstName']),
            'custName'    => $this->sessFirstName,
            'projectName' => $this->proj['title'],
            'projectLink' => SITE_URL . $this->reqData['profileLink'] . '/' . $this->proj['slug'],
            'date'        => date(PHP_DATE_FORMAT),
        ));

        sendEmailAddress($this->proj['provider']['email'], $array['subject'], $array['message']);

        //send notification
        insert_user_notification($typeId = 5, $from = $this->sessUserId, $to = $this->proj['provider']['userId'], $referenceId = $this->proj['id']);

        if ($this->dataOnly) {
            return array(
                'status' => 1,
                'msg'    => toastr_Splendid_Milestone_is_accepted,
            );
        } else {
            echo json_encode(array(
                'status'   => 1,
                'msg'      => toastr_Splendid_Milestone_is_accepted,
                'redirect' => $origin,
            ));
            exit;
        }
    }

    public function startProject($details = false)
    {

        extract($this->reqData);

        //check previous status
        if ($this->proj['jobStatus'] != "milestone_accepted") {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => toastr_The_status_of_project_does_not_meet_required_constraints_for_this_action,
                );
            } else {
                echo json_encode(array(
                    'status' => 0,
                    'msg'    => toastr_The_status_of_project_does_not_meet_required_constraints_for_this_action,
                ));
                exit;
            }
        }

        //check if already paid this milestone
        $isPaid = getTableValue('tbl_payment_history', 'id', array(
            'milestoneId'   => $this->proj['milestones'][0]['id'],
            'paymentType'   => 'project payment',
            'projectId'     => $this->proj['id'],
            'paymentStatus' => 'completed',
        ));

        if ($isPaid > 0) {
            $this->toastrExit(0, Payment_for_this_milestone_is_already_completed);
        }

        //check if escrow then do payment(admin commission + 1st milestone payment)
        //else only admin commission

        //get accepted bid
        $bid = $this->db->pdoQuery('SELECT b.* FROM tbl_bids AS b WHERE b.userId = ? AND b.`projectId` = ? AND b.`isActive`="y" AND b.`isNulled`="n" AND b.isFinal="y" GROUP BY b.userId ORDER BY b.id DESC', array(
            $this->proj['provider']['userId'],
            $this->proj['id'],
        ))->result();
        //calculate amount
        if ($bid['escrow'] == 'y') {
            $mil_amount      = $this->proj['milestones'][0]['price'];
            $adminCommission = (ADMIN_COMMISSION * $this->proj['price']) / 100;
            $balanceToDeduct = $mil_amount + $adminCommission;
            $firstMileId     = $this->proj['milestones'][0]['id'];
            //$balanceToDeduct = $this->proj['price'] + $adminCommission;
        } else {
            $adminCommission = (ADMIN_COMMISSION * $this->proj['price']) / 100;
            $balanceToDeduct = $adminCommission;
            $firstMileId     = 0;

        }

        if ($details) {
            if ($this->dataOnly) {
                return array("adminCommission" => $adminCommission,
                    "balanceToDeduct"              => $balanceToDeduct);
            } else {
                echo json_encode(array(
                    'status'          => 1,
                    'msg'             => "undefined",
                    "adminCommission" => $adminCommission,
                    "balanceToDeduct" => $balanceToDeduct,
                ));
                exit;
            }
        }

        //check if customer has enough balance
        $custBal = $this->user['walletamount'];

        if ($custBal < $balanceToDeduct) {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => Insufficient_wallet_amount_You_must_have_at_least_PART1 . " " . $balanceToDeduct . " " . Insufficient_wallet_amount_You_must_have_at_least_PART2,
                );
            } else {
                echo json_encode(array(
                    'status' => 0,
                    'msg'    => Insufficient_wallet_amount_You_must_have_at_least_PART1 . " " . $balanceToDeduct . " " . Insufficient_wallet_amount_You_must_have_at_least_PART2,
                ));
                exit;
            }
        }

        //now deduct amount
        $this->db->pdoQuery('UPDATE tbl_users SET walletamount = walletamount - ' . $balanceToDeduct . ' WHERE userId = ? ', array($this->proj['userId']));

        $this->db->insert('tbl_payment_history', array(
            'userId'        => $this->proj['userId'],
            'paymentType'   => 'project payment',
            'adminCommission' => $adminCommission,
            'paymentStatus' => 'completed',
            'totalAmount'   => (string) $balanceToDeduct,
            'createdDate'   => date('Y-m-d H:i:s'),
            'projectId'     => $this->proj['id'],
            'milestoneId'   => $firstMileId,
        ));

        //when payment is done change status to progress
        $this->db->update($this->table, array('jobStatus' => 'progress'), array('id' => $this->proj['id']));

        // send mail & notification
        $_SESSION['sendMailTo'] = issetor($this->proj['provider']['userId'], 0);
        $array                  = generateEmailTemplate('proj_start', array(
            'greetings'   => ucfirst($this->proj['provider']['firstName']),
            'custName'    => $this->sessFirstName,
            'projectName' => $this->proj['title'],
            'projectLink' => SITE_URL . $this->reqData['profileLink'] . '/' . $this->proj['slug'],
            'date'        => date(PHP_DATE_FORMAT),
        ));
        //echo '<br/>'.$array['message'];exit;
        sendEmailAddress($this->proj['provider']['email'], $array['subject'], $array['message']);

        //send notification
        insert_user_notification($typeId = 12, $from = $this->sessUserId, $to = $this->proj['provider']['userId'], $referenceId = $this->proj['id']);

        if ($this->dataOnly) {
            return array(
                'status' => 1,
                'msg'    => project_is_in_progress,
            );
        } else {
            echo json_encode(array(
                'status'   => 1,
                'msg'      => project_is_in_progress,
                'redirect' => $origin,
            ));
            exit;
        }
    }

    public function createMilestones()
    {
        global $db;
        extract($this->reqData);

        if (!isset($dataOnly) || !$dataOnly) {
            if (!checkFormToken($token)) {
                $response['status']   = 0;
                $response['msg']      = Security_token_for_this_action_is_invalid_Please_refresh_and_try_again;
                $response['newToken'] = setFormToken();
                echo json_encode($response);
                exit;
            }
        }

        if ($this->dataOnly) {
            $milestone_date = explode(',', $_REQUEST['milestone_date']);
            $price          = explode(',', $_REQUEST['price']);
            $description    = explode(',', $_REQUEST['description']);
        }

        if (!empty($price) && !empty($milestone_date) && !empty($description)) {

            //check if milestone total is equal to project price
            if ((float) array_sum($price) != (float) $this->proj['price']) {

                if ($this->dataOnly) {
                    return array(
                        'status' => 0,
                        'msg'    => Milestone_total_must_be_same_as_the_price_of_project,
                    );
                } else {
                    echo json_encode(array(
                        'status' => 0,
                        'msg'    => Milestone_total_must_be_same_as_the_price_of_project,
                    ));
                    exit;
                }
            }

            //mk array first
            $dataArray = array();
            for ($i = 0; $i < count($price); $i++) {
                $dataArray[] = array(
                    'price'          => $price[$i],
                    'milestone_date' => date('Y-m-d H:i:s', strtotime($milestone_date[$i])),
                    'description'    => trim($description[$i]),
                    'projectId'      => $this->proj['id'],
                    'created_date'   => date('Y-m-d H:i:s'),
                );
            }

            // use insertBatch function to insert multiple row at once and get all last insert id in array
            $q = $this->db->insertBatch('tbl_milestone', $dataArray, true)->getAllLastInsertId();
            // print array last insert id
            //PDOHelper::PA($q);
            if (count($q) == count($price)) {

                //send mail & notification to customer
                $_SESSION['sendMailTo'] = issetor($this->user['userId'], 0);
                $array                  = generateEmailTemplate('mil_created', array(
                    'greetings'    => ucfirst($this->user['firstName']),
                    'providerName' => $this->sessFirstName,
                    'projectName'  => $this->proj['title'],
                    'projectLink'  => SITE_URL . $this->reqData['profileLink'] . '/' . $this->proj['slug'],
                    'date'         => date(PHP_DATE_FORMAT),
                ));
                //echo '<br/>'.$array['message'];exit;
                sendEmailAddress($this->user['email'], $array['subject'], $array['message']);

                //send notification
                insert_user_notification($typeId = 6, $from = $this->sessUserId, $to = $this->user['userId'], $referenceId = $this->proj['id'], array('%projectName%' => $this->proj['title']));

                if ($this->dataOnly) {
                    return array(
                        'status' => 1,
                        'msg'    => All_milestones_are_sent_for_approval_to_customer,
                    );
                } else {
                    echo json_encode(array(
                        'status' => 1,
                        'msg'    => All_milestones_are_sent_for_approval_to_customer,
                    ));
                    exit;
                }
            }

        } else {
            echo json_encode(array(
                'status' => 0,
                'msg'    => toastr_fill_all_required_details_before_proceed,
            ));
            exit;
        }

    }

    public function create_mil_modal()
    {
        return get_view(DIR_TMPL . $this->module . "/modal_create_milestones-nct.tpl.php",
            array('%project_price%' => $this->proj['price'], '%tokenValue%' => setFormToken()));
    }

    public function panel_milestones_row()
    {
        $html = $qry = $condition = $sort_by = null;
        extract($this->proj);
        if (!empty($milestones)) {
            $i       = 0;
            $lastKey = count($milestones) - 1;
            foreach ($milestones as $k => $v) {
                ++$i;
                extract($v);
                $btns = null;
                //////////////////
                //$this->fb->info($k);

                if ($jobStatus == "progress" || $jobStatus == "completed") {
                    switch ($status) {
                        case 'unapproved':

                            if ($bid['escrow'] == 'y') {
                                if ($i == 1) {
                                    if ($this->isMe) {
                                        $btns = '<a href="javascript:void(0);" class="btn btn_blue_new small-font16" >' . Waiting_for_request . '</a><p>*' . Wait_for_provider_to_ask_approval . '</p>';
                                    } else {
                                        $btns = '<a href="javascript:void(0);" class="btn btn_blue_new small-font16" data-operation="requestMilApprove" data-info="' . $id . '">' . Send_For_Approval . '</a><p>*' . Tell_customer_that_this_milestone_is_completed . '</p>';
                                    }
                                } else {
                                    //check if this mil's payment is already made or not
                                    $milPayCheck = getTableValue('tbl_payment_history', 'id', array('projectId' => $this->proj['id'], 'milestoneId' => $id));
                                    if ($milPayCheck) {
                                        if ($this->isMe) {
                                            $btns = '<a href="javascript:void(0);" class="btn btn_blue_new small-font16" >' . Waiting_for_request . '</a><p>*' . Wait_for_provider_to_ask_approval . '</p>';
                                        } else {
                                            $btns = '<a href="javascript:void(0);" class="btn btn_blue_new small-font16" data-operation="requestMilApprove" data-info="' . $id . '">' . Send_For_Approval . '</a><p>*' . Tell_customer_that_this_milestone_is_completed . '</p>';
                                        }
                                    } else {
                                        if ($this->isMe) {
                                            $btns = '<a href="javascript:void(0);" class="btn btn_blue_new" data-do="advanceMilPay" data-info="' . $id . '"> ' . Advance_Pay . ' </a><p>*' . Since_the_escrow_is_set_you_need_to_pay_in_advance . '</p>';
                                        } else {
                                            $btns = '<a href="javascript:void(0);" class="btn btn_blue_new small-font13">' . Wait_for_advance_payment . '</a><p>*' . Do_not_start_work_for_this_milestone . '</p>';
                                        }
                                    }
                                }
                            } else {
                                $btns = ($this->isMe) ? null : '<a href="javascript:void(0);" class="btn btn_blue_new" data-operation="requestMilApprove" data-info="' . $id . '">' . Send_For_Approval . '</a><p>*' . Tell_customer_that_this_milestone_is_completed . '</p>';
                            }
                            $current   = null;
                            $righttick = null;
                            break;
                        case 'remain':
                            $btns      = ($this->isMe) ? '<a href="javascript:void(0);" class="btn btn_blue_new" data-do="payMil" data-info="' . $id . '">' . btn_Confirm . '</a><p>*' . Provider_has_completed_this_milestone . '</p>' : '<a href="javascript:void(0);" class="btn btn_blue_new">' . Payment_Pending . '</a>';
                            $current   = null;
                            $righttick = null;
                            break;
                        case 'paid':
                            $btns      = '<i class="fa fa-check check-mt"></i>';
                            $current   = 'current';
                            $righttick = 'text-right';
                            break;
                    }
                } else {
                    $btns = $righttick = $current = null;
                }
                //////////////////

                if ($this->dataOnly) {
                    $final_array = array('iterator' => $i, 'price' => $price, 'milestone_date' => date('d-M-Y', strtotime($milestone_date)), 'description' => filtering($description), 'btn' => $btns, 'current' => $current, 'righttick' => $righttick);
                    return $final_array;
                }

                $replace = array(
                    '%iterator%'       => $i,
                    '%price%'          => $price,
                    '%milestone_date%' => date('d-M-Y', strtotime($milestone_date)),
                    '%description%'    => filtering($description),
                    '%btn%'            => $btns,
                    '%current%'        => $current,
                    '%righttick%'      => $righttick,
                );
                $html .= get_view(DIR_TMPL . $this->module . "/panel_milestones_row-nct.tpl.php", $replace);

            }
        }

        if ($this->dataOnly) {
            return array();
        }

        return (trim($html) != null) ? $html : You_do_not_have_any_milestones_on_this_project_yet;
    }

    //see all milestones on the project
    public function panel_milestones()
    {
        $replace = array('%row%' => $this->panel_milestones_row());
        return get_view(DIR_TMPL . $this->module . "/panel_milestones-nct.tpl.php", $replace);
    }

    public function editBid()
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

        //check if project is still open or repoen
        if ($this->proj['jobStatus'] != "open" && $this->proj['jobStatus'] != "reopened") {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => toastr_freezed_for_bidding,
                );
            } else {
                echo json_encode(array(
                    'status' => 0,
                    'msg'    => toastr_freezed_for_bidding,
                ));
                exit;
            }

        } else {

            //check if bidding time is expired
            if (new DateTime() > new DateTime($this->proj['biddingDeadline'])) {

                if ($this->dataOnly) {
                    return array(
                        'status' => 0,
                        'msg'    => toastr_Due_date_for_bidding_on_this_project_has_passed,
                    );
                } else {

                    echo json_encode(array(
                        'status' => 0,
                        'msg'    => toastr_Due_date_for_bidding_on_this_project_has_passed,
                    ));
                    exit;
                }
            } else {
                extract($this->reqData);
                //php validations
                $duration    = (isset($duration) && $duration >= 1) ? $duration : 0;
                $price       = (isset($price) && $price >= 1) ? $price : 0;
                $escrow      = issetor($escrow, 'n');
                $bidDetail   = issetor($bidDetail, null);
                $createdTime = date('Y-m-d H:i:s');
                if ($duration == 0 || $price == 0 || trim($bidDetail) == null) {

                    if ($this->dataOnly) {
                        return array(
                            'status' => 0,
                            'msg'    => toastr_fill_all_required_details_before_proceed,
                        );
                    } else {
                        echo json_encode(array(
                            'status' => 0,
                            'msg'    => toastr_fill_all_required_details_before_proceed,
                        ));
                        exit;
                    }

                }

                //change entry in tbl_bids
                //retrive last bid on this project by me/provider
                $lastbid = getTableValue('tbl_bids', 'MAX(id)', array(
                    'projectId' => $this->proj['id'],
                    'userId'    => $this->sessUserId,
                ));

                $affectedRows = $this->db->update('tbl_bids', array(
                    'duration'   => $duration,
                    'price'      => $price,
                    'escrow'     => $escrow,
                    'bidDetail'  => $bidDetail,
                    'updateTime' => $createdTime,
                ), array('id' => $lastbid))->affectedRows();

                if ($affectedRows > 0) {

                    //send mail & notification to customer
                    $array = generateEmailTemplate('bid_edited', array(
                        'greetings'    => ucfirst($this->user['firstName']),
                        'providerName' => $this->sessFirstName,
                        'projectName'  => $this->proj['title'],
                        'projectLink'  => SITE_URL . $this->reqData['profileLink'] . '/' . $this->proj['slug'],
                        'date'         => date(PHP_DATE_FORMAT),
                    ));
                    //echo '<br/>'.$array['message'];exit;
                    sendEmailAddress($this->user['email'], $array['subject'], $array['message']);

                    //send notification
                    insert_user_notification($typeId = 7, $from = $this->sessUserId, $to = $this->user['userId'], $referenceId = $this->proj['id'], array('%projectName%' => $this->proj['title']));

                    if ($this->dataOnly) {
                        return array(
                            'status' => 1,
                            'msg'    => Your_bid_has_been_updated,
                        );
                    } else {
                        echo json_encode(array(
                            'status' => 1,
                            'msg'    => Your_bid_has_been_updated,
                        ));
                        exit;
                    }
                } else {

                }

            }

        }
    }

    public function edit_bid_modal()
    {

        //check if project is still open or repoen
        if ($this->proj['jobStatus'] != "open" && $this->proj['jobStatus'] != "reopened") {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => toastr_freezed_for_bidding,
                );
            } else {
                echo json_encode(array(
                    'status' => 0,
                    'msg'    => toastr_freezed_for_bidding,
                ));
                exit;
            }
        } else {
            extract($this->proj);
            //check if bidding time is expired
            if (new DateTime() > new DateTime($biddingDeadline)) {

                if ($this->dataOnly) {
                    return array(
                        'status' => 0,
                        'msg'    => toastr_Due_date_for_bidding_on_this_project_has_passed,
                    );
                } else {
                    echo json_encode(array(
                        'status' => 0,
                        'msg'    => toastr_Due_date_for_bidding_on_this_project_has_passed,
                    ));
                    exit;
                }
            } else {
                if ($this->dataOnly) {
                    return array(
                        'status' => 1,
                        'msg'    => 'You are eligible',
                    );
                }
                $bidDetail = $this->db->pdoQuery('SELECT * FROM tbl_bids WHERE userId=? AND projectId =? ', array(
                    $this->sessUserId,
                    $this->proj['id'],
                ))->result();
                extract($bidDetail);
                return get_view(DIR_TMPL . $this->module . "/edit_bid_modal-nct.tpl.php", array(
                    '%duration%'   => $duration,
                    '%price%'      => $price,
                    '%escrowY%'    => ($escrow == "y") ? 'checked="checked"' : null,
                    '%escrowN%'    => ($escrow == "n") ? 'checked="checked"' : null,
                    '%bidDetail%'  => $bidDetail,
                    '%tokenValue%' => setFormToken(),
                ));
            }
        }
    }

    public function placeBid()
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

//check if project is still open or repoen
        if ($this->proj['jobStatus'] != "open" && $this->proj['jobStatus'] != "reopened") {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => toastr_freezed_for_bidding,
                );
            } else {
                echo json_encode(array(
                    'status' => 0,
                    'msg'    => toastr_freezed_for_bidding,
                ));
                exit;
            }
        }

        //check if user already placed bid
        $bidExist = $this->db->pdoQuery('SELECT id FROM tbl_bids WHERE userId=? AND projectId =?  AND `isNulled` = "n"', array(
            $this->sessUserId,
            $this->proj['id'],
        ))->result();
        $bidExist = $bidExist['id'];

        if ($bidExist != null && $bidExist > 0) {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => You_have_already_placed_bid,
                );
            } else {
                echo json_encode(array(
                    'status' => 0,
                    'msg'    => You_have_already_placed_bid,
                ));
                exit;
            }

        }
        //in case project is reopened, check that if user placed bid before or not when project was open
        //if he did then don't deduct credits..
        $placeBidInPast = getTableValue('tbl_bids', 'id', array(
            'userId'    => $this->sessUserId,
            'projectId' => $this->proj['id'],
        ));
        if ($placeBidInPast <= 0) {
            //check if user has suficient credits
            $check = $this->db->pdoQuery('SELECT userId FROM tbl_users WHERE userId=? AND totalCredits >=? ', array(
                $this->sessUserId,
                CREDITS_PER_BID,
            ))->result();
            $check = $check['userId'];
            if ($check == null || $check < 1) {

                if ($this->dataOnly) {
                    return array(
                        'status' => 0,
                        'msg'    => toastr_insufficient_credit_to_bid,
                    );
                } else {
                    echo json_encode(array(
                        'status' => 0,
                        'msg'    => toastr_insufficient_credit_to_bid,
                    ));
                    exit;
                }

            }
        }

        //check if bidding time is expired
        if (new DateTime() > new DateTime($this->proj['biddingDeadline'])) {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => toastr_Due_date_for_bidding_on_this_project_has_passed,
                );
            } else {
                echo json_encode(array(
                    'status' => 0,
                    'msg'    => toastr_Due_date_for_bidding_on_this_project_has_passed,
                ));
                exit;
            }

        } else {
            extract($this->reqData);
            //php validations
            $duration    = (isset($duration) && $duration >= 1) ? $duration : 0;
            $price       = (isset($price) && $price >= 1) ? $price : 0;
            $escrow      = issetor($escrow, 'n');
            $bidDetail   = issetor($bidDetail, null);
            $createdTime = date('Y-m-d H:i:s');
            if ($duration == 0 || $price == 0 || trim($bidDetail) == null) {

                if ($this->dataOnly) {
                    return array(
                        'status' => 0,
                        'msg'    => toastr_fill_all_required_details_before_proceed,
                    );
                } else {
                    echo json_encode(array(
                        'status' => 0,
                        'msg'    => toastr_fill_all_required_details_before_proceed,
                    ));
                    exit;
                }

            }

            //make entry in tbl_bids
            $lastInsId = $this->db->insert('tbl_bids', array(
                'projectId'   => $this->proj['id'],
                'userId'      => $this->sessUserId,
                'duration'    => $duration,
                'price'       => $price,
                'escrow'      => $escrow,
                'bidDetail'   => $bidDetail,
                'createdTime' => $createdTime,
            ))->lastInsertId();

            if ($lastInsId > 0) {
                //deduct credits from user
                $this->db->pdoQuery("UPDATE tbl_users SET totalCredits = totalCredits-? WHERE userId=? ", array(
                    CREDITS_PER_BID,
                    $this->sessUserId,
                ));

                //add log
                $this->db->insert('tbl_credit_log', array(
                    'userId'          => $this->sessUserId,
                    'amount'          => CREDITS_PER_BID,
                    'transactionType' => 'bid',
                    'referenceId'     => $this->proj['id'],
                    'createdDate'     => date('Y-m-d H:i:s'),
                    'description'     => CREDITS_PER_BID . ' ' . credits_debited_in_reference_to_your_bid_on . ' ' . $this->proj['title'] . '.',
                ));

                //send mail & notification to customer
                $_SESSION['sendMailTo'] = issetor($this->user['userId'], 0);

                $array = generateEmailTemplate('bid_placed', array(
                    'greetings'    => ucfirst($this->user['firstName']),
                    'providerName' => $this->sessFirstName,
                    'projectName'  => $this->proj['title'],
                    'projectLink'  => SITE_URL . $this->reqData['profileLink'] . '/' . $this->proj['slug'],
                    'date'         => date(PHP_DATE_FORMAT),
                ));
                //echo '<br/>'.$array['message'];exit;

                sendEmailAddress($this->user['email'], $array['subject'], $array['message']);

                //send notification
                insert_user_notification($typeId = 8, $from = $this->sessUserId, $to = $this->user['userId'], $referenceId = $this->proj['id'], array('%projectName%' => $this->proj['title']));

                if ($this->dataOnly) {
                    return array(
                        'status' => 1,
                        'msg'    => Your_bid_is_received,
                    );
                } else {
                    echo json_encode(array(
                        'status' => 1,
                        'msg'    => Your_bid_is_received,
                    ));
                    exit;
                }

            } else {

            }

        }

    }

    public function place_bid_modal()
    {
        //check if project is still open or repoen
        if ($this->proj['jobStatus'] != "open" && $this->proj['jobStatus'] != "reopened") {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => toastr_freezed_for_bidding,
                );
            } else {
                echo json_encode(array(
                    'status' => 0,
                    'msg'    => toastr_freezed_for_bidding,
                ));
                exit;
            }
        }

        if ($this->sessUserId <= 0) {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => Please_login_to_perform_this_action,
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

        $pSkills = $this->db->pdoQuery('select GROUP_CONCAT( DISTINCT ps.skillId SEPARATOR "," ) AS skillId FROM tbl_project_skills as ps LEFT JOIN tbl_skills as s ON ps.skillId = s.id WHERE ps.projectId =? AND s.status= "a" ', array($this->proj['id']))->result();
        $pSkills = explode(',', $pSkills['skillId']);
        $pSkills = array_filter($pSkills, function ($val) {
            return !empty($val);
        });
        $uSkills = $this->db->pdoQuery('select GROUP_CONCAT( DISTINCT us.skillId SEPARATOR "," ) AS skillId FROM tbl_user_skills as us LEFT JOIN tbl_skills as s ON us.skillId = s.id WHERE us.userId =? AND s.status= "a" ', array($this->sessUserId))->result();
        $uSkills = explode(',', $uSkills['skillId']);
        $uSkills = array_filter($uSkills, function ($val) {
            return !empty($val);
        });
        $diff = array();

        //$this->fb->info('ps',$pSkills);
        //$this->fb->info('us',$uSkills);
        if (!empty($pSkills)) {
            $diff = array_diff($pSkills, $uSkills);
            if (count($diff) == count($pSkills)) {

                if ($this->dataOnly) {
                    return array(
                        'status' => 0,
                        'msg'    => toastr_You_must_have_at_least_one_of_the_required_skills_to_bid_on_this_project,
                    );
                } else {
                    echo json_encode(array(
                        'status' => 0,
                        'msg'    => toastr_You_must_have_at_least_one_of_the_required_skills_to_bid_on_this_project,
                    ));
                    exit;
                }
            }
        }

        //check if user already placed bid
        $bidExist = $this->db->pdoQuery('SELECT id FROM tbl_bids WHERE userId=? AND projectId =? AND isNulled="n" ', array(
            $this->sessUserId,
            $this->proj['id'],
        ))->result();
        $bidExist = $bidExist['id'];
        if ($bidExist != null && $bidExist > 0) {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => You_have_already_placed_bid,
                );
            } else {
                echo json_encode(array(
                    'status' => 0,
                    'msg'    => You_have_already_placed_bid,
                ));
                exit;
            }
        }
        //in case project is reopened, check that if user placed bid before or not when project was open
        //if he did then don't deduct credits..
        $placeBidInPast = getTableValue('tbl_bids', 'id', array(
            'userId'    => $this->sessUserId,
            'projectId' => $this->proj['id'],
        ));
        if ($placeBidInPast <= 0) {
            //check if user has suficient credits
            $check = $this->db->pdoQuery('SELECT userId FROM tbl_users WHERE userId=? AND totalCredits >=? ', array(
                $this->sessUserId,
                CREDITS_PER_BID,
            ))->result();
            $check = $check['userId'];
            if ($check == null || $check < 1) {

                if ($this->dataOnly) {
                    return array(
                        'status' => 0,
                        'msg'    => toastr_insufficient_credit_to_bid,
                    );
                } else {
                    echo json_encode(array(
                        'status' => 0,
                        'msg'    => toastr_insufficient_credit_to_bid,
                    ));
                    exit;
                }
            }
        }

        extract($this->proj);
        //check if bidding time is expired
        if (new DateTime() > new DateTime($biddingDeadline)) {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => toastr_Due_date_for_bidding_on_this_project_has_passed,
                );
            } else {
                echo json_encode(array(
                    'status' => 0,
                    'msg'    => toastr_Due_date_for_bidding_on_this_project_has_passed,
                ));
                exit;
            }
        } else {

            if ($this->dataOnly) {
                return array(
                    'status' => 1,
                    'msg'    => 'You are eligible',
                );
            }

            return get_view(DIR_TMPL . $this->module . "/place_bid_modal-nct.tpl.php", array('%tokenValue%' => setFormToken()));
        }

    }

    public function tab_ul()
    {
        $html     = null;
        $tab_data = array();
        extract($this->proj);
        //dump_exit($this -> user['userType']);
        //check if this is my project
        if ($this->isMe) {
            $html .= '
                <li>
                    <a href="javascript:void(0);" class="current" data-method="panel_about">' . About . '</a>
                </li>
                <li>
                    <a href="javascript:void(0);" class="" data-method="panel_bids">' . Bids . '</a>
                </li>
            ';
            $tab_data[] = About;
            $tab_data[] = Bids;
            if (!in_array($jobStatus, array(
                'open',
                'reopened',
                'closed',
                'expired',
            ))) {
                $html .= '
                    <li>
                        <a href="javascript:void(0);" class="" data-method="panel_milestones">' . Milestones . '(' . count($milestones) . ')</a>
                    </li>
                ';
                $tab_data[] = Milestones;
            }

            if ($jobStatus == 'progress') {
                $html .= '
                    <li>
                        <a href="javascript:void(0);" class="" data-method="panel_workroom">' . Workroom . '</a>
                    </li>
                ';
                $tab_data[] = Workroom;
                if ($bid['escrow'] == 'y') {
                    $html .= '
                    <li>
                        <a href="javascript:void(0);" class="" data-method="panel_dispute">' . Dispute . '</a>
                    </li>
                ';
                    $tab_data[] = Dispute;
                }
            }
            if ($jobStatus == 'dispute') {
                $html .= '
                    <li>
                        <a href="javascript:void(0);" class="" data-method="panel_dispute">' . Dispute . '</a>
                    </li>
                ';
                $tab_data[] = Dispute;
            }

            if (in_array($jobStatus, array(
                'completed',
                'closed',
            ))) {
                $html .= '
                    <li>
                        <a href="javascript:void(0);" class="" data-method="panel_reviews">' . Reviews_and_Ratings . '</a>
                    </li>

                ';
                $tab_data[] = Reviews_and_Ratings;
            }

        } else if ($this->sessUserType == "p" && ($this->sessUserId == $this->proj['providerId'] || $this->isBidder($this->sessUserId))) {
            $html .= '
                <li>
                    <a href="javascript:void(0);" class="current" data-method="panel_about">' . About . '</a>
                </li>
                <li>
                    <a href="javascript:void(0);" class="" data-method="panel_bid">' . Bid . '</a>
                </li>

            ';
            $tab_data[] = About;
            $tab_data[] = Bid;
            if (!in_array($jobStatus, array(
                "open",
                "reopened",
                'closed',
                'expired',
            ))) {
                $html .= '
                    <li>
                        <a href="javascript:void(0);" class="" data-method="panel_milestones">' . Milestones . '(' . count($milestones) . ')</a>
                    </li>
                ';
                $tab_data[] = Milestones;
            }

            if ($jobStatus == 'progress') {
                $html .= '
                    <li>
                        <a href="javascript:void(0);" class="" data-method="panel_workroom">' . Workroom . '</a>
                    </li>
                ';
                $tab_data[] = Workroom;
                if ($bid['escrow'] == 'y') {
                    $html .= '
                    <li>
                        <a href="javascript:void(0);" class="" data-method="panel_dispute">' . Dispute . '</a>
                    </li>
                ';
                    $tab_data[] = Dispute;
                }
            }

            if ($jobStatus == 'dispute') {
                $html .= '
                    <li>
                        <a href="javascript:void(0);" class="" data-method="panel_dispute">' . Dispute . '</a>
                    </li>
                ';
                $tab_data[] = Dispute;
            }

            if (in_array($jobStatus, array(
                'completed',
                'closed',
            ))) {
                $html .= '
                    <li>
                        <a href="javascript:void(0);" class="" data-method="panel_reviews">' . Reviews_and_Ratings . '</a>
                    </li>

                ';
                $tab_data[] = Reviews_and_Ratings;
            }
        } else {
            $html .= '
                <li>
                    <a href="javascript:void(0);" class="current" data-method="panel_about">' . About . '</a>
                </li>
            ';
            $tab_data[] = About;
        }
        if ($this->dataOnly) {
            return $tab_data;
            exit;
        }

        return $html;
    }

    //when customer see all bids on the project
    public function panel_bids_row()
    {
        $html       = $qry       = $condition       = $sort_by       = null;
        $paramArray = array($this->proj['id']);
        //get bid placed by all providers
        $qry = ' SELECT IFNULL(AVG(f.averageRating),0) AS average, COUNT(f.id) AS totalReviews, b.id, b.isAccepted, u.profileLink, u.profilePhoto, CONCAT_WS(" ", u.firstName, u.lastName) AS fullName , b.* FROM tbl_bids AS b LEFT JOIN tbl_users AS u ON b.userId = u.userId LEFT JOIN tbl_feedbacks AS f ON b.userId = f.`userto` LEFT JOIN tbl_users AS fromu ON f.userFrom = fromu.userId AND fromu.`isActive` = "y" WHERE b.`projectId` = ? AND b.`isNulled` = "n" AND b.`isActive` = "y" AND b.isFinal="y" AND u.`isActive` = "y"  GROUP BY b.userId ';

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

        if ($this->dataOnly) {
            return array(
                'page'          => issetor($page, 0),
                'numPages'      => issetor($pager->numPages, 0),
                'total_records' => issetor($totalRows, 0),
                'data'          => (is_object($qry) && $qry->affectedRows() > 0) ? $qry->results() : array(),
            );
        }

        if ($qry->affectedRows() > 0) {
            $results = $qry->results();
            foreach ($results as $k => $v) {
                extract($v);
                $bidsBtns = null;
                if ($this->proj['jobStatus'] == "open" || $this->proj['jobStatus'] == "reopened") {
                    $bidsBtns = get_view(DIR_TMPL . $this->module . "/panel_bids_row_accept_btns-nct.tpl.php", array('%bidId%' => $id));
                } elseif ($isAccepted) {
                    $bidsBtns = get_view(DIR_TMPL . $this->module . "/panel_bids_row_accepted_btns-nct.tpl.php", array('%name%' => Accepted));
                } else {
                    $bidsBtns = null;
                }
                $replace = array(
                    '%profileLink%'   => SITE_URL . $profileLink,
                    '%profilePhoto%'  => tim_thumb_image($profilePhoto, 'profile', 75, 75),
                    '%fullName%'      => $fullName,
                    '%averageRating%' => number_format($average, 1),
                    '%totalReviews%'  => $totalReviews,
                    '%bidDetail%'     => String_crop(filtering($bidDetail), 200),
                    '%price%'         => $price,
                    '%escrow%'        => ($escrow == 'y') ? null : 'hide',
                    '%duration%'      => $duration,
                    '%createdTime%'   => date('d-M-Y', strtotime($createdTime)),
                    '%bidId%'         => $id,
                    '%bidsBtns%'      => $bidsBtns,
                );

                $html .= get_view(DIR_TMPL . $this->module . "/panel_bids_row-nct.tpl.php", $replace);
            }
        }
        return (trim($html) != null) ? $html : You_do_not_have_any_bid_on_this_project_yet;

    }

    //when customer see all bids on the project
    public function panel_bids()
    {
        $replace = array('%row%' => $this->panel_bids_row());
        return get_view(DIR_TMPL . $this->module . "/panel_bids-nct.tpl.php", $replace);
    }

    //when provider has placed a bid on the project
    public function panel_bid_row()
    {
        $html = null;

        //get bid placed by loggedin provider
        $results = $this->db->pdoQuery('SELECT b.* FROM tbl_bids AS b WHERE b.userId = ? AND b.`projectId` = ? AND b.`isActive`="y" AND b.`isNulled`="n" AND b.isFinal="y" GROUP BY b.userId ORDER BY b.id DESC', array(
            $this->sessUserId,
            $this->proj['id'],
        ))->results();

        foreach ($results as $k => $v) {
            extract($v);
            $replace = array(
                '%bidDetail%'   => filtering($bidDetail),
                '%price%'       => $price,
                '%escrow%'      => ($escrow == 'y') ? null : 'hide',
                '%duration%'    => $duration,
                '%createdTime%' => date('d-M-Y', strtotime($createdTime)),
                '%bidId%'       => $id,
            );
            $html .= get_view(DIR_TMPL . $this->module . "/panel_bid_row-nct.tpl.php", $replace);
        }
        return (trim($html) != null) ? $html : You_have_not_placed_bid_on_this_project_yet;

    }

    //when provider has placed a bid on the project
    public function panel_bid()
    {
        $replace = array('%row%' => $this->panel_bid_row());
        return get_view(DIR_TMPL . $this->module . "/panel_bid-nct.tpl.php", $replace);
    }

    //for project description, attachments & skills required
    public function panel_about()
    {
        $u = $this->user;
        $p = $this->proj;

        extract($p);
        $replace = array(
            '%description%' => nl2br(filtering($description)),
            '%attachments%' => $this->attachments(),
            '%skills%'      => $this->skills(),
        );
        return get_view(DIR_TMPL . $this->module . "/panel_about-nct.tpl.php", $replace);
    }

    public function place_bid_btn()
    {
        extract($this->proj);
        //for customer
        if ($this->isMe) {
            $btn = $btn_text = null;
            switch ($jobStatus) {
                case 'accepted':
                    //give accept milestones btn if milestones are sent by provider
                    $btn      = (!empty($milestones)) ? '<a href="javascript:void(0);" class="btn btn_blue_new btn_light_hover" data-do="acceptMil">' . Accept_Milestones . '</a>' : '<a href="javascript:void(0);" class="btn btn_blue_new btn_light_hover">' . Awaiting_Milestones . '</a>';
                    $btn_text = (!empty($milestones)) ? "Accept Milestones" : "Awaiting Milestones";
                    break;
                case 'open':
                case 'reopened':
                    //check if there are any bid on this project?
                    if (!empty($bids)) {
                        $btn      = '<a href="javascript:void(0);" data-ele="showBidsPanel" class="btn btn_blue_new btn_light_hover">' . Bids_Recieved . '</a>';
                        $btn_text = "Bids Recieved";
                        break;
                    } else {
                        $btn      = '<a href="' . SITE_PROJECT_EDIT . '?slug=' . $slug . '" class="btn btn_blue_new btn_light_hover"><i class="fa fa-pencil-square-o"></i> ' . Edit_Project . '</a>';
                        $btn_text = "Edit Project";
                        break;
                    }

                case 'milestone_accepted':
                    $ajax_path = SITE_URL . 'ajax-pro';
                    $btn       = '<a id="pay_now_click" class="btn btn_blue_new btn_light_hover"> ' . Pay_Now . ' </a>';
                    $btn_text  = "Pay Now";
                    break;

                case 'progress':
                    $btn      = ($bid['escrow'] == 'y') ? '<a href="javascript:void(0);" class="btn btn_blue_new btn_light_hover" data-do="raiseDispute"><i class="fa fa-pencil-square-o"></i> ' . Open_Dispute . '</a>' : null;
                    $btn_text = ($bid['escrow'] == 'y') ? "Open Dispute" : null;
                    break;
                case 'dispute':
                    //check if escrow
                    $bid = $this->db->pdoQuery('SELECT b.* FROM tbl_bids AS b WHERE b.userId = ? AND b.`projectId` = ? AND b.`isActive`="y" AND b.`isNulled`="n" AND b.isFinal="y" GROUP BY b.userId ORDER BY b.id DESC', array(
                        $this->proj['provider']['userId'],
                        $this->proj['id'],
                    ))->result();
                    $btn      = ($bid['escrow'] == 'y') ? '<a href="javascript:void(0);" class="btn btn_blue_new btn_light_hover" data-do="closeDispute">' . Close_Dispute . '</a>' : null;
                    $btn_text = ($bid['escrow'] == 'y') ? "Close Dispute" : null;
                    break;
                case 'completed':
                    $btn      = '<a href="javascript:void(0);" class="btn btn_blue_new btn_light_hover" data-ele="openReviewModal"><i class="fa fa-star"></i> ' . Rate_and_Review . '</a>';
                    $btn_text = "Rate and Review";
                    break;
                case 'expired':
                    $btn      = '<a href="' . SITE_PROJECT_REPOST . '?slug=' . $slug . '" class="btn btn_blue_new btn_light_hover" ><i class="fa fa-repeat"></i> ' . Repost_this_project . '</a>';
                    $btn_text = "Repost this project";
                    break;
                default:
                    $btn = $btn_text = null;
                    break;
            }

        }
        //for provider
        else {
            $btn = null;
            switch ($jobStatus) {
                case 'accepted':
                    //check if project is awarded to me
                    if ($providerId == $this->sessUserId) {
                        //give create milestones btn if not already created
                        $btn      = (empty($milestones)) ? '<a href="javascript:void(0);" class="btn btn_blue_new btn_light_hover" data-ele="openCreateMilestones">' . Create_Milestones . '</a>' : '<a href="javascript:void(0);" class="btn btn_blue_new btn_light_hover" data-ele="openCreateMilestones" data-info="edit">' . Edit_Milestones . '</a>';
                        $btn_text = (empty($milestones)) ? "Repost this project" : "Edit Milestones";

                    } else {
                        //bidding is closed
                        $btn      = '<a href="javascript:void(0);" class="btn btn_blue_new btn_light_hover disabled">' . Closed_for_Bidding . '</a>';
                        $btn_text = "Closed for Bidding";
                    }
                    break;
                case 'milestone_accepted':
                    $btn      = '<a href="javascript:void(0);" class="btn btn_blue_new btn_light_hover"> ' . Milestones_Accepted . ' </a>';
                    $btn_text = "Milestones Accepted";

                    break;
                case 'progress':
                    if ($providerId == $this->sessUserId) {
                        $btn      = ($bid['escrow'] == 'y') ? '<a href="javascript:void(0);" class="btn btn_blue_new btn_light_hover" data-do="raiseDispute"><i class="fa fa-pencil-square-o"></i> ' . Open_Dispute . '</a>' : null;
                        $btn_text = ($bid['escrow'] == 'y') ? "Open Dispute" : null;
                    }
                    break;
                case 'open':
                case 'reopened':
                    //get bid placed by loggedin provider
                    $bidId = getTableValue('tbl_bids', 'id', array(
                        'userId'    => $this->sessUserId,
                        'projectId' => $this->proj['id'],
                        'isActive'  => 'y',
                        'isNulled'  => 'n',
                    ));
                    if ($bidId) {
                        //give edit bid btn
                        $btn      = '<a href="javascript:void(0);" class="btn btn_blue_new btn_light_hover" data-ele="openPlaceBidModal" data-info="edit" ><i class="fa fa-pencil-square-o"></i> ' . Edit_Bid . '</a>';
                        $btn_text = "Edit Bid";

                    } else if ($this->sessUserType != 'c') {
                        //give place bid btn
                        $btn      = '<a href="javascript:void(0);" class="btn btn_blue_new btn_light_hover" data-ele="openPlaceBidModal"><i class="fa fa-send"></i> ' . Place_Bid . '</a>';
                        $btn_text = "Place Bid";

                    }
                    break;
                case 'dispute':
                    //check if escrow
                    $bid = $this->db->pdoQuery('SELECT b.* FROM tbl_bids AS b WHERE b.userId = ? AND b.`projectId` = ? AND b.`isActive`="y" AND b.`isNulled`="n" AND b.isFinal="y" GROUP BY b.userId ORDER BY b.id DESC', array(
                        $this->proj['provider']['userId'],
                        $this->proj['id'],
                    ))->result();
                    $btn      = ($bid['escrow'] == 'y' && $providerId == $this->sessUserId) ? '<a href="javascript:void(0);" class="btn btn_blue_new btn_light_hover" data-do="closeDispute">' . Close_Dispute . '</a>' : null;
                    $btn_text = ($bid['escrow'] == 'y' && $providerId == $this->sessUserId) ? "Close Dispute" : null;
                    break;
                case 'completed':
                    $btn = $btn_text = null;
                    break;
                case 'expired':
                default:
                    $btn      = '<a href="javascript:void(0);" class="btn btn_blue_new btn_light_hover disabled">' . Closed_for_Bidding . '</a>';
                    $btn_text = "Closed for Bidding";
                    break;
            }
        }
        return ($this->dataOnly) ? array($btn_text, $btn) : $btn;
    }

    public function closeDispute()
    {
        //change project status to progress
        $affectedRows = $this->db->update($this->table, array('jobStatus' => 'progress'), array('id' => $this->proj['id']))->affectedRows();
        if ($affectedRows > 0) {
            //TODO:: send mail to other party
            if ($this->sessUserId == $this->proj['userId']) {
                $_SESSION['sendMailTo'] = issetor($this->proj['provider']['userId'], 0);

                $array = generateEmailTemplate('dispute_closed', array(
                    'greetings'    => ucfirst($this->proj['provider']['firstName']),
                    'userName'     => $this->sessFirstName,
                    'projectTitle' => $this->proj['title'],
                    'projectLink'  => SITE_URL . $this->reqData['profileLink'] . '/' . $this->proj['slug'],
                    'date'         => date(PHP_DATE_FORMAT),
                ));
                //echo '<br/>'.$array['message'];exit;
                sendEmailAddress($this->proj['provider']['email'], $array['subject'], $array['message']);

                //send notification
                insert_user_notification($typeId = 10, $from = $this->sessUserId, $to = $this->proj['provider']['userId'], $referenceId = $this->proj['id'], array('%projectName%' => $this->proj['title']));

            } else {
                $array = generateEmailTemplate('dispute_closed', array(
                    'greetings'    => ucfirst($this->user['firstName']),
                    'userName'     => $this->sessFirstName,
                    'projectTitle' => $this->proj['title'],
                    'projectLink'  => SITE_URL . $this->reqData['profileLink'] . '/' . $this->proj['slug'],
                    'date'         => date(PHP_DATE_FORMAT),
                ));
                //echo '<br/>'.$array['message'];exit;
                sendEmailAddress($this->user['email'], $array['subject'], $array['message']);

                //send notification
                insert_user_notification($typeId = 10, $from = $this->sessUserId, $to = $this->user['userId'], $referenceId = $this->proj['id'], array('%projectName%' => $this->proj['title']));

            }

            if ($this->dataOnly) {
                return array(
                    'status' => 1,
                    'msg'    => Dispute_is_resolved_Project_is_once_again_live_to_continue,
                );
            } else {
                echo json_encode(array(
                    'status'   => 1,
                    'msg'      => Dispute_is_resolved_Project_is_once_again_live_to_continue,
                    'redirect' => $this->reqData['origin'],
                ));
                exit;
            }
        } else {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => toastr_something_went_wrong,
                );
            } else {
                $this->toastrExit(0, toastr_something_went_wrong);
            }
        }
    }

    public function escalateToAdmin()
    {
        //mk entry in project dispute
        $lastInsertedId = $this->db->insert('tbl_project_dispute', array(
            'projectId'   => $this->proj['id'],
            'userId'      => $this->sessUserId,
            'subject'     => Dispute_has_been_escalated,
            'description' => Dispute_for_PROJ_TITLE_has_been_escalated_to_admin_PART1 . ' ' . $this->proj['title'] . ' ' . Dispute_for_PROJ_TITLE_has_been_escalated_to_admin_PART2,
            'milestoneId' => 0, //current milestoneId
            'createdDate' => date('Y-m-d H:i:s'),
        ))->lastInsertId();

        //update jobstatus to close
        if ($lastInsertedId != null) {
            $affectedRows = $this->db->update($this->table, array('jobStatus' => 'closed'), array('id' => $this->proj['id']))->affectedRows();
        }

        if ($affectedRows > 0) {

            //TODO:: send mail to other party
            if ($this->sessUserId == $this->proj['userId']) {
                $_SESSION['sendMailTo'] = issetor($this->proj['provider']['userId'], 0);
                $array                  = generateEmailTemplate('dispute_escalated', array(
                    'greetings'   => ucfirst($this->proj['provider']['firstName']),
                    'userName'    => $this->sessFirstName,
                    'projectName' => $this->proj['title'],
                    'projectLink' => SITE_URL . $this->reqData['profileLink'] . '/' . $this->proj['slug'],
                    'date'        => date(PHP_DATE_FORMAT),
                ));
                //echo '<br/>'.$array['message'];exit;
                sendEmailAddress($this->proj['provider']['email'], $array['subject'], $array['message']);

                //send notification
                insert_user_notification($typeId = 11, $from = $this->sessUserId, $to = $this->proj['provider']['userId'], $referenceId = $this->proj['id'], array('%projectName%' => $this->proj['title']));

            } else {
                $array = generateEmailTemplate('dispute_escalated', array(
                    'greetings'   => ucfirst($this->user['firstName']),
                    'userName'    => $this->sessFirstName,
                    'projectName' => $this->proj['title'],
                    'projectLink' => SITE_URL . $this->reqData['profileLink'] . '/' . $this->proj['slug'],
                    'date'        => date(PHP_DATE_FORMAT),
                ));
                //echo '<br/>'.$array['message'];exit;
                sendEmailAddress($this->user['email'], $array['subject'], $array['message']);

                //send notification
                insert_user_notification($typeId = 11, $from = $this->sessUserId, $to = $this->user['userId'], $referenceId = $this->proj['id'], array('%projectName%' => $this->proj['title']));

            }
            $array = generateEmailTemplate('dispute_escalated_admin', array(
                'greetings'    => 'Admin',
                'userName'     => $this->sessFirstName,
                'projectTitle' => $this->proj['title'],
                'projectLink'  => SITE_URL . $this->reqData['profileLink'] . '/' . $this->proj['slug'],
                'date'         => date(PHP_DATE_FORMAT),
            ));

            sendEmailAddress(ADMIN_EMAIL, $array['subject'], $array['message']);

            if ($this->dataOnly) {
                return array(
                    'status' => 1,
                    'msg'    => Dispute_has_been_escalated,
                    'html'   => '<p>' . Escalated_to_Admin . '</p>',
                );
            } else {
                echo json_encode(array(
                    'status'   => 1,
                    'msg'      => Dispute_has_been_escalated,
                    'redirect' => $this->reqData['origin'],
                    'html'     => '<p>' . Escalated_to_Admin . '</p>',
                ));
                exit;
            }
        } else {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => toastr_something_went_wrong,
                );
            } else {
                $this->toastrExit(0, toastr_something_went_wrong);
            }
        }
    }

    public function escalate_text()
    {

        $check = getTableValue('tbl_project_dispute', 'id', array('projectId' => $this->proj['id']));
        $btn   = null;
        if ($this->proj['jobStatus'] == 'dispute' && $check == null) {
            $btn = '<a href="javascript:void(0);" data-do="escalateToAdmin">' . Escalate_to_Admin . '</a>';
        } elseif ($this->proj['jobStatus'] == 'dispute' && $check > 0) {
            $btn = '<p>' . Escalated_to_Admin . '</p>';
        }

        return $btn;
    }

    public function getPageContent()
    {
        $u = $this->user;
        $p = $this->proj;
        extract($p);

        /*
         * check bidding deadline
         * if no one has bid within it then change status to expired
         */

        if (new DateTime() > new DateTime($biddingDeadline) && empty($bids)) {
            $affectedRows = $this->db->update($this->table, array(
                'jobStatus' => 'expired',
            ), array(
                'id' => $this->proj['id'],
            ))->affectedRows();
        }

        /*
         * Reopen
         * if customer does not pay first milestone within ADMIN_RESPONSE_TIME of the one he accepted then "Reopen"
         */
        $response_time = (int) ADMIN_RESPONSE_TIME;
        $affectedRows3 = $this->db->pdoQuery('UPDATE tbl_projects SET jobStatus = "reopen", isFeatured="n" WHERE id IN (SELECT DISTINCT projectId FROM tbl_milestone WHERE STATUS <> "paid" AND TIMESTAMPDIFF(HOUR, created_date, NOW()) >= ' . $response_time . ') AND jobStatus = "milestone_accepted" ');

        /*
         * check last featured date for expiration
         */

        if (new DateTime() > new DateTime($featuredExpiryDate) || !in_array($jobStatus, array('open', 'reopened'))) {
            $affectedRows = $this->db->update($this->table, array(
                'isFeatured'         => 'n',
                'featuredDays'       => 0,
                'featuredExpiryDate' => null,
            ), array(
                'id' => $this->proj['id'],
            ))->affectedRows();
        }

        $p = $this->projectData($this->reqData['slug']);
        extract($p);

        if ($this->isMe) {
            if (in_array($jobStatus, array(
                'open',
                'reopened', 'expired',
            ))) {
                $priceToShow = $budget;
            } else {
                $priceToShow     = $price;
                $adminCommission = (ADMIN_COMMISSION * $price) / 100;
                $priceToShow     = $priceToShow + $adminCommission;
            }

        } else {
            $priceToShow = (in_array($jobStatus, array(
                'open',
                'reopened', 'expired',
            ))) ? $budget : $price;
        }

        if ($this->dataOnly) {
            return $priceToShow;
            exit;
        }
        $status = ucwords(str_replace(array(
            ' ',
        ), array(
            '_',
        ), $jobStatus));
        $status     = constant($status);
        $exp_wanted = str_replace(array(
            ' ',
        ), array(
            '_',
        ), ucwords($experienceWanted));

        $replace = array(
            '%title%'                  => filtering($title),
            '%budget%'                 => $priceToShow,
            '%jobStatus%'              => $status,
            '%jobStatusClass%'         => ($jobStatus == 'dispute') ? 'close-label' : null,
            '%isFeatured%'             => ($isFeatured == 'y') ? null : 'hide',
            '%cat%'                    => $cateLink . ($subCateLink != null ? ' | ' : '') . $subCateLink,
            '%experienceWanted%'       => '<a target="_blank" href="' . SITE_SEARCH_EXPERIENCE_PROJECTS . urlencode($experienceWanted) . '">' . constant($exp_wanted) . '</a>',
            '%duration%'               => $duration,
            '%bidsUrl%'                => ($this->isMe) ? SITE_URL . $slug . '/bids/' : 'javascript:void(0)',
            '%bids%'                   => $bids,
            '%avgBid%'                 => ($bids > 0) ? number_format($averageBid, 2) : 0,
            '%avgETA%'                 => ($bids > 0) ? (int) $averageETA : 0,
            '%invited%'                => $invited,
            '%createdDate%'            => date('d-M-Y', strtotime($createdDate)),
            '%biddingDeadline%'        => date('d-M-Y', strtotime($biddingDeadline)),
            '%featuredExpiryDate%'     => ($featuredExpiryDate == null) ? Not_Available : date('d-M-Y', strtotime($featuredExpiryDate)),
            '%hidefeaturedExpiryDate%' => ($this->isMe && $isFeatured == 'y' && $jobStatus == 'open') ? null : 'hide',

            //tab_ul
            '%tab_ul%'                 => $this->tab_ul(),

            //tabs_panel
            '%tab_panel%'              => $this->panel_about(),

            //place a bid btn
            '%place_bid_btn%'          => $this->place_bid_btn(),

            //escalate to admin
            '%hide_escalate%'          => ($jobStatus != 'dispute') ? 'hide' : null,
            '%escalate_text%'          => $this->escalate_text(),

            //add to fav
            '%hide_fav%'               => ($this->isMe) ? 'hide' : null,
            '%fav_text%'               => ($isFavorited != null && $isFavorited > 0) ? '<a href="javascript:void(0);" class="black-link" data-operation="favoriteProject" data-info="' . $id . '"><i class="fa fa-heart"></i> ' . Remove_from_Favorites . '</a>' : '<a href="javascript:void(0);" class="black-link" data-operation="favoriteProject" data-info="' . $id . '"><i class="fa fa-heart-o"></i> ' . Add_to_Favorites . '
                                        </a>',

            //report as inappropriate
            '%hide_report%'            => ($this->isMe) ? 'hide' : null,
            '%report_text%'            => ($isReported != null && $isReported > 0) ? '<p>' . You_flagged_this_as_Inappropriate . '</p>' : '<a href="javascript:void(0);" data-operation="reportProject" data-info="' . $id . '">' . Flag_as_Inappropriate . '
                                        </a>',

            //reopen link
            '%hide_reopen%'            => (!$this->isMe) ? 'hide' : null,
            '%reopenlink%'             => (in_array($jobStatus, array('accepted'))) ? '<a href="javascript:void(0);" data-do="reopenProject">' . Reopen_Project . '</a>' : null,

            //invite link
            '%hide_invite%'            => (!$this->isMe) ? 'hide' : null,
            '%invitelink%'             => (in_array($jobStatus, array('open', 'reopened'))) ? '<a href="javascript:void(0);" data-ele="openInviteModal">' . Invite_Provider . '</a>' : null,

            //about client
            //one customer cant see other customer
            '%about_client%'           => $this->about_client(),
        );

        return get_view(DIR_TMPL . $this->module . "/" . $this->module . ".tpl.php", $replace);
    }

    public function about_client()
    {
        $u = $this->user;
        $p = $this->proj;
        extract($p);
        if ($this->sessUserId == 0 || (!$this->isMe && $this->sessUserType == "c")) {
            return null;
        }
        $msg_btn = null;

        if ($this->isMe && !in_array($jobStatus, array(
            'open',
            'reopened',
            'expired',
        ))) {
            //check if provider has sent milestones to customer
            if (!empty($milestones) && in_array($jobStatus, array('accepted'))) {
                $msg_btn = '<li><a href="javascript:void(0);" class="btn btn_blue_new dark-blue-btn" data-do="requestNewMil">' . Request_New_Milestones . '</a></li>';
            } elseif (in_array($jobStatus, array(
                'progress',
                'dispute',
            ))) {
                //$msg_btn = '<li><a href="javascript:void(0);" class="btn btn_blue_new dark-blue-btn">Message</a></li>';
            }

            $replace = array(
                '%profileLink%'         => SITE_URL . $provider['profileLink'],
                '%profilePhoto%'        => tim_thumb_image($provider['profilePhoto'], 'profile', 75, 75),
                '%fullName%'            => $provider['fullName'],
                '%location%'            => (trim($provider['userlocation']) == '') ? Not_Available : $provider['userlocation'],
                '%completed%'           => ($provider['completed'] == null) ? 0 : $provider['completed'],
                '%ongoing%'             => ($provider['ongoing'] != null) ? $provider['ongoing'] : 0,
                '%customerCreatedDate%' => date('d-M-Y', strtotime($provider['createdDate'])),
                '%msg_btn%'             => $msg_btn,
            );

            if ($this->dataOnly) {
                $final_array = array("profilePhoto" => $provider['profilePhoto'], "fullName" => $provider['fullName'], "location" => (trim($provider['userlocation']) == '') ? Not_Available : $provider['userlocation'], "completed" => $provider['completed'], "ongoing" => $provider['ongoing'], "customerCreatedDate" => date('d-M-Y', strtotime($provider['createdDate'])), 'msg_btn' => strip_tags($msg_btn));
                return $final_array;
            } else {
                return get_view(DIR_TMPL . $this->module . "/about_provider-nct.tpl.php", $replace);
            }
        } else {
            //check if I have sent milestones to customer
            if (!empty($milestones) && $this->sessUserId == $provider['userId'] && in_array($jobStatus, array(
                'accepted',
                'milestone_accepted',
                'progress',
                'dispute',
            ))) {
                //$msg_btn = '<li><a href="javascript:void(0);" class="btn btn_blue_new dark-blue-btn">Message</a></li>';
            }
            $replace = array(
                '%profileLink%'         => SITE_URL . $u['profileLink'],
                '%profilePhoto%'        => tim_thumb_image($u['profilePhoto'], 'profile', 75, 75),
                '%fullName%'            => $u['fullName'],
                '%location%'            => (trim($u['userlocation']) == '') ? Not_Available : $u['userlocation'],
                '%project_posted%'      => issetor($u['total'], 0),
                '%spent%'               => issetor($u['spent'], 0),
                '%customerCreatedDate%' => date('d-M-Y', strtotime($u['createdDate'])),
                '%msg_btn%'             => $msg_btn,
            );

            if ($this->dataOnly) {
                $final_array = array("profilePhoto" => $u['profilePhoto'], "fullName" => $u['fullName'], "location" => (trim($u['userlocation']) == '') ? Not_Available : $u['userlocation'], "project_posted" => issetor($u['total'], 0), "spent" => issetor($u['spent'], 0), "customerCreatedDate" => date('d-M-Y', strtotime($u['createdDate'])));
                return $final_array;
            } else {
                return get_view(DIR_TMPL . $this->module . "/about_client-nct.tpl.php", $replace);
            }
        }

    }

    public function skills()
    {
        $skills = $this->db->pdoQuery('SELECT DISTINCT ps.skillId, s.slug, s.skillName_' . $_SESSION['lId'] . ' as skillName FROM tbl_project_skills AS ps LEFT JOIN tbl_skills AS s ON ps.skillId = s.id  WHERE ps.projectId =? AND s.status = "a"', array($this->proj['id']))->results();

        $html = null;
        if (!empty($skills)) {
            foreach ($skills as $k => $v) {
                extract($v);
                $html .= get_view(DIR_TMPL . $this->module . "/skills-nct.tpl.php", array(
                    '%link%' => SITE_SEARCH_SKILLS_PROJECTS . $slug,
                    '%name%' => $skillName,
                ));
            }
        } else {
            $html .= Not_Available;
        }

        return sanitize_output($html);
    }

    public function attachments()
    {
        $files = $this->proj['files'];
        $html  = null;

        foreach ($files as $k => $v) {
            if (file_exists(DIR_UPD . 'project/' . $v['fileName'])) {
                $html .= get_view(DIR_TMPL . $this->module . "/attachments-nct.tpl.php", array(
                    '%link%' => SITE_URL . $this->user['profileLink'] . '/' . $this->proj['slug'] . '/?action=method&method=downloadFile&type=project&filename=' . $v['fileName'],
                    '%name%' => $v['fileName'],
                ));
            }
        }

        return (trim($html) != null) ? $html : '<li>' . Not_Available . '</li>';
    }

    public function userData($profileLink)
    {

        $doesExist = getTableValue('tbl_users', 'userId', array(
            'profileLink' => $profileLink,
            'isActive'    => 'y',
        ));
        //dump_exit(isset($doesExist));
        if (isset($doesExist) && $doesExist > 0) {
            $arr = array();
            $arr = $this->db->pdoQuery("SELECT CONCAT_WS( ', ', tc.`cityName`, s.`stateName`, c.`countryName` ) AS userlocation, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName, u.* FROM tbl_users AS u LEFT JOIN tbl_country AS c ON u.`countryCode` = c.`CountryId` LEFT JOIN tbl_state AS s ON u.`state` = s.`StateID` LEFT JOIN `tbl_city` AS tc ON u.`city` = tc.`CityId` WHERE u.profileLink=? ", array($profileLink))->result();

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
                $arr['total'] = getTableValue('tbl_projects', 'count("id")', array(
                    'userId'   => $arr['userId'],
                    'isActive' => 'y',
                ));
                $spent        = $this->db->pdoQuery("SELECT IFNULL(SUM(ph.totalAmount) + SUM(ph.adminCommission), 0) AS totalAmount FROM tbl_payment_history AS ph WHERE ph.`userId` = ? AND (ph.paymentType = 'featured' OR ph.paymentType = 'project payment' )", array($arr['userId']))->result();
                $arr['spent'] = $spent['totalAmount'];
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

    public function projectData($slug)
    {

        $doesExist = $this->db->pdoQuery("SELECT p.id FROM tbl_projects AS p LEFT JOIN tbl_users AS u ON p.`userId` = u.`userId` WHERE p.`isActive` = 'y' AND u.`isActive` = 'y' AND u.`status` = 'a' AND p.slug = ? AND u.profileLink=?", array(
            $slug,
            $this->reqData['profileLink'],
        ))->result();

        if (isset($doesExist['id']) && $doesExist['id'] > 0) {
            $arr = array();

            if ($this->dataOnly) {
                $arr = $this->db->pdoQuery("SELECT p.*,IFNULL(AVG(b.`price`), 0) AS averageBid, IFNULL(AVG(b.`duration`), 0) AS averageETA, GROUP_CONCAT( DISTINCT CONCAT(s.skillName_" . $_SESSION['lId'] . ", '') SEPARATOR ', ' ) AS skills, c.cateName_" . $_SESSION["lId"] . " AS cateName, c.slug as cslug, sc.cateName_" . $_SESSION["lId"] . " AS subCateName, sc.slug as scslug, CONCAT_WS(',', c.id, sc.id) AS catIds FROM tbl_projects AS p LEFT JOIN tbl_project_skills AS ps ON p.id = ps.`projectId` LEFT JOIN tbl_skills AS s ON ps.`skillId` = s.`id` LEFT JOIN tbl_categories AS c ON p.categoryid = c.id AND c.`isactive` = 'y' LEFT OUTER JOIN tbl_categories AS sc ON p.subcategoryid = sc.id AND sc.`isactive` = 'y' LEFT JOIN tbl_bids AS b ON p.id = b.`projectId` AND b.`isFinal` = 'y' AND b.`isNulled` = 'n'  WHERE p.slug = ? AND s.status = 'a' ", array($slug))->result();
            } else {
                $arr = $this->db->pdoQuery("SELECT p.*,IFNULL(AVG(b.`price`), 0) AS averageBid, IFNULL(AVG(b.`duration`), 0) AS averageETA, GROUP_CONCAT( DISTINCT CONCAT(s.skillName_" . $_SESSION['lId'] . ", ',') SEPARATOR ', ' ) AS skills, c.cateName_" . $_SESSION["lId"] . " AS cateName,c.slug as cslug, sc.cateName_" . $_SESSION["lId"] . " AS subCateName, sc.slug as scslug, CONCAT_WS(',', c.id, sc.id) AS catIds FROM tbl_projects AS p LEFT JOIN tbl_project_skills AS ps ON p.id = ps.`projectId` LEFT JOIN tbl_skills AS s ON ps.`skillId` = s.`id` LEFT JOIN tbl_categories AS c ON p.categoryid = c.id AND c.`isactive` = 'y' LEFT OUTER JOIN tbl_categories AS sc ON p.subcategoryid = sc.id AND sc.`isactive` = 'y' LEFT JOIN tbl_bids AS b ON p.id = b.`projectId` AND b.`isFinal` = 'y' AND b.`isNulled` = 'n'  WHERE p.slug = ? AND s.status = 'a' ", array($slug))->result();
            }

            //dump_exit($arr);
            $arr['cateLink']    = ($arr['categoryId'] > 0) ? '<a target="_blank" href="' . SITE_SEARCH_CATEGORY_PROJECTS . $arr['cslug'] . '">' . ucwords($arr['cateName']) . '</a>' : null;
            $arr['subCateLink'] = ($arr['subcategoryId'] > 0) ? '<a target="_blank" href="' . SITE_SEARCH_CATEGORY_PROJECTS . $arr['scslug'] . '">' . ucwords($arr['subCateName']) . '</a>' : null;
            //get bid placed by all providers
            $arr['bids'] = $this->db->pdoQuery(' SELECT IFNULL(AVG(f.averageRating),0) AS average, COUNT(f.id) AS totalReviews, b.id, u.profileLink, u.profilePhoto, CONCAT_WS(" ", u.firstName, u.lastName) AS fullName , b.* FROM tbl_bids AS b LEFT JOIN tbl_users AS u ON b.userId = u.userId LEFT JOIN tbl_feedbacks AS f ON b.userId = f.`userto` LEFT JOIN tbl_users AS fromu ON f.userFrom = fromu.userId AND fromu.`isActive` = "y" WHERE b.`projectId` = ? AND b.`isActive` = "y" AND b.`isNulled` = "n" AND u.`isActive` = "y" GROUP BY b.userId ', array($arr['id']))->affectedRows();
            //get accepted bid
            $arr['bid'] = $this->db->select('tbl_bids', '*', array('projectId' => $arr['id'], 'isFinal' => 'y', 'isActive' => 'y'))->result();
            if ($this->dataOnly) {
                $arr['experienceWanted'] = str_replace(array(' '), array('_'), ucwords($arr['experienceWanted']));
                $arr['experienceWanted'] = constant($arr['experienceWanted']);
                $arr['jobStatus_toShow'] = ucwords($arr['jobStatus']);
                $arr['jobStatus_toShow'] = constant($arr['jobStatus_toShow']);
            }
            $providerLink    = getTableValue('tbl_users', 'profileLink', array('userId' => $arr['providerId']));
            $arr['provider'] = ($providerLink != null) ? $this->userData($providerLink) : null;

            if ($this->dataOnly) {
                $arr['invited'] = $this->db->pdoQuery("SELECT tpi.*,tu.firstName,tu.lastName,tu.profilePhoto
                    FROM tbl_project_invitation AS tpi
                    LEFT JOIN tbl_users AS tu
                    ON tu.userId = tpi.providerId
                    WHERE tpi.projectId = '" . $arr['id'] . "' AND tpi.isActive = 'y'")->results();
                $arr['invited_count'] = count($arr['invited']);
            } else {
                $arr['invited'] = getTableValue('tbl_project_invitation', 'count("id")', array(
                    'projectId' => $arr['id'],
                    'isActive'  => 'y',
                ));
            }

            if ($this->dataOnly) {
                $arr['files'] = array();
                $file_list    = $this->db->select('tbl_project_files', array('fileName'), array(
                    'projectId' => $arr['id'],
                    'isActive'  => 'y'))->results();
                if (!empty($file_list)) {
                    foreach ($file_list as $value) {
                        if (file_exists(DIR_UPD . 'project/' . $value['fileName'])) {
                            $file_path      = SITE_UPD . 'project/' . $value['fileName'];
                            $arr['files'][] = array("file_path" => $file_path, "fileName" => $value['fileName']);
                        }
                    }
                }
                $arr['files'] = (!empty($arr['files'])) ? $arr['files'] : array();

            } else {
                $arr['files'] = $this->db->select('tbl_project_files', array('fileName'), array(
                    'projectId' => $arr['id'],
                    'isActive'  => 'y',
                ))->results();
            }

            $arr['isReported'] = getTableValue('tbl_report_abuse', 'id', array(
                'projectId' => $arr['id'],
                'userId'    => $this->sessUserId,
                'isActive'  => 'y',
            ));
            $arr['isFavorited'] = getTableValue('tbl_favourites', 'id', array(
                'favoriteId' => $arr['id'],
                'userId'     => $this->sessUserId,
                'type'       => 2,
            ));
            $arr['milestones'] = $this->db->select('tbl_milestone', '*', array(
                'projectId' => $arr['id'],
                'isActive'  => 'y',
                'isNulled'  => 'n',
            ), ' ORDER BY milestone_date ASC ')->results();

            //dump_exit($arr);
            return $arr;
        } else {
            if (!$this->dataOnly) {
                $_SESSION["msgType"] = disMessage(array(
                    'type' => 'err',
                    'var'  => toastr_url_not_found,
                ));
                redirectPage(SITE_URL);
            }
        }
    }

    public function milDetail($mid = 0)
    {
        if ($mid > 0) {
            return $this->db->select('tbl_milestone', '*', array('id' => $mid))->result();
        }
    }

    public function invited_provider_list()
    {

        $invited_array = $this->db->pdoQuery("SELECT tpi.*,tu.firstName,tu.lastName,tu.profilePhoto
                    FROM tbl_project_invitation AS tpi
                    LEFT JOIN tbl_users AS tu
                    ON tu.userId = tpi.providerId
                    WHERE tpi.projectId = '" . $this->proj['id'] . "' AND tpi.isActive = 'y'")->results();

        $final_array = array();

        foreach ($invited_array as $key) {
            $projectId    = $key['projectId'];
            $providerId   = $key['providerId'];
            $createdDate  = $key['createdDate'];
            $profilePhoto = $key['profilePhoto'];
            $fullname     = $key['firstName'] . ' ' . $key['lastName'];
            $isActive     = $key['isActive'];
            $accepted     = $key['accepted'];

            $final_array[] = array('projectId' => $projectId, 'providerId' => $providerId, 'createdDate' => $createdDate, 'profilePhoto' => $profilePhoto, 'fullname' => $fullname, 'isActive' => $isActive, 'accepted' => $accepted);
        }

        return $final_array;
    }

    public function toastrExit($bool = 0, $msg = 'undefined')
    {
        if ($this->dataOnly) {
            return array(
                'status' => $bool,
                'msg'    => $msg);
        } else {
            echo json_encode(array(
                'status' => $bool,
                'msg'    => $msg,
            ));
            exit;
        }

    }

    public function getPaymentDetails()
    {

        if ($this->dataOnly) {
            if (!empty($this->startProject(true))) {
                $response['status'] = 1;
                $response['msg']    = Success;
                $response['data']   = $this->startProject(true);
            } else {
                $response['status'] = 0;
                $response['msg']    = Payment_Details_are_not_available_right_now;
                $response['data']   = array();
            }
            return $response;
        } else {
            return $this->startProject(true);
        }
    }

    public function isBidder($userId = 0)
    {
        if ($userId) {
            $check = getTableValue('tbl_bids', 'id', array(
                'userId'    => $userId,
                'projectId' => $this->proj['id'],
                'isActive'  => 'y',
                'isNulled'  => 'n',
                'isFinal'   => 'y'));

            return ($check) ? true : false;

        } else {
            return false;
        }
    }

}

/*
 * important notes:
 * 1. how to know which milestones are accepted in last?
 * => jobStatus = milestone_accepted && milestones from tbl_milestone as final=y
 */
