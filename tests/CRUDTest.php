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
     * Test Alat CRUD with gambar field
     */
    public function test_alat_crud() {
        // Create
        $data = [
            'Nama_Alat' => 'Mikroskop Binokuler',
            'Merk' => 'Olympus',
            'Qty' => 5,
            'gambar' => 'http://example.com/mikroskop.jpg'
        ];
        $insert_id = $this->alat_obj->tambahAlat( $data );
        $this->assertNotEmpty( $insert_id );

        // Read
        $alat = $this->alat_obj->getAlatById( $this->db_insert_id() );
        $this->assertEquals( 'Mikroskop Binokuler', $alat['Nama_Alat'] );
        $this->assertEquals( 5, $alat['Qty'] );
        $this->assertEquals( 'http://example.com/mikroskop.jpg', $alat['gambar'] );

        // Update
        $update_data = [
            'id' => $alat['id'],
            'Nama_Alat' => 'Mikroskop Binokuler Pro',
            'Merk' => 'Olympus X',
            'Qty' => 10,
            'gambar' => 'http://example.com/mikroskop_pro.jpg'
        ];
        $this->alat_obj->ubahAlat( $update_data );
        $updated = $this->alat_obj->getAlatById( $alat['id'] );
        $this->assertEquals( 'Mikroskop Binokuler Pro', $updated['Nama_Alat'] );
        $this->assertEquals( 10, $updated['Qty'] );
        $this->assertEquals( 'http://example.com/mikroskop_pro.jpg', $updated['gambar'] );

        // Delete
        $this->alat_obj->hapusAlat( $alat['id'] );
        $deleted = $this->alat_obj->getAlatById( $alat['id'] );
        $this->assertNull( $deleted );
    }

    /**
     * Test Bahan CRUD with gambar field
     */
    public function test_bahan_crud() {
        // Create Catalog
        $bahan_data = [
            'Nama_Bahan' => 'Ethanol 96%',
            'Alias' => 'Alkohol',
            'Kategori' => 'Pelarut',
            'Merk' => 'Merck',
            'Satuan_Dasar' => 'ml',
            'gambar' => 'http://example.com/ethanol.jpg'
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

        // Read Total Stock and image
        $bahan = $this->bahan_obj->getBahanById( $bahan_id );
        $this->assertEquals( 1000, (float)$bahan['TotalJumlah'] );
        $this->assertEquals( 'http://example.com/ethanol.jpg', $bahan['gambar'] );

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

    /**
     * Test Import Alat CSV
     */
    public function test_import_alat_csv() {
        $admin_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
        wp_set_current_user( $admin_id );

        // Create temporary CSV file
        $csv_content = "ID,Nama Alat,Merk,Qty\n,Test Import Alat,Brand X,15\n";
        $temp_file = tempnam( sys_get_temp_dir(), 'csv' );
        file_put_contents( $temp_file, $csv_content );

        $file_array = [
            'name'     => 'test_import_alat.csv',
            'tmp_name' => $temp_file,
            'error'    => UPLOAD_ERR_OK
        ];

        $export_import = new SL_SIMLAB_ExportImportClass();
        $imported_count = $export_import->importAlat( $file_array );
        
        @unlink( $temp_file );

        $this->assertEquals( 1, $imported_count );

        // Verify in DB
        $alat_class = new SL_SIMLAB_AlatClass();
        $all_alat = $alat_class->getAlat();
        $found = false;
        foreach ( $all_alat as $alat ) {
            if ( $alat['Nama_Alat'] === 'Test Import Alat' ) {
                $found = true;
                $this->assertEquals( 'Brand X', $alat['Merk'] );
                $this->assertEquals( 15, $alat['Qty'] );
                break;
            }
        }
        $this->assertTrue( $found, "Imported Alat should exist in database" );
    }

    /**
     * Test Import Bahan CSV
     */
    public function test_import_bahan_csv() {
        $admin_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
        wp_set_current_user( $admin_id );

        // Create temporary CSV file
        $csv_content = "ID,Nama Bahan,Alias,Kategori,Label Botol,Jumlah,Satuan,Merk,Exp,Letak\n,Test Import Bahan,Bhn,Chemicals,Bottle Z,500,ml,Merck,12/12/2026,Shelf B\n";
        $temp_file = tempnam( sys_get_temp_dir(), 'csv' );
        file_put_contents( $temp_file, $csv_content );

        $file_array = [
            'name'     => 'test_import_bahan.csv',
            'tmp_name' => $temp_file,
            'error'    => UPLOAD_ERR_OK
        ];

        $export_import = new SL_SIMLAB_ExportImportClass();
        $imported_count = $export_import->importBahan( $file_array );
        
        @unlink( $temp_file );

        $this->assertEquals( 1, $imported_count );

        // Verify in DB
        $bahan_class = new SL_SIMLAB_BahanClass();
        $all_bahan = $bahan_class->getBahan();
        $found_bahan = null;
        foreach ( $all_bahan as $bahan ) {
            if ( $bahan['Nama_Bahan'] === 'Test Import Bahan' ) {
                $found_bahan = $bahan;
                break;
            }
        }
        $this->assertNotNull( $found_bahan, "Imported Bahan should exist in database" );
        $this->assertEquals( 'Bhn', $found_bahan['Alias'] );
        $this->assertEquals( 500.0, (float)$found_bahan['StokTotal'] );

        // Verify kemasan in DB
        $kemasan_list = $bahan_class->getKemasanByBahan( $found_bahan['id'] );
        $this->assertCount( 1, $kemasan_list );
        $this->assertEquals( 'Bottle Z', $kemasan_list[0]['label_kemasan'] );
        $this->assertEquals( 500.0, (float)$kemasan_list[0]['jumlah_tersedia'] );
        $this->assertEquals( '2026-12-12', $kemasan_list[0]['exp_date'] );
        $this->assertEquals( 'Shelf B', $kemasan_list[0]['letak'] );
    }

    /**
     * Test Logbook Alat CRUD and concurrency stock check
     */
    public function test_logbook_alat_crud() {
        $admin_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
        wp_set_current_user( $admin_id );

        // Create equipment
        $data_alat = [
            'Nama_Alat' => 'Centrifuge',
            'Merk' => 'Eppendorf',
            'Qty' => 3
        ];
        $this->alat_obj->tambahAlat( $data_alat );
        $alat_id = $this->db_insert_id();

        $logbook_alat = new SL_SIMLAB_LogbookAlatClass();

        // Add valid booking within stock limit
        $now = time();
        $start_date = wp_date( 'Y-m-d H:i:s', $now );
        $end_date = wp_date( 'Y-m-d H:i:s', $now + 3600 ); // +1 hour

        $data_booking = [
            'id_alat' => $alat_id,
            'Qty' => 2,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];

        $res = $logbook_alat->addLogAlat( $data_booking );
        $this->assertNotEmpty( $res );
        $booking_id = $this->db_insert_id();

        // Verify booking in DB
        $booking = $logbook_alat->getLogAlatById( $booking_id );
        $this->assertNotNull( $booking );
        $this->assertEquals( 'Centrifuge', $booking['Nama_Alat'] );
        $this->assertEquals( 2, $booking['qty'] );

        // Try to book more during the same time (exceeds stock 3 since 2 are already booked)
        ob_start();
        $res_fail = $logbook_alat->addLogAlat([
            'id_alat' => $alat_id,
            'Qty' => 2,
            'start_date' => $start_date,
            'end_date' => $end_date
        ]);
        ob_end_clean();

        $this->assertEquals( 0, $res_fail );

        // Delete the logbook entry
        $logbook_alat->hapusLog( $booking_id );
        $deleted = $logbook_alat->getLogAlatById( $booking_id );
        $this->assertNull( $deleted );

        // Cleanup Alat
        $this->alat_obj->hapusAlat( $alat_id );
    }

    /**
     * Test Logbook Bahan CRUD and stock reduction
     */
    public function test_logbook_bahan_crud() {
        $admin_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
        wp_set_current_user( $admin_id );

        // Create material catalog
        $bahan_data = [
            'Nama_Bahan' => 'Hydrochloric Acid',
            'Alias' => 'HCl',
            'Kategori' => 'Asam',
            'Merk' => 'Merck',
            'Satuan_Dasar' => 'ml'
        ];
        $this->bahan_obj->tambahBahan( $bahan_data );
        $bahan_id = $this->db_insert_id();

        // Add chemical packaging
        $kemasan_data = [
            'id_bahan' => $bahan_id,
            'label_kemasan' => 'Bottle HCl-01',
            'kapasitas_awal' => 1000,
            'satuan' => 'ml',
            'exp_date' => '2026-12-31',
            'letak' => 'Acid Cabinet'
        ];
        $this->bahan_obj->tambahKemasan( $kemasan_data );
        $kemasan_id = $this->db_insert_id();

        // Add Logbook Bahan entry (use chemical)
        $logbook_bahan = new SL_SIMLAB_LogbookBahanClass();
        $log_data = [
            'id_kemasan' => $kemasan_id,
            'Qty' => 150.5,
            'tanggal' => wp_date( 'Y-m-d H:i:s' ),
            'tujuan' => 'Reagent preparation'
        ];

        $log_id = $logbook_bahan->addLogBahan( $log_data );
        $this->assertNotEmpty( $log_id );

        // Check if log is saved correctly
        $log = $logbook_bahan->getLogBahanById( $log_id );
        $this->assertNotNull( $log );
        $this->assertEquals( 'Hydrochloric Acid', $log['Nama_Bahan'] );
        $this->assertEquals( 150.5, (float)$log['qty'] );

        // Check if kemasan stock is reduced (1000 - 150.5 = 849.5)
        $kemasan = $this->bahan_obj->getKemasanById( $kemasan_id );
        $this->assertEquals( 849.5, (float)$kemasan['jumlah_tersedia'] );

        // Try to use more than available stock (exceed 849.5)
        ob_start();
        $fail_log = $logbook_bahan->addLogBahan([
            'id_kemasan' => $kemasan_id,
            'Qty' => 900,
            'tanggal' => wp_date( 'Y-m-d H:i:s' )
        ]);
        ob_end_clean();
        $this->assertEquals( 0, $fail_log );

        // Clean up
        $logbook_bahan->hapusLog( $log_id );
        $this->bahan_obj->hapusBahan( $bahan_id );
    }

    /**
     * Test PubChem lookup and cache
     */
    public function test_pubchem_lookup() {
        // Mock the wp_remote_get response to avoid external API dependencies
        add_filter( 'pre_http_request', function( $preempt, $parsed_args, $url ) {
            if ( strpos( $url, 'property/IUPACName' ) !== false ) {
                return [
                    'headers' => [],
                    'body'    => json_encode( [
                        'PropertyTable' => [
                            'Properties' => [
                                [
                                    'CID' => 702,
                                    'IUPACName' => 'ethanol',
                                    'IsomericSMILES' => 'CCO',
                                    'MolecularFormula' => 'C2H6O',
                                    'MolecularWeight' => '46.07'
                                ]
                            ]
                        ]
                    ] ),
                    'response' => [ 'code' => 200, 'message' => 'OK' ],
                    'cookies'  => [],
                    'filename' => null
                ];
            }
            if ( strpos( $url, 'GHS+Classification' ) !== false ) {
                return [
                    'headers' => [],
                    'body'    => json_encode( [
                        'Record' => [
                            'Section' => [
                                [
                                    'Name' => 'GHS Classification',
                                    'Information' => [
                                        [
                                            'Name' => 'Signal',
                                            'Value' => [
                                                'StringWithMarkup' => [
                                                    [ 'String' => 'Danger' ]
                                                ]
                                            ]
                                        ],
                                        [
                                            'Name' => 'Pictogram(s)',
                                            'Value' => [
                                                'StringWithMarkup' => [
                                                    [
                                                        'Markup' => [
                                                            [ 'Extra' => 'GHS02' ],
                                                            [ 'Extra' => 'GHS07' ]
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ],
                                        [
                                            'Name' => 'Hazard Statement',
                                            'Value' => [
                                                'StringWithMarkup' => [
                                                    [ 'String' => 'H225: Highly flammable liquid and vapor' ],
                                                    [ 'String' => 'H319: Causes serious eye irritation' ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ] ),
                    'response' => [ 'code' => 200, 'message' => 'OK' ],
                    'cookies'  => [],
                    'filename' => null
                ];
            }
            return $preempt;
        }, 10, 3 );

        // Run lookup
        $result = SL_SIMLAB_PubChemClass::lookup( 'ethanol' );

        $this->assertNotWPError( $result );
        $this->assertEquals( 702, $result['cid'] );
        $this->assertEquals( 'ethanol', $result['iupac_name'] );
        $this->assertEquals( 'CCO', $result['smiles'] );
        $this->assertEquals( 'Danger', $result['hazard']['signal_word'] );
        $this->assertContains( 'GHS02', $result['hazard']['all_codes'] );
        $this->assertContains( 'GHS07', $result['hazard']['all_codes'] );
        $this->assertContains( 'H225: Highly flammable liquid and vapor', $result['hazard']['all_statements'] );
        $this->assertContains( 'H319: Causes serious eye irritation', $result['hazard']['all_statements'] );
    }

    private function db_insert_id() {
        global $wpdb;
        return $wpdb->insert_id;
    }
}
