<?php

class TopSkills extends Home {

    public $show_on_home;
    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') {
        global $db, $fb, $fields;
        $this->fb = $fb;
$this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_top_skills';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        //dump_exit($this->id);
        if ($this->id > 0) {

        	/*$qrySel = $this->db->pdoQuery('SELECT tsl.topSkill, tts.*, GROUP_CONCAT(DISTINCT tsl.skill) AS skillIds, GROUP_CONCAT(DISTINCT s.skillName_1) AS skillNames FROM tbl_top_skills AS tts LEFT JOIN tbl_top_skill_list AS tsl ON tts.`id` = tsl.`topSkill` LEFT JOIN tbl_skills AS s ON FIND_IN_SET (s.id, tsl.skill) WHERE tsl.`topSkill` = ? ',array($id))->result();*/
            $qrySel = $this->db->pdoQuery("SELECT *,skillName_1 AS skillName FROM tbl_top_skills  WHERE id = ".$id)->result();

            $fetchRes = $qrySel;
			foreach ($fetchRes as $k => $v) {
                $this->{$k} = filtering($v);
            }
            $this->id = $id;
        } else {
            $fetchRes = $this->db->pdoQuery("SHOW COLUMNS FROM " . $this->table)->results();
            foreach ($fetchRes as $k => $v) {
                $this->{$v["Field"]} = $v["Default"];
            }
        }
        switch ($type) {
            case 'add' : {
                    $this->data['content'] = $this->getForm();
                    break;
                }
            case 'edit' : {
                    $this->data['content'] = $this->getForm();
                    break;
                }
            case 'view' : {
                    $this->data['content'] = $this->viewForm();
                    break;
                }
            case 'delete' : {
                    $this->data['content'] = json_encode($this->dataGrid());
                    break;
                }
            case 'datagrid' : {
                    $this->data['content'] = json_encode($this->dataGrid());
                }
        }
    }

    public function viewForm() {

        $content = $this->displayBox(array("label" => "Skill name &nbsp;:", "value" => $this->skillName)) .
                $this->displayBox(array("label" => "Skill description &nbsp;:", "value" => $this->skill_description)) .
                $this->displayBox(array("label" => "Show on home&nbsp;:", "value" => $this->show_on_home == 'y' ? 'Active' : 'Deactive')) .
                $this->displayBox(array("label" => "Added on&nbsp;:", "value" => date(PHP_DATE_FORMAT, strtotime($this->createdDate)))) ;
        return $content;
    }

    public function getForm() {

        $content = '';
        $languages = $this->db->select("tbl_languages", '*', array("status" => 'a'))->results();
        $html = null;
        foreach ($languages as $key => $value) {
            $html .= get_view(DIR_ADMIN_TMPL . $this->module . "/textfield.tpl.php", array(
                '%label%' => 'Skill name to show on home page',
                '%id%' => $value['id'],
                '%languageName%' => $value['languageName'],
                '%fieldName%' => 'skillName['.$value['id'].']',
                '%fieldValue%' => (isset($this->{"skillName_" . $value['id']}) ? $this->{"skillName_" . $value['id']} : null)));
        }
        ///////////
        $show_on_home_a = ($this->show_on_home == 'y' ? 'checked' : '');
        $show_on_home_d = ($this->show_on_home == 'n' ? 'checked' : '');


        $replace = array(
            "%html%"      => $html,
            "%MEND_SIGN%" => MEND_SIGN,
            "%SKILL_OPTIONS%" => $this->skill_options(),
            "%SKILL_DESCRIPTION%" => $this->skill_description,

			"%OLD_IMAGE%" => tim_thumb_image($this->image,"skill"),
			"%SITE_SKILLIMG%" => SITE_UPD."skill/",
			"%DIR_SKILLIMG%" => DIR_UPD."skill/",


            "%OUTPUTPROFILEIMG%" => tim_thumb_image($this->image,"skill",100,100),
            "%STATUS_A%" => $show_on_home_a,
            "%STATUS_D%" => $show_on_home_d,
            "%TYPE%" => $this->type,
            "%ID%" => $this->id
        );
        $content = get_view(DIR_ADMIN_TMPL . $this->module . "/form-nct.tpl.php", $replace);
        return sanitize_output($content);
    }

	public function getSelectBoxOption(){
		$selectBoxOption = get_view(DIR_ADMIN_TMPL.$this->module."/select_option-nct.tpl.php");
		return sanitize_output($selectBoxOption);
	}

	public function skill_options() {
		$content = '';
		$getSelectBoxOption=$this->getSelectBoxOption();
		$fields = array("%VALUE%","%SELECTED%","%DISPLAY_VALUE%");
        if($this->id > 0)
        {
            $sId = $this->db->pdoQuery("SELECT GROUP_CONCAT(DISTINCT skill) AS skillIds FROM tbl_top_skill_list WHERE topSkill = ".$this->id)->result();
                $skillIds = $sId['skillIds'];
        }else{
            $skillIds = array();
        }

        if(isset($skillIds))	{
            $skillsResult = explode(',', $skillIds);
        }else{
            $skillsResult = array();
        }

		//dump_exit($skillsResult);
        $qrySelskills=$this->db->pdoQuery("SELECT id, skillName_1 AS skillName FROM tbl_skills where status='a' ORDER BY skillName ASC")->results();
        foreach ($qrySelskills as $selResult) {
            $selected = (in_array($selResult['id'],$skillsResult))?"selected":"";
            $fields_replace = array($selResult['id'],$selected,$selResult['skillName']);
            $content.=str_replace($fields,$fields_replace,$getSelectBoxOption);
        }
		return sanitize_output($content);
	}

    public function dataGrid() {
        $langId    = isset($langId) ? $langId : 1;
        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%'), array('\_', '\%'), $chr);

        $whereCond = '';
        if (isset($chr) && $chr != '') {
            $whereCond .= " WHERE ( skillName_$langId LIKE '%" . $chr . "%' OR skill_description LIKE '%" . $chr . "%' )";
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = "skillName_$langId ASC";


        $sql = "SELECT * FROM " . $this->table . " " . $whereCond . " order by " . $sorting;
        $sql_with_limit = $sql . " LIMIT " . $offset . " ," . $rows . " ";

        $getTotalRows = $this->db->pdoQuery($sql)->results();
        $totalRow = count($getTotalRows);

        $qrySel = $this->db->pdoQuery($sql_with_limit)->results();

        foreach ($qrySel as $fetchRes) {
            $id = $fetchRes['id'];
            $show_on_home = $fetchRes['show_on_home'];

            $show_on_home = ($fetchRes['show_on_home'] == "y") ? "checked" : "";

            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $id . "", "check" => $show_on_home)) : '';
            $operation = '';

            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $id . "", "class" => "btn default btn-xs black btnEdit", "value" => '<i class="fa fa-edit"></i>&nbsp;Edit')) : '';
            $operation .=(in_array('delete', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $id . "", "class" => "btn default btn-xs red btn-delete", "value" => '<i class="fa fa-trash-o"></i>&nbsp;Delete')) : '';
            $operation .=(in_array('view', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $id . "", "class" => "btn default blue btn-xs btn-viewbtn", "value" => '<i class="fa fa-laptop"></i>&nbsp;View')) : '';


            $final_array = array(
                filtering($fetchRes["id"], 'output', 'int'),
                filtering($fetchRes["skillName_".$langId])
            );

            if (in_array('status', $this->Permission)) {
                $final_array = array_merge($final_array, array($switch));
            }
            if (in_array('edit', $this->Permission) || in_array('delete', $this->Permission) || in_array('view', $this->Permission)) {
                $final_array = array_merge($final_array, array($operation));
            }

            $row_data[] = $final_array;
        }
        $result["sEcho"] = $sEcho;
        $result["iTotalRecords"] = (int) $totalRow;
        $result["iTotalDisplayRecords"] = (int) $totalRow;
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

    public function getPageContent() {
        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".tpl.php");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $final_result = $main_content->parse();
        return $final_result;
    }

}
















