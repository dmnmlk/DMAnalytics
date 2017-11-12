<?php
/*
Plugin Name:  DMNMLK Analytics
Description:  Podstawowy plugin pozwalający na analizę ruchu w sklepie internetowym
Version:      1.0
Author:       Damian Małek
Author URI:   http://www.dmnmlk.pl
*/

// create, insert, delete data in database
include( plugin_dir_path( __FILE__ ) . 'admin/database.php');

// all hooks & additional functions
include( plugin_dir_path( __FILE__ ) . 'functions.php');

// menu generating file
include( plugin_dir_path( __FILE__ ) . 'admin/menu.php');

// standard statistic view
include( plugin_dir_path( __FILE__ ) . 'admin/standard_view.php');

// extended statistic view
include( plugin_dir_path( __FILE__ ) . 'admin/extended_view.php');

add_action( 'init', 'dmnmlk_setup_post_types' );
function dmnmlk_setup_post_types()
{
	// register the "book" custom post type
	register_post_type( 'book', ['public' => 'true'] );
}

// activation of plugin
register_activation_hook( __FILE__, 'dmnmlk_install' );
function dmnmlk_install()
{
	dmnmlk_create_tables();
	dmnmlk_insert_data();
	dmnmlk_setup_post_types();
	flush_rewrite_rules();
}

// deactovation of plugin
register_deactivation_hook( __FILE__, 'dmnmlk_deactivation' );
function dmnmlk_deactivation()
{
	flush_rewrite_rules();
}

// uninstalling a plugin
register_uninstall_hook(__FILE__, 'dmnmlk_uninstall');
function dmnmlk_uninstall()
{
	dmnmlk_drop_tables();
	flush_rewrite_rules();
}


