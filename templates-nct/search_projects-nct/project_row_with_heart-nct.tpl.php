<li class="search-cell" data-infinite="row">
    <div class="search-left">
        <a target="_blank"  href="%profileLink%" class="search-user" title="%fullName%">
        <span class="user-photo">
            <img src="%profilePhoto%" alt="">
        </span> %fullName% </a>
    </div>
    <div class="search-right-content">
        <h3><a target="_blank"  href="%slug%">%title%</a> <a href="javascript:void(0);" class="fa %like_icon% like-icon" data-operation="favoriteProjectMyProviders" data-info="%pid%" title="{Like}"></a></h3>
        <p class="search-disc">
            %desc%
        </p>
        <p>%cat_subcat%</p>
        <ul class="search-tag">
            %skills%
        </ul>
        <div class="clearfix"></div>
        <div class="row">
            <div class="col-sm-3">
                <p class="search-bottom">
                    {Est_Budget} <strong>{CURRENCY_SYMBOL}%budget%</strong>
                </p>
            </div>
            <div class="col-sm-3">
                <div class="search-bottom">
                    {Bids} <div class="tooltip-top"> <strong class="blue-color">%bids%</strong>
                    <div class="tooltip_box">
                        <ul class="tooltip-row">
                            <li class="left-cell">
                                {Average_Value}
                            </li>
                            <li class="right-cell">
                                <strong>{CURRENCY_SYMBOL}%avgBid%</strong>
                            </li>
                            <li class="left-cell">
                                {Average_ETA}
                            </li>
                            <li class="right-cell">
                                <strong>%avgETA% {days}</strong>
                            </li>
                        </ul>
                    </div> </div> 
                </div>
            </div>
            <div class="col-sm-4">
                <p class="search-bottom">
                    {Posted_on} <strong>%postedDate%</strong>
                </p>
            </div>
            <div class="col-sm-2">
                <span class="feature-label %isFeatured%">
                    {Featured}
                </span>
            </div>
        </div>
    </div>
</li>
