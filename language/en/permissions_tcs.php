<?php
/**
 *
 * Topic Cement Style. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

/**
 * Some characters you may want to copy&paste: ’ » “ ” …
 */
$lang = array_merge($lang, [
	'ACL_A_SET_PRIORITY'	=> '<strong>Topic Cement Style</strong> - Can set topic priority',
	'ACL_M_SET_PRIORITY'	=> '<strong>Topic Cement Style</strong> - Can set topic priority',
]);
