<?php
class TopSkills {
    function __construct($module = "", $reqData = array()) {
        foreach ($GLOBALS as $key => $values) {
            $this -> $key = $values;
        }
        $this -> module = $module;
        $this -> reqData = $reqData;
        $this -> table = "tbl_top_skills";
    }

    public function getPageContent() {
        $html = null;        
        $replace =  array('%TOP_SKILLS%' => $this->top_skills_rows());
        $html .= get_view(DIR_TMPL . $this->module . "/" . $this -> module . ".tpl.php", $replace);       
        return $html;
    }

    public function sub_skills_rows($skillString = null){
        $skillArr = explode(',', $skillString);
        $html = null;
        $skillArr = array_filter($skillArr,function ($val){
            return !empty($val);
        }); 
        if(sizeof($skillArr)>0){
            foreach ($skillArr as $key => $value) {  
                if($value != null)          
                    $html .= "<li>".ucwords($value)."</li>";
            }
            return '<ul class="skill-over">'.$html.((sizeof($skillArr) > 2)?'<li class="text-right"><i class="blue-color">'.and_more.'..</i></li>':null)."</ul>";
        }else{
            return null;
        }
        
    }


    public function top_skills_rows($count = false){
        global $db;
        $html = NULL;
        $sql = 'SELECT ts.image, ts.id, ts.`skillName_'.$_SESSION['lId'].'`,ts.`show_on_home`, GROUP_CONCAT( DISTINCT CONCAT(s.slug) SEPARATOR "," ) AS skillSlugs, GROUP_CONCAT( DISTINCT CONCAT(s.skillName_'.$_SESSION['lId'].') SEPARATOR "," ) AS skills FROM tbl_top_skills AS ts LEFT JOIN tbl_top_skill_list AS tsl ON ts.`id` = tsl.`topSkill` left join tbl_skills as s on tsl.skill = s.id where ts.show_on_home="y" GROUP BY ts.`id` order by ts.`skillName_'.$_SESSION['lId'].'`';      
        $qrySel = $this->db->pdoQuery($sql);

        $total_rows = $qrySel->affectedRows() ;
        if($count){
            return $total_rows;
        }
        $qrySel = $qrySel->results();

        if($total_rows > 0){
            foreach ($qrySel as $fetchRes) {
                $replace = array(
                    "%HREF%" => SITE_SEARCH_SKILLS_PROVIDERS . (($fetchRes['skillSlugs'] != "") ? strtolower($fetchRes['skillSlugs']) : null),
                    "%SRC%" => tim_thumb_image($fetchRes['image'], "skill", 250, 284),
                    "%TITLE%" => ucwords($fetchRes['skillName_'.$_SESSION['lId']]),
                    "%subSkills%" => $this->sub_skills_rows($fetchRes['skills'])
                    );
                $html .= get_view(DIR_TMPL . $this->module . "/" . "top_skills_row.tpl.php", $replace);
            }
        }else{
            $html = null;
        }
        
        return $html;
    }


}
?>
