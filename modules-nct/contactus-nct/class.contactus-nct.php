<?php
class ContactUs {
    function __construct($module = "", $id = 0, $reqData = array()) {
        foreach ($GLOBALS as $key => $values) {
            $this -> $key = $values;

        }
        $this -> module = $module;
        $this -> id = $id;

        //for web service
        $this->dataOnly = (isset($this->reqData['dataOnly']) && $this->reqData['dataOnly']==true)?true:false;
        $this->sessUserId = (isset($reqData['userId']) && $reqData['userId'] >0)?$reqData['userId']:$this -> sessUserId;

        $this->reqData = $reqData;
        if($this->dataOnly){
            $_SESSION['lId'] = (isset($this->reqData['lId']) && $this->reqData['lId'] >0)?$this->reqData['lId']:1;  
            setLang();  
        }

    }
    public function submitContactForm(){
        //make php validations
        $firstName = issetor($this->reqData['firstName'],null);
        $lastName = issetor($this->reqData['lastName'],null);
        $email = issetor($this->reqData['email'],null);        
        $contactNo = issetor($this->reqData['contactNo'],' ');
        $message = issetor($this->reqData['message'],null);

        if(trim($firstName) == null || trim($lastName) == null || trim($email) == null || trim($message) == null){
            echo json_encode(array('status'=>0,'msg'=>toastr_fill_all_required_details_before_proceed));exit;            
        }else{
            //mk entry in contact us table
            $lastInsertId = $this->db->insert('tbl_contact_us',array(
                'firstName'=>$firstName,
                'lastName'=>$lastName,
                'email'=>$email,
                'contactNo'=>$contactNo,
                'message'=>$message,
                'ipAddress'=>get_ip_address(),
                'createdDate'=>date('Y-m-d H:i:s')
            ))->lastInsertId();    
            //send email to admin
            
            if($lastInsertId > 0){
                $to = $email;
                $arrayCont = array(
                    'username' => $firstName,
                    'email' => $email,
                    'contactNo' => $contactNo,
                    'message' => $message                    
                );    
                $_SESSION['sendMailTo'] = getTableValue('tbl_users','userId',array('email'=>$email));                
                $array = generateEmailTemplate('contactUs', $arrayCont);
                sendEmailAddress($to, $array['subject'], $array['message']);
                        
                if($this->reqData['dataOnly'])
                {
                    $response['status'] = 1;
                    $response['msg'] = toastr_contact_us_thank_you;
                    return $response;
                }
                else
                {
                    echo json_encode(array('status'=>1,'msg'=>toastr_contact_us_thank_you));exit;
                }
            }
            
        }
        
    }

    public function getPageContent() {
        if ($this -> sessUserId > 0) {
            $usr = $this -> db -> select("tbl_users", array("*"), array("userId" => $this -> sessUserId)) -> result();
            $firstName = $usr['firstName'];
            $lastName = $usr['lastName'];
            $email = $usr['email'];
        }
        else {
            $firstName = null;
            $lastName = null;
            $email = null;        
        }
        
        $replace = array(
            "%firstName%" => $firstName,
            "%lastName%" => $lastName,
            "%email%" => $email
        );
        return get_view(DIR_TMPL . $this -> module . "/" . $this -> module . ".tpl.php",$replace);
    }

}
?>
