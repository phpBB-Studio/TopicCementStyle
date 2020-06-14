<?php
/**
 *
 * Topic Cement Style. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\tcs;

/**
 * Topic Cement Style Extension base
 *
 */
class ext extends \phpbb\extension\base
{
	/**
	 * Indicate whether or not the extension can be enabled.
	 *
	 * @return bool				True if enableable, false otherwise.
	 * @access public
	 */
	public function is_enableable()
	{
		if (!(phpbb_version_compare(PHPBB_VERSION, '3.2.7', '>=') && phpbb_version_compare(PHPBB_VERSION, '4.0.0@dev', '<')))
		{
			if (phpbb_version_compare(PHPBB_VERSION, '3.3.0', '>='))
			{
				$language= $this->container->get('language');
				$language->add_lang('ext_require', 'phpbbstudio/tcs');

				return $language->lang('ERROR_PHPBB_VERSION', '3.2.7', '4.0.0@dev');
			}
			else
			{
				$user = $this->container->get('user');
				$lang = $user->lang;

				$user->add_lang_ext('phpbbstudio/tcs', 'ext_require');

				$lang['EXTENSION_NOT_ENABLEABLE'] .= '<br>' . $user->lang('ERROR_PHPBB_VERSION', '3.2.7', '4.0.0@dev');

				$user->lang = $lang;

				return false;
			}
		}

		return true;
	}
}
