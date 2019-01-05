<?php
class Language extends Home
{

    public $languageName;
    public $isActive;
    public $data = array();

    public function __construct($id = 0, $searchArray = array(), $type = '')
    {
        $this->data['id']  = $this->id  = $id;
        $this->type        = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        $this->table = 'tbl_languages';
        if ($this->id > 0) {
            $fetchRes   = $this->db->select($this->table, array("id", "languageName", "isDefault", "status", "created_date"), array("id" => $id))->result();
            foreach ($fetchRes as $k => $v) {
                $this->{$k} = filtering($v);
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
                    $this->data['content'] = $this->viewForm();
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
        $content = $this->displayBox(array("label" => "Language name &nbsp;:", "value" => $this->languageName)) .
                   $this->displayBox(array("label" => "Status&nbsp;:", "value" => ($this->status == 'a') ? 'Active' : 'Inactive'));
        return $content;
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

    public function getForm()
    {
        $content = '';
        $content = '';

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-nct.tpl.php");
        $main_content = $main_content->parse();
        $default_y    = ($this->isDefault == 'y' ? 'checked' : '');
        $default_n    = ($this->isDefault != 'y' ? 'checked' : '');
        $status_a     = ($this->status == 'a' ? 'checked' : '');
        $status_d     = ($this->status != 'a' ? 'checked' : '');
        $def_tpl = "";
        if($this->isDefault != 'y'){
            $main_content_def = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/default-lang-nct.tpl.php");
            $main_content_def = $main_content_def->parse();
            $def_arr = array("%DEFAULT_Y%", "%DEFAULT_N%");
            $def_rep = array($default_y, $default_n);
            $def_tpl = str_replace($def_arr,$def_rep,$main_content_def);
        }
        $fields = array("%MEND_SIGN%", "%LANGUAGE_NAME%","%DEFAULT_LAN%", "%STATUS_A%", "%STATUS_D%", "%TYPE%", "%ID%");

        $fields_replace = array(MEND_SIGN, $this->languageName, $def_tpl , $status_a, $status_d, $this->type, $this->id);
        $content        = str_replace($fields, $fields_replace, $main_content);
        return sanitize_output($content);
    }

    public function dataGrid()
    {
        $content = $operation = $whereCond = $totalRow = null;
        $result  = $tmp_rows  = $row_data  = array();
        extract($this->searchArray);
        $chr       = str_replace(array('_', '%'), array('\_', '\%'), $chr);
        $whereCond = array('status !=' => 't');
        if (isset($chr) && $chr != '') {
            $whereCond = $whereCond + array("and languageName LIKE" => "%$chr%");
            $whereCond += array("OR created_date LIKE" => "%$chr%");
        }
        if (isset($sort)) {
            $sorting = $sort . ' ' . $order;
        } else {
            $sorting = 'isDefault ASC';
        }

        $totalRow = $this->db->count($this->table, $this->merge_key_value($whereCond));
        $qrySel   = $this->db->select($this->table, array("id", "languageName", "isDefault", "status", "created_date"), $whereCond, " ORDER BY $sorting limit $offset , $rows")->results();
        foreach ($qrySel as $fetchRes) {
            $id        = $fetchRes['id'];
            $status    = $fetchRes['status'];
            $status    = ($status == 'a') ? "checked" : "";
            $isDefault = (isset($fetchRes['isDefault'])) ? $fetchRes['isDefault'] : "";

            $switch    = $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['id'] . "", "check" => $status));
            $switch    = ($isDefault != 'y') ? $switch : '';
            $operation = (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['id'] . "", "class" => "btn default btn-xs black btnEdit", "value" => '<i class="fa fa-edit"></i>&nbsp;Edit')) : '';
            $operation .= (in_array('delete', $this->Permission) && $isDefault != 'y') ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['id'] . "", "class" => "btn default btn-xs red btn-delete", "value" => '<i class="fa fa-trash-o"></i>&nbsp;Delete')) : '';
            $operation .= (in_array('view', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['id'] . "", "class" => "btn default blue btn-xs btn-viewbtn", "value" => '<i class="fa fa-laptop"></i>&nbsp;View')) : '';

            $updated_date = date(PHP_DATETIME_FORMAT, strtotime($fetchRes["created_date"]));

            $final_array = array($fetchRes["languageName"] . (($isDefault == 'y') ? ' (Default Language)' : ''));
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
    public function getSelectBoxOption()
    {
        $content      = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/select_option-nct.tpl.php");
        $content .= $main_content->parse();
        return sanitize_output($content);
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
    public function getPageContent()
    {
        $final_result             = null;
        $main_content             = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".tpl.php");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $main_content->getForm    = $this->getForm();
        $final_result             = $main_content->parse();
        return $final_result;
    }

    public function updateRecords($table, $valArray, $awhere)
    {
        $this->db->update($table, $valArray, $awhere);
    }

    public function insertRecords($table, $valArray)
    {
        return $this->db->insert($table, $valArray)->getLastInsertId();
    }

    public function deleteRecords($table, $awhere)
    {
        $this->db->delete($table, $awhere);
    }

    public function checkIfLangIsDefault($table, $where)
    {
        return getTableValue($table, "isDefault", $where);
    }

    public function merge_key_value($array, $sep = ' ')
    {
        $out = '';
        foreach ($array as $key => $value) {
            $out .= $key . $sep . "'" . $value . "'";
        }

        return $out;
    }

}
