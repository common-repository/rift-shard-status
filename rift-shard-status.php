<?php
  /*
   Plugin Name: Rift Shard Status
   Plugin URI: http://www.baraans-corner.de/wordpress-plugins/rift-shard-status/
   Description: Shows the population, queue size and type (PvP, PvE, RP) of a shard (server) from the MMORPG Rift.
   Version: 1.0.2
   Author: Baraan@EU-Immerwacht <baraans@baraans-corner.de>
   Author URI: http://www.baraans-corner.de/
   Text Domain: rift_shard_status
   Domain Path: i18n/

   Copyright 2011-present Baraan@EU-Immerwacht  <baraans@baraans-corner.de>

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

// The widget code
require_once('rift-shard-status-widget.php');

/**
 * Returns html shard status.
 */
function rift_shard_status_html($instance){
	// check the cache dir
	$cache_dir = ABSPATH . "wp-content/plugins/rift-shard-status/cache";
	if(!is_writable($cache_dir)){
		return __("Cache directory not writable. Please make sure wordpress can write the cache directory and the files within.", 'rift_shard_status');
	}

	// some settings
	$shard = $instance['shard'];
	$cache_time = $instance['cache_time'];
	$show_last_update = $instance['show_last_update'];
	$region = strtolower($instance['region']);
	if($region == "us") $region = "na";
	$cache_file = "$cache_dir/rift-shards-$region.xml";
	$url = "http://status.riftgame.com/$region-status.xml";
	$last_cache_update = time()-filemtime($cache_file);

	// load the data, either directly or from cachefrom cache
	if( !(file_exists($cache_file) && $last_cache_update < $cache_time) ){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		$data = curl_exec($curl);
		curl_close($curl);

		if(strlen($data) > 0){
			// save the string to the cache file
			$fh = fopen($cache_file, 'w') or die(__("Cannot open cache file, check the install guide and make sure the cache directory is writable.", 'rift_shard_status'));
			fwrite($fh, $data);
			fclose($fh);
			$last_cache_update = 0;
		}
		else{
			return sprintf(__('Rift status server not reachable. Tried to fetch <a href="%1$s">this Link</a>.', 'rift_shard_status'), $url);
		}
	}
	else{
		// cache hit
		$fh = fopen($cache_file, 'r');
		$data = fread($fh, filesize($cache_file));
		fclose($fh);
	}

	// parse the data
	$xml = new SimpleXMLElement($data);
	$shard_data = $xml->xpath("//shard[@name='$shard']");
	$shard_data = $shard_data[0];
	
	$html .= "<!-- Rift Shard Status <http://www.baraans-corner.de/wordpress-plugins/rift-shard-status/> -->\n<div class='riftss_content'>";
	if($shard_data){
		$online = $shard_data['online']=="True"?true:false;
		$pvep = $shard_data['pvp']=="True"?true:false;
		$rp = $shard_data['rp']=="True"?true:false;
		$queued = $shard_data['queued'];
		
		$name = "<span class='riftss_name riftss_shard_". ($online?__("online", 'rift_shard_status'):__("offline", 'rift_shard_status')) ."'>".$shard_data['name']."</span>";
		$online_text = ($online?"<span class='riftss_online'>". __("online", 'rift_shard_status'):"<span class='riftss_offline'>". __("offline", 'rift_shard_status'))."</span>";
		$pvep_text = ($pvep?"<span class='riftss_pvp'>". __("PvP", 'rift_shard_status'):"<span class='riftss_pve'>PvE")."</span>";
		$rp = $rp?"/<span class='riftss_rp'>RP</span>":"";
		$lang = "<span class='riftss_lang'>".$shard_data['language']."</span>";
		/* translators: population as we get it from trion can be low, medium, high or FULL */
		$population = "<span class='riftss_shard_". $shard_data['population'] ."'>". __(trim($shard_data['population']), 'rift_shard_status')."</span>";

		/* translators: i.e.: Immerwacht (PvE) is online */
		$html .= "<div class='riftss_main'>". sprintf(__('%1$s is %2$s.', 'rift_shard_status'), "$name ($pvep_text$rp_text)", $online_text) ."</div>";
		$span_class = "riftss_shard_low";

		if($queued < 1){
			$html .= sprintf(__('The population is %1$s and there are <span class="riftss_shard_low">no login queues</span>.',
						'rift_shard_status'), $population, $span_class);
		}
		else{
			if($queued < 200) $span_class = "riftss_shard_medium";
			else if($queued < 500) $span_class = "riftss_shard_high";
			else $span_class = "riftss_shard_FULL";

			$html .= sprintf(_n('The population is %1$s and there is <span class="%2$s">one player in the queue</span>.',
					'The population is %1$s and there are <span class="%2$s">%3$s players queued</span> to login.',
					$queued, 'rift_shard_status'), $population, $span_class, $queued);
		}

		if($show_last_update){
			$html .= "<br /><span class='riftss_last_updated'>";
			if($last_cache_update < 60){
				$html .= "(". __('Just updated', 'rift_shard_status') .")";
			}
			else if($last_cache_update < 120){
				$html .= "(". __('Last updated about a minute ago', 'rift_shard_status') .")";
			}
			else{
				//$html .= "(". __('Last updated about ". floor($last_cache_update/60) ." minutes ago)";
				$html .= "(". sprintf(__('Last updated about %1$s minutes ago', 'rift_shard_status'), floor($last_cache_update/60)) .")";
			}
			$html .= "</span>";
		}
	}
	else{
		$html .= sprintf(__('Shard "%1$s" couldn\'t be found or the server wasn\'t reachable.', 'rift_shard_status'), $shard);
	}
		
	$html .= "</div>";
	return $html;
}

