<?php

class SiteSetting extends Home
{

    function __construct()
    {
        parent::__construct();
        $this->table = 'tbl_site_settings';
    }

    public function _index()
    {
        $content = '';

        $sqlSetting = $this->db->select($this->table, "*")->results();
        foreach ($sqlSetting as $k => $setrow)
        {
            $required  = '';
            $mend_sign = '';

            if ($setrow["type"] == "filebox" && $setrow["value"] == "")
            {
                $required  = "required ";
                $mend_sign = MEND_SIGN;
            }
            if ($setrow["type"] == "filebox" && !empty($setrow["value"]))
            {
                $setrow["value"] = "<br/>" . $this->img(array(
                    "onlyField" => true,
                    "src" => "" . SITE_IMG . $setrow["value"] . "",
                    "width" => "" . (($setrow["constant"] == "SITE_FAVICON") ? "20px" : "200px") . ""
                ));
            }
            if ($setrow["type"] == "radio")
            {
                $mend_sign = MEND_SIGN;
            }
            else if ($setrow["type"] == "selectBox")
            {
                $mend_sign = MEND_SIGN;
                $content .= $this->selectBox(array(
                    "label" => $mend_sign . $setrow["label"] . ":",
                    "onlyField" => false,
                    "allow_null" => true,
                    "allow_null_value" => "",
                    "class" => "required",
                    "name" => $setrow["id"],
                    "choices" => array(
                        0 => "Select Location"
                    ),
                    "value" => $setrow["value"],
                    "defaultValue" => true,
                    "multiple" => false,
                    "optgroup" => false,
                    "intoDB" => array(
                        "val" => true,
                        "table" => "tbl_locations",
                        "fields" => "*",
                        "where" => "status='1'",
                        "orderBy" => "location_name",
                        "valField" => "id",
                        "dispField" => "location_name"
                    )
                ));
            }
            else
            {

                if ($setrow["required"] == 1)
                {
                    $required  = "required ";
                    $mend_sign = MEND_SIGN;
                }

                $content .= $this->{$setrow["type"]}(array(
                    "label" => $mend_sign . $setrow["label"] . ":",
                    "value" => $setrow["value"],
                    "class" => $required . $setrow["class"],
                    "name" => $setrow["id"]
                ));
            }

            if (!empty($setrow['hint']))
            {
                //$content.=$this->displayBox(array("label"=>"&nbsp;","value"=>$setrow['hint'],"class"=>"hint"));
            }
        }
        $content .= $this->buttonpanel_start() . $this->button(array(
            "onlyField" => true,
            "name" => "submitSetForm",
            "type" => "submit",
            "class" => "green",
            "value" => "Submit",
            "extraAtt" => ""
        )) . $this->button(array(
            "onlyField" => true,
            "name" => "cn",
            "type" => "button",
            "class" => "btn-toggler",
            "value" => "Cancel",
            "extraAtt" => "onclick=\"location.href='" . SITE_ADM_MOD . "home-nct/'\""
        ));

        $content .= $this->buttonpanel_end();
        return $content;
    }

    public function button($btn)
    {
        $btn['value']     = isset($btn['value']) ? $btn['value'] : '';
        $btn['name']      = isset($btn['name']) ? $btn['name'] : '';
        $btn['class']     = isset($btn['class']) ? 'btn ' . $btn['class'] : 'btn';
        $btn['type']      = isset($btn['type']) ? $btn['type'] : '';
        $btn['src']       = isset($btn['src']) ? $btn['src'] : '';
        $btn['extraAtt']  = isset($btn['extraAtt']) ? ' ' . $btn['extraAtt'] : '';
        $btn['onlyField'] = isset($btn['onlyField']) ? $btn['onlyField'] : false;
        $btn["src"]       = ($btn["type"] == "image" && $btn["src"] != '') ? $btn["src"] : '';

        $main_content_only_field     = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/button_onlyfield.tpl.php");
        $main_content_only_field     = $main_content_only_field->parse();
        $fields                      = array(
            "%TYPE%",
            "%NAME%",
            "%CLASS%",
            "%ID%",
            "%SRC%",
            "%EXTRA%",
            "%VALUE%"
        );
        $fields_replace              = array(
            $btn["type"],
            $btn["name"],
            $btn["class"],
            $btn["name"],
            $btn["src"],
            $btn['extraAtt'],
            $btn["value"]
        );
        $sub_final_result_only_field = str_replace($fields, $fields_replace, $main_content_only_field);

        if ($btn['onlyField'] == true)
        {
            return $sub_final_result_only_field;
        }
        else
        {
            $main_content   = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/button.tpl.php");
            $main_content   = $main_content->parse();
            $fields         = array(
                "%BUTTON%"
            );
            $fields_replace = array(
                $sub_final_result_only_field
            );
            return str_replace($fields, $fields_replace, $main_content);
        }
    }

