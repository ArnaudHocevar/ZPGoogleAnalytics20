<?php
/**
 * google_analytics -- Places the tracking registation code for google analytics in a photo gallery.
 * This code was modeled after the google_maps plugin by Dustin Brewer (mankind) and Stephen Billard (sbillard)
 *
 * @author Jeff Smith (j916) up to 2.5.0 - http://www.moto-treks.com/zenPhoto/google-analytics-for-zenphoto.html
 * @author Arnaud Hocevar starting 3.0.0
 * @version 4.0
 * @package plugins
 */

$plugin_is_filter = 5 | THEME_PLUGIN;
$plugin_description = gettext("Support for providing Google Analytics tracking to ZenPhoto20");
$plugin_author = 'Arnaud Hocevar';
$plugin_version = '4.0';
$plugin_URL = "http://github.com/ArnaudHocevar/ZPGoogleAnalytics20";
$option_interface = "GoogleAnalytics";

// Include all the dependencies
require_once(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/google_analytics/inc-functions.php');
require_once(SERVERPATH . '/' . USER_PLUGIN_FOLDER . '/google_analytics/inc-config.php');


// Register callbacks
zp_register_filter(getOption(GAConfig::getConfItem('TrackPageViewsPosition','property_name')), 'GoogleAnalytics::GAinitialize',0);
zp_register_filter(getOption(GAConfig::getConfItem('TrackImageViewsPosition','property_name')), 'GoogleAnalytics::GAColorboxHook',0);


class GoogleAnalytics {

	// Constructor: set all the option defaults
	public function __construct() {
		//$conflist = GAConfig::getConf();
		foreach (GAConfig::getConf() as $sub) {
			setOptionDefault($sub['property_name'],$sub['default']);
			}
		}

	// Set up configuration options in administration panel
	public function getOptionsSupported() {
		$t_arr = array();
		foreach (GAConfig::getConf() as $sub) {
			$t_sarr = array(
				'order' => $sub['option_order'],
				'key' => $sub['property_name'],
				'type' => $sub['option_type'],
				'desc' => $sub['option_desc']
			);
			if($sub['option_type'] == OPTION_TYPE_SELECTOR)
				$t_sarr['selections'] = $sub['option_selections'];
			if($sub['option_type'] == OPTION_TYPE_RADIO)
				$t_sarr['buttons'] = $sub['option_button'];
			$t_arr[$sub['option_header']] = $t_sarr;
		}
		return $t_arr;
	}

	function handleOption($option, $currentValue) {}
	
	public static function GAinitialize() {
		// Only enable analytics if a valid UA identifier is available
		if(GAToolbox::validateAnalyticsId(getOption(GAConfig::getConfItem('AnalyticsId','property_name')))
				&& ((zp_loggedin(ADMIN_RIGHTS) && getOption(GAConfig::getConfItem('AdminTrackingEnabled','property_name'))) 
						|| !zp_loggedin(ADMIN_RIGHTS)) ) {
			// Analytics JS header
			echo "<script type=\"text/javascript\">
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
    ga('create', '" . getOption(GAConfig::getConfItem('AnalyticsId','property_name')) . "', 'auto');\n";
			echo GAToolbox::buildAdditionalParams('require');
			echo GAToolbox::buildAdditionalParams('other');
			echo GAToolbox::buildAdditionalParams('set');
			if(getOption(GAConfig::getConfItem('TrackPageViews','property_name')) == 1) {
				echo "    ga('send', 'pageview');\n";
				}
			echo "</script>\n";
		}
	}
	
	public static function GAColorboxHook() {
		// Only enable analytics if a valid UA identifier is available AND we want to track image view
		if(GAToolbox::validateAnalyticsId(getOption(GAConfig::getConfItem('AnalyticsId','property_name')))
				&& ((zp_loggedin(ADMIN_RIGHTS) && getOption(GAConfig::getConfItem('AdminTrackingEnabled','property_name'))) 
						|| !zp_loggedin(ADMIN_RIGHTS))
				&& getOption(GAConfig::getConfItem('TrackImageViews','property_name')) == 1) {
			echo "<script type=\"text/javascript\">
	$(document).ready(function () {
		$(document).bind('cbox_complete', function(){
			var href = this.href;
			if (href) {
				ga('send', 'pageview', href]);
			}
		});
	});
	</script>\n";
		}
	}

}
?>
