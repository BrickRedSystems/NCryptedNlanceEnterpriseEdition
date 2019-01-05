<!-- Modal -->
<div class="modal fade" id="ratings_modal" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    &times;
                </button>
                <h4 class="modal-title">{Rate_this_provider}</h4>
            </div>
            <div class="modal-body">
                <form id="reviewForm">
                <input type="hidden" name="token" value="%tokenValue%">
                    <div class="row margintop15">
                        <div class="col-md-6 font_bold">
                            {Was_the_provider_on_time_with_work} :
                        </div>
                        <div class="col-md-6">
                            <input id="workOnTime" name="workOnTime" type="text" class="rating rating-loading" value="%workOnTime%" data-size="sm" title="">
                        </div>
                    </div>
                    <div class="row margintop15">
                        <div class="col-md-6 font_bold">
                            {How_well_did_provider_communicate} :
                        </div>
                        <div class="col-md-6">
                            <input id="communication" name="communication" type="text" class="rating rating-loading" value="%communication%" data-size="sm" title="">
                        </div>
                    </div>
                    <div class="row margintop15">
                        <div class="col-md-6 font_bold">
                            {Overall_how_would_you_rate_the_reliability_of_this_provider} :
                        </div>
                        <div class="col-md-6">
                            <input id="reliability" name="reliability" type="text" class="rating rating-loading" value="%reliability%" data-size="sm" title="">
                        </div>
                    </div>
                    <div class="row margintop15">
                        <div class="col-md-6 font_bold">
                            {Professionalism} :
                        </div>
                        <div class="col-md-6">
                            <input id="professionalism" name="professionalism" type="text" class="rating rating-loading" value="%professionalism%" data-size="sm" title="">
                        </div>
                    </div>
                    <div class="row margintop15">
                        <div class="col-md-6 font_bold">
                            {How_likely_is_it_that_you_would_work_again_this_provider} :
                        </div>
                        <div class="col-md-6">
                            <input id="wouldWorkAgain" name="wouldWorkAgain" type="text" class="rating rating-loading" value="%wouldWorkAgain%" data-size="sm" title="">
                        </div>
                    </div>
                    <div class="row margintop15">                        
                        <div class="col-md-12">
                            <textarea class="form-control textarea_default" rows="5" placeholder="{Write_a_short_review}" style="width: 100%;"                         
                            name="review"                            
                            data-validation="validateDescription"
                            data-validation-error-msg="{Please_check_your_message_carefully}">%review%</textarea>
                        </div>
                    </div>
                    <div class="row margintop15"> 
                        <div class="col-md-12">                            
                            <input type="hidden" name="action" value="method"/>
                            <input type="hidden" name="method" value="rateThisProvider"/>
                            <input type="submit" class="btn btn_blue_new btn_light_hover" value="{Submit_my_review}" data-ele="submitReview"> 
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    {Close}
                </button>
            </div>
        </div>
    </div>
</div>