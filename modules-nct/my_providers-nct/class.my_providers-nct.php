<?php
class myProviders {
	function __construct($module = "", $id = 0, $reqData = array()) {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
		$this->reqData = $reqData;
		$this->table = 'tbl_projects';
		$this->sessUserId = (isset($reqData['userId']) && $reqData['userId'] >0)?$reqData['userId']:$this->sessUserId;
		$this -> dataOnly = (isset($this->reqData['dataOnly']) && $this->reqData['dataOnly']==true)?true:false;
		$this->sort = issetor($this->reqData['sort_by'], null);
		$this->keyword = issetor($this->reqData['keyword'], null);
		$this->keyword = str_replace(array(
			'_',
			'%',
			"'"
		), array(
			'\_',
			'\%',
			"\'"
		), $this->keyword);
		if($this->dataOnly){
        	$_SESSION['lId'] = (isset($this->reqData['lId']) && $this->reqData['lId'] >0)?$this->reqData['lId']:1;	
        	setLang();	
        }
	}

	public function invited($count = false) {
		$html = null;
		switch ($this->sort) {
			case 'relevence' :
				$order_by = 'p.isFeatured ASC';
				break;
			case 'newest-to-oldest' :
				$order_by = 'i.createdDate DESC';
				break;
			case 'oldest-to-newest' :
				$order_by = 'i.createdDate ASC';
				break;
			default :
				$order_by = 'p.isFeatured ASC';
				break;
		}
		

		$results = $this->db->pdoQuery('SELECT p.id as projectId, CONCAT_WS(" ", u.`firstName`, u.`lastName`) AS fullName, IFNULL(AVG(fe.averageRating), 0) AS average, COUNT(fe.id) AS totalReviews, i.`providerId`, i.`projectId`, p.title, p.`description`, f.id AS isFavorite, i.`createdDate`, p.`isFeatured`, u.profileLink, u.`profilePhoto` FROM tbl_project_invitation AS i JOIN tbl_users AS u ON i.`providerId` = u.userId AND u.`isActive` = "y" JOIN tbl_projects AS p ON i.`projectId` = p.id LEFT JOIN tbl_favourites AS f ON i.`projectId` = f.`favoriteId` AND f.`userId` = i.providerId AND f.`type` = 2 LEFT JOIN tbl_feedbacks AS fe ON i.`providerId` = fe.`userTo` WHERE p.userId= ? AND (p.title LIKE "%' . $this->keyword . '%" OR u.firstName LIKE "%' . $this->keyword . '%" OR u.lastName LIKE "%' . $this->keyword . '%") GROUP BY i.`providerId` ORDER BY ' . $order_by, array($this->sessUserId));

		if($this->dataOnly){
            return $results->results();
        }else{
            $results = $results->results();
        }

		if ($count) {
			return count($results);
		}
		if(empty($results)){
			return get_view(DIR_TMPL . $this->module . "/no-provider-nct.tpl.php", array('%msg%'=>There_are_no_such_providers));			
		}
		foreach ($results as $k => $v) {
			extract($v);
			$html .= get_view(DIR_TMPL . $this->module . "/invited-row.tpl.php", array(
				'%title%' => $title,
				'%desc%' => String_crop(filtering($description), 150),
				'%fullName%' => $fullName,
				'%profileLink%' => SITE_URL . $profileLink,
				'%profilePhoto%' => tim_thumb_image($profilePhoto, 'profile', 300, 300),
				'%isFavorite%' => ($isFavorite > 0) ? 'fa-heart' : 'fa-heart-o',
				'%date%' => date(PHP_DATE_FORMAT, strtotime($createdDate)),
				'%rating%' => number_format($average, 1),
				'%totalReviews%' => $totalReviews,
				'%isFeatured%' => ($isFeatured == 'n') ? 'hide' : null,
				'%pid%' => $projectId
			));
		}

		return $html;
	}

