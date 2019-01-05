<?php
class Notifications {
	function __construct($module = "", $id = 0, $reqData = array()) {
		global $js_variables;
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
		$this->reqData = $reqData;
		$this->table = "tbl_users";
		$this->user = $this->userData();
		$js_variables = ($this->user['userType'] == "p") ? "var method ='reviews_rows'" : "var method ='project_rows'";
		
	}


	public function notification_rows() {
		$results = $this->db->pdoQuery("SELECT n.* FROM tbl_notification AS n WHERE n.to=? ORDER BY n.id DESC", array($this->sessUserId))->results();
		$html = null;
		if (!empty($results)) {
			foreach ($results as $k => $v) {
				$replace = array(
					'%notification%' => filtering($v['notification']),
					'%createdDate%' => date('d-M-Y',$v['createdDate'])
				);
				$html .= get_view(DIR_TMPL . $this->module . "/noti_row-nct.tpl.php", $replace);
			}
		}
		else {
			$html = get_view(DIR_TMPL . $this->module . "/no_noti_row-nct.tpl.php");
		}
		return $html;
	}

	public function getPageContent() {
		$user = $this->user;
		// note... if both profile type are different then hide contact details else show them

		$replace = array(
			
			'%notifications%' => $this->notification_rows()
		);

		return get_view(DIR_TMPL . $this->module . "/" . $this->module . ".tpl.php", $replace);
	}

	public function userData() {
		$doesExist = getTableValue($this->table, 'userId', array(
			'userId' => $this->sessUserId,
			'isActive' => 'y'
		));
		if (isset($doesExist) && $doesExist > 0) {
			$arr = array();
			$arr = $this->db->pdoQuery("SELECT u.*, CONCAT_WS(' ',u.firstName,u.lastName) AS fullName FROM tbl_users AS u WHERE u.userId=?", array($this->sessUserId))->result();
			if ($arr['userType'] == 'c') {
				$arr['ongoing'] = getTableValue('tbl_projects', 'count("id")', array(
					'userId' => $arr['userId'],
					'jobStatus' => 'progress',
					'isActive' => 'y'
				));
				$arr['completed'] = getTableValue('tbl_projects', 'count("id")', array(
					'userId' => $arr['userId'],
					'jobStatus' => 'completed',
					'isActive' => 'y'
				));
			}
			else {
				$arr['ongoing'] = getTableValue('tbl_projects', 'count("id")', array(
					'providerId' => $arr['userId'],
					'jobStatus' => 'progress',
					'isActive' => 'y'
				));
				$arr['completed'] = getTableValue('tbl_projects', 'count("id")', array(
					'providerId' => $arr['userId'],
					'jobStatus' => 'completed',
					'isActive' => 'y'
				));
			}
			return $arr;
		}
		else {
			$_SESSION["msgType"] = disMessage(array(
				'type' => 'err',
				'var' => toastr_url_not_found
			));
			redirectPage(SITE_URL);
		}
	}

}
?>
