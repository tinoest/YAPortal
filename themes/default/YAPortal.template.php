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

function template_yaportal_index()
{
	global $context, $txt, $scripturl;

	echo '<div class="elk_article_container">';
	
	if(!empty($context['yaportal_topPanel'])) {
		echo '
		<div class="elk_article_topPanel">
			<h3 class="category_header">'.$context['yaportal_topPanel']['title'].'</h3>
			'.$context['yaportal_topPanel']['content'].'
		</div>';
	}

	if(!empty($context['yaportal_rightPanel'])) {
    		echo '
		<div class="elk_article_rightPanel">
			<h3 class="category_header">'.$context['yaportal_rightPanel']['title'].'</h3>
			'.$context['yaportal_rightPanel']['content'].'
		</div>';
	}

	if(empty($context['yaportal_rightPanel']) && empty($context['yaportal_leftPanel'])) {	
		$style = 'style="grid-column: span 3"';
	}
	else if(empty($context['yaportal_rightPanel']) || empty($context['yaportal_leftPanel'])) {	
		$style = 'style="grid-column: span 2"';
	}
	else {
		$style = 'style="grid-column: span 1"';
	}

	echo'
	<div class="elk_article_centerPanel" '.$style.'>';

	foreach($context['articles'] as $article) {
		echo '<h3 class="category_header"><a href="'.$scripturl.'?article/'.$article['id'].'/">'.$article['title'].'</a></h3>';
		echo sprintf(
			'<span class="views_text"> Views: %d%s</span>', $article['views'], 
			( $context['comments-enabled'] == 1 ) ? ' | '.$txt['yaportal-comments'] . $article['comments'] : ''
		);
		echo sprintf('<span class="views_text"> | Written By: %s in %s | %s </span>', $article['member'], $article['category'], htmlTime($article['dt_published']));
		echo '<section><article class="post_wrapper forumposts"><div style="margin : 0.5em">'.$article['body'].'</div></article></section>';

		
	}
	
	if (!empty($context['page_index'])) {
		template_pagesection();
	}

	echo '</div>';

	if(!empty($context['yaportal_leftPanel'])) {
    		echo '
		<div class="elk_article_leftPanel">
			<h3 class="category_header">'.$context['yaportal_leftPanel']['title'].'</h3>
			'.$context['yaportal_leftPanel']['content'].'
		</div>';
	}

	if(!empty($context['yaportal_bottomPanel'])) {
    		echo '
		<div class="elk_article_bottomPanel">
			<h3 class="category_header">'.$context['yaportal_bottomPanel']['title'].'</h3>
			'.$context['yaportal_bottomPanel']['content'].'
		</div>';
	}


	echo '</div>';
}

function template_yaportal()
{
	global $context, $txt;

	if(array_key_exists('article_error', $context) && !empty($context['article_error'])) {
		echo '
		<div id="eb_view_articles">
			<div class="ea_article">
				<h3 class="category_header">'.$context['article_error'].'</h3>
			</div>
		</div>';
	}
	else {
		$article = $context['article'];
		
		echo '
		<div id="eb_view_articles">
			<div class="ea_article">
				<h3 class="category_header">'.$article['title'].'</h3>';
				echo sprintf(
					'<span class="views_text"> Views: %d%s</span>', $article['views'], 
					( $context['comments-enabled'] == 1 ) ? ' | '.$txt['yaportal-comments'] . $article['comments'] : ''
				);
				echo sprintf('<span class="views_text"> | Written By: %s in %s | %s </span>', $article['member'], $article['category'], htmlTime($article['dt_published']));
				echo '<section><article class="post_wrapper forumposts"><div style="margin : 0.5em">'.$article['body'].'</div></article></section>
			</div>
		</div>';
	}
}