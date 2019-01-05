<?php
class Searchprojects
{
    public function __construct($module = "", $id = 0, $reqData = array())
    {
        global $js_variables;
        foreach ($GLOBALS as $key => $values) {
            $this->$key = $values;
        }
        $this->module        = $module;
        $this->id            = $id;
        $this->reqData       = $reqData;
        $this->sessUserId    = (isset($reqData['userId']) && $reqData['userId'] > 0) ? $reqData['userId'] : $this->sessUserId;
        $this->dataOnly      = (isset($this->reqData['dataOnly']) && $this->reqData['dataOnly'] == true) ? true : false;
        $this->categoryId    = (isset($this->reqData['category']) && trim($this->reqData['category']) != "") ? $this->reqData['category'] : 0;
        $this->subcategoryId = (isset($this->reqData['subcategory']) && trim($this->reqData['subcategory']) != "") ? $this->reqData['subcategory'] : null;
        $this->skillIds      = (isset($this->reqData['skills']) && trim($this->reqData['skills']) != "") ? $this->reqData['skills'] : null;
        $this->level         = (isset($this->reqData['level']) && trim($this->reqData['level']) != "") ? $this->reqData['level'] : null;
        $this->budget_from   = (isset($this->reqData['budget_from']) && trim($this->reqData['budget_from']) != "") ? $this->reqData['budget_from'] : null;
        $this->budget_to     = (isset($this->reqData['budget_to']) && trim($this->reqData['budget_to']) != "") ? $this->reqData['budget_to'] : null;
        $this->keyword       = (isset($this->reqData['keyword']) && trim($this->reqData['keyword']) != "") ? $this->reqData['keyword'] : null;
        $this->isFeatured    = (isset($this->reqData['isFeatured']) && trim($this->reqData['isFeatured']) != "") ? $this->reqData['isFeatured'] : null;
        $this->sort_by       = (isset($this->reqData['sort_by']) && trim($this->reqData['sort_by']) != "") ? $this->reqData['sort_by'] : null;

        $js_variables  = "var site_search_projects ='" . SITE_SEARCH_PROJECTS . "'";
        $this->keyword = str_replace(array('_', '%', "'"), array('\_', '\%', "\'"), $this->keyword);
        if($this->dataOnly){
            $_SESSION['lId'] = (isset($this->reqData['lId']) && $this->reqData['lId'] >0)?$this->reqData['lId']:1;  
            setLang();  
        }
    }

