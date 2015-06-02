<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main backend class file/controller.
 * What it does:
 * - shows/adds/edits plugin settings
 * - adding metaboxes to admin area
 * - adding admin scripts
 * - other admin area only things
 *
 * How it works:
 * - everything is hooked up in the constructor
 */
class CMPopUpFlyInBackend {

    public static $calledClassName;
    protected static $instance = NULL;
    protected static $cssPath = NULL;
    protected static $jsPath = NULL;
    protected static $viewsPath = NULL;
    public static $settingsPageSlug = NULL;
    public static $proPageSlug = NULL;
    public static $aboutPageSlug = NULL;
    public static $exportPageSlug = NULL;
    public static $exportNonceAction = 'cm-popupflyin-export';
    public static $exportNonceField = 'cm-popupflyin-export-nonce';
    protected static $bannersDataArray = array();
    public static $isPreview = false;
    public static $customMetaboxes = array();

    /**
     * Main Instance
     *
     * Insures that only one instance of class exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 1.0
     * @static
     * @staticvar array $instance
     * @return The one true CMPopUpFlyIn
     */
    public static function instance() {
        $class = __CLASS__;
        if (!isset(self::$instance) && !( self::$instance instanceof $class )) {
            self::$instance = new $class;
        }
        return self::$instance;
    }

    public function __construct() {
        if (empty(self::$calledClassName)) {
            self::$calledClassName = __CLASS__;
        }
        self::$cssPath = CMPOPFLY_PLUGIN_URL . 'backend/assets/css/';
        self::$jsPath = CMPOPFLY_PLUGIN_URL . 'backend/assets/js/';
        self::$viewsPath = CMPOPFLY_PLUGIN_DIR . 'backend/views/';

        self::$settingsPageSlug = CMPOPFLY_SLUG_NAME . '-settings';
        self::$aboutPageSlug = CMPOPFLY_SLUG_NAME . '-about';
        self::$proPageSlug = CMPOPFLY_SLUG_NAME . '-pro';

        /*
         * Metabox SECTION
         */
        require_once CMPOPFLY_PLUGIN_DIR . 'libs/wpalchemy/wpalchemy.php';
        require_once CMPOPFLY_PLUGIN_DIR . 'libs/ip2locationlite.php';

        self::$customMetaboxes[] = new WPAlchemy_MetaBox(array
            (
            'id' => '_cm_advertisement_items',
            'title' => 'Advertisement Items',
            'template' => CMPOPFLY_PLUGIN_DIR . 'libs/wpalchemy/metaboxes/cm-help-items.php',
            'types' => array(CMPopUpFlyInShared::POST_TYPE),
            'init_action' => array(self::$calledClassName, 'metaInit'),
            'save_filter' => array(self::$calledClassName, 'metaRepeatingSaveFilter'),
        ));

        self::$customMetaboxes[] = new WPAlchemy_MetaBox(array
            (
            'id' => '_cm_advertisement_items_custom_fields',
            'title' => 'Campaign - Options',
            'template' => CMPOPFLY_PLUGIN_DIR . 'libs/wpalchemy/metaboxes/cm-help-items-options.php',
            'types' => array(CMPopUpFlyInShared::POST_TYPE)
        ));

        add_filter('query_vars', array(self::$calledClassName, 'addQueryVars'));
        add_action('parse_query', array(self::$calledClassName, 'processQueryArg'));

        /*
         * Recreate the default filters on the_content
         * this will make it much easier to output the meta content with proper/expected formatting
         */
        add_filter('meta_content', 'wptexturize');
        add_filter('meta_content', 'convert_smilies');
        add_filter('meta_content', 'convert_chars');
        add_filter('meta_content', 'wpautop');
        add_filter('meta_content', 'shortcode_unautop');
        add_filter('meta_content', 'prepend_attachment');
        add_filter('meta_content', 'do_shortcode');

        /*
         * Metabox SECTION END
         */

        add_filter('mce_css', array(self::$calledClassName, 'plugin_mce_css'));

        add_action('init', array(self::$calledClassName, 'createPostType'));
        add_action('current_screen', array(self::$calledClassName, 'handlePost'));
        add_action('current_screen', array(self::$calledClassName, 'handleExport'));
        add_action('save_post', array(self::$calledClassName, 'updateLinkedTemplates'));

        add_action('admin_menu', array(self::$calledClassName, 'addMenu'));
//        add_filter('post_row_actions',array(self::$calledClassName, 'addRowAction'), 10, 2);
        add_filter('page_row_actions', array(self::$calledClassName, 'addRowAction'), 10, 2);

        add_filter('manage_edit-' . CMPopUpFlyInShared::POST_TYPE . '_columns', array(self::$calledClassName, 'editScreenColumns'));
        add_filter('manage_' . CMPopUpFlyInShared::POST_TYPE . '_posts_custom_column', array(self::$calledClassName, 'editScreenColumnsContent'), 10, 2);
        /*
         * Preview
         */
        add_action('wp_ajax_cm_popupflyin_preview', array(self::$calledClassName, 'outputPreview'));

        add_filter('post_type_link', array(self::$calledClassName, 'replacePostLink'), 999, 4);
        add_filter('plugins_loaded', array(self::$calledClassName, 'stop_ckeditor'));

        /*
         * Metaboxes
         */
        add_action('save_post', array(self::$calledClassName, 'savePostdata'));
        add_action('update_post', array(self::$calledClassName, 'savePostdata'));

        /*
         * Notice
         */
        add_action('admin_notices', array(self::$calledClassName, 'showMessage'));
        /*
         * Ajax handlers
         */
        add_action('wp_trash_post', array(self::$calledClassName, 'clearPinnedPostsOnCampaignDelete'));

        add_action('post_type_labels_'.CMPopUpFlyInShared::POST_TYPE, array(self::$calledClassName, 'customListLabels'));
    }

