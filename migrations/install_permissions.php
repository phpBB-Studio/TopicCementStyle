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

class install_permissions extends \phpbb\db\migration\migration
{
	/**
	 * Assign migration file dependencies for this migration.
	 *
	 * @return array		Array of migration files
	 * @access public
	 * @static
	 */
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v32x\v322');
	}

	/**
	 * Add the Topic Cement Style extension permissions to the database.
	 *
	 * @return array 		Array of permissions
	 * @access public
	 */
	public function update_data()
	{
		return array(
			/* Admins Group permissions */
			array('permission.add', array('a_set_priority')),		/* Can set topic priority */
			/* Moderators Group permissions */
			array('permission.add', array('m_set_priority')),		/* Can set topic priority */
		);
	}
}
