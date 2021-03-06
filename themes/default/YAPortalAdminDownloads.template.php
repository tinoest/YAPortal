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

function template_yaportal_edit()
{
	global $settings, $context, $scripturl, $txt;

	echo '<link rel="stylesheet" type="text/css" href="'.$settings['theme_url'].'/css/pell.css">
		<h2 class="category_header">Post Image</h2>
		<div class="forumposts">
			<form id="download_form_edit" action="'.$scripturl.'?action=admin;area=yaportaldownload;sa=editdownload;" value="Submit" method="post" accept-charset="UTF-8" enctype="multipart/form-data">';

			if(isset($context['download_id'])) {
				echo '<input type="hidden" name="id" value="'.$context['download_id'].'" />';
			}

			echo '<dl id="post_header">
				<dt class="clear"><label for="post_subject" id="caption_subject">Subject:</label></dt>';

				if(!empty($context['download_subject'])) {
					echo '<dd><input type="text" name="download_subject" value="'.$context['download_subject'].'" tabindex="1" size="80" maxlength="80" class="input_text" placeholder="Subject" required="required" /><br /></dd>';
				}
				else {
					echo '<dd><input type="text" name="download_subject" value="" tabindex="1" size="80" maxlength="80" class="input_text" placeholder="Subject" required="required" /></dd>';
				}
				echo '<dt class="clear"><label for="download_category">Downloads Category:</label></dt>';

				echo '<dd><select name="download_category">';
				if(!empty($context['download_categories']) && is_array($context['download_categories'])) {
					foreach($context['download_categories'] as $k => $v) {
						if($k == $context['download_category']) {
							echo '<option value="'.$k.'" selected>'.$v.'</option>';
						}
						else {
							echo '<option value="'.$k.'">'.$v.'</option>';
						}
					}
				}
				echo '</select></dd>
				<dt class="clear"><label for="download_status">Status:</label></dt>
				<dd><select name="download_status">';
				foreach( array( 0 => $txt['yaportal-disabled'] , 1 => $txt['yaportal-enabled'], 2 => $txt['yaportal-approval'] ) as $k => $v) {
					if($k == $context['download_status']) {
						echo '<option value="'.$k.'" selected>'.$v.'</option>';
					}
					else {
						echo '<option value="'.$k.'">'.$v.'</option>';
					}
				}
				echo '</select></dd>
				</dl>
				<input type="hidden" id="download_body" name="download_body" />
				<div id="editor_toolbar_container">
					<div id="eb_editor" class="eb_editor"></div>
				</div>
				<div id="post_confirm_buttons" class="submitbutton">
                    <div style="float: left;">
                        <input type="file" id="download_link" name="download_link" />
                    </div>
                    <div style="float: right;">
					    <input type="submit" value="Submit">
                    </div>
				</div>
            <input type="hidden" name="'.$context['session_var'].'" value="'.$context['session_id'].'" />
			</form>
		</div>
		<script src="'.$settings['theme_url'].'/scripts/pell.js"></script>
		<script>
		var editor = window.pell.init({
			element: document.getElementById(\'eb_editor\'),
			defaultParagraphSeparator: \'p\',
			styleWithCSS: false,
			onChange: function (html) {
				document.getElementById(\'download_body\').value = html
			}
		})
		';
		if(!empty($context['download_body'])) {
			echo 'editor.content.innerHTML = '.JavaScriptEscape($context['download_body']);
		}
		echo '</script>';

        if(!empty($context['download_link_src'])) {
            echo '<a href="'. $context['download_link_src'] .'" download>'.$txt['yaportal-download'].'</a>';
        }
}

function template_yaportal_list()
{
	global $context;

	template_show_list('download_list');

}

function template_elkcategory_list()
{
	global $context;

	template_show_list('category_list');

}

function template_elkcategory_add()
{
	global $context, $scripturl, $txt;

	echo '
	<h2 class="category_header">Add Category</h2>
	<div class="forumposts">
		<form id="download_form_edit" action="'.$scripturl.'?action=admin;area=yaportaldownload;sa=addcategory;" value="Submit" method="post" accept-charset="UTF-8">
			<dl id="post_header">
				<dt class="clear"><label for="category_name">'.$txt['yaportal-category-name'].'</label></dt>
			<input type="text" name="category_name" value=""> </input>
			</dl>
			<dl id="post_header">
				<dt class="clear"><label for="category_desc">'.$txt['yaportal-category-desc'].'</label></dt>
				<input type="text" name="category_desc" value=""> </input>
			</dl>
			<dl id="post_header">
				<dt class="clear"><label for="category_desc">'.$txt['yaportal-category-status'].'</label></dt>
				<input type="checkbox" name="category_enabled" '.(!empty($context['category_enabled']) ? 'checked' : '').'> </input>
			</dl>
			<input type="hidden" name="'.$context['session_var'].'" value="'.$context['session_id'].'" />
			<div id="post_confirm_buttons" class="submitbutton">
					<input type="submit" value="Submit">
			</div>
		</form>
	</div>';
}

function template_elkcategory_edit()
{
	global $context, $scripturl, $txt;

	echo '
	<h2 class="category_header">Add Category</h2>
	<div class="forumposts">
		<form id="download_form_edit" action="'.$scripturl.'?action=admin;area=yaportaldownload;sa=editcategory;" value="Submit" method="post" accept-charset="UTF-8">
			<input type="hidden" name="category_id" value="'.$context['category_id'].'"> </input>
			<dl id="post_header">
				<dt class="clear"><label for="category_name">'.$txt['yaportal-category-name'].'</label></dt>
			<input type="text" name="category_name" value="'.$context['category_name'].'"> </input>
			</dl>
			<dl id="post_header">
				<dt class="clear"><label for="category_desc">'.$txt['yaportal-category-desc'].'</label></dt>
				<input type="text" name="category_desc" value="'.$context['category_desc'].'"> </input>
			</dl>
			<dl id="post_header">
				<dt class="clear"><label for="category_desc">'.$txt['yaportal-category-status'].'</label></dt>
				<input type="checkbox" name="category_enabled" '.(!empty($context['category_enabled']) ? 'checked' : '').'> </input>
			</dl>
			<div id="post_confirm_buttons" class="submitbutton">
					<input type="submit" value="Submit">
			</div>
			<input type="hidden" name="'.$context['session_var'].'" value="'.$context['session_id'].'" />
		</form>
	</div>';
}
