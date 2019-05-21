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
	'ACL_CAT_PHPBB_STUDIO'				=> 'phpBB Studio',

	'TCS_LEGEND_1'						=> 'Topic Cement Style',

	'TCS_PRIORITY'						=> 'Topic priority',
	'TCS_PRIORITY_DESC'					=> 'Works with every topic type. 0 to disable.',
	'TCS_PRIORITY_EXP'					=> 'Can be used alone.',
	'TCS_TOPIC_COLOR'					=> 'Subject color',
	'TCS_TOPIC_BCKG'					=> 'Subject background color',
	'TCS_TOPIC_HEX_STORED'				=> 'Now',
	'TCS_COLOR_EXPLAIN'					=> 'Chose a color to enable the fields below.',
	'TCS_TOPIC_F_SIZE'					=> 'Font size',
	'TCS_TOPIC_F_SIZE_RANGE'			=> 'In px, can be max. 40.',
	'TCS_TOPIC_F_SIZE_NATIVE'			=> '<em>(Zero to use native size.)</em>',
	'TCS_TOPIC_F_WEIGHT'				=> 'Font weight',
	'TCS_TOPIC_F_STYLE'					=> 'Font style',
	'TCS_TOPIC_F_FAMILY'				=> 'Font family',
	'TCS_COLORPICKER_EXPLAIN'			=> 'Input a color in #HexDec value or use the color-picker.',
	'TCS_COLORPICKER_STORED'			=> 'Color #HexDec value and actual color stored in the DB.',
	'TCS_TOPIC_F_FAMILY_HOLDER'			=> 'Like tahoma or Times New Roman, Georgia, serif or whatever.',
	'TCS_W3S'							=> 'w3schools',
	'TCS_BLANK'							=> '<em>(Leave it blank to disable.)</em>',
	'TCS_TOPIC_FONT_SIZE_MAX_80'		=> 'The font size can not be more than 80px.',
	'TCS_FONT_FAMILY_MAX_40'			=> 'The font family can not be more than 40 chars long.',

	// Translators please do not change the following line, no need to translate it!
	'PHPBBSTUDIO_TCS_CREDIT_LINE'		=> '<a href="https://phpbbstudio.com">Topic Cement Style</a> &copy; 2019 - phpBB Studio',
));
