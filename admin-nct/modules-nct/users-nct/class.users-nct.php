<?php

class Users extends Home {

    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') {
        global $db, $fields, $sessCataId;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_users';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();
        if ($this->id > 0) {
            $query = "SELECT u.* 
                    FROM tbl_users u 
                    WHERE u.userId = '" . $this->id . "' ";

            $qrySel = $this->db->pdoQuery($query)->result();

            $fetchRes = $qrySel;

            $this->data['firstName'] = $this->firstName = filtering($fetchRes['firstName']);
            $this->data['lastName'] = $this->lastName = filtering($fetchRes['lastName']);
            $this->data['email'] = $this->email = filtering($fetchRes['email']);
            $this->data['userType'] = $this->userType = filtering($fetchRes['userType']);
            $this->data['status'] = $this->status = $fetchRes['status'];
        } else {
            $this->data['firstName'] = $this->firstName = '';
            $this->data['lastName'] = $this->lastName = '';
            $this->data['email'] = $this->email = '';
            $this->data['status'] = $this->status = 'a';
			$this->data['userType'] = $this->userType = 'c';
        }
		
        switch ($type) {
            case 'add' : {
                    $this->data['content'] = (in_array('add', $this->Permission)) ? $this->getForm() : '';
                    break;
                }
            case 'edit' : {
                    $this->data['content'] = (in_array('edit', $this->Permission)) ? $this->getForm() : '';
                    break;
                }
            case 'view' : {
                    $this->data['content'] = (in_array('view', $this->Permission)) ? $this->viewForm() : '';
                    break;
                }
            case 'delete' : {
                    $this->data['content'] = (in_array('delete', $this->Permission)) ? json_encode($this->dataGrid()) : '';
                    break;
                }
            case 'datagrid' : {
                    $this->data['content'] = (in_array('module', $this->Permission)) ? json_encode($this->dataGrid()) : 'test';
                }
        }
    }

    public function viewForm() {
    	
        $content = $this->displayBox(array("label" => "First Name&nbsp;:", "value" => $this->firstName)) 
                . $this->displayBox(array("label" => "Last Name&nbsp;:", "value" => $this->lastName)) 
                . $this->displayBox(array("label" => "Email&nbsp;:", "value" => $this->email)) 
                . $this->displayBox(array("label" => "User Type&nbsp;:", "value" => $this->userType == 'p' ? 'Provider' : 'Customer'))
                . $this->displayBox(array("label" => "Status&nbsp;:", "value" => $this->status == 'a' ? 'Active' : 'Deactive'));
        return $content;
    }

    public function getForm() {
        $content = '';

        $getSelectBoxOption = $this->getSelectBoxOption();
        $fields = array("%VALUE%", "%SELECTED%", "%DISPLAY_VALUE%");

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-nct.tpl.php");
        $main_content = $main_content->parse();
        $static_a = ($this->status == 'a' ? 'checked' : '');
        $static_d = ($this->status != 'a' ? 'checked' : '');


        $fields = array(
            "%FIRST_NAME%",
            "%LAST_NAME%",
            "%EMAIL_ADDRESS%",
            "%STATUS_A%",
            "%STATUS_D%",
            "%TYPE%",
            "%ID%"
        );

        $fields_replace = array(
            $this->data['firstName'],
            $this->data['lastName'],
            $this->data['email'],
            $static_a,
            $static_d,
            $this->type,
            $this->id
        );

        $content = str_replace($fields, $fields_replace, $main_content);
        return filtering($content, 'output', 'text');
    }

    public function dataGrid() {
    	
        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
		
        $chr = str_replace(array('_', '%',"'"), array('\_', '\%',"\'"), $chr);
        //$whereCond = ' where  status!=\'a\'';
        if (isset($chr) && $chr != '') {
            $whereCond .= "  WHERE (firstName LIKE '%" . $chr . "%' OR lastName LIKE '%" . $chr . "%' OR email LIKE '%" . $chr . "%' OR DATE_FORMAT(createdDate, '" . MYSQL_DATE_FORMAT . "') LIKE '%" . $chr . "%')";
        }

        if (isset($day) && $day != '') {
            if ($whereCond) {
                $whereCond .= " AND ";
            } else {
                $whereCond .= " WHERE ";
            }
            $whereCond .= " DAY(u.createdDate) = '" . $day . "' ";
        }

        if (isset($month) && $month != '') {
            if ($whereCond) {
                $whereCond .= " AND ";
            } else {
                $whereCond .= " WHERE ";
            }
            $whereCond .= " MONTH(u.createdDate) = '" . $month . "' ";
        }

        if (isset($year) && $year != '') {
            if ($whereCond) {
                $whereCond .= " AND ";
            } else {
                $whereCond .= " WHERE ";
            }
            $whereCond .= " YEAR(u.createdDate) = '" . $year . "' ";
        }

        if (isset($user_type) && $user_type != '') {
        	if ($whereCond) {
                $whereCond .= " AND ";
            } else {
                $whereCond .= " WHERE ";
            }
        	$whereCond .= " u.userType = '" . $user_type . "' ";
        }

        $adminType = getTableValue("tbl_admin", "adminType", array("id" => $_SESSION['adminUserId'] ));
        
        if($adminType == "g") {
            if ($whereCond) {
                $whereCond .= " AND ";
            } else {
                $whereCond .= " WHERE ";
            }
            $whereCond .= " u.isOpen = 'y' ";
        }
        	
        
        if (isset($sort)) {
            $sorting = $sort . ' ' . $order;
        } else {
            $sorting = ' userId DESC';
        }
		
		

        $query = "SELECT u.* 
                    FROM tbl_users u 
                    " . $whereCond . " ORDER BY " . $sorting;

        $query_with_limit = $query . " LIMIT " . $offset . " ," . $rows . " ";

        $totalUsers = $this->db->pdoQuery($query)->results();

        $qrySel = $this->db->pdoQuery($query_with_limit)->results();
        $totalRow = count($totalUsers);

        foreach ($qrySel as $fetchRes) {
            $status = ($fetchRes['status'] == "a") ? "checked" : "";

            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $fetchRes['userId'] . "", "check" => $status)) : '';
            $operation = '';

            $operation .=(in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $fetchRes['userId'] . "", "class" => "btn default btn-xs black btnEdit", "value" => '<i class="fa fa-edit"></i>&nbsp;Edit')) : '';
            $operation .=(in_array('delete', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $fetchRes['userId'] . "", "class" => "btn default btn-xs red btn-delete", "value" => '<i class="fa fa-trash-o"></i>&nbsp;Delete')) : '';
            $operation .=(in_array('view', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $fetchRes['userId'] , "class" => "btn default blue btn-xs btn-viewbtn", "value" => '<i class="fa fa-laptop"></i>&nbsp;View')) : '';
            
            $firstName = (isset($fetchRes["firstName"]) && $fetchRes["firstName"] != '') ? $fetchRes["firstName"] : 'N/A';
            $lastName = (isset($fetchRes["lastName"]) && $fetchRes["lastName"] != '') ? $fetchRes["lastName"] : 'N/A';

            if(isset($fetchRes['userType']) && $fetchRes['userType'] != '')
            {
                if($fetchRes['userType'] == 'p')
                {
                    $userType = 'Provider';
                }
                else if($fetchRes['userType'] == 'c')
                {
                    $userType = 'Customer';
                }
                else
                {
                    $userType = 'Not Selected';
                }
            }
            else
            {
                $userType = 'Not Selected';
            }

			// $userType = (isset($fetchRes["userType"]) && $fetchRes["userType"] == 'p') ? "Provider" : (isset($fetchRes["userType"]) && $fetchRes["userType"] == 'c') ? "Customer" : "Not Selected";
            $email = (isset($fetchRes["email"]) && $fetchRes["email"] != '') ? $fetchRes["email"] : 'N/A';

            $final_array = array(
                filtering($fetchRes['userId'], 'output', 'int'),
                filtering($firstName),
                filtering($lastName),
                filtering($email),
                filtering($userType),
                date(PHP_DATE_FORMAT,strtotime($fetchRes['createdDate']))
            );

            $final_array = array_merge($final_array, array($switch));
            $final_array = array_merge($final_array, array($operation));
            //echo "<pre>";print_r($final_array);exit;
            $row_data[] = $final_array;
        }

        $result["sEcho"] = $sEcho;
        $result["iTotalRecords"] = (int) $totalRow;
        $result["iTotalDisplayRecords"] = (int) $totalRow;
        $result["aaData"] = $row_data;
        return $result;
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

    public function getSelectBoxOption() {
        $content = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/select_option-nct.tpl.php");
        $content.= $main_content->parse();
        return sanitize_output($content);
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

    public function getPageContent() {
        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".tpl.php");
        $main_content->breadcrumb = $this->getBreadcrumb();

        $main_content_parsed = $final_result = $main_content->parse();

        $fields = array(
            "%VIEW_ALL_RECORDS_BTN%"
        );

        $view_all_records_btn = '';
        if (( isset($_GET['day']) && $_GET['day'] != '' ) || ( isset($_GET['month']) && $_GET['month'] != '' ) || ( isset($_GET['year']) && $_GET['year'] != '' )) {
            $view_all_records_btn = $this->getViewAllBtn();
        }

        $fields_replace = array(
            $view_all_records_btn
        );

        $final_result = str_replace($fields, $fields_replace, $main_content_parsed);

        return $final_result;
    }

}
