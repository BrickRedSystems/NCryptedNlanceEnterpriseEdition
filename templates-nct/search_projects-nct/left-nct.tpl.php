<div class="search-filter">
    <h3 class="filter-title">
        {Filters}
        <span class="filter-toggle">
            <i class="fa fa-navicon">
            </i>
        </span>
        <a class="clear-filter btn" href="{SITE_SEARCH_PROJECTS}">
            {CLEAR_ALL}
        </a>
    </h3>
    <div class="search-form">
        <form>
            <div class="form-group marginbtm0">
                <select class="form-control" data-ele="cat">
                    %projectCategories%
                </select>
            </div>
            <div class="clearfix">
            </div>
            <div class="filter-box">
                <h4>
                    {Select_Sub_Category}
                </h4>
                <select class="selectBox-bg form-control" data-actions-box="true" data-ele="multiselectsubcat" data-live-search="true" multiple="" name="subCat[]">
                    %projectSubCategories%
                </select>
            </div>
            <div class="clearfix">
            </div>
            <div class="filter-box">
                <h4>
                    {Select_Skills}
                </h4>
                <select class="selectBox-bg form-control " data-actions-box="true" data-ele="multiselectskills" data-live-search="true" id="skillIds" multiple="" name="skillIds[]">
                    %skill_options%
                </select>
            </div>
            <div class="clearfix">
            </div>
            <div class="filter-box">
                <h4>
                    {Experience_Level}
                </h4>
                <div class="filter-scroll" data-ele="levels">
                    <ul class="check-row">
                        <li>
                            <label class="control control--checkbox">
                                {Entry_Level}
                                <input %check_entry_level% type="checkbox" value="entry level"/>
                                <span class="control__indicator">
                                </span>
                            </label>
                        </li>
                        <li>
                            <label class="control control--checkbox">
                                {Moderate}
                                <input %check_moderate% type="checkbox" value="moderate"/>
                                <span class="control__indicator">
                                </span>
                            </label>
                        </li>
                        <li>
                            <label class="control control--checkbox">
                                {Expert}
                                <input %check_expert% type="checkbox" value="expert"/>
                                <span class="control__indicator">
                                </span>
                            </label>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="clearfix">
            </div>
            <div class="filter-box">
                <div class="form-group">
                    <div class="filter-cell">
                        <h5>
                            {Budget}
                        </h5>
                        <p class="range-amount">
                            {CURRENCY_SYMBOL}
                            <span data-ele="sliderMin">
                                0
                            </span>
                            - {CURRENCY_SYMBOL}
                            <span data-ele="sliderMax">
                                100
                            </span>
                        </p>
                        <div class="clearfix">
                        </div>
                        <input data-ele="priceRange" data-max="%highestBudget%" data-min="%lowestBudget%" name="range_25" type="text" value=""/>
                    </div>
                </div>
            </div>
            <div class="clearfix">
            </div>
            <div class="filter-box">
                <div class="filter-scroll mCustomScrollbar">
                    <ul class="check-row">
                        <li>
                            <label class="control control--checkbox">
                                {Featured}
                                <input %check_isFeatured% data-ele="isFeatured" type="checkbox"/>
                                <span class="control__indicator">
                                </span>
                            </label>
                        </li>
                    </ul>
                </div>
            </div>
        </form>
    </div>
</div>
