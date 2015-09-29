<?php

class CMPOPFLY_Settings
{
    const TYPE_BOOL = 'bool';
    const TYPE_INT = 'int';
    const TYPE_STRING = 'string';
    const TYPE_COLOR = 'color';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_RADIO = 'radio';
    const TYPE_SELECT = 'select';
    const TYPE_MULTISELECT = 'multiselect';
    const TYPE_CSV_LINE = 'csv_line';

    /*
     * OPTIONS
     */
    // General
    const OPTION_ITEMS_ORDER = 'cmpopfly_items_order';
    //Widget
    const OPTION_DEFAULT_WIDGET_ICON = 'cmpopfly_default_widget_icon';
    const OPTION_DEFAULT_WIDGET_ICON_THEME = 'cmpopfly_default_widget_icon_theme';
    const OPTION_CUSTOM_WIDGET_ICON = 'cmpopfly_custom_widget_icon';
    const OPTION_DEFAULT_WIDGET_ICON_TOP = 'cmpopfly_default_widget_icon_top';
    const OPTION_DEFAULT_WIDGET_SIDE = 'cmpopfly_default_widget_side';
    const OPTION_DEFAULT_WIDGET_TYPE = 'cmpopfly_default_widget_type';
    const OPTION_DEFAULT_WIDGET_THEME = 'cmpopfly_default_widget_theme';
    const OPTION_DEFAULT_WIDGET_WIDTH = 'cmpopfly_default_widget_width';
    const OPTION_DEFAULT_WIDGET_HEIGHT = 'cmpopfly_default_widget_height';
    const OPTION_CUSTOM_WIDGET_WIDTH = 'cmpopfly_custom_widget_width';
    const OPTION_CUSTOM_WIDGET_HEIGHT = 'cmpopfly_custom_widget_height';
    const OPTION_WIDGET_SHOWSEARCH = 'cmpopfly_widget_showsearch';
    const OPTION_WIDGET_SHOWTITLE = 'cmpopfly_widget_showtitle';
    const OPTION_DISPLAY_METHOD = 'cmpopfly_default_display_method';
    const OPTION_BACKGROUND_COLOR = 'cmpopfly_custom_background_color';
    const OPTION_DELAY_TO_SHOW = 'cmpopfly_custom_delay_to_show';
    const OPTION_CUSTOM_WIDGET_SHAPE = 'cm-campaign-widget-shape';
    const OPTION_CUSTOM_WIDGET_SHOW_EFFECT = 'cm-campaign-widget-show-effect';
    const OPTION_CUSTOM_WIDGET_INTERVAL = 'cm-campaign-widget-interval';
    const OPTION_CUSTOM_WIDGET_INTERVAL_RESET_TIME = 'cm-campaign-widget-interval-reset-time';
    const OPTION_CUSTOM_WIDGET_UNDERLAY_TYPE = 'cm-campaign-widget-underlay-type';
    const OPTION_CUSTOM_WIDGET_SELECTE_BANNER = 'cm-campaign-widget-selected-banner';
    const OPTION_CUSTOM_WIDGET_CLICKS_COUNT_METHOD = 'cm-campaign-widget-clicks-count-method';

    /*
     * OPTIONS - END
     */
    const ACCESS_EVERYONE = 0;
    const ACCESS_USERS = 1;
    const ACCESS_ROLE = 2;
    const EDIT_MODE_DISALLOWED = 0;
    const EDIT_MODE_WITHIN_HOUR = 1;
    const EDIT_MODE_WITHIN_DAY = 2;
    const EDIT_MODE_ANYTIME = 3;

    public static $categories = array(
        'general'    => 'General',
        'appearance' => 'Appearance',
        'custom_css' => 'Custom CSS',
        'labels'     => 'Labels',
        'custom'     => 'Custom',
    );
    public static $subcategories = array(
        'general'    => array(
//            'general' => 'General Options'
        ),
        'appearance' => array(
//            'tiles'       => 'Tiles View Settings',
//            'list'        => 'List View Settings',
//            'image_tiles' => 'Image Tiles View Settings',
        ),
        'custom_css' => array(
            'custom_css' => 'Custom CSS',
        ),
        'custom' => array(
            'widget' => 'Widget Options',
        ),
    );

    public static $currentCategory = NULL;
    public static $currentSubcategory = NULL;

