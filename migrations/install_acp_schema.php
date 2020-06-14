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

class install_acp_schema extends \phpbb\db\migration\migration
{
	/**
	* {@inheritdoc}
	*/
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'forums', 'forum_topic_priority');
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
				$this->table_prefix . 'forums' => [
					'forum_topic_priority'	=>	['BOOL', 1],
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
				$this->table_prefix . 'forums' => [
					'forum_topic_priority',
				],
			],
		];
	}
}
