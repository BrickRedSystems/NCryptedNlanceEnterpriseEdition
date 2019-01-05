<?php
class Statistics extends Home {
	function __construct() {
		parent::__construct();
	}
	public function getStatistics() {
		//$total_users = getTableValue('tbl_users','COUNT(id)',array("usertype <>"=>3)); 
		$total_users = getTableValue('tbl_users','COUNT(userId)');
		$total_providers = getTableValue('tbl_users','COUNT(userId)',array("usertype"=>'p'));
		$total_customers = getTableValue('tbl_users','COUNT(userId)',array("usertype"=>'c'));	
		$total_projects = getTableValue('tbl_projects','COUNT(id)');
		
		$replace = array(
			'%TOTAL_USERS%' => !empty($total_users) ? $total_users : 0,
			'%TOTAL_PROVIDERS%' => !empty($total_providers) ? $total_providers : 0,			
			'%TOTAL_CUSTOMERS%' => !empty($total_customers) ? $total_customers : 0,
			'%TOTAL_PROJECTS%' => !empty($total_projects) ? $total_projects : 0	
		);		
		return get_view(DIR_ADMIN_TMPL.$this->module."/numeric_statistics_list-nct.tpl.php",$replace);
		
	}
	public function getPageContent(){
		$final_result = NULL;
		$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/".$this->module.".tpl.php");
		$main_content->breadcrumb = $this->getBreadcrumb();
		$final_result = $main_content->parse();
		$fields = array("%STATISTICS_LIST%");
		$fields_replace = array($this->getStatistics());
		$main_content=str_replace($fields,$fields_replace,$final_result);		
		return $main_content;
		
	}
}