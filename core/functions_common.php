<?php
/**
 *
 * Topic Cement Style. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\tcs\core;

/**
 * Common functions.
 */
class functions_common
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\auth\auth							$auth			Authentication object
	 * @param  \phpbb\db\driver\driver_interface		$db				Database object
	 * @return void
	 * @access public
	 */
	public function __construct(
		\phpbb\auth\auth $auth, 
		\phpbb\db\driver\driver_interface $db
	)
	{
		$this->auth		= $auth;
		$this->db		= $db;
	}

	/**
	 * Returns whether the user is authed
	 *
	 * @return bool
	 */
	public function tcs_is_authed()
	{
		return (bool) ( $this->auth->acl_get('m_set_priority') || $this->auth->acl_get('a_set_priority') );
	}

	/**
	 * Check if the TCS extension is enabled for a specific forum.
	 *
	 * @param  int		$forum_id		The forum identifier
	 * @return bool						Whether or not the extension is enabled for this forum
	 * @access public
	 */
	public function tcs_forum_enabled($forum_id)
	{
		if (empty($forum_id))
		{
			return false;
		}

		$sql = 'SELECT forum_topic_priority
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . (int) $forum_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$s_enabled = (bool) $this->db->sql_fetchfield('forum_topic_priority');
		$this->db->sql_freeresult($result);

		return (bool) $s_enabled;
	}

	/**
	 * Strip emojis from a string
	 *
	 * @param string		$string
	 * @return string
	 */
	public function tcs_strip_emojis($string)
	{
		return preg_replace('/[\x{10000}-\x{10FFFF}]/u', "", $string);
	}

	/**
	 * Return font_weight's array
	 *
	 * @param string		$string
	 * @return string
	 */
	public function tcs_font_weight()
	{
		return array('', 'normal', 'bold', 'bolder', 'lighter', '100', '200', '300', '400', '500', '600', '700', '800', '900', 'initial', 'inherit');
	}

	/**
	 * Return font_style's array
	 *
	 * @param string		$string
	 * @return string
	 */
	public function tcs_font_style()
	{
		return array('', 'normal', 'italic', 'oblique', 'initial', 'inherit');
	}

	/**
	 * Build an options string for a HTML <select> field.
	 *
	 * @param  array	$array				The array to build the options from
	 * @param  mixed	$select				The option that should be selected
	 * @param  bool		$no_keys			Whether or not to use the array keys as <option value="">
	 * @return string						An string of all options for a select field
	 */
	public function tcs_type_select($array, $select, $no_keys)
	{
		$options = '';

		foreach ($array as $key => $option)
		{
			$value = $no_keys ? $option : $key;
			$selected = $select == $value ? ' selected' : '';

			$options .= '<option value="' . $value . '"' . $selected . '>' . $option . '</option>';
		}

		return (string) $options;
	}
}
