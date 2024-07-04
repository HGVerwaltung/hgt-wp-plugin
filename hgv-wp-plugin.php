<?php
/*
Plugin Name: HG Verwaltung
Plugin URI: https://wiki.hgverwaltung.ch
Description: Hilft dir, Inhalte aus der HG Verwaltung einzubinden.
Author: EHV
Version: 1.1
*/


$hgv_pluginName = "HGVerwaltung";
$hgv_optionsCode = "wpv_code";


register_activation_hook(__FILE__, 'hgV_install');
register_deactivation_hook(__FILE__, 'hgv_deactivate');
register_uninstall_hook(__FILE__, 'hgv_uninstall');

add_action('plugin_action_links_' . plugin_basename(__FILE__), array("HGVerwaltungAdmin", 'addAdminLinksToPlugin'));
add_action('admin_menu', array("HGVerwaltungAdmin", 'addAdminPages'));
add_action('admin_post_hgV_wpvCode', array("HGVerwaltungAdmin", 'hgV_wpvCode_response'));
add_action('init', 'register_hgv_pattern_categories');
add_action('init', 'register_hgv_patterns');

add_shortcode("hgv", array("HGVerwaltungFrontend", 'hgv_shortcode'));

require_once(__DIR__ . "/admin/hgVerwaltungAdmin.php");
require_once(__DIR__ . "/frontend/hgVerwaltungFrontend.php");

function register_hgv_patterns()
{
   foreach (HGVerwaltungFrontend::getHelpPatterns() as $key => $pattern) {
      $key = 'hgv/help-patterns-' . $key;
      register_block_pattern(
         $key,
         $pattern
      );
   }
}

function register_hgv_pattern_categories()
{
   register_block_pattern_category(
      'hgverwaltung',
      array('label' => 'HG Verwaltung')
   );
}

function hgv_GetUpdateUrl()
{
   $hgv_inDevMode = get_option("wpv_inDevMode", "false");
   if ($hgv_inDevMode != "true") {
      return "https://git.hgverwaltung.ch/zr/hgv-wp-plugin/raw/branch/master/version.json";
   }
   else
   {
      return "https://git.hgverwaltung.ch/zr/hgv-wp-plugin/raw/branch/development/devVersion.json";
   }
}

function hgV_install()
{
   add_option("wpv_code", 'test');
   add_option("wpv_inDevMode", "false");
   add_option("wpv_date_format", get_option("date_format"));
}


function hgv_uninstall()
{
   delete_option("wpv_code");
   delete_option("wpv_date_format");   
   delete_option("wpv_inDevMode");
}

function hgv_deactivate()
{
   //nothing to do here right now.
}

require 'plugin-update-checker/plugin-update-checker.php';

$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	hgv_GetUpdateUrl(),
	__FILE__, //Full path to the main plugin file or functions.php.
	'hgv-wp-plugin'
);