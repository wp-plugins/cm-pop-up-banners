
(function ($) {

    $(document).ready(function ($) {

        var data = {
            'action': 'cm_onboarding_widget',
            'post_id': cmpopfly_data.post_id,
            'url': location.href,
        };

        if(typeof cmpopfly_data.help_id !== 'undefined')
        {
            data['help_id'] = cmpopfly_data.help_id;
        }

        var body = $('body');

        var init_widget = function () {

            $('#cmpopfly-search').fastLiveFilter('#cmpopfly-widget-content', {
                timeout: 200,
                'nothing_found': cmpopfly_data.nothing_found
            });

        };

        $.ajax({
            url: cmpopfly_data.ajaxurl,
            data: data,
            method: 'post'
        }).done(function (response) {
            var lib_url, stylesheet, widget_container;
            widget_container = $('<div id="cmpopfly-widget-container-wrapper" class="' + cmpopfly_data.side + ' ' + response.type + '"></div>').appendTo(body);

            if (!response || typeof response === 'undefined' || typeof response.body === 'undefined')
            {
                return;
            }

            widget_container.append(response.body);

            $('#cmpopfly-widget-container-wrapper .cmpopfly-btn-open').click(function () {
                $('#cmpopfly-widget-container').toggleClass('show');
                $('#cmpopfly-widget-container-wrapper .cmpopfly-btn-clse').toggleClass('show');
                return false;
            });

            $('#cmpopfly-widget-container-wrapper .cmpopfly-btn-close').click(function () {
                $('#cmpopfly-widget-container').toggleClass('show');
                $('#cmpopfly-widget-container-wrapper .cmpopfly-btn-open').toggleClass('show');
            });

            lib_url = cmpopfly_data.js_path + 'widget.' + response.type + '.js';
            $.getScript(lib_url);

            lib_url = cmpopfly_data.js_path + 'jquery.search.js';
            $.getScript(lib_url, init_widget);

            stylesheet = document.createElement('link');
            stylesheet.href = cmpopfly_data.css_path + 'base.css';
            stylesheet.rel = 'stylesheet';
            stylesheet.type = 'text/css';
            document.getElementsByTagName('head')[0].appendChild(stylesheet);

            stylesheet = document.createElement('link');
            stylesheet.href = cmpopfly_data.css_path + 'widget.' + response.type + '/' + response.theme + '.css';
            stylesheet.rel = 'stylesheet';
            stylesheet.type = 'text/css';
            document.getElementsByTagName('head')[0].appendChild(stylesheet);
        }).fail( function (response){
            console.log(response);
        });

    });

})(jQuery);