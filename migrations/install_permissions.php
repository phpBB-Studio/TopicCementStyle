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

class install_permissions extends \phpbb\db\migration\migration
{
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
	public function update_data()
	{
		return [
			/* Admins Group permissions */
			['permission.add', ['a_set_priority']],		/* Can set topic priority */
			/* Moderators Group permissions */
			['permission.add', ['m_set_priority']],		/* Can set topic priority */
		];
	}
}
