<?php
/**
 *
 * Topic Cement Style. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, phpBB Studio, https://www.phpbbstudio.com
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

	/** @var \phpbb\template\template */
	protected $template;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\auth\auth							$auth		Authentication object
	 * @param  \phpbb\db\driver\driver_interface		$db			Database object
	 * @param  \phpbb\template\template					$template	Template object
	 * @return void
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template)
	{
		$this->auth			= $auth;
		$this->db			= $db;
		$this->template		= $template;
	}

	/**
	 * Returns whether the user is authed
	 *
	 * @return bool
	 */
	public function tcs_is_authed()
	{
		return ($this->auth->acl_get('m_set_priority') || $this->auth->acl_get('a_set_priority'));
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
	 * @return array
	 */
	public function tcs_font_weight()
	{
		return ['', 'normal', 'bold', 'bolder', 'lighter', '100', '200', '300', '400', '500', '600', '700', '800', '900', 'initial', 'inherit'];
	}

	/**
	 * Return font_style's array
	 *
	 * @return array
	 */
	public function tcs_font_style()
	{
		return ['', 'normal', 'italic', 'oblique', 'initial', 'inherit'];
	}

	/**
	 * Build an options string for a HTML <select> field.
	 *
	 * @param  array	$array				The array to build the options from
	 * @param  mixed	$select				The option that should be selected
	 * @param  bool		$no_keys			Whether or not to use the array keys as <option value="">
	 * @return string						A string with all options for a selector
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

	/**
	 * Retrieve the title color
	 *
	 * @param  $topic_ids 		array 			the topic id array for which to retrieve the color
	 * @param  $topic_rowset	array|boolean 	the topic rowset data
	 * @return string							the style code for the title
	 * @return void
	 */
	public function get_topic_color($topic_ids, &$topic_rowset = false)
	{
		if ($topic_ids)
		{
			if (!is_array($topic_ids))
			{
				$topic_ids = [$topic_ids];
			}

			$sql = 'SELECT topic_id, topic_color, topic_background, topic_font_size, topic_font_weight, topic_font_style, topic_font_family
				FROM ' . TOPICS_TABLE . '
				WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids);
			$result = $this->db->sql_query($sql);
			$topic_color_rows = $this->db->sql_fetchrowset($result);
			$this->db->sql_freeresult($result);

			if (!$topic_color_rows)
			{
				return;
			}

			if (!$topic_rowset)
			{
				$this->topic_color			= $topic_color_rows[0]['topic_color'];
				$this->topic_background		= $topic_color_rows[0]['topic_background'];
				$this->topic_font_size		= $topic_color_rows[0]['topic_font_size'];
				$this->topic_font_weight	= $topic_color_rows[0]['topic_font_weight'];
				$this->topic_font_style		= $topic_color_rows[0]['topic_font_style'];
				$this->topic_font_family	= $topic_color_rows[0]['topic_font_family'];

				$this->template->assign_vars(array(
					'TOPIC_COLOR', $this->topic_color,
					'TOPIC_BCKG', $this->topic_background,
					'TOPIC_FONT_SIZE', $this->topic_font_size,
					'TOPIC_FONT_WEIGHT', $this->topic_font_weight,
					'TOPIC_FONT_STYLE', $this->topic_font_style,
					'TOPIC_FONT_FAMILY', $this->topic_font_family,
				));
			}
			else
			{
				foreach ($topic_color_rows as $row)
				{
					if (isset($topic_rowset[$row['topic_id']]))
					{
						$topic_rowset[$row['topic_id']]['topic_background']		= $row['topic_background'];
						$topic_rowset[$row['topic_id']]['topic_color']			= $row['topic_color'];
						$topic_rowset[$row['topic_id']]['topic_font_size']		= $row['topic_font_size'];
						$topic_rowset[$row['topic_id']]['topic_font_weight']	= $row['topic_font_weight'];
						$topic_rowset[$row['topic_id']]['topic_font_style']		= $row['topic_font_style'];
						$topic_rowset[$row['topic_id']]['topic_font_family']	= $row['topic_font_family'];
					}
				}
			}
		}
	}

	/**
	 * Colors and style for the topic titles that are in lists
	 *
	 * @param $row			array	The topic row
	 * @param $list_row		array	The list row
	 * @param $title_key	string	The key for the title
	 * @return string				The HTML formatted string for the title
	 */
	public function tcs_color_title_in_list($row, $list_row, $title_key)
	{
		/* At least chose a color first! */
		if (!empty($row['topic_color']))
		{
			$topic_color	= $row['topic_color'];
			$padding		= 'padding: 0 4px 0 4px;';

			/* Produce valid HTML */
			$topic_background	= !empty($row['topic_background'])	? $padding . ' background-color: ' . $row['topic_background'] . '; '	: '';
			$topic_font_size	= !empty($row['topic_font_size'])	? 'font-size: ' . $row['topic_font_size'] . 'px; '						: '';
			$topic_font_weight	= !empty($row['topic_font_weight'])	? 'font-weight: ' . $row['topic_font_weight'] . '; '					: '';
			$topic_font_style	= !empty($row['topic_font_style'])	? 'font-style: ' . $row['topic_font_style'] . '; '						: '';
			$topic_font_family	= !empty($row['topic_font_family'])	? 'font-family: ' . $row['topic_font_family'] . '; '					: '';

			$style_concat		= $topic_background . $topic_font_size . $topic_font_weight . $topic_font_style . $topic_font_family;

			$list_row[$title_key] = sprintf('<span style="' . $style_concat . 'color: %s;">%s</span>', $topic_color, $list_row[$title_key]);
		}

		return $list_row;
	}
}
