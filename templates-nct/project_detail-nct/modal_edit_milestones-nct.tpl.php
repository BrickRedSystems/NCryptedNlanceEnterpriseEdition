<div class="modal fade create-milestone-data" id="MilModal" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">
					&times;
				</button>
				<h3>{Create_Milestone}</h3>
			</div>
			<div class="modal-body bid-modal-body">
				<form id="milForm">
				<input type="hidden" name="token" value="%tokenValue%">
					<div class="clearfix"></div>
					%row%					
					<div class="clearfix"></div>
					<div class="center-block bottom-part">
						<a href="javascript:void(0);" data-ele="addMil"><i class="fa fa-plus-square-o"></i> {Add_a_Milestone}</a>
						<h3>{Milestone_Total}
						<span>
							{CURRENCY_SYMBOL}<span data-ele="milTotal">%milestone_total%</span> / %project_price%
						</span></h3>
					</div>
					<div class="form-group">
					    <input type="hidden" name="action" value="method"/>
					    <input type="hidden" name="method" value="editMilestones"/>
						<button type="submit" class="btn btn_blue btn-block" data-ele="submitEditMilForm">
							<strong>{Submit}</strong>
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
