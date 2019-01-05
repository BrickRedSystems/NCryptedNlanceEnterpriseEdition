<!DOCTYPE html>

<html lang="%LANGCODE%">
    <head>
		%HEAD%
	</head>
    <body class="%LANGCLASS%">
        <div class="page-wrap main-gray-bg">
          	%SITE_HEADER%
            %BODY%
        </div>
        %FOOTER%
            
        <!--<script type="text/javascript" src="{SITE_ADM_PLUGIN}bootstrap-toastr/toastr.min.js"></script>-->
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.3/toastr.min.js"></script>
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
        /*$(function(){
        toastr['error']('Hello test', 'title');
        });*/
        </script>
        %MESSAGE_TYPE%
    </body>
</html>

