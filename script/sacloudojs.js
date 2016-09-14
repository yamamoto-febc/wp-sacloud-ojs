var sacloudojs_connect_test = function ($) {
    return function () {
        var data = {
            action: "sacloudojs_connect_test",
            accesskey: $("#sacloudojs-accesskey").val(),
            secret: $("#sacloudojs-secret").val(),
            bucket: $("#sacloudojs-bucket").val(),
            useSSL: $("#sacloudojs-use-ssl").val(),
            useCache: $("#sacloudojs-use-cache").val()
        };

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: data,
            success: function (response) {
                var res = $.parseJSON(response);

                $("html,body").animate({scrollTop: 0}, 1000);
                $("#sacloudojs-flash p").empty().append(res["message"]);
                if (res["is_error"]) {
                    $("#sacloudojs-flash").addClass("notice-error").removeClass("notice-success");
                } else {
                    $("#sacloudojs-flash").removeClass("notice-error").addClass("notice-success");
                }

                $('#sacloudojs-flash').fadeIn().queue(function () {
                    setTimeout(function () {
                        $('#sacloudojs-flash').dequeue();
                    }, 5000);
                });
                $('#sacloudojs-flash').fadeOut();
                //dataType: 'html'
            }
        });
        $("#selupload_spinner").unbind("ajaxSend");
    }
}(jQuery);

jQuery(function () {
    jQuery("#sacloudojs-flash").hide();
});
