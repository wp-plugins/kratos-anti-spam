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
function sp_kas_admin_icon()
{
	echo '
		<style> 
      #toplevel_page_sp_kas_settings div.wp-menu-image:before { content: "\f334"; }
		</style>
	';
  //get other icons from http://melchoyce.github.io/dashicons/
}
add_action( 'admin_head', 'sp_kas_admin_icon' );
add_action('admin_menu', 'sp_kas_menus');

function sp_kas_menus() {
    add_menu_page('Kratos Anti Spam', 'Kratos Anti Spam', 'administrator', 'sp_kas_settings', 'sp_kas_settings',"","26.1345619");
    add_submenu_page('sp_kas_settings', 'Settings', 'Settings', 'administrator', 'sp_kas_settings', 'sp_kas_settings');
    add_submenu_page('sp_kas_settings', 'About', 'About', 'administrator', 'sp_kas_about', 'sp_kas_about');
    
    add_action( 'admin_init', 'sp_kas_register_settings' );
}
function sp_kas_check_string_length($string,$length=255)
{
  $string=(string)$string;
  if($string!="")
  {
    $length=intval($length);
    if($length>0)
    {
      if(strlen($string)>$length)
        $string=substr($string,0,255);
      return $string;
    }
  }
  return $string;
}
function sp_kas_register_settings() {
	register_setting( 'sp_kas_settings_group', 'sp_kas_protect' );
  register_setting( 'sp_kas_settings_group', 'sp_kas_ajax_key' );
  register_setting( 'sp_kas_settings_group', 'sp_kas_send_log' );
  register_setting( 'sp_kas_settings_group', 'sp_kas_send_log_to', 'sp_kas_validate_email' );
  register_setting( 'sp_kas_settings_group', 'sp_kas_send_log_at' );
  register_setting( 'sp_kas_settings_group', 'sp_kas_log_post' );
  register_setting( 'sp_kas_settings_group', 'sp_kas_error_url', 'sp_kas_validate_url' );
  register_setting( 'sp_kas_settings_group', 'sp_kas_ajax_head', 'sp_kas_validate_ajax_head' );
  register_setting( 'sp_kas_settings_group', 'sp_kas_exclude', 'sp_kas_validate_exclude');
  register_setting( 'sp_kas_settings_group', 'sp_kas_log_sent' );
}

