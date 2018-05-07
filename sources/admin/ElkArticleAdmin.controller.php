<?php

class ElkArticleAdmin_Controller extends Action_Controller
{
	public function action_index()
	{
		require_once(SUBSDIR . '/Action.class.php');
		// Where do you want to go today?
		$subActions = array(
			'index' 		=> array($this, 'action_list'),
			'listarticle' 		=> array($this, 'action_list'),
			'editarticle' 		=> array($this, 'action_edit'),
			'deletearticle'		=> array($this, 'action_delete'),
			'listcategory' 		=> array($this, 'action_list_category'),
			'addcategory' 		=> array($this, 'action_add_category'),
			'editcategory' 		=> array($this, 'action_edit_category'),
			'deletecategory' 	=> array($this, 'action_delete_category'),
		);
		// We like action, so lets get ready for some
		$action = new Action('');
		// Get the subAction, or just go to action_index
		$subAction = $action->initialize($subActions, 'index');
		// Finally go to where we want to go

		$action->dispatch($subAction);
	}

	public function action_list()
	{
		global $context, $scripturl, $txt;

		$list = array (
			'id' => 'article_list',
			'title' => 'Articles',
			'items_per_page' => 25,
			'no_items_label' => 'No Articles Found',
			'base_href' => $scripturl . '?action=admin;area=articleconfig;sa=listarticle;',
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
								<a href="?action=admin;area=articleconfig;sa=editarticle;article_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" accesskey="p">Modify</a>&nbsp;
								<a href="?action=admin;area=articleconfig;sa=deletearticle;article_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(' . JavaScriptEscape('Are you sure you want to delete?') . ') && submitThisOnce(this);" accesskey="d">Delete</a>',
							'params' => array(
								'id' => true,
							),
						),
						'class' => 'centertext nowrap',
					),
				),
			),
			'form' => array(
				'href' => $scripturl . '?action=admin;area=articleconfig;sa=deletearticle',
				'include_sort' => true,
				'include_start' => true,
				'hidden_fields' => array(
					$context['session_var'] => $context['session_id'],
				),
			),
		);
	
		$context['page_title']		= 'Article List';
		$context['sub_template'] 	= 'elkarticle_list';	
		$context['default_list'] 	= 'article_list';
		// Create the list.
		require_once(SUBSDIR . '/GenericList.class.php');
		createList($list);
		loadTemplate('ElkArticleAdmin');
	}

	public function action_edit() 
	{
		global $context, $user_info;

		require_once(SUBSDIR . '/ElkArticle.subs.php');
		require_once(SUBSDIR . '/ElkArticleAdmin.subs.php');

		// Set the defaults
		$context['article_category']	= 1;
		$context['article_subject'] 	= '';
		$context['article_body'] 	= '';

		if (!empty($_POST['article_subject']) && !empty($_POST['article_body']) && empty($_POST['article_id'])) {
			if (checkSession('post', '', false) !== '') {
				return;
			}

			$subject			= $_POST['article_subject'];
			$body				= $_POST['article_body'];
			$category_id			= $_POST['article_category'];

			$context['article_id']		= insert_article($subject, $body, $category_id, $user_info['id']);
			$context['article_subject'] 	= $subject;
			$context['article_body'] 	= $body;
			$context['article_category']	= $category_id;
		}
		else if (!empty($_POST['article_subject']) && !empty($_POST['article_body']) && !empty($_POST['article_id'])) {
			if (checkSession('post', '', false) !== '') {
				return;
			}
	
			$subject			= $_POST['article_subject'];
			$body				= $_POST['article_body'];
			$category_id			= $_POST['article_category'];
			$article_id	 		= $_POST['article_id'];

			update_article($subject, $body, $category_id, $article_id);

			$context['article_id'] 		= $article_id;
			$context['article_subject'] 	= $subject;
			$context['article_body'] 	= $body;
			$context['article_category']	= $category_id;
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
		}

		$context['article_categories']	= get_article_categories();
	
		$context['sub_template'] 	= 'elkarticle_edit';
		loadTemplate('ElkArticleAdmin');
	}

	public function action_delete()
	{
		require_once(SUBSDIR . '/ElkArticleAdmin.subs.php');
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

		$list = array (
			'id' => 'category_list',
			'title' => 'Categories',
			'items_per_page' => 25,
			'no_items_label' => 'No Categories Found',
			'base_href' => $scripturl . '?action=admin;area=articleconfig;sa=listcategories;',
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
								<a href="?action=admin;area=articleconfig;sa=editcategory;category_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" accesskey="p">Modify</a>&nbsp;
								<a href="?action=admin;area=articleconfig;sa=deletecategory;category_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(' . JavaScriptEscape('Are you sure you want to delete?') . ') && submitThisOnce(this);" accesskey="d">Delete</a>',
							'params' => array(
								'id' => true,
							),
						),
						'class' => 'centertext nowrap',
					),
				),
			),
			'form' => array(
				'href' => $scripturl . '?action=admin;area=articleconfig;sa=addcategory',
				'include_sort' => true,
				'include_start' => true,
				'hidden_fields' => array(
					$context['session_var'] => $context['session_id'],
				),
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => '<input type="submit" name="action_add_category" value="' . $txt['elkarticle-addcategory'] . '" class="right_submit" />',
				),
			),
		);
	
		$context['page_title']		= 'Category List';
		$context['sub_template'] 	= 'elkcategory_list';	
		$context['default_list'] 	= 'category_list';
		// Create the list.
		require_once(SUBSDIR . '/GenericList.class.php');
		createList($list);
		loadTemplate('ElkArticleAdmin');
	}

	public function action_add_category()
	{
		global $context;

		if(!empty($_POST['category_name'])) {
			require_once(SUBSDIR . '/ElkArticleAdmin.subs.php');
			$name = $_POST['category_name'];
			insert_category($name);	
			$this->action_list_category();
		}
		else {
			$context['page_title']		= 'Add Category';
			$context['sub_template'] 	= 'elkcategory_add';	
			loadTemplate('ElkArticleAdmin');
		}
	}

	public function list_articles()
	{
		require_once(SUBSDIR . '/ElkArticleAdmin.subs.php');
		return get_articles_list();
	}
 
	public function list_total_articles()
	{
		require_once(SUBSDIR . '/ElkArticle.subs.php');
		return get_total_articles();
	}

	public function list_categories()
	{
		require_once(SUBSDIR . '/ElkArticle.subs.php');
		return get_category_list();
	}
 
	public function list_total_categories()
	{
		require_once(SUBSDIR . '/ElkArticle.subs.php');
		return get_total_categories();
	} 
 
}