    public static function getOptionsConfig()
    {

        return apply_filters('cmpopfly_options_config', array(
            
//            // General
//            self::OPTION_ITEMS_ORDER   => array(
//                'type'        => self::TYPE_SELECT,
//                'default'     => 'DESC',
//                'category'    => 'custom',
//                'subcategory' => 'general',
//                'title'       => 'Product Items order',
//                'desc'        => 'Select whether the items should be ordered ascenging or descending',
//                'options'     => array('DESC' => 'DESC',
//                    'ASC'  => 'ASC'),
//            ),
            // General - Widget
            self::OPTION_DEFAULT_WIDGET_ICON       => array(
                'type'        => self::TYPE_SELECT,
                'default'     => '01',
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Select the widget icon',
                'desc'        => 'Allows to select the icon for the widget',
                'options'     => array('01' => '01', '02' => '02', '03' => '03', '04' => '04', '05' => '05', '06' => '06', '11' => '11', '12' => '12'),
            ),
            // General - Widget
            self::OPTION_DEFAULT_WIDGET_ICON_THEME => array(
                'type'        => self::TYPE_SELECT,
                'default'     => 'grey',
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Select the theme of the widget icon',
                'desc'        => 'Allows to select the theme of the widget icon',
                'options'     => array('grey' => 'Grey', 'white' => 'White'),
            ),
            // General - Widget
            self::OPTION_CUSTOM_WIDGET_ICON        => array(
                'type'        => self::TYPE_STRING,
                'default'     => '',
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Select the URL of the custom widget icon',
                'desc'        => 'If you wish you can provide the URL for the custom widget icon and paste it here. It will override the defautls.',
            ),
            // General - Widget
            self::OPTION_DEFAULT_WIDGET_ICON_TOP   => array(
                'type'        => self::TYPE_STRING,
                'default'     => '10%',
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Select the vertical position of the icon',
                'desc'        => 'Allows to set the position of the icon as the distanse from the top of the page (0 means the very top, 100% means very bottom)',
            ),
            // General - Widget
            self::OPTION_DEFAULT_WIDGET_SIDE       => array(
                'type'        => self::TYPE_SELECT,
                'default'     => 'right',
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Select the side the widget appears on',
                'desc'        => 'Allows to select the side of the screen the widget should appear on',
                'options'     => array('right' => 'Right', 'left' => 'Left'),
            ),
            // General - Widget
            self::OPTION_DEFAULT_WIDGET_TYPE       => array(
                'type'        => self::TYPE_SELECT,
                'default'     => 'popup',
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Select the widget type',
                'desc'        => 'Allows to select the type of the widget',
                'options'     => array('popup' => 'Pop-Up', 'flyin' => 'Fly-In Bottom'),
            ),
            // General - Widget
            self::OPTION_DEFAULT_WIDGET_THEME      => array(
                'type'        => self::TYPE_SELECT,
                'default'     => 'black',
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Select the widget theme',
                'desc'        => 'Allows to select the theme of the widget',
                'options'     => array('black' => 'Black', 'white' => 'White'),
            ),
            // General - Widget
            self::OPTION_DEFAULT_WIDGET_WIDTH      => array(
                'type'        => self::TYPE_STRING,
                'default'     => '250px',
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Select the width of the widget',
                'desc'        => 'Allows to select the width of the widgets container',
            ),
            // General - Widget
            self::OPTION_DEFAULT_WIDGET_HEIGHT     => array(
                'type'        => self::TYPE_STRING,
                'default'     => '305px',
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Select the height of the widget',
                'desc'        => 'Allows to select the height of the widgets container',
            ),
            // General - Widget
            self::OPTION_WIDGET_SHOWSEARCH         => array(
                'type'        => self::TYPE_BOOL,
                'default'     => TRUE,
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Show "Search" in the widget',
                'desc'        => 'Allows to decide if the Search input should appear within the widget',
            ),
            // General - Widget
            self::OPTION_WIDGET_SHOWTITLE          => array(
                'type'        => self::TYPE_BOOL,
                'default'     => FALSE,
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Show Help Item\'s title in the widget',
                'desc'        => 'Allows to decide if the Help Item title should appear on the top of the widget',
            ),
             // General - Widget
            self::OPTION_DISPLAY_METHOD       => array(
                'type'        => self::TYPE_RADIO,
                'default'     => 'selected',
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Select the widget display method',
                'desc'        => 'Allows to select the display method of the widget',
                'options'     => array('selected' => 'Selected'),
            ),
            // General - Widget
            self::OPTION_CUSTOM_WIDGET_WIDTH      => array(
                'type'        => self::TYPE_STRING,
                'default'     => '250px',
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Select the width of the widget',
                'desc'        => 'Allows to select the width of the widget',
            ),
            // General - Widget
            self::OPTION_CUSTOM_WIDGET_HEIGHT     => array(
                'type'        => self::TYPE_STRING,
                'default'     => '300px',
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Select the height of the widget',
                'desc'        => 'Allows to select the height of the widget',
            ),
            // General - Widget
            self::OPTION_BACKGROUND_COLOR     => array(
                'type'        => self::TYPE_STRING,
                'default'     => '#ffffff',
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Select background color of the widghe',
                'desc'        => 'Allows to select the background color of the widget',
            ),
            // General - Widget
            self::OPTION_DELAY_TO_SHOW => array(
                'type'        => self::TYPE_STRING,
                'default'     => '0',
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Set the time between page loads and appearing of the widget',
                'desc'        => 'Allows to set the time between page loads and appearing of the widget',
            ),
            // General - Widget
            self::OPTION_CUSTOM_WIDGET_SHAPE => array(
                'type'        => self::TYPE_SELECT,
                'default'     => 'rounded',
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Select the widget shape',
                'desc'        => 'Allows to select the shape of the widget',
                'options'     => array('rounded' => 'Rounded Edges', 'sharp' => 'Sharp Edges'),
            ),
            // General - Widget
            self::OPTION_CUSTOM_WIDGET_SHOW_EFFECT => array(
                'type'        => self::TYPE_RADIO,
                'default'     => 'popin',
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Select the widget show effect',
                'desc'        => 'Allows to select widget show effect',
                'options'     => array('popin' => 'Pop-In'),
            ),
            // General - Widget
            self::OPTION_CUSTOM_WIDGET_INTERVAL => array(
                'type'        => self::TYPE_SELECT,
                'default'     => 'always',
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Select the widget showing interval',
                'desc'        => 'Allows to select the showing interval of the widget',
                'options'     => array('always' => 'Every Time Page Loads', 'once' => 'Only First Time Page Loads'),
            ),
            // General - Widget
            self::OPTION_CUSTOM_WIDGET_INTERVAL_RESET_TIME => array(
                'type'        => self::TYPE_STRING,
                'default'     => '7',
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Enter first time page loads interval option reset period',
                'desc'        => 'Allows to set first time page loads interval option reset period',
            ),
            // General - Widget
            self::OPTION_CUSTOM_WIDGET_UNDERLAY_TYPE => array(
                'type'        => self::TYPE_SELECT,
                'default'     => 'dark',
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Select the widget underlay type',
                'desc'        => 'Allows to select widget underlay type',
                'options'     => array('dark' => 'Dark Underlay',
                                        'light' => 'Light Underlay',
                                        'no' => 'No Underlay'
                                ),
            ),
            // General - Widget
            self::OPTION_CUSTOM_WIDGET_SELECTE_BANNER => array(
                'type'        => self::TYPE_SELECT,
                'default'     => '',
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Select the widget banner',
                'desc'        => 'Allows to select widget baner',
                'options'     => array(),
            ),
             // General - Widget
            self::OPTION_CUSTOM_WIDGET_CLICKS_COUNT_METHOD => array(
                'type'        => self::TYPE_RADIO,
                'default'     => 'all',
                'category'    => 'custom',
                'subcategory' => 'widget',
                'title'       => 'Select the widget clicks count method',
                'desc'        => 'Allows to select the clicks count method of the widget',
                'options'     => array('one' => 'Only one click per banner show', 'all' => 'All clicks until close button click'),
            )
           )
        );
    }

