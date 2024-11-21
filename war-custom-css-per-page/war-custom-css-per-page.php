<?php
/**
 * Plugin Name: Custom CSS per Pagina
 * Description: Aggiunge un'area di testo per CSS personalizzato valido solo per la pagina o il post corrente.
 * Version: 1.2
 * Author: Web Agency a Roma
 * Author URI: https://webagencyaroma.it/
 * Plugin URI: https://webagencyaroma.it/custom-css-per-pagina/
 */

// Impedisce l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

// Il resto del codice del plugin

// Aggiunge il campo meta box nell'editor di post e pagine
function add_custom_css_meta_box() {
    add_meta_box(
        'custom_css_meta_box',
        'CSS Personalizzato',
        'render_custom_css_meta_box',
        ['post', 'page'],
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'add_custom_css_meta_box');

// Contenuto del meta box
function render_custom_css_meta_box($post) {
    // Recupera il valore salvato
    $custom_css = get_post_meta($post->ID, '_custom_css', true);

    // Aggiunge il campo nonce per protezione CSRF
    wp_nonce_field('save_custom_css', 'custom_css_nonce');

    echo '<textarea style="width:100%;height:200px;" name="custom_css">' . esc_textarea($custom_css) . '</textarea>';
}

// Salva il CSS personalizzato
function save_custom_css_meta_box($post_id) {
    // Verifica il nonce
    if (!isset($_POST['custom_css_nonce']) || !wp_verify_nonce($_POST['custom_css_nonce'], 'save_custom_css')) {
        return;
    }

    // Verifica permessi dell'utente
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Verifica se il campo è stato inviato
    if (isset($_POST['custom_css'])) {
        // Pulisce il CSS
        $custom_css = wp_strip_all_tags($_POST['custom_css']);
        update_post_meta($post_id, '_custom_css', $custom_css);
    }
}
add_action('save_post', 'save_custom_css_meta_box');

// Aggiunge il CSS personalizzato alla pagina o al post
function add_custom_css_to_page() {
    if (is_singular(['post', 'page'])) {
        global $post;

        // Recupera il CSS personalizzato
        $custom_css = get_post_meta($post->ID, '_custom_css', true);

        // Mostra solo se il CSS non è vuoto
        if (!empty($custom_css)) {
            echo '<style>' . esc_html($custom_css) . '</style>';
        }
    }
}
add_action('wp_head', 'add_custom_css_to_page');