/**
 * If the user has the (default) setting of using the Rift Shard Status CSS, load it.
 */
function rift_shard_status_css() {
	if (get_option('rift_shard_status_css') == true) {
		wp_enqueue_style('rift_shard_status_css', WP_CONTENT_URL.'/plugins/rift-shard-status/rift-shard-status.css');
	}
}
add_action('wp_print_styles', 'rift_shard_status_css');

/**
 * Set the default settings on activation on the plugin.
 */
function rift_shard_status_activation_hook() {
	return rift_shard_status_restore_config(false);
}
register_activation_hook(__FILE__, 'rift_shard_status_activation_hook');


/**
 * Add the Rift Shard Status menu to the Settings menu
 */
function rift_shard_status_restore_config($force=false) {
	if($force || (get_option('rift_shard_status_css', "NOTSET") == "NOTSET")){
		update_option('rift_shard_status_css', true);
	}
}

/**
 * Add the Rift Shard Status menu to the Settings menu
 */
function rift_shard_status_admin_menu() {
	add_options_page('Rift Shard Status', 'Rift Shard Status', 8, 'rift_shard_status', 'rift_shard_status_submenu');
}
add_action('admin_menu', 'rift_shard_status_admin_menu');

/**
 * Displays the Rift Shard Status admin menu
 */
function rift_shard_status_submenu() {
	// check if the cache dir is writable and complain if not.
	$cache_dir = ABSPATH . "wp-content/plugins/rift-shard-status/cache";
	if(!is_writable($cache_dir)){
		rift_shard_status_message(sprintf(__('Cache dir (%1$s) not writable. Please make sure wordpress can write into the cache directory for the plugin to work.', 'rift_shard_status'), $cache_dir));
	}

	// restore the default config
	if (isset($_REQUEST['restore']) && $_REQUEST['restore']) {
		check_admin_referer('rift_shard_status_config');
		rift_shard_status_restore_config(true);
		rift_shard_status_message(__("Restored all settings to defaults.", 'rift_shard_status') ."<a href=''>". __("Back", 'rift_shard_status') ."</a>");
	}
	// saves the settings from the page
	else if (isset($_REQUEST['save']) && $_REQUEST['save']) {
		check_admin_referer('rift_shard_status_config');
		$error = "";

		// save the different settings
		// boolean values
		foreach ( array('css') as $val ) {
			if ( isset($_POST[$val]) && $_POST[$val] )
				update_option('rift_shard_status_'.$val,true);
			else
				update_option('rift_shard_status_'.$val,false);
		}

		// done saving
		if($error){
			$error = __("Some settings couldn't be saved. More details in the error message below:", 'rift_shard_status') ."<br />". $error;
			rift_shard_status_message($error);
		}
		else{
			rift_shard_status_message(__("Changes saved.", 'rift_shard_status') ."<a href=''>". __("Back", 'rift_shard_status') ."</a>");
		}
	}
	else {
	/**
	 * Display options.
	 */
	?>
	<form action="<?php echo attribute_escape( $_SERVER['REQUEST_URI'] ); ?>" method="post">
	<?php
		if ( function_exists('wp_nonce_field') )
			 wp_nonce_field('rift_shard_status_config');
	?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e("Rift Shard Status Options", 'rift_shard_status'); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row" valign="top">
						<?php _e("Include Rift Shard Status CSS", "rift_shard_status"); ?>
					</th>
					<td>
						<?php _e("If checked the CSS included with the addon will be used. In case you want to modify the design deactivate this option and copy the contents of rift-shard-status.css into your own stylesheet to prevent them from being overwritten by updates of the plugin.", 'rift_shard_status'); ?><br/>
						<input type="checkbox" name="css" <?php checked( get_option('rift_shard_status_css'), true ) ; ?> />
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>
						<span class="submit"><input name="save" value="<?php _e("Save Changes", 'rift_shard_status'); ?>" type="submit" /></span>
						<span class="submit"><input name="restore" value="<?php _e("Restore Built-in Defaults", 'rift_shard_status'); ?>" type="submit"/></span>
					</td>
				</tr>
			</table>
		</div>
	</form>
<?php
	}
}


/**
 * Add a settings link to the plugins page, so people can go straight from the plugin page to the
 * settings page.
 */
function rift_shard_status_filter_plugin_actions( $links, $file ){
	// Static so we don't call plugin_basename on every plugin row.
	static $this_plugin;
	if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);

	if ( $file == $this_plugin ){
		$settings_link = '<a href="options-general.php?page=rift_shard_status">' . __('Settings') . '</a>';
		array_unshift( $links, $settings_link ); // before other links
	}
	return $links;
}
add_filter( 'plugin_action_links', 'rift_shard_status_filter_plugin_actions', 10, 2 );

/**
 * Update message, used in the admin panel to show messages to users.
 */
function rift_shard_status_message($message) {
	echo "<div id=\"message\" class=\"updated fade\"><p>$message</p></div>\n";
}

function rift_shard_status_init(){
	$i18n_dir = 'rift-shard-status/i18n/';
	load_plugin_textdomain('rift_shard_status', false, $i18n_dir);
}
add_action('init', 'rift_shard_status_init');

?>