	public function favorite($count = false) {
		$html = null;
		switch ($this->sort) {
			case 'relevence' :
				$order_by = ' ORDER BY average DESC ';
				break;
			case 'newest-to-oldest' :
				$order_by = ' ORDER BY u.`userId` DESC ';
				break;
			case 'oldest-to-newest' :
				$order_by = ' ORDER BY u.`userId` ASC ';
				break;
			default :
				$order_by = ' ORDER BY average DESC ';
				break;
		}

		$results = $this->db->pdoQuery("SELECT GROUP_CONCAT( DISTINCT CONCAT(ts.skillName_".$_SESSION['lId'].", ',') SEPARATOR ', ' ) AS skills, IFNULL(tbl_ongoing.total,0) AS total_ongoing, IFNULL(tbl_completed.total,0) AS total_completed, IFNULL(AVG(f.averageRating), 0) AS average, COUNT(f.id) AS totalReviews, CONCAT_WS( ', ', tc.`cityName`, s.`stateName`, c.`countryName` ) AS userlocation, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName, @ruserId := u.userId, u.* FROM tbl_favourites AS fav LEFT JOIN tbl_users AS u ON fav.favoriteId = u.userId AND fav.type = 1 AND fav.userId = ? LEFT JOIN tbl_country AS c ON u.`countryCode` = c.`CountryId` LEFT JOIN tbl_state AS s ON u.`state` = s.`StateID` LEFT JOIN `tbl_city` AS tc ON u.`city` = tc.`CityId` LEFT JOIN tbl_feedbacks AS f ON u.userId = f.`userTo` LEFT JOIN (SELECT COUNT('id') AS total, providerId FROM tbl_projects WHERE jobStatus = 'open' AND isActive = 'y' AND providerId = @ruserId GROUP BY providerId) AS tbl_ongoing ON tbl_ongoing.providerId = u.userId LEFT JOIN (SELECT COUNT('id') AS total, providerId FROM tbl_projects WHERE jobStatus = 'completed' AND isActive = 'y' GROUP BY providerId) AS tbl_completed ON tbl_completed.providerId = u.userId  LEFT JOIN tbl_user_skills AS us ON u.userId = us.`userId` LEFT JOIN tbl_skills AS ts ON us.`skillId` = ts.`id` AND ts.`status`='a' WHERE u.`userType` = 'p' AND u.`isActive` = 'y' AND (u.firstName LIKE '%" . $this->keyword . "%' OR u.lastName LIKE '%" . $this->keyword . "%') GROUP BY u.`userId` " . $order_by, array($this->sessUserId));
		if($this->dataOnly){
            return $results->results();
        }else{
            $results = $results->results();
        }

		if ($count) {
			return count($results);
		}
		if(empty($results)){
			return get_view(DIR_TMPL . $this->module . "/no-provider-nct.tpl.php", array('%msg%'=>There_are_no_such_providers));	
		}
		foreach ($results as $k => $v) {
			$reviews = $this->get_review($v['userId']);
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

			$html .= get_view(DIR_TMPL . $this->module . "/favorite-row.tpl.php", $replace);

		}

		return $html;

	}

