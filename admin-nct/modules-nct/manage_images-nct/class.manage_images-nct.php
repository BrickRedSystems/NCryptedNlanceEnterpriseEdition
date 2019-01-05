<?php
class Image extends Home {
	public $page_name;
	public $page_title;
	public $meta_keyword;
	public $meta_desc;
	public $page_desc;
	public $isActive;
	public $data = array();
	public function __construct($id=0, $searchArray=array(), $type='') {
		$this->data['id'] = $this->id = $id;
		$this->table = 'tbl_home_images';
		$this->type = ($this->id > 0 ? 'edit' : 'add');
		$this->searchArray = $searchArray;
		parent::__construct();
		if($this->id>0){
			$qrySel = $this->db->select($this->table, array("*"),array("id"=>$id))->result();
			$fetchRes = $qrySel;
			$this->data['name'] = $this->name = $fetchRes['name'];
			$this->data['image_name'] = $this->image_name = $fetchRes['image_name'];
			$this->data['created_date'] = $this->created_date = $fetchRes['created_date'];
			$this->data['updated_date'] = $this->updated_date = $fetchRes['updated_date'];
			$this->data['status'] = $this->status = $fetchRes['status'];
			$this->data['filetype'] = $this->filetype = $fetchRes['filetype'];
			$this->data['image_caption'] = $this->image_caption = $fetchRes['image_caption'];
			
		}else{
			$this->data['name'] = $this->name = '';
			$this->data['image_name'] = $this->image_name = '';
			$this->data['created_date'] = $this->created_date = '';
			$this->data['updated_date'] = $this->updated_date = '';
			$this->data['status'] = $this->status = 'a';
			$this->data['filetype'] = $this->filetype = '';
			$this->data['image_caption'] = $this->image_caption = '';

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
				$this->data['content'] =  (in_array('delete',$this->Permission))?json_encode($this->dataGrid()):'';
				break;
			}
			case 'datagrid' : {
				$this->data['content'] =  (in_array('module',$this->Permission))?json_encode($this->dataGrid()):'';
			}
		}
	}
	public function operation($text) {

        $text['href'] = isset($text['href']) ? $text['href'] : 'Enter Link Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? '' . trim($text['class']) : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/operation-nct.tpl.php');
        $main_content = $main_content->parse();
        $fields = array("%HREF%", "%CLASS%", "%VALUE%", "%EXTRA%");
        $fields_replace = array($text['href'], $text['class'], $text['value'], $text['extraAtt']);
        return str_replace($fields, $fields_replace, $main_content);
    }
    public function form_start($text){
		$text['action'] = isset($text['action']) ? $text['action'] : '';
		$text['method'] = isset($text['method']) ? $text['method'] : 'post';
		$text['name'] = isset($text['name']) ? $text['name'] : '';
		$text['id'] = isset($text['id']) ? $text['id'] : '';
		$text['class'] = isset($text['class']) ? ''.trim($text['class']) : 'form-horizontal';
		$text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
		$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/".'form_start.tpl.php');
		$main_content=$main_content->parse();
		$fields = array("%ACTION%","%METHOD%","%NAME%","%ID%","%CLASS%","%EXTRA%");
		$fields_replace = array($text['action'],$text['method'],$text['name'],$text['name'],$text['class'],$text['extraAtt']);
		return str_replace($fields,$fields_replace,$main_content);
	}
	public function form_end(){
		$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/".'form_end.tpl.php');
		$main_content=$main_content->parse();
		return $main_content;
	}

	public function getForm() {
		$content = '';
		$content .= 
		$this->form_start(array("name"=>"frmCont","extraAtt"=>"novalidate='novalidate'")).
		$this->textBox(array("label"=>"".MEND_SIGN."Name: ","name"=>"name","class"=>"logintextbox-bg required","value"=>$this->data['name'],'extraAtt'=>'readonly','onlyField'=>false)).
		$this->textBox(array("label"=>"".MEND_SIGN."Banner Caption: ","name"=>"image_caption","class"=>"logintextbox-bg required","value"=>$this->data['image_caption'],'extraAtt'=>'','onlyField'=>false));

		if($this->id == 1) {
			$content .= '<link rel="stylesheet" type="text/css" href="'.SITE_ADM_CSS.'token-input.css">
			<script type="text/javascript" src="'.SITE_ADM_JS.'jquery.tokeninput.js"></script>
			';
			$content .= $this->fileBox(array("label"=>"".MEND_SIGN."Image(1355X645 PX): ","name"=>"image", "class"=>"textarea-bg ".(($this->type!="edit")?"required":"").""));
			if($this->image_name != ''){
				$content .=	$this->img(array("src"=>SITE_UPD_HOMEIMG.$this->image_name,"label"=>"Uploaded Image:","width"=>"100","height"=>"100"));
			}
		} else {
			$content .=$this->fileBox(array("label"=>"".MEND_SIGN."Video: ","name"=>"video", "class"=>"textarea-bg ".(($this->type!="edit")?"required":"").""));
			$video_link = $this->operation(array('href'=>SITE_UPD_HOMEIMG.$this->image_name,'value'=>$this->image_name,'name'=>$this->image_name,'extraAtt'=>'target="_blank"'));
				$content .=	'

				<div class="form-group">
					<label class="control-label col-md-3">Preview:&nbsp;</label>
					<div class="col-md-4">
						<video width="400" controls>
						  	<source src="'.SITE_UPD_HOMEIMG.$this->image_name.'" type="video/mp4">
						  	<source src="mov_bbb.ogg" type="video/ogg">
						</video>
					</div>
				</div>';
		}
		$content .= '
				<div class="form-group"> 
		            <label class="control-label col-md-3">'.MEND_SIGN.'Show on home?: &nbsp;</label> 
		            <div class="col-md-4"> 
		                <div class="radio-list" data-error-container="#form_2_Status: _error"> 
		                    <label class=""> 
		                        <input class="radioBtn-bg required" id="y" name="status" type="radio" value="a" '.($this->status == 'a'?'checked':'').'> Yes
		                    </label>
		                    <span for="status" class="help-block"></span> 

		                    <label class=""> 
		                        <input class="radioBtn-bg required" id="n" name="status" type="radio" value="d" '.($this->status == 'd'?'checked':'').'> No
		                    </label>
		                    <span for="status" class="help-block"></span> 
		                </div>
		                <div id="form_2_Status: _error"></div> 
		            </div>
		        </div>
		        <div class="flclear clearfix"></div>
		';
		$content .= $this->hidden(array("name"=>"txt_old_image", "value"=>$this->image_name, "class"=>"", "extraAtt"=>"")).
		
		$this->hidden(array("name"=>"type","value"=>"".$this->type."")).
		$this->hidden(array("name"=>"id","value"=>"".$this->id."")).
		$this->buttonpanel_start().
		$this->button(array("onlyField"=>true,"name"=>"submitAddForm", "type"=>"submit", "class"=>"green", "value"=>"Submit", "extraAtt"=>"")).
		$this->button(array("onlyField"=>true,"name"=>"cn", "type"=>"button", "class"=>"btn-toggler", "value"=>"Cancel", "extraAtt"=>"")).
		$this->buttonpanel_end();
		$content .= $this->form_end();
		return sanitize_output($content);
	}
	public function buttonpanel_start(){
		$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/".'buttonpanel_start.tpl.php');
		$main_content=$main_content->parse();
		return $main_content;
	}
	public function buttonpanel_end(){
		$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/".'buttonpanel_end.tpl.php');
		$main_content=$main_content->parse();
		return $main_content;
	}
	public function button($btn){
		$btn['value'] = isset($btn['value']) ? $btn['value'] : '';
		$btn['name'] = isset($btn['name']) ? $btn['name'] : '';
		$btn['class'] = isset($btn['class']) ? 'btn '.$btn['class'] : 'btn';
		$btn['type'] = isset($btn['type']) ? $btn['type'] : '';
		$btn['src'] = isset($btn['src']) ? $btn['src'] : '';
		$btn['extraAtt'] = isset($btn['extraAtt']) ? ' '.$btn['extraAtt'] : '';
		$btn['onlyField'] = isset($btn['onlyField']) ? $btn['onlyField'] : false;
		$btn["src"]=($btn["type"]=="image" && $btn["src"]!='')?$btn["src"]:'';
		$main_content_only_field = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/".'button_onlyfield.tpl.php');
		$main_content_only_field=$main_content_only_field->parse();
		$fields = array("%TYPE%","%NAME%","%CLASS%","%ID%","%SRC%","%EXTRA%","%VALUE%");
		$fields_replace = array($btn["type"],$btn["name"],$btn["class"],$btn["name"],$btn["src"],$btn['extraAtt'],$btn["value"]);
		$sub_final_result_only_field=str_replace($fields,$fields_replace,$main_content_only_field);
		if($btn['onlyField']==true){
			return $sub_final_result_only_field;
		}else{
			$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/".'button.tpl.php');
			$main_content=$main_content->parse();
			$fields = array("%BUTTON%");
			$fields_replace = array($sub_final_result_only_field);
			return str_replace($fields,$fields_replace,$main_content);
		}
	}
	public function img($text){
		$text['href'] = isset($text['href']) ? $text['href'] : '';
		$text['src'] = isset($text['src']) ? $text['src'] : 'Enter Image Path Here: ';
		$text['name'] = isset($text['name']) ? $text['name'] : '';
		$text['id'] = isset($text['id']) ? $text['id'] : '';
		$text['class'] = isset($text['class']) ? ''.trim($text['class']) : '';
		$text['height'] = isset($text['height']) ? ''.trim($text['height']) : '';
		$text['width'] = isset($text['width']) ? ''.trim($text['width']) : '';
		$text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
		$text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : '';
		$text['label'] = isset($text['label']) ? $text['label'] : '';
		if($text['onlyField']==true){
			$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/".'img_onlyfield.tpl.php');
			$main_content=$main_content->parse();
		}else{
			$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/".'img.tpl.php');
			$main_content=$main_content->parse();
		}
		$fields = array("%NAME%","%LABEL%","%HREF%","%SRC%","%CLASS%","%ID%","%ALT%","%WIDTH%","%HEIGHT%","%EXTRA%");
		$fields_replace = array($text['name'],$text['label'],$text['href'],$text['src'],$text['class'],$text['id'],$text['name'],$text['width'],$text['height'],$text['extraAtt']);
		return str_replace($fields,$fields_replace,$main_content);
	}
	public function textBox($text){
		$text['label'] = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
		$text['value'] = isset($text['value']) ? $text['value'] : '';
		$text['name'] = isset($text['name']) ? $text['name'] : '';
		$text['class'] = isset($text['class']) ? 'form-control '.trim($text['class']) : 'form-control';
		$text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
		$text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
		if($text["onlyField"]==true){
			$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/".'textbox_onlyfield.tpl.php');
		}else{
			$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/".'textbox.tpl.php');
		}
		$main_content=$main_content->parse();
		$fields = array("%CLASS%","%NAME%","%ID%","%VALUE%","%EXTRA%","%LABEL%");
		$fields_replace = array($text['class'],$text['name'],$text['name'],$text['value'],$text['extraAtt'],$text['label']);
		return str_replace($fields,$fields_replace,$main_content);
	}
	
	public function fileBox($text){
		$text['label'] = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
		$text['value'] = isset($text['value']) ? $text['value'] : '';
		$text['name'] = isset($text['name']) ? $text['name'] : '';
		$text['class'] = isset($text['class']) ? 'form-control '.trim($text['class']) : 'form-control';
		$text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
		$text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
		$text["help"] = isset($text["help"])?$text["help"]:"";
		$text["helptext"] = ($text["help"]!="")?'<p class="help-block">'.$text["help"].'</p>':"";
		if($text["onlyField"]==true){
			$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/".'filebox_onlyfield.tpl.php');
		}else{
			$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/".'filebox.tpl.php');
		}
		$main_content=$main_content->parse();
		$fields = array("%CLASS%","%NAME%","%ID%","%VALUE%","%EXTRA%","%LABEL%","%HELPTEXT%");
		$fields_replace = array($text['class'],$text['name'],$text['name'],$text['value'],$text['extraAtt'],$text['label'],$text["helptext"]);
		return str_replace($fields,$fields_replace,$main_content);
	}
	public function hidden($text) {
		$text['label'] = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
		$text['value'] = isset($text['value']) ? $text['value'] : '';
		$text['name'] = isset($text['name']) ? $text['name'] : '';
		$text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
		$text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
		$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/".'hidden.tpl.php');
		$main_content=$main_content->parse();
		$fields = array("%NAME%","%ID%","%VALUE%","%EXTRA%");
		$fields_replace = array($text['name'],$text['name'],$text['value'],$text['extraAtt']);
		return str_replace($fields,$fields_replace,$main_content);
	}

	public function dataGrid() {
		$content = $operation = $whereCond = $totalRow = NULL;
		$result = $tmp_rows = $row_data = array();
		extract($this->searchArray);
		$chr = str_replace(array('_', '%'), array('\_', '\%'),$chr );
		$whereCond = ($chr != null)?" where i.name LIKE '%".$chr."%'":null;
		//dump_exit($whereCond);
		if(isset($sort))
			$sorting = $sort.' '. $order;
		else
			$sorting = 'id DESC';
		$totalRow = $this->db->count($this->table, $whereCond);
		

		$qrySel = $this->db->pdoQuery("
			select i.*
			from tbl_home_images as i $whereCond order by $sorting ")->results();


		foreach($qrySel as $fetchRes) {
			$status = ($fetchRes['status']=="a") ? "checked" : "";
			$switch  =(in_array('status',$this->Permission))?$this->toggel_switch(array("action"=>"ajax.".$this->module.".php?id=".$fetchRes['id']."","check"=>$status)):'';
			$operation = (in_array('edit',$this->Permission))?$this->operation(array("href"=>"ajax.".$this->module.".php?action=edit&id=".$fetchRes['id']."","class"=>"btn default btn-xs black btnEdit","value"=>'<i class="fa fa-edit"></i>&nbsp;Edit')):'';
			if($fetchRes["id"] == 2){
				$image = '<video width="300" controls>
						  	<source src="'.SITE_UPD_HOMEIMG.$fetchRes['image_name'].'" type="video/mp4">
						  	<source src="mov_bbb.ogg" type="video/ogg">
						</video>';
			}else{
				$image = $this->img(array("src"=>SITE_UPD_HOMEIMG.$fetchRes['image_name'],"width"=>"300","height"=>"150","onlyField"=>true));
			}
			$final_array =  array($fetchRes["name"],$fetchRes["filetype"],$image);
			
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

	public function toggel_switch($text) {
        $text['action'] = isset($text['action']) ? $text['action'] : 'Enter Action Here: ';
        $text['check'] = isset($text['check']) ? $text['check'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? '' . trim($text['class']) : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/switch-nct.tpl.php');
        $main_content = $main_content->parse();
        $fields = array("%NAME%", "%CLASS%", "%ACTION%", "%EXTRA%", "%CHECK%");
        $fields_replace = array($text['name'], $text['class'], $text['action'], $text['extraAtt'], $text['check']);
        return str_replace($fields, $fields_replace, $main_content);
    }

	public function getPageContent(){
		$final_result = NULL;
		$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/".$this->module.".tpl.php");
		$main_content->breadcrumb = $this->getBreadcrumb();
		$final_result = $main_content->parse();
		return $final_result;
	}
}