<div class="mainpart">
	<!-- /Section-heading start -->
	<section class="section-heading">
		<div class="container">
			<h1>{Financial_Information}</h1>
		</div>
	</section>
	<!-- /Section-heading over -->
	<!-- /Section-middle start -->
	<div class="section-middle">
		<div class="container">
			<div class="row">
				<div class="col-sm-3">
					<!-- section-middle-left start -->
					<div class="section-middle-left">
						<ul data-ele="tabs">
							<li class="active" data-method="inProgress">
								{In_Progress}
							</li>
							<li data-method="completed">
								{Completed}
							</li>
						</ul>
					</div>
					<!-- section-middle-left end -->
				</div>
				<div class="col-sm-9" data-ele="tab_panel">
					<!-- section-middle-right start -->
					%tab_panel%
					<!-- section-middle-right end -->					
				</div>
			</div>
		</div>
	</div>
	<!-- /Section-middle over -->
</div>