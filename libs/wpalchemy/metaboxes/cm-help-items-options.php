<div class="my_meta_control cm-help-items-options">

    <?php
    wp_print_styles('editor-buttons');

    ob_start();
    wp_editor('', 'content', array(
        'dfw'           => true,
        'editor_height' => 1,
        'tinymce'       => array(
            'resize'             => true,
            'add_unload_trigger' => false,
            'relative_urls'      => false,
            'remove_script_host' => false,
            'convert_urls'       => false
        ),
    ));
    $content = ob_get_contents();
    ob_end_clean();

    $args = array(
        'post_type'         => 'page',
        'show_option_none'  => CMPopUpFlyIn::__('None'),
        'option_none_value' => '',
    );

    add_filter('the_editor_content', 'wp_richedit_pre');
    $switch_class = 'tmce-active';

    $defaultWidgetType = CMPOPFLY_Settings::getOption(CMPOPFLY_Settings::OPTION_DEFAULT_WIDGET_TYPE);
    $widgetType = CMPOPFLY_Settings::getOptionConfig(CMPOPFLY_Settings::OPTION_DEFAULT_WIDGET_TYPE);
    $displayMethod = CMPOPFLY_Settings::getOptionConfig(CMPOPFLY_Settings::OPTION_DISPLAY_METHOD);
    $widgetDisplayMethod = CMPOPFLY_Settings::getOptionConfig(CMPOPFLY_Settings::OPTION_DISPLAY_METHOD);
    $widgetShape = CMPOPFLY_Settings::getOptionConfig(CMPOPFLY_Settings::OPTION_CUSTOM_WIDGET_SHAPE);
    $widgetShowEffect = CMPOPFLY_Settings::getOptionConfig(CMPOPFLY_Settings::OPTION_CUSTOM_WIDGET_SHOW_EFFECT);
    $widgetInterval = CMPOPFLY_Settings::getOptionConfig(CMPOPFLY_Settings::OPTION_CUSTOM_WIDGET_INTERVAL);
    $underlayType = CMPOPFLY_Settings::getOptionConfig(CMPOPFLY_Settings::OPTION_CUSTOM_WIDGET_UNDERLAY_TYPE);
    $selectedBanner = CMPOPFLY_Settings::getOptionConfig(CMPOPFLY_Settings::OPTION_CUSTOM_WIDGET_SELECTE_BANNER);
    $clicksCountMethod = CMPOPFLY_Settings::getOptionConfig(CMPOPFLY_Settings::OPTION_CUSTOM_WIDGET_CLICKS_COUNT_METHOD);
    if( isset($_GET['post']) )
    {
        $activityDates = get_post_meta($_GET['post'], CMPopUpFlyInShared::CMPOPFLY_CUSTOM_ACTIVITY_DATES_META_KEY);
    }
    if( !empty($activityDates) )
    {
        $activityDates = maybe_unserialize($activityDates[0]);
    }
    else
    {
        $activityDates = false;
    }
    ?>

    <div id="cmpopfly-options-group">

        <div class="cmpopfly-options-group">
            <label>Type</label>
            <p>
                <?php $mb->the_field('cm-campaign-widget-type'); ?>
                <select name="<?php $mb->the_name(); ?>" id="cm-campaign-widget-type">
                    <?php
                    $fieldValue = $mb->get_the_value();
