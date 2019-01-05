<?php

class Notifications extends Home {

    public $status;
    public $data = array();

    public function __construct($module, $id = 0, $objPost = NULL, $searchArray = array(), $type = '') {
        global $db, $fields;
        $this->db = $db;
        $this->data['id'] = $this->id = $id;
        $this->fields = $fields;
        $this->module = $module;
        $this->table = 'tbl_operational_status';

        $this->type = ($this->id > 0 ? 'edit' : 'add');
        $this->searchArray = $searchArray;
        parent::__construct();

        if ($this->id > 0) {
            $qrySel = $this->db->select($this->table, "*", array("id" => $id))->result();
            $fetchRes = $qrySel;

            $this->data['operational_status'] = $this->operational_status = $fetchRes['operational_status'];

            $this->data['status'] = $this->status = $fetchRes['status'];
            $this->data['added_on'] = $this->added_on = $fetchRes['added_on'];
        } else {
            $this->data['operational_status'] = $this->operational_status = '';

            $this->data['status'] = $this->status = '';
            $this->data['added_on'] = $this->added_on = '';
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
        $content = $this->displayBox(array("label" => "Operational Status &nbsp;:", "value" => $this->operational_status)) .
                $this->displayBox(array("label" => "Status&nbsp;:", "value" => $this->status == 'a' ? 'Active' : 'Deactive')) .
                $this->displayBox(array("label" => "Added On&nbsp;:", "value" => date("d M, Y", strtotime($this->added_on))));
        return $content;
    }

    public function getNotificationsCount() {
        $get_notifications_count = $this->db->pdoQuery("SELECT COUNT(*) as notifications_count FROM tbl_admin_notifications WHERE admin_id = " . filtering($_SESSION['adminUserId'], 'input', 'int') . " AND is_notified = 'n' AND is_notified = 'n' ")->result();
        return $get_notifications_count['notifications_count'];
    }

    public function getNewNotifications() {
        global $adminUserId;
        $final_result = '';

        $final_result_array = array();

        $query = "SELECT * 
                    FROM tbl_admin_notifications 
                    WHERE is_notified = 'n' AND is_read = 'n' AND admin_id = " . filtering($adminUserId, 'input', 'int') . " 
                    ORDER BY id DESC ";

        $get_notifications = $this->db->pdoQuery($query)->results();

        //echo "<pre>";print_r($get_notifications);exit;
        if ($get_notifications) {
            $notification = new MainTemplater(DIR_ADMIN_TMPL . "notification-li-nct.tpl.php");
            $notification_parsed = $notification->parse();

            $field = array(
                '%NOTIFICATION%',
                '%NOTIFICATION_URL%',
                '%NOTIFICATION_TITLE%',
                '%NOTIFICATION_DATE%',
                '%TIME_AGO%'
            );

            foreach ($get_notifications as $notification) {
                $notification_date = date("d M, Y", strtotime($notification['date_added']));
                $response = get_time_difference($notification['date_added'], date("Y-m-d H:i:s"));

                if ($response['days']) {
                    $time_ago = $response['days'] . " Days ago";
                } else if ($response['hours']) {
                    $time_ago = $response['hours'] . " Hours ago";
                } else if ($response['minutes']) {
                    $time_ago = $response['minutes'] . " Mins ago";
                } else if ($response['seconds']) {
                    $time_ago = $response['seconds'] . " Secs ago";
                }

                $type = $notification['type'];

                switch ($type) {
                    case 'new_user' : {
	                    $user_details = $this->db->select("tbl_users", "*", array("userId" => $notification['entity_id']))->result();
	                    $notification_text = "New user with user name " . $user_details['userName'] . " has been registered.";
	                    $notification_url = SITE_ADM_MOD . "users-nct";
	                    $notification_title = "New user registered.";
	                    break;
	                }

                }

                $field_replace = array(
                    filtering($notification_text),
                    filtering($notification_url),
                    filtering($notification_title),
                    $notification_date,
                    $time_ago
                );

                $final_result.= str_replace($field, $field_replace, $notification_parsed);                
                $this->db->update("tbl_admin_notifications", array("is_notified" => 'y'), array("id" => $notification['id']));
            }
            $final_result_array['notification_html'] = filtering($final_result, 'output', 'text');
        }



        return $final_result_array;
    }

    public function getNotifications($limit = 10, $offset = 0, $listing_type = 'general') {
        global $adminUserId;
        $final_result = '';

        $this->limit = $limit;
        $this->offset = $offset;

        $query = "SELECT * 
                    FROM tbl_admin_notifications 
                    WHERE admin_id = " . filtering($adminUserId, 'input', 'int') . " 
                    ORDER BY id DESC LIMIT " . $this->limit . " OFFSET " . $this->offset;

        $get_notifications = $this->db->pdoQuery($query)->results();

        if ($get_notifications) {
            $notification = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/single-notification-nct.tpl.php");
            $notification_parsed = $notification->parse();

            $field = array(
                '%NOTIFICATION%',
                '%NOTIFICATION_URL%',
                '%NOTIFICATION_TITLE%',
                '%NOTIFICATION_DATE%',
                '%TIME_AGO%'
            );

            foreach ($get_notifications as $notification) {
                $notification_date = date("d M, Y", strtotime($notification['date_added']));
                $response = get_time_difference($notification['date_added'], date("Y-m-d H:i:s"));

                if ($response['days']) {
                    $time_ago = $response['days'] . " Days ago";
                } else if ($response['hours']) {
                    $time_ago = $response['hours'] . " Hours ago";
                } else if ($response['minutes']) {
                    $time_ago = $response['minutes'] . " Mins ago";
                } else if ($response['seconds']) {
                    $time_ago = $response['seconds'] . " Secs ago";
                }

                $type = $notification['type'];

                switch ($type) {
                   case 'new_user' : {
	                    $user_details = $this->db->select("tbl_users", "*", array("userId" => $notification['entity_id']))->result();
	                    $notification_text = "New user with user name " . $user_details['userName'] . " has been registered.";
	                    $notification_url = SITE_ADM_MOD . "users-nct";
	                    $notification_title = "New user registered.";
	                    break;
	                }

                }

                $field_replace = array(
                    filtering($notification_text),
                    filtering($notification_url),
                    filtering($notification_title),
                    $notification_date,
                    $time_ago
                );

                $final_result.= str_replace($field, $field_replace, $notification_parsed);
               
                $this->db->update("tbl_admin_notifications", array("is_notified" => 'y'), array("id" => $notification['id']));
            }
            
        }
        return filtering($final_result, 'output', 'text');
    }

    public function getForm() {

        $content = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form-nct.tpl.php");
        $main_content = $main_content->parse();
        $status_a = ($this->status == 'a' ? 'checked' : '');
        $status_d = ($this->status != 'a' ? 'checked' : '');

        $fields = array(
            "%MEND_SIGN%",
            "%OPERATIONAL_STATUS%",
            "%STATUS_A%",
            "%STATUS_D%",
            "%TYPE%",
            "%ID%"
        );

        $fields_replace = array(
            MEND_SIGN,
            $this->data['operational_status'],
            $status_a,
            $status_d,
            $this->type,
            $this->id
        );

        $content = str_replace($fields, $fields_replace, $main_content);
        return sanitize_output($content);
    }

    public function dataGrid() {

        $content = $operation = $whereCond = $totalRow = NULL;
        $result = $tmp_rows = $row_data = array();
        extract($this->searchArray);
        $chr = str_replace(array('_', '%'), array('\_', '\%'), $chr);

        $whereCond = '';
        if (isset($chr) && $chr != '') {
            $whereCond .= " WHERE operational_status LIKE '%" . $chr . "%' ";
        }

        if (isset($sort))
            $sorting = $sort . ' ' . $order;
        else
            $sorting = 'id DESC';

        $totalRow = $this->db->count($this->table, $whereCond);

        $sql = "SELECT * FROM " . $this->table . " " . $whereCond . " order by " . $sorting . " limit " . $offset . " ," . $rows . " ";

        $qrySel = $this->db->pdoQuery($sql)->results();

        foreach ($qrySel as $fetchRes) {
            $id = $fetchRes['id'];
            $status = $fetchRes['status'];

            $status = ($fetchRes['status'] == "a") ? "checked" : "";

            $switch = (in_array('status', $this->Permission)) ? $this->toggel_switch(array("action" => "ajax." . $this->module . ".php?id=" . $id . "", "check" => $status)) : '';
            $operation = '';

            $operation .= (in_array('edit', $this->Permission)) ? $this->operation(array("href" => "ajax." . $this->module . ".php?action=edit&id=" . $id . "", "class" => "btn default btn-xs black btnEdit", "value" => '<i class="fa fa-edit"></i>&nbsp;Edit')) : '';
            $operation .=(in_array('delete', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=delete&id=" . $id . "", "class" => "btn default btn-xs red btn-delete", "value" => '<i class="fa fa-trash-o"></i>&nbsp;Delete')) : '';
            $operation .=(in_array('view', $this->Permission)) ? '&nbsp;&nbsp;' . $this->operation(array("href" => "ajax." . $this->module . ".php?action=view&id=" . $id . "", "class" => "btn default blue btn-xs btn-viewbtn", "value" => '<i class="fa fa-laptop"></i>&nbsp;View')) : '';


            $final_array = array($fetchRes["id"], $fetchRes["operational_status"]);

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
