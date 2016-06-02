/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


jQuery(document).ready(function () {
    jQuery('.submit').hide();
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            'action': 'load_wp_login_nonce',
        },
        success: function (data) {
            jQuery('#loginform, #registerform').append(data);
            jQuery('.submit').show();
        },
        error: function (data) {
            console.log(data);
        }
    });
});