<?php
require_once "../../includes-nct/config-nct.php";
require_once "class.pms-nct.php";
global $db, $sessUserId;
$memberId = $sessUserId;
$final_data = array();

$obj = new Message($module);

if (isset($_REQUEST["action"])) {
	extract($_REQUEST);
	if ($action == "get-conversation") {
		if($user_id!=""){
			$detail = $db->select('tbl_users',array('firstName','lastName'),array('id'=>$user_id))->result();
			$final_data['uname'] = $detail['firstName'].' '.$detail['lastName'];
			$final_data['uId'] = $user_id;
			if($tab == "inbox"){
			$final_data['result'] = $obj->inboxMessages($user_id);
			
			}
			$final_data['code'] = 200;
			$final_data['msg'] = -1;
		}else{
			$final_data['code'] = 300;
			$final_data['msg'] = "Sorry! Invalid request.";
		}
	}else if ($action == "prmnt-delete-msg") {
		if($item_id!=""){
			$delete_item = $db->select('tbl_msg_deleted',array('*'),array('id'=>$item_id,'userId'=>$memberId));
			$check = $delete_item->affectedRows();
			if($check > 0){
					$msgId = $delete_item->result();
					$db->update('tbl_msg_deleted',array('prmnt_flag'=>"1"),array("userId"=>$memberId,"id"=>$item_id));
					$msg = $db->select('tbl_msg',array('*'),array('id'=>$msgId['msgId']))->result();
					if($msg['senderId'] == $memberId){
						$user_id = $msg['receiverId'];
					}else if($msg['receiverId'] == $memberId){
						$user_id = $msg['senderId'];
					}
				
				$detail = $db->select('tbl_user',array('firstName','lastName'),array('id'=>$user_id))->result();
				$final_data['uname'] = $detail['firstName'].' '.$detail['lastName'];
				$final_data['uId'] = $user_id;
				
				/*delete item list*/
				$final_data['delete_result'] = deleteMessages($user_id);
				$final_data['code'] = 200;
				$final_data['msg'] = "Message has been deleted permanently.";
			}else{
			$final_data['code'] = 300;
			$final_data['msg'] = "Sorry! Invalid request.";
			}
		}else{
			$final_data['code'] = 300;
			$final_data['msg'] = "Sorry! Invalid request.";
		}
	}else if ($action == "message-replay") {

		if($receiverId!=""){
			$db->insert('tbl_msg',array("senderId"=>$memberId,"receiverId"=>$receiverId,"msgDesc"=>$replay_message,"createdDate"=>date("Y-m-d H:i:s")));
			$final_data['result'] = $obj->inboxMessages($receiverId);
			//$final_data['dlt_btn'] = deleteConvView($receiverId);
			$final_data['code'] = 200;
			$final_data['msg'] = "Message has been sent successfully.";
		}else{
			$final_data['code'] = 300;
			$final_data['msg'] = "Sorry! Invalid request.";
		}
		
	}else {
		$final_data["code"] = 300;
		$final_data["msg"] = "Sorry! Invalid request";
	}
	
} else {
	$final_data["msg"] = "Sorry! You have no rights to access this page.";
	$final_data["code"] = 100;
}
echo json_encode($final_data);
exit;
?>