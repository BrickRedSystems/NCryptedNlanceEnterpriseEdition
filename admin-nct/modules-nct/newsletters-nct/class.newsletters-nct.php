<?php

class newsletter extends Home {

    public $status;
    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') {
        global $db, $fb, $fields, $sessCataId;
        $this->fb = $fb;
$this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_newsletters';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
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
                    break;
                }
            case 'sendNL' : {
                    $this->data['content'] = $this->getSendNLForm();
                    break;
                }
        }
    }

    public function viewForm() {
        $content = $this->displayBox(array("label" => "Newsletter Name&nbsp;:", "value" => $this->newsletter_name)) .
                $this->displayBox(array("label" => "Newsletter Subject&nbsp;:", "value" => $this->newsletter_subject)) .
                $this->displayBox(array("label" => "Newsletter Content&nbsp;:", "value" => filtering($this->newsletter_content, 'output', 'text'))) .
                $this->displayBox(array("label" => "Status&nbsp;:", "value" => $this->status == 'a' ? 'Active' : 'Deactive')) .
                $this->displayBox(array("label" => "Posted date&nbsp;:", "value" => date(PHP_DATE_FORMAT, strtotime($this->added_on))));
        return $content;
    }

    public function getForm() {

        $content = '';
        /////////////
        $html = null;
        $languages = $this->db->select("tbl_languages", '*', array("status" => 'a'))->results();
        foreach ($languages as $key => $value) {
            //dump($this->{"templates_" . $value['id']});
            $html .= get_view(DIR_ADMIN_TMPL . $this->module . "/subject.tpl.php", array('%id%' => $value['id'], '%languageName%' => $value['languageName'], '%NLSUBJECT%' => (isset($this->{"newsletter_subject_" . $value['id']}) ? $this->{"newsletter_subject_" . $value['id']} : null)));
            $html .= get_view(DIR_ADMIN_TMPL . $this->module . "/templates.tpl.php", array('%id%' => $value['id'], '%languageName%' => $value['languageName'], '%NLCONTENT%' => (isset($this->{"newsletter_content_" . $value['id']}) ? $this->{"newsletter_content_" . $value['id']} : null)));

        }
        /////////////

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-nct.tpl.php");
        $main_content = $main_content->parse();
        $status_a = ($this->status == 'a' ? 'checked' : '');
        $status_d = ($this->status != 'a' ? 'checked' : '');

        $fields = array(
            "%html%",
            "%MEND_SIGN%",
            "%NLNAME%",
            "%STATUS_A%",
            "%STATUS_D%",
            "%TYPE%",
            "%ID%"
        );

        $fields_replace = array(
            $html,
            MEND_SIGN,
            filtering($this->newsletter_name),
            filtering($status_a),
            filtering($status_d),
            filtering($this->type),
            filtering($this->id, 'output', 'int')
        );

        $content = str_replace($fields, $fields_replace, $main_content);
        return sanitize_output($content);
    }

    public function getSendNLForm() {
        $content = '';

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/nlform-nct.tpl.php");
        $main_content = $main_content->parse();

        $fields = array(
            "%MEND_SIGN%",
            "%USERS%",
            "%TYPE%",
            "%ID%"
        );
        
        $subscribers_array = $this->getSubscribedUsers();
        
        $html_result = $subscribers_array['html_result'];
        $total_count = $subscribers_array['total_count'];
        
        $fields_replace = array(
            MEND_SIGN,
            $html_result,
            filtering($this->type),
            filtering($this->id, 'output', 'int')
        );
        
        $content = str_replace($fields, $fields_replace, $main_content);
        return filtering($content, 'output', 'text');
    }

    public function dataGrid() {
        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%'), array('\_', '\%'), $chr);
        $whereCond = '';
        if (isset($chr) && $chr != '') {
            //$whereCond["treatment_name LIKE"] = "%$chr%";
            $whereCond = " WHERE nl.newsletter_name LIKE '%" . $chr . "%'  OR nl.newsletter_subject LIKE '%" . $chr . "%' OR DATE_FORMAT(nl.added_on, '" . MYSQL_DATE_FORMAT . "') LIKE '%" . $chr . "%'";
        }

        if (isset($sort))
        //$sorting = $sort . ' ' . $order;
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'nl.id';

        //$totalRow = $this->db->count($this->table, $whereCond);

        $totalRow = $this->db->pdoQuery('SELECT COUNT(nl.id) as totalRows FROM tbl_newsletters nl '
                        . $whereCond . ' ORDER BY ' . $sorting . ' limit ' . $offset . ", " . $rows)->result();
        $totalRow = $totalRow['totalRows'];


        //$qrySel = $this->db->select("tbl_treatments", array("id", "treatment_name","category_id","subcategory_id", "status", "added_on"), $whereCond, " ORDER BY $sorting limit $offset , $rows")->results();

        $qrySel = $this->db->pdoQuery('SELECT nl.* FROM tbl_newsletters nl '
                        . $whereCond . ' ORDER BY ' . $sorting . ' limit ' . $offset . ", " . $rows)->results();

        foreach ($qrySel as $fetchRes) {
            $id = $fetchRes['id'];
            $status = $fetchRes['status'];

            $status = ($fetchRes['status'] == "a") ? "checked" : "";

            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $id . "", "check" => $status)) : '';
            $operation = '';
           
            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $id . "", "class" => "btn default btn-xs black btnEdit", "value" => '<i class="fa fa-edit"></i>&nbsp;Edit')) : '';
             if($id!=1){
            $operation .=(in_array('delete', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $id . "", "class" => "btn default btn-xs red btn-delete", "value" => '<i class="fa fa-trash-o"></i>&nbsp;Delete')) : '';
            }
            $operation .=(in_array('view', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['id'] . "", "class" => "btn default blue btn-xs btn-viewbtn", "value" => '<i class="fa fa-laptop"></i>&nbsp;View')) : '';
             if($id!=1){
            $operation .=(in_array('sendNL', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=sendNL&id=" . $fetchRes['id'] . "", "class" => "btn default btn-xs black btnEdit", "value" => '<i class="fa fa-laptop"></i>&nbsp;Send Newsletter')) : '';
            }


            //$row_data[] = array($fetchRes["countryName"],$switch,$operation);			

            $final_array = array(filtering($fetchRes['id'], 'output', 'int'));
            $final_array = array_merge($final_array, array(filtering($fetchRes["newsletter_name"])));
            $final_array = array_merge($final_array, array(filtering($fetchRes["newsletter_subject_1"])));
            $final_array = array_merge($final_array, array(date(PHP_DATE_FORMAT,strtotime($fetchRes['added_on']))));
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

    public function getSubscribedUsers() {
        $final_result = $users_select = "";
        $response_array = array();

        $users = $this->db->select("tbl_subscribers", "*", array("status" => "a"))->results();

        if ($users) {
            //$single_user_tpl = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/single-user-nct.tpl.php");
            //$single_user_tpl_parsed = $single_user_tpl->parse();
            $single_user_tpl_parsed = $this->getSelectBoxOption();
            
            $fields = array("%VALUE%", "%SELECTED%", "%DISPLAY_VALUE%");
            
            foreach ($users as $single_user) {
                $selected = "";

                $fields_replace = array(
                    filtering($single_user['id'], 'output', 'int'),
                    $selected,
                    filtering($single_user['email'])
                );

                $users_select .= str_replace($fields, $fields_replace, $single_user_tpl_parsed);
            }
            
            $users_dd_tpl = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/users-dd-nct.tpl.php");
            $users_dd_tpl->set('users', $users_select);
            
            $final_result = $users_dd_tpl->parse();
        }
        
        $response_array['html_result'] = $final_result;
        $response_array['total_count'] = count($users);

        return $response_array;
    }

}
