<?php

// adding menu to admin panel
add_action('admin_menu', 'dmnmlk_menu');
function dmnmlk_menu()
{
	// adding plugin to admin menu view	
    add_menu_page(
        'Statystyki i analiza sklepu',
        'dmnmlk Analytics',
        'manage_options',
        'dma_standard',
        '',
        'dashicons-chart-bar',
        4
    );
	// adding 2 submenus to plugin admin menu
    add_submenu_page(
        'dma_standard',
        'Przegląd',
        'Analiza podstawowa',
        'manage_options',
        'dma_standard',
		'dmnmlk_admin_subpage_standard_html'
    );
    add_submenu_page(
        'dma_standard',
        'Przegląd',
        'Analiza rozszerzona',
        'manage_options',
        'dma_extended',
		'dmnmlk_admin_subpage_extended_html'
    );
}

// Admin footer modification
add_filter('admin_footer_text', 'remove_footer_admin');
function remove_footer_admin () 
{
    echo '';
}

// Usunięcie wersji WP z admin menu
add_action( 'admin_menu', 'my_footer_shh' );
function my_footer_shh() {
    remove_filter( 'update_footer', 'core_update_footer' ); 
}


