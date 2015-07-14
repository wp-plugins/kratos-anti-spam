<?php
/*
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
if(defined('WP_UNINSTALL_PLUGIN') ){
  global $wpdb;
  $table_name = $wpdb->prefix . "kratosantispam";
  $sql="DROP TABLE IF EXISTS $table_name";
  $wpdb->query($sql);
  
  delete_option( 'sp_kas_protect' );
  delete_option( 'sp_kas_ajax_key' );
  delete_option( 'sp_kas_send_log' );
  delete_option( 'sp_kas_send_log_to' );
  delete_option( 'sp_kas_send_log_at' );
  delete_option( 'sp_kas_log_post' );
  delete_option( 'sp_kas_error_url' );
  delete_option( 'sp_kas_ajax_head' );
  delete_option( 'sp_kas_exclude' );
  delete_option( 'sp_kas_log_sent' );
}
?>