    public static function editScreenColumns($columns) {
        $baseColumns = $columns;
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'title' => __('Item name'),
            'global' => __('Global'),
            'date' => __('Date'),
        );

        return $columns;
    }

    public static function editScreenColumnsContent($column, $post_id) {
        switch ($column) {
            case 'global' :
                $helpItemMeta = CMPopUpFlyInBackend::prepareHelpItemData($post_id, FALSE);
                $status = CMPopUpFlyIn::__('No');
                if (isset($helpItemMeta['cm-campaign-show-allpages']) && $helpItemMeta['cm-campaign-show-allpages']) {
                    $status = CMPopUpFlyIn::__('Yes');
                }
                echo $status;
                break;
        }
    }

    public static function stop_ckeditor($plugins) {
        $get = $_GET;
        if (!empty($get['post_type']) || !empty($get['post'])) {
            $postType = null;

            if (!empty($get['post_type'])) {
                $postType = $get['post_type'];
            } elseif (!empty($get['post'])) {
                $postType = get_post_type($get['post']);
            }

            if ($postType && $postType == 'cm-help-item') {
                remove_action('init', 'ckeditor_init');
            }
        }
        return $plugins;
    }

    public static function replacePostLink($post_link, $post, $leavename, $sample) {
        if ($post->post_type == CMPopUpFlyInShared::POST_TYPE) {
            return admin_url('admin-ajax.php?action=cm_popupflyin_preview&campaign_id=' . $post->ID);
        }
        return $post_link;
    }

    public static function addQueryVars($vars) {
        $vars[] = "post_id";
        $vars[] = "cm-action";
        return $vars;
    }

    /**
     * Create custom post type
     */
    public static function createPostType() {
        $args = array(
            'label' => 'Campaign',
            'labels' => array(
                'add_new_item' => 'Add New Campaign',
                'add_new' => 'Add New Campaign',
                'edit_item' => 'Edit Campaign Item',
                'view_item' => 'View Campaign Item',
                'singular_name' => 'Advertisement Item',
                'name' => CMPOPFLY_PLUGIN_NAME,
                'menu_name' => 'Campaigns'
            ),
            'description' => 'CM Campaigns',
            'map_meta_cap' => true,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'public' => true,
            'show_ui' => true,
            'show_in_admin_bar' => true,
            'show_in_menu' => CMPOPFLY_SLUG_NAME,
            '_builtin' => false,
            'capability_type' => 'post',
            'hierarchical' => true,
            'has_archive' => false,
            'rewrite' => array('slug' => CMPopUpFlyInShared::POST_TYPE, 'with_front' => false, 'feeds' => false, 'feed' => false),
            'query_var' => true,
            'supports' => array('title', 'revisions'),
        );

        register_post_type(CMPopUpFlyInShared::POST_TYPE, $args);

        $args2 = array(
            'label' => 'Help Item Template',
            'labels' => array(
                'add_new_item' => 'Add New Help Item Template',
                'add_new' => 'Add Help Item Template',
                'edit_item' => 'Edit Help Item Template',
                'view_item' => 'View Help Item Template',
                'singular_name' => 'Help Item Template',
                'name' => CMPOPFLY_PLUGIN_NAME,
                'menu_name' => 'Help Item Templates'
            ),
            'description' => 'CM Help Item Templates',
            'map_meta_cap' => true,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'public' => false,
            'show_ui' => true,
            'show_in_admin_bar' => true,
            'show_in_menu' => false,
            '_builtin' => false,
            'capability_type' => 'post',
            'hierarchical' => true,
            'has_archive' => false,
            'rewrite' => array('slug' => CMPopUpFlyInShared::POST_TYPE_TEMPLATE, 'with_front' => false, 'feeds' => false, 'feed' => false),
            'query_var' => true,
            'supports' => array('title', 'editor', 'revisions'),
        );

        register_post_type(CMPopUpFlyInShared::POST_TYPE_TEMPLATE, $args2);
    }

    /**
     * Checks for an action during the query parsing
     */
    public static function processQueryArg() {
        $postType = get_query_var('post_type');
        $postId = get_query_var('post_id');
        $action = get_query_var('cm-action');

        if (is_admin() && $postType == CMPopUpFlyInShared::POST_TYPE && $postId && $action) {
            switch ($action) {
                case 'export':
                    self::exportHelpItems($postId);
                    break;

                default:
                    break;
            }

            $redirectUrl = esc_url(add_query_arg(array('post_type' => CMPopUpFlyInShared::POST_TYPE), admin_url('edit.php')));
            wp_redirect($redirectUrl);
            exit();
        }
    }

    public static function getWidgetForPage($postId, $type = 'widget') {
            if(empty($postId)){
                return false;
            }
            $campaignId = $urlCampaign = $globalCampaign = false;
            /*
             * checks if page/post config is not blocking the campaign
             */

            if(CMPopUpFlyInShared::checkIfNotBlocked($postId)){
                return false;
            }

            /*
             * if page has campaign assigned
             */
            $Campaign = CMPopUpFlyInShared::getPostHelpItem($postId);
            /*
             * if not is campaign matched to url pattern
             */
            $urlCampaign = CMPopUpFlyInShared::getHelpItemMatchingUrl(get_permalink());
            /*
             * if not is global campaign set
             */
            $globalCampaign= CMPopUpFlyInShared::getGlobalHelpItem();

            if ($Campaign === FALSE || $Campaign === '-1' || $Campaign === '' || $Campaign == '0') {
                if (!empty($urlCampaign)) {
                    $campaignId = $urlCampaign;
                } else {
                    if (!empty($globalCampaign)) {
                        $campaignId = $globalCampaign;
                    }else{
                        /*
                        * No campaign - not defined
                        */
                       $campaignId = FALSE;
                    }

                }
            }else{
                $campaignId = $Campaign;
            }
        if ($campaignId === FALSE) {
            /*
             * no campaigns
             */
            return false;
        }
        $post = get_post($campaignId);
        if (!$post || $post->post_type !== CMPopUpFlyInShared::POST_TYPE || $post->post_status == 'auto-draft' || wp_is_post_revision($campaignId) || wp_is_post_autosave($campaignId)) {
            /*
             * Wrong post type!
             */
            return false;
        }
        $additionalData = array('campaign_id' => $campaignId);
        if($type == 'widget'){
            $postMeta = get_post_meta($campaignId);
            $postMeta = array_merge($postMeta, $additionalData);
            return $postMeta;
        }elseif($type == 'campaign'){
            return $post;
        }
        return false;
    }
    /**
     * Outputs the preview
     */
    public static function outputPreview() {

        $helpItemPostId = filter_input(INPUT_GET, 'campaign_id');

        if ($helpItemPostId) {
            $post = get_post($helpItemPostId);
            if (!$post || $post->post_type !== CMPopUpFlyInShared::POST_TYPE || $post->post_status == 'auto-draft' || wp_is_post_revision($helpItemPostId) || wp_is_post_autosave($helpItemPostId)) {
                echo 'Wrong post type!';
                die();
            }
        } else {
            echo 'No "campaign_id" parameter!';
            die();
        }

        self::$isPreview = true;
        CMPopUpFlyInShared::getWidgetOutput();

        $linkPath = CMPOPFLY_PLUGIN_DIR . 'backend/views/preview.phtml';
        $cssPath = self::$cssPath;

        if (file_exists($linkPath)) {
            ob_start();
            require $linkPath;
            $content = ob_get_contents();
            ob_end_clean();
            echo $content;
        }
        die();
    }

    /**
     * Returns the name of the view from the $templateId
     */
    public static function getTemplateName($templateId) {
        return $templateId;
    }

    /**
     * Returns the content of the template based on ID
     */
    public static function getTemplate($templateId) {
        $template = '';

        $templatePost = get_post($templateId);

        if (!empty($templatePost)) {
            $template = array(
                'content' => $templatePost->post_content,
                'title' => $templatePost->post_title
            );
        }

        return $template;
    }

    /**
     * Returns the list of available templates
     */
    public static function getTemplatesList() {
        $templates = array();

        $templatePosts = get_posts(array(
            'post_type' => CMPopUpFlyInShared::POST_TYPE_TEMPLATE,
            'posts_per_page' => -1
        ));

        if (!empty($templatePosts)) {
            foreach ($templatePosts as $templatePost) {
                $templates[$templatePost->ID] = $templatePost->post_title;
            }
        }

        return $templates;
    }

    public static function prepareHelpItemData($postId, $fillJsonStruct = true) {
        $postMeta = array();
        $postData = get_post($postId, ARRAY_A);
        if (!empty($postData)) {
            $postData = array_intersect_key($postData, array('ID' => '', 'post_title' => ''));
            if (!empty(self::$customMetaboxes)) {
                foreach (self::$customMetaboxes as $metabox) {
                    $meta = $metabox->the_meta($postId);
                    if (is_array($meta)) {
                        $postMeta = array_merge($postMeta, $meta);
                    }
                }
            }

            $postData = array_merge($postData, $postMeta);
        }

        $helpItemContent = $fillJsonStruct ? self::fillHelpItemJsonStruct($postData) : $postData;
        return $helpItemContent;
    }

    public static function fillHelpItemJsonStruct($postData) {
        $itemsData = array();
        $helpItemObj = new stdClass();

        $helpItemObj->id = $postData['ID'];
        $helpItemObj->title = !empty($postData['post_title']) ? $postData['post_title'] : '';
        $helpItemObj->header = !empty($postData['header']) ? $postData['header'] : '';
        $helpItemObj->footer = !empty($postData['footer']) ? $postData['footer'] : '';
        $helpItemObj->widget_type = !empty($postData['cm-campaign-widget-type']) ? $postData['cm-campaign-widget-type'] : '';

        foreach ($postData['cm-help-item-group'] as $groupKey => $group) {
            $dataRow = array();
            $dataRow['id'] = $groupKey;

            foreach ($group as $fieldKey => $fieldValue) {
                switch ($fieldKey) {
                    case 'textarea':
                        $fieldValue = wpautop(do_shortcode(self::replaceImgWithBase64($fieldValue)));
                        $fieldKey = 'content';
                        break;

                    default:
                        break;
                }

                $dataRow[$fieldKey] = $fieldValue;
            }

            $itemsData[] = $dataRow;
        }

        foreach ($itemsData as $helpItemItemsArr) {
            $helpItem = new stdClass();

            foreach ($helpItemItemsArr as $key => $value) {
                $helpItem->$key = $value;
            }

            $helpItemObj->helpItems[] = $helpItem;
        }

        return $helpItemObj;
    }

    /**
     * Function exports the items with given postIds
     * @param type $postIds
     */
    public static function exportHelpItems($postIds) {
        $helpItems = array();

        if (!is_array($postIds)) {
            $postIds = array($postIds);
        }

        foreach ($postIds as $postId) {
            if (is_numeric($postId)) {
                $helpItem = self::prepareHelpItemData($postId, true);

                /*
                 * Add the helpItem if it's not empty
                 */
                if (!empty($helpItem)) {
                    $helpItems[] = $helpItem;
                }
            }
        }

        $helpItemContent = json_encode($helpItems);
        $filename = 'cm_ad_items_' . md5(implode(',', $postIds)) . '_' . date('Ymd_His', current_time('timestamp'));

        /*
         *  Prepare File
         */
        $file = tempnam("tmp", "zip");

        if ($file) {
            $zip = new ZipArchive();
            $zip->open($file, ZipArchive::OVERWRITE);

            /*
             *  Stuff with content
             */
            $zip->addFromString($filename . '.json', $helpItemContent);

            /*
             * Close and send to users
             */
            $zip->close();

            header('Content-Type: application/zip');
            header('Content-Length: ' . filesize($file));
            header('Content-Disposition: attachment; filename="' . $filename . '.zip' . '"');
            readfile($file);
            unlink($file);
            exit;
        }
    }

    public static function addRowAction($actions, $post) {
        if ($post->post_type == CMPopUpFlyInShared::POST_TYPE) {
            $pinOption = get_option('cm_popupflyin_json_api_pinprotect', false);
            $pin = !empty($pinOption) ? '&pin=' . $pinOption : '';
            $actions['cm_json'] = '<a href="' . admin_url('admin-ajax.php?action=cm_popupflyin_json_api&help_id=' . $post->ID . $pin) . '" target="_blank">JSON API</a>';
            $actions['cm_export'] = '<a href="' . admin_url('edit.php?post_type=cm-help-item&post_id=' . $post->ID . '&cm-action=export') . '">Export</a>';
            $actions['popupflyin_preview'] = '<a href="' . admin_url('admin-ajax.php?action=cm_popupflyin_preview&help_id=' . $post->ID) . '" target="_blank">Preview</a>';
            unset($actions['preview']);
            unset($actions['view']);
        }
        return $actions;
    }

    public static function addMenu() {
        global $submenu;
        add_menu_page('Campaign', CMPOPFLY_PLUGIN_NAME, 'edit_posts', CMPOPFLY_SLUG_NAME, 'edit.php?post_type=' . CMPopUpFlyInShared::POST_TYPE);
        add_submenu_page(CMPOPFLY_SLUG_NAME, 'Add New Campaign', 'Add New Campaign', 'edit_posts', 'post-new.php?post_type=' . CMPopUpFlyInShared::POST_TYPE);

        add_submenu_page(CMPOPFLY_SLUG_NAME, 'Settings', 'Settings', 'edit_posts', self::$settingsPageSlug, array(self::$calledClassName, 'renderAdminPage'));
        add_submenu_page(CMPOPFLY_SLUG_NAME, 'About', 'About', 'manage_options', self::$aboutPageSlug, array(self::$calledClassName, 'renderAdminPage'));
        add_submenu_page(CMPOPFLY_SLUG_NAME, 'Pro', 'Pro', 'manage_options', self::$proPageSlug, array(self::$calledClassName, 'renderAdminPage'));
        $submenu[CMPOPFLY_SLUG_NAME][999] = array('Yearly membership offer', 'manage_options', 'https://www.cminds.com/store/cm-wordpress-plugins-yearly-membership/');
        $submenu[CMPOPFLY_SLUG_NAME][500] = array('User Guide', 'manage_options', CMPOPFLY_URL);

        add_filter('views_edit-' . CMPopUpFlyInShared::POST_TYPE, array(self::$calledClassName, 'filterAdminNav'), 10, 1);
        add_filter('views_edit-' . CMPopUpFlyInShared::POST_TYPE_TEMPLATE, array(self::$calledClassName, 'filterAdminNav'), 10, 1);

        if( current_user_can('manage_options') ){
            add_action('admin_head', array(__CLASS__, 'admin_head'));
        }
    }
    public static function admin_head()
    {
        echo '<style type="text/css">
        		#toplevel_page_'.CMPOPFLY_SLUG_NAME.' a[href*="cm-wordpress-plugins-yearly-membership"] {color: white;}
    			a[href*="cm-wordpress-plugins-yearly-membership"]:before {font-size: 16px; vertical-align: middle; padding-right: 5px; color: #d54e21;
    				content: "\f487";
				    display: inline-block;
					-webkit-font-smoothing: antialiased;
					font: normal 16px/1 \'dashicons\';
    			}
    			#toplevel_page_'.CMPOPFLY_SLUG_NAME.' a[href*="cm-wordpress-plugins-yearly-membership"]:before {vertical-align: bottom;}

        	</style>';
    }
    /**
     * Filters admin navigation menus to show horizontal link bar
     * @global string $submenu
     * @global type $plugin_page
     * @param type $views
     * @return string
     */
    public static function filterAdminNav($views) {
        global $submenu, $plugin_page;
        $scheme = is_ssl() ? 'https://' : 'http://';
        $adminUrl = str_replace($scheme . $_SERVER['HTTP_HOST'], '', admin_url());
        $currentUri = str_replace($adminUrl, '', $_SERVER['REQUEST_URI']);
        $submenus = array();

        if (isset($submenu[CMPOPFLY_SLUG_NAME])) {
            $thisMenu = $submenu[CMPOPFLY_SLUG_NAME];

            $firstMenuItem = $thisMenu[0];
            unset($thisMenu[0]);

            $secondMenuItem = array('Trash', 'edit_posts', 'edit.php?post_status=trash&post_type=' . CMPopUpFlyInShared::POST_TYPE, 'Trash');
            array_unshift($thisMenu, $firstMenuItem, $secondMenuItem);

            foreach ($thisMenu as $item) {
                $slug = $item[2];
                $isCurrent = ($slug == $plugin_page || strpos($item[2], '.php') === strpos($currentUri, '.php'));
                $isCurrent = ($slug == $currentUri);
                $isExternalPage = strpos($item[2], 'http') !== FALSE;
                $isNotSubPage = $isExternalPage || strpos($item[2], '.php') !== FALSE;
                $url = $isNotSubPage ? $slug : get_admin_url(null, 'admin.php?page=' . $slug);
                $target = $isExternalPage ? '_blank' : '';
                $submenus[$item[0]] = '<a href="' . $url . '" target="' . $target . '" class="' . ($isCurrent ? 'current' : '') . '">' . $item[0] . '</a>';
            }
        }
        return $submenus;
    }

    public static function getAdminNav() {
        global $self, $parent_file, $submenu_file, $plugin_page, $typenow, $submenu;
        ob_start();
        $submenus = array();

        $menuItem = CMPOPFLY_SLUG_NAME;
        if (isset($submenu[$menuItem])) {
            $thisMenu = $submenu[$menuItem];

            foreach ($thisMenu as $sub_item) {
                $slug = $sub_item[2];

                // Handle current for post_type=post|page|foo pages, which won't match $self.
                $self_type = !empty($typenow) ? $self . '?post_type=' . $typenow : 'nothing';

                $isCurrent = FALSE;
                $subpageUrl = get_admin_url('', 'admin.php?page=' . $slug);

                if (
                        (!isset($plugin_page) && $self == $slug ) ||
                        ( isset($plugin_page) && $plugin_page == $slug && ( $menuItem == $self_type || $menuItem == $self || file_exists($menuItem) === false ) )
                ) {
                    $isCurrent = TRUE;
                }

                $url = (strpos($slug, '.php') !== false || strpos($slug, 'http://') !== false) ? $slug : $subpageUrl;
                $submenus[] = array(
                    'link' => $url,
                    'title' => $sub_item[0],
                    'current' => $isCurrent
                );
            }
            include self::$viewsPath . 'nav.phtml';
        }
        $nav = ob_get_contents();
        ob_end_clean();
        return $nav;
    }

    /*
     * Sanitize the input similar to post_content
     * @param array $meta - all data from metabox
     * @param int $post_id
     * @return array
     */

    public static function kia_single_save_filter($meta, $post_id) {

        if (isset($meta['test_editor'])) {
            $meta['test_editor'] = sanitize_post_field('post_content', $meta['test_editor'], $post_id, 'db');
        }

        return $meta;
    }

    /*
     * Sanitize the input similar to post_content
     * @param array $meta - all data from metabox
     * @param int $post_id
     * @return array
     */

    public static function metaRepeatingSaveFilter($meta, $post_id) {
        array_walk($meta, function ( &$masterItem, $key, $post_id ) {
            foreach ($masterItem as &$item) {
                if (isset($item['cm_load_template']) && !empty($item['template_linked'])) {
                    $template = self::getTemplate($item['cm_load_template']);
                    $item['textarea'] = sanitize_post_field('post_content', $template['content'], $post_id, 'db');
                    $item['title'] = sanitize_post_field('post_title', $template['title'], $post_id, 'db');
                } else {
                    if (isset($item['textarea'])) {
                        $item['textarea'] = sanitize_post_field('post_content', $item['textarea'], $post_id, 'db');
                        if(!isset($item['banner-uuid'])){
                            $item['banner-uuid'] = CMPopUpFlyInShared::giveUniqueId();
                        }
                    }
                }
            }
        }, $post_id);
        return $meta;
    }

    /*
     * Enqueue styles and scripts specific to metaboxs
     */

    public static function enqueueScripts() {
// I prefer to enqueue the styles only on pages that are using the metaboxes
        wp_enqueue_style('wpalchemy-metabox', CMPOPFLY_PLUGIN_URL . 'libs/wpalchemy/assets/meta.css');

//make sure we enqueue some scripts just in case ( only needed for repeating metaboxes )
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-mouse');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-spinner');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-tooltip');

        /*
         * enque jQuery UI styles
         */
        wp_enqueue_style('ac_jqueryUIStylesheet', CMPOPFLY_PLUGIN_URL . 'shared/assets/css/jquery-ui/smoothness/jquery-ui-1.10.3.custom.min.css');
        wp_enqueue_script('word-count');

        wp_enqueue_script('editor');

        wp_enqueue_script('quicktags');
        wp_enqueue_style('buttons');

        wp_enqueue_script('wplink');

        wp_enqueue_script('wp-fullscreen');
        wp_enqueue_script('media-upload');

