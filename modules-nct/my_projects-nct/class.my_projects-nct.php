<?php
class myProjects
{
    public function __construct($module = "", $id = 0, $reqData = array())
    {
        foreach ($GLOBALS as $key => $values) {
            $this->$key = $values;
        }
        $this->module     = $module;
        $this->id         = $id;
        $this->reqData    = $reqData;
        $this->table      = 'tbl_projects';
        $this->sessUserId = (isset($reqData['userId']) && $reqData['userId'] > 0) ? $reqData['userId'] : $this->sessUserId;
        $this->user       = $this->userData();
        $this->dataOnly   = (isset($this->reqData['dataOnly']) && $this->reqData['dataOnly'] == true) ? true : false;
        if($this->dataOnly){
            $_SESSION['lId'] = (isset($this->reqData['lId']) && $this->reqData['lId'] >0)?$this->reqData['lId']:1;  
            setLang();  
        }
    }

    public function project_rows($project_status = 'open', $count = false)
    {

        global $db, $fb;
        $html           = $qry           = $condition           = $sort_by           = null;
        $project_status = ($project_status == null || !in_array($project_status, array('open', 'reopened', 'completed', 'progress', 'milestone_accepted', 'accepted', 'dispute', 'closed', 'expired'))) ? 'open' : $project_status;
        //dump($project_status);
        switch ($project_status) {
            default:
            case 'open':
            case 'reopened':
                $paramArray = array(
                    $project_status,
                    $this->user['userId'],
                );
                if ($this->user['userType'] == 'c') {
                    $qry = "SELECT p.id, p.title, COUNT(b.id) AS bids, p.slug, p.`description`, p.budget AS budget, p.`isFeatured`, p.`slug`, u.`profileLink`, u.`profilePhoto`, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName FROM tbl_projects AS p LEFT JOIN tbl_users AS u ON p.userId = u.userId LEFT JOIN tbl_bids AS b ON p.`id` = b.`projectId` WHERE p.`isActive` = 'y' AND u.`isActive` = 'y' AND p.`jobStatus` = ? AND p.userId=? GROUP BY p.id ORDER BY p.`id` DESC  ";

                } else {
                    $paramArray = array(
                        $this->sessUserId,
                        $this->sessUserId,
                        $project_status,
                        $this->user['userId'],
                    );
                    $qry = "SELECT p.id, p.title, COUNT(b.id) AS bids,(select
                        case when (favoriteId=p.id AND userId = ?) Then 'yes' Else 'no' END
                        from tbl_favourites
                        where userId= ? AND favoriteId=p.id) AS isFavorite, p.slug, p.`description`, p.budget AS budget, p.`isFeatured`, p.`slug`, u.`profileLink`, u.`profilePhoto`, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName FROM tbl_projects AS p LEFT JOIN tbl_users AS u ON p.userId = u.userId LEFT JOIN tbl_bids AS b ON p.`id` = b.`projectId` WHERE p.`isActive` = 'y' AND u.`isActive` = 'y' AND p.`jobStatus` = ? AND b.userId=? GROUP BY p.id ORDER BY p.`id` DESC  ";
                }

                $est_or_total = Est_Budget;
                break;
            case 'completed':
            case 'progress':
            case 'milestone_accepted':
            case 'accepted':
            case 'dispute':
            case 'closed':
            case 'expired':
                $paramArray = array(
                    $project_status,
                    $this->user['userId'],
                );
                if ($this->user['userType'] == 'c') {
                    $qry = "SELECT p.id, p.title, COUNT(b.id) AS bids, p.slug, p.`description`, p.price AS budget, p.`isFeatured`, p.`slug`, u.`profileLink`, u.`profilePhoto`, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName FROM tbl_projects AS p LEFT JOIN tbl_users AS u ON p.userId = u.userId LEFT JOIN tbl_bids AS b ON p.`id` = b.`projectId` WHERE p.`isActive` = 'y' AND u.`isActive` = 'y' AND p.`jobStatus` = ? AND p.userId=? GROUP BY p.id ORDER BY p.`id` DESC  ";
                } else {
                    $paramArray = array(
                        $this->sessUserId,
                        $this->sessUserId,
                        $project_status,
                        $this->user['userId'],
                    );
                    $qry = "SELECT p.id, p.title,COUNT(b.id) AS bids, (select
                        case when (favoriteId=p.id AND userId = ?) Then 'yes' Else 'no' END
                        from tbl_favourites
                        where userId= ? AND favoriteId=p.id) AS isFavorite, p.slug, p.`description`, p.price AS budget, p.`isFeatured`, p.`slug`, u.`profileLink`, u.`profilePhoto`, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName FROM tbl_projects AS p LEFT JOIN tbl_users AS u ON p.userId = u.userId LEFT JOIN tbl_bids AS b ON p.`id` = b.`projectId` WHERE p.`isActive` = 'y' AND u.`isActive` = 'y' AND p.`jobStatus` = ? AND p.providerId=? GROUP BY p.id ORDER BY p.`id` DESC  ";
                }
                $est_or_total = Total_Price;
                break;
        }

        ////////////////////////////
        // put pagination
        $limit_cond = null;

        $q = $this->db->pdoQuery($qry . $condition . $sort_by, $paramArray);
        //$fb->info($qry . $condition . $sort_by,'query -> '.$project_status);
        $totalRows = $q->affectedRows();
        if ($count) {
            return $totalRows;
        }
        $pageNo = isset($this->reqData["pageNo"]) ? $this->reqData["pageNo"] : 0;
        $pager  = getPagerData($totalRows, LIMIT, $pageNo);
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
        //$this->fb->info($pager,'pager');
        //$this->fb->info($pageNo,'pageNo');
        //$this->fb->info($project_status,'status');
        if ($this->dataOnly) {
            return array(
                'page'          => issetor($page, 0),
                'numPages'      => issetor($pager->numPages, 0),
                'total_records' => issetor($totalRows, 0),
                'data'          => (is_object($qry) && $qry->affectedRows() > 0) ? $qry->results() : array(),
            );
        }

        if (is_object($qry) && $qry->affectedRows() > 0) {
            $results = $qry->results();
            foreach ($results as $k => $v) {
                $bids = $this->db->pdoQuery(' SELECT IFNULL(AVG(f.averageRating),0) AS average, COUNT(f.id) AS totalReviews, b.id, u.profileLink, u.profilePhoto, CONCAT_WS(" ", u.firstName, u.lastName) AS fullName , b.* FROM tbl_bids AS b LEFT JOIN tbl_users AS u ON b.userId = u.userId LEFT JOIN tbl_feedbacks AS f ON b.userId = f.`userto` LEFT JOIN tbl_users AS fromu ON f.userFrom = fromu.userId WHERE b.`projectId` = ? AND b.`isActive` = "y" AND b.`isNulled` = "n" AND u.`isActive` = "y"  GROUP BY b.userId ', array($v['id']))->affectedRows();

                $replace = array(
                    '%profilePhoto%' => tim_thumb_image($v['profilePhoto'], 'profile', 75, 75),
                    '%profileLink%'  => filtering($v['profileLink']),
                    '%fullName%'     => filtering($v['fullName']),
                    '%slug%'         => $v['profileLink'] . "/" . $v['slug'],
                    '%title%'        => filtering($v['title']),
                    '%isFeatured%'   => ($v['isFeatured'] == 'n') ? 'hide' : null,
                    '%desc%'         => filtering(String_crop($v['description'], 230)),
                    '%est_or_total%' => $est_or_total,
                    '%budget%'       => CURRENCY_SYMBOL . filtering($v['budget']),
                    '%bids%'         => filtering($bids),
                    '%pid%'          => $v['id'],
                    '%like_icon%'    => is_my_fav($v['id'], 'project') ? 'fa-heart' : 'fa-heart-o',
                );
                if ($this->user['userType'] == 'p') {
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

    public function getPageContent()
    {
        //dump_exit($this->reqData);
        $rel  = (isset($this->reqData['rel']) && $this->reqData['rel'] != null) ? $this->reqData['rel'] : 'false';
        $func = (isset($this->reqData['extra']) && $this->reqData['extra'] != null) ? $this->reqData['extra'] : 'open';

        //$rel = issetor($this -> reqData['rel'], 'false');
        //$func = issetor($this -> reqData['extra'], 'open');

        //dump_exit($this->reqData);

        return get_view(DIR_TMPL . $this->module . "/" . $this->module . ".tpl.php", array(
            '%right%' => $this->project_rows("{$func}"),
            '%left%'  => $this->left(),
        ));
    }

    public function left()
    {
        $html   = null;
        $result = $this->db->pdoQuery("SHOW COLUMNS FROM $this->table LIKE 'jobStatus'")->results();

        if ($result) {
            $fetchRes = explode("','", preg_replace("/(enum|set)\('(.+?)'\)/", "\\2", $result[0]["Type"]));
        }
        $func = issetor($this->reqData['extra'], 'open');

        foreach ($fetchRes as $k => $v) {
            $status = ucwords($v);
            $status = constant($status);
            $html .= get_view(DIR_TMPL . $this->module . "/left_li.tpl.php", array(
                '%title%'       => $status,
                '%slug%'        => $v,
                '%total%'       => $this->project_rows($v, true),
                '%classActive%' => ($v == $func) ? 'active' : null,
            ));
        }
        return $html;
    }

    public function userData()
    {
        $arr = array();
        $arr = $this->db->pdoQuery("SELECT u.*, CONCAT_WS(' ',u.firstName,u.lastName) AS fullName FROM tbl_users AS u WHERE u.userId=? ", array($this->sessUserId))->result();
        if ($arr['userType'] == 'c') {
            $arr['ongoing'] = getTableValue('tbl_projects', 'count("id")', array(
                'userId'    => $arr['userId'],
                'jobStatus' => 'open',
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
                'jobStatus'  => 'open',
                'isActive'   => 'y',
            ));
            $arr['completed'] = getTableValue('tbl_projects', 'count("id")', array(
                'providerId' => $arr['userId'],
                'jobStatus'  => 'completed',
                'isActive'   => 'y',
            ));
        }
        return $arr;

    }

}
