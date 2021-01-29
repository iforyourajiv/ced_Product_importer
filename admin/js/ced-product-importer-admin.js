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
        id: id,
        filename: filename,
      },
      beforeSend: function () {
        $("#loader").show();
      },
      success: function (data) {
        $("#loader").hide();
        $("#message").append(
          "<div class='notice is-dismissible notice-success'> <p>" +
            data +
            "</p></div>"
        );
        setTimeout(function () {
          $("#message").fadeOut(2000);
        }, 2000);
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
          dataForBulk: dataForBulk,
          filename: filename,
        },
        beforeSend: function () {
          $("#loader").show();
        },
        success: function (data) {
          $("#loader").hide();
          $("#message").append(
            "<div class='notice is-dismissible notice-success'> <p>" +
              data +
              "</p></div>"
          );
          setTimeout(function () {
            $("#message").fadeOut(2000);
          }, 2000);
        },
      });
    }
  });
})(jQuery);
