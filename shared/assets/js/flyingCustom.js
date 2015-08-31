if(flyin_custom_data.showMethod == 'always'){
    setCookie('ouibounceBannerBottomShown', 'true', -1);
}
if(getCookie('ouibounceBannerBottomShown') == ''){
    var _flyingBottomOui = flyingBottomAd({
        htmlContent: '<div id=\"flyingBottomAd\"><span class=\"flyingBottomAdClose  popupflyin-close-button\"></span><div class=\"popupflyin-clicks-area\">' + flyin_custom_data.content + '</div></div>',
        delay: flyin_custom_data.secondsToShow
    });
    if(flyin_custom_data.showMethod ==  'once'){
        setCookie('ouibounceBannerBottomShown', 'true', flyin_custom_data.resetTime);
    }
}