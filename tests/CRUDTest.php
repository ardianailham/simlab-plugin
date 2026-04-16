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

    private function db_insert_id() {
        global $wpdb;
        return $wpdb->insert_id;
    }
}
