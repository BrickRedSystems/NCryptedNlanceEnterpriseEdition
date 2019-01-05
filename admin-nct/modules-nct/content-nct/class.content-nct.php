<?php

class Content extends Home
{

    public $page_name;
    public $page_title;
    public $meta_keyword;
    public $meta_desc;
    public $page_desc;
    public $isActive;
    public $data = array();

    public function __construct($module, $id = 0, $objPost = null, $searchArray = array(), $type = '')
    {
        global $db, $fb, $fields, $sessCataId;
        $this->db         = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields     = $fields;
        $this->module     = $module;
        $this->table      = 'tbl_content';

        $this->type        = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $fetchRes = $this->db->select($this->table, "*", array("pageId" => $id))->result();
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
                    $this->data['content'] = $this->getForm();
                    break;
                }
            case 'edit':{
                    $this->data['content'] = $this->getForm();
                    break;
                }
            case 'view':{
                    $this->data['content'] = $this->viewForm();
                    break;
                }
            case 'delete':{
                    $this->data['content'] = json_encode($this->dataGrid());
                    break;
                }
            case 'datagrid':{
                    $this->data['content'] = json_encode($this->dataGrid());
                    break;
                }
        }
    }

    public function viewForm()
    {
        $languages = $this->db->select("tbl_languages", '*', array("status" => 'a'))->results();
        $content = null;
        foreach ($languages as $key => $value) {
            $content .= $this->displayBox(array("label" => "Page Title (".$value['languageName']."):", "value" => filtering($this->{"pageTitle_".$value['id']})));
            $content .= $this->displayBox(array("label" => "Page Description (".$value['languageName']."):", "value" => filtering($this->{"pageDesc_".$value['id']}, 'output', 'text')));
        }
        $content .=
        $this->displayBox(array("label" => "Met Keyword&nbsp;:", "value" => filtering($this->metaKeyword))) .
        $this->displayBox(array("label" => "Meta Description&nbsp;:", "value" => filtering($this->metaDesc))) .        
        $this->displayBox(array("label" => "Page Slug&nbsp;:", "value" => filtering($this->pageSlug)));
        return $content;
    }

    public function getForm()
    {
        $content      = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-nct.tpl.php");
        $main_content = $main_content->parse();
        $static_a     = ($this->isActive == 'y' ? 'checked' : '');
        $static_d     = ($this->isActive != 'y' ? 'checked' : '');

        $languages = $this->db->select("tbl_languages", '*', array("status" => 'a'))->results();

        $html = null;

        foreach ($languages as $key => $value) {
            //dump($value);
            
                $html .= get_view(DIR_ADMIN_TMPL . $this->module . "/title.tpl.php", array('%id%' => $value['id'], '%languageName%' => $value['languageName'], '%PAGE_TITLE%' => (isset($this->{"pageTitle_" . $value['id']}) ? $this->{"pageTitle_" . $value['id']} : null)));
                $html .= get_view(DIR_ADMIN_TMPL . $this->module . "/cke.tpl.php", array('%id%' => $value['id'], '%languageName%' => $value['languageName'], '%PAGE_DESCRIPTION%' => (isset($this->{"pageDesc_" . $value['id']}) ? $this->{"pageDesc_" . $value['id']} : null)));
            

        }
        $html .= get_view(DIR_ADMIN_TMPL . $this->module . "/metaKey.tpl.php");
        $html .= get_view(DIR_ADMIN_TMPL . $this->module . "/desc.tpl.php");

        $fields = array(
            "%html%",
            "%META_KEYWORD%",
            "%META_DESCRIPTION%",
            "%STATIC_A%",
            "%STATIC_D%",
            "%TYPE%",
            "%ID%",
        );

        $fields_replace = array(
            $html,
            (isset($this->metaKeyword) ? filtering($this->metaKeyword) : null),
            (isset($this->metaDesc) ? filtering($this->metaDesc) : null),
            filtering($static_a),
            filtering($static_d),
            filtering($this->type),
            filtering($this->id, 'input', 'int'),
        );

        $content = str_replace($fields, $fields_replace, $main_content);
        return sanitize_output($content);
    }

    public function dataGrid()
    {
        $content = $operation = $whereCond = $totalRow = null;
        $result  = $tmp_rows  = $row_data  = array();
        extract($this->searchArray);
        $chr       = str_replace(array('_', '%', "'"), array('\_', '\%', "\'"), $chr);
        $whereCond = array();
        if (isset($chr) && $chr != '') {
            $whereCond = array("pageTitle LIKE" => "%$chr%", "OR pageSlug LIKE" => "%$chr%");
        }

        if (isset($sort)) {
            $sorting = $sort . ' ' . $order;
        } else {
            $sorting = 'pageId DESC';
        }

        $totalRow = $this->db->count($this->table, $whereCond);

        $qrySel = $this->db->select("tbl_content", "*", $whereCond, " ORDER BY $sorting limit $offset , $rows")->results();
        $defaultLangId =  getTableValue("tbl_languages", "id", array("isDefault" => 'y'));
        foreach ($qrySel as $fetchRes) {
            $status = ($fetchRes['isActive'] == "y") ? "checked" : "";

            $switch    = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['pageId'] . "", "check" => $status)) : '';
            $operation = '';
            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['pageId'] . "", "class" => "btn default btn-xs black btnEdit", "value" => '<i class="fa fa-edit"></i>&nbsp;Edit')) : '';
            $operation .= (in_array('delete', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['pageId'] . "", "class" => "btn default btn-xs red btn-delete", "value" => '<i class="fa fa-trash-o"></i>&nbsp;Delete')) : '';
            $operation .= (in_array('view', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['pageId'] . "", "class" => "btn default blue btn-xs btn-viewbtn", "value" => '<i class="fa fa-laptop"></i>&nbsp;View')) : '';

            
            $final_array = array(
                filtering($fetchRes["pageId"]),
                filtering($fetchRes["pageTitle_".$defaultLangId]),
                filtering($fetchRes["pageSlug"]),
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

        $main_content   = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/switch-nct.tpl.php');
        $main_content   = $main_content->parse();
        $fields         = array("%NAME%", "%CLASS%", "%ACTION%", "%EXTRA%", "%CHECK%");
        $fields_replace = array($text['name'], $text['class'], $text['action'], $text['extraAtt'], $text['check']);
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
        $fields           = array("%HREF%", "%CLASS%", "%VALUE%", "%EXTRA%");
        $fields_replace   = array($text['href'], $text['class'], $text['value'], $text['extraAtt']);
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function displaybox($text)
    {

        $text['label']     = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
        $text['value']     = isset($text['value']) ? $text['value'] : '';
        $text['name']      = isset($text['name']) ? $text['name'] : '';
        $text['class']     = isset($text['class']) ? 'form-control-static ' . trim($text['class']) : 'form-control-static';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
        $text['extraAtt']  = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        $main_content   = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/displaybox.tpl.php');
        $main_content   = $main_content->parse();
        $fields         = array("%LABEL%", "%CLASS%", "%VALUE%");
        $fields_replace = array($text['label'], $text['class'], $text['value']);
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
