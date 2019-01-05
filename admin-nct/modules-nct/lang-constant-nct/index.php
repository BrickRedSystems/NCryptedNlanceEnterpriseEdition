<?php
$reqAuth = true;
require_once "../../../includes-nct/config-nct.php";
require_once "class.lang-constant-nct.php";

$module = "lang-constant-nct";
$table  = "tbl_lang_constants";

chkPermission($module);
$Permission = chkModulePermission($module);

$styles = array(
    array(
        "data-tables/DT_bootstrap.css",
        SITE_ADM_PLUGIN
    ),
    array(
        "bootstrap-switch/css/bootstrap-switch.min.css",
        SITE_ADM_PLUGIN
    )
);

$scripts = array(
    "core/datatable.js",
    array(
        "data-tables/jquery.dataTables.js",
        SITE_ADM_PLUGIN
    ),
    array(
        "data-tables/DT_bootstrap.js",
        SITE_ADM_PLUGIN
    ),
    array(
        "bootstrap-switch/js/bootstrap-switch.min.js",
        SITE_ADM_PLUGIN
    )
);

$metaTag = getMetaTags(array(
    "description" => "Admin Panel",
    "keywords" => 'Admin Panel',
    "author" => SITE_NM
));

$id         = isset($_GET["id"]) ? (int) trim($_GET["id"]) : 0;
$postType   = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type       = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;
$headTitle  = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage ') . ' Language Constants';
$winTitle   = $headTitle . ' - ' . SITE_NM;
$breadcrumb = array(
    $headTitle
);

$constObj = new Constant($id = 0, array(), $type = 'langArray');

if (isset($_POST["submitAddForm"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
    extract($_POST);
    
    $objPost->constantName = (isset($constantName) && trim($constantName)!=null) ? str_replace(array(' ', '%',"'",'.','"'), array('_', '','','',''),trim($constantName) ) : null;
    
    if ($type == 'edit' && $id > 0) {
        if (in_array('edit', $Permission)) {
            $counter = 1;
            foreach ($constantValue as $k => $v) {
                $qrySel = 'select id from tbl_lang_constants where ((id = ? OR subId = ?) AND languageId = ?)';
                
                $q = $constObj->getRecords($qrySel, array(
                    $id,
                    $id,
                    $k
                ));
                
                $qrysel1 = $q->result();
                $numRows = $q->affectedRows();
                
                if ($numRows > 0) {
                    
                    $objPost->constantValue = ($v);
                    if(ENVIRONMENT == 'd'){
                        $constObj->updateRecords($table, array(
                            "constantName" => $objPost->constantName,
                            'constantValue' => htmlentities($objPost->constantValue)
                        ), array(
                            "id" => $qrysel1['id']
                        ));
                    }else{
                        $constObj->updateRecords($table, array(
                            'constantValue' => htmlentities($objPost->constantValue)
                        ), array(
                            "id" => $qrysel1['id']
                        ));
                    }
                    
                    
                } else {
                    $objPost->createdDate   = date('Y-m-d H:i:s');
                    $objPost->subId         = ($counter == 1) ? '0' : $id;
                    $objPost->languageId    = $k;
                    $objPost->constantValue = $v;
                    $objPost->constantName  = getTableValue("tbl_lang_constants", "constantName", array(
                        "id" => $id
                    ));
                    $valArray               = array(
                        "languageId" => $k,
                        "subId" => $objPost->subId,
                        "constantName" => $objPost->constantName,
                        "constantValue" => htmlentities($v)
                    );
                    
                    $constObj->insertRecords($table, $valArray);
                }
                $counter++;
            }
            
            $activity_array = array(
                "id" => $id,
                "module" => $module,
                "activity" => 'edit'
            );
            
            add_admin_activity($activity_array);
            
            $_SESSION["toastr_message"] = disMessage(array(
                'type' => 'suc',
                'var' => 'Record edited successfully.'
            ));
        } else {
            $msgType = $_SESSION["toastr_message"] = disMessage(array(
                'type' => 'err',
                'var' => 'You are not authorized to perform this activity.'
            ));
        }
    } else {
        if (in_array('add', $Permission)) {
            
            if ($constObj->getRecordsCount($table, array(
                "constantName" => $objPost->constantName
            )) == 0) {
                
                $counter = 1;
                foreach ($constantValue as $k => $v) {
                    $objPost->subId         = ($counter == 1) ? '0' : $counstantId;
                    $objPost->languageId    = $k;
                    $objPost->constantValue = $v;
                    
                    $objPost->createdDate = date('Y-m-d H:i:s');
                    
                    $valArray = array(
                        "languageId" => $k,
                        "subId" => $objPost->subId,
                        "constantName" => $objPost->constantName,
                        "constantValue" => htmlentities($objPost->constantValue),
                        "created_date" => $objPost->createdDate
                    );
                    
                    $insertId = $constObj->insertRecords($table, $valArray);
                    
                    $counstantId = ($counter == 1) ? $insertId : $counstantId;
                    $counter++;
                }
                $activity_array = array(
                    "id" => $id,
                    "module" => $module,
                    "activity" => 'add'
                );
                
                add_admin_activity($activity_array);
                
                $_SESSION["toastr_message"] = disMessage(array(
                    'type' => 'suc',
                    'var' => 'Record added successfully.'
                ));
            } else {
                $_SESSION["toastr_message"] = disMessage(array(
                    'type' => 'err',
                    'var' => 'Record already exists.'
                ));
            }
        } else {
            $msgType = $_SESSION["toastr_message"] = disMessage(array(
                'type' => 'err',
                'var' => 'You are not authorized to perform this activity.'
            ));
        }
    }
    makeConstantFile();
    header('Location:' . $_SERVER['REQUEST_URI']);
    exit;
    
}

$constObj    = new Constant($id = 0, array(), $type = 'langArray');
$pageContent = $constObj->getPageContent();

require_once DIR_ADMIN_TMPL . "parsing-nct.tpl.php";