<?php 
$ajax_page=false;
$header_panel=true;     
$reqAuth = true;
require_once '../../nct-includes/config-nct.php';
 
$totalAlbum = isset($_POST['txt_albums'])  ? abs($_POST['txt_albums']) : 0;  
$totalEP    = isset($_POST['txt_ep'])     ? abs($_POST['txt_ep']) : 0;

$getAdminEPPrice    = PRICE_PER_UPLOAD_EP;
$totalAlbumPrice    = getTotalAlbumPrice($totalAlbum);
$totalEPPrice       = ($getAdminEPPrice * $totalEP);
$subTotal           = ($totalAlbumPrice + $totalEPPrice); 
// For Vat 

 $vatPercent = 0;
        $vatPercentArr = calculateVatPercent(true);

        if(!empty($vatPercentArr['isEUCountry']) && $vatPercentArr['isEUCountry'] == 'y'){
            $vatPercent = (!empty($vatPercentArr['vatValue']) ? $vatPercentArr['vatValue'] : DEFAULT_VAT_PERCENTAGE);
        }
        $vatCountryId = (!empty($vatPercentArr['countryId']) ? $vatPercentArr['countryId'] : 0);
        $vatAmount =  ( (($vatPercent * $subTotal)/100));


$totalPrice         = ceil($subTotal + $vatAmount); 
$admin_vat          = $vatAmount; 

$finalAmount_old = 0;


$getUserAfterOneMonthDate = getUserAfterOneMonthDate($memberId,1);  
$currentDate = date('Y-m-d');
$checkUserPurchaseAnyplan = checkUserPurchaseAnyplan($memberId);
    if(strtotime($getUserAfterOneMonthDate) >=  strtotime($currentDate) ){    
        $_SESSION["msgType"] = disMessage(array(
            'type' => 'err',
            'var' => "Right now, You have no need to purchase membership plan. Because currently you have got free all services for first month. "
        ));
        redirectPage(SITE_URL);  
    }

    if(strtotime($getUserAfterOneMonthDate) <  strtotime($currentDate) ){
        if($checkUserPurchaseAnyplan == 0){

            $countUserTotalAlbums   = countUserTotalAlbumsOrEP($memberId,'a');
            $countUserTotalEP       = countUserTotalAlbumsOrEP($memberId,'e');

            $totalOldAddedAlbumPrice    = getTotalAlbumPrice($countUserTotalAlbums);
            $totalOldAddedEPPrice       = ($getAdminEPPrice * $countUserTotalEP); 
            $oldSubTotal                = ($totalOldAddedAlbumPrice + $totalOldAddedEPPrice);

            $vatPercent         = 0;
            $vatPercentArr      = calculateVatPercent(true);

            if(!empty($vatPercentArr['isEUCountry']) && $vatPercentArr['isEUCountry'] == 'y'){
                $vatPercent = (!empty($vatPercentArr['vatValue']) ? $vatPercentArr['vatValue'] : DEFAULT_VAT_PERCENTAGE);
            }
            $vatCountryId  = (!empty($vatPercentArr['countryId']) ? $vatPercentArr['countryId'] : 0);
            $vatAmount_old     =  ((($vatPercent * $oldSubTotal)/100));
            $content['vat_amount'] = $vatAmount_old;

            $finalAmount_old    = ceil($oldSubTotal + $vatAmount_old);


            $totalAlbum = (int)($totalAlbum + $countUserTotalAlbums);
            $totalEP    = (int)($totalEP    + $countUserTotalEP);
        }
    }        

$finalPrice = round($totalPrice + $finalAmount_old,2);   

if( ($totalAlbum > 0  || $totalEP > 0 ) && $memberId > 0 && $finalPrice > 0){ ?>
    <div align="center" style="width:100%;margin-top:30px;">
        <h1>Please wait, We are connecting to Paypal.... Please do not refresh the page.</h1>
    </div> 
    <?php $_SESSION['random_number'] = md5(rand(1, 50)); ?>
    <form name="frm_membership_plan" action="<?php echo PAYPAL_URL; ?>" method="post" id="frm_membership_plan">
        <input type="hidden" name="item_name" value="Membership plan purchase">
        <input type="hidden" name="cmd" value="_xclick-subscriptions">
        <input type="hidden" name="business" value="<?php echo PAYPAL_EMAIL; ?>">
        <input type="hidden" name="currency_code" value="GBP">
        <input type="hidden" name="no_shipping" value="1">
        <input type="image" src="http://www.paypal.com/en_GB/i/btn/x-click-but20.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" title="Make payments with PayPal - it's fast, free and secure!" style="display: none;">
       <!--  For Trial Period
        <input type="hidden" name="a1" value="<?php echo $finalPrice;?>">
        <input type="hidden" name="p1" value="1">
        <input type="hidden" name="t1" value="M"> 
        -->         
        <input type="hidden" name="a3" value="<?php echo $finalPrice;?>">
        <input type="hidden" name="p3" value="1">
        <input type="hidden" name="t3" value="M"> 
        <input type="hidden" name="src" value="1">
        <input type="hidden" name="sra" value="1">
        <!--<input type="hidden" name="srt" value="7">-->
        <input type="hidden" name="no_note" value="1">
        <input type="hidden" name="notify_url" value="<?php echo SITE_URL . 'notify-purchase-plan/'; ?>">
        <input type="hidden" name="return" value="<?php echo SITE_URL . 'success-purchase-plan/'; ?>">
        <input type="hidden" name="cancel_return" value="<?php echo SITE_URL . 'cancel-purchase-plan/'; ?>">
        <input type="hidden" name="custom" value="<?php echo 'memberId='.$memberId.',totalAlbum='.$totalAlbum.',totalEP='.$totalEP.',admin_vat='.$admin_vat.'   '; ?>">
        <input type="hidden" name="bn" value="NCryptedTechnologies_SP_EC" >
    </form>
    <script type="text/javascript">document.frm_membership_plan.submit();</script>
<?php 
 }
else{
   $_SESSION["msgType"] = disMessage(array(
                'type' => 'err',
                'var' => "Please fill all value properly."
    ));
    redirectPage(SITE_URL.'membership-plans/');  
}