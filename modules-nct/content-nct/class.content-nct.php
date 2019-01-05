<?php
class Content {
    public $metaDesc;
    public $metaKeyword;
    function __construct($module = "", $id = 0) {
        global $fields, $memberId;
        $this -> fields = $fields;
        $this -> memberId = $memberId;

        foreach ($GLOBALS as $key => $values) {
            $this -> $key = $values;
        }
        $this -> module = $module;
        $this -> id = $id;
    }

    public function getPageContent() {
        $html = html(DIR_TMPL . $this->module."/".$this->module.".tpl.php");
        $fetchRes = $this -> db -> select("tbl_content", array("*"), array(
            "pageId" => $this -> id,
            "isactive" => 'y'
        )) -> result();

        if (!empty($fetchRes)) {
            $pageTitle = $fetchRes["pageTitle_".$_SESSION["lId"]];
            $pageDesc = $fetchRes["pageDesc_".$_SESSION["lId"]];
            $this->metaDesc = $fetchRes["metaDesc"];
            $this->metaKeyword = $fetchRes["metaKeyword"];
            
        }
        return str_replace(array(
            "%pageTitle%",
            "%pageDesc%"
        ), array(
            $pageTitle,
            $pageDesc
        ), $html);
    }

}
?>