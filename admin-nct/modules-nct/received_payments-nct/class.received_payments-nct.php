<?php
class ReceivedPayments extends Home{

	public function __construct($module, $id=0, $objPost=NULL, $searchArray=array(), $type='') {	
		global $db, $fb, $sessCataId;		
		$this->fb = $fb;
$this->db = $db;
		$this->module = $module;
		$this->table = 'tbl_payment_history';
		$this->type = $type;
		$this->searchArray = $searchArray;
		parent::__construct();
		
		switch($type){
			case 'add' : {
				$this->data['content'] =  $this->getForm();
				break;
			}
			case 'edit' : {
				$this->data['content'] =  $this->getForm();
				break;
			}
			case 'view' : {
				$this->data['content'] =  $this->viewForm();
				break;
			}
			case 'delete' : {
				$this->data['content'] =  json_encode($this->dataGrid());
				break;
			}
			case 'datagrid' : {
				$this->data['content'] =  json_encode($this->dataGrid());
			}
		}
	}
	public function viewForm(){
		$content = '';
		return $content;
	}
	public function getForm() {
		$content = '';
		return sanitize_output($content);
	}
	public function dataGrid() {
		$content = $operation = $whereCond = $totalRow = NULL;
		$result = $tmp_rows = $row_data = array();
		extract($this->searchArray);
		//note exclude redeem and project payments instructed by purvi ma'am
		$whereStr = ' WHERE p.paymentType != "redeem" AND p.paymentType != "project payment" AND p.paymentStatus = "completed" ';
		
		if(!empty($userType) && $userType!='') {
			$whereStr .= (empty($whereStr)?" WHERE ":" AND ")."u.userType='".$userType."'";
		}
		
		if(isset($chr) && $chr != '') {
			$chr = str_replace(array('_', '%', "'"), array('\_', '\%', "\'"), $chr);
			$chr = strtolower(str_replace("=", "", ($chr)));
			$whereStr .= (empty($whereStr)?" WHERE ":" AND ")."(p.paymentStatus LIKE '%".$chr."%' OR p.paymentType LIKE '%".$chr."%' OR p.totalAmount LIKE '%".$chr."%' OR CONCAT(u.firstName,' ',u.lastName) LIKE '%".$chr."%' OR p.transactionId LIKE '%".$chr."%' OR p.paypal_fees LIKE '%".$chr."%')";
			
		}
		if(isset($sort)){
			if($sort == "paymentType"){
        		$sorting = ' LPAD(p.paymentType, 3, 0)'. ' ' . $order;
        	}else{
				$sorting = $sort.' '. $order;
			}
		}else {
			$sorting = 'p.id DESC';
		}
		$totalRowTmp = $this->db->pdoQuery("SELECT COUNT(p.id) AS nmrows
				FROM tbl_payment_history AS p
				LEFT JOIN tbl_users AS u ON u.userId = p.userId				
				".$whereStr." ")->result();
		$totalRow = $totalRowTmp['nmrows'];

		$qrySel = $this->db->pdoQuery("SELECT p.id, p.paymentType, 
			CASE 
				WHEN p.paymentType = 'deposit to wallet' THEN p.adminCommission 
				WHEN p.paymentType = 'escrow' THEN p.totalAmount 
				WHEN p.paymentType = 'adhoc purchase' THEN p.totalAmount
				WHEN p.paymentType = 'buy membership' THEN p.totalAmount
				WHEN p.paymentType = 'featured' THEN p.totalAmount
				WHEN p.paymentType = 'redeem' THEN p.adminCommission 
				WHEN p.paymentType = 'project payment' THEN p.adminCommission 
				ELSE 0 
			END AS totalAmount, 
			p.adminCommission, p.paypal_fees, p.transactionId, p.createdDate, CONCAT(u.firstName,' ',u.lastName) AS userName, u.userType
				FROM tbl_payment_history AS p
				LEFT JOIN tbl_users AS u ON u.userId = p.userId
				".$whereStr." order by ".$sorting." limit ".$offset." ,".$rows." ")->results();
		
		foreach($qrySel as $fetchRes) {
			$final_array =  array(
				!empty($fetchRes["id"]) ? $fetchRes["id"] :0,
				!empty($fetchRes['userName']) ? $fetchRes['userName'] :'N/A',
				($fetchRes['userType']=='p') ? 'Provider':'Customer',					
				!empty($fetchRes["totalAmount"]) ? $fetchRes["totalAmount"] :0,
				!empty($fetchRes["adminCommission"]) ? (CURRENCY_SYMBOL.$fetchRes["adminCommission"]):0,
				!empty($fetchRes["paypal_fees"]) ? ($fetchRes["paypal_fees"]):0,
				(!empty($fetchRes['paymentType'])?ucfirst(strtolower(stripslashes($fetchRes["paymentType"]))):'N/A'),
				(!empty($fetchRes['transactionId'])?$fetchRes['transactionId']:'N/A'),
				(!empty($fetchRes['createdDate'])? date(PHP_DATE_FORMAT,strtotime($fetchRes['createdDate'])):'N/A')
			);
			$row_data[] = $final_array;
		}
		$result = array();
		$result["sEcho"]=$sEcho;
		$result["iTotalRecords"] = (int)$totalRow;
		$result["iTotalDisplayRecords"] = (int)$totalRow;
		$result["aaData"] = $row_data;
		return $result;
	}
	public function getPageContent(){		
		$final_result = NULL;		
		$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/".$this->module.".tpl.php");
		$main_content->breadcrumb = $this->getBreadcrumb();
		$main_content->Permission = $this->Permission;
		$final_result = $main_content->parse();
		return $final_result;
	}
}