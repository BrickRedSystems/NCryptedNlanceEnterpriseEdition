<?php

class User_dashboard extends Home {

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
            $query = "SELECT u.*, country.countryName, state.stateName, city.cityName 
                    FROM tbl_users u 
                    LEFT JOIN tbl_country country ON u.country_id = country.CountryId 
                    LEFT JOIN tbl_state state ON u.state_id = state.StateID 
                    LEFT JOIN tbl_city city on u.city_id = city.CityId 
                    WHERE u.id = '" . $this->id . "' ";

            $qrySel = $this->db->pdoQuery($query)->result();

            $fetchRes = $qrySel;

            $this->data['first_name'] = $this->first_name = filtering($fetchRes['first_name']);
            $this->data['last_name'] = $this->last_name = filtering($fetchRes['last_name']);
            $this->data['email_address'] = $this->email_address = filtering($fetchRes['email_address']);
            $this->data['profile_picture_name'] = $this->profile_picture_name = filtering($fetchRes['profile_picture_name']);
            $this->data['gender'] = $this->gender = ( ( $fetchRes['gender'] == "m" ) ? "Male" : "Female" );

            $this->data['phone_no'] = $this->phone_no = filtering($fetchRes['phone_no']);

            $this->data['country_id'] = $this->country_id = filtering($fetchRes['country_id'], 'output', 'int');
            $this->data['state_id'] = $this->state_id = filtering($fetchRes['state_id'], 'output', 'int');
            $this->data['city_id'] = $this->city_id = filtering($fetchRes['city_id'], 'output', 'int');

            $this->data['countryName'] = $this->countryName = filtering($fetchRes['countryName']);
            $this->data['stateName'] = $this->stateName = filtering($fetchRes['stateName']);
            $this->data['cityName'] = $this->cityName = filtering($fetchRes['cityName']);

