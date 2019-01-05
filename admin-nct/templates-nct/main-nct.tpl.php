<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <?php print $this->head; ?> 
    </head>
    <?php $class = ($this->module == "login-nct") ? "login" : "page-header-fixed"; ?>
    <body class="<?php echo $class; ?>">
        <?php print $this->site_header; ?>
        <?php
        if ($this->adminUserId > 0) {
            echo '<div class="page-container">';
        }
        ?>
        <?php print $this->left; ?>
        <div class="page-content-wrapper">
            <?php
            if ($this->adminUserId > 0) {
                echo '<div class="page-content">';
            }
            ?>
            <?php print $this->body; ?>
            <?php
            if ($this->adminUserId > 0) {
                echo '</div>';
            }
            ?>
        </div>
        <?php print $this->right; ?>
        <?php
        if ($this->adminUserId > 0) {
            echo '</div>';
        }
        ?>
        <?php print $this->footer; ?>

        <!-- new coding added  start-->	
        <!--[if lt IE 9]>
<script src="<?php echo SITE_ADM_PLUGIN; ?>respond.min.js"></script>
<script src="<?php echo SITE_ADM_PLUGIN; ?>excanvas.min.js"></script> 
<![endif]-->
        <!--Main table End-->
        <!-- <script src="<?php echo SITE_ADM_PLUGIN; ?>flot/jquery.min.js" type="text/javascript"></script>-->
        <script src="<?php echo SITE_ADM_PLUGIN; ?>jquery-migrate-1.2.1.min.js" type="text/javascript"></script>
        <!-- IMPORTANT! Load jquery-ui-1.10.3.custom.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
        <script src="<?php echo SITE_ADM_PLUGIN; ?>jquery-ui/jquery-ui-1.10.3.custom.min.js" type="text/javascript"></script>
        <script src="<?php echo SITE_ADM_PLUGIN; ?>bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="<?php echo SITE_ADM_PLUGIN; ?>bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js" type="text/javascript"></script>
        <script src="<?php echo SITE_ADM_PLUGIN; ?>jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
        <script src="<?php echo SITE_ADM_PLUGIN; ?>jquery.blockui.min.js" type="text/javascript"></script>
        <script src="<?php echo SITE_ADM_PLUGIN; ?>jquery.cokie.min.js" type="text/javascript"></script>
        <script src="<?php echo SITE_ADM_PLUGIN; ?>uniform/jquery.uniform.min.js" type="text/javascript"></script>
        <script src="<?php echo SITE_ADM_PLUGIN; ?>jquery-validation/dist/jquery.validate.js" type="text/javascript"></script>
        
        <script type="text/javascript" src="<?php echo SITE_ADM_PLUGIN; ?>bootstrap-datepicker/js/bootstrap-datepicker.js"></script>

        <script type="text/javascript" src="<?php echo SITE_ADM_PLUGIN; ?>jquery-validation/dist/additional-methods.min.js"></script>
        <script type="text/javascript" src="<?php echo SITE_ADM_PLUGIN; ?>select2/select2.min.js"></script>
        <script type="text/javascript" src="<?php echo SITE_ADM_PLUGIN; ?>ckeditor/ckeditor.js"></script>
        <!-- <script type="text/javascript" src="<?php echo SITE_ADM_PLUGIN; ?>bootstrap-toastr/toastr.min.js"></script> -->
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.js"></script>
		<script type="text/javascript" src="<?php echo SITE_JS; ?>cropper.min.js"></script>
		
        <?php if($this->module == 'home-nct') { ?>
            <script type="text/javascript" src="<?php echo SITE_ADM_PLUGIN; ?>flot/jquery.flot.min.js"></script>
            <script type="text/javascript" src="<?php echo SITE_ADM_PLUGIN; ?>flot/jquery.flot.resize.min.js"></script>
            <script type="text/javascript" src="<?php echo SITE_ADM_PLUGIN; ?>flot/jquery.flot.pie.min.js"></script>
            <script type="text/javascript" src="<?php echo SITE_ADM_PLUGIN; ?>flot/jquery.flot.stack.min.js"></script>
            <script type="text/javascript" src="<?php echo SITE_ADM_PLUGIN; ?>flot/jquery.flot.crosshair.min.js"></script>
            <script type="text/javascript" src="<?php echo SITE_ADM_PLUGIN; ?>flot/jquery.flot.categories.min.js"></script>
            <script type="text/javascript" src="<?php echo SITE_ADM_PLUGIN; ?>flot/jquery.flot.time.min.js"></script>
            
            <script type="text/javascript" src="<?php echo SITE_ADM_PLUGIN; ?>flot/jquery.flot.grow.js"></script>
            
            <script type="text/javascript" src="<?php echo SITE_ADM_PLUGIN; ?>flot/plugins/jquery_flot_animator/jquery.flot.animator.min.js"></script>
            
            <script type="text/javascript" src="<?php echo SITE_ADM_JS; ?>custom/charts.js"></script>
        <?php } ?>

        <script type="text/javascript">
            /*toastr.options = {
                "closeButton": true,
                "debug": false,
                "positionClass": "toast-top-full-width",
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            } */           

        </script>
        <script type="text/javascript">
            toastr.options = {
              "closeButton": true,
              "debug": false,
              "newestOnTop": true,
              "progressBar": false,
              "positionClass": "toast-top-right",
              "preventDuplicates": true,
              "onclick": null,
              "showDuration": "300",
              "hideDuration": "1000",
              "timeOut": "5000",
              "extendedTimeOut": "1000",
              "showEasing": "swing",
              "hideEasing": "linear",
              "showMethod": "fadeIn",
              "hideMethod": "fadeOut"
            }
        </script>

        <!-- BEGIN PAGE LEVEL SCRIPTS -->
        <script src="<?php echo SITE_ADM_JS; ?>core/app.js" type="text/javascript"></script>
        <script src="<?php echo SITE_ADM_JS; ?>custom/components-pickers.js" type="text/javascript"></script>
        <script src="<?php echo SITE_ADM_JS; ?>core/admin.js" type="text/javascript"></script>

        <script type="text/javascript">
            jQuery(document).ready(function () {
                <?php echo $this->toastr_message; ?>
                App.init();
                ComponentsPickers.init();
            });
            
            $(document).on('click', '.btn-delete', function (e) {
                e.preventDefault();
                /*toastr.options.tapToDismiss = false;
                toastr.options.timeOut = 50000;
                toastr.options.extendedTimeOut = 0;
                toastr.options.positionClass = "toast-top-right";
                toastr["warning"]('Deleting record can not be undone. Are you sure?<br /><button type="button" class="btn clear">Yes</button> <button type="button" class="btn clear">No</button>')*/
                if (confirm("Deleting record can not be undone. Are you sure?")) {
                    var $this = $(this);
                    var editLink = $this.attr('href');

                    $.ajax({
                        url: editLink,
                        type: "POST",
                        dataType: "json",
                        success: function (response) {
                            //console.log(response);
                            if ('' != response.type && '' != response.message) {
                                toastr[response.type](response.message);
                                if ('success' == response.type) {
                                    OTable.fnDraw();
                                }
                            } else {
                                toastr['error']('There seems to be an issue. Please try again after some time.');
                            }
                        }
                    });

                }
            });
            $(document).on('click', '.btn-send', function (e) {
                e.preventDefault();
                if (confirm("Are you sure to send newsletter?")) {
                    var $this = $(this);
                    var editLink = $this.attr('href');
                    $.get(editLink, function (r) {
                        OTable.fnDraw();
                        toastr['success']('<?php echo disMessage(array('var' => 'newssendsuccess'), false); ?>');
                    });
                }
            });
            $(document).on('click', '.send', function (e) {
                e.preventDefault();
                if (confirm("Are you sure to send password to this dealer?")) {
                    var $this = $(this);
                    var editLink = $this.attr('href');
                    $.get(editLink, function (r) {
                        OTable.fnDraw();
                        toastr['success']('<?php echo disMessage(array('var' => 'Password sent successfully.'), false); ?>');
                    });
                }
            });

            $(document).on('click', '.btn-viewbtn', function (e) {
                e.preventDefault();
                var $this = $(this);
                var viewLink = $this.attr('href');
                var PageTitle = $this.attr('data-page_title');
                PageTitle = (PageTitle != null) ? PageTitle : 'View details';
                $(".modal-title").html(PageTitle);
                $(".modal-body").html('<div class="popup-loader"><img src="<?php echo SITE_ADM_IMG; ?>ajax-loading.gif" align="middle" /></div>');
                $("#myModal_autocomplete").modal();
                $.get(viewLink, function (r) {
                    $(".modal-body").html(r);
                });
            });

            function addOverlay() {
                $('<div id="overlayDocument"><img src="<?php echo SITE_ADM_IMG; ?>ajax-modal-loading.gif" /></div>').appendTo(document.body)
            }
            function removeOverlay() {
                $('#overlayDocument').remove();
            }
            function loadCKE(id) {
                var instance = CKEDITOR.instances[id];
                if (instance) {
                    CKEDITOR.remove(instance);
                }
                CKEDITOR.replace(id,{
                    filebrowserUploadUrl: '<?php echo SITE_URL;?>includes-nct/upload.php'
                });
            }

            
            $(document).ready(function () {
                $(".date-picker").datepicker({
                    autoclose: true,
                    format: "yyyy-mm-dd"
                });

                 $(document).on('keydown', '.checkFloat', function (e) { 
                    // Allow: backspace, delete, tab, escape, enter and .
                    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
                         // Allow: Ctrl+A, Command+A
                        (e.keyCode == 65 && ( e.ctrlKey === true || e.metaKey === true ) ) || 
                         // Allow: home, end, left, right, down, up
                        (e.keyCode >= 35 && e.keyCode <= 40)) {
                             // let it happen, don't do anything
                             return;
                    }
                    // Ensure that it is a number and stop the keypress
                    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                        e.preventDefault();
                    }
                });

                $(document).on('keydown', '.checkNumber', function (e) { 
                    // Allow: backspace, delete, tab, escape, enter and .
                    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13]) !== -1 ||
                         // Allow: Ctrl+A, Command+A
                        (e.keyCode == 65 && ( e.ctrlKey === true || e.metaKey === true ) ) || 
                         // Allow: home, end, left, right, down, up
                        (e.keyCode >= 35 && e.keyCode <= 40)) {
                             // let it happen, don't do anything
                             return;
                    }
                    // Ensure that it is a number and stop the keypress
                    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                        e.preventDefault();
                    }
                });
                
            });
            
            function ajaxFormSubmit(form_element, toggle_portlet_toggler = true) {
                
                $(form_element).ajaxForm({
                    beforeSend: function () {
                        addOverlay();
                    },
                    uploadProgress: function (event, position, total, percentComplete) {

                    },
                    success: function (html, statusText, xhr, $form) {
                        obj = $.parseJSON(html);
                        
                        if (obj.status) {
                            toastr["success"](obj.success);
                            
                            if(toggle_portlet_toggler) {
                                $('.portlet-toggler').toggle();
                                OTable.fnDraw();
                            }                            
                            return false;
                        } else {
                            toastr["error"](obj.error);
                            return false;
                        }                        
                        return false;
                    },
                    complete: function (xhr) {
                        removeOverlay();
                        return false;
                    }
                }).submit();
                
            }
            
            function initilizeRaty(control_name, score) {
                $(control_name).raty({
                    scoreName: 'star_ratings',
                    score: score,
                    readOnly: true,
                    half: true,
                    starHalf: '<?php echo SITE_PLUGIN ?>raty/images/star-half.png',
                    starOff: '<?php echo SITE_PLUGIN ?>raty/images/star-off.png',
                    starOn: '<?php echo SITE_PLUGIN ?>raty/images/star-on.png',
                    hints: ['Bad', 'Poor', 'Regular', 'Good', 'Excellent']
                });
            }

        </script>
        <?php echo load_js($this->scripts); ?>
        <!-- END PAGE LEVEL SCRIPTS -->
        <!-- new coding added  start-->	
    </body>
</html>