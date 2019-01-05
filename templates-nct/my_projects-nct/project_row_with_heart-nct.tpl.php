<section data-infinite="row">
	<div class="section-middle-right-project">
		<span class="profileimage">
			<img class="img-circle" src="%profilePhoto%" alt="">
			<a href="{SITE_URL}%profileLink%" target="_blank">%fullName%</a>
		</span>
		<h4>
			<a href="{SITE_URL}%slug%" target="_blank">%title%</a>
			<a class="favouriteproject" data-ele="like" data-operation="favoriteProjectMyProjects" data-info="%pid%" title="{Like}">
				<i class="fa %like_icon%"></i>
			</a>
		</h4>
		<p>
			%desc%
		</p>
		<ul>
			<li>
				%est_or_total% <b>%budget%</b>
			</li>
			<li>
				{Bids}<b>%bids%</b>
			</li>
			<li>
				<button type="button" class="btn btn-primary small-btn featured-btn %isFeatured%">
					{Featured}
				</button>
			</li>
		</ul>
	</div>
	<div class="clearfix"></div>
</section>
