<?php
class City extends Home{
	
	public $status;
	public $data = array();
	
	public function __construct($id=0, $searchArray=array(), $type=''){		
		global $db, $fb, $fields, $sessCataId,$module;		
		$this->fb = $fb;
$this->db = $db;
		$this->data['id'] = $this->id = $id;
		$this->fields = $fields;
		$this->module = $module;
		$this->table = 'tbl_city';	
	
		$this->type = ($this->id > 0 ? 'edit' : 'add');
		$this->searchArray = $searchArray;
		parent::__construct();		
		if($this->id>0){
			$qrySel = $this->db->select($this->table, array("CityId","stateId","countryId","cityName","TimeZone","isActive"),array("CityId"=>$id))->result();
			$fetchRes = $qrySel;
			$this->data['id'] = $this->id = $fetchRes['CityId'];
			$this->data['stateId'] = $this->stateId = $fetchRes['stateId'];
			$this->data['countryId'] = $this->countryId = $fetchRes['countryId'];
			$this->data['cityName'] = $this->cityName = $fetchRes['cityName'];
			$this->data['timeZone'] = $this->timeZone = $fetchRes['TimeZone'];
			$this->data['isActive'] = $this->status = $fetchRes['isActive'];
		}else{
			$this->data['cityName'] = $this->cityName = '';
			$this->data['timeZone'] = $this->timeZone = '';
			$this->data['id'] = $this->id = '';
			$this->data['stateId'] = $this->stateId = 0;
			$this->data['countryId'] = $this->countryId = 0;
			$this->data['isActive'] = $this->status = 'a';
		}
		switch($type){
			case 'add' : {
				$this->data['content'] = (in_array('add',$this->Permission))?$this->getForm():'';
				break;
			}
			case 'edit' : {
				$this->data['content'] = (in_array('edit',$this->Permission))?$this->getForm():'';
				break;
			}
			case 'view' : {
				$this->data['content'] =  '';
				break;
			}
			case 'delete' : {
				$this->data['content'] =  (in_array('delete',$this->Permission))?json_encode($this->dataGrid()):'';
				break;
			}
			case 'datagrid' : {
				$this->data['content'] =   (in_array('module',$this->Permission))?json_encode($this->dataGrid()):'';
			}
		}
	}
	public function getForm() {
		$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/form-nct.tpl.php");
		$main_content = $main_content->parse();
		$status_a=($this->status == 'y' ? 'checked':'');
		$status_d=($this->status != 'y' ? 'checked':'');
		$fields = array("%OPTION_C%","%OPTION_S%","%CITY_NAME%","%STATUS_A%","%STATUS_D%","%TYPE%","%ID%");
		$fields_replace = array($this->getCountry($this->countryId),$this->getState($this->stateId,$this->countryId),$this->cityName,$status_a,$status_d,$this->type,$this->id);

		$content=str_replace($fields,$fields_replace,$main_content);
		return sanitize_output($content);
	}
	
