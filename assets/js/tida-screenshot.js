jQuery(document).ready(function(){
    jQuery(".tida_screenshot_form").on('submit', function(e){
        e.preventDefault();
        var url = jQuery(this).find('input[type="text"]').val();
        jQuery.ajax({
            method: 'POST',
            url: tida_screenshot_params.ajax_url,
            data: { action : 'get_tida_screenshot', url : url },
            dataType: 'json',
            beforeSend: function() {
                jQuery('.screenshot_result img').attr('src', '');
                jQuery('.screenshot_msg').html('<span class="text_blink">' + tida_screenshot_params.please_text + '</span>');
            },
            success: function(response) {
                jQuery('.screenshot_result img').attr('src', response.src);
                jQuery('.screenshot_msg').html(response.msg);
            },
            error: function(error) {
                console.log(error);
            }
        });
    });
});