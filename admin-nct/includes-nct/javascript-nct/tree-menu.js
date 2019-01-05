$(document).ready(function (){
	function closeMenuTab() {	
		$('#treeMenu ul li:has("div")').find('div').slideUp('slow');	
		$('#treeMenu ul li:has("div")').find('span:first').addClass('closed');
		$('#treeMenu ul li:has("div")').find('span:first').removeClass('opened');
		$('#treeMenu ul .clf').show();
	}

	 closeMenuTab();
	 $('#treeMenu li:has("div")').click (function (){ 
		spanCl=$(this).find('span:first').attr('class');
		closeMenuTab();			
		if(spanCl!='closed opened') {
			$(this).find('div:first').slideToggle('slow');
			$(this).find('span:first').toggleClass('opened');
		}
	 });
	 
});