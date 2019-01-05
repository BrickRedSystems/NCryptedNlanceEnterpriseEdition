<li class="search-cell">
    <div class="search-left">
        <a href="%profileLink%" class="search-user">
        <span class="user-photo">
            <img src="%profilePhoto%" alt="%fullName%" title="%fullName%">
        </span> %fullName% </a>
        <div class="clearfix"></div>
        
    </div>
    <div class="search-right-content">
        <h3><a href="%slug%">%title%</a></h3>
        <p class="search-disc">
            %desc%
        </p>
        <div class="clearfix"></div>
        <div class="row">
            <div class="col-sm-12">
                <p class="search-bottom">
                    {Favorited_Date} <strong>%date%</strong>
                </p>
            </div>
        </div>
        <ul class="right-icons">
            <li>
                <a href="javascript:void(0);"  data-operation="favoriteProjectMyProviders" data-info="%pid%" title="Like">
                    <i class="fa %isFavorite% like-icon"></i>
                </a>
            </li>
            <li></li>
            <li class="%isFeatured%">
                <span class="feature-label">
                    Featured
                </span>
            </li>
        </ul>
    </div>
</li>
