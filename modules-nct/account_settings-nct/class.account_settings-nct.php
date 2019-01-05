<?php
class Accountsettings {
	function __construct($module = "", $reqData = array()) {
		foreach ($GLOBALS as $key => $values) {
			$this -> $key = $values;
		}
		$this -> module = $module;		
        $this -> reqData = $reqData;
        $this -> dataOnly = (isset($this->reqData['dataOnly']) && $this->reqData['dataOnly']==true)?true:false;	
        $this->sessUserId = (isset($reqData['userId']) && $reqData['userId'] >0)?$reqData['userId']:$this->sessUserId;
        $this->sessUserType = (isset($reqData['userType']) && $reqData['userType'] != null)?$reqData['userType']:$this->sessUserType;
        if($this->dataOnly){
        	$_SESSION['lId'] = (isset($this->reqData['lId']) && $this->reqData['lId'] >0)?$this->reqData['lId']:1;	
        	setLang();	
        }
        
	}

	public function getPageContent() {
		$replace = array(
			'%noti_settings%' => $this -> get_noti_settings(),
			'%noti_type_count%' => $this -> get_noti_settings(TRUE),
			'%tokenValue%' => setFormToken()
		);
		return get_view(DIR_TMPL . $this -> module . "/" . $this -> module . ".tpl.php", $replace);
	}

	public function get_noti_settings($count = FALSE) {
		global $db;		
		//msgExit("session: ".print_r($_SESSION,true));
        $results = $db -> pdoQuery('SELECT * FROM tbl_notification_types WHERE userType = ? OR userType = "both" ',array($this->sessUserType));
        if($this->dataOnly){        	
        	$results = $results->results();
        	foreach ($results as $k => $v) {
				$isEntry = getTableValue('tbl_notification_settings', 'id', array('userId'=>$this->sessUserId,'typeId'=>$v['id']));
				$results[$k]['checked'] = ($isEntry > 0) ? false:true;
				$results[$k]['title'] = constant($v['title']);
			}
            return $results;
        }else{
            $results = $results->results();
        }

		if($count == TRUE){
			return count($results);
		}
		$html = null;
		foreach ($results as $k => $v) {
			$isEntry = getTableValue('tbl_notification_settings', 'id', array('userId'=>$this->sessUserId,'typeId'=>$v['id']));
			$html .= get_view(DIR_TMPL . $this -> module . "/checkboxes-nct.tpl.php", array(
				'%label%' => constant($v['title']),
				'%name%' => $v['id'],
				'%value%' => 1,
				'%checked%' => ($isEntry > 0)?NULL:'checked'
			));
		}

		return $html;
	}

}
?>
