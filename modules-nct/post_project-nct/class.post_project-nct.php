<?php
class PostProject
{
    public function __construct($module = "", $id = 0, $reqData = array(), $files = array())
    {
        global $js_variables;
        foreach ($GLOBALS as $key => $values) {
            $this->$key = $values;
        }
        $this->module  = $module;
        $this->id      = $id;
        $this->reqData = $reqData;

        // for web service
        $this->dataOnly   = (isset($this->reqData['dataOnly']) && $this->reqData['dataOnly'] == true) ? true : false;
        $this->sessUserId = (isset($reqData['userId']) && $reqData['userId'] > 0) ? $reqData['userId'] : $this->sessUserId;

        $this->files = $files;
        $this->table = "tbl_projects";
        if($this->dataOnly){
            $_SESSION['lId'] = (isset($this->reqData['lId']) && $this->reqData['lId'] >0)?$this->reqData['lId']:1;
            setLang();
        }
    }

    public function postProject()
    {
        //dump_exit($_SESSION['uploadedFiles']);
        extract($this->reqData);

        ///////////////////
        if (!isset($dataOnly) || !$dataOnly) {
            if (!checkFormToken($token)) {
                $response['status']   = 0;
                $response['msg']      = Security_token_for_this_action_is_invalid_Please_refresh_and_try_again;
                $response['newToken'] = setFormToken();
                echo json_encode($response);
                exit;
            }
        }
        ///////////////////

        $categoryId    = issetor($categoryId, 0);
        $subcategoryId = issetor($subcategoryId, 0);
        $title         = issetor($title, null);
        $description   = issetor($description, null);
        $budget        = (isset($budget) && $budget >= 1) ? $budget : 0;
        $duration      = (isset($duration) && $duration >= 1) ? $duration : 0;
        $isFeatured    = issetor($isFeatured, 'n');
        $featuredDays  = (isset($featuredDays) && $featuredDays >= 1) ? $featuredDays : 0;

        // php side validations
        if (trim($title) == null || $categoryId == 0 || trim($description) == null || $budget < 1 || $duration < 1) {
            $response['status'] = 0;
            $response['msg']    = toastr_fill_all_required_details_before_proceed;

            if ($this->dataOnly) {
                return $response;
                exit;
            } else {
                echo json_encode($response);
                exit;
            }
        }

        if ($isFeatured == 'y' && $featuredDays < 1) {
            $response['status'] = 0;
            $response['msg']    = Minimum_days_to_set_project_as_featured_is_invalid_Please_check;
            if ($this->dataOnly) {
                return $response;
                exit;
            } else {
                echo json_encode($response);
                exit;
            }
        }

        //START:: check if user has enough balance to make featured
        $checkBalance = getTableValue('tbl_users', 'userId', array(
            'walletamount >=' => $featuredDays * FEATURED_PROJ_PRICE,
            'userId'          => $this->sessUserId,
        ));

        if ($isFeatured == 'y' && (!isset($checkBalance) || $checkBalance == "")) {
            $response['status'] = 0;
            $response['msg']    = toastr_insufficient_wallet_amount_deposit_to_proceed.'<br/><br/><a style="float:right;" target="_blank" href="'.SITE_WALLET.'">'.Click_here_to_deposit_funds.'</a>';
            if ($this->dataOnly) {
                return $response;
                exit;
            } else {
                echo json_encode($response);
                exit;
            }
        }
        //END:: check if user has enough balance to make featured

        $insObj                = new stdClass();
        $insObj->userId        = $this->sessUserId;
        $insObj->providerId    = 0;
        $insObj->categoryId    = $categoryId;
        $insObj->subcategoryId = $subcategoryId;
        $insObj->title         = $title;
        $insObj->slug          = makeSlug($title, $this->table, 'id', 'slug');

        $insObj->description = $description;
        $insObj->budget      = $budget;
        //$insObj -> price = null;
        $insObj->duration        = $duration;
        $insObj->biddingDeadline = date('Y-m-d H:i:s', strtotime($biddingDeadline));
        //$insObj->hideFromSearch = null;
        //$insObj->jobStatus = null; 'open','accepted','milestone_accepted','progress','completed','dispute','closed','reopened'
        $insObj->isFeatured         = $isFeatured;
        $insObj->featuredDays       = ($featuredDays > 0 && $isFeatured == 'y') ? $featuredDays : 0;
        $insObj->createdDate        = date('Y-m-d H:i:s');
        $insObj->isActive           = 'y';
        $insObj->featuredExpiryDate = ($featuredDays > 0 && $isFeatured == 'y') ? date('Y-m-d H:i:s', strtotime("+ $featuredDays day")) : null;
        //$insObj->experienceWanted = null; 'entry level','moderate','expert'

        //insert the project
        $lastInsertedId = 0;
        $lastInsertedId = $this->db->insert($this->table, (array) $insObj)->lastInsertId();

        //halt the execution if project is not inserted
        if ($lastInsertedId < 1) {
            $response['status'] = 0;
            $response['msg']    = toastr_We_encounterd_some_issues_while_creating_this_project;
            if ($this->dataOnly) {
                return $response;
                exit;
            } else {
                echo json_encode($response);
                exit;
            }
        }

        //START:: deduct wallet balance if set to featured
        if ($isFeatured == 'y') {
            $calculatedBalance = $featuredDays * FEATURED_PROJ_PRICE;
            $this->db->pdoQuery("UPDATE tbl_users SET walletamount = walletamount-? WHERE userId=?", array(
                $calculatedBalance,
                $this->sessUserId,
            ));
            $this->db->insert('tbl_payment_history', array(
                'userId'        => $this->sessUserId,
                'paymentType'   => 'featured',
                'paymentStatus' => 'completed',
                'totalAmount'   => $calculatedBalance,
                'createdDate'   => date('Y-m-d H:i:s'),
                'projectId'     => $lastInsertedId,
                'featuredDays'  => $featuredDays,
            ));

        }
        //END:: deduct wallet balance if set to featured

        //START:: for skill tags
        $skillsIds = (isset($this->reqData['hidden-skillsId']) && $this->reqData['hidden-skillsId'] != null) ? $this->reqData['hidden-skillsId'] : null;
        if ($skillsIds != null && $lastInsertedId > 0) {
            $ids_array = explode(',', $skillsIds);
            $this->db->delete('tbl_project_skills', array('projectId' => $lastInsertedId));
            foreach ($ids_array as $k => $v) {
                $query = getTableValue('tbl_skills', 'id', array('skillName_' . $_SESSION['lId'] => $v));
                if ($query > 0) {
                    $this->db->insert('tbl_project_skills', array(
                        'projectId' => $lastInsertedId,
                        'skillId'   => $query,
                    ));
                } else {
                    /////////
                    $fetchRes   = $this->db->pdoQuery("SHOW COLUMNS FROM tbl_skills")->results();
                    $skillnmArr = array();
                    foreach ($fetchRes as $key => $value) {
                        if (startsWith($value["Field"], "skillName")) {
                            $skillnmArr[$value["Field"]] = $v;
                        }
                    }

                    $skillnmArr['skill_description'] = $v;
                    $skillnmArr['added_on']          = date('Y-m-d H:i:s');
                    //dump_exit($skillnmArr);
                    $last_id = $this->db->insert("tbl_skills", $skillnmArr)->lastInsertId();
                    /////////
                    $this->db->insert('tbl_project_skills', array(
                        'projectId' => $lastInsertedId,
                        'skillId'   => $last_id,
                    ));
                }
            }
        }
        //END:: for skill tags

        if ($this->dataOnly) {
            if (isset($_REQUEST['uploadedFiles'])) {
                $uploadedFilesArray = explode(',', $_REQUEST['uploadedFiles']);

                foreach ($uploadedFilesArray as $k) {
                    $this->db->insert('tbl_project_files', array(
                        'projectId'   => $lastInsertedId,
                        'fileName'    => $k,
                        'createdDate' => date('Y-m-d H:i:s'),
                    ));
                }
            }
        } else {
            //START:: for project files
            if (isset($_SESSION['uploadedFiles'])) {
                foreach ($_SESSION['uploadedFiles'] as $k => $v) {
                    $this->db->insert('tbl_project_files', array(
                        'projectId'   => $lastInsertedId,
                        'fileName'    => $v,
                        'createdDate' => date('Y-m-d H:i:s'),
                    ));
                }
                unset($_SESSION['uploadedFiles']);
            }
            //END:: for project files
        }

        if ($this->dataOnly) {
            $data['status'] = 1;
            $data['msg']    = Your_project_is_posted_successfully;
            $data['data']   = array("slug" => $insObj->slug, "projectId" => $lastInsertedId);
            return $data;
            exit;
        }

        $profileLink          = getTableValue('tbl_users', 'profileLink', array('userId' => $this->sessUserId));
        $response['status']   = 1;
        $response['msg']      = Your_project_is_posted_successfully;
        $response['redirect'] = SITE_URL . $profileLink . '/' . $insObj->slug;

        //start:: send notification to providers matching posted project skills

        $relevantUsers = $this->db->pdoQuery('SELECT u.profileLink , u.email , u.firstName , us.userId , ps.projectId FROM tbl_user_skills AS us INNER JOIN tbl_users AS u ON u.userId = us.userId INNER JOIN tbl_project_skills AS ps ON ps.skillId = us.skillId WHERE u.isActive = "y" AND u.status="a" AND u.userType ="p" AND ps.projectId = ? ', array($lastInsertedId))->results();
        foreach ($relevantUsers as $key => $value) {
            //get skills of proj
            $skills = $this->db->pdoQuery('SELECT GROUP_CONCAT( DISTINCT s.skillName_1 SEPARATOR " | ") as skillName FROM tbl_project_skills AS ps LEFT JOIN tbl_skills AS s ON ps.skillId = s.id  WHERE ps.projectId =? AND s.status = "a" group by ps.projectId ', array($lastInsertedId))->result();

            //send mail & notification to customer
            $_SESSION['sendMailTo'] = issetor($value['userId'], 0);
            $array                  = generateEmailTemplate('project_matching_skill_is_posted', array(
                'greetings'       => ucfirst($value['firstName']),
                'profileLink'     => SITE_URL . $value['profileLink'],
                'projectTitle'    => $insObj->title,
                'description'     => string_crop($insObj->description),
                'projectSkills'   => $skills['skillName'],
                'budget'          => number_format($insObj->budget,2) . ' ' . DEFAULT_CURRENCY_CODE,
                'loginLink'       => SITE_LOGIN,
                'projectLink'     => SITE_URL . $profileLink . '/' . $insObj->slug,
                'biddingDeadline' => date(PHP_DATE_FORMAT, strtotime($insObj->biddingDeadline)),
            ));
            //echo '<br/>'.$array['message'];exit;
            sendEmailAddress($value['email'], $array['subject'], $array['message']);

            //send notification
            insert_user_notification($typeId = 2, $from = $this->sessUserId, $to = $value['userId'], $referenceId = $lastInsertedId, array('%projectName%' => $insObj->title));

        }
        //end:: send notification to providers matching posted project skills

        echo json_encode($response);
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
                'script_url'     => SITE_PROJECT_POST . "?action=method&method=uploadFile", //path of file where object of UploadHandler.php is made
                'upload_dir'     => DIR_UPD . "project/",
                'upload_url'     => SITE_UPD . "project/",
                'type'           => 'project_files',
                'print_response' => false,
                'max_file_size'  => MAX_FILE_SIZE);
        } else {
            $options = array(
                'script_url'     => SITE_PROJECT_POST . "?action=method&method=uploadFile", //path of file where object of UploadHandler.php is made
                'upload_dir'     => DIR_UPD . "project/",
                'upload_url'     => SITE_UPD . "project/",
                'type'           => 'project_files',
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

        if ($this->dataOnly) {
            return $arr['files'];
        }

        exit;
        //END:: to get names of uploaded files
    }

    public function get_sub_cats($subcatId = 0, $parentId = 0)
    {
        $parentId = (isset($this->reqData['parentId']) && $this->reqData['parentId'] != null) ? $this->reqData['parentId'] : $parentId;
        //dump_exit($parentId);
        if ($subcatId == 0 && $parentId == 0) {
            return null;
        } else if ($parentId == 0) {
            $selCats = $this->db->select('tbl_categories', array(
                'id',
                'cateName_'.$_SESSION["lId"].' as cateName ',
            ), array(
                'isActive'    => 'y',
                'parentId > ' => 0,
            ), 'order by cateName_' . $_SESSION["lId"] . ' asc');
        } else {
            $selCats = $this->db->select('tbl_categories', array(
                'id',
                'cateName_'.$_SESSION["lId"].' as cateName ',
            ), array(
                'isActive' => 'y',
                'parentId' => $parentId,
            ), 'order by cateName_' . $_SESSION["lId"] . ' asc');

        }

        if ($this->dataOnly) {
            return $selCats->results();
        } else {
            $selCats = $selCats->results();
        }

        $html = null;
        foreach ($selCats as $k) {
            $replace = array(
                "%VALUE%"    => $k['id'],
                "%SELECTED%" => ($subcatId == $k['id']) ? 'selected' : null,
                "%LABEL%"    => $k['cateName'],
            );
            $html .= get_view(DIR_TMPL . $this->module . "/" . "option-nct.tpl.php", $replace);
        }
        return $html;
    }

    public function get_cats($catId = 0)
    {
        $selCats = $this->db->select('tbl_categories', array(
            'id',
            'cateName_'.$_SESSION["lId"].' as cateName ',
        ), array(
            'isActive' => 'y',
            'parentId' => 0,
        ), 'order by cateName_' . $_SESSION["lId"] . ' asc');

        if ($this->dataOnly) {
            return $selCats->results();
        } else {
            $selCats = $selCats->results();
        }

        $html = null;
        foreach ($selCats as $k) {
            $replace = array(
                "%VALUE%"    => $k['id'],
                "%SELECTED%" => ($catId == $k['id']) ? 'selected' : null,
                "%LABEL%"    => $k['cateName'],
            );
            $html .= get_view(DIR_TMPL . $this->module . "/" . "option-nct.tpl.php", $replace);
        }
        return $html;
    }

    public function allSkills()
    {
        global $db;
        $arr    = array();
        $result = $db->select("tbl_skills", array(
            "id",
            "skillName_" . $_SESSION['lId'].' as skillName ',
        ), array("status" => 'a'));

        if ($this->dataOnly) {
            return $result->results();
        } else {
            $result = $result->results();
        }

        foreach ($result as $k => $v) {
            $arr[$v["id"]] = $v["skillName"];
        }
        return json_encode($arr);
    }

    public function getPageContent()
    {
        global $js_variables;
        $allSkills    = $this->allSkills();
        $js_variables = "var prefilledValues = [], allSkills= $allSkills, SITE_PLUGIN='" . SITE_PLUGIN . "'";

        $replace = array('%cats%' => $this->get_cats(), '%tokenValue%' => setFormToken());
        return get_view(DIR_TMPL . $this->module . "/" . $this->module . ".tpl.php", $replace);
    }

}
