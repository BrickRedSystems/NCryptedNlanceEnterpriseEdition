<?php
class subCategory extends Home {
	
	public $constantValue;
	public $constantName;
	public $data = array();
	
	public function __construct($module,$id=0, $searchArray=array(), $type='') {		
		global $db, $fb, $fields, $sessCataId;
        $this->fb = $fb;
$this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_categories';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;

		parent::__construct();				
		if ($this->id > 0) {
            $fetchRes = $this->db->select('tbl_categories', '*', array('id' => $this->id))->result();

            foreach ($fetchRes as $k => $v) {
                $this->{$k} = filtering($v);
            }

        } else {

            $fetchRes = $this->db->pdoQuery("SHOW COLUMNS FROM " . $this->table)->results();
            foreach ($fetchRes as $k => $v) {
                $this->{$v["Field"]} = $v["Default"];
            }
        }

		
		switch($type){
			case 'add' : {
				$this->data['content'] =  (in_array('add',$this->Permission))?$this->getForm():'';
				break;
			}
			case 'edit' : {
				$this->data['content'] =  (in_array('edit',$this->Permission))?$this->getForm():'';
				break;
			}
			case 'view' : {
				$this->data['content'] =  '';
				break;
			}
			case 'delete' : {
				$this->data['content'] = (in_array('delete',$this->Permission))?json_encode($this->dataGrid()):'';
				break;
			}
			case 'datagrid' :  {
				$this->data['content'] = (in_array('module',$this->Permission))?json_encode($this->dataGrid()):'';
			}
		}

	}
	
	public function getForm() {
		$content = '';
        $languages = $this->db->select("tbl_languages", '*', array("status" => 'a'))->results();
        $html = null;

        //start:: to generate cat dropdown
        $category_opt= new MainTemplater(DIR_ADMIN_TMPL.$this->module."/option-nct.tpl.php");
		$category_opt = $category_opt->parse();
		$fields = array('%VALUE%','%SELECTED%','%LABEL%');
		$defaultLangId = getTableValue('tbl_languages','id',array('isDefault'=>'y'));
		$selCats = $this->db->select('tbl_categories',array('id','cateName_'.$defaultLangId),array('isActive'=>'y','parentId'=>0))->results();
		$category_name_field = NULL;
		foreach ($selCats as $category) {
			$selected = ($this->id == $category['id'] ? "selected='selected'" : '');
			$fields_replace = array($category['id'],$selected,$category['cateName_'.$defaultLangId]);
			$category_name_field .= str_replace($fields,$fields_replace,$category_opt);
		}			
        //end:: to generate cat dropdown

        foreach ($languages as $key => $value) {
            //dump($value);
            $html .= get_view(DIR_ADMIN_TMPL . $this->module . "/textfield.tpl.php", array(
            	'%label%' => 'Sub-category Name',
            	'%id%' => $value['id'], 
            	'%languageName%' => $value['languageName'], 
            	'%fieldName%' => 'cateName['.$value['id'].']',
            	'%fieldValue%' => (isset($this->{"cateName_" . $value['id']}) ? $this->{"cateName_" . $value['id']} : null)));
            $html .= get_view(DIR_ADMIN_TMPL . $this->module . "/textarea.tpl.php", array(
            	'%label%' => 'Description',
            	'%id%' => $value['id'], 
            	'%languageName%' => $value['languageName'], 
            	'%fieldName%' => 'description['.$value['id'].']',
            	'%fieldValue%' => (isset($this->{"description_" . $value['id']}) ? $this->{"description_" . $value['id']} : null)));

        }
        $replace = array(
            "%html%"      => $html,
            "%CATEGORY_NAME_FIELD%" => $category_name_field,
            "%STATUS_A%"  => ($this->isActive == 'y' ? 'checked' : ''),
            "%STATUS_D%"  => ($this->isActive == 'n' ? 'checked' : ''),
            "%TYPE%"      => $this->type,
            "%ID%"        => $this->id,
        );

        $content = get_view(DIR_ADMIN_TMPL . $this->module . "/form-nct.tpl.php", $replace);
        return sanitize_output($content);
		
	}
	
	public function dataGrid() {
		$content = $operation = $whereCond = $whereCond1= $totalRow = NULL;
		$result = $tmp_rows = $row_data = array();
		extract($this->searchArray);
		$langId  = isset($langId) ? $langId : 1;
		$chr = isset($chr)?str_replace(array('_', '%'), array('\_', '\%'),$chr ) : '';
		$whrCond = '';
		/*$whrArr = array();*/
		if(isset($chr) && $chr != '') {
			$whrCond =" AND sub.cateName_$langId LIKE '%".$chr."%' OR sub.description_$langId LIKE '%".$chr."%'";
			
		}		
		if(isset($sort))
			$sorting = $sort.' '. $order;
		else
			$sorting = 'sub.cateName_'.$langId.' DESC';	
		

		$qry = 'SELECT sub.id, sub.cateName_'.$langId.' AS subcateName, sub.description_'.$langId.', sub.isActive, sub.id, cat.cateName_'.$langId.' FROM tbl_categories AS sub INNER JOIN tbl_categories AS cat ON (sub.parentId = cat.id) WHERE 1'.$whrCond. ' ORDER BY '.$sorting;
		/////////////////
		$totalRows = $this->db->pdoQuery($qry)->results();
		$totalRow = count($totalRows);
		
        $query_with_limit = $qry . " LIMIT " . $offset . " ," . $rows . " ";
        $results = $this->db->pdoQuery($query_with_limit)->results();
        ///////////

		
		foreach($results as $fetchRes) {
			$status = ($fetchRes['isActive']=="y") ? "checked" : "";
			$id = $fetchRes['id'];			
			$switch  =(in_array('status',$this->Permission))?$this->toggel_switch(array("action"=>"ajax.".$this->module.".php?id=".$id."","check"=>$status)):'';			
			$operation =(in_array('edit',$this->Permission))?'&nbsp;&nbsp;'.$this->operation(array("href"=>SITE_ADM_MOD.$this->module."/ajax.".$this->module.".php?action=edit&id=".$id,"class"=>"btn default btn-xs black btnEdit","value"=>'<i class="fa fa-edit"></i>&nbsp;Edit')):'';			
			$operation .=(in_array('delete',$this->Permission))?'&nbsp;&nbsp;'.$this->operation(array("href"=>"ajax.".$this->module.".php?action=delete&id=".$id."","class"=>"btn default btn-xs red btn-delete","value"=>'<i class="fa fa-trash-o"></i>&nbsp;Delete')):'';
			$final_array = array($id,stripslashes($fetchRes["subcateName"]),stripslashes($fetchRes["cateName_".$langId]),stripslashes($fetchRes['description_'.$langId]));
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
		$main_content->getForm = $this->getForm();
		$final_result = $main_content->parse();
		return $final_result;
	}
}