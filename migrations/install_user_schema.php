<?php
/**
 *
 * Topic Cement Style. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\tcs\migrations;

class install_user_schema extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'topics', 'topic_priority');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v32x\v322');
	}

	public function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'topics'			=> array(
					'topic_priority'				=> array('ULINT', 0),
					'topic_color'					=> array('VCHAR_UNI:7', null),		// HexDec color MAX 7
					'topic_background'				=> array('VCHAR_UNI:7', null),		// HexDec color MAX 7
					'topic_font_size'				=> array('USINT', 0),				// font_size in PX - if 0 inherits
					'topic_font_weight'				=> array('VCHAR_UNI:30', null),		// font_weight
					'topic_font_style'				=> array('VCHAR_UNI:30', null),		// font_style
					'topic_font_family'				=> array('VCHAR_UNI:80', null),		// font-family
				),
			),
			'add_index' => array(
				$this->table_prefix . 'topics'			=> array(
					'topic_priority'				=> array('topic_priority'),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'topics'			=> array(
					'topic_priority',
					'topic_color',
					'topic_background',
					'topic_font_size',
					'topic_font_weight',
					'topic_font_style',
					'topic_font_family',
				),
			),
			'drop_keys' => array(
				$this->table_prefix . 'topics'			=> array(
					'topic_priority',
				),
			),
		);
	}
}
