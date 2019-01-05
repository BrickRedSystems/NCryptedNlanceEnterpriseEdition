<?php
class Home
{
    public function __construct($module = "", $reqData = array())
    {
        foreach ($GLOBALS as $key => $values) {
            $this->$key = $values;
        }
        $this->module   = $module;
        $this->reqData  = $reqData;
        $this->userData = getUserData();

        //dump_exit($this->userData);
        //TODO:: set height width of featured projects
    }

    public function getLangClass($getCode = false){
        $langCode = getTableValue('tbl_languages','langCode',array('id'=>$_SESSION['lId']));
        if($getCode){
            return $langCode;
        }else{
            return ($langCode == 'en')? null : 'nonEnglishLTR';    
        }
        
    }

    public function getMessages($count = false)
    {
        if ($count) {
            $unreadMsgs = $this->db->pdoQuery("SELECT m.*, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName FROM tbl_messages AS m LEFT JOIN tbl_users AS u ON m.senderId = u.userId WHERE m.readStatus = 'n' AND m.`receiverId`=? ORDER BY m.id ASC ", array($this->sessUserId))->results();
            return count($unreadMsgs);
        } else {
            $Msgs = $this->db->pdoQuery("SELECT m.*, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName, u.profileLink, u.profilePhoto FROM tbl_messages AS m LEFT JOIN tbl_users AS u ON m.senderId = u.userId WHERE m.`receiverId`=? ORDER BY m.id DESC LIMIT 5 ", array($this->sessUserId))->results();
            $html = null;
            if (!empty($Msgs)) {
                foreach ($Msgs as $k => $v) {
                    extract($v);

                    $response = get_time_difference($createdDate, date("Y-m-d H:i:s"));
                    if ($response['days']) {
                        $time_ago = ($response['days'] > 10) ? few . ' ' . days . ' ' . ago : $response['days'] .' ' .days . ' ' . ago;
                    } else if ($response['hours']) {
                        $time_ago = $response['hours'] . ' ' . hours . " " . ago;
                    } else if ($response['minutes']) {
                        $time_ago = $response['minutes'] . ' ' . minutes . " " . ago;
                    } else if ($response['seconds']) {
                        $time_ago = $response['seconds'] . ' ' . seconds . " " . ago;
                    }

                    $replace = array(
                        '%fullName%'     => ucwords($fullName),
                        '%profileLink%'  => SITE_URL . $profileLink,
                        '%messageLink%'  => '#',
                        '%profilePhoto%' => tim_thumb_image($profilePhoto, 'profile', 300, 300),
                        '%class%'        => ($readStatus == 'n') ? 'unread' : null,
                        '%desc%'         => filtering($description),
                        '%time%'         => $time_ago,
                        '%msgId%'        => $id,
                    );
                    $html .= get_view(DIR_TMPL . $this->module . "/messages/row-nct.tpl.php", $replace);
                }
            } else {

                $html .= get_view(DIR_TMPL . $this->module . "/messages/no_message-nct.tpl.php");
            }
            return $html;
        }
    }

    public function getNotifications($count = false)
    {
        if ($count) {
            $unreadNoti = $this->db->pdoQuery("SELECT n.*, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName FROM tbl_notification AS n LEFT JOIN tbl_users AS u ON n.fromUserId = u.userId WHERE n.isReaded = 'n' AND n.`toUserId` = ? ORDER BY n.id ASC  ", array($this->sessUserId))->results();
            return count($unreadNoti);
        } else {
            $Noti = $this->db->pdoQuery("SELECT nt.color, n.*, CONCAT_WS(' ', u.firstName, u.lastName) AS fullName, u.profileLink, u.profilePhoto FROM tbl_notification AS n LEFT JOIN tbl_users AS u ON n.fromUserId = u.userId LEFT JOIN tbl_notification_types AS nt ON n.`typeId` = nt.id WHERE n.`toUserId` = ? ORDER BY n.id DESC LIMIT 5 ", array($this->sessUserId))->results();
            $html = null;
            if (!empty($Noti)) {
                foreach ($Noti as $k => $v) {
                    extract($v);

                    $response = get_time_difference($createdDate, date("Y-m-d H:i:s"));

                    if ($response['days']) {
                        $time_ago = ($response['days'] > 10) ? few . ' ' . days . ' ' . ago : $response['days'] . ' '.days . ' ' . ago;
                    } else if ($response['hours']) {
                        $time_ago = $response['hours'] . ' ' . hours . " " . ago;
                    } else if ($response['minutes']) {
                        $time_ago = $response['minutes'] . ' ' . minutes . " " . ago;
                    } else if ($response['seconds']) {
                        $time_ago = $response['seconds'] . ' ' . seconds . " " . ago;
                    }

                    $replace = array(
                        '%fullName%'         => ucwords($fullName),
                        '%profileLink%'      => SITE_URL . $profileLink,
                        '%notificationLink%' => 'javascript:void(0);',
                        '%profilePhoto%'     => tim_thumb_image($profilePhoto, 'profile', 300, 300),
                        '%class%'            => ($isReaded == 'n') ? 'unread' : null,
                        '%notification%'     => filtering(get_user_notification($id)),
                        '%time%'             => $time_ago,
                        '%notiId%'           => $id,
                        '%color%'            => $color,
                    );
                    $html .= get_view(DIR_TMPL . $this->module . "/notifications/row-nct.tpl.php", $replace);
                }
            } else {

                $html .= get_view(DIR_TMPL . $this->module . "/notifications/no_notification-nct.tpl.php");
            }
            return $html;
        }
    }

