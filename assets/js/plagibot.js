(function (e, f) {
    var b = {};
    var g = function (a) {
        if(b[a]) { 
            f.clearInterval(b[a]);
            b[a] = null;
        };
    };

    e.fn.waitUntilExists = function (s, h, o, c) {
        if(o == null || o == undefined) o = 0;
        var d = e(s)
        var k = d.not(function () {
            return e(this).data("waitUntilExists.found");
        });

        if (h === "remove") {
            g(s);
        } else {
            if( typeof h !== 'undefined')
                k.each(h).data("waitUntilExists.found", !0);
                
            if (o && d.length) {
                g(s);
            }
            else if (!c) {
                b[s] = f.setInterval(function () {
                    d.waitUntilExists(s, h, o, !0);
                }, 500);
            }
        }
        return d
    }
})(jQuery, window);

jQuery(".edit-post-header-toolbar").waitUntilExists(".edit-post-header-toolbar",function(){
   jQuery("#editor").find(".edit-post-header-toolbar").append('<div id="plagibot_r3fg24_switch-mode" style="">\
			<button id="plagibot_r3fg24_switch-mode-button"  type="button" class="">\
				<span id="plagibot_r3fg24_preview-btn-text">Plagiarism Check</span>\
			</button>\
		</div>\
	');
})

jQuery(document).ready(function(e) {
	$ = jQuery;
	

	//button click event for classic editor
	$("#plagibot-metabox-btn").click(function(){
		$("[name='plagibot_button_click']").val(1);
		jQuery(this).parents('form').submit();
		jQuery(window).unbind('beforeunload.edit-post');
        $(this).attr('disabled',true).css({"background-color":"#3a7fffb8", "cursor" : "not-allowed"});
	})

	//click event for block editor
	$(document).on('click','#plagibot_r3fg24_switch-mode-button', function(){
		var wpEditor = wp.data.dispatch('core/editor');
    	wpEditor.savePost();
		redirectWhenSave();
        $(this).attr('disabled',true).css({"background-color":"#3a7fffb8", "cursor" : "not-allowed"});
	});


});

function redirectWhenSave(){
    $post_id = jQuery("#post_ID").val();
   //  jQuery("#wpp_send_new_post_notification").prop('checked',false);

    setTimeout(function () {
        if (wp.data.select('core/editor').isSavingPost()) {
          redirectWhenSave();
        } else {
          location.href = "?p="+ $post_id +"&action=plagibot-plagiarism-checker";
        }
    }, 300);
}
