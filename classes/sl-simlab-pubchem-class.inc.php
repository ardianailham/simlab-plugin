<?php
/**
 * SIMLAB PubChem Integration
 *
 * Provides a server-side WordPress AJAX proxy to the PubChem PUG REST API.
 * The proxy avoids mixed-content / CORS issues on some WordPress hosts and
 * caches results in a transient so repeated page-loads are instant.
 *
 * Fetches:
 *  - Structure PNG (image URL, returned as direct PubChem URL for <img>)
 *  - SMILES (IsomericSMILES)
 *  - Primary GHS hazard statement + pictogram icon URL
 *
 * Public AJAX action: sl_pubchem_lookup  (no login required)
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class SL_SIMLAB_PubChemClass
{
    /** Transient TTL — 7 days; PubChem data is stable */
    const CACHE_TTL = WEEK_IN_SECONDS;

    /** Base URL for PubChem PUG REST */
    const BASE = 'https://pubchem.ncbi.nlm.nih.gov/rest/pug';

    /**
     * Register the public AJAX action.
     * Call this from the plugin bootstrap.
     */
    public static function register_ajax()
    {
        add_action( 'wp_ajax_sl_pubchem_lookup',        [ __CLASS__, 'ajax_handler' ] );
        add_action( 'wp_ajax_nopriv_sl_pubchem_lookup', [ __CLASS__, 'ajax_handler' ] );
    }

    /**
     * AJAX handler — expects GET ?action=sl_pubchem_lookup&name=<compound_name>
     */
    public static function ajax_handler()
    {
        check_ajax_referer( 'sl_pubchem_lookup', 'nonce' );

        $name = isset( $_GET['name'] ) ? sanitize_text_field( wp_unslash( $_GET['name'] ) ) : '';

        if ( empty( $name ) ) {
            wp_send_json_error( [ 'message' => 'No compound name provided.' ], 400 );
        }

        $result = self::lookup( $name );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ], 404 );
        }

        wp_send_json_success( $result );
    }

    /**
     * Look up a compound by name.
     * Returns an associative array or WP_Error.
     *
     * @param  string $name  Compound name (e.g. "ethanol", "sulfuric acid")
     * @return array|WP_Error
     */
    public static function lookup( $name )
    {
        $cache_key = 'sl_pubchem_v3_' . md5( strtolower( trim( $name ) ) );
        $cached    = get_transient( $cache_key );
        if ( false !== $cached ) {
            return $cached;
        }

        $prop_url = self::BASE
            . '/compound/name/' . rawurlencode( $name )
            . '/property/IUPACName,IsomericSMILES,CanonicalSMILES,SMILES,MolecularFormula,MolecularWeight/JSON';

        $prop_resp = wp_remote_get( $prop_url, [
            'timeout'    => 12,
            'user-agent' => 'SIMLAB-Plugin/1.0.1; ' . home_url(),
        ] );
        if ( is_wp_error( $prop_resp ) ) {
            return new WP_Error( 'pubchem_http', 'PubChem request failed: ' . $prop_resp->get_error_message() );
        }
        if ( 200 !== wp_remote_retrieve_response_code( $prop_resp ) ) {
            return new WP_Error( 'pubchem_notfound', 'Compound "' . esc_html( $name ) . '" not found in PubChem.' );
        }

        $prop_body = json_decode( wp_remote_retrieve_body( $prop_resp ), true );
        if ( empty( $prop_body['PropertyTable']['Properties'][0] ) ) {
            return new WP_Error( 'pubchem_parse', 'Unexpected PubChem response format.' );
        }

        $props = $prop_body['PropertyTable']['Properties'][0];
        $cid   = (int) $props['CID'];

        // ── 2. Hazard / GHS data ───────────────────────────────────────────
        $hazard = self::_fetch_hazard( $cid );

        // Extract properties carefully — keys can be inconsistent in casing or presence
        $f_smiles = '';
        foreach ( ['IsomericSMILES', 'CanonicalSMILES', 'SMILES', 'smiles'] as $k ) {
            if ( ! empty( $props[$k] ) ) { $f_smiles = $props[$k]; break; }
        }

        $result = [
            'cid'              => $cid,
            'iupac_name'       => $props['IUPACName'] ?? '',
            'smiles'           => $f_smiles,
            'formula'          => $props['MolecularFormula'] ?? '',
            'molecular_weight' => $props['MolecularWeight'] ?? '',
            // Direct PubChem image URL — safe to embed in <img src>
            'structure_png'    => self::BASE . "/compound/cid/{$cid}/PNG?image_size=300x300",
            'pubchem_url'      => "https://pubchem.ncbi.nlm.nih.gov/compound/{$cid}",
            'hazard'           => $hazard,
        ];

        set_transient( $cache_key, $result, 30 ); // Short TTL for testing
        return $result;
    }

    /**
     * Fetch primary GHS hazard for a CID.
     * Returns an array: [ 'code', 'statement', 'pictogram_url', 'signal_word' ]
     * Returns null if no GHS data is available.
     */
    private static function _fetch_hazard( $cid )
    {
        $url = self::BASE . "/compound/cid/{$cid}/JSON";
        $resp = wp_remote_get( $url, [ 'timeout' => 12 ] );
        if ( is_wp_error( $resp ) || 200 !== wp_remote_retrieve_response_code( $resp ) ) {
            return null;
        }

        $body = json_decode( wp_remote_retrieve_body( $resp ), true );
        $sections = $body['PC_Compounds'][0]['props'] ?? [];

        // Walk through the flat props array looking for GHS Classification
        $ghs_statements = [];
        $pictograms     = [];
        $signal_word    = '';

        // PubChem full compound JSON doesn't carry GHS directly —
        // we use the PUG View endpoint which is richer.
        $view_url  = "https://pubchem.ncbi.nlm.nih.gov/rest/pug_view/data/compound/{$cid}/JSON?heading=GHS+Classification";
        $view_resp = wp_remote_get( $view_url, [
            'timeout'    => 15,
            'user-agent' => 'SIMLAB-Plugin/1.0.1; ' . home_url(),
        ] );
        if ( is_wp_error( $view_resp ) || 200 !== wp_remote_retrieve_response_code( $view_resp ) ) {
            return null;
        }

        $view = json_decode( wp_remote_retrieve_body( $view_resp ), true );

        // Recursively hunt for sections
        self::_extract_ghs( $view, $ghs_statements, $pictograms, $signal_word );

        if ( empty( $ghs_statements ) && empty( $pictograms ) ) {
            return null;
        }

        // Pick the first / most severe pictogram
        $primary_pictogram = '';
        $primary_code      = '';
        if ( ! empty( $pictograms ) ) {
            // PubChem returns pictogram names like "GHS02", map to SVG URL
            $first            = $pictograms[0];
            $primary_code     = $first;
            $primary_pictogram = self::_ghs_icon_url( $first );
        }

        // Primary hazard statement (first one)
        $primary_statement = ! empty( $ghs_statements ) ? $ghs_statements[0] : '';

        return [
            'code'          => $primary_code,
            'statement'     => $primary_statement,
            'all_statements'=> $ghs_statements,
            'pictogram_url' => $primary_pictogram,
            'all_pictograms'=> array_map( [ __CLASS__, '_ghs_icon_url' ], $pictograms ),
            'signal_word'   => $signal_word,
        ];
    }

    /**
     * Recursively extract GHS hazard statements, pictograms, and signal word
     * from the PUG View JSON tree. Deep search for specific labels.
     */
    private static function _extract_ghs( $node, &$statements, &$pictograms, &$signal_word )
    {
        if ( ! is_array( $node ) ) { return; }

        // 1. Check if this is an Information object
        $name = $node['Name'] ?? '';
        $val  = $node['Value'] ?? null;

        if ( $val && is_array( $val ) ) {
            $swms = $val['StringWithMarkup'] ?? [];

            // Signal Word
            if ( stripos( $name, 'Signal' ) !== false ) {
                foreach ( $swms as $swm ) {
                    $s = trim( $swm['String'] ?? '' );
                    if ( $s && ! $signal_word ) { $signal_word = $s; }
                }
            }

            // Pictograms
            if ( stripos( $name, 'Pictogram' ) !== false ) {
                foreach ( $swms as $swm ) {
                    // Check Markup icons
                    foreach ( $swm['Markup'] ?? [] as $markup ) {
                        $extra = $markup['Extra'] ?? '';
                        $url   = $markup['URL'] ?? '';
                        if ( preg_match( '/GHS\d+/i', $extra . $url, $m ) ) {
                            $code = strtoupper( $m[0] );
                            if ( ! in_array( $code, $pictograms, true ) ) { $pictograms[] = $code; }
                        }
                    }
                    // Fallback to string match
                    $s = $swm['String'] ?? '';
                    if ( preg_match( '/GHS\d+/i', $s, $m ) ) {
                        $code = strtoupper( $m[0] );
                        if ( ! in_array( $code, $pictograms, true ) ) { $pictograms[] = $code; }
                    }
                }
            }

            // Hazard Statements
            if ( stripos( $name, 'Hazard Statement' ) !== false && stripos( $name, 'Precautionary' ) === false ) {
                foreach ( $swms as $swm ) {
                    $s = trim( $swm['String'] ?? '' );
                    if ( $s && ! in_array( $s, $statements, true ) ) { $statements[] = $s; }
                }
            }
        }

        // 2. Iterate through all array children (Section, Information, etc.)
        foreach ( $node as $key => $child ) {
            if ( is_array( $child ) ) {
                if ( isset( $child[0] ) ) {
                    // Sequential array (like Section list)
                    foreach ( $child as $item ) {
                        self::_extract_ghs( $item, $statements, $pictograms, $signal_word );
                    }
                } else {
                    // Associative array
                    self::_extract_ghs( $child, $statements, $pictograms, $signal_word );
                }
            }
        }
    }

    /**
     * Return the UN ECE SVG URL for a GHS pictogram code like "GHS02".
     * Falls back to PubChem's own hosted image.
     */
    private static function _ghs_icon_url( $code )
    {
        // PubChem hosts these at a stable URL
        $num = preg_replace( '/[^0-9]/', '', $code );
        return "https://pubchem.ncbi.nlm.nih.gov/images/ghs/GHS{$num}.svg";
    }
}
