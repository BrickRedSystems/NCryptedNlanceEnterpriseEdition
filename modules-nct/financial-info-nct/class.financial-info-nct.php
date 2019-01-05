<?php
class FinancialInfo
{
    public function __construct($module = "", $id = 0, $reqData = array())
    {
        foreach ($GLOBALS as $key => $values) {
            $this->$key = $values;
        }
        $this->module  = $module;
        $this->id      = $id;
        $this->reqData = $reqData;

        //for web service
        $this->dataOnly     = (isset($this->reqData['dataOnly']) && $this->reqData['dataOnly'] == true) ? true : false;
        $this->sessUserId   = (isset($reqData['userId']) && $reqData['userId'] > 0) ? $reqData['userId'] : $this->sessUserId;
        $this->sessUserType = getTableValue('tbl_users', 'userType', array('userId' => $this->sessUserId));

        $this->completed_totals = $this->completed_row(true);
        $this->progress_totals  = $this->inProgress_row(true);

        if($this->dataOnly){
            $_SESSION['lId'] = (isset($this->reqData['lId']) && $this->reqData['lId'] >0)?$this->reqData['lId']:1;  
            setLang();  
        }

    }

    public function completed_row($totals = false)
    {
        $results = array();
        $html    = null;

        if ($this->sessUserType == "c") {
            $results = $this->db->pdoQuery('SELECT p.id , p.price AS total, p.slug, p.title, u.`profileLink`, CONCAT_WS(" ", u.`firstName`, u.`lastName`) AS fullName, u.`profilePhoto` FROM `tbl_projects` AS p LEFT JOIN tbl_users AS u ON p.userId = u.`userId` LEFT JOIN (SELECT IFNULL(SUM(m.price), 0) AS paid, m.`projectId` FROM tbl_milestone AS m WHERE m.status = "paid") AS tbl_paid ON p.`id` = tbl_paid.projectId WHERE u.`isActive` = "y" AND p.userId = ? AND p.jobStatus =? ', array(
                $this->sessUserId,
                "completed",
            ))->results();
        } else {
            $results = $this->db->pdoQuery('SELECT p.`id`, @pprice:= p.price AS total, p.slug, p.title, u.`profileLink`, CONCAT_WS(" ", u.`firstname`, u.`lastname`) AS fullName, u.`profilePhoto` FROM `tbl_projects` AS p LEFT JOIN tbl_users AS u ON p.userid = u.`userid` LEFT JOIN `tbl_milestone` AS m ON p.id = m.projectId  WHERE u.`isactive` = "y" AND p.providerid = ? AND p.jobstatus = ? GROUP BY p.`id`', array(
                $this->sessUserId,
                "completed",
            ))->results();
        }

        //calculate amounts
        $total_price = $total_paid = $total_outstanding = 0;
        if (!empty($results)) {
            foreach ($results as $k => $v) {
                extract($v);
                $paidAmt        = $this->db->pdoQuery("SELECT SUM(price) AS paid FROM tbl_milestone WHERE projectId = ? AND status = ? AND isNulled = ? AND isFinal = ?", array($id, "paid", "n", "y"))->result();
                $outstandingAmt = $this->db->pdoQuery("SELECT SUM(price) AS outstanding FROM tbl_milestone WHERE projectId = ? AND (status = ? OR status = ?) AND isNulled = ? AND isFinal = ?", array($id, "unapproved", "remain", "n", "y"))->result();
                $paid           = $paidAmt['paid'];
                $outstanding    = $outstandingAmt['outstanding'];
                if ($totals == false || $this->dataOnly) {
                    $html .= get_view(DIR_TMPL . $this->module . "/in_progress_row-nct.tpl.php", array(
                        '%profileLink%'       => SITE_URL . $profileLink,
                        '%projLink%'          => SITE_URL . $profileLink . '/' . $slug,
                        '%profilePhoto%'      => tim_thumb_image($profilePhoto, 'profile', 74, 74),

                        '%total_price%'       => issetor($this->completed_totals['total_price'],0),
                        '%total_paid%'        => issetor($this->completed_totals['total_paid'],0),
                        '%paid_or_earned%'    => ($this->sessUserType == "c") ? Paid : Earned,
                        '%total_outstanding%' => issetor($this->completed_totals['total_outstanding'],0),

                        '%fullName%'          => $fullName,
                        '%title%'             => filtering($title),
                        '%total%'             => number_format($total, 2),
                        '%paid%'              => number_format($paid, 2),
                        '%outstanding%'       => number_format($outstanding, 2),

                    ));
                }
                $total_price += $total;
                $total_paid += $paid;
                $total_outstanding += $outstanding;
                //for web services
                $results[$k]['paid']=$paid;
                $results[$k]['outstanding']=$outstanding;
            }

            if ($this->dataOnly) {

                return array(
                    'total_price'       => $total_price,
                    'total_paid'        => $total_paid,
                    'total_outstanding' => $total_outstanding,
                    'data'              => $results,
                );
            }
        } else {
            if ($this->dataOnly) {
                return array(
                    'total_price'       => $total_price,
                    'total_paid'        => $total_paid,
                    'total_outstanding' => $total_outstanding,
                    'data'              => $results,
                );
            }
        }
        return ($totals == true) ? array(
            'total_price'       => number_format($total_price, 2),
            'total_paid'        => number_format($total_paid, 2),
            'total_outstanding' => number_format($total_outstanding, 2),
        ) : $html;
    }

