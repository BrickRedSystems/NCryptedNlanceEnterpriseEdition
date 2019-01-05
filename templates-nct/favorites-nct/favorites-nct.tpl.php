<div class="mainpart">
	<!-- /Section-heading start -->
	<section class="section-heading">
		<div class="container">
			<h1>{My_Favorites}</h1>
		</div>
	</section>
	<!-- /Section-heading over -->
	<!-- /Section-middle start -->
	<div class="my-provider-hired">
		<div class="container">
			<div class="row">
				<div class="col-sm-3 section-middle clearfix">
					<div class="section-middle-left">
						<ul>
							<li class="%class_fav_projects%" data-ele="tabs">
								<a href="{SITE_FAVORITES}projects/"  rel="tab" data-container=".search-row" data-extra="favProjects">{Projects}<span class="count-figure">
									%total_fav_projects%</span></a>
							</li>
							<li class="%class_fav_users%"  data-ele="tabs">
								<a href="{SITE_FAVORITES}users/" rel="tab" data-container=".search-row" data-extra="favorite">{Providers}<span class="count-figure">
									%total_fav_users%</span></a>
							</li>
							
						</ul>
					</div>
				</div>
				<div class="col-sm-9">					
					<div class="search-right">
						<ul class="search-row">
							%row%
						</ul>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
	</div>
	<!-- /Section-middle over -->
</div>
