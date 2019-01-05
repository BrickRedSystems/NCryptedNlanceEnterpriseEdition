<?php
class Membership
{
    public function __construct($module = "", $id = 0, $token = "", $reqData = array(), $reffToken = "")
    {
        global $js_variables;
        foreach ($GLOBALS as $key => $values) {
            $this->$key = $values;
        }
        $this->module  = $module;
        $this->id      = $id;
        $this->reqData = $reqData;

        //for web service
        $this->dataOnly   = (isset($this->reqData['dataOnly']) && $this->reqData['dataOnly'] == true) ? true : false;
        $this->sessUserId = (isset($reqData['userId']) && $reqData['userId'] > 0) ? $reqData['userId'] : $this->sessUserId;

        $this->userData       = getUserData();
        $this->userMembership = getUserMembership();
        $js_variables         = "var credit_bunch ='" . $this->getAdminCreditPlan(true) . "'";

        if($this->dataOnly){
            $_SESSION['lId'] = (isset($this->reqData['lId']) && $this->reqData['lId'] >0)?$this->reqData['lId']:1;
            setLang();
        }
    }

    public function getAdminCreditPlan($onlyCredits = false, $onlyPrice = false)
    {
        $plan = $this->db->select('tbl_credit_plans', '*', array('isActive' => 'y'))->result();
        if (!empty($plan)) {
            if ($onlyCredits) {
                return $plan['credits'];
            }

            if ($onlyPrice) {
                return $plan['price'];
            }

            return $plan;
        } else {
            return false;
        }
    }

    public function getPageContent()
    {
        $results = $this->db->select('tbl_memberships', array('id','price','credits','membership_' . $_SESSION["lId"].' AS membership','description_' . $_SESSION["lId"].' AS description'), array('isActive' => 'y'));

        if ($this->dataOnly) {
            return $results->results();
            exit;
        } else {
            $results = $results->results();
        }

        $html = null;
        foreach ($results as $k => $v) {
            $replace = array(
                '%membership%'  => $v['membership'],
                '%description%' => $v['description'],
                '%credits%'     => $v['credits'],
                '%price%'       => CURRENCY_SYMBOL . $v['price'],
                '%id%'          => $v['id'],
                '%href%'        =>  SITE_BUY_MEMBERSHIP . $v['id'],
                '%buy_button%'  => ($v['id'] == $this->userMembership['membershipId']) ? Your_Current_Plan : Buy,

            );
            $html .= get_view(DIR_TMPL . $this->module . "/membership-row-nct.tpl.php", $replace);
        }

        return get_view(DIR_TMPL . $this->module . "/" . $this->module . ".tpl.php", array(
            '%plans%'        => $html,
            '%BUNCH_PRICE%'  => $this->getAdminCreditPlan(false, true),
            '%CREDIT_BUNCH%' => $this->getAdminCreditPlan(true),
            '%tokenValue%'   => setFormToken()));
        }

    }
