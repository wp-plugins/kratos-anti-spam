<?php
/* Plugin Name: Kratos Anti Spam
Plugin URI: http://www.softpill.eu/
Description: Kratos Anti Spam is built to stop bots from sending spam through all website forms. Stop SPAM! Stop HAKING! No annoying CAPTCHA for your users! As simple as that!
Version: 1.0
Author: Softpill.eu
Author URI: http://www.softpill.eu/
License: GPLv2 or later

Copyright 2015  Softpill.eu  (email : mail@softpill.eu)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define("SP_KAS_DIR", WP_PLUGIN_DIR."/".basename( dirname( __FILE__ ) ) );
define("SP_KAS_URL", plugins_url()."/".basename( dirname( __FILE__ ) ) );
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}
if(is_admin()){
function sp_kas_activation() {
  global $wpdb;
  $table_name = $wpdb->prefix . "kratosantispam";
  $sql="CREATE TABLE IF NOT EXISTS `$table_name` (
  `pre_header` varchar(255) DEFAULT NULL,
  `pre_val` varchar(255) DEFAULT NULL,
  `pre_key` varchar(255) DEFAULT NULL,
  `val` varchar(255) DEFAULT NULL,
  `key` varchar(255) DEFAULT NULL,
  `cdate` int(11) DEFAULT '0'
  )";
  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql );
}
register_activation_hook(__FILE__, 'sp_kas_activation');
function sp_kas_deactivation() {
}
register_deactivation_hook(__FILE__, 'sp_kas_deactivation');

require_once(SP_KAS_DIR.DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR."kas_admin.php");
require_once(SP_KAS_DIR.DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR."kas_about.php");
}
else{
//front-end
require_once(SP_KAS_DIR.DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR."kas_frontend.php");
}


?>