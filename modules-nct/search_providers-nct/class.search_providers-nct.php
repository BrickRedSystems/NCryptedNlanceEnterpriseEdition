<?php
class Searchproviders
{
    public function __construct($module = "", $id = 0, $reqData = array())
    {
        global $js_variables;
        foreach ($GLOBALS as $key => $values) {
            $this->$key = $values;
        }
        $this->module     = $module;
        $this->id         = $id;
        $this->reqData    = $reqData;
        $this->sessUserId = (isset($reqData['userId']) && $reqData['userId'] > 0) ? $reqData['userId'] : $this->sessUserId;
        $this->dataOnly   = (isset($this->reqData['dataOnly']) && $this->reqData['dataOnly'] == true) ? true : false;

        $this->categoryId = (isset($this->reqData['category']) && trim($this->reqData['category']) != "") ? $this->reqData['category'] : 0;

        $this->subcategoryId = (isset($this->reqData['subcategory']) && trim($this->reqData['subcategory']) != "") ? $this->reqData['subcategory'] : "null";

        $this->skillIds = (isset($this->reqData['skills']) && trim($this->reqData['skills']) != "") ? $this->reqData['skills'] : "null";
        $this->level    = (isset($this->reqData['level']) && trim($this->reqData['level']) != "") ? $this->reqData['level'] : "null";
        $this->keyword  = (isset($this->reqData['keyword']) && trim($this->reqData['keyword']) != "") ? $this->reqData['keyword'] : "null";
        $this->sort_by  = (isset($this->reqData['sort_by']) && trim($this->reqData['sort_by']) != "") ? $this->reqData['sort_by'] : "null";
        $js_variables   = "var site_search_providers ='" . SITE_SEARCH_PROVIDERS . "'";

        $this->keyword = str_replace(array('_', '%', "'"), array('\_', '\%', "\'"), $this->keyword);
        if($this->dataOnly){
            $_SESSION['lId'] = (isset($this->reqData['lId']) && $this->reqData['lId'] >0)?$this->reqData['lId']:1;  
            setLang();  
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

    public function getSkills($userId = 0)
    {
        if ($userId <= 0) {
            return null;
        }

        $skills = $this->db->pdoQuery('SELECT DISTINCT us.skillId,s.slug, s.skillName_'.$_SESSION["lId"].' AS skillName FROM tbl_user_skills AS us LEFT JOIN tbl_skills AS s ON us.skillId = s.id  WHERE us.userId =? AND s.status = "a"', array($userId))->results();
        if ($this->dataOnly) {
            return $skills;
        }
        $html = null;
        if (!empty($skills)) {
            foreach ($skills as $k => $v) {
                extract($v);
                $html .= get_view(DIR_TMPL . $this->module . "/skills-nct.tpl.php", array(
                    '%link%' => SITE_SEARCH_SKILLS_PROVIDERS . $slug,
                    '%name%' => ucwords($skillName),
                ));
            }
        }

        return sanitize_output($html);
    }

    public function getCatSubcat($data = array())
    {
        extract($data);
        $html     = null;
        $cateLink = ($catId > 0) ? '<a href="' . SITE_SEARCH_CATEGORY_PROVIDERS . $cslug . '">' . ucwords($cateName) . '</a>' : null;

        $subCateLink = ($subcatId > 0) ? '<a href="' . SITE_SEARCH_SUB_CATEGORY_PROVIDERS . $scslug . '">' . ucwords($subCateName) . '</a>' : null;

        $html = $cateLink . ($subCateLink != null ? ' | ' : '') . $subCateLink;

        return sanitize_output($html);
    }

    //TODO:: make cron to calculate experience of each user
    public function provider_rows($count = false)
    {
        global $db;
        $html      = $qry      = $condition      = $sort_by      = $group_by      = null;
        $qry       = "SELECT @points_veri:= (CASE WHEN u.facebook_verify = '1' THEN 1 ELSE 0 END) + (CASE WHEN u.google_verify = '1' THEN 1 ELSE 0 END) + (CASE WHEN u.linkedin_verify = '1' THEN 1 ELSE 0 END) AS veriPoints, @points_comp:= IFNULL(tbl_completed.total, 0) AS total_completed, (@points_veri+@points_comp)AS totPoints, GROUP_CONCAT( DISTINCT CONCAT(ts.skillName, ',') SEPARATOR ', ' ) AS skills, u.`catId`, ca.slug AS cslug, ca.`cateName_".$_SESSION['lId']."` AS cateName, u.`subcatId`,sc.slug AS scslug,sc.`cateName_".$_SESSION['lId']."` AS subCateName, IFNULL(tbl_ongoing.total,0) AS total_ongoing, IFNULL(tbl_completed.total,0) AS total_completed, IFNULL(AVG(f.averageRating), 0) AS average, COUNT(f.id) AS totalReviews, CONCAT_WS( ', ', tc.`cityName`, s.`stateName`, c.`countryName` ) AS userlocation, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName, @ruserId := u.userId, u.* FROM tbl_users AS u LEFT JOIN tbl_country AS c ON u.`countryCode` = c.`CountryId` LEFT JOIN tbl_state AS s ON u.`state` = s.`StateID` LEFT JOIN `tbl_city` AS tc ON u.`city` = tc.`CityId` LEFT JOIN tbl_feedbacks AS f ON u.userId = f.`userTo` LEFT JOIN (SELECT COUNT('id') AS total, providerId FROM tbl_projects WHERE jobStatus = 'progress' AND isActive = 'y' GROUP BY providerId) AS tbl_ongoing ON tbl_ongoing.providerId = u.userId LEFT JOIN (SELECT COUNT('id') AS total, providerId FROM tbl_projects WHERE jobStatus = 'completed' AND isActive = 'y' GROUP BY providerId) AS tbl_completed ON tbl_completed.providerId = u.userId  LEFT JOIN tbl_user_skills AS us ON u.userId = us.`userId` LEFT JOIN tbl_skills AS ts ON us.`skillId` = ts.`id` AND ts.`status`='a' LEFT JOIN tbl_categories AS ca ON u.catId = ca.id AND ca.`isactive` = 'y' LEFT OUTER JOIN tbl_categories AS sc ON u.subcatId = sc.id AND sc.`isactive` = 'y' WHERE u.`userType` = 'p' AND u.`isActive` = 'y' AND u.`status` = 'a' AND u.userId NOT IN ('" . $this->sessUserId . "')";
        $qryParams = array();

        //START:: filters
        if ($this->categoryId != null && $this->categoryId != "0") {
            $catIdFrmDB = $db->pdoQuery('select id from tbl_categories where slug = ?', array($this->categoryId))->result();
            $condition .= ' AND u.catId = ' . $catIdFrmDB['id'];
        }
        if ($this->subcategoryId != "null") {
            $subcatsarr   = explode(",", $this->subcategoryId);
            $subcatstring = implode('","', $subcatsarr);

            $subCatIdFrmDB = $db->pdoQuery('SELECT DISTINCT id FROM `tbl_categories` WHERE slug IN ("' . $subcatstring . '")')->results();

            $finalsubcatstring = null;
            $numItems          = count($subCatIdFrmDB);
            $i                 = 0;
            foreach ($subCatIdFrmDB as $key => $value) {
                if (++$i === $numItems) {
                    $finalsubcatstring .= $value['id'];
                } else {
                    $finalsubcatstring .= $value['id'] . ',';
                }
            }

            $condition .= ' AND u.subcatId IN (' . $finalsubcatstring . ') ';
        }
        if ($this->skillIds != "null") {
            $skillarr    = explode(",", $this->skillIds);
            $skillstring = implode('","', $skillarr);

            $skillIdsFrmDB = $db->pdoQuery('SELECT DISTINCT id FROM `tbl_skills` WHERE slug IN ("' . $skillstring . '")')->results();

            $finalSkillString = null;
            $numItems         = count($skillIdsFrmDB);
            $i                = 0;
            foreach ($skillIdsFrmDB as $key => $value) {
                if (++$i === $numItems) {
                    $finalSkillString .= $value['id'];
                } else {
                    $finalSkillString .= $value['id'] . ',';
                }
            }
            $condition .= ' AND us.`skillId` IN (' . $finalSkillString . ') ';
        }
        if ($this->level != "null") {
            $elements = explode(',', $this->level);
            $condition .= ' AND u.`experience` IN ("' . implode('", "', $elements) . '") ';
        }        
        if ($this->keyword != "null") {
            $condition .= ' AND  (u.aboutMe LIKE "%' . $this->keyword . '%" OR concat_ws(" ",u.firstName, u.lastName) LIKE "%' . $this->keyword . '%" OR concat_ws(" ",tc.`cityName`, s.`stateName`, c.`countryName`)  LIKE "%' . $this->keyword . '%") ';
        }
        //END:: filters

        //ORDERBY
        if ($this->sort_by != "null" && $this->sort_by == "newest_first") {
            $sort_by = ' ORDER BY u.`userId` DESC ';
        } else {
            //$sort_by = ' ORDER BY average DESC ';
            $sort_by = ' ORDER BY (@points_veri+@points_comp) DESC, u.`userId` DESC ';
        }
        //group by
        $group_by = ' GROUP BY u.`userId`  ';

        ////////////////////////////
        // put pagination
        $limit_cond = null;
        $q          = $this->db->pdoQuery($qry . $condition . $group_by . $sort_by, $qryParams);
        $this->fb->info('query', $qry . $condition . $group_by . $sort_by, $qryParams);
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
            $qry        = $this->db->pdoQuery($qry . $condition . $group_by . $sort_by . $limit_cond, $qryParams);

        }
        ////////////////////////////

        if ($this->dataOnly) {
            $datatosend = array(
                'page'          => issetor($page, 0),
                'numPages'      => issetor($pager->numPages, 0),
                'total_records' => issetor($totalRows, 0),
                'data'          => (is_object($qry) && $qry->affectedRows() > 0) ? $qry->results() : array(),
            );
            return $datatosend;
        }

        if (is_object($qry) && $qry->affectedRows() > 0) {
            $results = $qry->results();

            $this->fb->info('ds', $results);
            foreach ($results as $k => $v) {

                $reviews = $this->get_review($v['userId']);
                $replace = array(
                    '%profilePhoto%'    => tim_thumb_image($v['profilePhoto'], 'profile', 75, 75),
                    '%profileLink%'     => SITE_URL . $v['profileLink'],
                    '%fullName%'        => ucwords(filtering($v['fullName'])),
                    '%desc%'            => filtering(String_crop($v['aboutMe'], 180)),
                    '%averageRatings%'  => number_format($reviews[0], 1),
                    '%totalReviews%'    => $reviews[1],
                    '%total_completed%' => $v['total_completed'],
                    '%total_ongoing%'   => $v['total_ongoing'],
                    '%createdDate%'     => date('d-M-Y', strtotime($v['createdDate'])),
                    '%like_icon%'       => is_my_fav($v['userId'], 'user') ? 'fa-heart' : 'fa-heart-o',
                    '%location%'        => (trim($v['userlocation']) == '') ? Not_Available : $v['userlocation'],
                    '%userId%'          => $v['userId'],
                    '%hideInviteBtn%'   => ($this->sessUserType != 'c') ? 'hide' : null,
                    '%skills%'          => $this->getSkills($v['userId']),
                    '%cat_subcat%'      => $this->getCatSubcat($v),
                );

                if ($this->dataOnly) {
                    $results[$k]['averageRatings'] = number_format($reviews[0], 1);
                    $results[$k]['totalReviews']   = $reviews[1];
                    $results[$k]['skills']         = $this->getSkills($v['userId']);
                    $results[$k]['isFav']          = is_my_fav($v['userId'], 'user') ? true : false;
                } else {
                    if ($this->sessUserId != $v['userId'] && $this->sessUserType == 'c') {
                        $html .= get_view(DIR_TMPL . $this->module . "/provider_row_with_heart-nct.tpl.php", $replace);
                    } else {
                        $html .= get_view(DIR_TMPL . $this->module . "/provider_row-nct.tpl.php", $replace);
                    }
                }

            }
        } else {
            if ($this->dataOnly) {
                $datatosend['data'] = array();
                return $datatosend;
            } else {
                $html .= get_view(DIR_TMPL . $this->module . "/no_provider_row-nct.tpl.php", array('%status%' => 'open'));
            }
        }

        return sanitize_output($html);
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

    public function get_review($userId = null)
    {
        $results = $this->db->pdoQuery("SELECT AVG(f.averageRating) AS average, count(f.id) AS total FROM tbl_feedbacks AS f LEFT JOIN tbl_users AS u ON f.`userfrom` = u.userid WHERE f.userto = ? AND u.isactive = 'y' ", array('userTo' => $userId))->result();

        return array(
            $results['average'],
            $results['total'],
        );
    }

    public function left()
    {

        $levels = explode(',', $this->level);
        if ($this->level == "null") {
            $check_entry_level = $check_moderate = $check_expert = 'checked';
        }
        $check_entry_level = (!in_array('entry level', $levels)) ? null : 'checked';
        $check_moderate    = (!in_array('moderate', $levels)) ? null : 'checked';
        $check_expert      = (!in_array('expert', $levels)) ? null : 'checked';

        $replace = array(
            '%projectCategories%'    => $this->projectCategories(),
            '%projectSubCategories%' => $this->projectSubCategories(),
            '%skill_options%'        => $this->skill_options(),

            '%check_entry_level%'    => $check_entry_level,
            '%check_moderate%'       => $check_moderate,
            '%check_expert%'         => $check_expert,
        );
        return get_view(DIR_TMPL . $this->module . "/left-nct.tpl.php", $replace);
    }

    public function getPageContent()
    {
        $rel = issetor($this->reqData['rel'], 'false');

        //dump_exit($this->reqData);
        if ($rel == "true") {
            echo json_encode(array(
                'code'    => 200,
                'content' => $this->provider_rows(),
                'title'   => Search_Providers.' - ' . SITE_NM,
            ));
            exit;
        }

        $replace = array(
            '%left%'             => $this->left(),
            '%rows%'             => $this->provider_rows(),

            '%select_relevance%' => ($this->sort_by != "newest_first") ? 'selected' : null,
            '%select_newest%'    => ($this->sort_by == "newest_first") ? 'selected' : null,

            '%keyword%'          => ($this->keyword != "null") ? filtering($this->keyword) : '',
        );
        return get_view(DIR_TMPL . $this->module . "/" . $this->module . ".tpl.php", $replace);
    }

    public function projectCategories()
    {
        $page_option = '';
        $qrySel      = $this->db->pdoQuery("SELECT id,cateName_{$_SESSION['lId']} AS cateName,slug FROM tbl_categories where isActive='y' AND parentId=0 ORDER BY cateName ASC");

        if ($this->dataOnly) {
            return $qrySel->results();
        } else {
            $qrySel = $qrySel->results();
        }

        $page_option .= get_view(DIR_TMPL . $this->module . "/select_option-nct.tpl.php", array(
            "%VALUE%"         => 0,
            "%SELECTED%"      => null,
            "%DISPLAY_VALUE%" => All_categories,
        ));
        foreach ($qrySel as $fetchRes) {
            $selected = ($this->categoryId === $fetchRes['slug']) ? "selected" : "";
            $replace  = array(
                "%VALUE%"         => $fetchRes['slug'],
                "%SELECTED%"      => $selected,
                "%DISPLAY_VALUE%" => ucwords($fetchRes['cateName']),
            );
            $page_option .= get_view(DIR_TMPL . $this->module . "/select_option-nct.tpl.php", $replace);
        }
        return sanitize_output($page_option);
    }

    public function projectSubCategories($categoryId = null)
    {
        $page_option = '';
        $categoryId  = issetor($this->categoryId, $categoryId);

        if (isset($categoryId) && $categoryId != null && $this->categoryId != "0") {
            $qrySel = $this->db->pdoQuery("SELECT id,slug,cateName_{$_SESSION['lId']} AS cateName FROM tbl_categories where isActive='y' AND parentId = (select id from tbl_categories where slug = ? LIMIT 1) ORDER BY cateName ASC", array($categoryId));
        } else {
            $qrySel = $this->db->pdoQuery("SELECT id,slug,cateName_{$_SESSION['lId']} AS cateName FROM tbl_categories where isActive='y' AND parentId >0 ORDER BY cateName ASC");
        }
        if ($this->dataOnly) {
            return $qrySel->results();
        } else {
            $qrySel = $qrySel->results();
        }

        foreach ($qrySel as $fetchRes) {
            $selected = ($this->subcategoryId === $fetchRes['slug']) ? "selected" : "";
            $replace  = array(
                "%VALUE%"         => $fetchRes['slug'],
                "%SELECTED%"      => $selected,
                "%DISPLAY_VALUE%" => ucwords($fetchRes['cateName']),
            );
            $page_option .= get_view(DIR_TMPL . $this->module . "/select_option-nct.tpl.php", $replace);
        }
        return sanitize_output($page_option);
    }

    public function skill_options()
    {
        $content      = '';
        $skillsResult = explode(',', $this->skillIds);
        $qrySelskills = $this->db->pdoQuery("SELECT id, slug, skillName_{$_SESSION['lId']} AS skillName FROM tbl_skills where status='a' ORDER BY skillName ASC");
        if ($this->dataOnly) {
            return $qrySelskills->results();
        } else {
            $qrySelskills = $qrySelskills->results();
        }

        foreach ($qrySelskills as $selResult) {
            $selected = (in_array($selResult['slug'], $skillsResult)) ? "selected" : "";
            $replace  = array(
                "%VALUE%"         => $selResult['slug'],
                "%SELECTED%"      => $selected,
                "%DISPLAY_VALUE%" => ucwords($selResult['skillName']),
            );
            $content .= get_view(DIR_TMPL . $this->module . "/select_option-nct.tpl.php", $replace);
        }
        return sanitize_output($content);
    }

    public function projData($id = null)
    {
        if ($id > 0) {
            $result = $this->db->pdoQuery('select p.id,p.title, p.slug, u.profileLink from tbl_projects as p left join tbl_users as u on p.userId=u.userId where p.id =?',array($id))->result();
            return $result;
        }
    }

}