// special script for dealing with repeating textareas- needs to run AFTER all the tinyMCE init scripts, so make 'editor' a requirement
        wp_enqueue_script('kia-metabox', CMPOPFLY_PLUGIN_URL . 'libs/wpalchemy/assets/kia-metabox.js', array('jquery', 'word-count', 'editor', 'quicktags', 'wplink', 'wp-fullscreen', 'media-upload',), '1.1', true);

        /*
         * Enqueue popupflyin scripts
         */
        wp_enqueue_script('cm_popupflyin_backend', CMPOPFLY_PLUGIN_URL . 'backend/assets/js/cm-popupflyin-backend.js', array('jquery'), '1.0.0', true);
        wp_localize_script('cm_popupflyin_backend', 'cm_popupflyin_backend', array('ajaxurl' => admin_url('admin-ajax.php'), 'plugin_url' => CMPOPFLY_PLUGIN_URL));

        /*
         * Enqueue popupflyin styles
         */
        wp_enqueue_style('cm_popupflyin_css', CMPOPFLY_PLUGIN_URL . 'backend/assets/css/cm-popupflyin.css');
    }

    public static function metaInit() {
        add_action('admin_enqueue_scripts', array(self::$calledClassName, 'enqueueScripts'));
    }

    public static function kia_metabox_scripts() {
        wp_print_scripts('kia-metabox');
    }

    public static function plugin_mce_css($mce_css) {
        if (!empty($mce_css)) {
            $mce_css .= ',';
        }
        $mce_css .= CMPOPFLY_PLUGIN_URL . 'backend/assets/css/cm-popupflyin.css';
        return $mce_css;
    }

    public static function replaceImgWithBase64($content = '') {
        return preg_replace_callback(
                '#<img(.*)src=["\'](.*?)["\'](.*)/>#i', array(__CLASS__, '_replaceImgWithBase64'), $content
        );
    }

    public static function _replaceImgWithBase64($matches) {
        $img = '<img ' . $matches[1] . ' src="' . self::_curlBase64Encode($matches[2]) . '" ' . $matches[3] . '/>';
        return $img;
    }

    /**
     * Function grabs the image from the given url and prepares the Base64 encoded representation of this string
     * Then caches it and returns the base64 representation of the image with the right MIME type
     *
     * @param string $url - url of the image
     * @param int $ttl - time to live of cache
     * @return type
     */
    public static function _curlBase64Encode($url = null, $ttl = 86400) {
        if ($url) {
            $option_name = 'ep_base64_encode_images_' . md5($url);
            $data = get_option($option_name);
            if (isset($data['cached_at']) && (time() - $data['cached_at'] <= $ttl)) {
# serve cache
            } else {
                if (strstr($url, 'http:') === FALSE && strstr($url, 'https:') === FALSE) {
                    $base = get_bloginfo('url');
                    $url = $base . '/admin/' . $url;
                }
                $ch = curl_init();
                $options = array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSLVERSION => 3
                );
                curl_setopt_array($ch, $options);
                $returnData = curl_exec($ch);
                if (!$returnData) {
                    var_dump(curl_error($ch));
                    die;
                }
                $data['chunk'] = base64_encode($returnData);
                $data['mime'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if ($http_code === 200) {
                    $data['cached_at'] = time();
                    update_option($option_name, $data);
                }
            }
        }

        return 'data:' . $data['mime'] . ';base64,' . $data['chunk'];
    }

