<?php
class Message {
	function __construct($module = "", $id = 0) {
		foreach ($GLOBALS as $key => $values) {
			$this->$key = $values;
		}
		$this->module = $module;
		$this->id = $id;
	}
	public function getPageContent() {
		$html = html(DIR_TMPL . "{$this->module}/{$this->module}.tpl.php");
		$conv_userId = $this->getDefaultChat();
		$top_links = html(DIR_TMPL . "$this->module/top_links.tpl.php");
		$inbox = $this->inbox_content();
		
		return str_replace(array("%conv_userId%","%top_links%","%inbox%"),array($conv_userId,$top_links,$inbox),$html);
	}
		
	public function inbox_content() {
		$html = html(DIR_TMPL . "{$this->module}/inbox.tpl.php");
		$connection_list  = $this->getConnectionList();
		$userId = $this->getDefaultChat();
		$user_detail = $this->db->select('tbl_users',array('firstName','lastName'),array('id'=>$userId))->result();
		$userName = $user_detail['firstName'].' '.$user_detail['lastName'];
		$receiverId = $userId;
		$message = $this->inboxMessages($userId);
		//$html->isDelete = deleteConvView($userId);
		
		return str_replace(array("%connection_list%","%userName%","%receiverId%","%message%"),array($connection_list,$userName,$receiverId,$message),$html);
	}
	public function getDefaultChat(){
		$result = $this->db->pdoQuery("SELECT * FROM tbl_users where status='a' and id!=".$this->sessUserId." order by firstName LIMIT 1" );
		$tot_con = $result->affectedRows();
		
		if($tot_con>0){
			$connections = $result->result();
			return $connections['id'];
		}else{
			return 0;
		}
		
	}
	public function getConnectionList(){
		
		$result = $this->db->pdoQuery("SELECT * FROM tbl_users where status='a' AND id!=".$this->sessUserId." order by firstName" );
		$tot_con = $result->affectedRows();
		$i = 1;
		ob_start();
		if($tot_con>0){
			$final_data = "";
			$connections = $result->results();
			foreach ($connections as $connection) {
				extract($connection);
				
				$type = '';
				$detail_url =  SITE_URL.'profile/'.$firstName;
				$html = html(DIR_TMPL . "pms-nct/conn_list-nct.tpl.php");
					$CLASS = $i==1?"active":"";	
					$ID = $id;
					$FULLNAME = $firstName.' '.$lastName;
					
					if($profile_img!="" && file_exists(DIR_UPD.'profile/th1_'.$profile_img)){
						$ICON = SITE_UPD.'profile/th1_'.$profile_img;
					}else{
						$ICON = SITE_UPD.'th2_no_user_image.png';
					}
					$TYPE = $type;
					$PERMALINK = $detail_url;
					echo str_replace(array("%CLASS%","%ID%","%FULLNAME%","%ICON%","%TYPE%","%PERMALINK%"),array($CLASS,$ID,$FULLNAME,$ICON,$TYPE,$PERMALINK),$html);
					$i++;
			}
				
		}else{
			
		}
		return ob_get_clean();
	}
	public function inboxMessages($userId){
	
	   ob_start();
		$result = $this->db->pdoQuery("SELECT * FROM tbl_msg WHERE (CASE WHEN senderId=".$this->sessUserId." THEN receiverId=".$userId." WHEN receiverId=".$this->sessUserId." THEN senderId = ".$userId." END)  ORDER BY createdDate");
		$tot_msgs = $result->affectedRows();
		$delete_count = 0;
		if($tot_msgs>0){
			
			$msgs = $result->results();
			foreach ($msgs as $msg) {
				 $check_delete = 1;
				if($check_delete < 0){
					
					 $delete_count++;
				}else{

				$detail = $this->db->select('tbl_users',array('*'),array('id'=>$msg['senderId']))->result();
				$detail_url =  SITE_URL.'profile/'.$detail['firstName'];
				if($detail['profile_img']!="" && file_exists(DIR_UPD.'profile/th1_'.$detail['profile_img'])){
					$icon = SITE_UPD.'profile/th1_'.$detail['profile_img'];
				}else{
					$icon = SITE_UPD.'th2_no_user_image.png';
				}
				$sentMsg_html = html(DIR_TMPL . "pms-nct/my_chat.tpl.php");
				echo str_replace(
				array("%id%","%msg_id%","%username%","%icon%","%msgDesc%","%time%","%class1%","%class2%"),
				array($detail['id'],$msg['id'],$detail['firstName'].' '.$detail['lastName'],$icon,$msg['msgDesc'],getTime($msg['createdDate']),
					($msg['senderId'] == $this->sessUserId) ? "my-chat-main" : "other-chat-main",
					($msg['senderId'] == $this->sessUserId) ? "my-chat" : "other-chat"
					),
				$sentMsg_html);
				}
			}
		}else{
			echo "<h4>No any message found!</h4>";
		}
		return ob_get_clean();
	}
	
	
}

?>
