<?php
	require_once('install_config.php');


	define('SITENAME', $_SERVER['SERVER_NAME']);
	$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	if(INSTALL_TYPE == 'local'){
		$dirurl = $_SERVER["DOCUMENT_ROOT"] . '/nlance_web/';
		$siteurl = $protocol.SITENAME.'/nlance_web/';
		$rootfile = $dirurl.'demo.txt';
		if(file_exists($rootfile)){
			header('Location: '.$siteurl);
			exit;
		}
	}else{

		$dirurl = $_SERVER["DOCUMENT_ROOT"] . '/';
		$rootfile = $dirurl.'demo.txt';
		$siteurl = $protocol.SITENAME.'/';
		if(file_exists($rootfile)){
			header('Location: '.$siteurl);
			exit;
		}
	}
?>
<html lang="en">
	<head>
		<title>Nlance - Installation Details</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="shortcut icon" type="image/ico" href="<?php echo $siteurl; ?>install-nct/fav.ICO" />

		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
		<style>
		.vl {
		border-left: 3px solid #2b8bce;
		border-right: 3px solid #2b8bce;

		padding-left: 10px;
		padding-right: 10px;
		padding-top: 5px;
		padding-bottom: 5px;
		background-color: #7aaace;
		border-radius: 14px;
		color: #fff;
		}
		.spannote{
		font-size: small;
		}
		.verifyresponsedata{
		background-color: #3080bb;
		padding: 10px 10px 10px 10px;
		margin: 10px;
		border-radius: 12px;
		}
		.lds-hourglass {
		  display: inline-block;
		  /*position: relative;*/
		}
		.lds-hourglass:after {
		  /* with no content, nothing is rendered */
			content: "";
			position: fixed;
			/* element stretched to cover during rotation an aspect ratio up to 1/10 */
			top: -500%;
			left: -500%;
			right: -500%;
			bottom: -500%;
			z-index: 9999;
			pointer-events: all; /* to block content use: all */
			/* background */
			background-image: url('install-nct/loader.gif');
			background-color: rgba(0,0,0,0.1);
			background-repeat: no-repeat;
			background-position: center center;
			/*background-size: 100px 100px;*/
			display: block;
		}

		.paddingbtm10{
			padding-bottom: 10px;
		}
		.bold-red{
			font-weight: bold;
			color: red;
		}

		</style>
	</head>
	<body>
		<div class="loader">
		</div>
		<div class="container">
			<div class="row" style="padding-top: 10px;">
				<div class="logo">
					<a href="<?php echo $siteurl; ?>"><img src="<?php echo $siteurl; ?>install-nct/logo.png" alt="NCrypted Technologies" title="NCrypted Technologies" /></a>
				</div>
			</div>
			<div class="col-md-6">
				<div class="row">&nbsp;</div>
				<h3 style="color: #7aaace;">Nlance - Installation</h3>
				<form action="install-nct/install_db.php" id="install-form" method="POST">
					<div class="row">
						<div class="form-group col-md-8">
							<span class="spannote">Please make sure you have VERIFIED the Prerequisites using the 'Click to Verify' button in the right.</span>
						</div>
					</div>
					<div class="row">
						<div class="form-group col-md-8">
							<label for="site_url">Site URL:</label>
							<input type="site_url" class="form-control" id="site_url" name="site_url" placeholder="ex. mydomain.com">
						</div>
					</div>
					<div class="row" hidden id="dir_name_row">
						<div class="form-group col-md-8">
							<label for="dir_name">Directory Name:</label>
							<input type="dir_name" class="form-control" id="dir_name" name="dir_name" placeholder="Enter the folder name where the code is extracted">
						</div>
					</div>
					<div class="row">
						<div class="form-group col-md-8">
							<label for="db_host">Database Hostname:</label>
							<input type="db_host" readonly="" class="form-control" id="db_host" name="db_host" placeholder="ex. localhost">
						</div>
					</div>
					<div class="row">
						<div class="form-group col-md-8">
							<label for="db_name">Database Name:</label>
							<input type="db_name" readonly="" class="form-control" id="db_name" name="db_name" placeholder="ex. nlance_web (create the database first)">
						</div>
					</div>
					<div class="row">
						<div class="form-group col-md-8">
							<label for="db_username">Database Username:</label>
							<input type="db_username" readonly="" class="form-control" id="db_username" name="db_username" placeholder="ex. root">
						</div>
					</div>
					<div class="row">
						<div class="form-group col-md-8">
							<label for="db_pass">Database Password:</label>
							<input type="password" readonly="" class="form-control" id="db_pass" name="db_pass" placeholder="Please leave it blank if NO PASSWORD set for Database">
						</div>
					</div>
					<div class="row">
						<div class="form-group checkbox col-md-8">
							<label><input type="checkbox" id="accept"><span class="spannote"> I accept the license agreement for NCrypted Technologies with this install</span></label>
							<span class="bold-red chk-error hide">Please accept license agreement.</span>
						</div>
					</div>
					<div class="row">
						<div class="form-group col-md-4">
							<button type="submit" class="btn btn-info" id="install" disabled>Install</button>
						</div>
						<div class="form-group col-md-4">
							<button type="button" class="btn btn-default">Cancel</button>
						</div>
					</div>
				</form>
			</div>
			<div class="col-md-6">
				<div class="vl">
					<h3>Prerequisites</h3>
						<h5 class="bold-red">
							<blink>Please create DATABASE and USER first from MySql.</blink>
						</h5>
					<div class="paddingbtm10 row">

						<div class=" col-md-6">
							<label for="db_host">Database Hostname:</label>
							<input type="db_host" class="form-control db_content" data-desc = "db_host" id="db_host_chk" name="db_host" placeholder="ex. localhost">
						</div>

						<div class=" col-md-6">
							<label for="db_name">Database Name:</label>
							<input type="db_name" class="form-control db_content" data-desc = "db_name" id="db_name_chk" name="db_name" placeholder="create database first from MySql">
						</div>

						<div class=" col-md-6">
							<label for="db_username">Database Username:</label>
							<input type="db_username" class="form-control db_content" data-desc = "db_username" id="db_username_chk" name="db_username" placeholder="ex. root">
						</div>

						<div class=" col-md-6">
							<label for="db_pass">Database Password:</label>
							<input type="password" class="form-control db_content" data-desc = "db_pass" id="db_pass_chk" name="db_pass" placeholder="Password blank if NO Password">
						</div>

					</div>
					<span class="spannote">Make sure you have the below requirements fulfilled</span>

					<div class="row">

						<ul>
							<li>Apache 2 and above</li>
							<li>PHP version 5.6 - 7</li>
							<li>MySQL 5.0</li>
							<li>Increased execution time (\php\php.ini): max_execution_time = 180 OR 0;</li>
							<li>Turned URL FOPEN ON (\php\php.ini): allow_url_fopen = On; </li>
							<br><button class="btn btn-danger verify">Click to Verify</button>
						</ul>
					</div>
					<div class="row verifyresponse">
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
<script>

