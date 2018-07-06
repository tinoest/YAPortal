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

class YAPortalAdmin_Controller extends Action_Controller
{
	public function action_index()
	{
		require_once(SUBSDIR . '/Action.class.php');
		// Where do you want to go today?
		$subActions = array(
			'index' 		=> array($this, 'action_default'),
			'listarticle' 		=> array($this, 'action_list_article'),
			'editarticle' 		=> array($this, 'action_edit_article'),
			'deletearticle'		=> array($this, 'action_delete_article'),
			'listcategory' 		=> array($this, 'action_list_category'),
			'addcategory' 		=> array($this, 'action_add_category'),
			'editcategory' 		=> array($this, 'action_edit_category'),
			'deletecategory' 	=> array($this, 'action_delete_category'),
			'listblock' 		=> array($this, 'action_list_block'),
			'addblock' 		=> array($this, 'action_add_block'),
			'editblock' 		=> array($this, 'action_edit_block'),
			'deleteblock' 		=> array($this, 'action_delete_block'),
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

	public function action_admin_menu()
	{
		global $context, $txt;

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['yaportal-title'],
			'help' => '',
			'description' => $txt['yaportal-desc'],
			'tabs' => array(
				'listarticle' 	=> array(),
				'listcategory' 	=> array(),
				'listblock' 	=> array(),
				'listsettings' 	=> array(),
			),
		);
	}	

	public function action_list_article()
	{
		global $context, $scripturl, $txt;

		$this->action_admin_menu();

		$list = array (
			'id' => 'article_list',
			'title' => $txt['yaportal-articles'],
			'items_per_page' => 25,
			'no_items_label' => $txt['yaportal-notfound'],
			'base_href' => $scripturl . '?action=admin;area=yaportalconfig;sa=listarticle;',
			'default_sort_col' => 'title',
			'get_items' => array(
				'function' => array($this, 'list_articles'),
			),
			'get_count' => array(
				'function' => array($this, 'list_total_articles'),
			),
			'columns' => array(
				'title' => array(
					'header' => array(
						'value' => 'Title',
					),
					'data' => array(
						'db' => 'title',
					),
					'sort' => array(
						'default' => 'title ASC',
						'reverse' => 'title DESC',
					),
				),
				
				'category' => array(
					'header' => array(
						'value' => 'Category',
					),
					'data' => array(
						'db' => 'category',
					),
					'sort' => array(
						'default' => 'category_id ASC',
						'reverse' => 'category_id DESC',
					),
				),
				'author' => array(
					'header' => array(
						'value' => 'Author',
					),
					'data' => array(
						'db' => 'member',
					),
					'sort' => array(
						'default' => 'member_id ASC',
						'reverse' => 'member_id DESC',
					),
				),
				'date' => array(
					'header' => array(
						'value' => 'Date Published',
					),
					'data' => array(
						'db' => 'dt_published',
					),
					'sort' => array(
						'default' => 'dt_published ASC',
						'reverse' => 'dt_published DESC',
					),
				),
				'status' => array(
					'header' => array(
						'value' => 'Status',
						'class' => 'centertext',
					),
					'data' => array(
						'db' => 'status',
						'class' => 'centertext',
					),
					'sort' => array(
						'default' => 'status',
						'reverse' => 'status DESC',
					),
				),
				'action' => array(
					'header' => array(
						'value' => 'Actions',
						'class' => 'centertext',
					),
					'data' => array(
						'sprintf' => array (
							'format' => '
								<a href="?action=admin;area=yaportalconfig;sa=editarticle;article_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" accesskey="p">Modify</a>&nbsp;
								<a href="?action=admin;area=yaportalconfig;sa=deletearticle;article_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(' . JavaScriptEscape('Are you sure you want to delete?') . ') && submitThisOnce(this);" accesskey="d">Delete</a>',
							'params' => array(
								'id' => true,
							),
						),
						'class' => 'centertext nowrap',
					),
				),
			),
			'form' => array(
				'href' => $scripturl . '?action=admin;area=yaportalconfig;sa=editarticle',
				'include_sort' => true,
				'include_start' => true,
				'hidden_fields' => array(
					$context['session_var'] => $context['session_id'],
				),
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => '<input type="submit" name="action_edit" value="' . $txt['yaportal-addarticle'] . '" class="right_submit" />',
				),
			),
		);
	
		$context['page_title']		= 'Article List';
		$context['sub_template'] 	= 'yaportal_list';	
		$context['default_list'] 	= 'article_list';
		// Create the list.
		require_once(SUBSDIR . '/GenericList.class.php');
		createList($list);
		loadTemplate('YAPortalAdmin');
	}