    public static function getOptionsConfigByCategory($category, $subcategory = null)
    {
        $options = self::getOptionsConfig();
        self::$currentCategory = $category;
        self::$currentSubcategory = $subcategory;
        return array_filter($options, array(__CLASS__,'optionFilter'));
    }

    public static function optionFilter($val)
    {
        $category = self::$currentCategory;
        $subcategory = self::$currentSubcategory;
        if( $val['category'] == $category )
        {
            return (is_null($subcategory) OR $val['subcategory'] == $subcategory);
        }
    }

    public static function getOptionConfig($name)
    {
        $options = self::getOptionsConfig();
        if( isset($options[$name]) )
        {
            return $options[$name];
        }
    }

    public static function setOption($name, $value)
    {
        $options = self::getOptionsConfig();
        if( isset($options[$name]) )
        {
            $field = $options[$name];
            $old = get_option($name);
            if( is_array($old) OR is_object($old) OR strlen((string) $old) > 0 )
            {
                update_option($name, self::cast($value, $field['type']));
            }
            else
            {
                $result = update_option($name, self::cast($value, $field['type']));
            }
        }
    }

    public static function deleteAllOptions()
    {
        $params = array();
        $options = self::getOptionsConfig();
        foreach($options as $name => $optionConfig)
        {
            self::deleteOption($name);
        }

        return $params;
    }

