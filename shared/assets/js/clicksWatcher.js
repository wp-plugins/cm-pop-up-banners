window.CMPopupFlyinClicksAmountCounter = 0;
window.CMPopupFlyinAjaxRequestSent = false;
function CMregisterPopupFlyinWatchers(){
    jQuery('.popupflyin-clicks-area').on('click', function(event, element){
       window.CMPopupFlyinClicksAmountCounter++;
       if(clicks_watcher_data.countingMethod == 'one' && !window.CMPopupFlyinAjaxRequestSent){
           CMsendAjaxClickData();
           window.CMPopupFlyinAjaxRequestSent = true;
       }
    });
}
function CMpopupClosed(){
    if(clicks_watcher_data.countingMethod == 'all'){
        CMsendAjaxClickData();
    }
}
function CMsendAjaxClickData(clicksCount){
    jQuery(document).ready( function($) {
        if(window.CMPopupFlyinClicksAmountCounter > 0){
            $.ajax({
                'url': clicks_watcher_data.ajaxClickUrl,
                'type': 'post',
                'data': {
                    campaign_id: clicks_watcher_data.campaignId,
                    banner_id: clicks_watcher_data.bannerId,
                    amount: window.CMPopupFlyinClicksAmountCounter,
                }
            });
        }
    });
}