//    public static function displaySettingsPage()
//    {
//        $page = filter_input(INPUT_GET, 'page');
//
//        wp_enqueue_style('jquery-ui-tabs-css', self::$cssPath . 'jquery-ui-tabs.css');
//        wp_enqueue_script('jquery-ui-tabs');
//
//        $params = apply_filters('CMPOPFLY_admin_settings', array());
//        extract($params);
//
//        ob_start();
//        require_once CMPOPFLY_PLUGIN_DIR . 'backend/views/settings.phtml';
//        $content = ob_get_contents();
//        ob_end_clean();
//        echo $content;
//    }
//
//    public static function displayExportPage()
//    {
//        $page = filter_input(INPUT_GET, 'page');
//
//        ob_start();
//        require_once CMPOPFLY_PLUGIN_DIR . 'backend/views/export.phtml';
//        $content = ob_get_contents();
//        ob_end_clean();
//        echo $content;
//    }

    public static function renderAdminPage() {
        global $wpdb;
        $pageId = filter_input(INPUT_GET, 'page');

        $content = '';
        $title = '';

        switch ($pageId) {
            case CMPOPFLY_SLUG_NAME . '-settings': {
                    $title = CMPopUpFlyIn::__('Settings');
                    wp_enqueue_style('jquery-ui-tabs-css', self::$cssPath . 'jquery-ui-tabs.css');
                    wp_enqueue_script('jquery-ui-tabs');

                    $params = apply_filters('CMPOPFLY_admin_settings', array());
                    extract($params);

                    ob_start();
                    require_once CMPOPFLY_PLUGIN_DIR . 'backend/views/settings.phtml';
                    $content = ob_get_contents();
                    ob_end_clean();
                    break;
                }
            case CMPOPFLY_SLUG_NAME . '-about': {
                    $title = CMPopUpFlyIn::__('About');
                    ob_start();
                    include_once self::$viewsPath . 'about.phtml';
                    $content = ob_get_contents();
                    ob_end_clean();
                    break;
                }
            case CMPOPFLY_SLUG_NAME . '-pro': {
                    ob_start();
                    include_once self::$viewsPath . 'pro.phtml';
                    $content = ob_get_contents();
                    ob_end_clean();
                    break;
                }
            case CMPOPFLY_SLUG_NAME . '-userguide': {
                    wp_redirect('https://plugins.cminds.com/cm-product-catalog');
                    break;
                }
            case CMPOPFLY_SLUG_NAME . '-export': {
                    $title = CMPopUpFlyIn::__('Export');
                    ob_start();
                    require_once CMPOPFLY_PLUGIN_DIR . 'backend/views/export.phtml';
                    $content = ob_get_contents();
                    ob_end_clean();
                    break;
                }
        }

        self::displayAdminPage($content, $title);
    }
    public static function getAvailableCampaigns(){
        global $wpdb;
        return $wpdb->get_results('SELECT ID, post_title' .
                    ' FROM ' . CMPOPFLY_POST_TABLE .
                    " WHERE post_type = '".CMPopUpFlyInShared::POST_TYPE."'" .
                    " AND post_status = 'publish'" .
                    " ORDER BY ID DESC"
                );

    }
    public static function displayAdminPage($content, $title) {
        $nav = self::getAdminNav();
        include_once self::$viewsPath . 'template.phtml';
    }

    /**
     * Saves the settings
     */
    public static function handlePost() {
        $page = filter_input(INPUT_GET, 'page');
        $postData = filter_input_array(INPUT_POST);

        if ($page == 'cm-popupflyin-settings' && !empty($postData)) {
            $params = CMPOPFLY_Settings::processPostRequest();

            // Labels
            $labels = CMPOPFLY_Labels::getLabels();
            foreach ($labels as $labelKey => $label) {
                if (isset($_POST['label_' . $labelKey])) {
                    CMPOPFLY_Labels::setLabel($labelKey, stripslashes($_POST['label_' . $labelKey]));
                }
            }

//            foreach($postData as $key => $value)
//            {
//                update_option($key, $value);
//            }
        }
    }

    /**
     * Exports the Help Items
     */
    public static function handleExport() {
        $page = filter_input(INPUT_GET, 'page');
        $postData = filter_input_array(INPUT_POST);
        if ($page == 'cm-popupflyin-export' && !empty($postData) && check_admin_referer(self::$exportNonceAction, self::$exportNonceField)) {
            $args = array(
                'post_type' => CMPopUpFlyInShared::POST_TYPE,
                'post_status' => 'publish',
                'posts_per_page' => -1);

            $posts = get_posts($args);

            if ($posts) {
                foreach ($posts as $post) {
                    $postIds[] = $post->ID;
                }
                self::exportHelpItems($postIds);
            }
        }
    }

    /**
     * Save post metadata when a post is saved.
     *
     * @param int $post_id The ID of the post.
     */
    public static function updateLinkedTemplates($post_id) {
        /*
         * In production code, $slug should be set only once in the plugin,
         * preferably as a class property, rather than in each function that needs it.
         */
        $slug = CMPopUpFlyInShared::POST_TYPE_TEMPLATE;
        $postType = filter_input(INPUT_POST, 'post_type');

// If this isn't a 'book' post, don't update it.
        if ($slug != $postType) {
            return;
        }
        $args = array(
            'post_type' => CMPopUpFlyInShared::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => -1);

        $posts = get_posts($args);

        foreach ($posts as $post) {
            $needsSaving = FALSE;
            $metabox = self::$customMetaboxes[0];
            $meta = $metabox->the_meta($post->ID);

            if (!empty($meta['cm-help-item-group'])) {
                foreach ($meta['cm-help-item-group'] as $key => $cmItemGroup) {
                    if (!empty($cmItemGroup['cm_load_template']) && $cmItemGroup['cm_load_template'] == $post_id) {
                        if (!empty($cmItemGroup['template_linked']) && $cmItemGroup['template_linked'] && !empty($cmItemGroup['cm_load_template'])) {
                            $template = self::getTemplate($cmItemGroup['cm_load_template']);
                            $newTemplateContent = sanitize_post_field('post_content', $template['content'], $post->ID, 'db');
                            $newTemplateTitle = sanitize_post_field('post_title', $template['title'], $post_id, 'db');

                            if ($newTemplateContent !== $cmItemGroup['textarea']) {
                                $meta['cm-help-item-group'][$key]['textarea'] = $newTemplateContent;
                                $needsSaving = true;
                            }
                            if ($newTemplateTitle !== $cmItemGroup['title']) {
                                $meta['cm-help-item-group'][$key]['title'] = $newTemplateTitle;
                                $needsSaving = true;
                            }
                        }
                    }
                }
            }

            if ($needsSaving) {
                update_post_meta($post->ID, $metabox->id, $meta);
            }
        }
    }

    /**
     * Returns the list of post types for which the custom settings may be applied
     * @return type
     */
    public static function getApplicablePostTypes() {
        $postTypes = array('post', 'page');
        return apply_filters('cmpopfly-metabox-posttypes', $postTypes);
    }

    /**
     * Saves the information form the metabox in the post's meta
     * @param type $post_id
     */
    public static function savePostdata($post_id) {
        $doPreview = filter_input(INPUT_POST, 'wp-preview');
        if($doPreview == 'dopreview'){
            return;
        }
        $postType = isset($_POST['post_type']) ? $_POST['post_type'] : '';
        if (in_array($postType, array(CMPopUpFlyInShared::POST_TYPE))) {
            delete_option('cm-campaign-show-allpages');
            if (isset($_POST['_cm_advertisement_items_custom_fields']['cm-campaign-show-allpages'])) {
                /*
                 * if selected global, deselect all other global items
                 */
                if($_POST['_cm_advertisement_items_custom_fields']['cm-campaign-show-allpages'] == 1){
                    $postsArgs = array(
                        'post_type' => CMPopUpFlyInShared::POST_TYPE
                    );
                    $updatePosts = get_posts($postsArgs);
                    if(!empty($updatePosts)){
                        foreach ($updatePosts as $onePost){
                            $serializedPostMeta = get_post_meta($onePost->ID, '_cm_advertisement_items_custom_fields');
                            $postMeta = maybe_unserialize($serializedPostMeta);
                            $postMeta[0]['cm-campaign-show-allpages'] = 0;
                            update_post_meta($onePost->ID, '_cm_advertisement_items_custom_fields', $postMeta);
                        }
                    }
                }
                /*
                 * end of only one active campaign
                 */
                $newGlobalHelpItemId = $_POST['_cm_advertisement_items_custom_fields']['cm-campaign-show-allpages'];
                $globalHelpItem = CMPopUpFlyInShared::getGlobalHelpItem(true);

                $doPreview = filter_input(INPUT_POST, 'wp-preview');
                /*
                 * Trying to set another Help Item to show on all pages
                 */
                if (!$doPreview && !empty($newGlobalHelpItemId) && $globalHelpItem > 0 && $globalHelpItem !== $post_id) {
                    $url = esc_url(add_query_arg(array('warning' => 1), $_POST['_wp_http_referer']));
                    wp_safe_redirect($url);
                    exit();
                }
            }
            $args = array(
                'posts_per_page' => -1,
                'fields' => 'ids',
                'post_type' => 'page',
                'suppress_filters' => true,
                'meta_query' => array(
                    array(
                        'key' => CMPopUpFlyInShared::CMPOPFLY_SELECTED_AD_ITEM,
                        'value' => $post_id,
                    ),
                )
            );

            $query = new WP_Query($args);
            $pages = $query->get_posts();
            if (!empty($pages)) {
                foreach ($pages as $pageId) {
                    update_post_meta($pageId, CMPopUpFlyInShared::CMPOPFLY_SELECTED_AD_ITEM, '-1');
//                    update_post_meta($pageId, CMPopUpFlyInShared::CMPOPFLY_DISABLE_ADS, '0');
                }
            }

            $cmHelpItemOptions = filter_input(INPUT_POST, '_cm_advertisement_items_custom_fields', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if (!empty($cmHelpItemOptions['cm-help-item-options'])) {
                foreach ($cmHelpItemOptions['cm-help-item-options'] as $key => $helpItemOption) {
                    $showHelpItemPageId = intval($helpItemOption['cm-help-item-url']);
                    if ($showHelpItemPageId) {
                        $helpItem = $post_id;
                        update_post_meta($showHelpItemPageId, CMPopUpFlyInShared::CMPOPFLY_SELECTED_AD_ITEM, $helpItem);
                        //update_post_meta($showHelpItemPageId, CMPopUpFlyInShared::CMPOPFLY_DISABLE_ADS, '0');
                    }
                }
            }
        }
    }

    /**
     * Show the message
     * @global type $post
     * @return type
     */
    public static function showMessage() {
        global $post;

        if (empty($post)) {
            return;
        }

        $showWarning = filter_input(INPUT_GET, 'warning');
        if (in_array($post->post_type, array(CMPopUpFlyInShared::POST_TYPE)) && $showWarning == '1') {
            $globalHelpItemId = CMPopUpFlyInShared::getGlobalHelpItem();
            $url = esc_url(add_query_arg(array('post' => $globalHelpItemId, 'action' => 'edit'), admin_url('post.php')));

            cminds_show_message('One of the the other <a href="' . $url . '" target="_blank">Help Items (edit)</a> is set to be displayed on every page. You can only have one "global" Help Item.', true);
        }
    }

    public static function checkDateFrom($date = null){
        if(!empty($date)){
            $now = time();
            $dateString = strtotime($date);
            if($dateString > $now){
                return false;
            }else{
                return true;
            }
        }
        return false;
    }
    public static function checkDates($dateFrom = null, $dateTo = null){
        if(!empty($dateFrom) && !empty($dateTo)){
            $dateStringFrom = strtotime($dateFrom);
            $dateStringTo = strtotime($dateTo);
            if($dateStringFrom > $dateStringTo){
                return false;
            }else{
                return true;
            }
        }
        return false;
    }
    static function clearPinnedPostsOnCampaignDelete ($post_id) {
        $post_type = get_post_type( $post_id );
        if($post_type == CMPopUpFlyInShared::POST_TYPE){
            global $wpdb;
             $wpdb->query('
                UPDATE '.CMPOPFLY_POST_META_TABLE."
                    SET meta_value = -1
                    WHERE meta_key = '".CMPopUpFlyInShared::CMPOPFLY_SELECTED_AD_ITEM."'
                        AND meta_value = ".$post_id.';');
             $wpdb->query('
                DELETE FROM '.CMPOPFLY_HISTORY_TABLE."
                    WHERE campaign_id = ".$post_id.";");
            delete_option('cm-campaign-show-allpages');
        }
    }
    static function customListLabels ($labels) {
        $labels->not_found = __('No campaigns found');
        return $labels;
    }
}
