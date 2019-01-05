<?php
class Dispute extends Home {
	
	public $constantValue;
	public $constantName;
	public $data = array();
	
	public function __construct($module,$id=0, $searchArray=array(), $type='') {		
		global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_project_dispute';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
		parent::__construct();	
		$this->image1 = array();			
		if($this->id>0){
			$fetchRes= $this->db->pdoQuery("
			SELECT 
			  p.title,p.jobStatus,
			  CONCAT_WS(' ',u.`firstName`,u.`lastName`) AS userName,	
			  u.userType,
			  pd.*			  
			FROM
			  `tbl_project_dispute` AS pd 
			  LEFT JOIN `tbl_projects` AS p 
			    ON pd.`projectId` = p.`id` 
			  LEFT JOIN tbl_users AS u 
			    ON pd.`userId`=u.`userId`
			WHERE pd.id =? 
			",array((int)$this->id))->result();
			
			foreach ($fetchRes as $k => $v) {
				$this->{$k} = filtering($v);
			}
				
				
			
		}
		else{
			$fetchRes= $this->db->pdoQuery("SHOW COLUMNS FROM ".$this->table)->results();
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
        $content = $this->displayBox(array("label" => "Title :", "value" => $this->title)) 
                . $this->displayBox(array("label" => "User Name :", "value" => $this->userName)) 
				. $this->displayBox(array("label" => "Subject :", "value" => $this->subject))
				. $this->displayBox(array("label" => "Admin's Judgement :", "value" => $this->admin_judgement))
                . $this->displayBox(array("label" => "Description:", "value" => $this->description)) 
                
                . $this->displayBox(array("label" => "Added on :", "value" => $this->createdDate));
        return $content;
    }
	
	
	public function getForm()
	{
		$content = '';		
        $replace = array(
            "%TITLE%" => $this->title,            
            "%USER_NAME%" => $this->userName,
            "%PROJECTID%" => $this->projectId,
            "%SUBJECT%" => $this->subject,			
            "%DESC%" => $this->description,
            "%ADMIN_REPLY_0%" => ($this->admin_judgement == 0 ? 'selected' : ''),
            "%ADMIN_REPLY_1%" => ($this->admin_judgement == 1 ? 'selected' : ''),
            "%ADMIN_REPLY_2%" => ($this->admin_judgement == 2 ? 'selected' : ''),
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
		
		$chr = isset($chr)?str_replace(array('_', '%'), array('\_', '\%'),trim($chr) ) : '';
		$whrCond = '';
		
		///////////////////////////
		
        if (isset($chr) && $chr != '') {
            $whereCond .= "  WHERE (u.`firstName` LIKE '%" . $chr . "%' OR u.`lastName` LIKE '%" . $chr . "%' OR p.title LIKE '%" . $chr . "%' OR DATE_FORMAT(pd.createdDate, '" . MYSQL_DATE_FORMAT . "') LIKE '%" . $chr . "%')";
        }		
		
        if (isset($sort)) {
            $sorting = $sort . ' ' . $order;
        } else {
            $sorting = ' pd.id DESC';
        }		
				
        $query = "SELECT 
		  p.title,p.jobStatus,
		  CONCAT_WS(' ',u.`firstName`,u.`lastName`) AS userName,	
		  u.userType,
		  pd.*			  
		FROM
		  `tbl_project_dispute` AS pd 
		  LEFT JOIN `tbl_projects` AS p 
		    ON pd.`projectId` = p.`id` 
		  LEFT JOIN tbl_users AS u 
		    ON pd.`userId`=u.`userId`
		
		" . $whereCond . " ORDER BY " . $sorting;
			
		$totalRows = $this->db->pdoQuery($query)->results();
		$totalRow = count($totalRows);
		
        $query_with_limit = $query . " LIMIT " . $offset . " ," . $rows . " ";
        $qrySel = $this->db->pdoQuery($query_with_limit)->results();
        
		///////////////////////////
		
		foreach($qrySel as $fetchRes) {
			
			$id = $fetchRes['id'];
			$operation = NULL;
            if($fetchRes['admin_judgement'] == 'pending'){
                $operation .=(in_array('edit',$this->Permission))?'&nbsp;&nbsp;'.$this->operation(array("href"=>SITE_ADM_MOD.$this->module."/ajax.".$this->module.".php?action=edit&id=".$id,"class"=>"btn default btn-xs black btnEdit","value"=>'<i class="fa fa-edit"></i>&nbsp;Edit')):NULL;

            }
			$operation .=(in_array('delete',$this->Permission))?'&nbsp;&nbsp;'.$this->operation(array("href"=>"ajax.".$this->module.".php?action=delete&id=".$id,"class"=>"btn default btn-xs red btn-delete","value"=>'<i class="fa fa-trash-o"></i>&nbsp;Delete')):NULL;
			$operation .=(in_array('view',$this->Permission))?'&nbsp;&nbsp;'.$this->operation(array("href"=>"ajax.".$this->module.".php?action=view&id=".$id."","class"=>"btn default blue btn-xs btn-viewbtn","value"=>'<i class="fa fa-laptop"></i>&nbsp;View')):NULL;
			$operation .=(in_array('view',$this->Permission))?'&nbsp;&nbsp;'.$this->operation(array("extraAtt"=>"target='_blank'","href"=> SITE_ADM_MOD."dispute-messages-nct/?id=".$fetchRes["projectId"]."","class"=>"btn default blue btn-xs","value"=>'<i class="fa fa-laptop"></i>&nbsp;See all messages for this dispute')):NULL;

			$final_array = array(	
				$id,			
				stripslashes($fetchRes["title"]) ,
				stripslashes($fetchRes["userName"]) ,
				stripslashes($fetchRes["userType"]=='c'?'Customer':'Provider') ,			
				stripslashes($fetchRes["createdDate"]) ,
				($fetchRes["admin_judgement"]=='pending'?'Pending':($fetchRes["admin_judgement"]=='valid'?'Valid':'Invalid') ),			
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
	

	
}