    public function completed()
    {
        if ($this->completed_row() == null) {
            return get_view(DIR_TMPL . $this->module . "/no_in_progress_row-nct.tpl.php", array('%status%' => Completed));
        } else {

            extract($this->completed_row(true));
            return get_view(DIR_TMPL . $this->module . "/in_progress-nct.tpl.php", array(
                '%total_price%'       => $total_price,
                '%total_paid%'        => $total_paid,
                '%total_outstanding%' => $total_outstanding,
                '%paid_or_earned%'    => ($this->sessUserType == "c") ? Paid : Earned,
                '%row%'               => $this->completed_row(),
            ));
        }

    }

    public function inProgress_row($totals = false)
    {
        $results = array();
        $html    = null;
        if ($this->sessUserType == "c") {
            $results = $this->db->pdoQuery('SELECT p.id,p.price AS total, p.slug, p.title, u.`profileLink`, CONCAT_WS(" ", u.`firstName`, u.`lastName`) AS fullName, u.`profilePhoto` FROM `tbl_projects` AS p LEFT JOIN tbl_users AS u ON p.userId = u.`userId` LEFT JOIN (SELECT IFNULL(SUM(m.price), 0) AS paid, m.`projectId` FROM tbl_milestone AS m WHERE m.status = "paid") AS tbl_paid ON p.`id` = tbl_paid.projectId WHERE u.`isActive` = "y" AND p.userId = ? AND p.jobStatus =? ', array(
                $this->sessUserId,
                "progress",
            ))->results();
        } else {
            $results = $this->db->pdoQuery('SELECT p.`id`, @pprice:= p.price AS total, p.slug, p.title, u.`profileLink`, CONCAT_WS(" ", u.`firstname`, u.`lastname`) AS fullName, u.`profilePhoto` FROM `tbl_projects` AS p LEFT JOIN tbl_users AS u ON p.userid = u.`userid` LEFT JOIN `tbl_milestone` AS m ON p.id = m.projectId  WHERE u.`isactive` = "y" AND p.providerid = ? AND p.jobstatus = ? GROUP BY p.`id`', array(
                $this->sessUserId,
                "progress",
            ))->results();
        }

        //calculate amounts
        $total_price = $total_paid = $total_outstanding = 0;
        if (!empty($results)) {
            foreach ($results as $k => $v) {
                extract($v);
                $paidAmt        = $this->db->pdoQuery("SELECT SUM(price) AS paid FROM tbl_milestone WHERE projectId = ? AND status = ? AND isNulled = ? AND isFinal = ?", array($id, "paid", "n", "y"))->result();
                $outstandingAmt = $this->db->pdoQuery("SELECT SUM(price) AS outstanding FROM tbl_milestone WHERE projectId = ? AND (status = ? OR status = ?) AND isNulled = ? AND isFinal = ?", array($id, "unapproved", "remain", "n", "y"))->result();
                $paid           = $paidAmt['paid'];
                $outstanding    = $outstandingAmt['outstanding'];
                if ($totals == false || $this->dataOnly) {
                    $html .= get_view(DIR_TMPL . $this->module . "/in_progress_row-nct.tpl.php", array(
                        '%profileLink%'       => SITE_URL . $profileLink,
                        '%projLink%'          => SITE_URL . $profileLink . '/' . $slug,
                        '%profilePhoto%'      => tim_thumb_image($profilePhoto, 'profile', 74, 74),
                        '%fullName%'          => $fullName,

                        '%total_price%'       => $this->progress_totals['total_price'],
                        '%total_paid%'        => $this->progress_totals['total_paid'],
                        '%paid_or_earned%'    => ($this->sessUserType == "c") ? Paid : Earned,
                        '%total_outstanding%' => $this->progress_totals['total_outstanding'],

                        '%title%'             => filtering($title),
                        '%total%'             => number_format($total, 2),
                        '%paid%'              => number_format($paid, 2),
                        '%outstanding%'       => number_format($outstanding, 2),
                    ));
                }
                $total_price += $total;
                $total_paid += $paid;
                $total_outstanding += $outstanding;

                //for web services
                $results[$k]['paid']=$paid;
                $results[$k]['outstanding']=$outstanding;
            }

            if ($this->dataOnly) {
                return array(
                    'total_price'       => $total_price,
                    'total_paid'        => $total_paid,
                    'total_outstanding' => $total_outstanding,
                    'data'              => $results,
                );
            }
        } else {
            if ($this->dataOnly) {
                return array(
                    'total_price'       => $total_price,
                    'total_paid'        => $total_paid,
                    'total_outstanding' => $total_outstanding,
                    'data'              => $results,
                );
            }
        }

        return ($totals == true) ? array(
            'total_price'       => number_format($total_price, 2),
            'total_paid'        => number_format($total_paid, 2),
            'total_outstanding' => number_format($total_outstanding, 2),
        ) : $html;
    }

