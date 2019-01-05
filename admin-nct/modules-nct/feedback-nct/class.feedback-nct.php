<?php

class Feedback extends Home {

    public $category;
    public $status;
    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') {
        global $db, $fields, $sessCataId;
        $this -> db = $db;
        $this -> data['id'] = $this -> id = $id;
        $this -> fields = $fields;
        $this -> module = $module;
        $this -> table = 'tbl_feedback';

        $this -> type = ($this -> id > 0 ? 'edit' : 'add');
        $this -> searchArray = $searchArray;
        parent::__construct();
        if ($this -> id > 0) {
            $qrySel = $this -> db -> select('tbl_feedback', "*", array("id" => $id)) -> result();
            $fetchRes = $qrySel;
            $this -> data['firstName'] = $this -> firstName = filtering($fetchRes['firstName']);
            $this -> data['lastName'] = $this -> lastName = filtering($fetchRes['lastName'], 'output', 'text');
            $this -> data['message'] = $this -> message = filtering($fetchRes['message']);
            $this -> data['email'] = $this -> email = filtering($fetchRes['email']);
            $this -> data['status'] = $this -> status = filtering($fetchRes['status']);
        }
        else {
            $this -> data['firstName'] = $this -> firstName = '';
            $this -> data['lastName'] = $this -> lastName = '';
            $this -> data['message'] = $this -> message = '';
            $this -> data['email'] = $this -> email = '';
            $this -> data['status'] = $this -> status = 'y';
        }
        switch ($type) {
            case 'add' : {
                $this -> data['content'] = (in_array('add', $this -> Permission)) ? $this -> getForm() : '';
                break;
            }
            case 'edit' : {
                $this -> data['content'] = (in_array('edit', $this -> Permission)) ? $this -> getForm() : '';
                break;
            }
            case 'view' : {
                $this -> data['content'] = (in_array('view', $this -> Permission)) ? $this -> viewForm() : '';
                break;
            }
            case 'delete' : {
                $this -> data['content'] = (in_array('delete', $this -> Permission)) ? json_encode($this -> dataGrid()) : '';
                break;
            }
            case 'datagrid' : {
                $this -> data['content'] = (in_array('module', $this -> Permission)) ? json_encode($this -> dataGrid()) : '';
            }
        }
    }

    public function viewForm() {
        /*$content = $this->fields->displayBox(array("label" => "Subject&nbsp;:", "value" => $this->subject)) .
         $content = $this->fields->displayBox(array("label" => "Templates&nbsp;:", "value" => $this->templates));
         return $content;*/
    }

    public function getForm() {
        $content = '';

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this -> module . "/form-nct.tpl.php");
        $main_content = $main_content -> parse();
        
        $fields = array(
            "%MEND_SIGN%",
            "%FIRSTNAME%",
            "%MESSAGE%",
            "%LASTNAME%",
            "%EMAIL%",
            "%ID%",
            "%TYPE%"
        );

        $fields_replace = array(
            MEND_SIGN,
            $this -> firstName,
            $this -> message,
            $this -> lastName,
            $this -> email,
            $this -> id,
            $this -> type
        );

        $content = str_replace($fields, $fields_replace, $main_content);
        return sanitize_output($content);
    }

    public function dataGrid() {

        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this -> searchArray);
        $chr = str_replace(array(
            '_',
            '%'
        ), array(
            '\_',
            '\%'
        ), $chr);
        $whereCond = '';
        if (isset($chr) && $chr != '') {
            $whereCond .= " WHERE email LIKE '%" . $chr . "%' OR firstName LIKE '%" . $chr . "%' OR lastName LIKE '%" . $chr . "%' OR message LIKE '%" . $chr . "%' ";
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'id DESC';

        //$totalRow = $this->db->count($this->table, $whereCond);
        $qrySel = $this -> db -> pdoQuery("SELECT  *
									   FROM tbl_feedback
									   " . $whereCond . " order by " . $sorting . " limit " . $offset . " ," . $rows . " ") -> results();
        $totalRow = count($qrySel);
        foreach ($qrySel as $fetchRes) {
            $id = $fetchRes['id'];
            $status = ($fetchRes['status'] == "y") ? "checked" : "";
          
            $operation = '';
            $operation .= (in_array('edit', $this -> Permission)) ? $this -> operation(array(
                "href" => "ajax." . $this -> module . ".php?action=edit&id=" . $fetchRes['id'] . "",
                "class" => "btn default btn-xs black btnEdit",
                "value" => '<i class="fa fa-edit"></i>&nbsp;Edit'
            )) : '';
            $operation .= (in_array('delete', $this -> Permission)) ? '&nbsp;&nbsp;' . $this -> operation(array(
                "href" => "ajax." . $this -> module . ".php?action=delete&id=" . $fetchRes['id'] . "",
                "class" => "btn default btn-xs red btn-delete",
                "value" => '<i class="fa fa-trash-o"></i>&nbsp;Delete'
            )) : '';
         
           
            $final_array = array(
                filtering($fetchRes["id"]),
                filtering($fetchRes["firstName"]) . ' ' . filtering($fetchRes["lastName"]),
                filtering($fetchRes['email']),
                filtering($fetchRes["message"])
            );

          
            if (in_array('edit', $this -> Permission) || in_array('delete', $this -> Permission) || in_array('view', $this -> Permission)) {
                $final_array = array_merge($final_array, array($operation));
            }
            $row_data[] = $final_array;
        }

        $result["sEcho"] = $sEcho;
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

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this -> module . '/switch-nct.tpl.php');
        $main_content = $main_content -> parse();
        $fields = array(
            "%NAME%",
            "%CLASS%",
            "%ACTION%",
            "%EXTRA%",
            "%CHECK%"
        );
        $fields_replace = array(
            $text['name'],
            $text['class'],
            $text['action'],
            $text['extraAtt'],
            $text['check']
        );
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function operation($text) {

        $text['href'] = isset($text['href']) ? $text['href'] : 'Enter Link Here: ';
        $text['value'] = isset($text['value']) ? $text['value'] : '';
        $text['name'] = isset($text['name']) ? $text['name'] : '';
        $text['class'] = isset($text['class']) ? '' . trim($text['class']) : '';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this -> module . '/operation-nct.tpl.php');
        $main_content = $main_content -> parse();
        $fields = array(
            "%HREF%",
            "%CLASS%",
            "%VALUE%",
            "%EXTRA%"
        );
        $fields_replace = array(
            $text['href'],
            $text['class'],
            $text['value'],
            $text['extraAtt']
        );
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function getPageContent() {
        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this -> module . "/" . $this -> module . ".tpl.php");
        $main_content -> breadcrumb = $this -> getBreadcrumb();
        $final_result = $main_content -> parse();
        return $final_result;
    }

}
