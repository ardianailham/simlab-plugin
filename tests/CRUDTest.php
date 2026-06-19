<?php
/**
 * Class CRUDTest
 *
 * @package Simlab
 */

class CRUDTest extends WP_UnitTestCase {

    private $alat_obj;
    private $bahan_obj;

    public function set_up() {
        parent::set_up();
        $this->alat_obj = new SL_SIMLAB_AlatClass();
        $this->bahan_obj = new SL_SIMLAB_BahanClass();
        
        // Ensure tables are created (bootstrap usually does this, but being safe)
        $plugin = new SL_SimlabPlugin();
        $plugin->_install();
    }

    /**
     * Test Alat CRUD
     */
    public function test_alat_crud() {
        // Create
        $data = [
            'Nama_Alat' => 'Mikroskop Binokuler',
            'Merk' => 'Olympus',
            'Qty' => 5
        ];
        $insert_id = $this->alat_obj->tambahAlat( $data );
        $this->assertNotEmpty( $insert_id );

        // Read
        $alat = $this->alat_obj->getAlatById( $this->db_insert_id() );
        $this->assertEquals( 'Mikroskop Binokuler', $alat['Nama_Alat'] );
        $this->assertEquals( 5, $alat['Qty'] );

        // Update
        $update_data = [
            'id' => $alat['id'],
            'Nama_Alat' => 'Mikroskop Binokuler Pro',
            'Merk' => 'Olympus X',
            'Qty' => 10
        ];
        $this->alat_obj->ubahAlat( $update_data );
        $updated = $this->alat_obj->getAlatById( $alat['id'] );
        $this->assertEquals( 'Mikroskop Binokuler Pro', $updated['Nama_Alat'] );
        $this->assertEquals( 10, $updated['Qty'] );

        // Delete
        $this->alat_obj->hapusAlat( $alat['id'] );
        $deleted = $this->alat_obj->getAlatById( $alat['id'] );
        $this->assertNull( $deleted );
    }

    /**
     * Test Bahan CRUD
     */
    public function test_bahan_crud() {
        // Create Catalog
        $bahan_data = [
            'Nama_Bahan' => 'Ethanol 96%',
            'Alias' => 'Alkohol',
            'Kategori' => 'Pelarut',
            'Merk' => 'Merck',
            'Satuan_Dasar' => 'ml'
        ];
        $this->bahan_obj->tambahBahan( $bahan_data );
        $bahan_id = $this->db_insert_id();
        $this->assertNotEmpty( $bahan_id );

        // Create Packaging (Kemasan)
        $kemasan_data = [
            'id_bahan' => $bahan_id,
            'label_kemasan' => 'Botol A1',
            'kapasitas_awal' => 1000,
            'satuan' => 'ml',
            'exp_date' => '2027-01-01',
            'letak' => 'Lemari A'
        ];
        $this->bahan_obj->tambahKemasan( $kemasan_data );
        $kemasan_id = $this->db_insert_id();
        $this->assertNotEmpty( $kemasan_id );

        // Read Total Stock
        $bahan = $this->bahan_obj->getBahanById( $bahan_id );
        $this->assertEquals( 1000, (float)$bahan['TotalJumlah'] );

        // Update Stock via Usage
        $this->bahan_obj->updateByKemasan( $kemasan_id, 200 ); // Use 200ml
        $kemasan = $this->bahan_obj->getKemasanById( $kemasan_id );
        $this->assertEquals( 800, (float)$kemasan['jumlah_tersedia'] );

        // Delete Catalog (should cascade)
        $this->bahan_obj->hapusBahan( $bahan_id );
        $this->assertNull( $this->bahan_obj->getBahanById( $bahan_id ) );
        $this->assertNull( $this->bahan_obj->getKemasanById( $kemasan_id ) );
    }

    /**
     * Test Bahan GHS fields CRUD
     */
    public function test_bahan_ghs_crud() {
        // Create Catalog with GHS data
        $bahan_data = [
            'Nama_Bahan' => 'Methanol',
            'Alias' => 'MeOH',
            'Kategori' => 'Pelarut',
            'Merk' => 'Sigma',
            'Satuan_Dasar' => 'ml',
            'ghs_code' => 'GHS02, GHS06, GHS08',
            'hazard_statement' => "H225: Highly flammable liquid and vapor\nH301: Toxic if swallowed\nH311: Toxic in contact with skin",
            'signal_word' => 'Danger'
        ];
        $this->bahan_obj->tambahBahan( $bahan_data );
        $bahan_id = $this->db_insert_id();
        $this->assertNotEmpty( $bahan_id );

        // Read and assert
        $bahan = $this->bahan_obj->getBahanById( $bahan_id );
        $this->assertEquals( 'Methanol', $bahan['Nama_Bahan'] );
        $this->assertEquals( 'Danger', $bahan['signal_word'] );

        $ghs_codes = maybe_unserialize( $bahan['ghs_code'] );
        $this->assertIsArray( $ghs_codes );
        $this->assertCount( 3, $ghs_codes );
        $this->assertEquals( 'GHS02', $ghs_codes[0] );
        $this->assertEquals( 'GHS06', $ghs_codes[1] );
        $this->assertEquals( 'GHS08', $ghs_codes[2] );

        $hazard_statements = maybe_unserialize( $bahan['hazard_statement'] );
        $this->assertIsArray( $hazard_statements );
        $this->assertCount( 3, $hazard_statements );
        $this->assertEquals( 'H225: Highly flammable liquid and vapor', $hazard_statements[0] );
        $this->assertEquals( 'H301: Toxic if swallowed', $hazard_statements[1] );
        $this->assertEquals( 'H311: Toxic in contact with skin', $hazard_statements[2] );

        // Update
        $update_data = [
            'id' => $bahan_id,
            'Nama_Bahan' => 'Methanol Purified',
            'Alias' => 'MeOH',
            'Kategori' => 'Pelarut',
            'Merk' => 'Sigma',
            'Satuan_Dasar' => 'ml',
            'ghs_code' => 'GHS02, GHS06',
            'hazard_statement' => "H225: Highly flammable liquid and vapor\nH301: Toxic if swallowed",
            'signal_word' => 'Warning'
        ];
        $this->bahan_obj->ubahBahan( $update_data );

        $updated = $this->bahan_obj->getBahanById( $bahan_id );
        $this->assertEquals( 'Methanol Purified', $updated['Nama_Bahan'] );
        $this->assertEquals( 'Warning', $updated['signal_word'] );
        
        $ghs_codes_updated = maybe_unserialize( $updated['ghs_code'] );
        $this->assertCount( 2, $ghs_codes_updated );
        $this->assertEquals( 'GHS06', $ghs_codes_updated[1] );

        // Cleanup
        $this->bahan_obj->hapusBahan( $bahan_id );
    }

    private function db_insert_id() {
        global $wpdb;
        return $wpdb->insert_id;
    }
}
