<?php
class Experience extends Home{
	public $data = array();
	public function __construct($module, $id=0, $objPost=NULL, $searchArray=array(), $type='') {		
		global $db, $fb, $fields, $sessCataId;		
		$this->fb = $fb;
$this->db = $db;
		$this->data['id'] = $this->id = $id;
		$this->fields = $fields;
		$this->module = $module;
		$this->table = 'tbl_experience';	
	
		$this->type = ($this->id > 0 ? 'edit' : 'add');
		$this->searchArray = $searchArray;
		parent::__construct();	
			
		if($this->id>0){

			$fetchRes = $this->db->pdoQuery("SELECT e.* FROM tbl_experience AS e
					WHERE e.id=?",array($this->id))->result();
			
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
		$content = $this->displayBox(array("label"=>"Experience Level :","value"=>$this->name))
				.$this->displayBox(array("label"=>"Hours&nbsp :","value"=>$this->hours))						
				.$this->displayBox(array("label"=>"Status&nbsp :","value"=>$this->isActive == 'y'?'Active':'Deactive'));
		return $content;
	}
	public function getForm() {		
		$replace = array(
			"%NAME%" =>  $this->name,			
			"%HOURS%" => $this->hours,			
			"%STATUS_A%" => ($this->isActive == 'y' ? 'checked':''),
			"%STATUS_D%" => ($this->isActive == 'n' ? 'checked':''),
			"%TYPE%" => $this->type,
			"%ID%" => $this->id
		);

		$content=get_view(DIR_ADMIN_TMPL.$this->module."/form-nct.tpl.php",$replace);
		return sanitize_output($content);
	}

	public function dataGrid() {
		$content = $operation = $whereCond = $totalRow = NULL;
		$result = $tmp_rows = $row_data = array();
		extract($this->searchArray);
		$chr = str_replace(array('_', '%',"'"), array('\_', '\%',"\'"),$chr);		      
		$whrCond = '';			
        if (isset($chr) && $chr != '') {
            $whereCond .= "  WHERE (e.name LIKE '%" . $chr . "%' OR DATE_FORMAT(e.createdDate, '" . MYSQL_DATE_FORMAT . "') LIKE '%" . $chr . "%')";
        }		
		        
        if (isset($sort)) {
            $sorting = $sort . ' ' . $order;
        } else {
            $sorting = ' e.hours ASC ';
        }		
				
        $query = "SELECT e.* FROM tbl_experience AS e " . $whereCond . " ORDER BY " . $sorting;			
		$qrySel = $this->db->pdoQuery($query)->results();
		$totalRow = count($qrySel);
		 

		foreach($qrySel as $fetchRes) {
			$id = $fetchRes['id'];
			$status = ($fetchRes['isActive']=="y") ? "checked" : "";
			$switch = (in_array('status',$this->Permission))?$this->toggel_switch(array("action"=>"ajax.".$this->module.".php?id=".$fetchRes['id']."","check"=>$status,"class"=>"make-switch")):'';
			
			$operation = NULL;
			$operation .=(in_array('edit',$this->Permission))?'&nbsp;&nbsp;'.$this->operation(array("href"=>SITE_ADM_MOD.$this->module."/ajax.".$this->module.".php?action=edit&id=".$id,"class"=>"btn default btn-xs black btnEdit","value"=>'<i class="fa fa-edit"></i>&nbsp;Edit')):NULL;			
			$operation .=(in_array('view',$this->Permission))?'&nbsp;&nbsp;'.$this->operation(array("href"=>"ajax.".$this->module.".php?action=view&id=".$id."","class"=>"btn default blue btn-xs btn-viewbtn","value"=>'<i class="fa fa-laptop"></i>&nbsp;View')):NULL;

			$final_array = array(
				stripslashes($fetchRes["id"]) ,				
				ucfirst(strtolower(stripslashes($fetchRes["name"]))) ,				
				stripslashes($fetchRes["hours"]) ,			
				/*$switch ,*/
				$operation				
			);
			$row_data[] = array_filter($final_array,function ($val){
			    return !is_null($val);
			});				
		}
		$result["sEcho"]=$sEcho;
		$result["iTotalRecords"] = (int)$totalRow;
		$result["iTotalDisplayRecords"] = (int)$totalRow;
		$result["aaData"] = $row_data;
		return $result;	
	}
	public function toggel_switch($text) {
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

	public function select_option($text) {
		$text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['selected'] = isset($text['selected']) ? ''.trim($text['selected']) : '';
        $text['display_value'] = isset($text['display_value']) ? $text['display_value'] : '';

		$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module.'/select_option-nct.tpl.php');
		$main_content=$main_content->parse();
		$fields = array("%VALUE%", "%SELECTED%", "%DISPLAY_VALUE%");
		$selected = (($text['value']==$text['selected'])?"selected='selected'":'');
		$fields_replace = array($text['value'],$selected,$text['display_value']);
		return str_replace($fields,$fields_replace,$main_content);	
	}

	public function operation($text) {
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
	public function displaybox($text) {
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
	public function getPageContent() {		
		$final_result = NULL;		
		$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/".$this->module.".tpl.php");
		$main_content->breadcrumb = $this->getBreadcrumb();			
		$final_result = $main_content->parse();
		return $final_result;
	}
}