	public function action_edit_article() 
	{
		global $context, $user_info;

		require_once(SUBSDIR . '/YAPortal.subs.php');
		require_once(SUBSDIR . '/YAPortalAdmin.subs.php');


		// Set the defaults
		$context['article_category']	= 1;
		$context['article_subject'] 	= '';
		$context['article_body'] 	= '';

		$status				= 1;
		if(array_key_exists('article_status', $_POST)) {
			$status			= $_POST['article_status'];
		}
		$context['article_status']	= $status;

		if (!empty($_POST['article_subject']) && !empty($_POST['article_body']) && empty($_POST['article_id'])) {
			if (checkSession('post', '', false) !== '') {
				return;
			}

			$subject			= $_POST['article_subject'];
			$body				= $_POST['article_body'];
			$category_id			= $_POST['article_category'];


			$context['article_id']		= insert_article($subject, $body, $category_id, $user_info['id'], $status);
			$context['article_subject'] 	= $subject;
			$context['article_body'] 	= $body;
			$context['article_category']	= $category_id;
		}
		else if ( (!empty($_POST['article_subject']) || !empty($_POST['article_body'])) && !empty($_POST['article_category']) && !empty($_POST['article_id'])) {
			if (checkSession('post', '', false) !== '') {
				return;
			}
	
			$subject			= $_POST['article_subject'];
			if(array_key_exists('article_body', $_POST) && !empty($_POST['article_body'])) {
				$body			= $_POST['article_body'];
			}
			else {
				$body			= null;
			}
			$category_id			= $_POST['article_category'];
			$article_id	 		= $_POST['article_id'];

			update_article($subject, $body, $category_id, $article_id, $status);

			$article_data			= get_article($article_id);
			$context['article_id'] 		= $article_data['id'];
			$context['article_subject'] 	= $article_data['title'];
			$context['article_body'] 	= $article_data['body'];
			$context['article_category']	= $article_data['category_id'];
			$context['article_status']	= $article_data['status'];
		}
		else if (!empty($_GET['article_id'])) {
			if (checkSession('get', '', false) !== '') {
				return;
			}
			
			$article_id	 		= $_GET['article_id'];
			$article_data			= get_article($article_id);
			$context['article_id'] 		= $article_data['id'];
			$context['article_subject'] 	= $article_data['title'];
			$context['article_body'] 	= $article_data['body'];
			$context['article_category']	= $article_data['category_id'];
			$context['article_status']	= $article_data['status'];
		}

		$context['article_categories']	= get_article_categories();
	
		$context['sub_template'] 	= 'yaportal_edit';

		loadTemplate('YAPortalAdmin');
	}

	public function action_delete_article()
	{
		require_once(SUBSDIR . '/YAPortalAdmin.subs.php');
		if (!empty($_GET['article_id'])) {
			if (checkSession('get', '', false) !== '') {
				return;
			}
			
			$id	=  $_GET['article_id'];
			delete_article($id);
		}

		// Just Load the list again
		$this->action_list();
	}

	public function action_list_category()
	{
		global $context, $scripturl, $txt;

		$this->action_admin_menu();

		$list = array (
			'id' => 'category_list',
			'title' => $txt['yaportal-categories'],
			'items_per_page' => 25,
			'no_items_label' => $txt['yaportal-notfound'],
			'base_href' => $scripturl . '?action=admin;area=yaportalconfig;sa=listcategory;',
			'default_sort_col' => 'name',
			'get_items' => array (
				'function' => array($this, 'list_categories'),
			),
			'get_count' => array (
				'function' => array($this, 'list_total_categories'),
			),
			'columns' => array (
				'name' => array (
					'header' => array (
						'value' => 'Name',
					),
					'data' => array (
						'db' => 'name',
					),
					'sort' => array (
						'default' => 'name ASC',
						'reverse' => 'name DESC',
					),
				),
				'description' => array(
					'header' => array(
						'value' => 'Description',
					),
					'data' => array(
						'db' => 'description',
					),
					'sort' => array(
						'default' => 'description ASC',
						'reverse' => 'description DESC',
					),
				),
				'ariticles' => array(
					'header' => array(
						'value' => 'Articles',
					),
					'data' => array(
						'db' => 'articles',
					),
					'sort' => array(
						'default' => 'articles ASC',
						'reverse' => 'articles DESC',
					),
				),
				'status' => array(
					'header' => array(
						'value' => 'Status',
						'class' => 'centertext',
					),
					'data' => array(
						'db' => 'status',
						'class' => 'centertext',
					),
					'sort' => array(
						'default' => 'status ASC',
						'reverse' => 'status DESC',
					),
				),
				'action' => array(
					'header' => array(
						'value' => 'Actions',
						'class' => 'centertext',
					),
					'data' => array(
						'sprintf' => array (
							'format' => '
								<a href="?action=admin;area=yaportalconfig;sa=editcategory;category_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" accesskey="p">Modify</a>&nbsp;
								<a href="?action=admin;area=yaportalconfig;sa=deletecategory;category_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(' . JavaScriptEscape('Are you sure you want to delete?') . ') && submitThisOnce(this);" accesskey="d">Delete</a>',
							'params' => array(
								'id' => true,
							),
						),
						'class' => 'centertext nowrap',
					),
				),
			),
			'form' => array(
				'href' => $scripturl . '?action=admin;area=yaportalconfig;sa=addcategory',
				'include_sort' => true,
				'include_start' => true,
				'hidden_fields' => array(
					$context['session_var'] => $context['session_id'],
				),
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => '<input type="submit" name="action_add_category" value="' . $txt['yaportal-addcategory'] . '" class="right_submit" />',
				),
			),
		);
	