    public function getHeaderContent($module = 'home-nct')
    {
        $header_section = null;
        if (isset($this->sessUserId) && $this->sessUserId > 0) {
            $replace = array(
                '%navbar_Type%'            => ($module == 'home-nct') ? 'white' : null,
                '%userName%'               => $this->userData['firstName'] . ' ' . $this->userData['lastName'],
                '%profilePhoto%'           => tim_thumb_image($this->userData['profilePhoto'], 'profile', 35, 35),
                '%profileLink%'            => $this->userData['profileLink'],
                '%unreadMessageCount%'     => $this->getMessages(true),
                '%hideUnreadMessageCount%' => (($this->getMessages(true) == 0) ? 'hide' : null),
                '%messages%'               => $this->getMessages(),
                '%unreadNotiCount%'        => $this->getNotifications(true),
                '%hideUnreadNotiCount%'    => (($this->getNotifications(true) == 0) ? 'hide' : null),
                '%notifications%'          => $this->getNotifications(),
                '%lang_options%'           => $this->getLanguageOptions(),
            );
            $header_section = get_view(DIR_TMPL . $this->module . "/after-login-header-section.tpl.php", $replace);
        } elseif ($module == 'registration-nct' || $module == 'login-nct') {
            $replace = array(
                '%hidesignup%'   => ($module == 'registration-nct') ? 'hide' : null,
                '%hidesignin%'   => ($module == 'login-nct') ? 'hide' : null,
                '%lang_options%' => $this->getLanguageOptions(),
            );
            $header_section = get_view(DIR_TMPL . $this->module . "/before-login-header-section-no-search.tpl.php", $replace);
        } else {
            $header_section = get_view(DIR_TMPL . $this->module . "/before-login-header-section.tpl.php", array('%lang_options%' => $this->getLanguageOptions()));
        }
        $replace = array(
            '%header_section%' => $header_section,
            '%header_type%'    => ($module != 'home-nct') ? 'white_header_menu' : null,
            '%logo_type%'      => ($module != 'home-nct') ? '_blue' : null,
            '%search_type%'    => ($module != 'home-nct') ? (($module == 'registration-nct' || $module == 'login-nct') ? 'hide' : 'search-inner') : null,
            '%group_addon%'    => ($module != 'home-nct') ? 'inner-page' : null,
            '%dropdown_type%'  => ($module != 'home-nct') ? 'dropdown-inner' : null,
            '%searchbox_type%' => ($module != 'home-nct') ? 'inner-form-control' : null,
            '%isProvidersSelected%' => ($this->sessUserType == "c" || $this->sessUserType == null) ? 'selected' : null,
            '%isProjectsSelected%' => ($this->sessUserType == "p" ) ? 'selected' : null,
            '%headerSearchPlaceholder%' => ($this->sessUserType == "c" || $this->sessUserType == null) ? Find_a_freelancer : find_a_project,
            
        );

        return get_view(DIR_TMPL . "header-nct.tpl.php", $replace);
    }

    public function getFooterContent()
    {

        $pages     = $this->db->select("tbl_content", array('*'), array("isActive" => 'y'))->results();
        $menu_item = null;
        foreach ($pages as $page) {
            if(trim($page['pageSlug']) != null && trim($page['pageTitle_' . $_SESSION['lId']]) != null){
                $menu_item .= "<li><a href=" . SITE_URL . 'content/' . $page['pageSlug'] . ">" . $page['pageTitle_' . $_SESSION['lId']] . "</a></li>";
            }            
        }
		$menu_item .= '<li><a href="' . SITE_CONTACTUS . '">' . Contact_us . '</a></li>';
        $menu_item .= '<li><a href="' . SITE_URL . 'feedback">'.Feedback.'</a></li>';

        return get_view(DIR_TMPL . "footer-nct.tpl.php", array('%MENU_ITEMS%' => $menu_item));
    }

