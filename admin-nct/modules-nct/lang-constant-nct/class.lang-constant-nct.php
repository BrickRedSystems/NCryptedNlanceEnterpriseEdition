<?php
class Constant extends Home
{

    public $constantValue;
    public $constantName;
    public $data = array();

    public function __construct($id = 0, $searchArray = array(), $type = '')
    {
        $this->data['id'] = $this->id = $id;
        $this->table      = 'tbl_lang_constants';
        $this->type        = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $qrySel   = $this->db->select($this->table, array("id", "constantValue", "constantName", "created_date"), array("id" => $id))->result();
            $fetchRes = $qrySel;

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
            case 'langArray':{
                    $qryLang = $this->db->select("tbl_languages", array("id", "languageName"), array("status" => 'a'), "ORDER BY languageName")->results();
                    foreach ($qryLang as $fetchLang) {
                        $this->langArray[$fetchLang['id']] = $fetchLang['languageName'];
                    }
                    break;
                }
            case 'datagrid':{
                    $this->data['content'] = (in_array('module', $this->Permission)) ? json_encode($this->dataGrid()) : '';
                }
        }

    }

    public function viewForm()
    {
        $content = $this->displayBox(array("label" => "Constant &nbsp;:", "value" => $this->constantName)) .
        $this->displayBox(array("label" => "Value &nbsp;:", "value" => $this->constantValue));
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
        $tplNm = ($this->id > 0 && ENVIRONMENT == 'p') ? "/display_lang-constant-nct.tpl.php" : "/constant_name-nct.tpl.php";
   
        $dispalay_constant         = new MainTemplater(DIR_ADMIN_TMPL . $this->module . $tplNm);
        $dispalay_constant_content = $dispalay_constant->parse();
        $search                    = array("%CONSTANT_NAME%");
        $replace                   = array($this->constantName);
        $constant_name_field       = str_replace($search, $replace, $dispalay_constant_content);

      

        $qrySel = $this->db->select("tbl_languages", array("id", "languageName", "created_date"), array("status" => 'a'))->results();

        $i       = 0;
        $qrySubL = $this->db->select($this->table, array("subId"), array("id =" => $this->id))->result();
        $subId   = $qrySubL['subId'];

        $constant_value          = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/constant_value-nct.tpl.php");
        $constant_value_content  = $constant_value->parse();
        $constant_value_search   = array("%MEND_SIGN%", "%LANGUAGE_NAME%", "%CONSTANT_VALUE%", "%ID%");
        $constant_value_content1 = "";
        foreach ($qrySel as $fetchRes) {
            if ($this->type == 'edit') {
                $qrysel1  = $this->db->select($this->table, array("id", "constantValue", "created_date"), array("languageId =" => $fetchRes["id"]), " AND ( id = $this->id OR " . ($subId == 0 ? 'subId = ' . $this->id . '' : 'id = ' . $subId . '') . ")")->results();

                $fetchRow = $qrysel1;

                $this->constantValue = ($this->type == 'edit' && isset($fetchRow[0]['constantValue'])) ? $fetchRow[0]['constantValue'] : '';
                
                $constant_value_replace = array(MEND_SIGN, $fetchRes['languageName'], stripslashes($this->constantValue), $fetchRes['id']);
               
            } else {
               
                $constant_value_replace = array(MEND_SIGN, $fetchRes['languageName'], '', $fetchRes['id']);
            }
            //$i++;
            $constant_value_content1 .= str_replace($constant_value_search, $constant_value_replace, $constant_value_content);
        }

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-nct.tpl.php");
        $main_content = $main_content->parse();

        $fields = array("%MEND_SIGN%", "%CONSTANT_NAME_FIELD%", "%CONSTANT_VALUE%", "%TYPE%", "%ID%");

        $fields_replace = array(MEND_SIGN, $constant_name_field, $constant_value_content1, $this->type, $this->id);

        $content = str_replace($fields, $fields_replace, $main_content);
        return sanitize_output($content);
    }

    public function dataGrid()
    {
        $content = $operation = $whereCond = $whereCond1 = $totalRow = null;
        $result  = $tmp_rows  = $row_data  = array();
        extract($this->searchArray);
        $langId    = isset($langId) ? $langId : 1;
        $chr       = isset($chr) ? $chr : "";
        $chr       = str_replace(array('_', '%','"'), array('\_', '\%','\"'), $chr);
        $whereCond = array();

        if (isset($sort)) {
            $sorting = $sort . ' ' . $order;
        } else {
            $sorting = 'id DESC';
        }

        $totalRowTmp = $this->db->pdoQuery('select COUNT(id) AS nmrows from tbl_lang_constants where languageId = ' . $langId . ' AND (constantValue Like "%' . $chr . '%" OR constantName LIKE "%' . $chr . '%")')->result();
        $totalRow    = $totalRowTmp['nmrows'];

        $qrySel = 'select id,subId,constantValue,constantName,created_date from tbl_lang_constants where languageId = ' . $langId . ' AND (constantValue Like "%' . $chr . '%" OR constantName LIKE "%' . $chr . '%") ORDER BY ' . $sorting . ' limit ' . $offset . ' , ' . $rows;

        $Qrysel = $this->db->pdoQuery($qrySel);
        $qrysel = $Qrysel->results();

        foreach ($qrysel as $fetchRes) {

            if (strlen($fetchRes["constantValue"]) > 50) {
                //$fetchRes["constantValue"] = String_crop($fetchRes["constantValue"], 50);
            }

            $id        = $fetchRes['subId'] == 0 ? $fetchRes['id'] : $fetchRes['subId'];
            $operation = (in_array('edit', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => SITE_ADM_MOD . $this->module . "/ajax." . $this->module . ".php?action=edit&id=" . $id, "class" => "btn default btn-xs black btnEdit", "value" => '<i class="fa fa-edit"></i>&nbsp;Edit')) : '';

            $operation .= (in_array('delete', $this->Permission) && ENVIRONMENT == 'd') ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['id'] . "", "class" => "btn default btn-xs red btn-delete", "value" => '<i class="fa fa-trash-o"></i>&nbsp;Delete')) : '';
            $operation .= (in_array('view', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['id'] . "", "class" => "btn default blue btn-xs btn-viewbtn", "value" => '<i class="fa fa-laptop"></i>&nbsp;View')) : '';

            $englishValue = getTableValue("tbl_lang_constants", "constantValue", array("id" => $fetchRes['subId']));

            $final_array = array(
                stripslashes($fetchRes["constantName"]),
                $fetchRes['constantValue'],

            );

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
        $main_content->langArray  = $this->langArray;        
        $final_result             = $main_content->parse();
        
        return $final_result;
    }

    public function getRecords($qrySel, $valArray)
    {
        return $this->db->pdoQuery($qrySel, $valArray);
    }

    public function updateRecords($table, $valArray, $awhere)
    {
        return $this->db->update($table, $valArray, $awhere);
    }

    public function insertRecords($table, $valArray)
    {
        return $this->db->insert($table, $valArray)->getLastInsertId();
    }

    public function getRecordsCount($table, $valArray)
    {
        return $this->db->count($table, $valArray);
    }

    public function deleteRecords($table, $aWhere)
    {
        $this->db->delete($table, $aWhere);
    }

}
