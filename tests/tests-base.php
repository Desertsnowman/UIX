<?php
/**
 * UIX tests base
 *
 * @package   Tests_UIX
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link      http://cramer.co.za
 */

class Test_UIX extends WP_UnitTestCase {

    public function register_paths( $uix ) {
        $uix->register( __DIR__ . '/ui' );
    }

    public function test_register_hook() {
        // checks to see if the auto loader loaded structures
        $uix = uix();
        $this->assertNotEmpty( $uix->ui );
    }

    public function test_locations() {
        // add register action loaded the page demo
        $uix = uix();
        $this->assertNotEmpty( $uix->ui->page['uixdemo'] );
    }

    public function test_auto_adding() {
        $uix    = uix();
        $panel  = $uix->add( 'panel', 'test_panel', array(
            'label' =>  'test panel'
        ) );
        // check slug
        $this->assertSame( $panel->slug, 'test_panel' );
        // check id
        $this->assertSame( $panel->id(), 'uix-panel-test_panel' );
        // check child
        $this->assertEmpty( $panel->child );
        // check data
        $this->assertEmpty( $panel->get_data() );

        return $panel;
    }

}