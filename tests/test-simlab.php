<?php
/**
 * Class SimlabTest
 *
 * @package Simlab
 */

/**
 * Sample test case.
 */
class SimlabTest extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	public function test_plugin_globals_initialized() {
		// Replace this with some actual testing code.
		$this->assertTrue( class_exists( 'SL_SimlabPlugin' ), 'Main plugin class should exist.' );
        $this->assertTrue( class_exists( 'SL_SIMLAB_AlatClass' ), 'Alat class should exist.' );
	}
    
    public function test_get_time() {
        $base = new SL_SIMLAB_BaseClass();
        $time = $base->getTime();
        
        $this->assertIsArray( $time );
        $this->assertCount( 2, $time );

        // Should be formatted properly for datetime-local
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $time[0]);
    }
    
    public function test_menu_slug_alat() {
        $alat = new SL_SIMLAB_AlatClass();
        $this->assertEquals( '-daftar-alat', $alat->menu_slug );
    }

    public function test_menu_slug_bahan() {
        $bahan = new SL_SIMLAB_BahanClass();
        $this->assertEquals( '-daftar-bahan', $bahan->menu_slug );
    }

    public function test_admin_settings_menu_added() {
        // Since admin menus are loaded on 'admin_menu' hook trigger
        $plugin = new SL_SimlabPlugin();
        $plugin->simlab_admin_menu();

        global $submenu;
        $this->assertArrayHasKey( 'simlab', $submenu, "Settings menu not registered" );
    }
}
