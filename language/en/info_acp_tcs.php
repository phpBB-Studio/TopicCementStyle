<?php
/**
 *
 * Topic Cement Style. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	// Modules
	'ACP_TCS_CAT'					=> 'phpBB Studio - Topic Cement Style',
	'ACP_TCS_SETTINGS'				=> 'Settings',

	// Forums
	'ACP_FORUM_TOPIC_PRIORITY'		=> 'Topic Cement Style',
	'ACP_FORUM_TOPIC_PRIORITY_DESC'	=> 'Whether or not we want to prioritise and style title of topics.',
));
