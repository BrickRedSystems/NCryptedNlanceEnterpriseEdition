<?php
class Messages {
	function __construct($module = "", $id = 0, $reqData = array()) {
		global $js_variables;
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
		$this->reqData = $reqData;
		$this->table = "tbl_users";

		//for web service
        $this -> dataOnly = (isset($this->reqData['dataOnly']) && $this->reqData['dataOnly']==true)?true:false;
        $this -> sessUserId = (isset($reqData['userId']) && $reqData['userId'] >0)?$reqData['userId']:$this -> sessUserId;
        if($this->dataOnly){
        	$_SESSION['lId'] = (isset($this->reqData['lId']) && $this->reqData['lId'] >0)?$this->reqData['lId']:1;	
        	setLang();	
        }
	}
	
	public function markRead(){
		if(isset($this->reqData['info']) && $this->reqData['info'] > 0){
			$senderId = (int)$this->reqData['info'];
			$affectedRows = $this->db->update('tbl_messages',array('readStatus'=>'y'),array('senderId'=>$senderId,'receiverId'=>$this->sessUserId))->affectedRows();
			return json_encode(array('status'=>1,'msg'=>'undefined'));
		}
		
	}

	public function left() {
		$html = null;
		$results = $this->db->pdoQuery('SELECT SUM(m.readStatus = "n") AS tot_unread, SUM(m.readStatus = "y") AS tot_read, m.`senderId`, u.profilePhoto, CONCAT_WS(" ", u.firstName, u.lastName) AS fullName FROM tbl_messages AS m LEFT JOIN tbl_users AS u ON m.`senderId` = u.`userId` WHERE u.`isActive` = "y" AND m.`receiverId` = ? GROUP BY m.`senderId` ORDER BY m.id DESC ', array($this->sessUserId));

		if($this->dataOnly)
		{
			return $results->results();
			exit;
		}
		else
		{
			$results = $results->results();
		}

		if (!empty($results)) {
			$i = 0;
			foreach ($results as $key => $value) {
				extract($value);
				$html .= get_view(DIR_TMPL . $this->module . "/left-row-nct.tpl.php", array(
					'%profilePhoto%' => tim_thumb_image($profilePhoto, 'profile', 75, 75),
					'%fullName%' => $fullName,
					'%no_of_unread_msgs%' => $tot_unread,
					'%senderId%' => $senderId,
					'%activeClass%' => ($i == 0)? 'active':null
				));
				$i++;
			}
		}
		return $html;
	}

	public function right() {
		if(isset($this->reqData['info']) && $this->reqData['info'] > 0){
			$senderId = (int)$this->reqData['info'];
		}else{
			$lastmessager = $this->db->pdoQuery('SELECT SUM(m.readStatus = "n") AS tot_unread, SUM(m.readStatus = "y") AS tot_read, m.`senderId`, u.profilePhoto, CONCAT_WS(" ", u.firstName, u.lastName) AS fullName FROM tbl_messages AS m LEFT JOIN tbl_users AS u ON m.`senderId` = u.`userId` WHERE u.`isActive` = "y" AND m.`receiverId` = ? GROUP BY m.`senderId` ORDER BY m.id DESC LIMIT 1 ', array($this->sessUserId))->result();

			if($this->dataOnly)
			{
				$senderId = $this->reqData['senderId'];
			}
			else
			{
				$senderId = $lastmessager['senderId'];
			}
		}
		
		$Msgs = $this->db->pdoQuery("SELECT m.*, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName FROM tbl_messages AS m LEFT JOIN tbl_users AS u ON m.senderId = u.userId WHERE ((m.senderId = ? AND m.`receiverId`=?) OR (m.senderId = ? AND m.`receiverId`=?)) ORDER BY m.id ASC ", array(
			$senderId,
			$this->sessUserId,
			$this->sessUserId,
			$senderId
		));

		if($this->dataOnly)
		{
			return $Msgs->results();
			exit;
		}
		else
		{
			$Msgs = $Msgs->results();
		}


		$html = null;
		if (!empty($Msgs)) {
			foreach ($Msgs as $k => $v) {
				extract($v);
				$replace = array(
					'%firstName%' => ucwords($fullName),
					'%class%' => ($this->sessUserId == $senderId) ? 'my-chat' : 'other-chat',
					'%desc%' => nl2br(filtering($description)),
					'%time%' => date(PHP_DATETIME_FORMAT, strtotime($createdDate)),
				);
				$html .= get_view(DIR_TMPL . $this->module . "/message_row-nct.tpl.php", $replace);
			}
		}
		else {
			$btn = '
                <p>
                    '.There_are_no_messages_in_this_conversation.'
                </p>';
			$replace = array('%msg%' => $btn, );
			$html .= get_view(DIR_TMPL . $this->module . "/no_message_row-nct.tpl.php", $replace);
		}
		return $html;
	}

	public function getPageContent() {
		extract($this->userData());
		//mark all notifications as read
		//$this->db->update('tbl_notification',array('isReaded'=>'y'),array('toUserId'=>$this->sessUserId));

		$replace = array(
			'%left%' => $this->left(),
			'%right%' => $this->right()
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
					'jobStatus' => 'open',
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
					'jobStatus' => 'open',
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
