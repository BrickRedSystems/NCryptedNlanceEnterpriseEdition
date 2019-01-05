<?php
class EditProfile
{
    public function __construct($module = "", $reqData = array())
    {
        foreach ($GLOBALS as $key => $values) {
            $this->$key = $values;
        }
        $this->module   = $module;
        $this->reqData  = $reqData;
        $this->dataOnly = (isset($this->reqData['dataOnly']) && $this->reqData['dataOnly'] == true) ? true : false;
        if($this->dataOnly){
            $_SESSION['lId'] = (isset($this->reqData['lId']) && $this->reqData['lId'] >0)?$this->reqData['lId']:1;  
            setLang();  
        }
    }

    public function getPageContent()
    {
        global $js_variables;

        $u = $this->db->pdoQuery("SELECT u.* ,
            GROUP_CONCAT( DISTINCT CONCAT( '\"' ,s.skillName_".$_SESSION['lId'].",'\"') SEPARATOR ', ') AS skills
            FROM
            tbl_users AS u
            LEFT JOIN tbl_user_skills AS us
            ON u.userId = us.`userId`
            LEFT JOIN tbl_skills AS s
            ON us.`skillId` = s.`id`
            WHERE u.userId = ?
            GROUP BY u.`userId`", array($this->sessUserId))->result();

        extract($u);
        $allSkills    = $this->allSkills();
        $js_variables = "var prefilledValues = [$u[skills]], allSkills= $allSkills";        
        $replace = array(
            '%show_main_user_image%'  => tim_thumb_image($profilePhoto, 'profile', 134, 134),
            '%firstName%'             => filtering($firstName),
            '%lastName%'              => filtering($lastName),
            '%email%'                 => filtering($email),
            '%aboutMe%'               => filtering(nl2br($aboutMe)),
            '%cats%'                  => $this->get_cats($catId),
            '%subcats%'               => $this->get_sub_cats($subcatId, $catId),
            '%country_options%'       => $this->countryOptions($countryCode),
            '%state_options%'         => $this->stateOptions($state, $countryCode),
            '%city_options%'          => $this->cityOptions($city, $state),
            '%paypalEmail%'           => $paypalEmail,
            '%contactCode_options%'   => $this->contactCodeOptions($contactCode),
            '%contactNo%'             => $contactNo,
            '%cropmodal%'             => $this->profilepiccropmodal(),
            '%hideSkillsForCustomer%' => ($userType == 'c' ? 'hide' : null),
            '%tokenValue%'            => setFormToken(),
            '%language_options%'      => $this->languageOption($langId),

        );
        return get_view(DIR_TMPL . $this->module . "/" . $this->module . ".tpl.php", $replace);
    }

    public function profilepiccropmodal()
    {
        return get_view(DIR_TMPL . "profilepiccrop.tpl.php");
    }

    public function allSkills()
    {
        global $db;
        $arr    = array();
        $result = $db->select("tbl_skills", array("id", "skillName_".$_SESSION['lId'].' as skillName '), array("status" => 'a'))->results();

        foreach ($result as $k => $v) {
            $arr[$v["id"]] = $v["skillName"];
        }
        return json_encode($arr);
    }

    public function contactCodeOptions($contactCode)
    {
        global $db;
        $html   = null;
        $result = $db->pdoQuery('SELECT DISTINCT phonecode, countryName from tbl_country where isActive="y" order by countryName asc ')->results();

        foreach ($result as $k) {
            $replace = array(
                "%VALUE%"    => $k['phonecode'],
                "%SELECTED%" => ($contactCode == $k['phonecode']) ? 'selected' : null,
                "%LABEL%"    => $k['countryName'] . " (" . $k['phonecode'] . ")",
                "%TITLE%"    => $k['phonecode'],
            );
            $html .= get_view(DIR_TMPL . $this->module . "/" . "option-nct.tpl.php", $replace);
        }
        return $html;
    }

    public function get_sub_cats($subcatId = 0, $parentId = 0)
    {
        if ($subcatId == 0 && $parentId == 0) {
            return null;
        } else if ($parentId == 0) {
            $selCats = $this->db->select('tbl_categories', array(
                'id',
                'cateName_'.$_SESSION["lId"].' as cateName ',
            ), array(
                'isActive'    => 'y',
                'parentId > ' => 0,
            ), 'order by cateName asc');
        } else {
            $selCats = $this->db->select('tbl_categories', array(
                'id',
                'cateName_'.$_SESSION["lId"].' as cateName ',
            ), array(
                'isActive' => 'y',
                'parentId' => $parentId,
            ), 'order by cateName_'.$_SESSION["lId"].' asc');

        }

        if ($this->dataOnly) {
            return $selCats->results();
        } else {
            $selCats = $selCats->results();
        }

        $html = null;
        foreach ($selCats as $k) {
            $replace = array(
                "%VALUE%"    => $k['id'],
                "%SELECTED%" => ($subcatId == $k['id']) ? 'selected' : null,
                "%LABEL%"    => $k['cateName'],
                "%TITLE%"    => $k['cateName'],
            );
            $html .= get_view(DIR_TMPL . $this->module . "/" . "option-nct.tpl.php", $replace);
        }
        return $html;
    }

    public function get_cats($catId = 0)
    {
        $selCats = $this->db->select('tbl_categories', array(
            'id',
            'cateName_'.$_SESSION["lId"].' as cateName ',
        ), array(
            'isActive' => 'y',
            'parentId' => 0,
        ), 'order by cateName_'.$_SESSION["lId"].' asc');
        if ($this->dataOnly) {
            return $selCats->results();
        } else {
            $selCats = $selCats->results();
        }
        $html = null;
        foreach ($selCats as $k) {
            $replace = array(
                "%VALUE%"    => $k['id'],
                "%SELECTED%" => ($catId == $k['id']) ? 'selected' : null,
                "%LABEL%"    => $k['cateName'],
                "%TITLE%"    => $k['cateName'],
            );
            $html .= get_view(DIR_TMPL . $this->module . "/" . "option-nct.tpl.php", $replace);
        }
        return $html;
    }

    public function cityOptions($city = 0, $state = 0)
    {
        global $db;
        $html = null;
        if ($city == 0 && $state == 0) {
            return null;
        } elseif ($state == 0) {
            $result = $db->select("tbl_city", array(
                "CityId",
                "cityName",
                "CountryID",
                "StateID",
            ), array("isActive" => 'y'), 'order by cityName asc');
        } else {
            $result = $db->select("tbl_city", array(
                "CityId",
                "cityName",
                "CountryID",
                "StateID",
            ), array(
                "isActive" => 'y',
                'StateID'  => $state,
            ), 'order by cityName asc');
        }

        if ($this->dataOnly) {
            return $result->results();
        } else {
            $result = $result->results();
        }

        foreach ($result as $k) {
            $replace = array(
                "%VALUE%"    => $k['CityId'],
                "%SELECTED%" => ($city == $k['CityId']) ? 'selected' : null,
                "%LABEL%"    => $k['cityName'],
                "%TITLE%"    => $k['cityName'],
            );
            $html .= get_view(DIR_TMPL . $this->module . "/" . "option-nct.tpl.php", $replace);
        }
        return $html;
    }

    public function stateOptions($state = 0, $countryCode = 0)
    {
        global $db;
        $html = null;
        if ($state == 0 && $countryCode == 0) {
            return null;
        } elseif ($countryCode == 0) {
            $result = $db->select("tbl_state", array(
                "StateID",
                "stateName",
                "CountryID",
            ), array("isActive" => 'y'), 'order by stateName asc');
        } else {
            $result = $db->select("tbl_state", array(
                "StateID",
                "stateName",
                "CountryID",
            ), array(
                "isActive"  => 'y',
                'CountryID' => $countryCode,
            ), 'order by stateName asc');
        }

        if ($this->dataOnly) {
            return $result->results();
        } else {
            $result = $result->results();
        }

        foreach ($result as $k) {
            $replace = array(
                "%VALUE%"    => $k['StateID'],
                "%SELECTED%" => ($state == $k['StateID']) ? 'selected' : null,
                "%LABEL%"    => $k['stateName'],
                "%TITLE%"    => $k['stateName'],
            );
            $html .= get_view(DIR_TMPL . $this->module . "/" . "option-nct.tpl.php", $replace);
        }
        return $html;
    }

    public function countryOptions($countryId)
    {
        global $db;
        $html   = null;
        $result = $db->select("tbl_country", array(
            "CountryId",
            "countryName",
        ), array("isActive" => 'y'), 'order by countryName asc');

        if ($this->dataOnly) {
            return $result->results('json');
        } else {
            $result = $result->results();
        }

        foreach ($result as $k) {
            $replace = array(
                "%VALUE%"    => $k['CountryId'],
                "%SELECTED%" => ($countryId == $k['CountryId']) ? 'selected' : null,
                "%LABEL%"    => $k['countryName'],
                "%TITLE%"    => $k['countryName'],
            );
            $html .= get_view(DIR_TMPL . $this->module . "/" . "option-nct.tpl.php", $replace);
        }
        return $html;
    }

    public function languageOption($lang)
    {

        global $db;
        $html   = null;
        $result = $db->select("tbl_languages", '*', array("status" => 'a'), ' order by isDefault,languageName ');

        if ($this->dataOnly) {
            return json_encode(array('status' => true, 'msg' => 'Success', 'data' => $result->results()));

        } else {
            $result = $result->results();
        }

        foreach ($result as $k) {
            $replace = array(
                "%VALUE%"    => $k['id'],
                "%SELECTED%" => ($lang == $k['id'] ? 'selected' : null),
                "%LABEL%"    => $k['languageName'],
            );
            $html .= get_view(DIR_TMPL . $this->module . "/" . "option-nct.tpl.php", $replace);
        }
        return $html;
    }

}
