<?php

class Home {

    public function __construct() {
        foreach ($GLOBALS as $key => $values) {
            $this->$key = $values;
        }
    }

    public function index() {
        $content = NULL;
        return $content;
    }
    
    public function getViewAllBtn() {
        $content = '';

        $view_all_btn = new MainTemplater(DIR_ADMIN_TMPL . "/view-all-btn-nct.tpl.php");
        $view_all_btn->set('module_url', SITE_ADM_MOD . $this->module);

        $content = $view_all_btn->parse();

        return $content;
    }

    public function getLeftMenu() {
        $admSl = $this->db->select("tbl_admin", array("adminType"), array("id =" => (int) $this->adminUserId))->result();

        $final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . "left_panel-nct.tpl.php");

        $sub_content_menu = new MainTemplater(DIR_ADMIN_TMPL . "left_panel_menu-nct.tpl.php");
        $sub_content_menu = $sub_content_menu->parse();

        $sub_content_submenu = new MainTemplater(DIR_ADMIN_TMPL . "left_panel_submenu-nct.tpl.php");
        $sub_content_submenu = $sub_content_submenu->parse();

        $sub_content_submenu_item = new MainTemplater(DIR_ADMIN_TMPL . "left_panel_submenu_item-nct.tpl.php");
        $sub_content_submenu_item = $sub_content_submenu_item->parse();

        $fields = array("%IMAGE%", "%SECTION_NAME%", "%SUBMENU_LIST%");
        $fields_submenu = array("%SUBMENU_ITEMS%");
        $fields_submenu_item = array("%PAGE_NAME%", "%PAGE_URL%", "%TITLE%");

        $qrySel = $this->db->select("tbl_adminsection", array("id", "section_name", "image"), array("id >" => 0,'isActive'=>'y'), "ORDER BY `order` ASC")->results();
        if (!empty($qrySel[0]) > 0) {

            $sub_final_result = '';

            foreach ($qrySel as $fetchRes) {
                $sub_final_result_submenu_item = $sub_final_result_submenu = NULL;
                $id = $fetchRes['id'];
                $qSelMenu = $this->db->select("tbl_adminrole", array('id,title,pagenm'), array("sectionid" => $id, "and status !=" => "d"), "ORDER BY seq ASC")->results();
                $qSelMenu_sub = $this->db->select("tbl_adminrole", array('GROUP_CONCAT(id) as id'), array("sectionid" => $id, "and status !=" => "d"))->result();

                if ($qSelMenu_sub['id'] != '') {
                    //echo $qSelMenu_sub['id'];
                    //exit;
                    $qSelMenu_total = $this->db->pdoQuery("select count(id) as total_records from tbl_admin_permission where admin_id = '" . (int) $this->adminUserId . "' and page_id in (" . $qSelMenu_sub['id'] . ") and permission!=''")->result();
                    $totalRow = $qSelMenu_total['total_records'];
                }

                if (!empty($qSelMenu[0]) > 0) {
                    foreach ($qSelMenu as $fetchMenu) {
                        $chkPermssion = $this->db->select("tbl_admin_permission", array("permission"), array("admin_id" => (int) $this->adminUserId, "page_id" => $fetchMenu['id']))->result();
                        if ((!empty($chkPermssion['permission'])) || $admSl['adminType'] != 'g') {
                            $title = $fetchMenu['title'];
                            $pagenm = $fetchMenu['pagenm'];
                            $fields_replace_submenu_item = array($pagenm, SITE_ADM_MOD . $pagenm, $title);
                            $sub_final_result_submenu_item .=str_replace($fields_submenu_item, $fields_replace_submenu_item, $sub_content_submenu_item);
                        }
                    }
                    $fields_replace_submenu = array($sub_final_result_submenu_item);
                    $sub_final_result_submenu .= str_replace($fields_submenu, $fields_replace_submenu, $sub_content_submenu);
                }

                //(!empty($chkPermssion['permission']) || 

                if ($totalRow > 0 || $admSl['adminType'] != 'g') {
                    $fields_replace = array($fetchRes['image'], $fetchRes['section_name'], $sub_final_result_submenu);
                    $sub_final_result .= str_replace($fields, $fields_replace, $sub_content_menu);
                }
            }
        }