    public static function deleteOption($name)
    {
        $options = self::getOptionsConfig();
        if( isset($options[$name]) )
        {
            delete_option($name);
        }
    }

    public static function getOption($name)
    {
        $options = self::getOptionsConfig();
        if( isset($options[$name]) )
        {
            $field = $options[$name];
            $defaultValue = (isset($field['default']) ? $field['default'] : null);
            return self::cast(get_option($name, $defaultValue), $field['type']);
        }
    }

    public static function getCategories()
    {
        $categories = array();
        $options = self::getOptionsConfig();
        foreach($options as $option)
        {
            $categories[] = $option['category'];
        }
        return $categories;
    }

    public static function getSubcategories($category)
    {
        $subcategories = array();
        $options = self::getOptionsConfig();
        foreach($options as $option)
        {
            if( $option['category'] == $category )
            {
                $subcategories[] = $option['subcategory'];
            }
        }
        return $subcategories;
    }

    protected static function boolval($val)
    {
        return (boolean) $val;
    }

    protected static function arrayval($val)
    {
        if( is_array($val) ) return $val;
        else if( is_object($val) ) return (array) $val;
        else return array();
    }

    protected static function cast($val, $type)
    {
        if( $type == self::TYPE_BOOL )
        {
            return (intval($val) ? 1 : 0);
        }
        else
        {
            $castFunction = $type . 'val';
            if( function_exists($castFunction) )
            {
                return call_user_func($castFunction, $val);
            }
            else if( method_exists(__CLASS__, $castFunction) )
            {
                return call_user_func(array(__CLASS__, $castFunction), $val);
            }
            else
            {
                return $val;
            }
        }
    }

    protected static function csv_lineval($value)
    {
        if( !is_array($value) ) $value = explode(',', $value);
        return $value;
    }

    public static function processPostRequest()
    {
        $params = array();
        $options = self::getOptionsConfig();
        foreach($options as $name => $optionConfig)
        {
            if( isset($_POST[$name]) )
            {
                $params[$name] = $_POST[$name];
                self::setOption($name, $_POST[$name]);
            }
        }

        return $params;
    }

    public static function userId($userId = null)
    {
        if( empty($userId) ) $userId = get_current_user_id();
        return $userId;
    }

    public static function isLoggedIn($userId = null)
    {
        $userId = self::userId($userId);
        return !empty($userId);
    }

    public static function getRolesOptions()
    {
        global $wp_roles;
        $result = array();
        if( !empty($wp_roles) AND is_array($wp_roles->roles) ) foreach($wp_roles->roles as $name => $role)
            {
                $result[$name] = $role['name'];
            }
        return $result;
    }

    public static function canReportSpam($userId = null)
    {
        return (self::getOption(self::OPTION_SPAM_REPORTING_ENABLED) AND ( self::getOption(self::OPTION_SPAM_REPORTING_GUESTS) OR self::isLoggedIn($userId)));
    }

    public static function getPagesOptions()
    {
        $pages = get_pages(array('number' => 100));
        $result = array(null => '--');
        foreach($pages as $page)
        {
            $result[$page->ID] = $page->post_title;
        }
        return $result;
    }

    public static function areAttachmentsAllowed()
    {
        $ext = self::getOption(self::OPTION_ATTACHMENTS_FILE_EXTENSIONS);
        return (!empty($ext) AND ( self::getOption(self::OPTION_ATTACHMENTS_ANSWERS_ALLOW) OR self::getOption(self::OPTION_ATTACHMENTS_QUESTIONS_ALLOW)));
    }

    public static function getLoginPageURL($returnURL = null)
    {
        if( empty($returnURL) )
        {
            $returnURL = get_permalink();
        }
        if( $customURL = CMPOPFLY_Settings::getOption(CMPOPFLY_Settings::OPTION_LOGIN_PAGE_LINK_URL) )
        {
            return esc_url(add_query_arg(array('redirect_to' => urlencode($returnURL)), $customURL));
        }
        else
        {
            return wp_login_url($returnURL);
        }
    }

}