		$context['page_title']		= 'Category List';
		$context['sub_template'] 	= 'elkcategory_list';	
		$context['default_list'] 	= 'category_list';
		// Create the list.
		require_once(SUBSDIR . '/GenericList.class.php');
		createList($list);
		loadTemplate('YAPortalAdmin');
	}

	public function action_add_category()
	{
		global $context;

		if(!empty($_POST['category_name'])) {
			if (checkSession('post', '', false) !== '') {
				return;
			}
			require_once(SUBSDIR . '/YAPortalAdmin.subs.php');
			$name 	= $_POST['category_name'];
			$desc 	= $_POST['category_desc'];
			if(!empty($_POST['category_enabled'])) {
				$status = 1;
			}
			else {
				$status	= 0;
			}
			insert_category($name, $desc, $status);	
			$this->action_list_category();
		}
		else {
			$context['page_title']		= 'Add Category';
			$context['sub_template'] 	= 'elkcategory_add';	
			loadTemplate('YAPortalAdmin');
		}
	}

	public function action_edit_category()
	{
		global $context;


		if ( !empty($_POST['category_id']) && !empty($_POST['category_name']) && !empty($_POST['category_desc']) ) {
			if (checkSession('post', '', false) !== '') {
				return;
			}
			require_once(SUBSDIR . '/YAPortalAdmin.subs.php');

			$category_id			= $_POST['category_id'];
			$category_name			= $_POST['category_name'];
			$category_desc			= $_POST['category_desc'];
			if(!empty($_POST['category_enabled'])) {
				$category_enabled 	= 1;
			}
			else {
				$category_enabled	= 0;
			}
			update_category($category_id, $category_name, $category_desc, $category_enabled);

			$context['category_id']		= $category_id;
			$context['category_name']	= $category_name;
			$context['category_desc']	= $category_desc;
			$context['category_enabled']	= $category_enabled;
		}
		else if(!empty($_GET['category_id'])) {
			if (checkSession('get', '', false) !== '') {
				return;
			}			
			require_once(SUBSDIR . '/YAPortal.subs.php');
			$category_id			= $_GET['category_id'];
			$category_details		= get_category($category_id);
			$context['category_id']		= $category_details['id'];
			$context['category_name']	= $category_details['name'];
			$context['category_desc']	= $category_details['description'];
			$context['category_enabled']	= $category_details['enabled'];

			$context['page_title']		= 'Edit Category';
			$context['sub_template'] 	= 'elkcategory_edit';
			loadTemplate('YAPortalAdmin');
			return;
		} 

		$this->action_list_category();
	}

	public function action_delete_category()
	{
		require_once(SUBSDIR . '/YAPortalAdmin.subs.php');
		if (!empty($_GET['category_id'])) {
			if (checkSession('get', '', false) !== '') {
				return;
			}
			
			$id	=  $_GET['category_id'];
			delete_category($id);
		}

		// Just Load the list again
		$this->action_list_category();
	}

	public function action_list_block()
	{
		global $context, $scripturl, $txt;

		$this->action_admin_menu();

		$list = array (
			'id' => 'block_list',
			'title' => $txt['yaportal-blocks'],
			'items_per_page' => 25,
			'no_items_label' => $txt['yaportal-notfound'],
			'base_href' => $scripturl . '?action=admin;area=yaportalconfig;sa=listblock;',
			'default_sort_col' => 'name',
			'get_items' => array (
				'function' => array($this, 'list_blocks'),
			),
			'get_count' => array (
				'function' => array($this, 'list_total_blocks'),
			),
			'columns' => array (
				'name' => array (
					'header' => array (
						'value' => 'Name',
					),
					'data' => array (
						'db' => 'name',
					),
					'sort' => array (
						'default' => 'name ASC',
						'reverse' => 'name DESC',
					),
				),
				'description' => array(
					'header' => array(
						'value' => 'Description',
					),
					'data' => array(
						'db' => 'description',
					),
					'sort' => array(
						'default' => 'description ASC',
						'reverse' => 'description DESC',
					),
				),
				'blocks' => array(
					'header' => array(
						'value' => 'Blocks',
					),
					'data' => array(
						'db' => 'blocks',
					),
					'sort' => array(
						'default' => 'blocks ASC',
						'reverse' => 'blocks DESC',
					),
				),
				'status' => array(
					'header' => array(
						'value' => 'Status',
						'class' => 'centertext',
					),
					'data' => array(
						'db' => 'status',
						'class' => 'centertext',
					),
					'sort' => array(
						'default' => 'status ASC',
						'reverse' => 'status DESC',
					),
				),
				'action' => array(
					'header' => array(
						'value' => 'Actions',
						'class' => 'centertext',
					),
					'data' => array(
						'sprintf' => array (
							'format' => '
								<a href="?action=admin;area=yaportalconfig;sa=editblock;block_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" accesskey="p">Modify</a>&nbsp;
								<a href="?action=admin;area=yaportalconfig;sa=deleteblock;block_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(' . JavaScriptEscape('Are you sure you want to delete?') . ') && submitThisOnce(this);" accesskey="d">Delete</a>',
							'params' => array(
								'id' => true,
							),
						),
						'class' => 'centertext nowrap',
					),
				),
			),
			'form' => array(
				'href' => $scripturl . '?action=admin;area=yaportalconfig;sa=addblock',
				'include_sort' => true,
				'include_start' => true,
				'hidden_fields' => array(
					$context['session_var'] => $context['session_id'],
				),
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => '<input type="submit" name="action_add_category" value="' . $txt['yaportal-addblock'] . '" class="right_submit" />',
				),
			),
		);
	
		$context['page_title']		= 'Block List';
		$context['sub_template'] 	= 'elkblock_list';	
		$context['default_list'] 	= 'block_list';
		// Create the list.
		require_once(SUBSDIR . '/GenericList.class.php');
		createList($list);
		loadTemplate('YAPortalAdmin');
	}

	public function action_add_block()
	{
		global $context;

		$this->action_list_block();
	}

	public function action_edit_block()
	{
		global $context;

		$this->action_list_block();
	}

	public function action_delete_block()
	{

		$this->action_list_block();
		return;

		require_once(SUBSDIR . '/YAPortalAdmin.subs.php');
		if (!empty($_GET['block_id'])) {
			if (checkSession('get', '', false) !== '') {
				return;
			}
			
			$id	=  $_GET['block_id'];
			delete_block($id);
		}

		// Just Load the list again
		$this->action_list_block();
	}

	public function action_list_settings()
	{
		global $txt, $context, $scripturl, $modSettings;
		
		$this->action_admin_menu();
		
		loadLanguage('YAPortal');
		// Lets build a settings form
		require_once(SUBSDIR . '/SettingsForm.class.php');
		// Instantiate the form
		$elkArticleSettings = new Settings_Form();
		// All the options, well at least some of them!
		$config_vars = array (
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
			array ('check', 'yaportal-rightPanel'),
			array ('check', 'yaportal-leftPanel'),
			array ('check', 'yaportal-topPanel'),
			array ('check', 'yaportal-bottomPanel'),
			array ('check', 'yaportal-enablecomments'),
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

	public function list_articles($start, $items_per_page, $sort)
	{
		require_once(SUBSDIR . '/YAPortalAdmin.subs.php');
		return get_articles_list($start, $items_per_page, $sort);
	}
 
	public function list_total_articles()
	{
		require_once(SUBSDIR . '/YAPortal.subs.php');
		return get_total_articles();
	}

	public function list_categories($start, $items_per_page, $sort)
	{
		require_once(SUBSDIR . '/YAPortal.subs.php');
		return get_category_list($start, $items_per_page, $sort);
	}
 
	public function list_total_categories()
	{
		require_once(SUBSDIR . '/YAPortal.subs.php');
		return get_total_categories();
	} 

	public function list_blocks($start, $items_per_page, $sort)
	{
		require_once(SUBSDIR . '/YAPortal.subs.php');
		return get_block_list($start, $items_per_page, $sort);
	}
 
	public function list_total_blocks()
	{
		require_once(SUBSDIR . '/YAPortal.subs.php');
		return get_total_blocks();
	} 
}
