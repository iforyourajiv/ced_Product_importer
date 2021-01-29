(function ($) {
  "use strict";

  //Getting Dropdon value to Show Wp_list Table according to File Type
  $(document).ready(function () {
    $("#fileSelection").change(function () {
      var filename = $(this).val();
      $.ajax({
        url: ajax_fetch_file.ajaxurl,
        type: "POST",
        data: {
          action: "ced_fetch_file",
          nonce: ajax_fetch_file.nonce,
          filename: filename,
        },
        dataType: "html",
        beforeSend: function () {
          $("#loader").show();
        },
        success: function (data) {
          $("#displaydata").html(data);
          $("#loader").hide();
        },
      });
    });
  });

  $(document).on("click", "#import_product", function () {
    var id = $(this).attr("data-productId");
    let filename = $("#fileSelection").val();
    $.ajax({
      url: ajax_fetch_file.ajaxurl,
      type: "POST",
      data: {
        action: "ced_import_product",
        nonce: ajax_fetch_file.nonce,
        id: id,
        filename: filename,
      },
      beforeSend: function () {
        $("#loader").show();
      },
      success: function (data) {
        $("#loader").hide();
        if('success'== data){
          $("#message").append(
            "<div class='notice is-dismissible notice-success'> <p> Product Uploaded Successfully</p></div>"
          );
          setTimeout(function () {
            $("#message").fadeOut(2000);
          }, 2000);
        } else {
          $("#message").append(
            "<div class='notice is-dismissible notice-error'> <p> Error ! Product Not Uploaded Successfully</p></div>"
          );
          setTimeout(function () {
            $("#message").fadeOut(2000);
          }, 2000);
        }
       
       
      },
    });
  });

  $(document).on("click", "#doaction", function () {
    var test = $("#bulk-action-selector-top").val();
    var dataForBulk = [];
    let filename = $("#fileSelection").val();
    if (test == "bulk-import") {
      $("input[name='bulk-import[]']:checked").each(function () {
        dataForBulk.push($(this).val());
      });
      $.ajax({
        url: ajax_fetch_file.ajaxurl,
        type: "POST",
        data: {
          action: "ced_bulk_import_product",
          nonce: ajax_fetch_file.nonce,
          dataForBulk: dataForBulk,
          filename: filename,
        },
        beforeSend: function () {
          $("#loader").show();
        },
        success: function (data) {
          $("#loader").hide();
          if('success'== data){
            $("#message").append(
              "<div class='notice is-dismissible notice-success'> <p> Product Uploaded Successfully</p></div>"
            );
            setTimeout(function () {
              $("#message").fadeOut(2000);
            }, 2000);
          } else {
            $("#message").append(
              "<div class='notice is-dismissible notice-error'> <p> Error Product Not Uploaded Successfully</p></div>"
            );
            setTimeout(function () {
              $("#message").fadeOut(2000);
            }, 2000);
          }
        },
      });
    }
  });
})(jQuery);
