<?php

class Templates extends Home
{

    public $category;
    public $status;
    public $data = array();

    public function __construct($module, $id = 0, $objPost = null, $searchArray = array(), $type = '')
    {
        global $db, $fields, $sessCataId;
        $this->db         = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields     = $fields;
        $this->module     = $module;
        $this->table      = 'tbl_email_templates';

        $this->type        = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $fetchRes = $this->db->select($this->table, "*", array("id" => $id))->result();
            foreach ($fetchRes as $k => $v) {
                $this->{$k} = $v;
            }
        } else {
            $fetchRes = $this->db->pdoQuery("SHOW COLUMNS FROM " . $this->table)->results();
            foreach ($fetchRes as $k => $v) {
                $this->{$v["Field"]} = $v["Default"];
            }
        }
        switch ($type) {
            case 'add':{
                    $this->data['content'] = (in_array('add', $this->Permission)) ? $this->getForm() : '';
                    break;
                }
            case 'edit':{
                    $this->data['content'] = (in_array('edit', $this->Permission)) ? $this->getForm() : '';
                    break;
                }
            case 'view':{
                    $this->data['content'] = (in_array('view', $this->Permission)) ? $this->viewForm() : '';
                    break;
                }
            case 'delete':{
                    $this->data['content'] = (in_array('delete', $this->Permission)) ? json_encode($this->dataGrid()) : '';
                    break;
                }
            case 'datagrid':{
                    $this->data['content'] = (in_array('module', $this->Permission)) ? json_encode($this->dataGrid()) : '';
                }
        }
    }

    public function viewForm()
    {
        $content = $this->fields->displayBox(array(
            "label" => "Subject&nbsp;:",
            "value" => $this->subject,
        )) . $content = $this->fields->displayBox(array(
            "label" => "Templates&nbsp;:",
            "value" => $this->templates,
        ));
        return $content;
    }

    public function getForm()
    {
        $content = '';
        /////////////
        $html = null;
        $languages = $this->db->select("tbl_languages", '*', array("status" => 'a'))->results();
        foreach ($languages as $key => $value) {
            //dump($this->{"templates_" . $value['id']});
            $html .= get_view(DIR_ADMIN_TMPL . $this->module . "/subject.tpl.php", array('%id%' => $value['id'], '%languageName%' => $value['languageName'], '%SUBJECT%' => (isset($this->{"subject_" . $value['id']}) ? $this->{"subject_" . $value['id']} : null)));
            $html .= get_view(DIR_ADMIN_TMPL . $this->module . "/templates.tpl.php", array('%id%' => $value['id'], '%languageName%' => $value['languageName'], '%TEMPLATES%' => (isset($this->{"templates_" . $value['id']}) ? $this->{"templates_" . $value['id']} : null)));

        }
        /*$html .= get_view(DIR_ADMIN_TMPL . $this->module . "/metaKey.tpl.php");
        $html .= get_view(DIR_ADMIN_TMPL . $this->module . "/desc.tpl.php");*/
        /////////////

        $replace = array(
            "%html%" => $html,
            "%MEND_SIGN%"   => MEND_SIGN,            
            "%DESCRIPTION%" => $this->description,            
            "%TYPES%"       => $this->types,
            "%TYPE%"        => $this->type,
            "%CONSTANT%"    => $this->constant,
            "%ID%"          => $this->id,
        );
        $content = get_view(DIR_ADMIN_TMPL . $this->module . "/form-nct.tpl.php", $replace);
        return sanitize_output($content);
    }

    public function dataGrid()
    {

        $content = $operation = $whereCond = $totalRow = null;
        $result  = $tmp_rows  = $row_data  = array();
        extract($this->searchArray);
        $chr       = str_replace(array('_', '%', "'"), array('\_', '\%', "\'"), $chr);
        $whereCond = '';
        if (isset($chr) && $chr != '') {
            $whereCond .= " WHERE constant LIKE '%" . $chr . "%' OR types LIKE '%" . $chr . "%' OR types LIKE '%" . $chr . "%' ";
        }

        if (isset($sort)) {
            $sorting = $sort . ' ' . $order;
        } else {
            $sorting = 'id DESC';
        }

        //$totalRow = $this->db->count($this->table, $whereCond);
        $qrySel = $this->db->pdoQuery("SELECT  id, types, constant, subject, templates, description, updateDate,status
                                       FROM tbl_email_templates
                                       " . $whereCond . " order by " . $sorting . " limit " . $offset . " ," . $rows . " ")->results();
        $qrySel_tot = $this->db->pdoQuery("SELECT  id, types, constant, subject, templates, description, updateDate,status
                                       FROM tbl_email_templates
                                       " . $whereCond . " order by " . $sorting )->results();
        $totalRow = count($qrySel_tot);
        foreach ($qrySel as $fetchRes) {
            $id     = $fetchRes['id'];
            $status = ($fetchRes['status'] == "y") ? "checked" : "";
            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array(
                "action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "",
                "check"  => $status,
            )) : '';
            $operation = '';
            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array(
                "href"  => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['id'] . "",
                "class" => "btn default btn-xs black btnEdit",
                "value" => '<i class="fa fa-edit"></i>&nbsp;Edit',
            )) : '';

            $final_array = array(
                filtering($fetchRes["id"]),
                filtering($fetchRes["constant"]),
                filtering($fetchRes["types"]),
                filtering($fetchRes["description"]),
            );

            if (in_array('status', $this->Permission)) {
                $final_array = array_merge($final_array, array($switch));
            }
            if (in_array('edit', $this->Permission) || in_array('delete', $this->Permission) || in_array('view', $this->Permission)) {
                $final_array = array_merge($final_array, array($operation));
            }
            $row_data[] = $final_array;
        }

        $result["sEcho"]                = $sEcho;
        $result["iTotalRecords"]        = (int) $totalRow;
        $result["iTotalDisplayRecords"] = (int) $totalRow;
        $result["aaData"]               = $row_data;
        return $result;
    }

    public function toggel_switch($text)
    {
        $text['action']   = isset($text['action']) ? $text['action'] : 'Enter Action Here: ';
        $text['check']    = isset($text['check']) ? $text['check'] : '';
        $text['name']     = isset($text['name']) ? $text['name'] : '';
        $text['class']    = isset($text['class']) ? '' . trim($text['class']) : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/switch-nct.tpl.php');
        $main_content = $main_content->parse();
        $fields       = array(
            "%NAME%",
            "%CLASS%",
            "%ACTION%",
            "%EXTRA%",
            "%CHECK%",
        );
        $fields_replace = array(
            $text['name'],
            $text['class'],
            $text['action'],
            $text['extraAtt'],
            $text['check'],
        );
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function operation($text)
    {

        $text['href']     = isset($text['href']) ? $text['href'] : 'Enter Link Here: ';
        $text['value']    = isset($text['value']) ? $text['value'] : '';
        $text['name']     = isset($text['name']) ? $text['name'] : '';
        $text['class']    = isset($text['class']) ? '' . trim($text['class']) : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
        $main_content     = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/operation-nct.tpl.php');
        $main_content     = $main_content->parse();
        $fields           = array(
            "%HREF%",
            "%CLASS%",
            "%VALUE%",
            "%EXTRA%",
        );
        $fields_replace = array(
            $text['href'],
            $text['class'],
            $text['value'],
            $text['extraAtt'],
        );
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function getPageContent()
    {
        $final_result             = null;
        $main_content             = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".tpl.php");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $final_result             = $main_content->parse();
        return $final_result;
    }

}
