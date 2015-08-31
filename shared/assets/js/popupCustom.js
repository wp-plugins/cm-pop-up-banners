function safex(e, t) {
    return typeof e === "undefined" ? t : e
}
jQuery(document).ready(function () {
    jQuery("body").append(safex(popup_custom_data.content, ''));
    if(popup_custom_data.showMethod == 'always'){
        setCookie('ouibounceBannerShown', 'true', -1);
    }
    if(getCookie('ouibounceBannerShown') == ''){
        ouibounce = ouibounce(document.getElementById('ouibounce-modal'), {});
        setTimeout(function(){
            ouibounce.fire();
            if(popup_custom_data.showMethod ==  'once'){
                setCookie('ouibounceBannerShown', 'true', popup_custom_data.resetTime);
            }
        }, popup_custom_data.secondsToShow), 
        jQuery('body').on('click', function() {
            ouibounce.close();
        });

        jQuery('#ouibounce-modal #close_button').on('click', function() {
            ouibounce.close();
        });

        jQuery('#ouibounce-modal .modal').on('click', function(e) {
            e.stopPropagation();
        });
    }
});