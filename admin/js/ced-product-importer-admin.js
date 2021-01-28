(function( $ ) {
	'use strict';

	//Getting Dropdon value to Show Wp_list Table according to File Type
	$(document).ready(function(){
		$('#fileSelection').change(function() {
			var filename=$(this).val();  
			$.ajax({
				url: ajax_fetch_file.ajaxurl,
				type: 'POST',
				data:{
				  action: 'fetch_file',
				  filename:filename
				},
				dataType:"html",
				success: function( data ){
				  $("#displaydata").html(data);
				}
			  });
		})

	})

	$(document).on('click','#import_product',function(){
		var id=$(this).attr("data-productId");
		let filename=$('#fileSelection').val();
		$.ajax({
			url: ajax_fetch_file.ajaxurl,
			type: 'POST',
			data:{
			  action: 'import_product',
			  id:id,
			  filename:filename
			},
			success: function( data ){
				console.log(data);
				alert(data);
				// window.location.href= 'admin.php?page=import-product';    
			}
		  });
	});

	$(document).on('click','#doaction',function(){
		var test=$("#bulk-action-selector-top").val();
		var dataForBulk=[];
		let filename=$('#fileSelection').val();
		if(test=='bulk-import'){
			$("input[name='bulk-import[]']:checked").each( function () {
				dataForBulk.push($(this).val());
			});
			$.ajax({
				url: ajax_fetch_file.ajaxurl,
				type: 'POST',
				data:{
				  action: 'bulk_import_product',
				  dataForBulk:dataForBulk,
				  filename:filename
				},
				success: function( data ){
					alert(data);
				}
			  });
		}
	})

})( jQuery );