            $this->data['status'] = $this->status = $fetchRes['status'];
        } else {
            $this->data['first_name'] = $this->first_name = '';
            $this->data['last_name'] = $this->last_name = '';
            $this->data['email_address'] = $this->email_address = '';
            $this->data['date_of_birth'] = $this->date_of_birth = '';
            $this->data['phone_no'] = $this->phone_no = '';

            $this->data['country_id'] = $this->country_id = '';
            $this->data['state_id'] = $this->state_id = '';
            $this->data['city_id'] = $this->city_id = '';

            $this->data['countryName'] = $this->countryName = '';
            $this->data['stateName'] = $this->stateName = '';
            $this->data['cityName'] = $this->cityName = '';

            $this->data['status'] = $this->status = 'a';
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
                    $this->data['content'] = (in_array('module', $this->Permission)) ? json_encode($this->dataGrid()) : '';
                }
        }
    }

    public function getExperience($user_id) {
        $final_result = $experience = "";

        $experience_container_tpl = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/experience-container-nct.tpl.php');
        
        $experiences = $this->db->select("tbl_user_experiences", "*", array("user_id" => $user_id ))->results();
        
        if($experiences) {
            $experience_single_row = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/experience-single-row-nct.tpl.php');
            $experience_single_row_parsed = $experience_single_row->parse();
            
            $fields = array(
                "%JOB_TITLE%",
                "%COMPANY_NAME%",
                "%JOB_LOCATION%",
                "%WORKED_FROM%",
                "%WORKED_TO%"
            );
            
            foreach($experiences as $single_experience) {
                $fields_replace = array(
                    filtering($single_experience['job_title']),
                    filtering($single_experience['company_name']),
                    filtering($single_experience['job_location']),
                    date(PHP_DATE_FORMAT, strtotime($single_experience['worked_from'])),
                    date(PHP_DATE_FORMAT, strtotime($single_experience['worked_to']))
                );
                
                $experience .= str_replace($fields ,$fields_replace, $experience_single_row_parsed);
            }
        } else {
            $no_records = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/no-records-nct.tpl.php');
            $no_records->set('colspan', '5');
            $no_records->set('no_records_message', 'Experience not added yet.');
            $experience = $no_records->parse();
        }
        
        $experience_container_tpl->set('experience', $experience);
        
        $final_result = $experience_container_tpl->parse();

        return $final_result;
    }

    public function getEducation($user_id) {
        $final_result = $educations_html = "";

        $education_container_tpl = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/education-container-nct.tpl.php');
        
        $educations = $this->db->select("tbl_user_education", "*", array("user_id" => $user_id ))->results();
        
        if($educations) {
            $education_single_row = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/education-single-row-nct.tpl.php');
            $education_single_row_parsed = $education_single_row->parse();
            
            $fields = array(
                "%DEGREE_NAME%",
                "%UNIVERSITY_NAME%",
                "%FIELD_OF_STUDY%",
                "%FROM%",
                "%TO%",
                "%GRADE_OR_PERCENTAGE%",
                "%DESCRIPTION%"
            );
            
            foreach($educations as $single_education) {
                $fields_replace = array(
                    filtering($single_education['degree_name']),
                    filtering($single_education['university_name']),
                    filtering($single_education['field_of_study']),
                    filtering($single_education['from_year']),
                    filtering($single_education['to_year']),
                    filtering($single_education['grade_or_percentage']),
                    filtering($single_education['description'])
                );
                
                $educations_html .= str_replace($fields ,$fields_replace, $education_single_row_parsed);
            }
        } else {
            $no_records = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/no-records-nct.tpl.php');
            $no_records->set('colspan', '7');
            $no_records->set('no_records_message', 'Education not added yet.');
            $educations_html = $no_records->parse();
        }
        
        $education_container_tpl->set('educations', $educations_html);
        
        $final_result = $education_container_tpl->parse();

        return $final_result;
    }

    public function getLanguages($user_id) {
        $final_result = "";

        $final_result = "Langiuages";

        return $final_result;
    }

    public function getSkills($user_id) {
        $final_result = "";

        $final_result = "Skills";

        return $final_result;
    }

    public function getMyPages($user_id) {
        $final_result = $companies_html = "";

        $companies_container_tpl = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/companies-container-nct.tpl.php');
        
        $query = "SELECT c.*, i.industry_name, cs.company_size  
                    FROM tbl_companies c
                    LEFT JOIN tbl_industries i ON i.id = c.company_industry_id
                    LEFT JOIN tbl_company_sizes cs ON cs.id = c.company_size_id
                    WHERE c.user_id = '".$user_id."' ";
        $companies = $this->db->pdoQuery($query)->results();
        
        if($companies) {
            $company_single_row = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/company-single-row-nct.tpl.php');
            $company_single_row_parsed = $company_single_row->parse();
            
            $fields = array(
                "%COMPANY_NAME%",
                "%INDUSTRY_NAME%",
                "%COMPANY_SIZE%",
                "%SERVICES_PROVIDED%",
                "%WEBSITE_OF_COMPANY%",
                "%ADDED_ON%",
                "%UPDATED_ON%"
            );
            
            foreach($companies as $single_company) {
                $fields_replace = array(
                    filtering($single_company['company_name']),
                    filtering($single_company['industry_name']),
                    filtering($single_company['company_size']),
                    filtering($single_company['services_provided']),
                    filtering($single_company['website_of_company']),
                    date(PHP_DATE_FORMAT, strtotime($single_company['added_on'])),
                    date(PHP_DATE_FORMAT, strtotime($single_company['updated_on']))
                );
                
                $companies_html .= str_replace($fields ,$fields_replace, $company_single_row_parsed);
            }
        } else {
            $no_records = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/no-records-nct.tpl.php');
            $no_records->set('colspan', '7');
            $no_records->set('no_records_message', 'Company has not been added yet.');
            $companies_html = $no_records->parse();
        }
        
        $companies_container_tpl->set('companies', $companies_html);
        
        $final_result = $companies_container_tpl->parse();

        return $final_result;
    }

    public function getFollowing($user_id) {
        $final_result = $companies_html = "";

        $companies_container_tpl = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/companies-container-nct.tpl.php');
        
        $query = "SELECT c.*, i.industry_name, cs.company_size 
                    FROM tbl_company_followers cf 
                    LEFT JOIN tbl_companies c ON cf.company_id = c.id 
                    LEFT JOIN tbl_industries i ON i.id = c.company_industry_id
                    LEFT JOIN tbl_company_sizes cs ON cs.id = c.company_size_id
                    WHERE c.user_id = '".$user_id."' ";
        $companies = $this->db->pdoQuery($query)->results();
        
        if($companies) {
            $company_single_row = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/company-single-row-nct.tpl.php');
            $company_single_row_parsed = $company_single_row->parse();
            
            $fields = array(
                "%COMPANY_NAME%",
                "%INDUSTRY_NAME%",
                "%COMPANY_SIZE%",
                "%SERVICES_PROVIDED%",
                "%WEBSITE_OF_COMPANY%",
                "%ADDED_ON%",
                "%UPDATED_ON%"
            );
            
            foreach($companies as $single_company) {
                $fields_replace = array(
                    filtering($single_company['company_name']),
                    filtering($single_company['industry_name']),
                    filtering($single_company['company_size']),
                    filtering($single_company['services_provided']),
                    filtering($single_company['website_of_company']),
                    date(PHP_DATE_FORMAT, strtotime($single_company['added_on'])),
                    date(PHP_DATE_FORMAT, strtotime($single_company['updated_on']))
                );
                
                $companies_html .= str_replace($fields ,$fields_replace, $company_single_row_parsed);
            }
        } else {
            $no_records = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/no-records-nct.tpl.php');
            $no_records->set('colspan', '7');
            $no_records->set('no_records_message', 'Company has not been added yet.');
            $companies_html = $no_records->parse();
        }
        
        $companies_container_tpl->set('companies', $companies_html);
        
        $final_result = $companies_container_tpl->parse();

        return $final_result;
    }

    public function getMyJobs($user_id) {
        $final_result = $jobs_html = "";

        $jobs_container_tpl = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/jobs-container-nct.tpl.php');
        
        $query = "SELECT j.*, IF(j.employment_type = 'f', 'Full Time', IF(j.employment_type = 'p', 'Part Time', IF( j.employment_type = 'c', 'Contract', 'Temporary' ) ) ) as employment_type_text, c.company_name, jc.job_category  
                    FROM tbl_jobs j 
                    LEFT JOIN tbl_companies c ON c.id = j.company_id 
                    LEFT JOIN tbl_job_category jc ON jc.id = j.job_category_id                     
                    WHERE j.user_id = '".$user_id."' ";
        $jobs = $this->db->pdoQuery($query)->results();
        
        if($jobs) {
            $job_single_row = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/job-single-row-nct.tpl.php');
            $job_single_row_parsed = $job_single_row->parse();
            
            $fields = array(
                "%COMPANY_NAME%",
                "%JOB_CATEGORY%",
                "%JOB_TITLE%",
                "%JOB_POSITION%",
                "%SALARY_OFFERED_MIN%",
                "%SALARY_OFFERED_MAX%",
                "%MIN_EXPERIENCE%",
                "%EMPLOYMENT_TYPE_TEXT%",
                "%LAST_DATE_OF_APPLICATION%",
                "%ADDED_ON%"
            );
            
            foreach($jobs as $single_job) {
                $fields_replace = array(
                    filtering($single_job['company_name']),
                    filtering($single_job['job_category']),
                    filtering($single_job['job_title']),
                    filtering($single_job['job_position']),
                    CURRENCY_SYMBOL.filtering($single_job['salary_offered_min'], 'output', 'float'),
                    CURRENCY_SYMBOL.filtering($single_job['salary_offered_max'], 'output', 'float'),
                    filtering($single_job['min_experience'], 'output', 'float'),
                    filtering($single_job['employment_type_text']),
                    date(PHP_DATE_FORMAT, strtotime($single_job['last_date_of_application'])),
                    date(PHP_DATE_FORMAT, strtotime($single_job['added_on']))
                );
                
                $jobs_html .= str_replace($fields ,$fields_replace, $job_single_row_parsed);
            }
        } else {
            $no_records = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/no-records-nct.tpl.php');
            $no_records->set('colspan', '11');
            $no_records->set('no_records_message', "No job has been posted yet.");
            $jobs_html = $no_records->parse();
        }
        
        $jobs_container_tpl->set('jobs', $jobs_html);
        
        $final_result = $jobs_container_tpl->parse();

        return $final_result;
    }

    public function getAppliedJobs($user_id) {
        $final_result = $jobs_html = "";

        $jobs_container_tpl = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/applied-jobs-container-nct.tpl.php');
        
        $query = "SELECT j.*, IF(j.employment_type = 'f', 'Full Time', IF(j.employment_type = 'p', 'Part Time', IF( j.employment_type = 'c', 'Contract', 'Temporary' ) ) ) as employment_type_text, c.company_name, jc.job_category,
                    IF(added_by_admin = 'y', 'Admin', concat_ws(' ', u.first_name, u.last_name) ) as posted_by, ja.applied_on 
                    FROM tbl_job_applications ja
                    LEFT JOIN tbl_jobs j ON j.id = ja.job_id 
                    LEFT JOIN tbl_users u ON u.id = j.user_id 
                    LEFT JOIN tbl_companies c ON c.id = j.company_id 
                    LEFT JOIN tbl_job_category jc ON jc.id = j.job_category_id                     
                    WHERE ja.user_id = '".$user_id."' ";
        $jobs = $this->db->pdoQuery($query)->results();
        
        if($jobs) {
            $job_single_row = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/applied-job-single-row-nct.tpl.php');
            $job_single_row_parsed = $job_single_row->parse();
            
            $fields = array(
                "%COMPANY_NAME%",
                "%JOB_CATEGORY%",
                "%JOB_TITLE%",
                "%JOB_POSITION%",
                "%SALARY_OFFERED_MIN%",
                "%SALARY_OFFERED_MAX%",
                "%MIN_EXPERIENCE%",
                "%EMPLOYMENT_TYPE_TEXT%",
                "%LAST_DATE_OF_APPLICATION%",
                "%ADDED_ON%",
                "%POSTED_BY%",
                "%APPLIED_ON%"
            );
            
            foreach($jobs as $single_job) {
                $fields_replace = array(
                    filtering($single_job['company_name']),
                    filtering($single_job['job_category']),
                    filtering($single_job['job_title']),
                    filtering($single_job['job_position']),
                    CURRENCY_SYMBOL.filtering($single_job['salary_offered_min'], 'output', 'float'),
                    CURRENCY_SYMBOL.filtering($single_job['salary_offered_max'], 'output', 'float'),
                    filtering($single_job['min_experience'], 'output', 'float'),
                    filtering($single_job['employment_type_text']),
                    date(PHP_DATE_FORMAT, strtotime($single_job['last_date_of_application'])),
                    date(PHP_DATE_FORMAT, strtotime($single_job['added_on'])),
                    filtering($single_job['posted_by']),
                    date(PHP_DATE_FORMAT, strtotime($single_job['applied_on'])),
                );
                
                $jobs_html .= str_replace($fields ,$fields_replace, $job_single_row_parsed);
            }
        } else {
            $no_records = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/no-records-nct.tpl.php');
            $no_records->set('colspan', '11');
            $no_records->set('no_records_message', "Not applied for any job yet.");
            $jobs_html = $no_records->parse();
        }
        
        $jobs_container_tpl->set('jobs', $jobs_html);
        
        $final_result = $jobs_container_tpl->parse();

        return $final_result;
    }

    public function getMyGroups($user_id) {
        $final_result = $groups_html = "";

        $groups_container_tpl = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/groups-container-nct.tpl.php');
        
        $query = "SELECT g.*, concat_ws(' ', u.first_name, u.last_name) as user_name, gt.group_type, 
                IF(privacy = 'pr', 'Private', 'Public') as privacy_text, 
                IF(accessibility = 'awa', '-', IF(accessibility = 'a', 'Auto join', 'Request to join' ) ) as accessibility_text 
                FROM tbl_groups g 
                LEFT JOIN tbl_users u ON u.id = g.user_id 
                LEFT JOIN tbl_group_types gt ON gt.id = g.group_type_id  
                WHERE g.user_id = '" . $user_id . "' ";
        
        $groups = $this->db->pdoQuery($query)->results();
        
        if($groups) {
            $group_single_row = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/group-single-row-nct.tpl.php');
            $group_single_row_parsed = $group_single_row->parse();
            
            $fields = array(
                "%GROUP_NAME%",
                "%GROUP_TYPE%",
                "%WEBSITE_URL%",
                "%PRIVACY_TEXT%",
                "%ACCESSIBILITY_TEXT%",
                "%ADDED_ON%",
                "%UPDATED_ON%"
            );
            
            foreach($groups as $single_group) {
                $fields_replace = array(
                    filtering($single_group['group_name']),
                    filtering($single_group['group_type']),
                    filtering($single_group['website_url']),
                    filtering($single_group['privacy_text']),
                    filtering($single_group['accessibility_text']),
                    date(PHP_DATE_FORMAT, strtotime($single_group['added_on'])),
                    date(PHP_DATE_FORMAT, strtotime($single_group['updated_on']))
                );
                
                $groups_html .= str_replace($fields ,$fields_replace, $group_single_row_parsed);
            }
        } else {
            $no_records = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/no-records-nct.tpl.php');
            $no_records->set('colspan', '11');
            $no_records->set('no_records_message', "Not applied for any job yet.");
            $groups_html = $no_records->parse();
        }
        
        $groups_container_tpl->set('groups', $groups_html);
        
        $final_result = $groups_container_tpl->parse();

        return $final_result;
    }

    public function getJoinedGroups($user_id) {
        $final_result = $groups_html = "";

        $groups_container_tpl = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/joined-groups-container-nct.tpl.php');
        
        $query = "SELECT gm.joined_on, g.*, concat_ws(' ', u.first_name, u.last_name) as user_name, gt.group_type, 
                IF(privacy = 'pr', 'Private', 'Public') as privacy_text, 
                IF(accessibility = 'awa', '-', IF(accessibility = 'a', 'Auto join', 'Request to join' ) ) as accessibility_text 
                FROM tbl_group_members gm
                LEFT JOIN tbl_groups g ON g.id = gm.group_id 
                LEFT JOIN tbl_users u ON u.id = g.user_id 
                LEFT JOIN tbl_group_types gt ON gt.id = g.group_type_id  
                WHERE gm.user_id = '" . $user_id . "' AND (action = 'aj' OR action = 'a' OR action = 'aa') ";
        
        $groups = $this->db->pdoQuery($query)->results();
        
        if($groups) {
            $group_single_row = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/joined-group-single-row-nct.tpl.php');
            $group_single_row_parsed = $group_single_row->parse();
            
            $fields = array(
                "%GROUP_NAME%",
                "%GROUP_TYPE%",
                "%WEBSITE_URL%",
                "%PRIVACY_TEXT%",
                "%ACCESSIBILITY_TEXT%",
                "%ADDED_ON%",
                "%UPDATED_ON%",
                "%JOINED_ON%"
            );
            
            foreach($groups as $single_group) {
                $fields_replace = array(
                    filtering($single_group['group_name']),
                    filtering($single_group['group_type']),
                    filtering($single_group['website_url']),
                    filtering($single_group['privacy_text']),
                    filtering($single_group['accessibility_text']),
                    date(PHP_DATE_FORMAT, strtotime($single_group['added_on'])),
                    date(PHP_DATE_FORMAT, strtotime($single_group['updated_on'])),
                    date(PHP_DATE_FORMAT, strtotime($single_group['joined_on']))
                );
                
                $groups_html .= str_replace($fields ,$fields_replace, $group_single_row_parsed);
            }
        } else {
            $no_records = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/no-records-nct.tpl.php');
            $no_records->set('colspan', '11');
            $no_records->set('no_records_message', "Not applied for any job yet.");
            $groups_html = $no_records->parse();
        }
        
        $groups_container_tpl->set('groups', $groups_html);
        
        $final_result = $groups_container_tpl->parse();

        return $final_result;
    }

    public function getConnections($user_id) {
        $final_result = "";

        $final_result = "Connections";

        return $final_result;
    }

    public function getMembershipPlans($user_id) {
        $final_result = "";

        $final_result = "Membsership Plans";

        return $final_result;
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
        
        $user_id = filtering($this->id, 'input', 'int');
        
        $fields = array(
            "%USER_ID%",
            "%USERs_PROFILE_PICTURE_URL%",
            "%USER_NAME%",
            "%EMAIL_ADDRESS%",
            "%EXPERIENCE_URL%",
            "%EDUCATION_URL%",
            "%LANGUAGES_URL%",
            "%SKILLS_URL%",
            "%MY_PAGES_URL%",
            "%FOLLOWING_URL%",
            "%MY_JOBS_URL%",
            "%APPLIED_JOBS_URL%",
            "%MY_GROUPS_URL%",
            "%JOINED_GROUPS_URL%",
            "%CONNECTIONS_URL%",
            "%MEMBERSHIP_PLANS_URL%",
            "%BASIC_INFORMATION_ACTIVE_CLASS%",
            "%EXPERIENCE_ACTIVE_CLASS%",
            "%EXPERIENCE_CONTENT%",
            "%EDUACATION_ACTIVE_CLASS%",
            "%EDUACATION_CONTENT%",
            "%LANGUAGES_ACTIVE_CLASS%",
            "%LANGUAGES_CONTENT%",
            "%SKILLS_ACTIVE_CLASS%",
            "%SKILLS_CONTENT%",
            "%COMPANY_ACTIVE_CLASS%",
            "%MY_PAGES_ACTIVE_CLASS%",
            "%MY_PAGES_CONTENT%",
            "%FOLLOWING_ACTIVE_CLASS%",
            "%FOLLOWING_CONTENT%",
            "%JOB_ACTIVE_CLASS%",
            "%MY_JOBS_ACTIVE_CLASS%",
            "%MY_JOBS_CONTENT%",
            "%APPLIED_JOBS_ACTIVE_CLASS%",
            "%APPLIED_JOBS_CONTENT%",
            "%GROUPS_ACTIVE_CLASS%",
            "%MY_GROUPS_ACTIVE_CLASS%",
            "%MY_GROUPS_CONTENT%",
            "%JOINED_GROUPS_ACTIVE_CLASS%",
            "%JOINED_GROUPS_CONTENT%",
            "%CONNECTIONS_ACTIVE_CLASS%",
            "%CONNECTIONS_CONTENT%",
            "%MEMBERSHIP_PLANS_ACTIVE_CLASS%",
            "%MEMBERSHIP_PLANS_CONTENT%",
        );

        $experience_url = SITE_ADMIN_URL . "user-dashboard/" . $this->id . "/action/experience";
        $education_url = SITE_ADMIN_URL . "user-dashboard/" . $this->id . "/action/education";
        $languages_url = SITE_ADMIN_URL . "user-dashboard/" . $this->id . "/action/languages";
        $skills_url = SITE_ADMIN_URL . "user-dashboard/" . $this->id . "/action/skills";
        $my_pages_url = SITE_ADMIN_URL . "user-dashboard/" . $this->id . "/action/my_pages";
        $following_url = SITE_ADMIN_URL . "user-dashboard/" . $this->id . "/action/following";
        $my_jobs_url = SITE_ADMIN_URL . "user-dashboard/" . $this->id . "/action/my_jobs";
        $applied_jobs_url = SITE_ADMIN_URL . "user-dashboard/" . $this->id . "/action/applied_jobs";
        $my_groups_url = SITE_ADMIN_URL . "user-dashboard/" . $this->id . "/action/my_groups";
        $joined_groups_url = SITE_ADMIN_URL . "user-dashboard/" . $this->id . "/action/joined_groups";
        $connections_url = SITE_ADMIN_URL . "user-dashboard/" . $this->id . "/action/connections";
        $membership_plans_url = SITE_ADMIN_URL . "user-dashboard/" . $this->id . "/action/membership_plans";

        $basic_information_active_class = "";

        $experience_active_class = $experience_content = "";
        $education_active_class = $education_content = "";
        $languages_active_class = $languages_content = "";
        $skills_active_class = $skills_content = "";

        $company_active_class = $my_pages_active_class = $my_pages_content = $following_active_class = $following_content = "";

        $job_active_class = $my_jobs_active_class = $my_jobs_content = $applied_jobs_active_class = $applied_jobs_content = "";

        $groups_active_class = $my_groups_active_class = $my_groups_content = $joined_groups_active_class = $joined_groups_content = "";

        $connections_active_class = $connections_content = "";
        $membership_plans_active_class = $membership_plans_content = "";


        $action = filtering($_REQUEST['action']);
        switch ($action) {
            case "experience": {
                    $basic_information_active_class = $experience_active_class = "active";
                    $my_pages_active_class = $my_jobs_active_class = $my_groups_active_class = "active";

                    $experience_content = $this->getExperience($user_id);
                    break;
                }
            case "education": {
                    $basic_information_active_class = $education_active_class = "active";
                    $my_pages_active_class = $my_jobs_active_class = $my_groups_active_class = "active";

                    $education_content = $this->getEducation($user_id);
                    break;
                }
            case "languages": {
                    $basic_information_active_class = $languages_active_class = "active";
                    $my_pages_active_class = $my_jobs_active_class = $my_groups_active_class = "active";

                    $languages_content = $this->getLanguages($user_id);
                    break;
                }
            case "skills": {
                    $basic_information_active_class = $skills_active_class = "active";
                    $my_pages_active_class = $my_jobs_active_class = $my_groups_active_class = "active";

                    $skills_content = $this->getSkills($user_id);
                    break;
                }
            case "my_pages": {
                    $company_active_class = $my_pages_active_class = "active";
                    $experience_active_class = $my_jobs_active_class = $my_groups_active_class = "active";

                    $my_pages_content = $this->getMyPages($user_id);
                    break;
                }
            case "following": {
                    $company_active_class = $following_active_class = "active";
                    $experience_active_class = $my_jobs_active_class = $my_groups_active_class = "active";

                    $following_content = $this->getFollowing($user_id);
                    break;
                }
            case "my_jobs": {
                    $job_active_class = $my_jobs_active_class = "active";
                    $experience_active_class = $my_pages_active_class = $my_groups_active_class = "active";

                    $my_jobs_content = $this->getMyJobs($user_id);
                    break;
                }
            case "applied_jobs": {
                    $job_active_class = $applied_jobs_active_class = "active";
                    $experience_active_class = $my_pages_active_class = $my_groups_active_class = "active";

                    $applied_jobs_content = $this->getAppliedJobs($user_id);
                    break;
                }
            case "my_groups": {
                    $groups_active_class = $my_groups_active_class = "active";
                    $experience_active_class = $my_pages_active_class = $my_jobs_active_class = "active";

                    $my_groups_content = $this->getMyGroups($user_id);
                    break;
                }
            case "joined_groups": {
                    $groups_active_class = $joined_groups_active_class = "active";
                    $experience_active_class = $my_pages_active_class = $my_jobs_active_class = "active";

                    $joined_groups_content = $this->getJoinedGroups($user_id);
                    break;
                }
            case "connections": {
                    $connections_active_class = "active";
                    $experience_active_class = $my_pages_active_class = $my_jobs_active_class = $my_groups_active_class = "active";

                    $connections_content = $this->getConnections($user_id);
                    break;
                }
            case "membership_plans": {
                    $membership_plans_active_class = "active";
                    $experience_active_class = $my_pages_active_class = $my_jobs_active_class = $my_groups_active_class = "active";

                    $membership_plans_content = $this->getMembershipPlans($user_id);
                    break;
                }
        }
        if ($this->profile_picture_name == "") {
            $profile_picture_name = "default_profile_pic.png";
        } else {
            $profile_picture_name = $this->profile_picture_name;
        }
        
        
        $users_profile_picture_url = SITE_URL . "image/" . DIR_NAME_USERS . "/" . $profile_picture_name . "?w=150&h=150";

        $fields_replace = array(
            $this->id,
            $users_profile_picture_url,
            $this->first_name . " " . $this->last_name,
            $this->email_address,
            $experience_url,
            $education_url,
            $languages_url,
            $skills_url,
            $my_pages_url,
            $following_url,
            $my_jobs_url,
            $applied_jobs_url,
            $my_groups_url,
            $joined_groups_url,
            $connections_url,
            $membership_plans_url,
            $basic_information_active_class,
            $experience_active_class,
            $experience_content,
            $education_active_class,
            $education_content,
            $languages_active_class,
            $languages_content,
            $skills_active_class,
            $skills_content,
            $company_active_class,
            $my_pages_active_class,
            $my_pages_content,
            $following_active_class,
            $following_content,
            $job_active_class,
            $my_jobs_active_class,
            $my_jobs_content,
            $applied_jobs_active_class,
            $applied_jobs_content,
            $groups_active_class,
            $my_groups_active_class,
            $my_groups_content,
            $joined_groups_active_class,
            $joined_groups_content,
            $connections_active_class,
            $connections_content,
            $membership_plans_active_class,
            $membership_plans_content
        );

        $final_result = str_replace($fields, $fields_replace, $main_content_parsed);

        return $final_result;
    }

}
