<?php
/**
 * UIX Data
 *
 * @package   uixv2
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 David Cramer
 */
namespace uixv2\data;

/**
 * localized data class
 * @package uixv2\data
 * @author  David Cramer
 */
abstract class localized extends \uixv2\ui\uix implements load{

    /**
     * active objects
     *
     * @since 1.0.0
     *
     * @var      array
     */
    protected $active_objects = array();

    protected function enqueue_active_assets(){
        // build data
        foreach( (array) $this->active_slugs as $slug ){

            // load object and data
            $uix            = $this->get( $slug );
            $config_object  = $this->get_data( $slug );

            /**
             * Filter config object
             *
             * @param array $config_object The object as retrieved from data
             * @param array $uix the UIX structure
             * @param array $slug the UIX object slug
             */
            $config_object = apply_filters( 'uix_data-' . $this->type, $config_object, $uix, $slug );       

            $this->active_objects[ $slug ] = array(
                'data'      => $config_object,
                'structure' => $uix
            );
        }
        // localize data for this screen
        $this->localize_data();

        parent::enqueue_active_assets();
    }

    /**
     * Define core localize UIX scripts
     *
     * @since 1.0.0
     *
     */
    public function uix_scripts() {
        // Initilize core scripts
        $core_scripts = array(
            'handlebars'    =>  $this->url . 'assets/js/handlebars.min-latest.js',
            'helpers'       =>  array(
                'src'           =>  $this->url . 'assets/js/uix-helpers' . $this->debug_scripts . '.js',
                'depends'       =>  array(
                    'jquery'
                )
            ),
            'admin'         =>  array(
                'src'           =>  $this->url . 'assets/js/uix-core' . $this->debug_scripts . '.js',
                'depends'       =>  array(
                    'jquery',
                    'handlebars'
                )               
            ),
            'modals'            =>  array(
                'src'           =>  $this->url . 'assets/js/uix-modals' . $this->debug_scripts . '.js',
                'depends'       =>  array(
                    'jquery'
                )               
            )
        );

        /**
         * Filter core UIX scripts
         *
         * @param array $core_scripts array of core UIX scripts to be registered
         */
        $core_scripts = apply_filters( 'uix_set_core_styles-' . $this->type, $core_scripts );

        // push to activly register scripts
        $this->scripts( $core_scripts );
    }
    
    /**
     * localize data
     *
     * @since 1.0.0
     */
    protected function localize_data(){
        wp_localize_script( $this->type . '-admin', 'UIX', $this->active_objects );
    }

    /**
     * get a UIX config store key
     * @since 1.0.0
     *
     * @return string $store_key the defiuned option name for this UIX object
     */
    public function store_key( $slug ){
        
        $uix = $this->get( $slug );
        $store_key = 'uix-' . $this->type . '-' . sanitize_text_field( $slug );
        if( !empty( $uix['store_key'] ) ){
            $store_key = $uix['store_key'];
        }

        return $store_key;
    }

    /**
     * Return a saved object from part by points
     *
     * @since 1.0.0
     *
     * @return    string/array    the requested setting
     */
    public static function get_val( $path ) {

        $path = explode( '.', $path );
        $temp = null;
        $slug = array_shift( $path );

        // get the instance
        $uix = static::get_instance();

        $temp = $uix->get_data( $slug );
        foreach ($path as $index => $value) {
            if( !isset( $temp[ $value ] ) ){
                return null;
            }
            $temp = $temp[ $value ];
        }

        return $temp;

    }
}