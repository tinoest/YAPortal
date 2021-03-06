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

function get_galleries_list($start, $items_per_page, $sort)
{

	$db = database();

	require_once( SUBSDIR . '/YAPortalGallery.subs.php');

	$categories 	= get_gallery_categories();
	$request 	    = $db->query('', '
		SELECT id, category_id, member_id, dt_created, dt_published, title,
			CASE WHEN status = 1
				THEN \'Enabled\'
				ELSE \'Disabled\'
			END
			AS status
		FROM {db_prefix}galleries
		ORDER BY '.$sort.'
		LIMIT '.$items_per_page.' OFFSET '.$start
	);

	$galleries 	= array();
	while ($row = $db->fetch_assoc($request)) {
		$member	= $db->query('', '
			SELECT member_name
			FROM {db_prefix}members
			WHERE id_member = {int:member_id}',
			array (
				'member_id' => $row['member_id'],
			)
		);
		$row['member'] 		    = $db->fetch_assoc($member)['member_name'];
		if(array_key_exists($row['category_id'], $categories)) {
			$row['category']	= $categories[$row['category_id']];
		}
		else {
			$row['category']	= 'Category Disabled';
		}
		$row['dt_created']	    = htmlTime($row['dt_created']);
		$row['dt_published']	= htmlTime($row['dt_published']);
		$galleries[] 		    = $row;
	}

	return $galleries;

}


function insert_gallery($subject, $body, $category_id, $member_id, $image_name, $status)
{

	$db = database();

	$db->insert('',
	'{db_prefix}galleries',
		array(
			'member_id' 	=> 'int',
			'category_id'	=> 'int',
			'title'		    => 'string',
			'body'		    => 'string',
			'image_name'	=> 'string',
			'dt_published'	=> 'int',
			'status'	    => 'int',
		),
		array (
			$member_id,
			$category_id,
			$subject,
			$body,
			$image_name,
			time(),
			$status,
		),
		array('id')
	);

	$gallery_id 	= $db->insert_id('{db_prefix}galleries', 'id');

	return $gallery_id;
}


function update_gallery( $subject, $body, $category_id, $gallery_id, $image_name, $status)
{
	$db = database();

	if(is_null($body) && is_null($image_name)) {
		$db->query('', '
		UPDATE {db_prefix}galleries
		SET title = {string:title}, category_id = {int:category_id}, status = {int:status}
			WHERE id = {int:id}',
			array (
				'title' 	    => $subject,
				'category_id'	=> $category_id,
				'status'	    => $status,
				'id'		    => $gallery_id,
			)
		);
	}
	else if(is_null($body)) {
		$db->query('', '
		UPDATE {db_prefix}galleries
		SET title = {string:title}, category_id = {int:category_id}, status = {int:status}, image_name = {string:image_name}
			WHERE id = {int:id}',
			array (
				'title' 	    => $subject,
				'category_id'	=> $category_id,
				'status'	    => $status,
                'image_name'    => $image_name,
				'id'		    => $gallery_id,
			)
		);
	}
	else if(is_null($image_name)) {
		$db->query('', '
		UPDATE {db_prefix}galleries
		SET title = {string:title}, category_id = {int:category_id}, status = {int:status}, body = {string:body}
			WHERE id = {int:id}',
			array (
				'title' 	    => $subject,
				'category_id'	=> $category_id,
				'status'	    => $status,
                'body'          => $body,
				'id'		    => $gallery_id,
			)
		);
	}
	else {
		$db->query('', '
		UPDATE {db_prefix}galleries
		SET title = {string:title}, body = {string:body}, category_id = {int:category_id}, status = {int:status}, image_name = {string:image_name}
			WHERE id = {int:id}',
			array (
				'title' 	    => $subject,
				'body'		    => $body,
				'image_name'	=> $image_name,
				'category_id'	=> $category_id,
				'status'	    => $status,
				'id'		    => $gallery_id,
			)
		);
	}
}

function delete_gallery($id)
{

	$db = database();

	$db->query('', '
		DELETE FROM {db_prefix}galleries
		WHERE id = {int:id}',
		array (
			'id'		=> $id,
		)
	);
}

function insert_category($name, $desc, $status)
{

	$db = database();

	$db->insert('',
		'{db_prefix}gallery_categories',
		array(
			'name' 		=> 'string',
			'description' 	=> 'string',
			'status'	=> 'int',
		),
		array (
			$name,
			$desc,
			$status,
		),
		array('id')
	);
}

function update_category( $category_id, $category_name, $category_desc, $category_enabled)
{
	$db = database();

	$db->query('', '
	UPDATE {db_prefix}gallery_categories
	SET name = {string:category_name} ,
	description = {string:category_desc},
	status = {int:category_enabled}
	WHERE id = {int:category_id}',
		array (
			'category_name' 	=> $category_name,
			'category_desc' 	=> $category_desc,
			'category_enabled'	=> $category_enabled,
			'category_id'		=> $category_id,
		)
	);
}

function delete_category($id)
{

	$db = database();

	$db->query('', '
		DELETE FROM {db_prefix}gallery_categories
		WHERE id = {int:id}',
		array (
			'id'		=> $id,
		)
	);
}

function resize_image($photoString, $fileName, $dimensions)
{

    $width  = $dimensions['width'];
    $height = $dimensions['height'];


    // Get new dimensions
    list($widthOriginal, $heightOriginal) = getimagesize($photoString);
    // Check dimensions we want are not the same as the image
    if(($widthOriginal != $width) || ($heightOriginal != $height)) {
        $ratioOriginal = $widthOriginal / $heightOriginal;

        if ( $width / $height > $ratioOriginal ) {
            $width = $height * $ratioOriginal;
        }
        else {
            $height = $width / $ratioOriginal;
        }

        // Resample
        $image_p  = imagecreatetruecolor($width, $height);
        $image    = imagecreatefromjpeg($photoString);
        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $widthOriginal, $heightOriginal);
        // Output
        imagejpeg($image_p, $fileName, 100);

        return TRUE;
    }
    else {
        return FALSE;
    }
}

