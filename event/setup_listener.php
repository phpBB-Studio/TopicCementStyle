<?php
/**
 *
 * Topic Cement Style. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\tcs\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Topic Cement Style Event listener.
 */
class setup_listener implements EventSubscriberInterface
{
	/**
	 * Assign functions defined in this class to event listeners in the core.
	 *
	 * @static
	 * @return array
	 * @access public
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup_after'								=> 'setup_lang',
			'core.permissions'									=> 'setup_permissions',
			'core.viewforum_modify_topic_ordering'				=> 'tcs_viewforum_modify_topic_ordering',
			'core.viewforum_modify_sort_direction'				=> 'tcs_viewforum_modify_sort_direction',
			//'core.viewforum_modify_sort_data_sql'				=> 'tcs_viewforum_modify_sort_data_sql',
			'core.viewforum_get_topic_ids_data'					=> 'tcs_viewforum_get_topic_ids_data',
			'core.viewforum_get_announcement_topic_ids_data'	=> array('tcs_viewforum_get_announcement_topic_ids_data', -1,),
			'core.posting_modify_template_vars'					=> 'tcs_topic_data_topic_priority',
			'core.posting_modify_submission_errors'				=> 'tcs_topic_priority_add_to_post_data',
			'core.posting_modify_submit_post_before'			=> 'tcs_topic_priority_add',
			'core.posting_modify_message_text'					=> 'tcs_modify_topic_priority',
			'core.submit_post_modify_sql_data'					=> 'tcs_submit_post_modify_sql_data',
			'core.viewtopic_modify_page_title'					=> 'tcs_viewtopic_modify_page_title',
			'core.viewforum_modify_topics_data'					=> 'tcs_viewforum_modify_topics_data',
			'core.viewforum_modify_topicrow'					=> 'tcs_viewforum_modify_topicrow',
			'core.display_forums_modify_template_vars'			=> 'tcs_display_forums_modify_template_vars',
			'core.display_forums_before'						=> 'tcs_display_forums_before',
		);
	}
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbbstudio\tcs\core\functions_common */
	protected $functions;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\auth\auth							$auth			Authentication object
	 * @param  \phpbb\db\driver\driver_interface
	 * @param  \phpbb\language\language					$language		Language object
	 * @param  \phpbb\request\request
	 * @param  \phpbb\template\template
	 * @param  \phpbbstudio\tcs\core\functions_common
	 * @return void
	 * @access public
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\request\request $request, \phpbb\template\template $template, \phpbbstudio\tcs\core\functions_common $functions)
	{
		$this->auth				= $auth;
		$this->db				= $db;
		$this->language			= $language;
		$this->request			= $request;
		$this->template			= $template;
		$this->functions		= $functions;
	}

	/**
	 * Load extension language file during user set up.
	 *
	 * @return void
	 * @access public
	 */
	public function setup_lang()
	{
		$this->language->add_lang('tcs_common', 'phpbbstudio/tcs');
	}

	/**
	 * Add permissions for TCS - Permission's language file is automatically loaded.
	 *
	 * @event  core.permissions
	 * @param  \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function setup_permissions($event)
	{
		/* Assigning them to local variables first */
		$permissions = $event['permissions'];
		$categories = $event['categories'];

		/* Setting up a new permissions's CAT for us */
		if ( !isset($categories['phpbb_studio']))
		{
			$categories['phpbb_studio']= 'ACL_CAT_PHPBB_STUDIO';
		}

		$permissions += 
		[
			'a_set_priority' => [
				'lang'	=> 'ACL_A_SET_PRIORITY',
				'cat'	=> 'phpbb_studio',
			],
			'm_set_priority' => [
				'lang'	=> 'ACL_M_SET_PRIORITY',
				'cat'	=> 'phpbb_studio',
			],
		];

		/* Merging our CAT to the native array of perms */
		$event['categories'] = array_merge($event['categories'], $categories);

		/* Copying back to event variable */
		$event['permissions'] = $permissions;
	}

	/**
	 * Modify the topic ordering if needed
	 *
	 * @event core.viewforum_modify_topic_ordering
	 * @var array	sort_by_text	Topic ordering options
	 * @var array	sort_by_sql		Topic ordering options
	 * @since 3.2.4
	 */
	public function tcs_viewforum_modify_topic_ordering($event)
	{
		$forum_id = $this->request->variable('f', 0);

		if ( $this->functions->tcs_forum_enabled($forum_id) )
		{
			$event['sort_by_sql'] = array(
				'a'	=> array('t.topic_priority', 't.topic_first_poster_name'),
				't'	=> array('t.topic_priority', 't.topic_last_post_time', 't.topic_last_post_id'),
				'r'	=> ( ($this->auth->acl_get('m_approve', $forum_id)) ? array('t.topic_priority', 't.topic_posts_approved + t.topic_posts_unapproved + t.topic_posts_softdeleted') : array('t.topic_priority', 't.topic_posts_approved') ),
				's'	=> array('t.topic_priority', 'LOWER(t.topic_title)'),
				'v'	=> array('t.topic_priority', 't.topic_views'),
			);
		}
	}

	/**
	 * Modify the topic sort ordering if needed
	 *
	 * @event core.viewforum_modify_sort_direction
	 * @var string	direction	Topic sort order
	 * @since 3.2.4
	 */
	public function tcs_viewforum_modify_sort_direction($event)
	{
		$forum_id = $this->request->variable('f', 0);

		/* Overrides also UCP choice if the TCS is active in this forum */
		if ( $this->functions->tcs_forum_enabled($forum_id) )
		{
			$event['direction'] = 'DESC';
		}
	}

	/**
	* Event to modify the SQL query before the topic ids data is retrieved
	*
	* @event core.viewforum_get_topic_ids_data
	* @var	array	forum_data		Data about the forum
	* @var	array	sql_ary			SQL query array to get the topic ids data
	* @var	string	sql_approved	Topic visibility SQL string
	* @var	int		sql_limit		Number of records to select
	* @var	string	sql_limit_time	SQL string to limit topic_last_post_time data
	* @var	array	sql_sort_order	SQL sorting string
	* @var	int		sql_start		Offset point to start selection from
	* @var	string	sql_where		SQL WHERE clause string
	* @var	bool	store_reverse	Flag indicating if we select from the late pages
	*
	* @since 3.1.0-RC4
	*
	* @changed 3.1.3 Added forum_data
	*/
	public function tcs_viewforum_get_topic_ids_data($event)
	{
		/* We don't check for forum enabled to avoid issues with active topics */
		$store_reverse = $event['store_reverse'];
		$sql_sort_order = $event['sql_sort_order'];

		$sql = $event['sql_ary'];
		$sql['ORDER_BY'] = 't.topic_type ' . ((!$store_reverse) ? 'DESC' : 'ASC') . ', t.topic_priority ' . ((!$store_reverse) ? 'DESC' : 'ASC') . ', ' . $sql_sort_order;
		$event['sql_ary'] = $sql;
	}

	/**
	* Event to modify the SQL query before the announcement topic ids data is retrieved
	*
	* @event core.viewforum_get_announcement_topic_ids_data
	* @var	array	forum_data			Data about the forum
	* @var	array	g_forum_ary			Global announcement forums array
	* @var	array	sql_anounce_array	SQL announcement array
	* @var	array	sql_ary				SQL query array to get the announcement topic ids data
	* @var	int		forum_id			The forum ID
	*
	* @since 3.1.10-RC1
	*/
	public function tcs_viewforum_get_announcement_topic_ids_data($event)
	{
		/**
		 * We don't check for forum enabled to keep the priority of Global Announcements board-wide 
		 * Our event overrides the similar one of the Topics Hierarchy extension if enabled
		 */
		$sql_ary = $event['sql_ary'];
		$sql_ary['ORDER_BY'] = 't.topic_type DESC, t.topic_priority DESC, t.topic_time DESC';
		$event['sql_ary'] = $sql_ary;
	}

	/**
	 * @todo
	 *
	 * @event  core.posting_modify_template_vars
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function tcs_topic_data_topic_priority($event)
	{
		$forum_id = $event['post_data']['forum_id'];

		if ( $this->functions->tcs_is_authed() && $this->functions->tcs_forum_enabled($forum_id))
		{
			$mode = $event['mode'];
			$post_data = $event['post_data'];
			$page_data = $event['page_data'];

			$post_data['topic_priority'] = (!empty($post_data['topic_priority'])) ? $post_data['topic_priority'] : 0;
			$post_data['topic_color'] = (!empty($post_data['topic_color'])) ? $post_data['topic_color'] : '';
			$post_data['topic_background'] = (!empty($post_data['topic_background'])) ? $post_data['topic_background'] : '';
			$post_data['topic_font_size'] = (!empty($post_data['topic_font_size'])) ? $post_data['topic_font_size'] : 0;
			$post_data['topic_font_weight'] = (!empty($post_data['topic_font_weight'])) ? $post_data['topic_font_weight'] : '';
			$post_data['topic_font_style'] = (!empty($post_data['topic_font_style'])) ? $post_data['topic_font_style'] : '';
			$post_data['topic_font_family'] = (!empty($post_data['topic_font_family'])) ? $post_data['topic_font_family'] : '';

			/* Check if we are posting or editing the very first post of the topic */
			if ( $mode == 'post' || ($mode == 'edit' && $post_data['topic_first_post_id'] == $post_data['post_id']) )
			{
				$sql = 'SELECT topic_font_weight FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . (int) $event['topic_id'];
				$result = $this->db->sql_query_limit($sql, 1);
				$topic_font_weight_select = (string) $this->db->sql_fetchfield('topic_font_weight');
				$this->db->sql_freeresult($result);
				$s_topic_font_weight = $this->functions->tcs_type_select($this->functions->tcs_font_weight(), $topic_font_weight_select, true);

				$sql = 'SELECT topic_font_style FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . (int) $event['topic_id'];
				$result = $this->db->sql_query_limit($sql, 1);
				$topic_font_style_select = (string) $this->db->sql_fetchfield('topic_font_style');
				$this->db->sql_freeresult($result);
				$s_topic_font_style = $this->functions->tcs_type_select($this->functions->tcs_font_style(), $topic_font_style_select, true);

				$page_data['TOPIC_PRIORITY'] = $this->request->variable('topic_priority', $post_data['topic_priority']);
				$page_data['TOPIC_COLOR'] = $this->request->variable('topic_color', $post_data['topic_color']);
				$page_data['TOPIC_BCKG'] = $this->request->variable('topic_background', $post_data['topic_background']);
				$page_data['TOPIC_FONT_SIZE'] = $this->request->variable('topic_font_size', $post_data['topic_font_size']);
				$page_data['TOPIC_FONT_WEIGHT'] = $s_topic_font_weight;
				$page_data['TOPIC_FONT_STYLE'] = $s_topic_font_style;
				$page_data['TOPIC_FONT_FAMILY'] = $this->request->variable('topic_font_family', $post_data['topic_font_family']);

				/* Template switches */
				$page_data['S_TOPIC_PRIORITY'] = (bool) $this->functions->tcs_is_authed();
			}

			$event['page_data']	= $page_data;
		}
	}

	/**
	 * @todo
	 *
	 * @event  core.posting_modify_template_vars
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function tcs_topic_priority_add_to_post_data($event)
	{
		if ( $this->functions->tcs_is_authed() && $this->functions->tcs_forum_enabled($event['forum_id']) )
		{
			$error = $event['error'];

			if (utf8_strlen($event['post_data']['topic_font_size']) > 40)
			{
				$error[] = $this->language->lang('TCS_TOPIC_FONT_SIZE_MAX_40');
			}

			if (utf8_strlen($event['post_data']['topic_font_family']) > 80)
			{
				$error[] = $this->language->lang('TCS_FONT_FAMILY_MAX_80');
			}

			$event['error'] = $error;

			$event['post_data'] = array_merge($event['post_data'], array(
				'topic_priority'	=> $this->request->variable('topic_priority', 0),
				'topic_color'		=> $this->request->variable('topic_color', '', true),
				'topic_background'	=> $this->request->variable('topic_background', '', true),
				'topic_font_size'	=> $this->request->variable('topic_font_size', 0),
				'topic_font_weight'	=> $this->request->variable('topic_font_weight', '', true),
				'topic_font_style'	=> $this->request->variable('topic_font_style', '', true),
				'topic_font_family'	=> $this->functions->tcs_strip_emojis($this->request->variable('topic_font_family', '', true)),
			));
		}
	}

	/**
	 * @todo
	 *
	 * @event  core.posting_modify_template_vars
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function tcs_topic_priority_add($event)
	{
		if ( $this->functions->tcs_is_authed() && $this->functions->tcs_forum_enabled($event['forum_id']) )
		{
			$event['data'] = array_merge($event['data'], array(
				'topic_priority'	=> $event['post_data']['topic_priority'],
				'topic_color'		=> $event['post_data']['topic_color'],
				'topic_background'	=> $event['post_data']['topic_background'],
				'topic_font_size'	=> $event['post_data']['topic_font_size'],
				'topic_font_weight'	=> $event['post_data']['topic_font_weight'],
				'topic_font_style'	=> $event['post_data']['topic_font_style'],
				'topic_font_family'	=> $event['post_data']['topic_font_family'],
			));
		}
	}

	/**
	 * @todo
	 *
	 * @event  core.posting_modify_template_vars
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function tcs_modify_topic_priority($event)
	{
		if ( $this->functions->tcs_is_authed() && $this->functions->tcs_forum_enabled($event['forum_id']) )
		{
			$event['post_data'] = array_merge($event['post_data'], array(
				// BigInt
				'topic_priority'	=> $this->request->variable('topic_priority', ( (!empty($event['post_data']['topic_priority'])) ? $event['post_data']['topic_priority'] : 0 ), 0),
				// HexDec (6)
				'topic_color'		=> $this->request->variable('topic_color', ( (!empty($event['post_data']['topic_color'])) ? $event['post_data']['topic_color'] : '' )),
				'topic_background'	=> $this->request->variable('topic_background', ( (!empty($event['post_data']['topic_background'])) ? $event['post_data']['topic_background'] : '' )),
				 // 0 to 65,535 bu MAX 80 px
				'topic_font_size'	=> $this->request->variable('topic_font_size', ( (!empty($event['post_data']['topic_font_size'])) ? $event['post_data']['topic_font_size'] : 0 ), 0),
				// MAX 30
				'topic_font_weight'	=> $this->request->variable('topic_font_weight', ( (!empty($event['post_data']['topic_font_weight'])) ? $event['post_data']['topic_font_weight'] : '' ), true),
				// MAX 30
				'topic_font_style'	=> $this->request->variable('topic_font_style', ( (!empty($event['post_data']['topic_font_style'])) ? $event['post_data']['topic_font_style'] : '' ), true),
				// MAX 80
				'topic_font_family'	=> $this->request->variable('topic_font_family', ( (!empty($event['post_data']['topic_font_family'])) ? $event['post_data']['topic_font_family'] : '' ), true),
			));
		}
	}

	/**
	 * @todo
	 *
	 * @event  core.posting_modify_template_vars
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function tcs_submit_post_modify_sql_data($event)
	{
		$mode = $event['post_mode'];

		if (!in_array($mode, array('edit_topic', 'edit_first_post', 'post')))
		{
			return;
		}

		$topic_priority = $event['data']['topic_priority'];
		$topic_color = $event['data']['topic_color'];
		$topic_background = $event['data']['topic_background'];
		$topic_font_size = $event['data']['topic_font_size'];
		$topic_font_weight = $event['data']['topic_font_weight'];
		$topic_font_style = $event['data']['topic_font_style'];
		$topic_font_family = $event['data']['topic_font_family'];

		$data_sql = $event['sql_data'];

		if ( $this->functions->tcs_is_authed() && $this->functions->tcs_forum_enabled($data_sql[TOPICS_TABLE]['sql']['forum_id']) )
		{
			$data_sql[TOPICS_TABLE]['sql']['topic_priority'] = (int) $topic_priority;
			$data_sql[TOPICS_TABLE]['sql']['topic_color'] = (string) $topic_color;
			$data_sql[TOPICS_TABLE]['sql']['topic_background'] = (string) $topic_background;
			$data_sql[TOPICS_TABLE]['sql']['topic_font_size'] = (int) $topic_font_size;
			$data_sql[TOPICS_TABLE]['sql']['topic_font_weight'] = (string) $topic_font_weight;
			$data_sql[TOPICS_TABLE]['sql']['topic_font_style'] = (string) $topic_font_style;
			$data_sql[TOPICS_TABLE]['sql']['topic_font_family'] = (string) $topic_font_family;
		}

		$event['sql_data'] = $data_sql;
	}

	/**
	 * Color the topic title in viewtopic
	 *
	 * @event  core.
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function tcs_viewtopic_modify_page_title($event)
	{
		$topic_data = $event['topic_data'];

		$forum_id = $topic_data['forum_id'];

		if ( $this->functions->tcs_forum_enabled($forum_id) )
		{
			$this->get_topic_color((int) $topic_data['topic_id']);
		}
	}

	/**
	 * @todo
	 *
	 * @event  core.
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function tcs_viewforum_modify_topics_data($event)
	{
		$forum_id = $event['forum_id'];

		if ( $this->functions->tcs_forum_enabled($forum_id) )
		{
			$topic_list = $event['topic_list'];
			$rowset = $event['rowset'];
			$this->get_topic_color($topic_list, $rowset);
			$event['rowset'] = $rowset;
		}
	}

	/**
	 * Add the topic title color to the topic_row
	 *
	 * @event  core.
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function tcs_viewforum_modify_topicrow($event)
	{
		$forum_id = $event['row']['forum_id'];

		if ( $this->functions->tcs_forum_enabled($forum_id) )
		{
			$event['topic_row'] = $this->tcs_color_title_in_list($event['row'], $event['topic_row'], 'TOPIC_TITLE');
		}
	}

	/**
	 * Take care of coloring topic titles for the last topic
	 *
	 * @event  core.
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function tcs_display_forums_before($event)
	{
		$forum_rows = $event['forum_rows'];

		if (!$event['forum_rows'] || !count($forum_rows))
		{
			return;
		}

		foreach ($forum_rows as $forum_id => $value)
		{
			if ( $this->functions->tcs_forum_enabled($forum_id) )
			{
				$forum_last_post_ids = array();

				foreach ($forum_rows as $row)
				{
					if ($row['forum_last_post_id'])
					{
						$forum_last_post_ids[] = $row['forum_last_post_id'];
					}
				}

				$sql_array = array(
					'SELECT'	=> 't.topic_id, t.topic_color, t.topic_background, t.topic_font_size, t.topic_font_weight, t.topic_font_style, t.topic_font_family, p.post_id',
					'FROM'	 	=> array(
						POSTS_TABLE		=> 'p',
						TOPICS_TABLE	=> 't',
					),
					'WHERE'		=> 'p.topic_id = t.topic_id AND ' . $this->db->sql_in_set('p.post_id', $forum_last_post_ids, false, true),
				);

				$result = $this->db->sql_query($this->db->sql_build_query('SELECT', $sql_array));
				$topic_color_rows = $this->db->sql_fetchrowset($result);
				$this->db->sql_freeresult($result);

				foreach ($forum_rows as $forum_id => $forum_data)
				{
					$post_id = (int) $forum_data['forum_last_post_id'];

					$topic_color = array_filter($topic_color_rows, function($color_row) use ($post_id) {
						return $color_row['post_id'] == $post_id;
					});

					if (count($topic_color))
					{
						foreach ($topic_color as $key => $value)
						{
							if (!empty($topic_color[$key]['topic_color']))
							{
								$forum_rows[$forum_id]['topic_color'] = $topic_color[$key]['topic_color'];
								$forum_rows[$forum_id]['topic_background'] = $topic_color[$key]['topic_background'];
								$forum_rows[$forum_id]['topic_font_size'] = $topic_color[$key]['topic_font_size'];
								$forum_rows[$forum_id]['topic_font_weight'] = $topic_color[$key]['topic_font_weight'];
								$forum_rows[$forum_id]['topic_font_style'] = $topic_color[$key]['topic_font_style'];
								$forum_rows[$forum_id]['topic_font_family'] = $topic_color[$key]['topic_font_family'];
							}
						}
					}
				}
			}
		}

		$event['forum_rows'] = $forum_rows;
	}

	/**
	 * Colorize the last post if needed
	 *
	 * @event  core.
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function tcs_display_forums_modify_template_vars($event)
	{
		$forums = array();
		$forums[] = $event['row']['forum_id'];

		foreach ($forums as $forum_id)
		{
			if ( $this->functions->tcs_forum_enabled($forum_id) )
			{
				$event['forum_row'] = $this->tcs_color_title_in_list($event['row'], $event['forum_row'], 'LAST_POST_SUBJECT_TRUNCATED');
			}
		}
	}

	/**
	 * Retrieve the title color
	 *
	 * @param  $topic_ids 		array 			the topic id array for which to retrieve the color
	 * @param  $topic_rowset	array|boolean 	the topic rowset data
	 * @return string							the style code for the title
	 * @return void
	 * @access private
	 */
	private function get_topic_color($topic_ids, &$topic_rowset = false)
	{
		if ($topic_ids)
		{
			if (!is_array($topic_ids))
			{
				$topic_ids = array($topic_ids);
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
				$this->topic_color = $topic_color_rows[0]['topic_color'];
				$this->topic_background = $topic_color_rows[0]['topic_background'];
				$this->topic_font_size = $topic_color_rows[0]['topic_font_size'];
				$this->topic_font_weight = $topic_color_rows[0]['topic_font_weight'];
				$this->topic_font_style = $topic_color_rows[0]['topic_font_style'];
				$this->topic_font_family = $topic_color_rows[0]['topic_font_family'];

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
						$topic_rowset[$row['topic_id']]['topic_background'] = $row['topic_background'];
						$topic_rowset[$row['topic_id']]['topic_color'] = $row['topic_color'];
						$topic_rowset[$row['topic_id']]['topic_font_size'] = $row['topic_font_size'];
						$topic_rowset[$row['topic_id']]['topic_font_weight'] = $row['topic_font_weight'];
						$topic_rowset[$row['topic_id']]['topic_font_style'] = $row['topic_font_style'];
						$topic_rowset[$row['topic_id']]['topic_font_family'] = $row['topic_font_family'];
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
	 * @return string				The HTML formatted string for the row
	 * @access private
	 */
	private function tcs_color_title_in_list($row, $list_row, $title_key)
	{
		/* At least chose a color first! */
		if (!empty($row['topic_color']))
		{
			$topic_color = $row['topic_color'];

			/* Let's produce valid HTML in any case */
			$topic_background	= !empty($row['topic_background']) ? 'background-color: ' . $row['topic_background'] . ' !important;' : '';
			$topic_font_size	= !empty($row['topic_font_size']) ? 'font-size: ' . $row['topic_font_size'] . 'px !important;' : '';
			$topic_font_weight	= !empty($row['topic_font_weight']) ? 'font-weight: ' . $row['topic_font_weight'] . ' !important;' : '';
			$topic_font_style	= !empty($row['topic_font_style']) ? 'font-style: ' . $row['topic_font_style'] . ' !important;' : '';
			$topic_font_family	= !empty($row['topic_font_family']) ? 'font-family: ' . $row['topic_font_family'] . ' !important;' : '';

			$list_row[$title_key] = sprintf('<span style="' . $topic_background . $topic_font_size . $topic_font_weight . $topic_font_style . $topic_font_family . ' color: %s;">%s</span>', $topic_color, $list_row[$title_key]);
		}
		return $list_row;
	}

}//EndOfClass