function sp_kas_validate_exclude($input="")
{
  if($input!="")
  {
    $output = trim(sanitize_text_field($input));
    $output=str_replace(" ","",$output);
    if($output!=$input)
    {
      $type = 'error';
      $message = __( 'Please note that Exclude protection ('.$input.') was stripped of newlines and spaces' );
      add_settings_error(
          'sp_kas_exclude',
          esc_attr( 'settings_updated' ),
          $message,
          $type
      );
    }
    return $output;
  }
  return "";
}
function sp_kas_validate_ajax_head($input="")
{
  if($input!="")
  {
    $tarr=array();
    $tarr=explode(";",$input);
    $validated_arr=array();
    if(count($tarr)>0)
    {
      foreach($tarr as $t)
      {
        $validated="";
        $tarr1=array();
        $tarr1=explode(":",$t);
        if(count($tarr1)==2)
        {
          $validated=trim(sanitize_text_field($tarr1[0])).":".trim(sanitize_text_field($tarr1[1]));
          $validated=str_replace(" ","",$validated);
          $validated_arr[]=$validated;
        }
      }
      $validated_str=implode(";",$validated_arr);
      if($validated_str!=str_replace(" ","",$input))
      {
        $type = 'error';
        $message = __( 'Request Headers exclusion are not valid ('.$input.')' );
        add_settings_error(
            'sp_kas_ajax_head',
            esc_attr( 'settings_updated' ),
            $message,
            $type
        );
      }
      return $validated_str;
    }
  }
  return "";
}
function sp_kas_validate_url($input="")
{
  $validated = esc_url( $input );
  if ($validated !== $input) {
      $type = 'error';
      $message = __( 'Invalid error URL, homepage will be used' );
      add_settings_error(
          'sp_kas_error_url',
          esc_attr( 'settings_updated' ),
          $message,
          $type
      );
  }
  return $validated;
}
function sp_kas_validate_email($input="")
{
  $validated = sanitize_email( $input );
  if ($validated !== $input) {
      $type = 'error';
      $message = __( 'Send log email is invalid, log email will not be sent' );
      add_settings_error(
          'sp_kas_send_log_to',
          esc_attr( 'settings_updated' ),
          $message,
          $type
      );
  }
  return $validated;
}
function sp_kas_admin_notice() {
  global $pagenow;
  if ($pagenow == 'admin.php' && $_GET['page'] == 'sp_kas_settings') {
    $errors = get_settings_errors();
    if(isset($errors[0]['message']))
    {
      ?>
      <div class="<?php echo $errors[0]['type'];?>">
          <p><?php echo $errors[0]['message'];?></p>
      </div>
      <?php
    }
  }
}
add_action( 'admin_notices', 'sp_kas_admin_notice' );
function sp_kas_settings() {
    $sp_kas_protect = (get_option('sp_kas_protect') != '') ? get_option('sp_kas_protect') : '-1';
    $sp_kas_ajax_key = (get_option('sp_kas_ajax_key') != '') ? get_option('sp_kas_ajax_key') : '-1';
    $sp_kas_send_log = (get_option('sp_kas_send_log') != '') ? get_option('sp_kas_send_log') : '-1';
    $sp_kas_send_log_to = (get_option('sp_kas_send_log_to') != '') ? get_option('sp_kas_send_log_to') : '';
    $sp_kas_send_log_at = (get_option('sp_kas_send_log_at') != '') ? get_option('sp_kas_send_log_at') : '10';
    $sp_kas_log_post = (get_option('sp_kas_log_post') != '') ? get_option('sp_kas_log_post') : '-1';
    $sp_kas_error_url = (get_option('sp_kas_error_url') != '') ? get_option('sp_kas_error_url') : '-1';
    $sp_kas_ajax_head = (get_option('sp_kas_ajax_head') != '') ? get_option('sp_kas_ajax_head') : '';
    $sp_kas_exclude = (get_option('sp_kas_exclude') != '') ? get_option('sp_kas_exclude') : '';
    $sp_kas_log_sent = (get_option('sp_kas_log_sent') != '') ? get_option('sp_kas_log_sent') : '0';
    
    $admin_email = get_option( 'admin_email' );
    if($sp_kas_protect=="-1")//default administrator email
    {
      $sp_kas_send_log_to=$admin_email;
    }
    if(trim($sp_kas_send_log_to)=="")
    {
      $sp_kas_send_log=0;
    }
    if($sp_kas_error_url=='-1')
    {
      $sp_kas_error_url=get_site_url()."/error";
    }
    ?>
    <form method="post" action="options.php" id="kasform" name="kasform">
    <input type="hidden" name="sp_kas_log_sent" value="<?php echo $sp_kas_log_sent;?>" />
    <?php settings_fields( 'sp_kas_settings_group' ); ?>
    <?php do_settings_sections( 'sp_kas_settings_group' ); ?>
    
    <h2>Kratos Anti Spam Configuration</h2>
    <p>Stop SPAM! Stop HAKING! No annoying CAPTCHA for your users! As simple as that!</p>
    <?php
    if($sp_kas_send_log==0)
    {
    ?>
    <strong>Please note that log email won't be send</strong>
    <?php
    }
    ?>
    <?php submit_button(); ?>
    <table class="form-table">
      <tr>
        <th scope="row"><label for="sp_kas_protect">Protect if logged?</label></th>
        <td>
          <select id="sp_kas_protect" name="sp_kas_protect">
            <option value="0" <?php echo (($sp_kas_protect==0)?'selected="selected"':"")?>>No</option>
            <option value="1" <?php echo (($sp_kas_protect==1)?'selected="selected"':"")?>>Yes</option>
          </select>
        </td>
        <td>
          <span onClick="javascript:jQuery('#sp_kas_desc_protect').toggle();" title="Toggle more" style="cursor:pointer;">
          Activate Kratos Anti Spam when users are logged in?</span>
        </td>
      </tr>
      <tr id="sp_kas_desc_protect" style="display:none">
        <td colspan="3">
        Set KAS to protect the website when the users are logged in or not
        </td>
      </tr>
      
      <tr>
        <th scope="row"><label for="sp_kas_ajax_key">Ajax Key Request?</label></th>
        <td>
          <select id="sp_kas_ajax_key" name="sp_kas_ajax_key">
            <option value="1" <?php echo (($sp_kas_ajax_key==1)?'selected="selected"':"")?>>Yes</option>
            <option value="0" <?php echo (($sp_kas_ajax_key==0)?'selected="selected"':"")?>>No</option>
          </select>
        </td>
        <td>
          <span onClick="javascript:jQuery('#sp_kas_desc_ajax_key').toggle();" title="Toggle more" style="cursor:pointer;">
          Choose if to use Ajax or not for requesting security check key (Ajax recommended)</span>
        </td>
      </tr>
      <tr id="sp_kas_desc_ajax_key" style="display:none">
        <td colspan="3">
        KAS enforces each POST form on your website with keys that are checked when the form is submitted, these keys can be generated from an AJAX request, or inline
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="sp_kas_send_log">Send log email?</label></th>
        <td>
          <select id="sp_kas_send_log" name="sp_kas_send_log">
            <option value="1" <?php echo (($sp_kas_send_log==1)?'selected="selected"':"")?>>Yes</option>
            <option value="0" <?php echo (($sp_kas_send_log==0)?'selected="selected"':"")?>>No</option>
          </select>
        </td>
        <td>
          <span onClick="javascript:jQuery('#sp_kas_desc_send_log').toggle();" title="Toggle more" style="cursor:pointer;">
          Send the logs email or not</span>
        </td>
      </tr>
      <tr id="sp_kas_desc_send_log" style="display:none">
        <td colspan="3">
        KAS logs a report of all blocked attacks if this setting is set to "Yes", this log gets sent each day, by the configuration set below
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="sp_kas_send_log_to">Send log email to:</label></th>
        <td>
          <input type="text" name="sp_kas_send_log_to" id="sp_kas_send_log_to" value="<?php echo $sp_kas_send_log_to;?>" />
        </td>
        <td>
          <span onClick="javascript:jQuery('#sp_kas_desc_send_log_to').toggle();" title="Toggle more" style="cursor:pointer;">
          Send the logs email to this email address</span>
        </td>
      </tr>
      <tr id="sp_kas_desc_send_log_to" style="display:none">
        <td colspan="3">
        Input an e-mail address, or multiple separated by (,) comma, these emails will get the KAS log
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="sp_kas_send_log_at">Send log email at:</label></th>
        <td>
          <select id="sp_kas_send_log_at" name="sp_kas_send_log_at">
            <option value="00" <?php echo (($sp_kas_send_log_at=='00')?'selected="selected"':"")?>>00</option>
      	    <option value="01" <?php echo (($sp_kas_send_log_at=='01')?'selected="selected"':"")?>>01</option>
      	    <option value="02" <?php echo (($sp_kas_send_log_at=='02')?'selected="selected"':"")?>>02</option>
      	    <option value="03" <?php echo (($sp_kas_send_log_at=='03')?'selected="selected"':"")?>>03</option>
      	    <option value="04" <?php echo (($sp_kas_send_log_at=='04')?'selected="selected"':"")?>>04</option>
      	    <option value="05" <?php echo (($sp_kas_send_log_at=='05')?'selected="selected"':"")?>>05</option>
      	    <option value="06" <?php echo (($sp_kas_send_log_at=='06')?'selected="selected"':"")?>>06</option>
      	    <option value="07" <?php echo (($sp_kas_send_log_at=='07')?'selected="selected"':"")?>>07</option>
      	    <option value="08" <?php echo (($sp_kas_send_log_at=='08')?'selected="selected"':"")?>>08</option>
      	    <option value="09" <?php echo (($sp_kas_send_log_at=='09')?'selected="selected"':"")?>>09</option>
      	    <option value="10" <?php echo (($sp_kas_send_log_at=='10')?'selected="selected"':"")?>>10</option>
      	    <option value="11" <?php echo (($sp_kas_send_log_at=='11')?'selected="selected"':"")?>>11</option>
      	    <option value="12" <?php echo (($sp_kas_send_log_at=='12')?'selected="selected"':"")?>>12</option>
      	    <option value="13" <?php echo (($sp_kas_send_log_at=='13')?'selected="selected"':"")?>>13</option>
      	    <option value="14" <?php echo (($sp_kas_send_log_at=='14')?'selected="selected"':"")?>>14</option>
      	    <option value="15" <?php echo (($sp_kas_send_log_at=='15')?'selected="selected"':"")?>>15</option>
      	    <option value="16" <?php echo (($sp_kas_send_log_at=='16')?'selected="selected"':"")?>>16</option>
      	    <option value="17" <?php echo (($sp_kas_send_log_at=='17')?'selected="selected"':"")?>>17</option>
      	    <option value="18" <?php echo (($sp_kas_send_log_at=='18')?'selected="selected"':"")?>>18</option>
      	    <option value="19" <?php echo (($sp_kas_send_log_at=='19')?'selected="selected"':"")?>>19</option>
      	    <option value="20" <?php echo (($sp_kas_send_log_at=='20')?'selected="selected"':"")?>>20</option>
      	    <option value="21" <?php echo (($sp_kas_send_log_at=='21')?'selected="selected"':"")?>>21</option>
      	    <option value="22" <?php echo (($sp_kas_send_log_at=='22')?'selected="selected"':"")?>>22</option>
            <option value="23" <?php echo (($sp_kas_send_log_at=='23')?'selected="selected"':"")?>>23</option>
          </select>
        </td>
        <td>
          <span onClick="javascript:jQuery('#sp_kas_desc_send_log_at').toggle();" title="Toggle more" style="cursor:pointer;">
          Send logs email at specific hour</span>
        </td>
      </tr>
      <tr id="sp_kas_desc_send_log_at" style="display:none">
        <td colspan="3">
        Select at what hour the KAS log email is sent
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="sp_kas_log_post">Log the POST request?</label></th>
        <td>
          <select id="sp_kas_log_post" name="sp_kas_log_post">
            <option value="0" <?php echo (($sp_kas_log_post==0)?'selected="selected"':"")?>>No</option>
            <option value="1" <?php echo (($sp_kas_log_post==1)?'selected="selected"':"")?>>Yes</option>
          </select>
        </td>
        <td>
          <span onClick="javascript:jQuery('#sp_kas_desc_log_post').toggle();" title="Toggle more" style="cursor:pointer;">
          Save the POST request in the log?</span>
        </td>
      </tr>
      <tr id="sp_kas_desc_log_post" style="display:none">
        <td colspan="3">
        Log or not the actual $_POST request received that was blocked by KAS
        </td>
      </tr>
      <tr>
        <th scope="row"><label>Send log link:</label></th>
        <td>
          <a href="<?php echo get_site_url();?>/kratos-send-log-email&_=<?php echo time();?>" target="_blank">Send KAS log now</a>
        </td>
        <td>
          <span onClick="javascript:jQuery('#sp_kas_desc_sendlog').toggle();" title="Toggle more" style="cursor:pointer;">
          Link to send the log manually</span>
        </td>
      </tr>
      <tr id="sp_kas_desc_sendlog" style="display:none">
        <td colspan="3">
        Click the link above to manually send the KAS log
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="sp_kas_error_url">Error redirect URL</label></th>
        <td>
          <input type="text" name="sp_kas_error_url" id="sp_kas_error_url" value="<?php echo $sp_kas_error_url;?>" />
        </td>
        <td>
          <span onClick="javascript:jQuery('#sp_kas_desc_error_url').toggle();" title="Toggle more" style="cursor:pointer;">
          Input your custom error redirect URL, leave blank for <?php echo get_site_url()."/error";?></span>
        </td>
      </tr>
      <tr id="sp_kas_desc_error_url" style="display:none">
        <td colspan="3">
        If KAS is misconfigured and there are issues with the website, KAS blocking pages that it shouldn't, this URL helps in debuging the problems. Normally this shouldn't be seen by human users
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="sp_kas_exclude">Exclude protection</label></th>
        <td>
          <textarea name="sp_kas_exclude" id="sp_kas_exclude"><?php echo $sp_kas_exclude;?></textarea>
        </td>
        <td>
          <span onClick="javascript:jQuery('#sp_kas_desc_exclude').toggle();" title="Toggle more" style="cursor:pointer;">
          Input URLs where to disable protection, separated by comma (,), partial urls allowed.</span>
        </td>
      </tr>
      <tr id="sp_kas_desc_exclude" style="display:none">
        <td colspan="3">
        Disable protection on specific urls. This needs to contain absolute urls (recommended) or partial urls that 3rd party servers requests them, in example the IPN url of PayPal or any other payment method
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="sp_kas_ajax_head">Request Headers exclusion</label></th>
        <td>
          <input type="text" name="sp_kas_ajax_head" id="sp_kas_ajax_head" value="<?php echo $sp_kas_ajax_head;?>" />
        </td>
        <td>
          <span onClick="javascript:jQuery('#sp_kas_desc_ajax_head').toggle();" title="Toggle more" style="cursor:pointer;">KAS will exclude protection for <strong>Request Headers</strong> pairs, used in ajax post requests, use pairs as "field:value" separated by (;) if multiple</span>
        </td>
      </tr>
      <tr id="sp_kas_desc_ajax_head" style="display:none">
        <td colspan="3">
        Disable protection by the request header. For KAS to know that an AJAX request was made, it checks the request headers, if you have problems with AJAX posting, this is the place to edit. "HTTP_" prefix is added automatically for the field name.
        </td>
      </tr>
    </table>
   
    <p>
    If you have issues with the plugin, we provide paid support
    </p>
    <a class="button button-primary" href="http://www.softpill.eu/kratos-anti-spam-wp" target="_blank">Buy Support</a>
    <?php
    if(get_option('sp_kas_protect')=="")
    {
      //we need to save the plugin configuration at least once
      ?>
      <script type="text/javascript">
      jQuery('#submit').click();
      </script>
      <?php
    }
    ?>
    </form>
    <p><strong style="font-size:120%;">IMPORTANT!</strong> After the first activation of the plugin use another browser and log in as administrator, if you can't login please disable the plugin and let us know. Kratos uses the "login_footer" action hook, and if you use another way for logging in that doesn't work with this hook you will be blocked from administration section.</p>
    <?php
}
?>