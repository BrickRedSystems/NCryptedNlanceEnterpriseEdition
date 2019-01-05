<?php
class Operations
{
    public function __construct($module = "", $id = 0, $reqData = array())
    {
        global $js_variables;
        foreach ($GLOBALS as $key => $values) {
            $this->$key = $values;
        }
        $this->module     = $module;
        $this->id         = $id;
        $this->reqData    = $reqData;
        $this->dataOnly   = (isset($this->reqData['dataOnly']) && $this->reqData['dataOnly'] == true) ? true : false;
        $this->sessUserId = (isset($reqData['userId']) && $reqData['userId'] > 0) ? $reqData['userId'] : $this->sessUserId;

        $this->user = $this->userData();
        $this->isMe = ($this->user['userId'] == $this->sessUserId) ? 1 : 0;
        if($this->dataOnly){
            $_SESSION['lId'] = (isset($this->reqData['lId']) && $this->reqData['lId'] >0)?$this->reqData['lId']:1;  
            setLang();  
        }
    }

    public function toastrExit($bool = false, $msg = 'undefined')
    {
        echo json_encode(array(
            'status' => $bool,
            'msg'    => $msg,
        ));
        exit;
    }

    public function requestMilApprove()
    {

        extract($this->reqData);

        $id = (isset($id) && $id > 0) ? $id : 0;

        if ($id == 0) {

            if ($this->dataOnly) {
                return array(
                    'status' => 1,
                    'msg'    => Customer_has_been_notified_about_Completion_of_this_milestone);
                exit;
            } else {
                $this->toastrExit(0, toastr_No_Such_Milestone_found);
            }
        } else {
            $milDetail = $this->db->pdoQuery('select u.email as custEmail, u.profileLink, p.slug, pr.email as providerEmail, m.*,p.title, p.price as projPrice, b.escrow, concat_ws(" ",u.firstName,u.lastName) as custNm, concat_ws(" ",pr.firstName,pr.lastName) as provNm from tbl_milestone as m left join tbl_projects as p on m.projectId = p.id left join tbl_users as u on p.userId = u.userId left join tbl_users as pr on p.providerId = pr.userId left join tbl_bids as b on m.projectId = b.projectId and b.isFinal = "y" and b.isNulled = "n" where m.id=?', array('id' => $id))->result();

            $this->db->update('tbl_milestone', array('status' => 'remain', 'pay_request_date' => date('Y-m-d H:i:s')), array('id' => $id));
            //start:: get mil seq number
            $mileList = $this->db->pdoQuery("SELECT id FROM  tbl_milestone WHERE projectId = ? AND isNulled = ? ORDER BY milestone_date ASC", array($milDetail['projectId'], 'n'))->results();
            $i        = 0;
            foreach ($mileList as $mData) {
                $i++;
                if ($mData['id'] == $milDetail['id']) {
                    $milNumber = $i;
                }
            }
            //end:: get mil seq number
            //TODO:: send mail to customer
            $array = generateEmailTemplate('MilApproval', array(
                'greetings'    => ucwords($milDetail['custNm']),
                'providerName' => ucwords($milDetail['provNm']),
                'projectName'  => $milDetail['title'],
                'projectLink'  => SITE_URL . $milDetail['profileLink'] . '/' . $milDetail['slug'] . '/',
                'date'         => date('d M, Y'),
            ));
            //echo '<br/>'.$array['message'];exit;
            $milId   = $milDetail['id'];
            
            sendEmailAddress($milDetail['custEmail'], $array['subject'], $array['message']);

            //send notification
            $origin = substr($origin, 0, strpos($origin, "#"));

            if ($this->dataOnly) {
                return array(
                    'status' => 1,
                    'msg'    => Customer_has_been_notified_about_Completion_of_this_milestone);
            } else {
                echo json_encode(array(
                    'status'   => true,
                    'msg'      => Customer_has_been_notified_about_Completion_of_this_milestone,
                    'redirect' => issetor($origin, ''),
                ));
                exit;
            }
        }
    }