        $main_content->set('getMenuList', $sub_final_result);
        $final_result = $main_content->parse();

        return $final_result;
    }

    public function SubadminAction() {
        $final_result = array();
        $qryRes = $this->db->pdoQuery("SELECT id,constant,title FROM tbl_subadmin_action")->results();
        foreach ($qryRes as $fetchRes) {
            $id = (isset($fetchRes['id'])) ? $fetchRes['id'] : 0;
            //$constant = (isset($fetchRes['constant']))?$fetchRes['constant']:'';
            $title = (isset($fetchRes['title'])) ? $fetchRes['title'] : '';

            $final_result = $final_result + array($id => $title);
        }
        return $final_result;
    }

    public function SubadminActionDetails() {
        $final_result = array();
        $qryRes = $this->db->pdoQuery("SELECT id,constant,title FROM tbl_subadmin_action")->results();
        foreach ($qryRes as $fetchRes) {
            $id = (isset($fetchRes['id'])) ? $fetchRes['id'] : 0;
            $constant = (isset($fetchRes['constant'])) ? $fetchRes['constant'] : '';
            $title = (isset($fetchRes['title'])) ? $fetchRes['title'] : '';

            $final_result[] = array("id" => $id, "constant" => $constant, "title" => $title);
        }
        return $final_result;
    }

    public function adminPageList() {
        $final_result = array();
        $qryRes = $this->db->pdoQuery("SELECT id,title,pagenm,page_action FROM tbl_adminrole WHERE status='a'")->results();
        foreach ($qryRes as $fetchRes) {
            $id = (isset($fetchRes['id'])) ? $fetchRes['id'] : 0;
            $title = (isset($fetchRes['title'])) ? $fetchRes['title'] : '';
            $pagenm = (isset($fetchRes['pagenm'])) ? $fetchRes['pagenm'] : '';
            $page_action = (isset($fetchRes['page_action'])) ? $fetchRes['page_action'] : 0;
            $page_action_id = array();
            if ($page_action != '') {
                $qryRes_sub = $this->db->pdoQuery("SELECT id,title FROM tbl_subadmin_action WHERE id in (" . $page_action . ")")->results();
                foreach ($qryRes_sub as $fetchRes_sub) {
                    $page_action_id[] = (isset($fetchRes_sub['title'])) ? $fetchRes_sub['title'] : '';
                }
            }
            $final_result[] = array("id" => $id, "title" => $title, "pagenm" => $pagenm, "pagenm" => $pagenm, "page_action" => $page_action, "page_action_id" => $page_action_id);
        }
        return $final_result;
    }

    public function getBreadcrumb() {

        $final_result = $sub_final_result = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . "breadcrumb-nct.tpl.php");
        $content_list = new MainTemplater(DIR_ADMIN_TMPL . "breadcrumb_item-nct.tpl.php");
        $content_list = $content_list->parse();
        $field_array = array("%TITLE%","%LINK%");
        $data = $this->breadcrumb;
        
        for ($i = 0; $i < count($data); $i++) {
            $replace = array($data[$i],"#");
            $sub_final_result .= str_replace($field_array, $replace, $content_list);
        }
        $main_content->set("breadcrumb_list", $sub_final_result);
        $final_result = $main_content->parse();
        return $final_result;
    }

    public function getSelectBoxOption() {
        $content = '';
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . "select_option-nct.tpl.php");
        $content = $main_content->parse();

        return sanitize_output($content);
    }

    public function getCountryDD($selected_country_id = '', $disabled = false) {
        $final_result = $country_option = NULL;

        $getSelectBoxOption = $this->getSelectBoxOption();
        $fields = array("%VALUE%", "%SELECTED%", "%DISPLAY_VALUE%");

        $qrySelCountry = $this->db->pdoQuery("SELECT * FROM tbl_country where isActive='y' ORDER BY countryName ASC")->results();

        foreach ($qrySelCountry as $fetchRes) {
            $selected = ($selected_country_id == $fetchRes['CountryId']) ? "selected" : "";

            $fields_replace = array($fetchRes['CountryId'], $selected, $fetchRes['countryName']);
            $country_option.=str_replace($fields, $fields_replace, $getSelectBoxOption);
        }

        $country_dd = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/country-dd-nct.tpl.php");
        $country_dd_parsed = $country_dd->parse();


        $fields_country = array("%COUNTRY_OPTIONS%", "%DISABLED%");

        $disabled_text = '';
        if ($disabled) {
            $disabled_text = ' disabled="disabled" ';
        }

        $fields_country_replace = array($country_option, $disabled_text);

        $final_result = str_replace($fields_country, $fields_country_replace, $country_dd_parsed);

        return $final_result;
    }

    public function getStateDD($country_id = '', $selected_state_id = '', $disabled = false) {
        $final_result = $state_options = NULL;
        $state_options = '';

        if ($country_id != '') {
            $getSelectBoxOption = $this->getSelectBoxOption();
            $fields = array("%VALUE%", "%SELECTED%", "%DISPLAY_VALUE%");

            $qrySelState = $this->db->pdoQuery("SELECT * FROM tbl_state where CountryID=" . $country_id . " AND isActive='y' ORDER BY stateName")->results();

            foreach ($qrySelState as $fetchRes) {
                $selected = ($selected_state_id == $fetchRes['StateID']) ? "selected" : "";

                $fields_replace = array($fetchRes['StateID'], $selected, $fetchRes['stateName']);
                $state_options.=str_replace($fields, $fields_replace, $getSelectBoxOption);
            }
        }

        $state_dd = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/state-dd-nct.tpl.php");
        $state_dd_parsed = $state_dd->parse();

        $fields_state = array("%STATE_OPTIONS%", "%DISABLED%");

        $disabled_text = '';
        if ($disabled) {
            $disabled_text = ' disabled="disabled" ';
        }

        $fields_state_replace = array($state_options, $disabled_text);

        $final_result = str_replace($fields_state, $fields_state_replace, $state_dd_parsed);

        return $final_result;
    }

    public function getCityDD($country_id = '', $state_id = '', $selected_city_id = '', $disabled = false) {
        $final_result = NULL;
        $city_options = '';

        if ($state_id != '') {
            $getSelectBoxOption = $this->getSelectBoxOption();
            $fields = array("%VALUE%", "%SELECTED%", "%DISPLAY_VALUE%");

            //City dropdown
            $qrySelCity = $this->db->pdoQuery("SELECT * FROM tbl_city where StateID = '" . $state_id . "' AND CountryID = '" . $country_id . "' AND isActive='y' ORDER BY cityName")->results();

            foreach ($qrySelCity as $fetchRes) {
                $selected = ($selected_city_id == $fetchRes['CityId']) ? "selected" : "";

                $fields_replace = array($fetchRes['CityId'], $selected, $fetchRes['cityName']);
                $city_options.=str_replace($fields, $fields_replace, $getSelectBoxOption);
            }
        }

        $city_dd = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/city-dd-nct.tpl.php");
        $city_dd_parsed = $city_dd->parse();

        $fields_city = array("%CITY_OPTIONS%", "%DISABLED%");

        $disabled_text = '';
        if ($disabled) {
            $disabled_text = ' disabled="disabled" ';
        }

        $fields_city_replace = array($city_options, $disabled_text);

        $final_result = str_replace($fields_city, $fields_city_replace, $city_dd_parsed);

        return $final_result;
    }

    public function getReportsArray($report_type, $month = '', $year = '', $report_tenure = 'monthly') {
        $final_result = NULL;
        
        $response_array = $result_array = $categories = array();

        if (!$month || !$year) {
            $month = date('m');
            $year = date('Y');
        }

        $sql_query = '';
        if ($report_type == 'users') {
            if ($report_tenure == 'monthly') {
                $sql_query = "SELECT DAY(createdDate) as day, MONTH(createdDate) as month, YEAR(createdDate) as year, COUNT(userId) no_of_statistics 
                    FROM tbl_users 
                    WHERE YEAR(createdDate) = '" . $year . "' AND MONTH(createdDate) = '" . $month . "' 
                    GROUP BY YEAR(createdDate), MONTH(createdDate), DAY(createdDate)";
            } else {
                $sql_query = "SELECT DAY(createdDate) as day, MONTH(createdDate) as month, YEAR(createdDate) as year, COUNT(userId) no_of_statistics 
                    FROM tbl_users 
                    WHERE YEAR(createdDate) = '" . $year . "'  
                    GROUP BY YEAR(createdDate), MONTH(createdDate) ";
            }
        } else if ($report_type == 'jobs') {
            if ($report_tenure == 'monthly') {
                $sql_query = "SELECT DAY(added_on) as day, MONTH(added_on) as month, YEAR(added_on) as year, COUNT(userId) no_of_statistics 
                    FROM tbl_jobs 
                    WHERE YEAR(added_on) = '" . $year . "' AND MONTH(added_on) = '" . $month . "' 
                    GROUP BY YEAR(added_on), MONTH(added_on), DAY(added_on)";
            } else {
                $sql_query = "SELECT DAY(added_on) as day, MONTH(added_on) as month, YEAR(added_on) as year, COUNT(userId) no_of_statistics 
                    FROM tbl_jobs 
                    WHERE YEAR(added_on) = '" . $year . "'  
                    GROUP BY YEAR(added_on), MONTH(added_on) ";
            }
        } else if ($report_type == 'companies') {

            if ($report_tenure == 'monthly') {
                $sql_query = "SELECT DAY(added_on) as day, MONTH(added_on) as month, YEAR(added_on) as year, COUNT(userId) no_of_statistics 
                    FROM tbl_companies 
                    WHERE YEAR(added_on) = '" . $year . "' AND MONTH(added_on) = '" . $month . "' 
                    GROUP BY YEAR(added_on), MONTH(added_on), DAY(added_on)";
            } else {
                $sql_query = "SELECT DAY(added_on) as day, MONTH(added_on) as month, YEAR(added_on) as year, COUNT(userId) no_of_statistics 
                    FROM tbl_companies 
                    WHERE YEAR(added_on) = '" . $year . "'  
                    GROUP BY YEAR(added_on), MONTH(added_on) ";
            }
        } else if ($report_type == 'groups') {

            if ($report_tenure == 'monthly') {
                $sql_query = "SELECT DAY(added_on) as day, MONTH(added_on) as month, YEAR(added_on) as year, COUNT(userId) no_of_statistics 
                    FROM tbl_groups 
                    WHERE YEAR(added_on) = '" . $year . "' AND MONTH(added_on) = '" . $month . "' 
                    GROUP BY YEAR(added_on), MONTH(added_on), DAY(added_on)";
            } else {
                $sql_query = "SELECT DAY(added_on) as day, MONTH(added_on) as month, YEAR(added_on) as year, COUNT(userId) no_of_statistics 
                    FROM tbl_groups 
                    WHERE YEAR(added_on) = '" . $year . "'  
                    GROUP BY YEAR(added_on), MONTH(added_on) ";
            }
        } else if ($report_type == 'revenue_earned') {

            if ($report_tenure == 'monthly') {
                $sql_query = "SELECT DAY(updated_on) as day, MONTH(updated_on) as month, YEAR(updated_on) as year, SUM(total_price) no_of_statistics 
                    FROM tbl_payment_history 
                    WHERE YEAR(updated_on) = '" . $year . "' AND MONTH(updated_on) = '" . $month . "' 
                    GROUP BY YEAR(updated_on), MONTH(updated_on), DAY(updated_on)";
            } else {
                $sql_query = "SELECT DAY(added_on) as day, MONTH(added_on) as month, YEAR(added_on) as year, SUM(total_price) no_of_statistics 
                    FROM tbl_payment_history 
                    WHERE YEAR(updated_on) = '" . $year . "'  
                    GROUP BY YEAR(updated_on), MONTH(updated_on) ";
            }
        }

        $report_data = $this->db->pdoQuery($sql_query)->results();
        
        if ($report_tenure == 'monthly') {
            $number_of_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            for ($i = 1; $i <= $number_of_days; $i++) {
                $response = searchInMultidimensionalArray($report_data, 'day', $i);

                if ($response['status']) {
                    $key = $response['key'];
                    $no_of_statistics = (int) $report_data[$key]['no_of_statistics'];
                } else {
                    $no_of_statistics = 0;
                }

                $date = convertDate('onlyDate', $i . "-" . $month . "-" . $year);
                
                //$date = strtotime($i . "-" . $month . "-" . $year);
                $result_array[] = array( (string)$i, $no_of_statistics);
                
                $categories[] = $i;
            }
        } else {
            for ($i = 1; $i <= 12; $i++) {
                $response = searchInMultidimensionalArray($report_data, 'month', $i);

                if ($response['status']) {
                    $key = $response['key'];
                    $no_of_statistics = (int) $report_data[$key]['no_of_statistics'];
                } else {
                    $no_of_statistics = 0;
                }

                $date = convertDate('onlyMonth', "01-" . $i . "-" . $year);
                
                $result_array[] = array($date, $no_of_statistics);
                $categories[] = $i;
            }
        }
        
        $response_array['data'] = json_encode($result_array);
        $response_array['categories'] = json_encode($categories);

        return json_encode($result_array);
    }

    public function getMonthsDD($report_type, $selected_month_no) {
        $final_result = NULL;
        $month_options = '';

        $getSelectBoxOption = $this->getSelectBoxOption();
        $fields = array("%VALUE%", "%SELECTED%", "%DISPLAY_VALUE%");

        $formattedMonthArray = array(
            "1" => "January",
            "2" => "February",
            "3" => "March",
            "4" => "April",
            "5" => "May",
            "6" => "June",
            "7" => "July",
            "8" => "August",
            "9" => "September",
            "10" => "October",
            "11" => "November",
            "12" => "December"
        );
        foreach ($formattedMonthArray as $month_no => $month_name) {
            $selected = ($selected_month_no == $month_no) ? "selected" : "";

            $fields_replace = array($month_no, $selected, $month_name);

            $month_options .= str_replace($fields, $fields_replace, $getSelectBoxOption);
        }

        $months_dd = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/months-dd-nct.tpl.php");
        $months_dd_parsed = $months_dd->parse();


        $fields_month = array("%REPORT_TYPE%", "%MONTH_OPTIONS%");

        $fields_month_replace = array($report_type, $month_options);

        $final_result = str_replace($fields_month, $fields_month_replace, $months_dd_parsed);

        return $final_result;
    }

    public function getYearDD($report_type, $selected_year) {
        $final_result = NULL;
        $year_options = '';


        $getSelectBoxOption = $this->getSelectBoxOption();
        $fields = array("%VALUE%", "%SELECTED%", "%DISPLAY_VALUE%");

        for ($i = 2015; $i <= date('Y'); $i++) {
            $selected = ($selected_year == $i) ? "selected" : "";

            $fields_replace = array($i, $selected, $i);

            $year_options .= str_replace($fields, $fields_replace, $getSelectBoxOption);
        }

        $years_dd = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/years-dd-nct.tpl.php");
        $years_dd_parsed = $years_dd->parse();

        $fields_year = array("%REPORT_TYPE%", "%YEAR_OPTIONS%");

        $fields_year_replace = array($report_type, $year_options);

        $final_result = str_replace($fields_year, $fields_year_replace, $years_dd_parsed);

        return $final_result;
    }
	
	public function getPageContent(){
		
		/*
		$total_users = getTableValue('tbl_users','COUNT(userId)');
				$total_providers = getTableValue('tbl_users','COUNT(userId)',array("usertype"=>'p'));
				$total_customers = getTableValue('tbl_users','COUNT(userId)',array("usertype"=>'c'));	
				$total_projects = getTableValue('tbl_projects','COUNT(id)');
				
				$replace = array(
					'%TOTAL_USERS%' => !empty($total_users) ? $total_users : 0,
					'%TOTAL_PROVIDERS%' => !empty($total_providers) ? $total_providers : 0,			
					'%TOTAL_CUSTOMERS%' => !empty($total_customers) ? $total_customers : 0,
					'%TOTAL_PROJECTS%' => !empty($total_projects) ? $total_projects : 0	
				);		
				return get_view(DIR_ADMIN_TMPL.$this->module."/numeric_statistics_list-nct.tpl.php",$replace);*/
		
		
		
		
		$admSl = $this->db->select("tbl_admin", array("adminType"), array("id ="=>(int)$this->adminUserId))->result();
		$sub_final_result = $final_result = NULL;
		$main_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/".$this->module.".tpl.php");
		$main_content->breadcrumb = $this->getBreadcrumb();
		$main_content->projectsPerCategory = $this->projectsPerCategory();
		$main_content->chart_data = $this->generateChartData();
		$main_content->revenueData = $this->revenueData();
		$main_content->postedProjects = $this->postedProjects();
		
		$sub_content = new MainTemplater(DIR_ADMIN_TMPL.$this->module."/dashboard_list-nct.tpl.php");
		$sub_content = $sub_content->parse();
		$fields = array('%PAGE_LINK%','%COLOR%','%IMAGE%','%PAGE_TITLE%');

		$qSelMenu = $this->db->select("tbl_adminrole", array('id,title,pagenm,image'),array("status !="=>"d"), "ORDER BY seq ASC")->results();
        
        if(!empty($qSelMenu[0]) > 0) {
			$i=0;
			$color_array = array("blue","green","red","yellow","dark","purple");
            foreach($qSelMenu as $fetchMenu) {
				$chkPermssion = $this->db->select("tbl_admin_permission", array("permission"), array("admin_id"=>(int)$this->adminUserId,"page_id"=>$fetchMenu['id']))->result();
				if((!empty($chkPermssion['permission'])) || $admSl['adminType'] != 'g'){
					$fields_replace = array(SITE_ADM_MOD.$fetchMenu['pagenm'],$color_array[$i],$fetchMenu['image'],$fetchMenu['title']);
					$sub_final_result .= str_replace($fields,$fields_replace,$sub_content);
					$i=($i==5)?-1:$i;
					$i++;
				}
			}
		}
		$main_content->set('dashboard_list',$sub_final_result);
		$final_result = $main_content->parse();
		return $final_result;
	}

