<?php
class Projects extends Home {
	
	public $constantValue;
	public $constantName;
	public $data = array();
	
	public function __construct($module,$id=0, $searchArray=array(), $type='') {		
		global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_projects';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
		parent::__construct();	
		$this->image1 = array();			
		if($this->id>0){
			$fetchRes= $this->db->pdoQuery("
				SELECT tp.*,CONCAT_WS(' ',u.firstName,u.lastName) AS userName
				FROM tbl_projects AS tp
				LEFT JOIN tbl_users AS u 
		    	ON tp.userId = u.userId 
				WHERE tp.id=?",array((int)$this->id))->result();
						
			$this->userId = filtering($fetchRes['userId']);
			$this->title = filtering($fetchRes['title']);
			$this->description = filtering($fetchRes['description']);
			$this->jobStatus = filtering($fetchRes['jobStatus']);
			$this->duration  = filtering($fetchRes['duration']);
			$this->userName  = filtering($fetchRes['userName']);
			$this->categoryId = filtering($fetchRes['categoryId']);
			$this->subcategoryId = filtering($fetchRes['subcategoryId']);
			$this->isFeatured = filtering($fetchRes['isFeatured']);
			$this->createdDate = filtering($fetchRes['createdDate']);
			$this->biddingDeadline = filtering($fetchRes['biddingDeadline']);
			
			
				
			
		}
		else{
			
			$this->userId = '';
			$this->userName = '';
			$this->title = '';
			$this->description = '';
			$this->jobStatus = '';
			$this->duration  = '';			
			$this->categoryId = '';
			$this->subcategoryId = '';
			$this->isFeatured = 'n';
			$this->createdDate = '';	
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
				$this->data['content'] =  (in_array('view',$this->Permission))?$this->viewForm():'';
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
	public function displaybox($text) {
        $text['label'] = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? 'form-control-static ' . trim($text['class']) : 'form-control-static';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/displaybox.tpl.php');
        $main_content = $main_content->parse();
        $fields = array("%LABEL%", "%CLASS%", "%VALUE%");
        $fields_replace = array($text['label'], $text['class'], $text['value']);
        return str_replace($fields, $fields_replace, $main_content);
    }
	
	public function viewForm() {					    	
        $content = $this->displayBox(array("label" => "Title&nbsp;:", "value" => $this->title)) 
                . $this->displayBox(array("label" => "User Name&nbsp;:", "value" => $this->userName)) 
                . $this->displayBox(array("label" => "Description&nbsp;:", "value" => $this->description)) 
                . $this->displayBox(array("label" => "Status&nbsp;:", "value" => $this->jobStatus ))
                . $this->displayBox(array("label" => "Added on&nbsp;:", "value" => $this->createdDate));
        return $content;
    }
	
	public function get_cats (){
		$page_option = '';
		$getSelectBoxOption=$this->getSelectBoxOption();
		$fields = array("%VALUE%","%SELECTED%","%DISPLAY_VALUE%");
		
		$qrySel=$this->db->pdoQuery("SELECT id,cateName FROM tbl_categories where isActive='y' AND parentId=0 ORDER BY cateName ASC")->results();
		foreach ($qrySel as $fetchRes) {
			$selected = ($this->categoryId==$fetchRes['id'])?"selected":"";

			$fields_replace = array($fetchRes['id'],$selected,$fetchRes['cateName']);
			$page_option.=str_replace($fields,$fields_replace,$getSelectBoxOption);
		}
		return $page_option;
	}
	public function get_subcats ($categoryId){		
		$page_option = '';
		$getSelectBoxOption=$this->getSelectBoxOption();
		$fields = array("%VALUE%","%SELECTED%","%DISPLAY_VALUE%");
		
		if(isset($categoryId)&&$categoryId>0){
			$qrySel=$this->db->pdoQuery("SELECT id,cateName FROM tbl_categories where isActive='y' AND parentId =? ORDER BY cateName ASC",array($categoryId))->results();	
		}else{
			$qrySel=$this->db->pdoQuery("SELECT id,cateName FROM tbl_categories where isActive='y' AND parentId >0 ORDER BY cateName ASC")->results();
		}
				
		foreach ($qrySel as $fetchRes) {
			$selected = ($this->subcategoryId==$fetchRes['id'])?"selected":"";

			$fields_replace = array($fetchRes['id'],$selected,$fetchRes['cateName']);
			$page_option.=str_replace($fields,$fields_replace,$getSelectBoxOption);
		}
		return $page_option;
	}
	
	public function get_proj_status (){
		$page_option = '';
		$getSelectBoxOption=$this->getSelectBoxOption();
		$fields = array("%VALUE%","%SELECTED%","%DISPLAY_VALUE%");
		
		////////////
		$result = $this->db->pdoQuery("SHOW COLUMNS FROM $this->table LIKE 'jobStatus'")->results();
		if ($result) {
		    $fetchRes = explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2", $result[0]["Type"]));
		}
		////////////
		foreach ($fetchRes as $k=>$v) {
			$selected = ($this->jobStatus==$v)?"selected":"";

			$fields_replace = array($v,$selected,$v);
			$page_option.=str_replace($fields,$fields_replace,$getSelectBoxOption);
		}
		return $page_option;
	}
	
	public function getForm()
	{
		$content = '';
		
        $replace = array(
            "%TITLE%" => $this->title,            
            "%DESC%" => $this->description,
            "%CAT_OPTION%" => $this->get_cats(),
            "%SUBCAT_OPTION%" => $this->get_subcats($this->categoryId),
            "%DURATION%" => $this->duration,
            "%STATUS_OPTION%" => $this->get_proj_status(),            
            "%FEATURED_Y%" => ($this->isFeatured == 'y' ? 'checked' : ''),
            "%FEATURED_N%" => ($this->isFeatured == 'n'? 'checked' : ''),
            "%TYPE%" => $this->type,
            "%ID%" => $this->id
        );

        $content = get_view(DIR_ADMIN_TMPL . $this->module . "/form-nct.tpl.php", $replace);
        return filtering($content, 'output', 'text');
	}

	public function dataGrid() {
		$content = $operation = $whereCond = $totalRow = NULL;
		$result = $tmp_rows = $row_data = array();
		extract($this->searchArray);
		
		$chr = isset($chr)?str_replace(array('_', '%',"'"), array('\_', '\%',"\'"),trim($chr) ) : '';
		$whrCond = '';
		
		///////////////////////////
		
        if (isset($chr) && $chr != '') {
            $whereCond .= "  WHERE (p.jobStatus LIKE '%" . $chr . "%' OR u.firstName LIKE '%" . $chr . "%' OR u.lastName LIKE '%" . $chr . "%' OR p.title LIKE '%" . $chr . "%' OR DATE_FORMAT(p.createdDate, '" . MYSQL_DATE_FORMAT . "') LIKE '%" . $chr . "%')";
        }		
		        
        if (isset($sort)) {
        	if($sort == "jobStatus"){
        		$sorting = ' LPAD(p.jobStatus, 3, 0)'. ' ' . $order;
        	}else{
        		$sorting = $sort . ' ' . $order;	
        	}
            
        } else {
            $sorting = ' p.id DESC';
        }		

        $query = "SELECT p.*,
		  CONCAT_WS(' ',u.firstName,u.lastName) AS userName		  
		  
		FROM
		  tbl_projects AS p 
		  
		  LEFT JOIN tbl_users AS u 
		    ON p.userId = u.userId 
		" . $whereCond . " ORDER BY " . $sorting;
			
		$totalRows = $this->db->pdoQuery($query)->results();
		$totalRow = count($totalRows);
		
        $query_with_limit = $query . " LIMIT " . $offset . " ," . $rows . " ";
        $qrySel = $this->db->pdoQuery($query_with_limit)->results();
        
		///////////////////////////
		
		foreach($qrySel as $fetchRes) {
			
			$id = $fetchRes['id'];
			//$status = ($fetchRes['isActive']=="y") ? "checked" : "";
			//$switch = (in_array('status',$this->Permission))?$this->toggel_switch(array("action"=>"ajax.".$this->module.".php?id=".$fetchRes['id']."","check"=>$status)):NULL;

			$operation = NULL;
			$operation .=(in_array('edit',$this->Permission))?'&nbsp;&nbsp;'.$this->operation(array("href"=>SITE_ADM_MOD.$this->module."/ajax.".$this->module.".php?action=edit&id=".$id,"class"=>"btn default btn-xs black btnEdit","value"=>'<i class="fa fa-edit"></i>&nbsp;Edit')):NULL;
			$operation .=(in_array('delete',$this->Permission))?'&nbsp;&nbsp;'.$this->operation(array("href"=>"ajax.".$this->module.".php?action=delete&id=".$id,"class"=>"btn default btn-xs red btn-delete","value"=>'<i class="fa fa-trash-o"></i>&nbsp;Delete')):NULL;
			$operation .=(in_array('view',$this->Permission))?'&nbsp;&nbsp;'.$this->operation(array("href"=>"ajax.".$this->module.".php?action=view&id=".$id."","class"=>"btn default blue btn-xs btn-viewbtn","value"=>'<i class="fa fa-laptop"></i>&nbsp;View')):NULL;

			$featured = ($fetchRes['isFeatured']=="y") ? "checked" : "";
			$featuredSwitch = $this->toggel_switch(array("action"=>"ajax.".$this->module.".php?id=".$fetchRes['id']."&switch_action=featured","check"=>$featured));
			$final_array = array(	
				$id,			
				stripslashes($fetchRes["title"]) ,
				stripslashes($fetchRes["userName"]) ,
				$featuredSwitch,
				ucfirst(strtolower(stripslashes($fetchRes["jobStatus"]))) ,
				stripslashes($fetchRes["createdDate"]) ,			
				//$switch,
				$operation				
			);
			$row_data[] = array_filter($final_array,function ($val){
			    return !is_null($val);
			});	
		}
		$result["q"]=$query;
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
		$main_content->getForm = '';
		//$main_content->langArray = $this->langArray;
		$final_result = $main_content->parse();
		return $final_result;
	}
	public function getCategory($categoryId = 0){
		$selCategory = $this->db->select('tbl_category',array('id','categoryName'),array('isActive'=>'y'),' ORDER BY categoryName')->results();
		$opt_content1 = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/option-nct.tpl.php");
		$opt_content = $opt_content1->parse();
		ob_start();
		echo '<option value="">Please Select Category</option>';
		foreach($selCategory as $category){
			extract($category);
			$selected = $categoryId == $id ? 'selected="selected"' : '';
			echo str_replace(array('%VALUE%','%SELECTED%','%LABEL%'),array($id,$selected,$categoryName),$opt_content);
		}
		return ob_get_clean();
	}
	public function getSubCategory($categoryId = 0,$SubcategoryId = 0){
		$selSubcategory = $this->db->select('tbl_subcategory',array('id','subcategoryName'),array('categoryID'=>$categoryId,'isActive'=>'y'),' ORDER BY subcategoryName')->results();
		$opt_content1 = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/option-nct.tpl.php");
		$opt_content = $opt_content1->parse();
		ob_start();
		echo '<option value="">Please Select</option>';
		foreach($selSubcategory as $subcategory){
			extract($subcategory);
			$selected = $SubcategoryId == $id ? 'selected="selected"' : '';
			echo str_replace(array('%VALUE%','%SELECTED%','%LABEL%'),array($id,$selected,$subcategoryName),$opt_content);
		}
		return ob_get_clean();
	}

	
}