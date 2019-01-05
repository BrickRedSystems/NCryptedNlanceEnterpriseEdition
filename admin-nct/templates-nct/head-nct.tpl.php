<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta content="width=device-width, initial-scale=1" name="viewport"/>
<?php echo $this->metaTag; ?>
<title><?php echo $this->title; ?></title>
<!-- BEGIN GLOBAL MANDATORY STYLES -->
<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
<link href="<?php echo SITE_ADM_PLUGIN; ?>font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo SITE_ADM_PLUGIN; ?>bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo SITE_ADM_PLUGIN; ?>uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
<!-- END GLOBAL MANDATORY STYLES -->
<!-- BEGIN THEME STYLES -->
<link href="<?php echo SITE_ADM_CSS; ?>style-metronic.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo SITE_ADM_CSS; ?>style.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo SITE_ADM_CSS; ?>style-responsive.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo SITE_ADM_CSS; ?>plugins.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo SITE_ADM_CSS; ?>pages/tasks.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo SITE_ADM_CSS; ?>themes/blue.css" rel="stylesheet" type="text/css" id="style_color"/>
<link href="<?php echo SITE_ADM_CSS; ?>print.css" rel="stylesheet" type="text/css" media="print"/>
<link href="<?php echo SITE_ADM_CSS; ?>custom.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo SITE_CSS; ?>cropper.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo SITE_ADM_PLUGIN; ?>bootstrap-toastr/toastr.min.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="<?php echo SITE_ADM_PLUGIN; ?>bootstrap-datepicker/css/datepicker.css"/>

<link href="<?php echo SITE_PLUGIN; ?>raty/jquery.raty.css" rel="stylesheet" />

<!-- END THEME STYLES -->
<link rel="shortcut icon" type="image/ico" href="{SITE_IMG}{SITE_FAVICON}">

<?php echo load_css($this->styles); ?>
<script src="<?php echo SITE_ADM_PLUGIN; ?>jquery-1.10.2.min.js" type="text/javascript"></script>
<script src="<?php echo SITE_ADM_PLUGIN; ?>bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
<script src="<?php echo SITE_PLUGIN; ?>jQuery-form/js/jquery.form.js"></script>
<script src="<?php print SITE_JS; ?>jquery.numeric.js" type="text/javascript" charset="utf-8"></script>

<script language="javascript" type="text/javascript">
    var SITE_URL = '<?php echo SITE_URL; ?>';
    var SITE_ADM_URL = '<?php echo SITE_ADMIN_URL; ?>';
    var SITE_ADM_MOD_URL = '<?php echo SITE_ADM_MOD; ?>';

    var SITE_ADM_URL_USERS = SITE_ADM_MOD_URL + 'users-nct/';

    var siteName = '<?php echo SITE_URL; ?>';
    var SITE_ADM_IMG = '<?php echo SITE_ADM_IMG; ?>';
    $(function () {
        var mBar = $('.page-sidebar-menu').find('li.sm-<?php echo $this->module; ?>');
        mBar.addClass('active');
        mBar.parents('ul.sub-menu').parent('li').addClass('active');
    });

    var CURRENCY_SYMBOL = '<?php echo CURRENCY_SYMBOL; ?>';
    var CURRENCY_CODE = '<?php echo PAYPAL_CURRENCY_CODE; ?>';
    var MONTH_NAMES
            = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    var MONTH_NAMES_SHORT
            = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
</script>
<?php //echo GOOGLE_ANA_CODE_COM; ?>
