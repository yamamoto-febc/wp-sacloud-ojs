var sacloudojs_connect_test = function($){return function() {
    var data = {
		action: "sacloudojs_connect_test",
		accesskey: $("#sacloudojs-accesskey").val(),
		secret: $("#sacloudojs-secret").val(),
		bucket: $("#sacloudojs-bucket").val(),
        useSSL: $("#sacloudojs-use-ssl").val()
    };

    $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: data,
        success: function (response) {
	    var res = $.parseJSON(response);

            $("html,body").animate({scrollTop: 0}, 1000);
            $("#sacloudojs-flash P").empty().append(res["message"]);
	    if(res["is_error"]) {
		$("#sacloudojs-flash").addClass("error");
	    } else {
		$("#sacloudojs-flash").removeClass("error");
	    }

	    $('#sacloudojs-flash').show();
        }
        //dataType: 'html'
    });
    $("#selupload_spinner").unbind("ajaxSend");
}}(jQuery);

var sacloudojs_resync = function() {
    if( ! confirm("It will may take a long time. Are you sure? ")) {
	return;
    }
};

jQuery(function() {
    jQuery("#sacloudojs-flash").hide();
});
