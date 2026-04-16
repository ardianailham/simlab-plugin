<?php
/**
 * Class UtilityTest
 *
 * @package Simlab
 */

class UtilityTest extends WP_UnitTestCase {

    /**
     * Test SL_SIMLAB_Auth roles
     */
    public function test_auth_roles() {
        $roles = SL_SIMLAB_Auth::get_roles();
        $this->assertIsArray( $roles );
        $this->assertArrayHasKey( 1, $roles );
        $this->assertEquals( 'Admin', $roles[1] );
        $this->assertEquals( 'Leader', $roles[2] );
        $this->assertEquals( 'Member', $roles[3] );
    }

    /**
     * Test SL_SIMLAB_Auth user roles
     */
    public function test_auth_user_role_assignment() {
        $user_id = $this->factory->user->create();
        
        // Default should be Member (3)
        $this->assertEquals( 3, SL_SIMLAB_Auth::get_user_role( $user_id ) );
        $this->assertTrue( SL_SIMLAB_Auth::is_member( $user_id ) );
        $this->assertFalse( SL_SIMLAB_Auth::is_admin( $user_id ) );

        // Set to Admin
        SL_SIMLAB_Auth::set_user_role( $user_id, 1 );
        $this->assertEquals( 1, SL_SIMLAB_Auth::get_user_role( $user_id ) );
        $this->assertTrue( SL_SIMLAB_Auth::is_admin( $user_id ) );

        // Set to Leader
        SL_SIMLAB_Auth::set_user_role( $user_id, 2 );
        $this->assertEquals( 2, SL_SIMLAB_Auth::get_user_role( $user_id ) );
        $this->assertTrue( SL_SIMLAB_Auth::is_leader( $user_id ) );
    }

    /**
     * Test SL_SIMLAB_Auth capability
     */
    public function test_auth_capabilities() {
        $admin_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
        $user_id = $this->factory->user->create();

        // Admin (WP) should always be Simlab Admin (1)
        $this->assertEquals( 1, SL_SIMLAB_Auth::get_user_role( $admin_id ) );
        $this->assertTrue( SL_SIMLAB_Auth::is_admin( $admin_id ) );

        // Booking permission
        $this->assertTrue( SL_SIMLAB_Auth::can_book( $admin_id ) );
        $this->assertTrue( SL_SIMLAB_Auth::can_book( $user_id ) );

        // Deleting logs
        $this->assertTrue( SL_SIMLAB_Auth::can_delete_log( $admin_id, $user_id ) ); // Admin can delete anyone's
        $this->assertTrue( SL_SIMLAB_Auth::can_delete_log( $user_id, $user_id ) ); // User can delete own
        $this->assertFalse( SL_SIMLAB_Auth::can_delete_log( $user_id, $admin_id ) ); // User cannot delete admin's
    }

    /**
     * Test SL_SIMLAB_BaseClass getTime
     */
    public function test_base_get_time() {
        $base = new SL_SIMLAB_BaseClass();
        $time = $base->getTime();
        
        $this->assertIsArray( $time );
        $this->assertCount( 2, $time );
        
        // Check format Y-m-d\TH:i
        $this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $time[0] );
        $this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $time[1] );
        
        // Check interval (should be 1 hour difference approximately)
        $start = strtotime( str_replace( 'T', ' ', $time[0] ) );
        $end = strtotime( str_replace( 'T', ' ', $time[1] ) );
        $this->assertEquals( 3600, $end - $start );
    }

    /**
     * Test template functions
     */
    public function test_template_functions() {
        $templates = sl_template_list();
        $this->assertArrayHasKey( 'sl_simlab_default.php', $templates );
        $this->assertEquals( 'SIMLAB Default', $templates['sl_simlab_default.php'] );

        $registered = sl_template_register( [], null, null );
        $this->assertArrayHasKey( 'sl_simlab_default.php', $registered );
    }

    /**
     * Test admin header and footer output
     */
    public function test_admin_header_footer() {
        ob_start();
        SL_SimlabPlugin::admin_header( 'Test Title', 'fa-test' );
        $header = ob_get_clean();

        $this->assertStringContainsString( 'Test Title', $header );
        $this->assertStringContainsString( 'fa-test', $header );
        $this->assertStringContainsString( 'SIMLAB', $header );

        ob_start();
        SL_SimlabPlugin::admin_footer();
        $footer = ob_get_clean();

        $this->assertStringContainsString( 'SIMLAB', $footer );
        $this->assertStringContainsString( '&copy;', $footer );
    }
}
