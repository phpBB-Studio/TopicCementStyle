<?php
/**
 *
 * Topic Cement Style. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\tcs\migrations;

class install_user_schema extends \phpbb\db\migration\migration
{
	/**
	* {@inheritdoc}
	*/
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'topics', 'topic_priority');
	}

	/**
	* {@inheritdoc}
	*/
	static public function depends_on()
	{
		return ['\phpbb\db\migration\data\v32x\v327'];
	}

	/**
	* {@inheritdoc}
	*/
	public function update_schema()
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'topics' => [
					'topic_priority'	=> ['ULINT', 0],				// MAX 4294967295
					'topic_color'		=> ['VCHAR_UNI:7', null],		// HexDec color MAX 7
					'topic_background'	=> ['VCHAR_UNI:7', null],		// HexDec color MAX 7
					'topic_font_size'	=> ['USINT', 0],				// font_size in PX - if 0 inherits
					'topic_font_weight'	=> ['VCHAR_UNI:30', null],		// font_weight
					'topic_font_style'	=> ['VCHAR_UNI:30', null],		// font_style
					'topic_font_family'	=> ['VCHAR_UNI:80', null],		// font-family
				],
			],
			'add_index' => [
				$this->table_prefix . 'topics' => [
					'topic_priority' => ['topic_priority'],
				],
			],
		];
	}

	/**
	* {@inheritdoc}
	*/
	public function revert_schema()
	{
		return [
			'drop_columns'	=> [
				$this->table_prefix . 'topics' => [
					'topic_priority',
					'topic_color',
					'topic_background',
					'topic_font_size',
					'topic_font_weight',
					'topic_font_style',
					'topic_font_family',
				],
			],
			'drop_keys' => [
				$this->table_prefix . 'topics' => [
					'topic_priority',
				],
			],
		];
	}
}
