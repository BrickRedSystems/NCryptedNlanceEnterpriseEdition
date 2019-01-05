<script src="{SITE_JS}jquery.imgareaselect.js"></script>  
<link href="{SITE_CSS}imgareaselect-default.css" rel="stylesheet">
<link href="{SITE_CSS}cropimage.css" rel="stylesheet">
    
    <h2>Edit Profile</h2>
         <div class="edit-form">
             <form name="frmPro" id="frmPro" method="post" enctype="multipart/form-data">
                     <div>
                         <label>First Name: </label>
                         <input type="text" value="%FIRSTNAME%" name="firstName" id="firstName">
                     </div>
                     <div>
                         <label>Last Name: </label>
                         <input type="text" value="%LASTNAME%" name="lastName" id="lastName">
                     </div>
                     <div>
                         <label>Email: </label>
                         <input type="text" value="%EMAIL%" name="email" id="email" readonly="readonly">
                     </div>
                     <div>
                         <label>Birth Date: </label>
                         <input type="text" value="%BDATE%" name="birthDate" id="birthDate">
                     </div>
                      <input type="submit" class="sbtPro" value="Save Changes" name="sbtPro" id="sbtPro">
                </form>
                      <div>
                          <label>Profile Image: </label>
                          <div class="user_div_photo">
                          <label for="file-input">
                            Edit Image
                          </label>
                          <form class="form-horizontal" style="display:none" action="javascript:void(0)" method="post" name="user_form_photo" id="user_form_photo" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="submit_edit_photo">
                            <input id="file-input" name="file-input" type="file"/>
                            <button type="submit" class="btn btn-default">submit</button>
                        </form>  
                        </div>
                        
                          <div class="profile-image">
                          <img src="%IMG%" id="show-croped-picture" alt="profile image">
                          </div>
                      </div> 
                     
                    
             </div>

<script type="text/javascript">
    $('#birthDate').datepicker({
        format: 'dd-mm-yyyy',
        todayBtn: 'linked'
    });
$(function(){
    $('#myModal_category').on('hidden.bs.modal', function () {
        $('#crop-picture').imgAreaSelect({disable:true,hide:true});
    });
});
</script>

<script type="text/javascript">
    
    $frmPro="#frmPro";
        {
    $($frmPro).validate(
            {
                rules:
                {
                    firstName: {required:true},
                    lastName:{required:true},
                    birthDate:{required:true}
                    
                },
                messages:
                {
                    firstName:
                    {
                        required:"Please enter your first name."
                    },
                    lastName:
                    {
                        required:"Please enter your last name."
                    },
                    birthDate:
                    {
                        required:"Please enter your birth date."
                    }
                   
                }
            });
        }
 $(document).on("change","#user_form_photo",function(e){
    e.preventDefault();
     var formOptions = {
            url:'{SITE_URL}modules-nct/edit_profile-nct/ajax.edit_profile-nct.php',
            type:'post',
            dataType:"json",
            async:false,
            cache:false,
            success: function (data) {
                 
                if (data.type == 'suc')
                    {

                       $('#myModal_category').modal('show');
                       $('#crop-picture').attr('src', data.msg);
                       $('#preview-picture').attr('src', data.msg);
                       $('#crop-picture').imgAreaSelect({  aspectRatio: '1:1', handles: true, onSelectChange: preview,x1: 120, y1: 90, x2: 280, y2: 210 });
                    }
                    else
                    {
                       toastr['error'](obj.msg, '');
                    }
                }
            //}
        };
    $(this).ajaxSubmit(formOptions);
});
function preview(img, selection) 
{
    var scaleX = 250/selection.width; 
    var scaleY = 200/selection.height; 

    $('#preview-picture > img').css({
            width: Math.round(scaleX * img.width) + 'px', 
            height: Math.round(scaleY * img.height) + 'px',
            marginLeft: '-' + Math.round(scaleX * selection.x1) + 'px', 
            marginTop: '-' + Math.round(scaleY * selection.y1) + 'px' 
    });

    var x1 = Math.round((img.naturalWidth/img.width)*selection.x1);
    var y1 = Math.round((img.naturalHeight/img.height)*selection.y1);
    var x2 = Math.round(x1+selection.width);
    var y2 = Math.round(y1+selection.height);

    $('#x1').val(x1);
    $('#y1').val(y1);
    $('#x2').val(x2);
    $('#y2').val(y2);   

    $('#w').val(Math.round((img.naturalWidth/img.width)*selection.width));
    $('#h').val(Math.round((img.naturalHeight/img.height)*selection.height));   
} 
$(document).ready(function () 
{ 
    $('.save_thumb').click(function() 
    {

        var x1 = $('#x1').val();
        var y1 = $('#y1').val();
        var x2 = $('#x2').val();
        var y2 = $('#y2').val();
        var w = $('#w').val();
        var h = $('#h').val();
        if(x1=='' || y1=='' || x2=='' || y2=='' || w=='' || h==''){
                alert('Please make a selection first.');
                return false;
        }
        else
        {          
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '{SITE_URL}modules-nct/edit_profile-nct/ajax.edit_profile-nct.php',
                data: $('#thumbnail').serialize(),
                timeout: 3000,
                cache:false,
                async:false,
                success: function (data) {
                    //var obj = jQuery.parseJSON(data);
                    var obj = JSON.parse(JSON.stringify(data));
                    $('#rotate-picture').html(obj.msg);
                    $('#crop-rotate-btn').hide();
                   // $('#rotate-image').show();
                    $('#crop-picture').imgAreaSelect({remove:true});
                    $('#myModal_category').modal('hide');
                    $('#show-croped-picture').attr('src', data.msg);
                    $("#crop_disable").removeAttr("disabled").text('Crop'); 
                },
                error: function () { 
                    $('#crop-rotate-btn').hide();
                    $('#rotate-image').show();
                    $('#crop-picture').imgAreaSelect({remove:true});
                    alert('failed');
                }
            });
            return true;
        }
    });
}); 
</script>
<div class="modal fade" id="myModal_category" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
              <h4 class="modal-title">Image Upload</h4>  
            </div>
            <div class="modal-body">
                <img id="crop-picture" src="" align="center" style="max-width:320px; max-height:500px;"/>
                    <div class="crop_preview_box_small" id='thumbviewimage' style="position:relative; display: none;"> 
                        <img src="" id="preview-picture" class="upload-img" alt=""/>
                    </div>   
                
                    <form name="thumbnail" id="thumbnail" action="" method="post">
                            <input type="hidden" name="x1" value="" id="x1" />
                            <input type="hidden" name="y1" value="" id="y1" />
                            <input type="hidden" name="x2" value="" id="x2" />
                            <input type="hidden" name="y2" value="" id="y2" />
                            <input type="hidden" name="w" value="" id="w" />
                            <input type="hidden" name="h" value="" id="h" />
                            <input type="hidden" name="wr" value="" id="wr" />
                            <input type="hidden" name="action" value="crop-photo" id="action" />                           
                    </form> 

            </div>           
            <div class="modal-footer">  
                <button type="button" class="submit-btn save_thumb text-center" id="crop_disable">Crop</button>             
                <button type="button" class="btn default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>  