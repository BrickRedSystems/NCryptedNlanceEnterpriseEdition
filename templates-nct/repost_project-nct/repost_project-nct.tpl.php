<div class="mainpart">
	<!-- /Section-heading start -->
	<section class="section-heading">
		<div class="container">
			<h1>{Repost_Project}</h1>
		</div>
	</section>
	<!-- /Section-heading over -->
	<!-- /Section-middle start -->
	<div class="section-middle">
		<div class="container">
			<div class="row">
				<div class="col-sm-7">
					<form id="repostProjectForm">
					<input type="hidden" name="token" value="%tokenValue%">
						<!-- step1 -->
						<!-- post-project-left start -->
						<div class="post-project-left " data-ele="steps" data-step="1">
							<div class="post-project-form">
								<div class="col-sm-12">
									<div class="form-group">
										<label>{Project_Title}</label>
										<input type="text" class="form-control" tabindex="1"										 
										name="title" value="%title%"
										data-validation="length custom"
										data-validation-regexp="^[a-zA-Z0-9'&_,\s]+$"
										data-validation-length="5-125"
										data-validation-help="{err_Some_special_chars_are_not_allowed}"
										data-validation-error-msg="{err_Project_title_has_to_be_an_alphanumeric_value}">
									</div>
								</div>
								<div class="col-sm-12">
									<div class="form-group">
										<label>{Category}</label>
										<select class="form-control" tabindex="2"
										data-ele="categoryId"
										name="categoryId" id="categoryId-input"
										data-validation="required"
										data-validation-error-msg="{err_Please_select_project_category}">
											<option value="">{Select_project_category}</option>
											%cats%
										</select>
									</div>
								</div>
								<div class="col-sm-12">
									<div class="form-group">
										<label>{Sub_Category}</label>
										<select class="form-control" tabindex="3"
										data-ele="subcategoryId"
										name="subcategoryId"
										data-validation-depends-on="categoryId">
											<option value="">{Select_project_sub_category}</option>
											%subcats%
										</select>
									</div>
								</div>
								<div class="col-sm-12">
									<div class="form-group">
										<div class="form-group">
											<label>{Project_Description} (<span id="pres-max-length">1200</span> {characters_left})</label>
											<textarea class="form-control" name="description" id="description" rows="10" tabindex="4"
											data-validation="validateDescription"
											data-validation-error-msg="{err_Please_check_description_carefully}">%description%</textarea>
										</div>
									</div>
								</div>
								<div class="col-sm-12">
									<button type="button" class="btn btn_blue btn-block btn_light_hover" data-ele="next" tabindex="5">
										{Next}
									</button>
								</div>
							</div>
						</div>
						<!-- post-project-left end -->
						<!-- step1 -->
						<!-- step2 -->
						<!-- post-project-left start -->
						<div class="post-project-left hide" data-ele="steps" data-step="2">
							<div class="post-project-form">
								<div class="col-sm-12">
									<div class="form-group">
										<label>{Required_Skills}</label>
										<div class="tagscont" data-ele="skillsContainer"></div>
										<input type="text" class="tagsinput form-control" placeholder="E.g: PHP, Photoshop, Mongo DB - Seperate with commas"										
										name="skillsId" data-ele="skillsId"
										data-validation="validateSkillsId"
										data-validation-error-msg="{err_Please_select_right_set_of_skills_for_the_project}"/>
									</div>
								</div>
								<div class="col-sm-12">
									<div class="form-group">
										<label>{Budget} ({PAYPAL_CURRENCY_CODE})</label>
                                        <div class="input-group">
                                        <div class="input-group-addon">
												<span class="fa fa-dollar">
												</span>
											</div>
										<input type="number" class="form-control" 
										name="budget" value="%budget%"
										data-validation="required number"
										data-validation-allowing="range[1.00;99999999999],float"
										data-validation-error-msg="{err_Please_enter_price_for_the_project}"/>
                                        </div>
									</div>
								</div>
								<div class="col-sm-12">
									<div class="form-group">
										<label>{Project_Duration}</label>
										<input type="number" class="form-control" 
										data-ele="duration"
										name="duration" value="%duration%"
										data-validation="required number"
										data-validation-allowing="range[1;99999999999]"
										data-validation-error-msg="{err_Please_enter_days_for_the_project}">
											
									</div>
								</div>
								<div class="col-sm-12">
									<div class="form-group">
										<label>{Bidding_Deadline}</label>
										<div class="input-group date" data-provide="datepicker" data-date-start-date="+1d" data-date-format="yyyy/mm/dd">
											<input type="text" class="form-control"
											name="biddingDeadline" value="%biddingDeadline%"
											data-validation="date required"
											data-validation-format="yyyy/mm/dd"
											data-validation-error-msg="{err_Please_select_bidding_deadline_for_this_project}">
											<div class="input-group-addon">
												<span class="fa fa-calendar">
												</span>
											</div>
										</div>
									</div>
								</div>
								<div class="col-sm-12">
									<button type="button" class="btn btn_blue btn-block btn_light_hover" data-ele="next">
										{Next}
									</button>
								</div>
							</div>
						</div>
						<!-- post-project-left end -->
						<!-- step2 -->
						<!-- step3 -->
						<!-- post-project-left start -->
						<div class="post-project-left hide" data-ele="steps" data-step="3">
							<div class="post-project-form" id="fileupload" >
								<div class="col-sm-12">
									<div class="form-group">
										<label>{Attach_File}</label>
										<label class="btn btn-default btn-file upload-file"> <span class="drp-file">
												{Drop_files_here_or_click_to_upload}</span> <!-- The file input field used as target for the file upload widget -->
											<input type="file" name="files[]" multiple style="visibility:hidden;">
										</label>
									</div>
									<!-- The table listing the files available for upload/download -->
                                    <div class="table-responsive">
									<table role="presentation" class="table table-striped">
										<tbody class="files"></tbody>
									</table>
                                    </div>
								</div>
								<div class="col-sm-12">
									<div class="form-group check-box">
										<label class="control control--checkbox margintop15">{Mark_as_Featured}
											<input type="checkbox" %isFeatured% name="isFeatured" id="isFeatured-input" value="y"/>
											<span class="control__indicator">
											</span> </label>
										<p>
											{Mark_as_Featured_text}
										</p>
									</div>
								</div>
								<div class="col-sm-12" id="featuredSection">
									<div class="form-group days-count">
										<label class="number-of-day">{Enter_No_of_Days}</label>
										<input type="number" class="form-control days-number"
										onchange="$('.amount span').html($(this).val()*{FEATURED_PROJ_PRICE});"
										name="featuredDays" value="%featuredDays%"							
										data-validation-depends-on="isFeatured"
										data-validation-depends-on-value="y"									
										data-validation-allowing="range[1;365]"									
										data-validation-error-msg="Please enter valid no. of days for featuring this project.">
										<span class="days">
											{CURRENCY_SYMBOL}{FEATURED_PROJ_PRICE} {Per_Day}</span>
									</div>
								</div>
								<div class="col-sm-12">
									<p class="days-count">
										{Total_Price} : <b class="amount">{CURRENCY_SYMBOL}<span>
											%total_price%</span></b>
									</p>
								</div>
								<div class="col-sm-12">
									<input type="hidden" name="action" value="method"/>
									<input type="hidden" name="method" value="repostProject"/>
									<button type="submit" class="btn btn_blue btn-block btn_light_hover" data-ele="finish">
										{Pay_and_Submit}
									</button>
								</div>
							</div>
						</div>
						<!-- post-project-left end -->
						<!-- step3 -->
					</form>
				</div>
				<div class="col-sm-5">
					<!-- post-project-right start -->
					<div class="post-project-right">
						<div class="post-project-step">
							<div class="step" data-ele="right-filled" data-right-step="1">
								<div class="step-left">
									<div class="current-step-round"></div>
									<h4>{Step} 1</h4>
								</div>
								<div class="step-right">
									<p>
										{Step_1_text}
									</p>
								</div>
							</div>
							<div class="step hide" data-ele="right-empty" data-right-step="1">
                            	<div class="step-left">
                                	<div class="pending-step-round">
                                    </div>
                                </div>
                                <div class="step-right">
                                	
                                </div>
                            </div>
							
							<div class="step hide" data-ele="right-filled" data-right-step="2">
								<div class="step-left">
									<div class="current-step-round"></div>
									<h4>{Step} 2</h4>
								</div>
								<div class="step-right">
									<p>
										{Step_2_text}
									</p>
								</div>
							</div>
							<div class="step" data-ele="right-empty" data-right-step="2">
                            	<div class="step-left">
                                	<div class="pending-step-round">
                                    </div>
                                </div>
                                <div class="step-right">
                                	
                                </div>
                            </div>
							
							<div class="step hide" data-ele="right-filled" data-right-step="3">
								<div class="step-left">
									<div class="current-step-round"></div>
									<h4>{Step} 3</h4>
								</div>
								<div class="step-right no-border">
									<p>
										{Step_3_text}
									</p>
								</div>
							</div>
							<div class="step" data-ele="right-empty" data-right-step="3">
                            	<div class="step-left">
                                	<div class="pending-step-round">
                                    </div>
                                </div>
                                <div class="step-right no-border">
                                	
                                </div>
                            </div>
							
							
						</div>
					</div>
					<!-- post-project-right end -->
				</div>
			</div>
		</div>
	</div>
	<!-- /Section-middle over -->
