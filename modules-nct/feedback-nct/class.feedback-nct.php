<?php
class feedback {
    function __construct($module = "", $id = 0, $reqData = array()) {
        foreach ($GLOBALS as $key => $values) {
            $this -> $key = $values;

        }
        $this -> module = $module;
        $this -> id = $id;
        $this->reqData = $reqData;

    }
    public function submitFeedbackForm(){
        //make php validations
        $firstName = issetor($this->reqData['firstName'],null);
        $lastName = issetor($this->reqData['lastName'],null);
        $email = issetor($this->reqData['email'],null);        
        $message = issetor($this->reqData['message'],null);
        if(trim($firstName) == null || trim($lastName) == null || trim($email) == null || trim($message) == null){
            echo json_encode(array('status'=>0,'msg'=>'Please fill all the details properly before submitting'));exit;            
        }else{
            //mk entry in contact us table
            $lastInsertId = $this->db->insert('tbl_feedback',array(
                'firstName'=>$firstName,
                'lastName'=>$lastName,
                'email'=>$email,
                'message'=>$message,
                'ipAddress'=>get_ip_address(),
                'createdDate'=>date('Y-m-d H:i:s')
            ))->lastInsertId();    
            //send email to admin
            
            if($lastInsertId > 0){
                
                $arrayCont = array('email'=>$email,'username'=>$firstName.' '.$lastName,'message'=>$message);
           
                $array = generateEmailTemplate('user_feedback',$arrayCont);
                sendEmailAddress(ADMIN_EMAIL,$array['subject'],$array['message']);


                if($this->reqData['dataOnly'])
                {
                    $response['status'] = 1;
                    $response['msg'] = 'Your feedback has been submited successfully.';
                    return $response;
                }
                else
                {
                    echo json_encode(array('status'=>1,'msg'=>'Your feedback has been submited successfully.'));exit;
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