function blink_text() {
    $('.bold-red').find('blink').fadeOut(500);
    $('.bold-red').find('blink').fadeIn(500);
}
setInterval(blink_text, 2000);

$("#site_url").on("blur", function(){
	if($("#site_url").val()){
		var input = $("#site_url");
		input.val(
			$("#site_url").val().replace(/https?:\/\//gi,'')
		);
		if(input.val() == 'localhost' || input.val() == 'demo.ncryptedprojects.com' || input.val().indexOf('192.168') != -1 || input.val().indexOf('nct') != -1){
			$("#dir_name_row").show();
		} else {
			$("#dir_name_row").hide();
		}
	}
});
$("#install-form").on("submit", function(){
	if($("#site_url").val() == ''){
		$("#site_url").attr("placeholder", 'Please enter Site URL ex. mydomain.com');
		$('#site_url').css('border-color', 'red');
		return false;
	} /* else if($("#dir_name").val() == ''){
		$("#dir_name").attr("placeholder", 'Please enter Root Directory');
		$('#dir_name').css('border-color', 'red');
		return false;
	}  */else if($("#db_host").val() == ''){
		$("#db_host").attr("placeholder", 'Please enter Hostname');
		$('#db_host').css('border-color', 'red');
		return false;
	} else if($("#db_name").val() == ''){
		$("#db_name").attr("placeholder", 'Please enter Database name');
		$('#db_name').css('border-color', 'red');
		return false;
	} else if($("#db_username").val() == ''){
		$("#db_username").attr("placeholder", 'Please enter username for the database');
		$('#db_username').css('border-color', 'red');
		return false;
	} else if($('#accept'). prop("checked") == false){
		$('.chk-error').removeClass('hide');
		return false;
	} else {
		$(".loader").addClass('lds-hourglass');
		return true;
	}
});

$('input[type="checkbox"]'). click(function(){
	if($(this). prop("checked") == true){
			$('.chk-error').addClass('hide');
	}else{
			$('.chk-error').removeClass('hide');
	}
});

$(document).on('keyup','.db_content',function (argument) {
	var attData = $(this).attr('data-desc');
	$('#'+attData).val($(this).val());
});
$('.verify').click(function() {
	$(".loader").addClass('lds-hourglass');

	var db_host_chk = $("#db_host_chk").val();
	var db_name_chk = $("#db_name_chk").val();
	var db_username_chk = $("#db_username_chk").val();
	var db_pass_chk = $("#db_pass_chk").val();
	if($("#db_host_chk").val() == ''){
		$("#db_host_chk").attr("placeholder", 'Please enter Hostname');
		$('#db_host_chk').css('border-color', 'red');
		$("#install").attr("disabled", true);
		$('.verifyresponse').html('');
		$(".loader").removeClass('lds-hourglass');

		return false;
	} else if($("#db_name_chk").val() == ''){
		$("#db_name_chk").attr("placeholder", 'Please enter Database name');
		$('#db_name_chk').css('border-color', 'red');
		$("#install").attr("disabled", true);
		$('.verifyresponse').html('');
		$(".loader").removeClass('lds-hourglass');
		return false;
	} else if($("#db_username_chk").val() == ''){
		$("#db_username_chk").attr("placeholder", 'Please enter username for database');
		$('#db_username_chk').css('border-color', 'red');
		$("#install").attr("disabled", true);
		$('.verifyresponse').html('');
		$(".loader").removeClass('lds-hourglass');
		return false;
	}else{
		$.ajax({
			type: "POST",
			dataType: 'json',
			url: "verify",
			data: {
					action: "verify",
					db_host: db_host_chk,
					db_name: db_name_chk,
					db_username: db_username_chk,
					db_pass : db_pass_chk
				},
			success: (function( msg ) {
				$(".loader").removeClass('lds-hourglass');

				$('.verifyresponse').html(msg.content);
				if(msg.ready == 1){
					$("#install").attr("disabled", false);
				} else {
					$("#install").attr("disabled", true);
				}
			}),
			fail: (function( msg ) {
				$(".loader").removeClass('lds-hourglass');

				$('.verifyresponse').html(msg.content);
				$("#install").attr("disabled", true);
			})
		}).done(function( msg ) {
		});
	}
});
</script>