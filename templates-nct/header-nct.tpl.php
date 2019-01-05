<noscript>
	<div style="position:relative;z-index:9999;background-color:#F90; border:#666; font-size:22px; padding:15px; text-align:center">
		<strong>For the best performance and user experience, please enable javascript.</strong>
	</div>
</noscript>

<header class="relative ">
	<nav class="navbar navbar-default header_menu %header_type%">
		<div class="container">
			<!-- Brand and toggle get grouped for better mobile display -->
			<div class="navbar-header col-md-2">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar top-bar"></span>
					<span class="icon-bar middle-bar"></span>
					<span class="icon-bar bottom-bar"></span>
				</button>
				<a class="navbar-brand" title="{SITE_NM}" href="{SITE_URL}"><img src="{SITE_LOGO_URL}" alt="logo"></a>
			</div>

			<!-- Collect the nav links, forms, and other content for toggling -->
			<div class="collapse navbar-collapse text-center" id="bs-example-navbar-collapse-1">
				<div class="search navbar-form navbar-left %search_type%">
                    
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon %group_addon%">
								<div class="dropdown %dropdown_type%">
								    <select class="selectBox-bg required" data-ele="multiselectsearch" data-showIcon="true">
								        <option %isProvidersSelected% value="providers" data-content="<i class='fa fa-users'></i><span class=''>{Providers}</span>">{Providers}</option>
								        <option %isProjectsSelected% value="projects" data-content="<i class='fa fa-file'></i><span class=''>{Projects}</span>">{Projects}</option>
								    </select>				   
								</div>
							</div>
							<input type="text" class="form-control %searchbox_type%" placeholder="%headerSearchPlaceholder%" data-ele="headerSearchBox">
                            <button data-ele="headerSearchBtn" type="submit" class="search-btn btn" title="{Search}"><i class="fa fa-search"></i></button>
						</div>
					</div>
					
				</div>
				%header_section%
			</div><!-- /.navbar-collapse -->
		</div><!-- /.container-fluid -->
	</nav>

</header>

