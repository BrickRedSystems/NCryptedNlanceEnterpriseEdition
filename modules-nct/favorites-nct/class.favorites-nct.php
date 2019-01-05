<?php
class Favorites {
    function __construct($module = "", $id = 0, $reqData = array()) {
        foreach ($GLOBALS as $key => $values) {
            $this -> $key = $values;
        }
        $this -> module = $module;
        $this -> id = $id;
        $this -> reqData = $reqData;
        $this -> dataOnly = (isset($this->reqData['dataOnly']) && $this->reqData['dataOnly']==true)?true:false;
        $this->sessUserId = (isset($reqData['userId']) && (int)$reqData['userId'] >0)?$reqData['userId']:$this->sessUserId;
        if($this->dataOnly){
            $_SESSION['lId'] = (isset($this->reqData['lId']) && $this->reqData['lId'] >0)?$this->reqData['lId']:1;  
            setLang();  
        }
    }

    public function projects($count = false) {
        $order_by = ' ORDER BY p.id DESC ';
        $html = null;

        $results = $this -> db -> pdoQuery('SELECT p.id AS projectId, CONCAT_WS(" ", u.`firstName`, u.`lastName`) AS fullName, p.id, p.title, p.slug, p.`description`, f.id AS isFavorite, f.`createdDate`, p.`isFeatured`, u.profileLink, u.`profilePhoto` FROM tbl_favourites AS f LEFT JOIN tbl_projects AS p ON f.`favoriteId` = p.`id` LEFT JOIN tbl_users AS u ON p.`userId` = u.`userId` WHERE f.userId = ? AND p.`isActive`= "y" AND u.`isActive`= "y" AND f.type = 2 GROUP BY p.`id` ' . $order_by, array($this -> sessUserId));
        
        if($this->dataOnly){
            return $results->results();          
        }else{
            $results = $results->results();  
        }
	if ($count) {
            return count($results);
        }
	if (!empty($results)) {
        foreach ($results as $k => $v) {
            extract($v);
            $html .= get_view(DIR_TMPL . $this -> module . "/fav_project-row.tpl.php", array(
                '%title%' => $title,
                '%slug%' => SITE_URL . $profileLink . "/" . $slug,
                '%desc%' => String_crop(filtering($description), 150),
                '%fullName%' => $fullName,
                '%profileLink%' => SITE_URL . $profileLink,
                '%profilePhoto%' => tim_thumb_image($profilePhoto, 'profile', 300, 300),
                '%isFavorite%' => 'fa-heart',
                '%date%' => date(PHP_DATE_FORMAT, strtotime($createdDate)),
                '%isFeatured%' => ($isFeatured == 'n') ? 'hide' : null,
                '%pid%' => $projectId
            ));
        } 
}else {
            $btn = '<p>'.You_do_not_have_any_favorite_projects_yet.'</p>';
            $replace = array('%msg%' => $btn);
            $html .= get_view(DIR_TMPL . $this->module . "/no_favorites_row-nct.tpl.php", $replace);
        }
        return $html;
    }

