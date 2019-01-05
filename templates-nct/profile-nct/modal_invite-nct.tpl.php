<div class="modal fade create-milestone-data" id="inviteModal" role="dialog">
	<div class="modal-dialog clearfix">
		<!-- Modal content-->
		<div class="modal-content clearfix">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">
					&times;
				</button>
				<h3>{Invite_provider_to_bid_on_your_open_projects}</h3>
			</div>
			<div class="modal-body bid-modal-body clearfix">
				<form id="inviteForm">
				<input type="hidden" name="token" value="%tokenValue%">
					<div class="clearfix"></div>
					<div class="milestones-form">
						<div class="form-group relative-select">
							<label>{Select_projects_for_which_you_like_to_invite_this_provider}</label>
                            <div class="clearfix">
							<select name="projectIds[]" class="selectBox-bg form-control required" multiple data-actions-box="true" data-live-search="true" data-ele="multiselectProjects">
								%project_options%
							</select>
                            </div>
						</div>
					</div>
					<div class="form-group">
						<input type="hidden" name="action" value="method"/>
						<input type="hidden" name="method" value="inviteProvider"/>
						<input type="hidden" name="suserId" value="%userId%"/>
						<button type="submit" class="btn btn_blue btn-block" data-ele="submitInviteForm">
							<strong>{Submit}</strong>
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
