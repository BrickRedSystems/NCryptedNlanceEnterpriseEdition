<div class="mainpart">
	<!-- /Section-heading start -->
	<section class="section-heading">
		<div class="container">
			<h1>{Search_projects}</h1>
		</div>
	</section>
	<!-- /Section-heading over -->
	<!-- /Section-middle start -->
	<div class="search-result-main">
		<div class="container">
			<div class="row">
				<div class="col-sm-3 clearfix">
					%left%
				</div>
				<div class="col-sm-9">
					<div class="row">
						<div class="col-sm-7 col-md-8">
							<div class="form-group search-fieldset">
								<input type="text" data-ele="searchBox" class="form-control field-border" placeholder="{Search_projects}" data-ele="keyword" value="%keyword%">
								<button type="submit" class="btn search-btn" data-ele="submitSearch">
									{Search}
								</button>
							</div>
						</div>
						<div class="col-sm-5 col-md-4">
							<div class="form-inline sort-form">
								<div class="form-group">
									<label>{Sort_by}:</label>
									<select class="selectBox-bg form-control" data-ele="sort_by">
										<option value="relevance" %select_relevance%>{Relevence}</option>
										<option value="newest_first" %select_newest%>{Newest_First}</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="clearfix"></div>
					<div class="search-right">
						<ul class="search-row" data-infinite="container">%rows%</ul>
					</div>
					<div class="clearfix"></div>
					
				</div>
			</div>
		</div>
	</div>
	<!-- /Section-middle over -->
</div>
