<?php

if (!defined('ABSPATH')) {
    exit;
}
class CMPopUpFlyInShared {

    protected static $instance = NULL;
    public static $calledClassName;
    public static $lastProductQuery = NULL;
    protected static $cssPath = NULL;
    protected static $jsPath = NULL;
    protected static $viewsPath = NULL;

    const POST_TYPE = 'cm-ad-item';
    const POST_TYPE_TEMPLATE = 'cm-ai-template';
    const POST_TYPE_TAXONOMY = 'cm-ai-category';
    const CMPOPFLY_SELECTED_AD_ITEM = 'cm-selected-ai';
    const CMPOPFLY_SHOW_AD_ITEM = 'cm-show-ai';
    const CMPOPFLY_DISABLE_ADS = 'cm-disable-ai';
    const CMPOPFLY_CUSTOM_WIDGET_TYPE = 'cmpopfly-custom-widget-type-hi';
    const CMPOPFLY_CUSTOM_ACTIVITY_DATES_META_KEY = '_cmpopfly-custom-activity_dates';
    const CMPOPFLY_ALL_USED_UNIQUE_ID_OPTION_NAME = 'cmpopfly-all-unique-used-options';

    public static $x = 1;
    public static $widget;
    public static $widgetConfig;
    public static $widgetUnderlayType;
    public static $selectedCampaignBannerId;

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

        self::$cssPath = CMPOPFLY_PLUGIN_URL . 'shared/assets/css/';
        self::$jsPath = CMPOPFLY_PLUGIN_URL . 'shared/assets/js/';
        self::$viewsPath = CMPOPFLY_PLUGIN_DIR . 'shared/views/';

