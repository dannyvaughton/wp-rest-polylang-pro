<?php
/**
 * Plugin Name: WP REST - Polylang Pro
 * Description: Polylang Pro integration for the WP REST API
 * Author: Danny Vaughton
 * Author URI: https://www.dannyvaughton.com
 * Version: 1.0.0
 * Plugin URI: https://github.com/dannyvaughton/wp-rest-polylang-pro
 * License: gpl-3.0
 */


class WP_REST_polylang
{

    static $instance = false;

    private function __construct() {
        // Check if polylang is installed
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        if (!is_plugin_active('polylang-pro/polylang.php')) {
            return;
        }

        add_action('rest_api_init', array($this, 'init'), 0);
    }

    public static function getInstance() {
        if ( !self::$instance )
            self::$instance = new self;
        return self::$instance;
    }

    public static function init() {
        global $polylang;

        if (isset($_GET['lang'])) {
            $current_lang = $_GET['lang'];

            $polylang->curlang = $polylang->model->get_language($current_lang);
        }

        $post_types = get_post_types( array( 'public' => true ), 'names' );
        $taxonomies = get_taxonomies( array( 'public' => true ), 'names' );

        foreach( $post_types as $post_type ) {
            if (pll_is_translated_post_type( $post_type )) {
                self::register_post_api_field($post_type);
            }
        }

        foreach( $taxonomies as $taxonomy ) {
            if (pll_is_translated_taxonomy( $taxonomy )) {
                self::register_taxonomy_api_field($taxonomy);
            }
        }
    }

    public function register_post_api_field($post_type) {
        register_rest_field(
            $post_type,
            "polylang_translations",
            array(
                "get_callback" => array( $this, "get_post_translations"  ),
                "schema" => null
            )
        );
    }

    public function register_taxonomy_api_field($taxonomy) {
        $taxonomy = ($taxonomy === 'post_tag') ? 'tag' : $taxonomy;
        register_rest_field(
            $taxonomy,
            "polylang_translations",
            array(
                "get_callback" => array( $this, "get_taxonomy_translations"  ),
                "schema" => null
            )
        );
    }

    public function get_current_lang( $object ) {
        return pll_get_post_language($object['id'], 'locale');
    }

    public function get_post_translations( $object ) {
        $translations = pll_get_post_translations($object['id']);

        return array_reduce($translations, function ($carry, $translation) {
            $item = array(
                'locale' => pll_get_post_language($translation, 'locale'),
                'id' => $translation
            );

            array_push($carry, $item);

            return $carry;
        }, array());
    }

    public function get_taxonomy_translations( $object ) {
        $translations = pll_get_term_translations($object['id']);
        return array_reduce($translations, function ($carry, $translation) {
            $item = array(
                'locale' => pll_get_post_language($translation, 'locale'),
                'id' => $translation
            );

            array_push($carry, $item);
            return $carry;
        }, array());
    }
}

$WP_REST_polylang = WP_REST_polylang::getInstance();
