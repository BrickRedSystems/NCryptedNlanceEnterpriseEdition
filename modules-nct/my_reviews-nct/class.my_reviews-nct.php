<?php
class MyReviews {
    function __construct($module = "", $id = 0, $reqData = array()) {
        global $js_variables;
        foreach ($GLOBALS as $key => $values) {
            $this -> $key = $values;
        }
        $this -> module = $module;
        $this -> id = $id;
        $this -> reqData = $reqData;      
        $this->sessUserId = (isset($reqData['userId']) && $reqData['userId'] >0)?$reqData['userId']:$this->sessUserId;
        $this->sessUserType = (isset($reqData['userType']) && $reqData['userType'] != '')?$reqData['userType']:$this->sessUserType;
        $this -> dataOnly = (isset($this->reqData['dataOnly']) && $this->reqData['dataOnly']==true)?true:false;
        if($this->dataOnly){
            $_SESSION['lId'] = (isset($this->reqData['lId']) && $this->reqData['lId'] >0)?$this->reqData['lId']:1;  
            setLang();  
        }
    }
    
    public function reviews_rows($count = false){

        $html = $qry = $condition = $sort_by = null;
        $paramArray = array($this->sessUserId);
        
        if($this->sessUserType == 'p'){
            $qry = 'select IFNULL(AVG(f.averageRating), 0) AS average, COUNT(f.id) AS totalReviews,f.review, f.addedDate, p.title, p.`slug`, cu.`profileLink` as cust_link, u.`profileLink`, u.`profilePhoto`, CONCAT_WS(" ", u.firstName, u.lastName) AS fullName  from tbl_feedbacks as f left join tbl_projects as p on f.jobId=p.id left join tbl_users as u on p.userId=u.userId left join tbl_users as cu on p.userId=cu.userId where p.isActive="y" and u.isActive="y" AND f.userTo=? group by f.jobId ';    
        }else{
            $qry = 'select IFNULL(AVG(f.averageRating), 0) AS average, COUNT(f.id) AS totalReviews,f.review, f.addedDate, p.title, p.`slug`, cu.`profileLink` as cust_link, u.`profileLink`, u.`profilePhoto`, CONCAT_WS(" ", u.firstName, u.lastName) AS fullName  from tbl_feedbacks as f left join tbl_projects as p on f.jobId=p.id left join tbl_users as u on p.providerId=u.userId left join tbl_users as cu on p.userId=cu.userId where p.isActive="y" and u.isActive="y" AND f.userFrom=? group by f.jobId ';
        }
        
        
        ////////////////////////////
        // put pagination
        $limit_cond = NULL;

        $q = $this -> db -> pdoQuery($qry . $condition . $sort_by, $paramArray);
        $totalRows = $q -> affectedRows();
        if($count){
            return $totalRows;
        }

        $pageNo = isset($this -> reqData["pageNo"]) ? $this -> reqData["pageNo"] : 0;
        $pager = getPagerData($totalRows, LIMIT, $pageNo);
        if ($pageNo <= $pager -> numPages) {
            $offset = $pager -> offset;
            if ($offset < 0) {
                $offset = 0;
            }

            $limit = $pager -> limit;

            $page = $pager -> page;
            $limit_cond = " LIMIT $offset, $limit";
            $qry = $this -> db -> pdoQuery($qry . $condition . $sort_by . $limit_cond, $paramArray);
        }
        ////////////////////////////

        if($this -> dataOnly){

                return array(
                'page'=>issetor($page,0),
                'numPages'=>issetor($pager->numPages,0),
                'total_records'=>issetor($totalRows,0),
                'data'=>(is_object($qry) && $qry->affectedRows() > 0)?$qry->results():array()
                );
        }

        if ($qry->affectedRows() > 0) {

            $results = $qry->results();

            foreach ($results as $k => $v) {
                $replace = array(
                    '%profilePhoto%' => tim_thumb_image($v['profilePhoto'], 'profile', 75, 75),
                    '%profileLink%' => filtering($v['profileLink']),
                    '%fullName%' => ucwords(filtering($v['fullName'])),
                    '%slug%' => $v['cust_link']."/".$v['slug'],
                    '%title%' => ucwords(filtering($v['title'])),                    
                    '%review%' => filtering($v['review']),
                    '%addedDate%' => date(PHP_DATE_FORMAT,strtotime($v['addedDate'])),
                    '%stars%' => renderStarRating($v['average']),
                    '%average%' => number_format($v['average'],1)
                    
                );
                $html .= get_view(DIR_TMPL . $this -> module . "/reviews_row-nct.tpl.php", $replace);
            }
        }
        else {

            if($this -> dataOnly){
                return array();
            }
            $msg = ($this->sessUserType == 'p')?You_have_not_received_any_reviews_yet:You_have_not_posted_any_reviews_yet;
            $html .= get_view(DIR_TMPL . $this -> module . "/no_reviews_row-nct.tpl.php", array('%msg%' => $msg));
        }
        
        
        return $html;
    }

    public function getPageContent() {       
        $replace = array(   
            '%totalReviews%' => $this -> reviews_rows(true),                 
            '%rows%' => $this -> reviews_rows()
        );
        return get_view(DIR_TMPL . $this -> module . "/" . $this -> module . ".tpl.php", $replace);
    }

    

}
?>
