<?php
$reqAuth = true;
require_once ("../../../includes-nct/config-nct.php");
require_once ("class.dispute-nct.php");

$module = "dispute-nct";
$table = "tbl_project_dispute";

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
$breadcrumb = array("Dispute");

$id = isset($_GET['id']) ? $_GET['id'] : 0;

$postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;
$ctypeTxt = isset($_REQUEST["ctype"]) ? trim($_REQUEST["ctype"]) : "f";
$ctype = $ctypeTxt == 'pages' ? 't' : ($ctypeTxt == 'messages' ? 'm' : 'f');
$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage ') . ' Dispute';
$winTitle = $headTitle . ' - ' . SITE_NM;
$insArray = array();

if (isset($_POST["submitAddForm"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
    extract($_POST);
    //0-pending ,1-accepted ,2-rejected
    $insArray = array(
        'subject' => $subject,
        'description' => $description,      
        'admin_judgement' => $admin_judgement
    );
    
    if (trim($subject) != "" && trim($subject)!='' ) {
        if ($type == 'edit' && $id > 0) {
            if (in_array('edit', $Permission)) {
                $db -> update($table, $insArray, array("id" => $id));
                $db -> update('tbl_projects', array("jobStatus"=>'closed'), array("id" => $projectId));

                $whoraisedDispute = getTableValue($table, 'userId',array("id"=>$id));
                $raiserType = getTableValue('tbl_users', 'userType',array("userId"=>$whoraisedDispute));
                $projectUserId = getTableValue('tbl_projects', 'userId',array("id"=>$projectId));
                $projectProviderId = getTableValue('tbl_projects', 'providerId',array("id"=>$projectId));
                
                // update total wallet amount in user table
                if($admin_judgement == 'valid'){
                    

                    if($whoraisedDispute == $projectUserId){
                        /*Customer win*/
                        $projectPrice_c =$projectPrice=0;
                        $judgement_c = 'valid';
                        $judgement_p = 'invalid';
                        /* milestone amount add to customer*/
                        $milePrice = $db->pdoQuery("SELECT m.price FROM tbl_milestone AS m INNER JOIN tbl_payment_history AS pm ON m.id = pm.milestoneId WHERE m.status != 'paid' AND m.projectId = ?",array($projectId))->results();
                        foreach ($milePrice as $mileValue) {
                            $projectPrice_c += $mileValue['price'];
                        }
                        /*update wallet of customer*/
                        $db->pdoQuery('UPDATE tbl_users SET walletamount=walletamount+? WHERE userId=?', array($projectPrice_c, $projectUserId));  
                    }
                    else{
                        /*Provider win*/

                        $judgement_p = 'valid';
                        $judgement_c = 'invalid';
                        $unPaidProjectPrice = $reqProjectPrice = 0;

                        /*requested milestone amount add to provider*/
                        $reqMilePrice = $db->pdoQuery("SELECT m.id,m.price FROM tbl_milestone AS m INNER JOIN tbl_payment_history AS pm ON m.id = pm.milestoneId  WHERE m.status = 'remain' AND m.projectId = ?",array($projectId))->results();
                        
                        foreach ($reqMilePrice as $reqValue) {
                            $milPrice  = $reqValue['price'];
                            $escrowCommission = ($milPrice * ESCROW_COMMISSION) / 100;
                            $balToAdd  = $milPrice - $escrowCommission;
                            $reqProjectPrice += $balToAdd;
                             //make entry for escrow in payment history
                            $db->insert('tbl_payment_history', array(
                                'userId'       => $projectProviderId,
                                'paymentType'  => 'escrow',
                                'membershipId' => 0,
                                'totalAmount'  => (string) $escrowCommission,
                                'ipAddress'    => get_ip_address(),
                                'balanceAdded' => (string) $balToAdd,
                                'createdDate'  => date('Y-m-d H:i:s'),
                                'milestoneId'  => $reqValue['id'],
                            ));

                            $db->insert('tbl_payment_history', array(
                                'userId'        => $projectProviderId,
                                'paymentType'   => 'project payment',
                                'paymentStatus' => 'completed',
                                'totalAmount'   => (string) $balToAdd,
                                'createdDate'   => date('Y-m-d H:i:s'),
                                'projectId'     => $projectId,
                                'milestoneId' => $reqValue['id'] 
                            ));
                        }
                        /*update wallet of provider*/
                        $projectPrice = $reqProjectPrice;
                        $db->pdoQuery('UPDATE tbl_users SET walletamount=walletamount+? WHERE userId=?', array($reqProjectPrice, $projectProviderId));  

                        /*remain milestone amount add to customer*/
                        $unPaidMilePrice = $db->pdoQuery("SELECT m.price FROM tbl_milestone AS m INNER JOIN tbl_payment_history AS pm ON m.id = pm.milestoneId WHERE m.status = 'unapproved' AND m.projectId = ?",array($projectId))->results();
                        foreach ($unPaidMilePrice as $unPaidValue) {
                            $unPaidProjectPrice += $unPaidValue['price'];
                        }
                        $projectPrice_c = $unPaidProjectPrice;
                        /*update wallet of customer*/
                        $db->pdoQuery('UPDATE tbl_users SET walletamount=walletamount+? WHERE userId=?', array($unPaidProjectPrice, $projectUserId));  

                    }
                }else if($admin_judgement == 'invalid'){
                    if($whoraisedDispute == $projectUserId){
                        /*Customer filed and Provider win*/

                        $judgement_p = 'valid';
                        $judgement_c = 'invalid';
                        $unPaidProjectPrice = $reqProjectPrice = 0;
                        /*requested milestone amount add to provider*/
                        
                        $reqMilePrice = $db->pdoQuery("SELECT m.id,m.price FROM tbl_milestone AS m INNER JOIN tbl_payment_history AS pm ON m.id = pm.milestoneId  WHERE m.status = 'remain' AND m.projectId = ?",array($projectId))->results();
                        
                        foreach ($reqMilePrice as $reqValue) {
                            $milPrice  = $reqValue['price'];
                            $escrowCommission = ($milPrice * ESCROW_COMMISSION) / 100;
                            $balToAdd  = $milPrice - $escrowCommission;
                            $reqProjectPrice += $balToAdd;
                             //make entry for escrow in payment history
                            $db->insert('tbl_payment_history', array(
                                'userId'       => $projectUserId,
                                'paymentType'  => 'escrow',
                                'membershipId' => 0,
                                'totalAmount'  => (string) $escrowCommission,
                                'ipAddress'    => get_ip_address(),
                                'balanceAdded' => (string) $balToAdd,
                                'createdDate'  => date('Y-m-d H:i:s'),
                                'milestoneId'  => $reqValue['id'],
                            ));

                            $db->insert('tbl_payment_history', array(
                                'userId'        => $projectProviderId,
                                'paymentType'   => 'project payment',
                                'paymentStatus' => 'completed',
                                'totalAmount'   => (string) $balToAdd,
                                'createdDate'   => date('Y-m-d H:i:s'),
                                'projectId'     => $projectId,
                                'milestoneId' => $reqValue['id'] ));
                        }
                        $projectPrice = $reqProjectPrice;
                        /*update wallet of provider*/
                        $db->pdoQuery('UPDATE tbl_users SET walletamount=walletamount+? WHERE userId=?', array($reqProjectPrice, $projectProviderId));  

                        /*remain milestone amount add to customer*/
                        $unPaidMilePrice = $db->pdoQuery("SELECT m.price FROM tbl_milestone AS m INNER JOIN tbl_payment_history AS pm ON m.id = pm.milestoneId WHERE m.status = 'unapproved' AND m.projectId = ?",array($projectId))->results();
                        
                        foreach ($unPaidMilePrice as $unPaidValue) {
                            $unPaidProjectPrice += $unPaidValue['price'];
                        }
                        $projectPrice_c = $unPaidProjectPrice;
                        /*update wallet of customer*/
                        $db->pdoQuery('UPDATE tbl_users SET walletamount=walletamount+? WHERE userId=?', array($unPaidProjectPrice, $projectUserId));  
                    }else{
                        /*Provider Faild and Customer win*/

                        $judgement_c = 'valid';
                        $judgement_p = 'invalid';
                        $projectPrice_c =$projectPrice=0;

                        /* milestone amount add to customer*/
                        $milePrice = $db->pdoQuery("SELECT m.price FROM tbl_milestone AS m INNER JOIN tbl_payment_history AS pm ON m.id = pm.milestoneId WHERE m.status != 'paid' AND m.projectId = ?",array($projectId))->results();
                        foreach ($milePrice as $mileValue) {
                            $projectPrice_c += $mileValue['price'];
                        }
                        
                        /*update wallet of customer*/
                        $db->pdoQuery('UPDATE tbl_users SET walletamount=walletamount+? WHERE userId=?', array($projectPrice_c, $projectUserId));
                    }
                }
                
                
                $custDetails = $db -> pdoQuery("SELECT u.firstName, u.email, u.profileLink, u.userType  FROM tbl_users AS u WHERE u.userId=?",array($projectUserId)) -> result();
                $providerDetails = $db -> pdoQuery("SELECT u.firstName, u.email, u.profileLink, u.userType   FROM tbl_users AS u WHERE u.userId=?",array($projectProviderId)) -> result();
                $projectDetails = $db -> pdoQuery("SELECT  p.title, p.description, p.slug  FROM tbl_projects AS p WHERE p.id=?",array($projectId)) -> result();     
                
                //send to customer
                $array = generateEmailTemplate('dispute_resolved_admin', array(
                    'greetings' => ucfirst($custDetails['firstName']),
                    'projectTitle' => $projectDetails['title'],
                    'projectLink' => SITE_URL.$custDetails['profileLink'].'/'.$projectDetails['slug'],
                    'userType' => ($custDetails['userType']=='p')?'provider':'customer',
                    'description' => $projectDetails['description'],
                    'judgement' => ucfirst($admin_judgement),
                    'remarks' => null,
                    'final_resolution' => 'Refund | '.CURRENCY_SYMBOL.' '.$projectPrice
                ));
                //echo $array['message'];
                sendEmailAddress($custDetails['email'], $array['subject'], $array['message']);
                
                //send to provider
                $array = generateEmailTemplate('dispute_resolved_admin', array(
                    'greetings' => ucfirst($providerDetails['firstName']),
                    'projectTitle' => $projectDetails['title'],
                    'projectLink' => SITE_URL.$providerDetails['profileLink'].'/'.$projectDetails['slug'],
                    'userType' => ($providerDetails['userType']=='p')?'provider':'customer',
                    'description' => $projectDetails['description'],
                    'judgement' => ucfirst($admin_judgement),
                    'remarks' => null,
                    'final_resolution' => 'Refund | '.CURRENCY_SYMBOL.' '.$projectPrice
                ));    			
                //echo '<br/>'.$array['message'];exit;
                sendEmailAddress($providerDetails['email'], $array['subject'], $array['message']);	
                                
				$_SESSION["toastr_message"] = disMessage(array(
					'type' => 'suc',
					'var' => 'Record has been updated successfully.'
				));
			}
			else {
				$msgType = $_SESSION["toastr_message"] = disMessage(array(
					'type' => 'err',
					'var' => 'You are not authorised to perform this action.'
				));
			}
		}
		else {
			if (in_array('add', $Permission)) {

				$db -> insert("tbl_project_dispute", $insArray);

				$_SESSION["toastr_message"] = disMessage(array(
					'type' => 'suc',
					'var' => 'Record has been added successfully.'
				));

			}
			else {
				$msgType = $_SESSION["toastr_message"] = disMessage(array(
					'type' => 'err',
					'var' => 'You are not authorised to perform this action.'
				));
			}
		}
		redirectPage(SITE_ADM_MOD . $module);
	}
	else {
		$msgType = array(
			'type' => 'err',
			'var' => 'Please fill all required fields carefully.'
		);
	}
}

$constObj = new Dispute($module, $id = 0, array(), $type = 'langArray');
$pageContent = $constObj -> getPageContent();
require_once (DIR_ADMIN_TMPL . "parsing-nct.tpl.php");
