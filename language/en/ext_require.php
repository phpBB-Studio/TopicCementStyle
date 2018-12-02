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
	'DTST_ERROR_325_VERSION'	=> 'Minimum phpBB version required is 3.2.5-RC1 but less than 3.3.0@dev',
	'DTST_ERROR_PHP_VERSION'	=> 'PHP version must be equal or greater than 5.5',
));