    public function getSkills($projectId = 0)
    {
        if ($projectId <= 0) {
            return null;
        }

        $skills = $this->db->pdoQuery('SELECT DISTINCT ps.skillId,s.slug, s.skillName_'.$_SESSION["lId"].' AS skillName FROM tbl_project_skills AS ps LEFT JOIN tbl_skills AS s ON ps.skillId = s.id  WHERE ps.projectId =? AND s.status = "a"', array($projectId))->results();

        if ($this->dataOnly) {
            return $skills;
        }

        $html = null;
        if (!empty($skills)) {
            foreach ($skills as $k => $v) {
                extract($v);
                $html .= get_view(DIR_TMPL . $this->module . "/skills-nct.tpl.php", array(
                    '%link%' => SITE_SEARCH_SKILLS_PROJECTS . $slug,
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
        $cateLink = ($categoryId > 0) ? '<a href="' . SITE_SEARCH_CATEGORY_PROJECTS . $cslug . '">' . ucwords($cateName) . '</a>' : null;

        $subCateLink = ($subcategoryId > 0) ? '<a href="' . SITE_SEARCH_SUB_CATEGORY_PROJECTS . $scslug . '">' . ucwords($subCateName) . '</a>' : null;

        $html = $cateLink . ($subCateLink != null ? ' | ' : '') . $subCateLink;

        return sanitize_output($html);
    }

    //TODO:: make cron to calculate experience of each user
    public function project_rows($count = false)
    {
        global $db, $fb;
        $html = $qry = $condition = $sort_by = $group_by = null;

        $nowTime = date('Y-m-d H:i:s');
        //check if provider has no skills or is logged out, in this case query for points won't work
        if(!$this->sessUserId){
            $qry = " SELECT p.userId AS pUser, p.id AS id, p.biddingDeadline, COUNT(ps.projectId) AS points, GROUP_CONCAT(DISTINCT s.skillName_{$_SESSION['lId']}) AS skills, p.title, p.`categoryId`, c.slug AS cslug, c.`cateName_{$_SESSION['lId']}` AS cateName, p.`subcategoryId`, sc.slug AS scslug, sc.`cateName_{$_SESSION['lId']}` AS subCateName, IFNULL(AVG(b.`price`), 0) AS averageBid, IFNULL(AVG(b.`duration`), 0) AS averageETA, COUNT(b.id) AS bids, p.userId, p.createdDate, p.slug, p.`description`, p.budget AS budget, p.`isFeatured`, p.`slug`, u.`profileLink`, u.`profilePhoto`, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName FROM tbl_projects AS p LEFT JOIN tbl_users AS u ON p.userId = u.userId LEFT JOIN tbl_bids AS b ON p.id = b.`projectId` AND b.`isFinal` = 'y' AND b.`isNulled` = 'n' LEFT JOIN tbl_categories AS c ON p.categoryid = c.id AND c.`isactive` = 'y' LEFT JOIN tbl_categories AS sc ON p.subcategoryid = sc.id AND sc.`isactive` = 'y' LEFT JOIN tbl_user_skills us ON us.userId = p.userId LEFT JOIN tbl_project_skills ps ON p.id = ps.projectId LEFT JOIN tbl_skills AS s ON ps.`skillId` = s.`id` AND s.`status` = 'a' WHERE p.`isActive` = 'y' AND p.`biddingDeadline` > ? AND u.`isActive` = 'y' AND ( p.`jobStatus` = 'open' OR p.`jobStatus` = 'reopened' ) ";
        }else{
            if($this->checkProviderHasSkills()){
                $qry       = " SELECT main.userId AS mainUser, main.projectId AS id, p.biddingDeadline,COUNT(ps.projectId) AS points, GROUP_CONCAT( DISTINCT s.skillName_{$_SESSION['lId']}) AS skills, p.title, p.`categoryId`,c.slug AS cslug,c.`cateName_{$_SESSION['lId']}` AS cateName, p.`subcategoryId`,sc.slug AS scslug,sc.`cateName_{$_SESSION['lId']}` AS subCateName,IFNULL(AVG(b.`price`), 0) AS averageBid, IFNULL(AVG(b.`duration`), 0) AS averageETA, COUNT(b.id) AS bids, p.userId, p.createdDate, p.slug, p.`description`, p.budget AS budget, p.`isFeatured`, p.`slug`, u.`profileLink`, u.`profilePhoto`, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName FROM (SELECT u.userId, p.projectId FROM (SELECT DISTINCT projectId FROM tbl_project_skills) p CROSS JOIN (SELECT DISTINCT userId FROM tbl_user_skills) u) main LEFT JOIN tbl_user_skills us ON us.userId = main.userId LEFT JOIN tbl_project_skills ps ON  main.projectId = ps.projectId LEFT JOIN tbl_skills AS s ON ps.`skillId` = s.`id` AND s.`status` = 'a' LEFT JOIN tbl_projects AS p ON main.projectId = p.id LEFT JOIN tbl_users AS u ON p.userId = u.userId LEFT JOIN tbl_bids AS b ON main.projectId = b.`projectId` AND b.`isFinal` = 'y' AND b.`isNulled` = 'n' LEFT JOIN tbl_categories AS c ON p.categoryid = c.id AND c.`isactive` = 'y' LEFT OUTER JOIN tbl_categories AS sc ON p.subcategoryid = sc.id AND sc.`isactive` = 'y' WHERE p.`isActive` = 'y' AND p.`biddingDeadline` > ? AND u.`isActive` = 'y' AND (p.`jobStatus` = 'open'  or  p.`jobStatus` = 'reopened')   ";
            }else{
                $qry = " SELECT p.userId AS pUser, p.id AS id, p.biddingDeadline, COUNT(ps.projectId) AS points, GROUP_CONCAT(DISTINCT s.skillName_{$_SESSION['lId']}) AS skills, p.title, p.`categoryId`, c.slug AS cslug, c.`cateName_{$_SESSION['lId']}` AS cateName, p.`subcategoryId`, sc.slug AS scslug, sc.`cateName_{$_SESSION['lId']}` AS subCateName, IFNULL(AVG(b.`price`), 0) AS averageBid, IFNULL(AVG(b.`duration`), 0) AS averageETA, COUNT(b.id) AS bids, p.userId, p.createdDate, p.slug, p.`description`, p.budget AS budget, p.`isFeatured`, p.`slug`, u.`profileLink`, u.`profilePhoto`, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName FROM tbl_projects AS p LEFT JOIN tbl_users AS u ON p.userId = u.userId LEFT JOIN tbl_bids AS b ON p.id = b.`projectId` AND b.`isFinal` = 'y' AND b.`isNulled` = 'n' LEFT JOIN tbl_categories AS c ON p.categoryid = c.id AND c.`isactive` = 'y' LEFT JOIN tbl_categories AS sc ON p.subcategoryid = sc.id AND sc.`isactive` = 'y' LEFT JOIN tbl_user_skills us ON us.userId = p.userId LEFT JOIN tbl_project_skills ps ON p.id = ps.projectId LEFT JOIN tbl_skills AS s ON ps.`skillId` = s.`id` AND s.`status` = 'a' WHERE p.`isActive` = 'y' AND p.`biddingDeadline` > ? AND u.`isActive` = 'y' AND ( p.`jobStatus` = 'open' OR p.`jobStatus` = 'reopened' ) ";
                
            }            
        }
        

        
        $qryParams = array($nowTime);

        //START:: filters
        if ($this->categoryId != null && $this->categoryId != "0") {
            $catIdFrmDB = $db->pdoQuery('select id from tbl_categories where slug = ?', array($this->categoryId))->result();
            $condition .= ' AND p.categoryId = ' . $catIdFrmDB['id'];
        }
        if ($this->subcategoryId != null) {
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

            $condition .= ' AND p.categoryId > 0 AND p.subcategoryId IN (' . $finalsubcatstring . ') ';
        }

        if ($this->skillIds != null) {
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

            $condition .= ' AND ps.`skillId` IN (' . $finalSkillString . ') ';
        }
        if ($this->level != null) {
            $elements = explode(',', $this->level);
            $condition .= ' AND p.`experienceWanted` IN ("' . implode('", "', $elements) . '") ';
        }
        if ($this->budget_from != null && $this->budget_to != null) {
            $condition .= ' AND p.`budget` >= ' . $this->budget_from;
            $condition .= ' AND p.`budget` <= ' . $this->budget_to;
        }
        if ($this->isFeatured == 'y') {
            $condition .= ' AND p.`isFeatured` = "' . $this->isFeatured . '"';
        }
        if ($this->keyword != null) {
            $condition .= ' AND (p.`title` LIKE "%' . $this->keyword . '%" OR p.`description` LIKE "%' . $this->keyword . '%") ';
        }
        //END:: filters

        //ORDERBY
        if ($this->sort_by != null && $this->sort_by == "newest_first") {
            $sort_by = ' ORDER BY p.`id` DESC ';
        } else {
            if ($this->sessUserType == 'p' && $this->checkProviderHasSkills()) {
                $sort_by = ' ORDER BY p.`isFeatured`, main.userId <> ' . $this->sessUserId . ', points DESC  ';
            } else {
                $sort_by = ' ORDER BY p.`isFeatured`, p.id DESC ';
            }

        }
        //group by
        $group_by = ($this->sessUserType == 'p' && $this->checkProviderHasSkills()) ? ' GROUP BY main.projectId ': ' GROUP BY p.id ';

        ////////////////////////////
        // put pagination
        $limit_cond = null;

        $q = $this->db->pdoQuery($qry . $condition . $group_by . $sort_by, $qryParams);
        $this -> fb -> info( $qry . $condition . $group_by . $sort_by,'query');
        $this -> fb -> info($qryParams,'params');
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
            );
        }

        if (is_object($qry) && $qry->affectedRows() > 0) {
            $results = $qry->results();
            $this->fb->info('ds', $results);
            foreach ($results as $k => $v) {
                $bids    = $this->db->pdoQuery(' SELECT IFNULL(AVG(f.averageRating),0) AS average, COUNT(f.id) AS totalReviews, b.id, u.profileLink, u.profilePhoto, CONCAT_WS(" ", u.firstName, u.lastName) AS fullName , b.* FROM tbl_bids AS b LEFT JOIN tbl_users AS u ON b.userId = u.userId LEFT JOIN tbl_feedbacks AS f ON b.userId = f.`userto` LEFT JOIN tbl_users AS fromu ON f.userFrom = fromu.userId WHERE b.`projectId` = ? AND b.`isActive` = "y" AND b.`isNulled` = "n" AND u.`isActive` = "y"  GROUP BY b.userId ', array($v['id']))->affectedRows();
                $replace = array(
                    '%profilePhoto%' => tim_thumb_image($v['profilePhoto'], 'profile', 75, 75),
                    '%profileLink%'  => SITE_URL . $v['profileLink'],
                    '%fullName%'     => ucwords(filtering($v['fullName'])),
                    '%slug%'         => SITE_URL . $v['profileLink'] . "/" . $v['slug'],
                    '%title%'        => ucwords(filtering($v['title'])),
                    '%isFeatured%'   => ($v['isFeatured'] == 'n') ? 'hide' : null,
                    '%desc%'         => filtering(String_crop($v['description'], 180)),
                    '%budget%'       => filtering($v['budget']),
                    '%bids%'         => filtering($bids),
                    '%pid%'          => $v['id'],
                    '%like_icon%'    => is_my_fav($v['id'], 'project') ? 'fa-heart' : 'fa-heart-o',
                    '%avgBid%'       => ($bids > 0) ? DEFAULT_CURRENCY_CODE.' '.number_format($v['averageBid'], 2) : 0,
                    '%avgETA%'       => ($bids > 0) ? (int) $v['averageETA'] : 0,
                    '%postedDate%'   => date(PHP_DATE_FORMAT, strtotime($v['createdDate'])),
                    '%skills%'       => $this->getSkills($v['id']),
                    '%cat_subcat%'   => $this->getCatSubcat($v),
                );

                if ($this->dataOnly) {
                    $results[$k]['bids']   = filtering($bids);
                    $results[$k]['avgBid'] = ($bids > 0) ? number_format($v['averageBid'], 2) : 0;
                    $results[$k]['avgETA'] = ($bids > 0) ? (int) $v['averageETA'] : 0;
                    $results[$k]['skills'] = $this->getSkills($v['id']);
                    $results[$k]['isFav']  = is_my_fav($v['id'], 'project') ? true : false;
                } else {
                    if ($this->sessUserId != $v['userId']) {
                        $html .= get_view(DIR_TMPL . $this->module . "/project_row_with_heart-nct.tpl.php", $replace);
                    } else {
                        $html .= get_view(DIR_TMPL . $this->module . "/project_row-nct.tpl.php", $replace);
                    }

                }

            }
            if ($this->dataOnly) {
                $datatosend['data'] = $results;
                return $datatosend;
            }
        } else {
            if ($this->dataOnly) {
                $datatosend['data'] = array();
                return $datatosend;
            } else {
                $html .= get_view(DIR_TMPL . $this->module . "/no_project_row-nct.tpl.php", array('%status%' => 'open'));
            }
        }

        return sanitize_output($html);
    }

    public function left()
    {
        $v = $this->db->pdoQuery('SELECT MIN(p.budget) AS lowestBudget, MAX(p.budget) AS highestBudget FROM tbl_projects AS p')->result();

        $levels = explode(',', $this->level);
        if ($this->level == null) {
            $check_entry_level = $check_moderate = $check_expert = 'checked';
        }
        $check_entry_level = (!in_array('entry level', $levels)) ? null : 'checked';
        $check_moderate    = (!in_array('moderate', $levels)) ? null : 'checked';
        $check_expert      = (!in_array('expert', $levels)) ? null : 'checked';

        $replace = array(
            '%projectCategories%'    => $this->projectCategories(),
            '%projectSubCategories%' => $this->projectSubCategories(),
            '%skill_options%'        => $this->skill_options(),
            '%lowestBudget%'         => ($v['lowestBudget'] <= 0 || $v['lowestBudget'] == null) ? 0 : $v['lowestBudget'],
            '%highestBudget%'        => ($v['highestBudget'] <= 0 || $v['highestBudget'] == null) ? 500000 : $v['highestBudget'],

            '%check_entry_level%'    => $check_entry_level,
            '%check_moderate%'       => $check_moderate,
            '%check_expert%'         => $check_expert,

            '%check_isFeatured%'     => ($this->isFeatured == "y") ? 'checked' : null,
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
                'content' => $this->project_rows(),
                'title'   => ucwords(Search_projects).' - ' . SITE_NM,
            ));
            exit;
        }

        $replace = array(
            '%left%'             => $this->left(),
            '%rows%'             => $this->project_rows(),

            '%select_relevance%' => ($this->sort_by != "newest_first") ? 'selected' : null,
            '%select_newest%'    => ($this->sort_by == "newest_first") ? 'selected' : null,

            '%keyword%'          => ($this->keyword != null) ? filtering($this->keyword) : '',
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

        if (isset($categoryId) && $categoryId != null) {
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

    function checkProviderHasSkills(){
        $check = $this->db->count('tbl_user_skills',array('userId'=>$this->sessUserId));
        return ($check) ? true : false;
    }

}
