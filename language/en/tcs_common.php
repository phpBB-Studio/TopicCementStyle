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
	'TCS_LEGEND_1'						=> 'Topic Cement Priority',
	'TCS_LEGEND_2'						=> 'Topic Cement Style',

	'TCS_PRIORITY'						=> 'Topic priority',
	'TCS_PRIORITY_DESC'					=> 'Works with every topic type.  0 disables this feature.',
	'TCS_PRIORITY_EXP'					=> 'Can be used alone.',
	'TCS_TOPIC_COLOR'					=> 'Subject',
	'TCS_TOPIC_BCKG'					=> 'Subject background',
	'TCS_TOPIC_HEX_STORED'				=> 'current',
	'TCS_COLOR_EXPLAIN'					=> 'Chose a color to use the other settings.',
	'TCS_TOPIC_F_SIZE'					=> 'Font size (px)',
	'TCS_TOPIC_F_SIZE_RANGE'			=> 'Pixels',
	'TCS_TOPIC_F_SIZE_NATIVE'			=> 'A value of 0 disables this feature.',
	'TCS_TOPIC_F_WEIGHT'				=> 'Font weight',
	'TCS_TOPIC_F_STYLE'					=> 'Font style',
	'TCS_TOPIC_F_FAMILY'				=> 'Font family',
	'TCS_COLORPICKER_EXPLAIN'			=> 'Input a HexDec value or use the color-picker.',
	'TCS_COLORPICKER_STORED'			=> 'Color HexDec value and actual stored color.',
	'TCS_TOPIC_F_FAMILY_HOLDER'			=> 'Like tahoma, Times New Roman, etc',
	'TCS_W3S'							=> 'w3schools',
	'TCS_BLANK'							=> 'Leave it blank to disable this feature.',
	'TCS_TOPIC_PRIORITY_MAX'			=> 'The topic priority can not exceed 4294967295.',
	'TCS_TOPIC_FONT_SIZE_MAX_80'		=> 'The font size can not be more than 80px.',
	'TCS_FONT_FAMILY_MAX_40'			=> 'The font family can not be more than 40 chars long.',

	// Translators please do not change the following line, no need to translate it!
	'PHPBBSTUDIO_TCS_CREDIT_LINE'		=> '<a href="https://phpbbstudio.com">Topic Cement Style</a> &copy; 2020 - phpBB Studio',
]);