    public function readMessage()
    {
        extract($this->reqData);
        $msgId = issetor($id, 0);
        if ($msgId <= 0) {
            $this->toastrExit(0, No_such_message_found);
        } else {
            $this->db->update('tbl_messages', array('readStatus' => 'y'), array(
                'receiverId' => $this->sessUserId,
                'id'         => $msgId,
            ));

            $msg = $this->db->pdoQuery('SELECT m.* from tbl_messages AS m where m.id=?', array($msgId))->result();
            extract($msg);
            switch ($type) {
                case 'bid':
                case 'dispute':
                case 'workroom':
                    $projDetail = $this->db->pdoQuery('select p.title, p.slug, u.userId, u.email, u.firstName, u.profileLink from tbl_projects as p left join tbl_users as u on p.userId = u.userId where p.id=? ', array($projectId))->result();
                    $msgLink    = SITE_URL . $projDetail['profileLink'] . '/' . $projDetail['slug'] . '/';
                    break;

                default:
                    $msgLink = 'javascript:void(0);';
                    break;
            }

            echo json_encode(array(
                'status'   => true,
                'msg'      => 'undefined',
                'redirect' => $msgLink,
            ));
            exit;
        }

    }

    public function readNotification()
    {
        extract($this->reqData);
        $notiId = issetor($id, 0);
        if ($notiId == 0) {
            $this->toastrExit(0, No_such_notification_found);
        } else {
            $this->db->update('tbl_notification', array('isReaded' => 'y'), array(
                'toUserId' => $this->sessUserId,
                'id'       => $notiId,
            ));           

            echo json_encode(array(
                'status'   => true,
                'msg'      => 'undefined',
                'redirect' => get_user_notification($notiId, $getLink = true),
            ));
            exit;
        }

    }

    public function acceptBid()
    {
        extract($this->reqData);

        $bidId = (isset($bidId) && $bidId > 0) ? $bidId : 0;
        if ($bidId == 0) {
            $this->toastrExit(0, No_such_bid_found);
        } else {

            if ($this->dataOnly) {
                require_once SITE_MOD . "bid-nct/class.bid-nct.php";
            } else {
                require_once "../bid-nct/class.bid-nct.php";
            }

            $obj = new Bid("bid-nct", 0, $this->reqData);

            $bidData = $obj->bid;

            //check if project already been awarded
            if ($bidData['jobStatus'] != "open" && $bidData['jobStatus'] != "reopened") {

                if ($this->dataOnly) {
                    return array(
                        'status' => 0,
                        'msg'    => The_project_has_already_been_awarded_or,
                    );
                } else {
                    echo json_encode(array(
                        'status' => false,
                        'msg'    => The_project_has_already_been_awarded_or,
                    ));
                    exit;
                }
            }

            //update bid status as accepted
            $affectedRows = $this->db->update('tbl_bids', array('isAccepted' => 1, 'updateTime' => date('Y-m-d H:i:s')), array('id' => $bidId))->affectedRows();

            //change project status to accepted & price of project is now what was given by provider in bid, add providerId
            $affectedRows_tp = $this->db->update('tbl_projects', array(
                'jobStatus'  => 'accepted',
                'price'      => $bidData['price'],
                'providerId' => $bidData['userId'],
                //'hiredDate' => date('Y-m-d H:i:s')
            ), array('id' => $bidData['projectId']))->affectedRows();

            //TODO:: send mail to provider
            //send a notification to provider to set up milestones for the said budget

            $array = generateEmailTemplate('bid_accepted', array(
                'greetings'   => ucfirst($bidData['provider']['firstName']),
                'custName'    => $this->sessFirstName,
                'projectName' => $bidData['title'],
                'projectLink' => SITE_URL . $bidData['customer']['profileLink'] . '/' . $bidData['slug'] . '/',
                'date'        => date('d M, Y'),
            ));
            //echo '<br/>'.$array['message'];exit;
            sendEmailAddress($bidData['provider']['email'], $array['subject'], $array['message']);

            //send notification
            insert_user_notification($typeId = 12, $from = $this->sessUserId, $to = $bidData['provider']['userId'], $referenceId = $bidData['projectId']);

            $origin = substr($origin, 0, strpos($origin, "#"));

            if ($this->dataOnly) {
                return array(
                    'status' => 1,
                    'msg'    => The_bid_is_accepted_and_provider_shall_now_send_you_milestones_shortly,
                );
            } else {
                echo json_encode(array(
                    'status'   => true,
                    'msg'      => The_bid_is_accepted_and_provider_shall_now_send_you_milestones_shortly,
                    'redirect' => issetor($origin, ''),
                ));
                exit;
            }
        }

    }

