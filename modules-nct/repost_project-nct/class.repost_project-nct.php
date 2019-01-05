<?php
class RepostProject
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
        $this->proj  = $this->projectData($reqData['slug']);
        if($this->dataOnly){
            $_SESSION['lId'] = (isset($this->reqData['lId']) && $this->reqData['lId'] >0)?$this->reqData['lId']:1;  
            setLang();  
        }
    }

    public function getFile()
    {
        global $fb;
        extract($this->reqData);
        require_once '../../themes-nct/javascript-nct/plugins-nct/blueimp/server/php/UploadHandler.php';
        $options = array(
            'script_url'     => SITE_PROJECT_EDIT . "?slug=" . $this->proj['slug'] . "&action=method&method=getFile", //path of file where object of UploadHandler.php is made
            'upload_dir'     => DIR_UPD . "project/",
            'upload_url'     => SITE_UPD . "project/",
            'type'           => 'project_files',
            'selected_files' => $this->proj['files_names'],
            'max_file_size'  => MAX_FILE_SIZE,
        );

        $upload_handler = new UploadHandler($options);
        exit;
    }

    public function repostProject()
    {
        global $fb;
        extract($this->reqData);

///////////////////
        if (!isset($dataOnly) || !$dataOnly) {
            if (!checkFormToken($token)) {
                $response['status']   = 0;
                $response['msg']      = Security_token_for_this_action_is_invalid_Please_refresh_and_try_again;
                $response['newToken'] = setFormToken();
                echo json_encode($response);
                exit;
            }}
///////////////////
        /*
         * process for reposting
         * 1. change status
         * 2. remove all previous bids
         * 3. remove from featured
         * 4. remove past milestones
         * 5. move all files of this project to new filder
         * 6.
         */
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

        $insObj                  = new stdClass();
        $insObj->userId          = $this->sessUserId;
        $insObj->providerId      = 0;
        $insObj->categoryId      = $categoryId;
        $insObj->subcategoryId   = $subcategoryId;
        $insObj->title           = $title;
        $insObj->slug            = $this->reqData['slug'] . generateRandString(4);
        $insObj->description     = $description;
        $insObj->budget          = $budget;
        $insObj->price           = 0;
        $insObj->duration        = $duration;
        $insObj->biddingDeadline = date('Y-m-d H:i:s', strtotime($biddingDeadline));
        //$insObj->hideFromSearch = null;
        $insObj->jobStatus    = 'open'; //'open','accepted','milestone_accepted','progress','completed','dispute','closed','reopened','expired'
        $insObj->isFeatured   = $isFeatured;
        $insObj->featuredDays = ($featuredDays > 0 && $isFeatured == 'y') ?$featuredDays:0;
        $insObj->createdDate  = date('Y-m-d H:i:s');
        $insObj->isActive     = 'y';
        $insObj->featuredExpiryDate = ($featuredDays > 0 && $isFeatured == 'y') ? date('Y-m-d H:i:s',strtotime("+ $featuredDays day")) : null;

        //$insObj->experienceWanted = null; 'entry level','moderate','expert'

        //insert the project
        $lastInsertedId = 0;
        $lastInsertedId = $this->db->insert($this->table, (array) $insObj)->lastInsertId();

        //halt the execution if project is not inserted
        if ($lastInsertedId < 1) {
            $response['status'] = 0;
            $response['msg']    = We_encounterd_some_issues_while_reposting_this_project_Please_try_again_later;

            if ($this->dataOnly) {
                return $response;
                exit;
            } else {
                echo json_encode($response);
                exit;
            }
        }

        //START:: deduct wallet balance if set to featured
        $calculatedBalance = $featuredDays * FEATURED_PROJ_PRICE;
        $this->db->pdoQuery("UPDATE tbl_users SET walletamount = walletamount-? WHERE userId=?", array(
            $calculatedBalance,
            $this->sessUserId,
        ));
        //END:: deduct wallet balance if set to featured

        //START:: for skill tags
        //first of all copy all skills of past project and add it to new one.
        $this->db->pdoQuery('INSERT INTO tbl_project_skills (projectId, skillId) SELECT ?, skillId FROM tbl_project_skills WHERE projectId = ? ', array($lastInsertedId, $this->proj['id']));
        $skillsIds = (isset($this->reqData['hidden-skillsId']) && $this->reqData['hidden-skillsId'] != null) ? $this->reqData['hidden-skillsId'] : null;
        if ($skillsIds != null) {
            $ids_array = explode(',', $skillsIds);
            $this->db->delete('tbl_project_skills', array('projectId' => $lastInsertedId));
            foreach ($ids_array as $k => $v) {
                $query = getTableValue('tbl_skills', 'id', array('skillName_'.$_SESSION['lId'] => $v));
                if ($query > 0) {
                    $this->db->insert('tbl_project_skills', array(
                        'projectId' => $this->proj['id'],
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
                        'projectId' => $this->proj['id'],
                        'skillId'   => $last_id,
                    ));
                }
            }
        }
        //END:: for skill tags
        //$fb->log($_SESSION,'session');
        //$fb->log($lastInsertedId,'insertedId');

        //START:: for project files
        /*
         * once a new project is created copy all files from tbl and insert as new one then delete previous record from table
         */

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
            $this->db->pdoQuery('INSERT INTO tbl_project_files (projectId, fileName, createdDate) SELECT ?, fileName, createdDate FROM tbl_project_files WHERE projectId = ? ', array($lastInsertedId, $this->proj['id']));

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

        //START:: Delete the project altogather
        $this->db->delete('tbl_projects', array('id' => $this->proj['id']));
        //END:: Delete the project altogather

        $response['status']   = 1;
        $response['msg']      = Your_project_is_reposted_successfully;
        $profLink             = getTableValue('tbl_users', 'profileLink', array('userId' => $this->proj['userId']));
        $response['redirect'] = SITE_URL . $profLink . '/' . $insObj->slug;

        if ($this->dataOnly) {
            $response['data'] = array("slug" => $insObj->slug, "projectId" => $lastInsertedId);
            return $response;
            exit;
        } else {
            echo json_encode($response);
            exit;
        }
    }

    public function uploadFile()
    {
        global $fb;
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

        //START:: to get names of uploaded file

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
            ), 'order by cateName_'.$_SESSION["lId"].' asc');
        } else {
            $selCats = $this->db->select('tbl_categories', array(
                'id',
                'cateName_'.$_SESSION["lId"].' as cateName ',
            ), array(
                'isActive' => 'y',
                'parentId' => $parentId,
            ), 'order by cateName_'.$_SESSION["lId"].' asc');

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
        ), 'order by cateName_'.$_SESSION["lId"].' asc');

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

        extract($this->proj);

        if ($jobStatus != 'expired') {
            $msgType = $_SESSION['msgType'] = disMessage(array(
                "type" => "err",
                "var"  => This_project_can_not_be_reposted
            ));
            redirectPage(SITE_URL);
        }

        $allSkills    = $this->allSkills();
        $js_variables = "var prefilledValues = [$skills], allSkills= $allSkills, SITE_PLUGIN='" . SITE_PLUGIN . "'";

        $total_price = 0;
        $replace     = array(
            '%title%'           => $title,
            '%cats%'            => $this->get_cats($categoryId),
            '%subcats%'         => $this->get_sub_cats($subcategoryId, $categoryId),
            '%description%'     => filtering(nl2br($description)),
            '%budget%'          => $budget,
            '%duration%'        => $duration,
            '%biddingDeadline%' => date('Y/m/d', strtotime($biddingDeadline)),
            '%isFeatured%'      => ($isFeatured == "y") ? 'checked="checked"' : null,
            '%featuredDays%'    => ($isFeatured == "y") ? $featuredDays : null,
            '%total_price%'     => $total_price,
            '%tokenValue%'      => setFormToken(),
        );
        return get_view(DIR_TMPL . $this->module . "/" . $this->module . ".tpl.php", $replace);
    }

    public function projectData($slug)
    {

        $doesExist = getTableValue($this->table, 'id', array(
            'slug'     => $slug,
            'userId'   => $this->sessUserId,
            'isActive' => 'y',
        ));
        if (isset($doesExist) && $doesExist > 0) {
            $arr = array();

            $arr = $this->db->pdoQuery("SELECT p.*, GROUP_CONCAT( DISTINCT CONCAT('\"' ,s.skillName_".$_SESSION['lId'].",'\"') SEPARATOR ', ') AS skills, CONCAT_WS(' | ', c.cateName_".$_SESSION["lId"].", sc.cateName_".$_SESSION["lId"].") AS cat, CONCAT_WS(' | ', c.slug, sc.slug) AS catSlugs FROM tbl_projects AS p LEFT JOIN tbl_project_skills AS ps ON p.id = ps.`projectId` LEFT JOIN tbl_skills AS s ON ps.`skillId` = s.`id` LEFT JOIN tbl_categories AS c ON p.categoryid = c.id AND c.`isactive` = 'y' LEFT OUTER JOIN tbl_categories AS sc ON p.subcategoryid = sc.id AND sc.`isactive` = 'y'  WHERE p.slug = ? AND s.status = 'a'  ", array($slug))->result();

            //get bid placed by all providers
            $arr['bids'] = $this->db->pdoQuery(' SELECT IFNULL(AVG(f.averageRating),0) AS average, COUNT(f.id) AS totalReviews, b.id, u.profileLink, u.profilePhoto, CONCAT_WS(" ", u.firstName, u.lastName) AS fullName , b.* FROM tbl_bids AS b LEFT JOIN tbl_users AS u ON b.userId = u.userId LEFT JOIN tbl_feedbacks AS f ON b.userId = f.`userto` LEFT JOIN tbl_users AS fromu ON f.userFrom = fromu.userId WHERE b.`projectId` = ? AND b.`isActive` = "y" AND u.`isActive` = "y" AND fromu.`isActive` = "y" GROUP BY b.userId ', array($arr['id']))->affectedRows();

            $arr['invited'] = getTableValue('tbl_project_invitation', 'count("id")', array(
                'projectId' => $arr['id'],
                'isActive'  => 'y',
            ));
            $arr['files'] = $this->db->select('tbl_project_files', array('fileName'), array(
                'projectId' => $arr['id'],
                'isActive'  => 'y',
            ))->results();

            $arr['files_names'] = $this->db->pdoQuery('SELECT GROUP_CONCAT(filename) as files FROM tbl_project_files WHERE projectId=? AND isActive=?', array(
                $arr['id'],
                'y',
            ))->result();
            $arr['files_names'] = explode(',', $arr['files_names']['files']);

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

}