//            echo '<option value="0" ' . selected('0', $fieldValue, false) . '>' . CMPopUpFlyIn::__('Default') . ' (' . $widgetType['options'][$defaultWidgetType] . ') </option>';
                    foreach($widgetType['options'] as $key => $value)
                    {
                        echo '<option value="' . $key . '" ' . selected($key, $fieldValue, false) . '>' . $value . '</option>';
                    }
                    ?>
                </select><br />
                <span class='field-info'>You can choose the different type for the current campaign.</span>
            </p>
            <label>Display method</label>
            <p>
                <span class="floatLeft">
                    <?php
                    $mb->the_field('cm-campaign-display-method');
                    $fieldValue = $mb->get_the_value();
                    if( empty($fieldValue) )
                    {
                        $fieldValue = $widgetDisplayMethod['default'];
                    }
                    foreach($widgetDisplayMethod['options'] as $key => $value)
                    {
                        echo '<input name="' . $mb->get_the_name() . '" type="radio" value="' . $key . '" ' . checked($key, $fieldValue, false) . ' class="campaign-display-method">' . $value . "<br />";
                    }
                    ?>
                </span>
                <span id='campaign-selected-banner-panel' class="floatLeft" style="display: none;">
                    Selected banner:
                    <?php $mb->the_field('cm-campaign-widget-selected-banner'); ?>
                    <input type="hidden" id="campaign-selected-banner-back" value="<?php echo $fieldValue = $mb->get_the_value() ?>"/>
                    <select name="<?php $mb->the_name(); ?>" id="cm-campaign-widget-selected-banner"></select>
                </span>
            <div class="clear"></div>
            <br />
            <span class='field-info'>You can choose the different display method for the current campaign.</span>
            </p>
            <label>Width</label>
            <p>
                <?php $mb->the_field('cm-campaign-widget-width'); ?>
                <input type="text" name="<?php $mb->the_name(); ?>" placeholder="250px" value="<?php echo $metabox->get_the_value(); ?>"/>
                <span class='field-info'>campaign width. If blank defaults to 250px. Please input value in pixels.</span>
            </p>
            <label>Height</label>
            <p>
                <?php $mb->the_field('cm-campaign-widget-height'); ?>
                <input type="text" name="<?php $mb->the_name(); ?>" placeholder="350px" value="<?php echo $metabox->get_the_value(); ?>"/>
                <span class='field-info'>campaign height. If blank defaults to 350px. Please input value in pixels.</span>
            </p>
            <label>Background color</label>
            <p>
                <?php $mb->the_field('cm-campaign-widget-background-color'); ?>
                <input type="text" name="<?php $mb->the_name(); ?>" placeholder="#ffffff" value="<?php echo $metabox->get_the_value(); ?>"/>
                <span class='field-info'>Campaign background color. Please enter it in hexadecimal color format (eg. #abc123). If blank defaults to #ffffff (white).</span>
            </p>
            <label>Shape</label>
            <p>
                <?php $mb->the_field('cm-campaign-widget-shape'); ?>
                <select name="<?php $mb->the_name(); ?>">
                    <?php
                    $fieldValue = $mb->get_the_value();
                    if( empty($fieldValue) )
                    {
                        $fieldValue = $widgetShape['default'];
                    }
                    foreach($widgetShape['options'] as $key => $value)
                    {
                        echo '<option value="' . $key . '" ' . selected($key, $fieldValue, false) . '>' . $value . '</option>';
                    }
                    ?>
                </select>
                <br />
                <span class='field-info'>You can choose the different shape for the current campaign.</span>
            </p>
        </div>

        <div class="cmpopfly-options-group">

            <?php $mb->the_field('cm-campaign-widget-show-effect'); ?>
            <input type="hidden" name="<?php $mb->the_name(); ?>" placeholder="0" value="<?php echo $mb->get_the_value(); ?>"/>

            <span id="underlayTypeContainer" style="display: none;">
                <label>Underlay type</label>
                <p>
                    <?php $mb->the_field('cm-campaign-widget-underlay-type'); ?>
                    <select name="<?php $mb->the_name(); ?>">
                        <?php
                        $fieldValue = $mb->get_the_value();
                        if( empty($fieldValue) )
                        {
                            $fieldValue = $underlayType['default'];
                        }
                        foreach($underlayType['options'] as $key => $value)
                        {
                            echo '<option value="' . $key . '" ' . selected($key, $fieldValue, false) . '>' . $value . '</option>';
                        }
                        ?>
                    </select>
                    <br />
                    <span class='field-info'>You can choose the different underlay type for current campaign.</span>
                </p>
            </span>
        </div>

        <div class="cmpopfly-options-group">
            <label>Show on every page</label>
            <p>
                <?php $mb->the_field('cm-campaign-show-allpages'); ?>
                <input type="hidden" name="<?php $mb->the_name(); ?>" value="0"/>
                <input type="checkbox" name="<?php $mb->the_name(); ?>" value="1" <?php checked('1', $metabox->get_the_value()); ?> class="<?php $mb->the_name(); ?>"/>
                <span class='field-info'>If this checkbox is selected then this campaign will be displayed on each post and page of your website</span>
            </p>
        </div>
    </div>

    <p class="meta-save"><button type="submit" class="button-primary" name="save"><?php _e('Update'); ?></button></p>

</div>