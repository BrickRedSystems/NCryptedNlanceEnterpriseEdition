<div class="search-filter">
	<h3 class="filter-title">{Filters} <span class="filter-toggle"><i class="fa fa-navicon"></i></span><a href="{SITE_SEARCH_PROVIDERS}" class="clear-filter btn">{CLEAR_ALL}</a></h3>
	<div class="search-form">
		<form>
			<div class="form-group marginbtm0">
				<select class="form-control" data-ele="cat">
					%projectCategories%
				</select>
			</div>
			<div class="clearfix"></div>
			<div class="filter-box">
				<h4>{Select_Sub_Category}</h4>
				<select name="subCat[]" class="selectBox-bg form-control " multiple data-actions-box="true" data-live-search="true" data-ele="multiselectsubcat">
					%projectSubCategories%
				</select>
			</div>
			<div class="clearfix"></div>
			<div class="filter-box">
				<h4>{Select_Skills}</h4>
				<select name="skillIds[]" id="skillIds" class="selectBox-bg form-control " multiple data-actions-box="true" data-live-search="true" data-ele="multiselectskills">
					%skill_options%
				</select>
			</div>
			<div class="clearfix"></div>
			<div class="filter-box">
				<h4>{Experience_Level}</h4>
				<div id="content-3" class="filter-scroll" data-ele="levels">
					<ul class="check-row">
						<li>
							<label class="control control--checkbox">{Entry_Level}
								<input type="checkbox" %check_entry_level% value="entry level"/>
								<span class="control__indicator"> </span> </label>
						</li>
						<li>
							<label class="control control--checkbox">{Moderate}
								<input type="checkbox" %check_moderate% value="moderate"/>
								<span class="control__indicator"> </span> </label>
						</li>
						<li>
							<label class="control control--checkbox">{Expert}
								<input type="checkbox" %check_expert% value="expert"/>
								<span class="control__indicator"> </span> </label>
						</li>
					</ul>
				</div>
			</div>
			<div class="clearfix"></div>
			
		</form>
	</div>
</div>
