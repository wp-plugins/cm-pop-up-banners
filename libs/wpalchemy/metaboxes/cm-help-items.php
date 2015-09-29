<div class="my_meta_control cm-help-items-metacontrol">

    <p class="warning"><?php _e('These textareas will NOT work without javascript enabled.'); ?></p>

    <!--<a href="#" class="dodelete-cm-help-item-group button"><?php _e('Remove All', ''); ?></a>-->
    <a href="#" class="toggleAll button"><?php _e('Toggle All', ''); ?></a>

    <p><?php _e('Add new Advertisement Items by using the "Add Advertisement Item" button.  Rearrange the order by dragging and dropping.', ''); ?></p>

    <?php
    wp_print_styles('editor-buttons');

    ob_start();
    wp_editor('', 'content', array(
        'dfw'           => true,
        'editor_height' => 1,
        'tinymce'       => array(
            'resize'             => true,
            'add_unload_trigger' => false,
            'relative_urls' => false,
            'remove_script_host' => false,
            'convert_urls' => false
        ),
    ));
    $content = ob_get_contents();
    ob_end_clean();

    /*
     * List of the templates
     */
    $templates = CMPopUpFlyInBackend::getTemplatesList();

	global $wp_version;
	if ( version_compare( $wp_version, '4.3', '<' ) ) {
		add_filter( 'the_editor_content', 'wp_richedit_pre' );
	} else {
		add_filter( 'the_editor_content', 'format_for_editor' );
	}
	$switch_class = 'tmce-active';
    ?>

    <?php while($mb->have_fields_and_multi('cm-help-item-group')): ?>

        <?php $mb->the_group_open(); ?>

        <div class="group-wrap <?php echo $mb->get_the_value('toggle_state') ? ' closed' : ''; ?>" >

            <?php $mb->the_field('toggle_state'); ?>
            <?php // @ TODO: toggle should be user specific ?>
            <input type="checkbox" name="<?php $mb->the_name(); ?>" value="1" <?php checked(1, $mb->get_the_value()); ?> class="toggle_state hidden" />

            <div class="group-control dodelete" title="<?php _e('Click to remove "Advertisement Item"', ''); ?>"></div>
            <div class="group-control toggle" title="<?php _e('Click to toggle', ''); ?>"></div>

            <?php $mb->the_field('title'); ?>

            <?php // need to html_entity_decode() the value b/c WP Alchemy's get_the_value() runs the data through htmlentities() ?>
            <h3 class="handle"><?php echo $mb->get_the_value() ? 'Advertisement Item - ' . substr(strip_tags(html_entity_decode($mb->get_the_value())), 0, 30) : 'Advertisement Item'; ?></h3>
            <?php $mb->the_field('banner-uuid'); ?>
            <input type="hidden" name="<?php $mb->the_name();?>" value="<?php echo $mb->get_the_value(); ?>">
            <div class="group-inside">

                <?php $mb->the_field('textarea'); ?>

                <p class="warning update-warning"><?php _e('Sort order has been changed.  Remember to save the post to save these changes.'); ?></p>

                <label>Content</label>

                <div class="customEditor wp-core-ui wp-editor-wrap <?php echo $switch_class; ?>">

                    <div class="wp-editor-tools hide-if-no-js">

                        <div class="wp-media-buttons custom_upload_buttons">
                            <?php do_action('media_buttons'); ?>
                        </div>

                        <div class="wp-editor-tabs">
                            <a data-mode="tmce" class="wp-switch-editor switch-tmce"><?php _e('Visual'); ?></a>
                            <a data-mode="html" class="wp-switch-editor switch-html"> <?php _ex('Text', 'Name for the Text editor tab (formerly HTML)'); ?></a>
                        </div>

                    </div><!-- .wp-editor-tools -->

                    <div class="wp-editor-container">
                        <textarea class="wp-editor-area" rows="10" cols="50" name="<?php $mb->the_name(); ?>" rows="3">
                            <?php echo esc_html(apply_filters('the_editor_content', html_entity_decode($mb->get_the_value()))); ?>
                        </textarea>
                    </div>
                    <p><span><?php _e('Enter in the content'); ?></span></p>

                    <div class="clear"></div>
                </div>

            </div><!-- .group-inside -->

        </div><!-- .group-wrap -->

        <?php $mb->the_group_close(); ?>
    <?php endwhile; ?>

    <p><a href="#" class="docopy-cm-help-item-group button"><span class="icon add"></span><?php _e('Add Advertisement Item', ''); ?></a></p>

    <p class="meta-save"><button type="submit" class="button-primary" name="save"><?php _e('Update'); ?></button></p>
</div>