/*

    public function getPageContent() {

        $admSl = $this->db->select("tbl_admin", array("adminType"), array("id =" => (int) $this->adminUserId))->result();
        $final_result = NULL;

        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".tpl.php");
        $main_content->breadcrumb = $this->getBreadcrumb();

        $sub_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/dashboard_list-nct.tpl.php");
        $sub_content = $sub_content->parse();

        $fields = array(
            '%PAGE_LINK%',
            '%COLOR%',
            '%IMAGE%',
            '%PAGE_TITLE%'
        );

        $qSelMenu = $this->db->select("tbl_adminrole", array('id,title,pagenm,image'), array("status !=" => "d"), "ORDER BY seq ASC")->results();
        if (!empty($qSelMenu[0]) > 0) {
            $i = 0;
            $sub_final_result = '';
            $color_array = array("blue", "green", "red", "yellow", "dark", "purple");
            foreach ($qSelMenu as $fetchMenu) {
                $chkPermssion = $this->db->select("tbl_admin_permission", array("permission"), array("admin_id" => (int) $this->adminUserId, "page_id" => $fetchMenu['id']))->result();
                if ((!empty($chkPermssion['permission'])) || $admSl['adminType'] != 'g') {
                    $fields_replace = array(SITE_ADM_MOD . $fetchMenu['pagenm'], $color_array[$i], $fetchMenu['image'], $fetchMenu['title']);
                    $sub_final_result .=str_replace($fields, $fields_replace, $sub_content);
                    $i = ($i == 5) ? -1 : $i;
                    $i++;
                }
            }
        }

        $main_content->set('dashboard_list', $sub_final_result);

        $getUsers = $this->db->pdoQuery("SELECT * FROM tbl_users")->results();
        $no_of_users = count($getUsers);

        $main_content->set('no_of_users', $no_of_users);

        $main_content_parsed = $main_content->parse();

        $fields_main_content = array(
            "%USER_REPORT_ARRAY%",
            "%MONTH_DD_USERS_REPORT%",
            "%YEAR_DD_USERS_REPORT%"
        );
        
        $fields_replace_main_content = array(
            $this->getReportsArray('users'),
            $this->getMonthsDD('users', date('m')),
            $this->getYearDD('users', date("Y"))
        );

        $final_result = str_replace($fields_main_content, $fields_replace_main_content, $main_content_parsed);

        return $final_result;
    }
*/




