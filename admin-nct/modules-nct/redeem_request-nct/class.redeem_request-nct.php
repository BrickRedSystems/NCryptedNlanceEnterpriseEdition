<?php
class Redeem extends Home {
	public $data = array();
	public function __construct($module, $id=0, $objPost=NULL, $searchArray=array(), $type='') {
		global $db, $fields, $sessCataId;
		$this->db = $db;
		$this->data['id'] = $this->id = $id;
		$this->fields = $fields;
		$this->module = $module;
		$this->table = 'tbl_redeem_requests';
		//$this->type = ($this->id > 0 ? 'edit' : 'add');
		$this->type = $type;
		$this->searchArray = $searchArray;
		parent::__construct();
		if($this->id>0){
			$fetchRes = $this->db->pdoQuery("SELECT r.*,CONCAT_WS(' ',u.firstName,u.lastName) AS userName,u.email,u.paypalEmail,u.userType
				FROM tbl_redeem_requests AS r
				LEFT JOIN tbl_users AS u on u.userId = r.userId			
				WHERE r.id=?",array($this->id))->result();

			foreach ($fetchRes as $k => $v) {
				$this->{$k} = filtering($v);
			}

		}else{
			$fetchRes= $this->db->pdoQuery("SHOW COLUMNS FROM ".$this->table)->results();
			foreach ($fetchRes as $k => $v) {
				$this->{$v["Field"]} = $v["Default"];
			}
		}
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
				$this->data['content'] = (in_array('view',$this->Permission))?$this->viewForm():'';
				break;
			}
			case 'delete' : {
				$this->data['content'] = (in_array('delete',$this->Permission))?json_encode($this->dataGrid()):'';
				break;
			}
			case 'datagrid' : {
				$this->data['content'] = (in_array('module',$this->Permission))?json_encode($this->dataGrid()):'';
				break;
			}
			case 'viewlistdata' : {
				$this->data['content'] = (in_array('module',$this->Permission))?json_encode($this->viewlistdata()):'';
				break;
			}
			case 'viewlistingrid' : {
				$this->data['content'] = (in_array('module',$this->Permission))?json_encode($this->viewlistdataGrid()):'';
				break;
			}
		}
	}


	public function viewForm(){
		$content = $this->displayBox(array("label"=>"User Name&nbsp;:","value"=>$this->userName)).
		$this->displayBox(array("label"=>"User Type&nbsp;:","value"=>($this->userType=='p'?'Provider':'Customer'))).
		$this->displayBox(array("label"=>"Email&nbsp;:","value"=>$this->email)).
		$this->displayBox(array("label"=>"PayPal ID&nbsp;:","value"=>$this->paypalEmail)).		
		$this->displayBox(array("label"=>"Amount&nbsp;:","value"=>CURRENCY_SYMBOL.$this->amount)).
		$this->displayBox(array("label"=>"Requested Date&nbsp;:","value"=>date(PHP_DATE_FORMAT,strtotime($this->createdDate)))).
		$this->displayBox(array("label"=>"Description&nbsp;:","value"=>$this->description)).
		$this->displayBox(array("label"=>"Request status &nbsp;:","value"=>$this->paymentStatus));
		return $content;
	}
	public function getSelectBoxOption(){
		$content = '';
		$selectBoxOption = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/select_option-nct.tpl.php");
		$selectBoxOption = $selectBoxOption->parse();
		return sanitize_output($selectBoxOption);
	}
	public function getForm() {
		$content = $page_option = $genre_option = $lang_option = $static_a = $static_d = '';
		$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/form-nct.tpl.php");
		$main_content = $main_content->parse();

		$getSelectBoxOption=$this->getSelectBoxOption();
		$fields = array("%VALUE%","%SELECTED%","%DISPLAY_VALUE%");

		$qrySelCountry=$this->db->pdoQuery("SELECT id,countryName FROM tbl_country where status='y' ORDER BY countryName ASC")->results();
		foreach ($qrySelCountry as $fetchRes) {
			$selected = ($this->country_id==$fetchRes['id'])?"selected":"";

			$fields_replace = array($fetchRes['id'],$selected,$fetchRes['countryName']);
			$page_option.=str_replace($fields,$fields_replace,$getSelectBoxOption);
		}

		$qrySelGenre = $this->db->pdoQuery("SELECT id,genre_name FROM tbl_genres where isactive='y' ORDER BY genre_name ASC")->results();
		foreach ($qrySelGenre as $fetchRes) {
			$selected = ($this->genreId==$fetchRes['id'])?"selected":"";

			$fields_replace = array($fetchRes['id'],$selected,$fetchRes['genre_name']);
			$genre_option.=str_replace($fields,$fields_replace,$getSelectBoxOption);
		}

		$qrySelLanguage = $this->db->pdoQuery("SELECT id,lang_name FROM tbl_languages where isactive='y' ORDER BY lang_name ASC")->results();
		foreach ($qrySelLanguage as $fetchRes) {
			$selected = ($this->langId==$fetchRes['id'])?"selected":"";

			$fields_replace = array($fetchRes['id'],$selected,$fetchRes['lang_name']);
			$lang_option.=str_replace($fields,$fields_replace,$getSelectBoxOption);
		}

		$static_a = ($this->isactive == 'y' ? 'checked':'');
		$static_d = ($this->isactive != 'y' ? 'checked':'');

		$profile = checkImageExist('user/'.$this->id.'/',$this->imagename);

		$fields = array("%FIRSTNAME%",
						"%LASTNAME%",
						"%STATUS_A%",
						"%STATUS_D%",
						"%TYPE%",
						"%ID%",
						"%COUNTRIES_OPTION%",
						"%GENRES_OPTION%",
						"%LANGUAGES_OPTION%",
						"%OUTPUTPROFILEIMG%",
						"%OLD_IMAGE%",
						"%SITE_PROFILEIMG%",
						"%DIR_PROFILEIMG%"
					);
		$fields_replace = array($this->data['firstName'],
								$this->data['lastName'],
								$static_a,
								$static_d,
								$this->type,
								$this->id,
								$page_option,
								$genre_option,
								$lang_option,
								$profile,
								$this->imagename,
								SITE_PROFILEIMG.$this->id.'/',
								DIR_PROFILEIMG.$this->id.'/'
							);
		$content=str_replace($fields,$fields_replace,$main_content);
		return sanitize_output($content);
	}

	public function dataGrid() {
		$content = $operation = $whereCond = $totalRow = NULL;
		$result = $tmp_rows = $row_data = array();
		extract($this->searchArray);
		$whereStr = '';

		if(!empty($userType) && $userType>0) {
			$whereStr .= (empty($whereStr)?" WHERE ":" AND ")."u.userType='".$userType."'";
		}

		if(!empty($status_filter)) {
			$whereStr .= (empty($whereStr)?" WHERE ":" AND ")."r.paymentStatus='".$status_filter."'";
		}

		if(isset($chr) && $chr != '') {
			$chr = str_replace(array('_', '%',"'"), array('\_', '\%',"\'"),$chr );			
			$chr = strtolower(str_replace("=", "", ($chr)));
			$whereStr .= (empty($whereStr)?" WHERE ":" AND ")."(LOWER(u.email) LIKE '%".$chr."%' OR userName LIKE '%".$chr."%' OR r.paypalId LIKE '%".$chr."%' OR r.amount LIKE '%".$chr."%')";
		}

		if(isset($sort))
			$sorting = $sort.' '. $order;
		else
			$sorting = 'r.id DESC';

		$qrySel = $this->db->pdoQuery("
                SELECT r.*, CONCAT(u.firstName, ' ', u.lastName) AS userName, u.email, ph.transactionId
                FROM tbl_redeem_requests AS r
                LEFT JOIN tbl_users AS u on u.userId = r.userId
                LEFT JOIN tbl_payment_history as ph on ph.membershipId = r.id 
                " . $whereStr . " order by " . $sorting . " limit " . $offset . " ," . $rows . " ")->results();
        $totalRow = count($qrySel);
		
		foreach($qrySel as $fetchRes) {
			$count = 0;
		
			if($fetchRes['paymentStatus']=='pending') {
				$pay = $this->operation(array("href"=>"ajax.".$this->module.".php?action=ApproveRedeem&id=".$fetchRes['id']."","class"=>"btn default btn-xs green btn-approveRedeem","value"=>'<i class="fa fa-money"></i>&nbsp;Pay'));
			} else if($fetchRes['paymentStatus']=='initiated') {
				$pay = $this->operation(array("href"=>"javascript:void(0);","class"=>"btn default btn-xs green","value"=>'<i class="fa fa-retweet"></i>&nbsp;Initiated'));
			} else {
				$pay = $this->operation(array("href"=>"javascript:void(0);","class"=>"btn default btn-xs green","value"=>'<i class="fa fa-check"></i>&nbsp;Paid'));
			}
			$operation = NULL;
			$operation .=(in_array('delete',$this->Permission))?'&nbsp;&nbsp;'.$this->operation(array("href"=>"ajax.".$this->module.".php?action=delete&id=".$fetchRes['id']."","class"=>"btn default btn-xs red btn-delete","value"=>'<i class="fa fa-trash-o"></i>&nbsp;Delete')):NULL;
			$operation .=(in_array('view',$this->Permission))?'&nbsp;&nbsp;'.$this->operation(array("href"=>"ajax.".$this->module.".php?action=view&id=".$fetchRes['id']."","class"=>"btn default blue btn-xs btn-viewbtn","value"=>'<i class="fa fa-laptop"></i>&nbsp;View')):NULL;
			$final_array =  array(
				$fetchRes['id'],
				(!empty($fetchRes['userName'])) ? $fetchRes['userName']:'N/A',
				(isset($fetchRes["email"])) && !empty($fetchRes["email"]) ? ($fetchRes["email"]):'N/A',
				(isset($fetchRes["paypalId"])) && !empty($fetchRes["paypalId"]) ? ($fetchRes["paypalId"]):'N/A',
				(!empty($fetchRes['amount'])?CURRENCY_SYMBOL.$fetchRes['amount']:'N/A'),
				(!empty($fetchRes['createdDate'])? date(PHP_DATE_FORMAT,strtotime($fetchRes['createdDate'])):'N/A'),
				(!empty($fetchRes['transactionId']) ? $fetchRes['transactionId'] : 'N/A'),
				$pay,
				$operation
			);
			$row_data[] = array_filter($final_array,function ($val){
			    return !is_null($val);
			});	

		}
		$result = array();
		$result["sEcho"]=$sEcho;
		$result["iTotalRecords"] = (int)$totalRow;
		$result["iTotalDisplayRecords"] = (int)$totalRow;
		$result["aaData"] = $row_data;		
		return $result;
	}

	public function select_option($text){
		$text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['selected'] = isset($text['selected']) ? ''.trim($text['selected']) : '';
        $text['display_value'] = isset($text['display_value']) ? $text['display_value'] : '';

		$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module.'/select_option-nct.tpl.php');
		$main_content=$main_content->parse();
		$fields = array("%VALUE%", "%SELECTED%", "%DISPLAY_VALUE%");
		$fields_replace = array($text['value'],$text['selected'],$text['display_value']);
		return str_replace($fields,$fields_replace,$main_content);
	}

	public function displaybox($text){
 		$text['label'] = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
		$text['value'] = isset($text['value']) ? $text['value'] : '';
		$text['name'] = isset($text['name']) ? $text['name'] : '';
		$text['class'] = isset($text['class']) ? 'form-control-static '.trim($text['class']) : 'form-control-static';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

		$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module.'/displaybox.tpl.php');
		$main_content=$main_content->parse();
		$fields = array("%LABEL%","%CLASS%","%VALUE%");
		$fields_replace = array($text['label'],$text['class'],$text['value']);
		return str_replace($fields,$fields_replace,$main_content);
	}

	public function toggel_switch($text){
		$text['action'] = isset($text['action']) ? $text['action'] : 'Enter Action Here: ';
		$text['check'] = isset($text['check']) ? $text['check'] : '';
		$text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? ''.trim($text['class']) : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

		$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module.'/switch-nct.tpl.php');
		$main_content=$main_content->parse();
		$fields = array("%NAME%","%CLASS%","%ACTION%","%EXTRA%","%CHECK%");
		$fields_replace = array($text['name'],$text['class'],$text['action'],$text['extraAtt'],$text['check']);
		return str_replace($fields,$fields_replace,$main_content);
	}
	public function operation($text){

		$text['href'] = isset($text['href']) ? $text['href'] : 'Enter Link Here: ';
		$text['value'] = isset($text['value']) ? $text['value'] : '';
		$text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? ''.trim($text['class']) : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
		$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module.'/operation-nct.tpl.php');
		$main_content=$main_content->parse();
		$fields = array("%HREF%","%CLASS%","%VALUE%","%EXTRA%");
		$fields_replace = array($text['href'],$text['class'],$text['value'],$text['extraAtt']);
		return str_replace($fields,$fields_replace,$main_content);
	}
	public function getPageContent(){
		$final_result = NULL;
		$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/".$this->module.".tpl.php");
		$main_content->breadcrumb = $this->getBreadcrumb();
		$final_result = $main_content->parse();
		return $final_result;
	}

	public function getPayPalRedeemLink($id) {
		$data = array('code'=>'100', 'msg'=>'User has not entred paypal email');
		$redeem_details = $this->db->pdoQuery('
			SELECT r.*, u.paypalEmail, u.walletamount 
			FROM tbl_redeem_requests AS r INNER JOIN tbl_users AS u ON(u.userId=r.userId) 
			WHERE r.id=? AND r.paymentStatus=?', array($id, 'pending'))->result();

		if($redeem_details['walletamount'] >= $redeem_details['amount']) {
			if(!empty($redeem_details['paypalId']) && !empty($redeem_details['amount'])) {

	            /*  calculate wallet amount
	                give wallet amount by subtracting admin commission from paid amount
	                walletAmount = mc_gross - (mc_gross * admin percent for redeem/ 100)
	            */
	            $calculatedAdminCommission = $redeem_details['amount'] * REDEEM_COMMISSION / 100 ;
	            $calculatedAmountToPay = $redeem_details['amount'] - $calculatedAdminCommission;

				//make entry of pending transaction			
				$payment_history_id = $this->db->insert('tbl_payment_history', array('userId' => $redeem_details['userId'],'paymentType'=>'redeem', 'membershipId' => $id, 'createdDate' => date('Y-m-d H:i:s'), 'adminCommission'=>(string)$calculatedAdminCommission))->lastInsertId();
				
				// Paypal
		       $en_action = base64_encode('redeem');
		        $en_action_cancel = '/'.base64_encode($payment_history_id);
		        $url_paypal  = PAYPAL_URL;
		        $url_paypal .= "?business=".urlencode($redeem_details['paypalId']);
		        $url_paypal .= "&cmd=".urlencode('_xclick');
		        $url_paypal .= "&item_name=Redeem Amount to user - ".urlencode(SITE_NM);
		        $url_paypal .= "&item_number=".urlencode($id);
		        $url_paypal .= "&custom=".urlencode($payment_history_id.'__'.$redeem_details['userId'].'__'.$redeem_details['id'].'__'.$calculatedAdminCommission.'__'.$redeem_details['amount']);
		        $url_paypal .= "&amount=".urlencode($calculatedAmountToPay);
		        $url_paypal .= "&currency_code=".urlencode(PAYPAL_CURRENCY_CODE);
		        $url_paypal .= "&handling=".urlencode('0');
		        $url_paypal .= "&rm=2";
		        $url_paypal .= "&bn=".urlencode('NCryptedTechnologies_SP_EC');
		        $url_paypal .= "&return=".urlencode(get_link('paypal_thankyou', $en_action));
		        $url_paypal .= "&cancel_return=".urlencode(get_link('paypal_failed', $en_action.$en_action_cancel));
		        $url_paypal .= "&notify_url=".urlencode(get_link('paypal_notify', $en_action));
		        $data['code'] = '200';
		        $data['url'] = $url_paypal;
		        
			}
		} else {
			$data['msg'] = "Currently user doesn't have sufficient balance to redeem";
		}

		return $data;
	}
}