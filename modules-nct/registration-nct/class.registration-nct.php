<?php
class Registration {
    function __construct($module = "", $reqData = array()) {
        foreach ($GLOBALS as $key => $values) {
            $this -> $key = $values;

        }
        $this -> module = $module;
        $this->reqData = $reqData;
        $this -> dataOnly = (isset($this->reqData['dataOnly']) && $this->reqData['dataOnly']==true)?true:false;
        
        if($this->dataOnly){
            $_SESSION['lId'] = (isset($this->reqData['lId']) && $this->reqData['lId'] >0)?$this->reqData['lId']:1;  
            setLang();  
        }
    }

    public function getselectUserType() {
        return get_view(DIR_TMPL . $this -> module . "/select-user-type.tpl.php",array('%tokenValue%' => setFormToken()));
    }

    public function contactCodeOptions($contactCode = null,$countryId = null) {
        global $db;
        $html = null;

        $result = $db -> pdoQuery('SELECT DISTINCT phonecode,countryId,countryName from tbl_country where isActive="y" order by countryName asc ');
        
        if($this->dataOnly){
            return json_encode(array('status'=>true,'msg'=>'Success','data'=>$result -> results()));            
        }else{
            $result = $result->results();
        }
        foreach ($result as $k) {
            
            $replace = array(
                "%VALUE%" => $k['phonecode'],
                "%SELECTED%" => ($countryId == $k['countryId']) ? 'selected' : null,
                "%LABEL%"    => $k['countryName'] . " (" . $k['phonecode'] . ")"
            );
            $html .= get_view(DIR_TMPL . $this -> module . "/" . "option-nct.tpl.php", $replace);
        }
        return $html;
    }

    public function getPageContent() {
        $replace = array(
            '%contactCode_options%' => $this -> contactCodeOptions(),
            "%language_options%" => $this -> languageOption(),
            '%country_options%' => $this -> countryOptions(),
            '%privacy_url%' => $this->get_url(),
            '%terms_url%' => $this->get_url('terms'),
            '%selected_customer%' => ((isset($this->reqData['select_user_type']) && $this->reqData['select_user_type'] == 'c') || (!isset($this->reqData['select_user_type'])))?'checked="checked"':null,
            '%selected_provider%'=>(isset($this->reqData['select_user_type']) && $this->reqData['select_user_type'] == 'p')?'checked="checked"':null,
            '%tokenValue%' => setFormToken()
            
        );
        return get_view(DIR_TMPL . $this -> module . "/" . $this -> module . ".tpl.php", $replace);
    }
	
	public function get_url($string = 'privacy'){
		$result = $this->db->pdoQuery('SELECT pageSlug FROM tbl_content WHERE pageTitle LIKE ?',array("%$string%"))->result();
		return $result['pageSlug'];
	}

    public function languageOption() {
       
        global $db;
        $html = null;
        $result = $db->select("tbl_languages", '*', array("status" => 'a'), ' order by isDefault,languageName ');

        if($this->dataOnly){
            return json_encode(array('status'=>true,'msg'=>'Success','data'=>$result -> results()));
            
        }else{
            $result = $result->results();
        }

        foreach ($result as $k) {
            $replace = array(
                "%VALUE%"    => $k['id'],
                "%SELECTED%" => null,
                "%LABEL%"    => $k['languageName'],
            );
            $html .= get_view(DIR_TMPL . $this -> module . "/" . "option-nct.tpl.php", $replace);
        }
        return $html;
    }
    public function countryOptions() {
        global $db;
        $html = null;
        $result = $db -> select("tbl_country", array(
            "CountryId",
            "countryName",
            "phonecode"
        ), array("isActive" => 'y'), ' order by countryName ');

        if($this->dataOnly){
            return json_encode(array('status'=>true,'msg'=>'Success','data'=>$result -> results()));
            
        }else{
            $result = $result->results();
        }

        foreach ($result as $k) {
            $replace = array(
                "%VALUE%" => $k['CountryId'],
                "%SELECTED%" => null,
                "%LABEL%" => $k['countryName']
            );
            $html .= get_view(DIR_TMPL . $this -> module . "/" . "option-nct.tpl.php", $replace);
        }
        return $html;
    }

    

    

}
?>