	public function dataGrid() {
		$content = $operation = $whereCond = $totalRow = NULL;
		$result = $tmp_rows = $row_data = array();
		extract($this->searchArray);
		$chr = str_replace(array('_', '%'), array('\_', '\%'),$chr );
		
		$aWhere = array();
		$sWhere = ' WHERE 1';
		if(isset($chr) && $chr != '') {
			$sWhere .= " AND (ct.cityName LIKE ? OR s.stateName LIKE ?)";
			$aWhere[] = "%$chr%";
			$aWhere[] = "%$chr%";
		}
		
		if(isset($sort)){
			//$sorting = (in_array($sort,array('stateName')) ? 's.' : 'c.').$sort.' '. $order;
			$alias = '';
			if($sort == 'stateName'){
				$alias = 's.';
			}else if($sort == 'cityName'){
				$alias = 'ct.';
			}else if($sort == 'countryName'){
				$alias = 'c.';
			}
			$sorting = $alias.$sort.' '. $order;

		}else{
			 $sorting = 'ct.CityId DESC';
		}

		$totalRowTmp = $this->db->pdoQuery("SELECT COUNT(ct.CityId) AS nmrows FROM tbl_city AS ct INNER JOIN tbl_state AS s ON ct.stateId = s.StateID $sWhere", $aWhere)->result(); 
		$totalRow = $totalRowTmp['nmrows'];
		
		$qrySel = $this->db->pdoQuery("SELECT ct.*,s.stateName,c.countryName FROM tbl_city AS ct INNER JOIN tbl_state AS s ON ct.stateId = s.StateID INNER JOIN tbl_country AS c ON ct.countryId = c.CountryId  $sWhere ORDER BY $sorting limit $offset , $rows", $aWhere)->results();
		foreach($qrySel as $fetchRes) {
			$id =  $fetchRes['CityId'];
			$stateId = $fetchRes['StateID'];
			$status = ($fetchRes['isActive']=="y") ? "checked" : "";		
			
			$switch  =(in_array('status',$this->Permission))?$this->toggel_switch(array("action"=>"ajax.".$this->module.".php?id=".$id."","check"=>$status)):'';			
			$operation='';
			
			$operation .= (in_array('edit',$this->Permission))?$this->operation(array("href"=>"ajax.".$this->module.".php?action=edit&id=".$id."&stateId=".$stateId."","class"=>"btn default btn-xs black btnEdit","value"=>'<i class="fa fa-edit"></i>&nbsp;Edit')):'';
			$operation .=(in_array('delete',$this->Permission))?'&nbsp;&nbsp;'.$this->operation(array("href"=>"ajax.".$this->module.".php?action=delete&id=".$id."","class"=>"btn default btn-xs red btn-delete","value"=>'<i class="fa fa-trash-o"></i>&nbsp;Delete')):'';
			
			$final_array =  array($fetchRes["cityName"],$fetchRes["stateName"],$fetchRes["countryName"]);
			if(in_array('status',$this->Permission)){
				$final_array =  array_merge($final_array, array($switch));
			}
			if(in_array('edit',$this->Permission) || in_array('delete',$this->Permission) || in_array('view',$this->Permission) ){ 		
				$final_array =  array_merge($final_array, array($operation));
			}			   
			$row_data[] = $final_array;		
		}
		$result["sEcho"]=$sEcho;
		$result["iTotalRecords"] = (int)$totalRow;
		$result["iTotalDisplayRecords"] = (int)$totalRow;
		$result["aaData"] = $row_data;
		return $result;	
	
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

	public function getCountry($id){
		$country_option = '';
		$getSelectBoxOption=$this->getSelectBoxOption();
		$fields = array("%VALUE%","%SELECTED%","%DISPLAY_VALUE%");

		$qrySelCountry=$this->db->pdoQuery("SELECT * FROM tbl_country where isActive='y' ORDER BY countryName")->results();

		foreach ($qrySelCountry as $fetchRes) {
			//echo $fetchRes['countryName'];
			$selected = ($id==$fetchRes['CountryId'] && $id>0)?"selected":"";
			
			$fields_replace = array($fetchRes['CountryId'],$selected,$fetchRes['countryName']);
			$country_option.=str_replace($fields,$fields_replace,$getSelectBoxOption);
		}

		return $country_option;
	}

	public function getState($stateId,$countryId){
		$country_option = $state_option = '';
		$getSelectBoxOption=$this->getSelectBoxOption();
		$fields = array("%VALUE%","%SELECTED%","%DISPLAY_VALUE%");

		$qrySelState=$this->db->pdoQuery("SELECT * FROM tbl_state where CountryID=".$countryId." AND isActive='y' ORDER BY stateName")->results();

		foreach ($qrySelState as $fetchRes) {
			$selected = ($stateId==$fetchRes['StateID'])?"selected":"";
			
			$fields_replace = array($fetchRes['StateID'],$selected,$fetchRes['stateName']);
			$state_option.=str_replace($fields,$fields_replace,$getSelectBoxOption);
		}

		return $state_option;
	}	

	public function getSelectBoxOption(){
		$content = '';
		$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/select_option-nct.tpl.php");
		$content.= $main_content->parse();
		return sanitize_output($content);
	}
	
	
	public function getCity($cityId,$stateId){
		$country_option = $state_option = '';
		$getSelectBoxOption=$this->getSelectBoxOption();
		$fields = array("%VALUE%","%SELECTED%","%DISPLAY_VALUE%");

		$qrySelState=$this->db->pdoQuery("SELECT * FROM tbl_city where StateID=".$stateId." AND status='y' ORDER BY cityName")->results();

		foreach ($qrySelState as $fetchRes) {
			$selected = ($cityId==$fetchRes['CityId'])? "selected" : "";
			
			$fields_replace = array($fetchRes['CityId'],$selected,$fetchRes['cityName']);
			$state_option.=str_replace($fields,$fields_replace,$getSelectBoxOption);
		}

		return $state_option;
	}
	
}