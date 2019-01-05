<div class="modal fade create-milestone-data" id="inviteModal" role="dialog">
	<div class="modal-dialog clearfix">
		<!-- Modal content-->
		<div class="modal-content clearfix">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">
					&times;
				</button>
				<h3>{Invite_providers_to_bid_on_this_project}</h3>
			</div>
			<div class="modal-body bid-modal-body clearfix">
				<form id="inviteForm">
					<div class="clearfix"></div>
					<div class="milestones-form">
						<div class="form-group relative-select">
							<label>{Select_providers_which_you_like_to_invite_this_project}</label>
                            <div class="clearfix">
							<select name="providerIds[]" class="selectBox-bg form-control required" multiple data-actions-box="true" data-live-search="true" data-ele="multiselectProviders">
								%provider_options%
							</select>
                            </div>
						</div>
					</div>
					<div class="form-group">
						<input type="hidden" name="action" value="method"/>
						<input type="hidden" name="method" value="inviteProvider"/>
						<button type="submit" class="btn btn_blue btn-block" data-ele="submitInviteForm">
							<strong>{Submit}</strong>
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>