        self::setupConstants();
        self::setupOptions();
        self::loadClasses();
        self::registerActions();
    }

    /**
     * Register the plugin's shared actions (both backend and frontend)
     */
    private static function registerActions() {

    }

    /**
     * Setup plugin constants
     *
     * @access private
     * @since 1.1
     * @return void
     */
    private static function setupConstants() {

    }

    /**
     * Setup plugin constants
     *
     * @access private
     * @since 1.1
     * @return void
     */
    private static function setupOptions() {
        /*
         * Adding additional options
         */
        do_action('cmpopfly_setup_options');
    }

    /**
     * Create taxonomies
     */
    public static function cmpopfly_create_taxonomies() {
        return;
    }

    /**
     * Load plugin's required classes
     *
     * @access private
     * @since 1.1
     * @return void
     */
    private static function loadClasses() {
        /*
         * Load the file with shared global functions
         */
        include_once CMPOPFLY_PLUGIN_DIR . "shared/functions.php";
    }

    public function registerShortcodes() {
        return;
    }

    public function registerFilters() {
        return;
    }

    public static function initSession() {
        if (!session_id()) {
            session_start();
        }
    }

    /**
     * Create custom post type
     */
    public static function registerPostTypeAndTaxonomies() {
        return;
    }

    /**
     * Gets the list of the products
     * @param type $atts
     * @return type
     */
    public static function getItems($atts = array()) {
        $postTypes = array(self::POST_TYPE);

        $args = array(
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'post_type' => $postTypes,
            'suppress_filters' => true
        );

        /*
         * Don't show paused products
         */
        if (!empty($atts['paused'])) {
            $args['meta_query'] = array(
                'relation' => 'OR',
                array(
                    'key' => 'CMPOPFLY_pause_prod',
                    'value' => '0',
                ),
                array(
                    'key' => 'CMPOPFLY_pause_prod',
                    'value' => '0',
                    'compare' => 'NOT EXISTS',
                ),
            );
        }

        /*
         * Don't show paused products
         */
        if (!empty($atts['from_edd'])) {
            $args['meta_query'] = array(
                'relation' => 'OR',
                array(
                    'key' => 'CMPOPFLY_edd_product',
                    'value' => '1',
                )
            );
        }

        /*
         * Return in categories
         */
        if (!empty($atts['cats'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => CMProductCatalogShared::POST_TYPE_TAXONOMY,
                    'terms' => $atts['cats'],
                    'operator' => 'IN',
                    'field' => 'slug',
                ),
            );
        }

        /*
         * Return with tags
         */
        if (!empty($atts['tags'])) {
            $args['tag_slug__in'] = $atts['tags'];
        }

        /*
         * Return only products with given ids
         */
        if (!empty($atts['item_ids'])) {
            $atts['item_ids'] = is_array($atts['item_ids']) ? $atts['item_ids'] : array($atts['item_ids']);
            $args['post__in'] = $atts['item_ids'];
        }

        /*
         * Return only products which title/description includes the query
         */
        if (!empty($atts['query'])) {
            $args['s'] = $atts['query'];
        }

        $query = new WP_Query($args);
        /*
         * Store the query to save info about pagination
         */
        self::$lastProductQuery = $query;
        $items = $query->get_posts();

        return $items;
    }

    public static function getItem($productIdName) {
        return;
    }
    /**
     * Function returns the help item assigned to the page
     */
    public static function checkIfNotBlocked($postId) {
        $selectedHelpItem = get_post_meta($postId, CMPopUpFlyInShared::CMPOPFLY_DISABLE_ADS, true);
        return (bool)$selectedHelpItem;
    }
    /**
     * Function returns the help item assigned to the page
     */
    public static function getPostHelpItem($postId) {
        $selectedHelpItem = get_post_meta($postId, CMPopUpFlyInShared::CMPOPFLY_SELECTED_AD_ITEM, true);
        return $selectedHelpItem;
    }

    /**
     * Function returns the help item which has the checkbox saying: "Show on all pages" selected, or FALSE
     */
    public static function getGlobalHelpItem($onlyCleanValues = false) {
        $result = get_option('cm-campaign-show-allpages', FALSE);
        if (!$result || $onlyCleanValues) {
            $helpItems = self::getItems();
            foreach ($helpItems as $helpItem) {
                $helpItemMeta = self::getCampaignOptionsMeta($helpItem->ID);
                if (isset($helpItemMeta['cm-campaign-show-allpages']) && $helpItemMeta['cm-campaign-show-allpages'] == 1) {
                    update_option('cm-campaign-show-allpages', $helpItem->ID);
                    $result = $helpItem->ID;
                    break;
                }
            }
        }
        return $result;
    }
    /*
     * function returns Campaign custom options
     */
    public static function getCampaignOptionsMeta($id) {
        $raw = get_post_meta($id);
        return maybe_unserialize($raw['_cm_advertisement_items_custom_fields'][0]);
    }
    /**
     * Function returns the help item matching the pattern
     */
    public static function getHelpItemMatchingUrl($url) {
        $result = FALSE;
        $helpItems = self::getItems();
        foreach ($helpItems as $helpItem) {
            $helpItemMeta = self::getCampaignOptionsMeta($helpItem->ID);
            if (!empty($helpItemMeta['cm-help-item-show-wildcard']) && strstr($url, $helpItemMeta['cm-help-item-show-wildcard']) !== FALSE) {
                $result = $helpItem->ID;
                break;
            }
        }
        return $result;
    }

    public static function getExternalLinkIcon($srcOnly = FALSE) {
        $iconUrl = CMPOPFLY_PLUGIN_URL . 'shared/assets/images/external.png';
        $result = $srcOnly ? $iconUrl : '<img src="' . $iconUrl . '" alt="External Link Icon" class="cmpopfly-external-link-icon" />';
        return $result;
    }

    public static function getWidgetOutput($atts = array()) {
        global $post;
        /*
         * for normal page view
         */
        $widget = false;
        if(!empty($post)){
            $postId = empty($post->ID) ? '' : $post->ID;
            $widget = CMPopUpFlyInBackend::getWidgetForPage($postId);
        }elseif(filter_input(INPUT_GET, 'campaign_id')){
            /*
             * for preview
             */
            $postId = filter_input(INPUT_GET, 'campaign_id');
            $widget = get_post_meta($postId);
        }
        /*
         * if no widget or empty widget banners return no output
         */
        if(!$widget || empty($widget['_cm_advertisement_items'])){
            return false;
        }
        self::$widget = $widget;
        $widgetConfig = maybe_unserialize($widget['_cm_advertisement_items_custom_fields'][0]);
        self::$widgetConfig = $widgetConfig;
        switch($widgetConfig['cm-campaign-widget-type']){
            case 'popup': return self::getPopUpOutput(); break;
            case 'flyin': return self::getFlyingBottomOutput(); break;
            default: return false; break;
        }
    }
    static function getPopUpOutput(){
        $widget = self::$widget;
        $widgetConfig = self::$widgetConfig;
        wp_enqueue_script('cmpopfly-popup-core', self::$jsPath . 'ouibounce.js', array('jquery'));
        wp_enqueue_script('cmpopfly-popup-custom', self::$jsPath . 'popupCustom.js', array('cmpopfly-popup-core'));
        wp_enqueue_style('cm_ouibounce_css', CMPOPFLY_PLUGIN_URL . 'shared/assets/css/ouibounce.css');

        /*
        * banner config resolve
        */
        $width = ((!empty($widgetConfig['cm-campaign-widget-width']) ? (intval($widgetConfig['cm-campaign-widget-width']) . 'px'):('600px')));
        $height = ((!empty($widgetConfig['cm-campaign-widget-height']) ? (intval($widgetConfig['cm-campaign-widget-height']) . 'px'):('400px')));
        $background = ((!empty($widgetConfig['cm-campaign-widget-background-color']) ? ($widgetConfig['cm-campaign-widget-background-color']):('#f0f1f2')));
        $userShowMethod = ((!empty($widgetConfig['cm-campaign-widget-interval']) ? ($widgetConfig['cm-campaign-widget-interval']):('always')));
        $underlayType = ((!empty($widgetConfig['cm-campaign-widget-underlay-type']) ? ($widgetConfig['cm-campaign-widget-underlay-type']):('dark')));
        $resetTime = ((!empty($widgetConfig['cm-campaign-widget-interval_reset_time']) ? ($widgetConfig['cm-campaign-widget-interval_reset_time']):(7)));
        $delay = (!empty($widgetConfig['cm-campaign-widget-delay-to-show']) && (intval($widgetConfig['cm-campaign-widget-delay-to-show']) > 0)) ?(intval($widgetConfig['cm-campaign-widget-delay-to-show']) * 1000) : (0) ;
        if(!preg_match("/#/", $background)){
            $background = '#'.$background;
        }
        switch ($underlayType){
            case 'dark'  : $underlayColor = 'rgba(0,0,0,0.5)'; break;
            case 'light' : $underlayColor = 'rgba(0,0,0,0.2)'; break;
            default : $underlayColor = 'rgba(0,0,0,0.5)'; break;
        }
        if(!empty($widgetConfig['cm-campaign-widget-shape'])){
            switch($widgetConfig['cm-campaign-widget-shape']){
                case 'rounded' : $banner_edges = '4px'; break;
                case 'sharp' : $banner_edges = '0px'; break;
                default : $banner_edges = '4px';
            }
        }else{
            $banner_edges = '4px';
        }

        if(!empty($widgetConfig['cm-campaign-widget-show-effect'])){
            switch($widgetConfig['cm-campaign-widget-show-effect']){
                case 'popin' : $show_effect = 'popin 1.0s'; break;
                case 'bounce' : $show_effect = 'bounce 1.0s'; break;
                case 'shake' : $show_effect = 'shake 1.0s'; break;
                case 'flash' : $show_effect = 'flash 0.5s'; break;
                case 'tada' : $show_effect = 'tada 1.5s'; break;
                case 'swing' : $show_effect = 'swing 1.0s'; break;
                case 'rotateIn' : $show_effect = 'rotateIn 1.0s'; break;
                default : $show_effect = 'popin 1.0s';
            }
        }else{
            $show_effect = 'popin 1.0s;';
        }
        /*
         * add custom html content filter
         */
        self::$widgetUnderlayType = $underlayType;
        $custom_css = '
            #ouibounce-modal .modal {
                    width: '.$width.';
                    height: '.$height.';
                    background-color: '.$background.';
                    z-index: 1;
                    position: absolute;
                    margin: auto;
                    top: 0;
                    right: 0;
                    bottom: 0;
                    left: 0;
                    border-radius: '.$banner_edges.';
                    -webkit-animation: '. $show_effect.';
                    animation: '. $show_effect.';
                  }'
                .(($underlayType != 'no')?('#ouibounce-modal .underlay {background-color: '.$underlayColor.';}'):(""));
        wp_add_inline_style('cm_ouibounce_css', $custom_css);
        $content = '<div id="ouibounce-modal">
                '.(($underlayType != 'no')?('<div class="underlay"></div>'):("")).'
                <div class="modal">
                <div id="close_button" class="popupflyin-close-button"></div>
                  <div class="modal-body popupflyin-clicks-area">'.preg_replace("/\"/", "'", self::getBannerContent()).'</div>
                </div>
              </div>';
        $scriptData = array();
        $scriptData['content'] = $content;
        $scriptData['showMethod'] = $userShowMethod;
        $scriptData['resetTime']  = $resetTime;
        $scriptData['secondsToShow'] = $delay;
        wp_localize_script('cmpopfly-popup-custom', 'popup_custom_data', $scriptData);
        /*
         * initialize js watchers
         */
        if(!CMPopUpFlyInBackend::$isPreview){
            self::initializeWatchers($widgetConfig);
        }
    }
    static function getBannerContent(){
        $preContent = maybe_unserialize(self::$widget['_cm_advertisement_items'][0]);
        /*
         * switch for selected
         */
        switch(self::$widgetConfig['cm-campaign-display-method']){
            case 'selected' : $adKey = self::$widgetConfig['cm-campaign-widget-selected-banner']; break;
            default: break;
        }
        /*
         * do impression event call
         */
        if(!CMPopUpFlyInBackend::$isPreview){
        }
        self::$selectedCampaignBannerId = $preContent['cm-help-item-group'][$adKey]['banner-uuid'];
        return $preContent['cm-help-item-group'][$adKey]['textarea'];
    }
    static function getFlyingBottomOutput(){
        wp_enqueue_script('cmpopfly-flying-bottom-core', self::$jsPath . 'flyingBottom.js', array('jquery'));
        wp_enqueue_script('cmpopfly-flying-custom', self::$jsPath . 'flyingCustom.js', array('cmpopfly-flying-bottom-core'));
        wp_enqueue_style('cm_ouibounce_css', CMPOPFLY_PLUGIN_URL . 'shared/assets/css/ouibounce.css');
        $widget = self::$widget;
        $widgetConfig = self::$widgetConfig;
        /*
        * banner config resolve
        */
        $width = ((!empty($widgetConfig['cm-campaign-widget-width']) ? (intval($widgetConfig['cm-campaign-widget-width']) . 'px'):('600px')));
        $height = ((!empty($widgetConfig['cm-campaign-widget-height']) ? (intval($widgetConfig['cm-campaign-widget-height']) . 'px'):('400px')));
        $background = ((!empty($widgetConfig['cm-campaign-widget-background-color']) ? ($widgetConfig['cm-campaign-widget-background-color']):('#f0f1f2')));
        $userShowMethod = ((!empty($widgetConfig['cm-campaign-widget-interval']) ? ($widgetConfig['cm-campaign-widget-interval']):('always')));
        $resetTime = ((!empty($widgetConfig['cm-campaign-widget-interval_reset_time']) ? ($widgetConfig['cm-campaign-widget-interval_reset_time']):(7)));
        $delay = (!empty($widgetConfig['cm-campaign-widget-delay-to-show']) && (intval($widgetConfig['cm-campaign-widget-delay-to-show']) > 0)) ?(intval($widgetConfig['cm-campaign-widget-delay-to-show']) * 1000) : (0) ;
        if(!preg_match("/#/", $background)){
            $background = '#'.$background;
        }
        if(!empty($widgetConfig['cm-campaign-widget-shape'])){
            switch($widgetConfig['cm-campaign-widget-shape']){
                case 'rounded' : $banner_edges = '4px'; break;
                case 'sharp' : $banner_edges = '0px'; break;
                default : $banner_edges = '4px';
            }
        }else{
            $banner_edges = '4px';
        }

        if(!empty($widgetConfig['cm-campaign-widget-show-effect'])){
            switch($widgetConfig['cm-campaign-widget-show-effect']){
                case 'popin' : $show_effect = 'popin 1.0s'; break;
                case 'bounce' : $show_effect = 'bounce 1.0s'; break;
                case 'shake' : $show_effect = 'shake 1.0s'; break;
                case 'flash' : $show_effect = 'flash 0.5s'; break;
                case 'tada' : $show_effect = 'tada 1.5s'; break;
                case 'swing' : $show_effect = 'swing 1.0s'; break;
                case 'rotateIn' : $show_effect = 'rotateIn 1.0s'; break;
                default : $show_effect = 'popin 1.0s';
            }
        }else{
            $show_effect = 'popin 1.0s;';
        }
        $custom_css = '
            #flyingBottomAd {
            padding: 0px;
            z-index: 100;
            border-radius: '.$banner_edges.' 0 0;
            -moz-border-radius: '.$banner_edges.' 0 0;
            -webkit-border-radius: '.$banner_edges.' 0 0;
            background: '.$background.';
            box-shadow: 0 0 20px rgba(0,0,0,.2);
            width: '.$width.';
            height: '.$height.';
            position: fixed;
            bottom: 0;
            right: 0;
            -webkit-backface-visibility: visible!important;
            -ms-backface-visibility: visible!important;
            backface-visibility: visible!important;
            -webkit-animation: '.$show_effect.';
            -moz-animation: '.$show_effect.';
            -o-animation: '.$show_effect.';
            animation: '.$show_effect.';
            -webkit-transition: bottom .5s ease,background-position .5s ease;
            transition: bottom .5s ease,background-position .5s ease;
        }
        ';
        wp_add_inline_style('cm_ouibounce_css', $custom_css);

        $scriptData['content'] = preg_replace("/'/", "\"", self::getBannerContent());
        $scriptData['showMethod'] = $userShowMethod;
        $scriptData['resetTime']  = $resetTime;
        $scriptData['secondsToShow'] = $delay;
        wp_localize_script('cmpopfly-flying-custom', 'flyin_custom_data', $scriptData);
        /*
         * initialize js watchers
         */
        if(!CMPopUpFlyInBackend::$isPreview){
            self::initializeWatchers($widgetConfig);
        }
    }
    static function initializeWatchers($widgetConfig){
        $widgetConfig = self::$widgetConfig;
        $countingMethod = (!empty($widgetConfig['cm-campaign-clicks-counting-method'])) ?($widgetConfig['cm-campaign-clicks-counting-method']) : ('one') ;
        wp_enqueue_script('cmpopfly-popup-clickswatcher', self::$jsPath . 'clicksWatcher.js', array('jquery'));
        $scriptData['countingMethod'] = $countingMethod;
        $scriptData['campaignId'] = self::$widget['campaign_id'];
        $scriptData['bannerId'] = self::$selectedCampaignBannerId;
        $scriptData['ajaxClickUrl'] = admin_url('admin-ajax.php'.'?action=cm_popupflyin_register_click');
        wp_localize_script('cmpopfly-popup-clickswatcher', 'clicks_watcher_data', $scriptData);
    }
    public static function giveUniqueId(){
        $allExistingIds = get_option( self::CMPOPFLY_ALL_USED_UNIQUE_ID_OPTION_NAME, '' );
        if(empty($allExistingIds)){
            $newId = self::giveNewUniqueId();
            $optArray = array($newId);
            update_option(self::CMPOPFLY_ALL_USED_UNIQUE_ID_OPTION_NAME, serialize($optArray));
            return $newId;
        }else{
            $allOptions = unserialize($allExistingIds);
            while(in_array($newId = self::giveNewUniqueId(), $allOptions)){}
            $allOptions[] = $newId;
            update_option(self::CMPOPFLY_ALL_USED_UNIQUE_ID_OPTION_NAME, serialize($allOptions));
            return $newId;
        }
        return false;
    }
    private static function giveNewUniqueId(){
        return floor((microtime(1) * floor(rand(1, 10)*rand(1, 10))) / floor(rand(1, 10)*rand(1, 10)));
    }
}
