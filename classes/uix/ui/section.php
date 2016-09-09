<?php
/**
 * UIX section
 *
 * @package   ui
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 David Cramer
 */
namespace uix\ui;

/**
 * A generic holder for multiple controls. this panel type does not handle saving, but forms part of the data object tree.
 *
 * @since 1.0.0
 * @see \uix\uix
 */
class section extends panel {

    /**
     * The type of object
     *
     * @since 1.0.0
     * @access public
     * @var      string
     */
    public $type = 'section';


    /**
     * Define core page styles
     *
     * @since 1.0.0
     * @access public
     */
    public function uix_styles() {
        $pages_styles = array(
            'sections'    =>  $this->url . 'assets/css/uix-sections' . $this->debug_styles . '.css',
        );
        $this->styles( $pages_styles );
    }

    /**
     * Render the Section
     *
     * @since 1.0.0
     * @access public
     */
    public function render(){
        
        if( !isset( $this->struct['active'] ) ){
            $this->struct['active'] = 'true';
        }

        echo '<div id="' . esc_attr( $this->id() ) . '" class="uix-section" aria-hidden="' . esc_attr( $this->struct['active'] ) . '">';
            echo '<div class="uix-section-content">';
                if( !empty( $this->struct['description'] ) ){
                    echo '<p class="description">' . esc_html( $this->struct['description'] ) . '</p>';
                }
                if( !empty( $this->struct['template'] ) ){
                    // tempalte
                    if( file_exists( $this->struct['template'] ) ){
                        include $this->struct['template'];
                    }else{
                        echo esc_html__( 'Template not found: ', 'text-domain' ) . $this->struct['template'];
                    }

                }elseif( !empty( $this->child ) ){
                    foreach ( $this->child as $control ) {
                        $control->render();
                    }                    
                }
                
            echo '</div>';
        echo '</div>';
    }

    /**
     * checks if the current section is active
     *
     * @since 1.0.0
     * @access public
     */
    public function is_active(){
        return $this->parent->is_active();
    }

}