    public function favoriteUser()
    {
        extract($this->reqData);
        $userId = issetor($id, 0);
        if ($userId <= 0) {
            $this->toastrExit(0, No_such_user_found);
        }
        $doesExist = getTableValue('tbl_favourites', 'id', array(
            'favoriteId' => $userId,
            'userId'     => $this->sessUserId,
            'type'       => 1,
        ));

        if (isset($doesExist) && $doesExist != "") {
            //unfav user
            $this->db->delete('tbl_favourites', array(
                'userId'     => $this->sessUserId,
                'favoriteId' => $userId,
                'type'       => 1,
            ));

            if ($this->dataOnly) {
                return array(
                    'status' => 1,
                    'msg'    => 'Success',
                    'html'   => '<a href="javascript:void(0);" class="" data-operation="favoriteUser" data-info="' . $userId . '"><i class="fa fa-heart-o"></i></a>',
                );
            } else {
                echo json_encode(array(
                    'status' => true,
                    'msg'    => 'undefined',
                    'html'   => '<a href="javascript:void(0);" class="" data-operation="favoriteUser" data-info="' . $userId . '"><i class="fa fa-heart-o"></i></a>',
                ));
                exit;
            }
        } else {
            //add to fav
            $lastInsertedId = $this->db->insert('tbl_favourites', array(
                'userId'      => $this->sessUserId,
                'favoriteId'  => $userId,
                'createdDate' => date('Y-m-d H:i:s'),
                'type'        => 1,
            ))->lastInsertId();
            if ($lastInsertedId > 0) {

                $userDetail = $this->userData($userId);

                $array = generateEmailTemplate('fav_user', array(
                    'greetings' => ucfirst($userDetail['firstName']),
                    'likerName' => $this->sessFirstName,
                    'likerLink' => SITE_URL . $this->user['profileLink'],
                    'date'      => date('d M, Y'),
                ));
                //echo '<br/>'.$array['message'];exit;
                sendEmailAddress($userDetail['email'], $array['subject'], $array['message']);

                //send notification
                insert_user_notification($typeId = 15, $to = $this->sessUserId, $referenceId = $userDetail['userId']);

                if ($this->dataOnly) {
                    return array(
                        'status' => 1,
                        'msg'    => 'Success',
                        'html'   => '<a href="javascript:void(0);" class="" data-operation="favoriteUser" data-info="' . $userId . '"><i class="fa fa-heart"></i></a>',
                    );
                } else {
                    echo json_encode(array(
                        'status' => true,
                        'msg'    => 'undefined',
                        'html'   => '<a href="javascript:void(0);" class="" data-operation="favoriteUser" data-info="' . $userId . '"><i class="fa fa-heart"></i></a>',
                    ));
                    exit;
                }
            }
        }
    }