function projectsPerCategory() {

    global $db,$sessUserId;
    $cat_array = array();
		
	$cat_array = $db->pdoQuery('SELECT COUNT(p.id) AS totalCate, p.categoryId, cat.cateName_1 as cateName 
        FROM tbl_categories cat
        LEFT JOIN tbl_projects p ON  cat.id = p.categoryId 
        WHERE p.isActive =  "y" GROUP BY p.categoryId ')->results();
		
    $final_result = array();

    if($cat_array) {

        $final_result['type'] = "pie";
        $final_result['theme'] = "light";

        foreach ($cat_array as $key => $value) {
            $final_result['dataProvider'][$key]['country'] = $value['cateName']; 
            $final_result['dataProvider'][$key]['litres'] = $value['totalCate'];
        }

        $final_result['valueField'] = "litres";
        $final_result['titleField'] = 'country';
        $final_result['balloon']['fixedPosition'] = true;

        $final_result['export']['enabled'] = true;
    }
    
    return json_encode($final_result);  
}

function generateChartData() {

    global $db,$sessUserId;
    $users_array = array();


    $users_array = $db->pdoQuery('SELECT u.*, SUM(IF(u.userType  = "c",1, 0)) AS cust, SUM(IF(u.userType  = "p",1, 0)) AS provider 
         FROM tbl_users u 
        WHERE u.status=  "a" GROUP BY DATE(u.createdDate)')->results();

    $views = array();

    if($users_array) {
        foreach ($users_array as $key => $value) {
            $date   = date(PHP_DATE_FORMAT,strtotime($value['createdDate']));
            $views[]= array('date' => $date, 'customer' => $value['cust'], 'provider' => $value['provider']);
            
        }
    }

    return json_encode($views); 
}

function postedProjects() {

    global $db,$sessUserId;
    $users_array = array();


    $users_array = $db->pdoQuery('SELECT COUNT(p.id) AS project, DATE(p.createdDate) AS date  FROM tbl_projects p WHERE p.`isActive` = "y" GROUP BY DATE(p.createdDate)')->results();

    $views = array();

    if($users_array) {
        foreach ($users_array as $key => $value) {            
            $views[]= array('date' => $value['date'], 'project' => $value['project']);            
        }
    }

    return json_encode($views); 
}

function revenueData(){
	global $db,$sessUserId;
    $users_array = array();
    $users_array = $db->pdoQuery('SELECT SUM(tph.totalAmount) AS totEarned, YEAR(tph.`createdDate`) AS fiscalYear FROM tbl_payment_history tph  GROUP BY YEAR(tph.`createdDate`)')->results();

    $views = array();

    if($users_array) {
        foreach ($users_array as $key => $value) {            
            $views[]= array('year' => $value['fiscalYear'], 'income' => $value['totEarned']);            
        }
    }

    return json_encode($views); 
}

}