    public function inProgress()
    {
        if ($this->inProgress_row() == null) {
            return get_view(DIR_TMPL . $this->module . "/no_in_progress_row-nct.tpl.php", array('%status%' => In_Progress));
        } else {

            extract($this->inProgress_row(true));
            return get_view(DIR_TMPL . $this->module . "/in_progress-nct.tpl.php", array(
                '%total_price%'       => $total_price,
                '%total_paid%'        => $total_paid,
                '%total_outstanding%' => $total_outstanding,
                '%paid_or_earned%'    => ($this->sessUserType == "c") ? Paid : Earned,
                '%row%'               => $this->inProgress_row(),
            ));
        }
    }

    /* Deposit History Code ::: START */

    public function deposit_history()
    {
        if ($this->deposit_history_row() == null) {
            return get_view(DIR_TMPL . $this->module . "/no_in_progress_row-nct.tpl.php", array('%status%' => 'in progress'));
        } else {
            return get_view(DIR_TMPL . $this->module . "/deposit_history-nct.tpl.php", array(
                '%row%' => $this->deposit_history_row(),
            ));
        }
    }

    public function deposit_history_row($totals = false)
    {
        $results = array();
        $html    = null;

        $results = $this->db->pdoQuery(
            'SELECT *
        FROM `tbl_payment_history`
        WHERE `userId` = ? AND `paymentType` = ?', array(
                $this->sessUserId,
                "deposit to wallet",
            ))->results();

        //calculate amounts
        if (!empty($results)) {
            
            if ($this->dataOnly) {
                return array(
                    'data' => $results,
                );
            }
            $count = 1;
            foreach ($results as $k) {
                extract($k);

                $html .= get_view(DIR_TMPL . $this->module . "/deposit_history_row-nct.tpl.php", array(
                    '%no%'             => $count,
                    '%total%'          => number_format($totalAmount, 2),
                    '%transaction_id%' => $transactionId,
                    '%date%'           => ($createdDate > 0) ? date(PHP_DATE_FORMAT, strtotime($createdDate)) : Not_Available,
                    '%status%'         => $paymentStatus,
                ));

                $count++;
            }
        } else {
            if ($this->dataOnly) {
                return array(
                    'data' => array(),
                );
            }

        }
        return $html;
    }

    public function getPageContent()
    {
        return get_view(DIR_TMPL . $this->module . "/" . $this->module . ".tpl.php", array('%tab_panel%' => $this->inProgress()));
    }

}
