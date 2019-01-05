<div class="mainpart">
	<!-- /Section-heading start -->
	<section class="section-heading">
		<div class="container">
			<h1>{My_Providers}</h1>
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
							<li class="%class_hired%" data-ele="tabs">
								<a href="{SITE_PROVIDERS}hired/"  rel="tab" data-container=".search-row" data-extra="hired">{Hired}<span class="count-figure">
									%total_hired%</span></a>
							</li>
							<li class="%class_favorite%"  data-ele="tabs">
								<a href="{SITE_PROVIDERS}favorite/" rel="tab" data-container=".search-row" data-extra="favorite">{Favorite}<span class="count-figure">
									%total_favorite%</span></a>
							</li>
							<li class="%class_invited%"  data-ele="tabs">
								<a href="{SITE_PROVIDERS}invited/" rel="tab" data-container=".search-row" data-extra="invited">{Invited}<span class="count-figure">
									%total_invited%</span></a>
							</li>
						</ul>
					</div>
				</div>
				<div class="col-sm-9">
					<div class="row">
						<div class="col-sm-7 col-md-8">
							<div class="form-group search-fieldset">
								<input value="%keyword%" data-ele="searchBox" type="text" class="form-control field-border" placeholder="{Search_by_project_name_or_provider_name}">
								<button type="submit" class="btn search-btn" data-ele="submitSearch">
									{Search}
								</button>
							</div>
						</div>
						<div class="col-sm-5 col-md-4">
							<div class="form-inline sort-form">
								<div class="form-group">
									<label>{Sort_by}:</label>
									<select class="form-control" data-ele="sort_by">
									    <option value="relevance" %select_relevance%>{Relevence}</option>
										<option value="newest-to-oldest" %select_n2o%>{Newest_to_Oldest}</option>
										<option value="oldest-to-newest" %select_o2n%>{Oldest_to_Newest}</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="clearfix"></div>
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
