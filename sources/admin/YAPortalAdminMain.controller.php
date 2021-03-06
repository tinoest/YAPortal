<?php

/**
 * @package "YAPortal" Addon for Elkarte
 * @author tinoest
 * @license BSD http://opensource.org/licenses/BSD-3-Clause
 *
 * @version 1.0.0
 *
 */

if (!defined('ELK'))
{
	die('No access...');
}

class YAPortalAdminMain_Controller extends Action_Controller
{
	public function action_index()
	{

        if (!allowedTo('yaportal_admin')) {
            isAllowedTo('yaportal_manage_settings');
        }

        require_once(SUBSDIR . '/Action.class.php');
		// Where do you want to go today?
		$subActions = array(
			'index' 		    => array($this, 'action_default'),
			'listsettings' 		=> array($this, 'action_list_settings'),
		);
		// We like action, so lets get ready for some
		$action = new Action('');
		// Get the subAction, or just go to action_index
		$subAction = $action->initialize($subActions, 'index');
		// Finally go to where we want to go

		$action->dispatch($subAction);
	}

	public function action_default()
	{
		$this->action_list_settings();
	}

	public function action_list_settings()
	{
		global $txt, $context, $scripturl, $modSettings;

		loadLanguage('YAPortal');
		// Lets build a settings form
		require_once(SUBSDIR . '/SettingsForm.class.php');
		// Instantiate the form
		$elkArticleSettings = new Settings_Form();
		// All the options, well at least some of them!
		$config_vars = array (
            // Front Page options
		    array ('title', 'yaportal-frontpage-options'),
			array ('check', 'yaportal-frontpage'),
			array ('select', 'yaportal-item-limit',
				array (
					$txt['yaportal-limit-10'],
					$txt['yaportal-limit-25'],
					$txt['yaportal-limit-50'],
					$txt['yaportal-limit-75'],
					$txt['yaportal-limit-100'],
				)
			),
            // Block Options
		    /*
            array ('title', 'yaportal-block-options'),
			array ('check', 'yaportal-rightPanel'),
			array ('check', 'yaportal-leftPanel'),
			array ('check', 'yaportal-topPanel'),
			array ('check', 'yaportal-bottomPanel'),
            */
            // Article Options
		    array ('title', 'yaportal-article-options'),
            array ('check', 'yaportal-article-menu-item'),
			array ('check', 'yaportal-enablecomments'),
            // Gallery Options
		    array ('title', 'yaportal-gallery-options'),
            array ('check', 'yaportal-gallery-menu-item'),
			array ('select', 'yaportal-gallery-item-limit',
				array (
					$txt['yaportal-limit-10'],
					$txt['yaportal-limit-25'],
					$txt['yaportal-limit-50'],
					$txt['yaportal-limit-75'],
					$txt['yaportal-limit-100'],
				)
			),
			array ('check', 'yaportal-gallery-enablecomments'),
            // Download options
		    array ('title', 'yaportal-download-options'),
            array ('check', 'yaportal-download-menu-item'),
            // SEOoptions
		    array ('title', 'yaportal-seo-options'),
            array ('check', 'yaportal-seo'),
            array ('check', 'yaportal-seo-strip-index'),
		);
		// Load the settings to the form class
		$elkArticleSettings->settings($config_vars);
		// Saving?
		if (isset($_GET['save'])) {
			if(!empty($_POST['yaportal-frontpage'])) {
				updateSettings(array('front_page' => 'YAPortal_Controller'));
			}
			else {
				removeSettings('front_page');
			}
			checkSession();
			Settings_Form::save_db($config_vars);
			redirectexit('action=admin;area=yaportalconfig;sa=listsettings');
		}

		$context['sub_template']	= 'show_settings';
		// Continue on to the settings template
		$context['settings_title'] 	= $txt['yaportal-options'];
		$context['page_title'] 		= $context['yaportal_settings_title'] = $txt['yaportal-settings'];
		$context['post_url'] 		= $scripturl . '?action=admin;area=yaportalconfig;sa=listsettings;save';
		Settings_Form::prepare_db($config_vars);

	}

}
