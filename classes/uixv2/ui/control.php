<?php
/**
 * UIX Metaboxes
 *
 * @package   uixv2
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 David Cramer
 */
namespace uixv2\ui;

/**
 * UIX Control class.
 *
 * @since       2.0.0
 */
class control extends \uixv2\data\data{

    /**
     * The type of object
     *
     * @since       2.0.0
     * @access public
     * @var         string
     */
    public $type = 'control';

    /**
     * Hold the values of the controls
     *
     * @since   2.0.0
     * @access protected
     * @var     array
     */
    protected $data = array();

    /**
     * Register the UIX objects
     *
     * @since 2.0.0
     * @access public
     * @param string $slug Object slug
     * @param array $object object structure array
     * @return object|\uix object instance
     */
    public static function register( $slug, $object, $parent = null ) {
            // get the current instance
            if( empty( $object['type'] ) )
                $object['type'] = 'text';

            $caller = get_called_class() . '\\' . $object['type'];
            
            return new $caller( $slug, $object, $parent );
    }

    /**
     * Sets the controls data
     *
     * @since 2.0.0
     * @see \uixv2\uix
     * @access public
     */
    public function setup() {
        // run parents to setup sanitization filters
        parent::setup();
        $data = uixv2()->request_vars( 'post' );
        if( isset( $data['uix'][ $this->parent->slug ][ $this->slug ] ) ){
            $this->set_data( $data['uix'][ $this->parent->slug ][ $this->slug ] );
        }

    }
    
    /**
     * Define core page styles
     *
     * @since 2.0.0
     * @access public
     */
    public function uix_styles() {
        $styles = array(
            'control-style'    =>  $this->url . 'assets/css/controls' . $this->debug_styles . '.css',
        );
        $this->styles( $styles );
    }

    /**
     * Create and Return the control's input name
     *
     * @since 2.0.0
     * @access public
     * @return string The control name
     */
    public function name(){
        return 'uix[' . $this->parent->slug . '][' . $this->slug . ']';
    }

    /**
     * Create and Return the control's ID
     *
     * @since 2.0.0
     * @access public
     * @return string The control ID
     */
    public function id(){
        return 'uix_' . $this->parent->slug . '-' . $this->slug;
    }


    /**
     * Gets the classes for the control input
     *
     * @since  2.0.0
     * @access public
     * @return array
     */
    public function classes() {

        $classes = array(
            'widefat'
        );

        return $classes;
    }


    /**
     * Gets the attributes for the control.
     *
     * @since  2.0.0
     * @access public
     * @return array Attributes for the input field
     */
    public function attributes() {

        $attributes = array(
            'id'        =>  $this->id(),
            'name'      =>  $this->name(),
            'class'     =>  implode( ' ', $this->classes() )
        );

        return $attributes;
    }

    /**
     * Build Attributes for the input control
     *
     * @since  2.0.0
     * @access public
     * @return string Attributes setup for the input field 
     */
    public function build_attributes() {
        
        foreach( $this->attributes() as $att => $value) {
            $attributes[] = sprintf( '%s="%s" ', esc_html( $att ), esc_attr( $value ) );
        }

        return implode( ' ', $attributes );
    }

    /**
     * Returns the main input field for rendering
     *
     * @since 2.0.0
     * @see \uixv2\ui\uix
     * @access public
     * @return string Input field HTML striung
     */
    public function input(){

        return '<input type="' . esc_attr( $this->type ) . '" value="' . esc_attr( $this->get_data() ) . '" ' . $this->build_attributes() . '>';
    }    

    /**
     * Returns the label for the control
     *
     * @since 2.0.0
     * @access public
     * @return string Lable string 
     */
    public function label(){
        
        if( isset( $this->struct['label'] ) )
            return '<label for="' . esc_attr( $this->id() ) . '"><span class="uix-control-label">' . esc_html( $this->struct['label'] ) . '</span></label>';

        return '';
    }


    /**
     * Returns the description for the control
     *
     * @since 2.0.0
     * @access public
     * @return string description string 
     */
    public function description(){
        
        if( isset( $this->struct['description'] ) )
            return '<span class="uix-control-description">' . esc_html( $this->struct['description'] ) . '</span>';

        return '';
    }


    /**
     * Render the Control
     *
     * @since 2.0.0
     * @see \uixv2\ui\uix
     * @access public
     */
    public function render(){

        echo '<div id="control-' . esc_attr( $this->slug ) . '" class="uix-control uix-control-' . esc_attr( $this->type ) . '">';
            
            echo $this->label();
            echo $this->input();
            echo $this->description();

        echo '</div>';
    }

    /**
     * checks if the current control is active
     *
     * @since 2.0.0
     * @access public
     */
    public function is_active(){
        return $this->parent->is_active();
    }

}