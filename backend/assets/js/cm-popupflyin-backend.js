var CM_popupflyin_backend = {};
plugin_url = window.cm_popupflyin_backend.plugin_url;

(function ($) {

    $('.cm-help-items-metacontrol').on('click', '.cm-template-control .cm-apply-template', function (e) {

        if (!confirm('Warning! This will change the current content of the editor. Are you sure?')) {
            return false;
        }

        var loadedTemplate = $(this).parents('.cm-template-control').find('select[name*="cm_load_template"]').val();
        var editor = $(this).parents('.customEditor').find('.wp-editor-area');
        var title = $(this).parents('.group-inside').find('.cm-help-item-title');
        var tinyMCEeditor = tinymce.get(editor.attr('id'));

        var data = {
            'action': 'cm_popupflyin_template_api',
            'template': loadedTemplate
        };

        $.post(window.cm_popupflyin_backend.ajaxurl, data, function (response) {

            if (typeof response !== 'undefined')
            {
                if (response.content.length)
                {
                    tinyMCEeditor.focus();
                    tinyMCEeditor.setContent(response.content);
                }
                if (response.title.length)
                {
                    title.val(response.title);
                }
            }

        }, 'json');

        return false;
    });
    $('#user_show_method-flying-bottom').on('change', function (e) {
        var resetField = $('#resetFloatingBottomBannerCookieContainer');
        if (this.value == 'once') {
            resetField.show();
        } else {
            resetField.hide();
        }
    }).change();
    $('#cm-campaign-widget-type').on('change', function (e) {
        var underlayField = $('#underlayTypeContainer');
        if (this.value == 'popup') {
            underlayField.show();
        } else {
            underlayField.hide();
        }
    }).change();
    /*
     * filling selected banner select and validation
     */
    jQuery(".campaign-display-method").on('change', function () {
        if (jQuery(".campaign-display-method:checked").val() === 'selected') {
            jQuery('#campaign-selected-banner-panel').show();
        } else {
            jQuery('#campaign-selected-banner-panel').hide();
        }
    }).change();
    jQuery(document.body).on('wpa_copy', function () {
        jQuery('#campaign-selected-banner-back').val(jQuery('#cm-campaign-widget-selected-banner').val());
        fillSelectedBannerDropdown();
    });
    jQuery(document.body).on('wpa_delete', function () {
        jQuery('#campaign-selected-banner-back').val(jQuery('#cm-campaign-widget-selected-banner').val());
        fillSelectedBannerDropdown();
    });
    function fillSelectedBannerDropdown () {
        bannersArray = jQuery('.wpa_group-cm-help-item-group');
        var lastSelectedBanner = jQuery('#campaign-selected-banner-back').val();
        var selectedBanner = jQuery('#cm-campaign-widget-selected-banner');
        selectedBanner.find('option').remove();
        for(i = 0, ii = bannersArray.length - 1; i < ii; i++){
            selectedBanner.append('<option value="' + i + '">Banner ' + (i + 1) + '</option>');
        }
        if (bannersArray.length - 1 < selectedBanner) {
            selectedBanner = '';
        }
        selectedBanner.val(lastSelectedBanner);
    }
    fillSelectedBannerDropdown();
    jQuery('p.meta-save button[name="save"]').on('click', function (e) {
        if (jQuery(".campaign-display-method:checked").val() === 'selected') {
            if (!jQuery('#cm-campaign-widget-selected-banner').val()) {
                e.preventDefault();
                alert('Selected banner field is empty!\nPlease select banner!');
            }
        }
    });

})(jQuery);