    public function getLanguageOptions($langId = 0)
    {
        $languages = $this->db->select("tbl_languages", '*', array("status" => 'a'), ' order by isDefault,languageName ')->results();
        $html      = null;
        $langId    = isset($_SESSION["lId"]) ? $_SESSION["lId"] : 0;
        //$this->fb->info(array($languages, $langId));
        foreach ($languages as $key => $values) {

            $replace = array(
                "%VALUE%"    => $values['id'],
                "%SELECTED%" => ($langId == $values['id']) ? 'selected' : null,
                "%LABEL%"    => $values['languageName'],
                "%TITLE%"    => $values['languageName'],
            );
            $html .= get_view(DIR_TMPL . $this->module . "/" . "option-nct.tpl.php", $replace);
        }
        return $html;
    }

    public function updateUserLang()
    {
        //dump_exit($this->reqData['userLanguage']);
        $_SESSION["lId"] = isset($this->reqData["userLanguage"]) ? $this->reqData["userLanguage"] : 0;
        if ($this->sessUserId > 0) {
            $this->db->update('tbl_users', array('langId' => $this->reqData["userLanguage"]), array('userId' => $this->sessUserId));
        }
    }

    public function setBanner($captionOnly = false)
    {
        $html   = null;
        $result = $this->db->select('tbl_home_images', '*', array('status' => 'a'))->result();
        if (!empty($result)) {
            if ($captionOnly) {
                return constant($result['image_caption']);
            }
            if (strtolower($result['filetype']) == 'image') {
                $html .= get_view(DIR_TMPL . $this->module . "/". 'image-nct.tpl.php',array("%BANNER_IMG%" => tim_thumb_banner_image($result['image_name'], 'slider',768, 1400)));
            } else {
                $html .= get_view(DIR_TMPL . $this->module . "/" . 'video-nct.tpl.php', array("%BANNER_IMG%" => SITE_UPD . 'slider/' . $result['image_name']));
            }
        } else {
            if ($captionOnly) {
                return 'Find the best freelancer';
            }
            $html .= get_view(DIR_TMPL . $this->module . "/" . 'image-nct.tpl.php', array("%BANNER_IMG%" => SITE_THUMB . "?src=" . SITE_IMG . 'banner1.jpg' . "&h=768&w=1400"));
        }

        return $html;
    }

    public function getPageContent()
    {
        $completed = getTableValue('tbl_projects', 'COUNT(id)', array('jobStatus' => 'completed'));
        $ongoing   = $this->db->pdoQuery('select count(id) as ongoing from tbl_projects where jobStatus IN ("progress","accepted","milestone_accepted")')->result();
        $ongoing   = $ongoing['ongoing'];
        $customers = getTableValue('tbl_users', 'COUNT(*)', array('userType' => 'c'));
        $providers = getTableValue('tbl_users', 'COUNT(*)', array('userType' => 'p'));

        $replace = array(
            "%TOP_SKILLS_SECTION%" => $this->get_top_skills(),
            "%FEATURED_PROJ%"      => $this->get_featured_projects(),
            "%image_or_video%"     => $this->setBanner(),
            "%banner_caption%"     => $this->setBanner(true),
            "%completed%"          => $completed,
            "%ongoing%"            => $ongoing,
            "%customers%"          => $customers,
            "%providers%"          => $providers,
            "%get_started_link%"   => ((int) $this->sessUserId < 1) ? SITE_LOGIN : (($this->sessUserType == "c") ? SITE_SEARCH_PROVIDERS : SITE_SEARCH),
        );

        return get_view(DIR_TMPL . $this->module . "/" . $this->module . ".tpl.php", $replace);
    }