    public function users($count = false) {
        $html = null;
        
        $order_by = ' ORDER BY average DESC ';
        $results = $this -> db -> pdoQuery("SELECT GROUP_CONCAT( DISTINCT CONCAT(ts.skillName_".$_SESSION['lId'].", ',') SEPARATOR ', ' ) AS skills, IFNULL(tbl_ongoing.total, 0) AS total_ongoing, IFNULL(tbl_completed.total, 0) AS total_completed, IFNULL(AVG(f.averageRating), 0) AS average, COUNT(f.id) AS totalReviews, CONCAT_WS( ', ', tc.`cityName`, s.`stateName`, c.`countryName` ) AS userlocation, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName, @ruserId := u.userId, u.* FROM tbl_favourites AS fav LEFT JOIN tbl_users AS u ON fav.favoriteId = u.userId AND fav.type = 1 LEFT JOIN tbl_country AS c ON u.`countryCode` = c.`CountryId` LEFT JOIN tbl_state AS s ON u.`state` = s.`StateID` LEFT JOIN `tbl_city` AS tc ON u.`city` = tc.`CityId` LEFT JOIN tbl_feedbacks AS f ON u.userId = f.`userTo` LEFT JOIN (SELECT COUNT('id') AS total, providerId FROM tbl_projects WHERE jobStatus = 'open' AND isActive = 'y' AND providerId = @ruserId GROUP BY providerId) AS tbl_ongoing ON tbl_ongoing.providerId = u.userId LEFT JOIN (SELECT COUNT('id') AS total, providerId FROM tbl_projects WHERE jobStatus = 'completed' AND isActive = 'y' GROUP BY providerId) AS tbl_completed ON tbl_completed.providerId = u.userId LEFT JOIN tbl_user_skills AS us ON u.userId = us.`userId` LEFT JOIN tbl_skills AS ts ON us.`skillId` = ts.`id` AND ts.`status` = 'a' WHERE u.`userType` = 'p' AND u.`isActive` = 'y' AND fav.userId = ? GROUP BY fav.userId  " . $order_by, array($this -> sessUserId));

        if ($this->dataOnly) {
            return $results->results();
        } else {
            $results = $results->results();
        }
        if ($count) {
            return count($results);
        }
        if (!empty($results)) {

        foreach ($results as $k => $v) {
            $reviews = $this -> get_review($v['userId']);
            $replace = array(
                '%profilePhoto%' => tim_thumb_image($v['profilePhoto'], 'profile', 75, 75),
                '%profileLink%' => SITE_URL . $v['profileLink'],
                '%fullName%' => filtering($v['fullName']),
                '%desc%' => filtering(String_crop($v['aboutMe'], 230)),
                '%averageRatings%' => number_format($reviews[0], 1),
                '%totalReviews%' => $reviews[1],
                '%total_completed%' => $v['total_completed'],
                '%total_ongoing%' => $v['total_ongoing'],
                '%createdDate%' => date(PHP_DATE_FORMAT, strtotime($v['createdDate'])),
                '%like_icon%' => is_my_fav($v['userId'], 'user') ? 'fa-heart' : 'fa-heart-o',
                '%location%' => (trim($v['userlocation']) == '') ? Not_Available : $v['userlocation'],
                '%userId%' => $v['userId']
            );

            $html .= get_view(DIR_TMPL . $this -> module . "/favorite-row.tpl.php", $replace);

        }
} else {
            $btn = '<p>'.You_do_not_have_any_favorite_users_yet.'</p>';
            $replace = array('%msg%' => $btn);
            $html .= get_view(DIR_TMPL . $this->module . "/no_favorites_row-nct.tpl.php", $replace);
        }

        return $html;

    }

    public function getPageContent() {
        //dump_exit($this->reqData);
        $rel = issetor($this -> reqData['rel'], 'false');
        $func = issetor($this -> reqData['extra'], 'projects');
        $func = (method_exists($this, $func)) ? $func : 'projects';
        //dump_exit($this->reqData);
        if ($rel == "true") {
            echo json_encode(array(
                'code' => 200,
                'content' => $this -> {$func}(),
                'title' => 'My providers - ' . SITE_NM
            ));
            exit ;
        }
        return get_view(DIR_TMPL . $this -> module . "/" . $this -> module . ".tpl.php", array(
            '%row%' => $this -> {$func}(),            
            '%total_fav_projects%' => $this -> projects(true),
            '%total_fav_users%' => $this -> users(true),
            '%class_fav_projects%' => ($func == "projects") ? 'active' : null,            
            '%class_fav_users%' => ($this->sessUserType=='p')?'hide':(($func == "users") ? 'active' : null),
            
        ));
    }

    public function get_review($userId = null) {
        $results = $this -> db -> pdoQuery("SELECT AVG(f.averageRating) AS average, count(f.id) AS total FROM tbl_feedbacks AS f LEFT JOIN tbl_users AS u ON f.`userfrom` = u.userid WHERE f.userto = ? AND u.isactive = 'y' ", array('userTo' => $userId)) -> result();

        return array(
            $results['average'],
            $results['total']
        );
    }

}
?>