    public function img($text)
    {
        $text['href']      = isset($text['href']) ? $text['href'] : '';
        $text['src']       = isset($text['src']) ? $text['src'] : 'Enter Image Path Here: ';
        $text['name']      = isset($text['name']) ? $text['name'] : '';
        $text['id']        = isset($text['id']) ? $text['id'] : '';
        $text['class']     = isset($text['class']) ? '' . trim($text['class']) : '';
        $text['height']    = isset($text['height']) ? '' . trim($text['height']) : '';
        $text['width']     = isset($text['width']) ? '' . trim($text['width']) : '';
        $text['extraAtt']  = isset($text['extraAtt']) ? $text['extraAtt'] : '';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : '';

        if ($text['onlyField'] == true)
        {
            $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/img_onlyfield.tpl.php");
            $main_content = $main_content->parse();
        }
        else
        {
            $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/img.tpl.php");
            $main_content = $main_content->parse();
        }
        $fields         = array(
            "%HREF%",
            "%SRC%",
            "%CLASS%",
            "%ID%",
            "%ALT%",
            "%WIDTH%",
            "%HEIGHT%",
            "%EXTRA%"
        );
        $fields_replace = array(
            $text['href'],
            $text['src'],
            $text['class'],
            $text['id'],
            $text['name'],
            $text['width'],
            $text['height'],
            $text['extraAtt']
        );
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function buttonpanel_start()
    {
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/buttonpanel_start.tpl.php");
        $main_content = $main_content->parse();

        return $main_content;
    }

    public function buttonpanel_end()
    {
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/buttonpanel_end.tpl.php");
        $main_content = $main_content->parse();
        return $main_content;
    }

    public function form_start($text)
    {
        $text['action']   = isset($text['action']) ? $text['action'] : '';
        $text['method']   = isset($text['method']) ? $text['method'] : 'post';
        $text['name']     = isset($text['name']) ? $text['name'] : '';
        $text['id']       = isset($text['id']) ? $text['id'] : '';
        $text['class']    = isset($text['class']) ? '' . trim($text['class']) : 'form-horizontal';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        $main_content   = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form_start.tpl.php");
        $main_content   = $main_content->parse();
        $fields         = array(
            "%ACTION%",
            "%METHOD%",
            "%NAME%",
            "%ID%",
            "%CLASS%",
            "%EXTRA%"
        );
        $fields_replace = array(
            $text['action'],
            $text['method'],
            $text['name'],
            $text['name'],
            $text['class'],
            $text['extraAtt']
        );
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function form_end()
    {
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/form_end.tpl.php");
        $main_content = $main_content->parse();
        return $main_content;
    }

    public function displayBox($text)
    {

        $text['label']     = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
        $text['value']     = isset($text['value']) ? $text['value'] : '';
        $text['name']      = isset($text['name']) ? $text['name'] : '';
        $text['class']     = isset($text['class']) ? 'form-control-static ' . trim($text['class']) : 'form-control-static';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
        $text['extraAtt']  = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        $main_content   = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/displaybox.tpl.php");
        $main_content   = $main_content->parse();
        $fields         = array(
            "%LABEL%",
            "%CLASS%",
            "%VALUE%"
        );
        $fields_replace = array(
            $text['label'],
            $text['class'],
            $text['value']
        );
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function textbox($text)
    {

        $text['label']    = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
        $text['value']    = isset($text['value']) ? $text['value'] : '';
        $text['name']     = isset($text['name']) ? $text['name'] : '';
        $text['class']    = isset($text['class']) ? 'form-control ' . trim($text['class']) : 'form-control';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        $content      = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/textbox-nct.tpl.php");
        $main_content = $main_content->parse();

        $fields         = array(
            "%CLASS%",
            "%NAME%",
            "%ID%",
            "%VALUE%",
            "%EXTRA%",
            "%LABEL%"
        );
        $fields_replace = array(
            $text['class'],
            $text['name'],
            $text['name'],
            $text['value'],
            $text['extraAtt'],
            $text['label']
        );
        $content        = str_replace($fields, $fields_replace, $main_content);
        return $content;
    }

    public function password($text)
    {
        $text['label']     = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
        $text['value']     = isset($text['value']) ? $text['value'] : '';
        $text['name']      = isset($text['name']) ? $text['name'] : '';
        $text['class']     = isset($text['class']) ? 'form-control ' . trim($text['class']) : 'form-control';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;
        $text['extraAtt']  = isset($text['extraAtt']) ? $text['extraAtt'] : '';

        if ($text["onlyField"] == true)
        {
            $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/password_onlyfield.tpl.php');
        }
        else
        {
            $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/password.tpl.php');
        }
        $main_content   = $main_content->parse();
        $fields         = array(
            "%CLASS%",
            "%NAME%",
            "%ID%",
            "%VALUE%",
            "%EXTRA%",
            "%LABEL%"
        );
        $fields_replace = array(
            $text['class'],
            $text['name'],
            $text['name'],
            $text['value'],
            $text['extraAtt'],
            $text['label']
        );
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function filebox($text)
    {

        $text['label']    = isset($text['label']) ? $text['label'] : 'Enter Text Here: ';
        $text['value']    = isset($text['value']) ? $text['value'] : '';
        $text['name']     = isset($text['name']) ? $text['name'] : '';
        $text['class']    = isset($text['class']) ? 'form-control ' . trim($text['class']) : 'form-control';
        $text['extraAtt'] = isset($text['extraAtt']) ? $text['extraAtt'] : '';
        $text["help"]     = isset($text["help"]) ? $text["help"] : "";
        $text["helptext"] = ($text["help"] != "") ? '<p class="help-block">' . $text["help"] . '</p>' : "";

        $content      = NULL;
        $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/filebox-nct.tpl.php");
        $main_content = $main_content->parse();

        $fields         = array(
            "%CLASS%",
            "%NAME%",
            "%ID%",
            "%VALUE%",
            "%EXTRA%",
            "%LABEL%",
            "%HELPTEXT%"
        );
        $fields_replace = array(
            $text['class'],
            $text['name'],
            $text['name'],
            $text['value'],
            $text['extraAtt'],
            $text['label'],
            $text["helptext"]
        );

        $content = str_replace($fields, $fields_replace, $main_content);
        return $content;
    }

    public function textArea($text)
    {
        $text['label']     = isset($text['label']) ? $text['label'] : 'Enter Password Here: ';
        $text['value']     = isset($text['value']) ? $text['value'] : '';
        $text['name']      = isset($text['name']) ? $text['name'] : '';
        $text['class']     = isset($text['class']) ? "form-control " . $text['class'] : 'form-control';
        $text['extraAtt']  = isset($text['extraAtt']) ? ' ' . $text['extraAtt'] : '';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;

        if ($text["onlyField"] == true)
        {
            $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/textarea_onlyfield.tpl.php');
        }
        else
        {
            $main_content = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/textarea.tpl.php');
        }
        $main_content   = $main_content->parse();
        $fields         = array(
            "%CLASS%",
            "%NAME%",
            "%ID%",
            "%VALUE%",
            "%EXTRA%",
            "%LABEL%"
        );
        $fields_replace = array(
            $text['class'],
            $text['name'],
            $text['name'],
            $text['value'],
            $text['extraAtt'],
            $text['label']
        );
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function textAreaEditor($text)
    {
        $text['label']     = isset($text['label']) ? $text['label'] : 'Enter Password Here: ';
        $text['value']     = isset($text['value']) ? $text['value'] : '';
        $text['name']      = isset($text['name']) ? $text['name'] : '';
        $text['class']     = isset($text['class']) ? 'ckeditor form-control ' . $text['class'] : 'ckeditor form-control';
        $text['extraAtt']  = isset($text['extraAtt']) ? ' ' . $text['extraAtt'] : '';
        $text['onlyField'] = isset($text['onlyField']) ? $text['onlyField'] : false;

        $main_content   = new MainTemplater(DIR_ADMIN_TMPL . $this->module . '/textarea_editor.tpl.php');
        $main_content   = $main_content->parse();
        $fields         = array(
            "%CLASS%",
            "%NAME%",
            "%ID%",
            "%VALUE%",
            "%EXTRA%",
            "%LABEL%"
        );
        $fields_replace = array(
            $text['class'],
            $text['name'],
            $text['name'],
            htmlentities($text['value']),
            $text['extraAtt'],
            $text['label']
        );
        return str_replace($fields, $fields_replace, $main_content);
    }

    public function getPageContent()
    {
        $final_result = NULL;

        $main_content             = new MainTemplater(DIR_ADMIN_TMPL . $this->module . "/" . $this->module . ".tpl.php");
        $main_content->breadcrumb = $this->getBreadcrumb();
        $main_content->getForm    = $this->_index();

        $final_result = $main_content->parse();
        return $final_result;
    }

}

?>