	public function hired($count = false) {
		$html = null;

		switch ($this->sort) {
			case 'relevence' :
				$order_by = 'p.isFeatured ASC';
				break;
			case 'newest-to-oldest' :
				$order_by = 'p.hiredDate DESC';
				break;
			case 'oldest-to-newest' :
				$order_by = 'p.hiredDate ASC';
				break;
			default :
				$order_by = 'p.isFeatured ASC';
				break;
		}
		$results = $this->db->pdoQuery('SELECT p.id as projectId, IFNULL(AVG(fe.averageRating),0) AS average, COUNT(fe.id) AS totalReviews, p.title, p.hiredDate, f.id AS isFavorite, CONCAT_WS("/", customer.profileLink, p.slug) AS projectLink, p.`description`, p.`isFeatured`,  CONCAT_WS(" ", u.`firstName`, u.`lastName`) AS fullName, u.profileLink, u.`profilePhoto` FROM tbl_projects AS p LEFT JOIN tbl_users AS u ON p.`providerId` = u.`userId` LEFT JOIN tbl_users AS customer ON p.`userId` = customer.`userId` LEFT OUTER JOIN tbl_favourites AS f ON p.`id` = f.`favoriteId` AND f.`userId` = p.userId AND f.`type` = 2 LEFT OUTER JOIN tbl_feedbacks AS fe ON p.`providerId` = fe.`userTo` WHERE p.`userId` = ? AND p.providerId > 0 AND (p.title LIKE "%' . $this->keyword . '%" OR u.firstName LIKE "%' . $this->keyword . '%" OR u.lastName LIKE "%' . $this->keyword . '%") GROUP BY p.id order by ' . $order_by, array($this->sessUserId));

		if($this->dataOnly){
            return $results->results();
        }else{
            $results = $results->results();
        }

		if ($count) {
			return count($results);
		}
		if(empty($results)){
			return get_view(DIR_TMPL . $this->module . "/no-provider-nct.tpl.php", array('%msg%'=>There_are_no_such_providers));	
		}
		foreach ($results as $k => $v) {
			extract($v);
			$html .= get_view(DIR_TMPL . $this->module . "/hired-row.tpl.php", array(
				'%title%' => $title,
				'%projectLink%' => SITE_URL . $projectLink,
				'%desc%' => String_crop(filtering($description), 150),
				'%fullName%' => $fullName,
				'%profileLink%' => SITE_URL . $profileLink,
				'%profilePhoto%' => tim_thumb_image($profilePhoto, 'profile', 300, 300),
				'%isFavorite%' => ($isFavorite > 0) ? 'fa-heart' : 'fa-heart-o',
				'%date%' => date(PHP_DATE_FORMAT, strtotime($hiredDate)),
				'%rating%' => number_format($average, 1),
				'%totalReviews%' => $totalReviews,
				'%isFeatured%' => ($isFeatured == 'n') ? 'hide' : null,
				'%pid%' => $projectId
			));
		}

		return $html;
	}

	public function getPageContent() {
		//dump_exit($this->reqData);
		$rel = issetor($this->reqData['rel'], 'false');
		$func = issetor($this->reqData['extra'], 'hired');
		$func = (method_exists($this, $func)) ? $func : 'hired';
		//dump_exit($this->reqData);
		if ($rel == "true") {
			echo json_encode(array(
				'code' => 200,
				'content' => $this->{$func}(),
				'title' => 'My providers - ' . SITE_NM
			));
			exit ;
		}
		return get_view(DIR_TMPL . $this->module . "/" . $this->module . ".tpl.php", array(
			'%row%' => $this->{$func}(),
			'%total_hired%' => $this->hired(true),
			'%total_invited%' => $this->invited(true),
			'%total_favorite%' => $this->favorite(true),
			'%class_hired%' => ($func == "hired") ? 'active' : null,
			'%class_invited%' => ($func == "invited") ? 'active' : null,
			'%class_favorite%' => ($func == "favorite") ? 'active' : null,
			'%select_relevance%' => ($this->sort == "relevance") ? 'selected' : null,
			'%select_n2o%' => ($this->sort == "newest-to-oldest") ? 'selected' : null,
			'%select_o2n%' => ($this->sort == "oldest-to-newest") ? 'selected' : null,
			'%keyword%' => $this->keyword
		));
	}

	public function get_review($userId = null) {
		$results = $this->db->pdoQuery("SELECT AVG(f.averageRating) AS average, count(f.id) AS total FROM tbl_feedbacks AS f LEFT JOIN tbl_users AS u ON f.`userfrom` = u.userid WHERE f.userto = ? AND u.isactive = 'y' ", array('userTo' => $userId))->result();

		return array(
			$results['average'],
			$results['total']
		);
	}

}
?>