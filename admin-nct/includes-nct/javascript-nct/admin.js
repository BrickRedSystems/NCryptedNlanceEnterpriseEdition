function displayMessagePart(isAjax) {
	if(isAjax == true) {
		urlPath = siteName+'admin/includes/displayMessage.php';
		 $.ajax({
				type:"GET",
				url: urlPath,
				success:function(response){
					if(response!="")
					{
						$('#msgPart').html(response).show(1);
					};
				}
			});
	}
	
	$('#closeMsgPart').click(function(){
		  $('#msgPart').fadeOut(1000, "linear");		
	})
	
	setTimeout(function() {
		  $('#msgPart').fadeOut(2500, "linear");
	}, 9000);	
	
}
/* email validation function */
function is_validate(email) {
	var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
	if(reg.test(email) == false) {
		return 'false';
	}
}
function ajaxListingFunction(divID, module, action, id, value) {
	var myConfirm;
	var urlPath;
	if(action == 'delete') {
		myConfirm = confirm('Are you sure to delete?');
	}
	else 
		myConfirm = true;

	if(myConfirm == true) {
       $('#'+divID+'').html('<div style="margin:80px; text-align:center;"><div style="padding:18px;"><img src="'+siteName+'themes/images/loadingWait.gif" alt="" border="0" /><\/div><\/div>');
		
		urlPath = siteName+'admin/modules/'+module+'/ajax.'+module+'.php?action='+action+'&id='+id+'&value='+value;
        $.ajax({
            type:"GET",
            url: urlPath,
		    success:function(response){

                if(response!="") {
					displayMessagePart(true);
                    $('#'+divID+'').html(response);					
					$('#dg').datagrid('reload');
                };
            }
        });
	}
}
function noResultsFound(sel){
 var row = $(sel).datagrid('getRows',true);
 if(row.length == 0){
	//alert(first_column);
	totalCols = $(sel).datagrid('getColumnFields');
	cnt = 0;first_column = '';
	for(i=0;i<totalCols.length;i++){
		a = $(sel).datagrid('getColumnOption', totalCols[i]).hidden;
		if(!a){
			cnt+=1;
			if(cnt == 1)
				first_column = totalCols[i]
		}
	}
	var test2 = {};
	test2[first_column] = 'No result found'; 
	$(sel).datagrid('insertRow',{row: test2});
	$(sel).datagrid('mergeCells',{
		index:0,
		field: first_column,
		colspan:cnt
	});
	$('#datagrid-row-r1-2-0 td div').css({'text-align':'center'});

 }
}
/*function printObject(o) {
  var out = '';
  for (var p in o) {
	out += p + ': ' + o[p] + '\n';
  }
  alert(out);
}	
printObject(rows[0].education[0]['school']['name']);
*/