    public function favoriteProvider()
    {
        extract($this->reqData);
        $userId = issetor($id, 0);
        if ($userId <= 0) {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => No_such_user_found,
                );
            } else {
                $this->toastrExit(0, No_such_user_found);
            }
        }
        $doesExist = getTableValue('tbl_favourites', 'id', array(
            'favoriteId' => $userId,
            'userId'     => $this->sessUserId,
            'type'       => 1,
        ));

        if (isset($doesExist) && $doesExist != "") {
            //unfav user
            $this->db->delete('tbl_favourites', array(
                'userId'     => $this->sessUserId,
                'favoriteId' => $userId,
                'type'       => 1,
            ));

            if ($this->dataOnly) {
                return array(
                    'status' => 1,
                    'msg'    => 'Success',
                    'html'   => '<a href="javascript:void(0);" class="" data-operation="favoriteProvider" data-info="' . $userId . '"><i class="fa fa-heart-o like-icon"></i></a>',
                );
            } else {
                echo json_encode(array(
                    'status' => true,
                    'msg'    => 'undefined',
                    'html'   => '<a href="javascript:void(0);" class="" data-operation="favoriteProvider" data-info="' . $userId . '"><i class="fa fa-heart-o like-icon"></i></a>',
                ));
                exit;
            }
        } else {
            //add to fav
            $lastInsertedId = $this->db->insert('tbl_favourites', array(
                'userId'      => $this->sessUserId,
                'favoriteId'  => $userId,
                'createdDate' => date('Y-m-d H:i:s'),
                'type'        => 1,
            ))->lastInsertId();
            if ($lastInsertedId > 0) {
                $userDetail = $this->userData($userId);

                $array = generateEmailTemplate('fav_user', array(
                    'greetings' => ucfirst($userDetail['firstName']),
                    'likerName' => $this->sessFirstName,
                    'likerLink' => SITE_URL . $this->user['profileLink'],
                    'date'      => date('d M, Y'),
                ));
                //echo '<br/>'.$array['message'];exit;
                sendEmailAddress($userDetail['email'], $array['subject'], $array['message']);

                //send notification
                insert_user_notification($typeId = 15, $from = $this->sessUserId, $to = $userId, $referenceId = $userDetail['userId']);

                if ($this->dataOnly) {
                    return array(
                        'status' => 1,
                        'msg'    => 'Success',
                        'html'   => '<a href="javascript:void(0);" class="" data-operation="favoriteProvider" data-info="' . $userId . '"><i class="fa fa-heart like-icon"></i></a>',
                    );
                } else {
                    echo json_encode(array(
                        'status' => true,
                        'msg'    => 'undefined',
                        'html'   => '<a href="javascript:void(0);" class="" data-operation="favoriteProvider" data-info="' . $userId . '"><i class="fa fa-heart like-icon"></i></a>',
                    ));
                    exit;
                }
            }
        }
    }

    public function favoriteProject()
    {

        extract($this->reqData);
        $projectId = issetor($projectId, 0);
        if ($projectId == 0) {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => No_such_project_found);
            } else {
                $this->toastrExit(0, No_such_project_found);
            }
        }
        $doesExist = getTableValue('tbl_favourites', 'id', array(
            'favoriteId' => $projectId,
            'userId'     => $this->sessUserId,
            'type'       => 2,
        ));

        if (isset($doesExist) && $doesExist != "") {
            //unfav proj
            $this->db->delete('tbl_favourites', array(
                'userId'     => $this->sessUserId,
                'favoriteId' => $projectId,
                'type'       => 2,
            ));

            if ($this->dataOnly) {
                return array(
                    'status' => 1,
                    'msg'    => 'Success',
                    'html'   => '<a href="javascript:void(0);" class="black-link" data-operation="favoriteProject" data-info="' . $projectId . '"><i class="fa fa-heart-o"></i> '.Add_to_Favorites.'</a>',
                );
            } else {
                echo json_encode(array(
                    'status' => true,
                    'msg'    => 'undefined',
                    'html'   => '<a href="javascript:void(0);" class="black-link" data-operation="favoriteProject" data-info="' . $projectId . '"><i class="fa fa-heart-o"></i> '.Add_to_Favorites.'</a>',
                ));
                exit;
            }
        } else {
            //add to fav
            $lastInsertedId = $this->db->insert('tbl_favourites', array(
                'userId'      => $this->sessUserId,
                'favoriteId'  => $projectId,
                'createdDate' => date('Y-m-d H:i:s'),
                'type'        => 2,
            ))->lastInsertId();
            if ($lastInsertedId > 0) {

                $projeDetail = $this->db->pdoQuery('select p.title, p.slug, u.userId, u.email, u.firstName, u.profileLink from tbl_projects as p left join tbl_users as u on p.userId = u.userId where p.id=? ', array($projectId))->result();

                $array = generateEmailTemplate('fav_project', array(
                    'greetings'   => ucfirst($projeDetail['firstName']),
                    'likerName'   => $this->sessFirstName,
                    'projectName' => $projeDetail['title'],
                    'likerLink'   => SITE_URL . $this->user['profileLink'],
                    'projectLink' => SITE_URL . $projeDetail['profileLink'] . '/' . $projeDetail['slug'] . '/',
                    'date'        => date('d M, Y'),
                ));
                //echo '<br/>'.$array['message'];exit;
                sendEmailAddress($projeDetail['email'], $array['subject'], $array['message']);

                //send notification
                insert_user_notification($typeId = 13, $from = $this->sessUserId, $to = $projeDetail['userId'], $referenceId = $projectId, array('%userName%' => $this->sessFirstName, '%projectName%' => $projeDetail['title']));

                if ($this->dataOnly) {
                    return array(
                        'status' => 1,
                        'msg'    => 'Success',
                        'html'   => '<a href="javascript:void(0);" class="black-link" data-operation="favoriteProject" data-info="' . $projectId . '"><i class="fa fa-heart"></i> '.Remove_from_Favorites.'</a>',
                    );
                } else {
                    echo json_encode(array(
                        'status' => true,
                        'msg'    => 'undefined',
                        'html'   => '<a href="javascript:void(0);" class="black-link" data-operation="favoriteProject" data-info="' . $projectId . '"><i class="fa fa-heart"></i> '.Remove_from_Favorites.'</a>',
                    ));
                    exit;
                }
            }
        }
    }

    public function favoriteProjectMyProviders()
    {
        extract($this->reqData);
        $projectId = issetor($id, 0);
        if ($projectId == 0) {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => No_such_project_found,
                );
            } else {
                $this->toastrExit(0, No_such_project_found);
            }
        }
        $doesExist = getTableValue('tbl_favourites', 'id', array(
            'favoriteId' => $projectId,
            'userId'     => $this->sessUserId,
            'type'       => 2,
        ));

        if (isset($doesExist) && $doesExist != "") {
            //unfav proj
            $this->db->delete('tbl_favourites', array(
                'userId'     => $this->sessUserId,
                'favoriteId' => $projectId,
                'type'       => 2,
            ));

            if ($this->dataOnly) {
                return array(
                    'status' => 1,
                    'msg'    => 'Success',
                    'html'   => '<a href="javascript:void(0);"  data-operation="favoriteProjectMyProviders" data-info="' . $projectId . '" ><i class="fa fa-heart-o like-icon"></i> </a>',
                );
            } else {
                echo json_encode(array(
                    'status' => true,
                    'msg'    => 'undefined',
                    'html'   => '<a href="javascript:void(0);"  data-operation="favoriteProjectMyProviders" data-info="' . $projectId . '" ><i class="fa fa-heart-o like-icon"></i> </a>',
                ));
                exit;
            }
        } else {
            //add to fav
            $lastInsertedId = $this->db->insert('tbl_favourites', array(
                'userId'      => $this->sessUserId,
                'favoriteId'  => $projectId,
                'createdDate' => date('Y-m-d H:i:s'),
                'type'        => 2,
            ))->lastInsertId();
            if ($lastInsertedId > 0) {
                $projeDetail = $this->db->pdoQuery('select p.title, p.slug, u.userId, u.email, u.firstName, u.profileLink from tbl_projects as p left join tbl_users as u on p.userId = u.userId where p.id=? ', array($projectId))->result();

                $array = generateEmailTemplate('fav_project', array(
                    'greetings'   => ucfirst($projeDetail['firstName']),
                    'likerName'   => $this->sessFirstName,
                    'projectName' => $projeDetail['title'],
                    'likerLink'   => SITE_URL . $this->user['profileLink'],
                    'projectLink' => SITE_URL . $projeDetail['profileLink'] . '/' . $projeDetail['slug'] . '/',
                    'date'        => date('d M, Y'),
                ));
                //echo '<br/>'.$array['message'];exit;
                sendEmailAddress($projeDetail['email'], $array['subject'], $array['message']);

                //send notification
                insert_user_notification($typeId = 13, $from = $this->sessUserId, $to = $projeDetail['userId'], $referenceId = $projectId, array('%userName%' => $this->sessFirstName, '%projectName%' => $projeDetail['title']));

                if ($this->dataOnly) {
                    return array(
                        'status' => 1,
                        'msg'    => 'Success',
                        'html'   => '<a href="javascript:void(0);"  data-operation="favoriteProjectMyProviders" data-info="' . $projectId . '" ><i class="fa fa-heart like-icon"></i> </a>',
                    );
                } else {
                    echo json_encode(array(
                        'status' => true,
                        'msg'    => 'undefined',
                        'html'   => '<a href="javascript:void(0);"  data-operation="favoriteProjectMyProviders" data-info="' . $projectId . '" ><i class="fa fa-heart like-icon"></i> </a>',
                    ));
                    exit;
                }
            }
        }
    }

    public function favoriteProjectMyProjects()
    {
        extract($this->reqData);
        $projectId = issetor($id, 0);
        if ($projectId == 0) {

            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => No_such_project_found,
                );
            } else {
                $this->toastrExit(0, No_such_project_found);
            }
        }
        $doesExist = getTableValue('tbl_favourites', 'id', array(
            'favoriteId' => $projectId,
            'userId'     => $this->sessUserId,
            'type'       => 2,
        ));

        if (isset($doesExist) && $doesExist != "") {
            //unfav proj
            $this->db->delete('tbl_favourites', array(
                'userId'     => $this->sessUserId,
                'favoriteId' => $projectId,
                'type'       => 2,
            ));

            if ($this->dataOnly) {
                return array(
                    'status' => 1,
                    'msg'    => 'Success',
                    'html'   => '<a class="favouriteproject" href="javascript:void(0);"  data-operation="favoriteProjectMyProjects" data-info="' . $projectId . '" ><i class="fa fa-heart-o like-icon"></i> </a>',
                );
            } else {
                echo json_encode(array(
                    'status' => true,
                    'msg'    => 'undefined',
                    'html'   => '<a class="favouriteproject" href="javascript:void(0);"  data-operation="favoriteProjectMyProjects" data-info="' . $projectId . '" ><i class="fa fa-heart-o like-icon"></i> </a>',
                ));
                exit;
            }
        } else {
            //add to fav
            $lastInsertedId = $this->db->insert('tbl_favourites', array(
                'userId'      => $this->sessUserId,
                'favoriteId'  => $projectId,
                'createdDate' => date('Y-m-d H:i:s'),
                'type'        => 2,
            ))->lastInsertId();
            if ($lastInsertedId > 0) {
                $projeDetail = $this->db->pdoQuery('select p.title, p.slug, u.userId, u.email, u.firstName, u.profileLink from tbl_projects as p left join tbl_users as u on p.userId = u.userId where p.id=? ', array($projectId))->result();

                $array = generateEmailTemplate('fav_project', array(
                    'greetings'   => ucfirst($projeDetail['firstName']),
                    'likerName'   => $this->sessFirstName,
                    'projectName' => $projeDetail['title'],
                    'likerLink'   => SITE_URL . $this->user['profileLink'],
                    'projectLink' => SITE_URL . $projeDetail['profileLink'] . '/' . $projeDetail['slug'] . '/',
                    'date'        => date('d M, Y'),
                ));
                //echo '<br/>'.$array['message'];exit;
                sendEmailAddress($projeDetail['email'], $array['subject'], $array['message']);

                //send notification
                insert_user_notification($typeId = 13, $from = $this->sessUserId, $to = $projeDetail['userId'], $referenceId = $projectId, array('%userName%' => $this->sessFirstName, '%projectName%' => $projeDetail['title']));

                if ($this->dataOnly) {
                    return array(
                        'status' => 1,
                        'msg'    => 'Success',
                        'html'   => '<a class="favouriteproject" href="javascript:void(0);"  data-operation="favoriteProjectMyProjects" data-info="' . $projectId . '" ><i class="fa fa-heart like-icon"></i> </a>',
                    );
                } else {
                    echo json_encode(array(
                        'status' => true,
                        'msg'    => 'undefined',
                        'html'   => '<a class="favouriteproject" href="javascript:void(0);"  data-operation="favoriteProjectMyProjects" data-info="' . $projectId . '" ><i class="fa fa-heart like-icon"></i> </a>',
                    ));
                    exit;
                }
            }
        }
    }

    public function reportProject()
    {
        extract($this->reqData);
        $projectId = issetor($projectId, 0);
        if ($projectId == 0) {
            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => No_such_project_found);
            } else {
                $this->toastrExit(0, No_such_project_found);
            }
        }
        $doesExist = getTableValue('tbl_report_abuse', 'id', array(
            'userId'    => $this->sessUserId,
            'projectId' => $projectId,
            'isActive'  => 'y',
        ));
        if (isset($doesExist) && $doesExist != "") {
            if ($this->dataOnly) {
                return array(
                    'status' => 0,
                    'msg'    => You_have_already_flagged_this_as_inappropriate);
            } else {
                $this->toastrExit(0, You_have_already_flagged_this_as_inappropriate);
            }
        } else {
            $lastInsertedId = $this->db->insert('tbl_report_abuse', array(
                'userId'      => $this->sessUserId,
                'projectId'   => $projectId,
                'message'     => Project_has_been_marked_as_inappropriate,
                'createdDate' => date('Y-m-d H:i:s'),
            ))->lastInsertId();
            if ($lastInsertedId > 0) {

                if ($this->dataOnly) {
                    return array(
                        'status' => 1,
                        'msg'    => item_has_been_flagged_as_inappropriate,
                    );
                } else {
                    echo json_encode(array(
                        'status' => true,
                        'msg'    => item_has_been_flagged_as_inappropriate,
                        'html'   => '<p>'.You_flagged_this_as_Inappropriate.'</p>',
                    ));
                    exit;
                }
            }
        }
    }

    public function userData($userId = 0)
    {
        $userId    = ($userId > 0) ? $userId : $this->sessUserId;
        $doesExist = getTableValue('tbl_users', 'userId', array(
            'userId'   => $userId,
            'isActive' => 'y',
        ));
        if (isset($doesExist) && $doesExist > 0) {
            $arr = array();
            $arr = $this->db->pdoQuery("SELECT u.*, CONCAT_WS(' ',u.firstName,u.lastName) AS fullName FROM tbl_users AS u WHERE u.userId=? ", array($userId))->result();
            if ($arr['userType'] == 'c') {
                $arr['ongoing'] = getTableValue('tbl_projects', 'count("id")', array(
                    'userId'    => $arr['userId'],
                    'jobStatus' => 'open',
                    'isActive'  => 'y',
                ));
                $arr['completed'] = getTableValue('tbl_projects', 'count("id")', array(
                    'userId'    => $arr['userId'],
                    'jobStatus' => 'completed',
                    'isActive'  => 'y',
                ));
            } else {
                $arr['ongoing'] = getTableValue('tbl_projects', 'count("id")', array(
                    'providerId' => $arr['userId'],
                    'jobStatus'  => 'open',
                    'isActive'   => 'y',
                ));
                $arr['completed'] = getTableValue('tbl_projects', 'count("id")', array(
                    'providerId' => $arr['userId'],
                    'jobStatus'  => 'completed',
                    'isActive'   => 'y',
                ));
            }
            return $arr;
        } else {

            echo json_encode(array(
                'status'   => false,
                'msg'      => Please_login_to_perform_this_action,
                'redirect' => SITE_LOGIN . "?path=" . issetor($this->reqData['origin'], ''),
            ));
            exit;
        }
    }

}