    public function top_skills_rows($count = false)
    {
        global $db;
        $html   = null;
        $sql    = 'SELECT ts.image, ts.id, ts.`skillName_'.$_SESSION['lId'].'`,ts.`show_on_home`, GROUP_CONCAT( DISTINCT CONCAT(s.slug) SEPARATOR "," ) AS skills FROM tbl_top_skills AS ts LEFT JOIN tbl_top_skill_list AS tsl ON ts.`id` = tsl.`topSkill` left join tbl_skills as s on tsl.skill = s.id where ts.show_on_home="y" GROUP BY ts.`id` order by ts.`skillName_'.$_SESSION['lId'].'` LIMIT 8';
        $qrySel = $this->db->pdoQuery($sql);

        $total_rows = $qrySel->affectedRows();
        if ($count) {
            return $total_rows;
        }
        $qrySel = $qrySel->results();

        if ($total_rows > 0) {
            foreach ($qrySel as $fetchRes) {
                $replace = array(
                    "%HREF%"  => SITE_SEARCH_SKILLS_PROVIDERS . (($fetchRes['skills'] != "") ? strtolower($fetchRes['skills']) : null),
                    "%SRC%"   => tim_thumb_image($fetchRes['image'], "skill", 250, 284),
                    "%TITLE%" => ucwords($fetchRes['skillName_'.$_SESSION['lId']]),
                );
                $html .= get_view(DIR_TMPL . $this->module . "/" . "top_skills.tpl.php", $replace);
            }
        } else {
            $html = null;
        }

        return $html;
    }

    public function get_top_skills()
    {
        $html = null;
        if ($this->top_skills_rows(true) >= 4) {
            $replace = array('%TOP_SKILLS%' => $this->top_skills_rows());
            $html .= get_view(DIR_TMPL . $this->module . "/" . "top_skills_section.tpl.php", $replace);
        }

        return $html;
    }

    public function get_featured_projects()
    {
        global $db;
        $html  = $featured_section  = null;
        $query = "SELECT tbl_spent.spent,
		  p.*,
		  char_length(p.description) AS desc_length,
		  CONCAT_WS(' ', u.firstname, u.lastname) AS username,
		  CONCAT_WS('/', u.profilelink, p.slug) AS proj_link,
		  c.cateName_".$_SESSION["lId"]." ,c.slug
		FROM
		  tbl_projects AS p
		  LEFT JOIN tbl_users AS u
		    ON p.userid = u.userid
		  LEFT JOIN tbl_categories AS c
		    ON p.categoryid = c.id
		  LEFT OUTER JOIN
		    (SELECT
		      (SUM(ph.totalAmount) + SUM(ph.adminCommission)) AS spent,
		      ph.userId
		    FROM
		      tbl_payment_history AS ph
		    WHERE ph.paymenttype = 'featured'
		      OR ph.paymenttype = 'project payment'
		    GROUP BY ph.userId) AS tbl_spent
		    ON p.userId = tbl_spent.userId
		WHERE p.isfeatured = 'y'
		  AND p.isactive = 'y'
		  AND p.jobStatus = 'open'
		ORDER BY p.id DESC LIMIT 3";
        $qrySel = $this->db->pdoQuery($query)->results();
        if (!empty($qrySel)) {
            foreach ($qrySel as $k) {
                $exp_level = str_replace(array(
                    ' ',
                ), array(
                    '_',
                ), ucwords($k['experienceWanted']));

                $replace = array(
                    "%proj_link%"             => SITE_URL . $k['proj_link'],
                    "%proj_title%"            => ucfirst(strtolower($k['title'])),
                    "%link_to_cat%"           => SITE_SEARCH_CATEGORY_PROJECTS . strtolower($k['slug']),
                    "%proj_cat%"              => ucfirst(strtolower($k['cateName_'.$_SESSION["lId"]])),
                    "%experience_level_link%" => SITE_SEARCH_EXPERIENCE_PROVIDERS . urlencode(strtolower($k['experienceWanted'])),
                    "%experience_level%"      => constant($exp_level),
                    "%estimated_budget_link%" => SITE_SEARCH . '?budget_from=0&budget_to=' . $k['budget'],
                    "%estimated_budget%"      => CURRENCY_SYMBOL . $k['budget'],
                    "%proj_description%"      => String_crop($k['description'], 200),
                    "%hide_more%"             => ($k['desc_length'] < 230) ? 'hide' : '',
                    "%client_spent_amount%"   => CURRENCY_SYMBOL . (!empty($k['spent']) ? $k['spent'] : '0.00'),
                    "%created_date%"          => date(PHP_DATE_FORMAT, strtotime($k['createdDate'])),
                );
                $html .= get_view(DIR_TMPL . $this->module . "/" . "featured_projects.tpl.php", $replace);
            }

            $featured_section .= get_view(DIR_TMPL . $this->module . "/" . "featured_projects_section.tpl.php", array('%FEATURED_PROJ_ROWS%' => $html));
        }

        return $featured_section;
    }

}
