(function( $ ) {
	'use strict';

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

})( jQuery );