</div>
<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td>
            <span class="preview"></span>
        </td>
        <td>
            <p class="name">{%=file.name%}</p>
            <strong class="error text-danger"></strong>
        </td>
        <td>
            <p class="size">Processing...</p>
            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
        </td>
        <td class="text-right">
            {% if (!i && !o.options.autoUpload) { %}
                <button class="btn btn_blue_new start" disabled>
                    <i class="fa fa-upload"></i>
                    <span>{Start}</span>
                </button>
            {% } %}
            {% if (!i) { %}
                <button class="btn btn_blue_new cancel-btn cancel">
                    <i class="fa fa-close"></i>
                    <span>{Cancel}</span>
                </button>
            {% } %}
        </td>
    </tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        <td>
            <span class="preview">
                {% if (file.thumbnailUrl) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
                {% } %}
            </span>
        </td>
        <td>
            <p class="name">
                {% if (file.url) { %}
                    <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                {% } else { %}
                    <span>{%=file.name%}</span>
                {% } %}
            </p>
            {% if (file.error) { %}
                <div><span class="label label-danger">{Error}</span> {%=file.error%}</div>
            {% } %}
        </td>
        <td>
            <span class="size">{%=o.formatFileSize(file.size)%}</span>
        </td>
        <td>
            {% if (file.deleteUrl) { %}
                <button class="btn btn-danger delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
                    <i class="glyphicon glyphicon-trash"></i>
                    <span>{Delete}</span>
                </button>
                
            {% } else { %}
                <button class="btn btn-warning cancel">
                    <i class="glyphicon glyphicon-ban-circle"></i>
                    <span>{Cancel}</span>
                </button>
            {% } %}
        </td>
    </tr>
{% } %}
</script>