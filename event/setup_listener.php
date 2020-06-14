<?php
/**
 *
 * Topic Cement Style. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\tcs\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Topic Cement Style Event listener.
 */
class setup_listener implements EventSubscriberInterface
{
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
	 * @param  \phpbb\db\driver\driver_interface		$db				Database object
	 * @param  \phpbb\language\language					$language		Language object
	 * @param  \phpbb\request\request					$request		Request object
	 * @param  \phpbb\template\template					$template		Template object
	 * @param  \phpbbstudio\tcs\core\functions_common	$functions		Custom class of functions
	 * @return void
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbbstudio\tcs\core\functions_common $functions
	)
	{
		$this->auth			= $auth;
		$this->db			= $db;
		$this->language		= $language;
		$this->request		= $request;
		$this->template		= $template;
		$this->functions	= $functions;
	}

	/**
	 * Assign functions defined in this class to event listeners in the core.
	 *
	 * @static
	 * @return array
	 */
	static public function getSubscribedEvents()
	{
		return [
			'core.user_setup_after'								=> 'setup_lang',
			'core.permissions'									=> 'setup_permissions',
			'core.viewforum_modify_topic_ordering'				=> 'tcs_viewforum_modify_topic_ordering',
			'core.viewforum_get_topic_ids_data'					=> 'tcs_viewforum_get_topic_ids_data',
			'core.viewforum_get_announcement_topic_ids_data'	=> ['tcs_viewforum_get_announcement_topic_ids_data', -1,],
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
		];
	}

	/**
	 * Load extension common language file during user set up.
	 *
	 * @return void
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
	 */
	public function setup_permissions(\phpbb\event\data $event)
	{
		$categories = $event['categories'];
		$permissions = $event['permissions'];

		if (empty($categories['phpbb_studio']))
		{
			/* Setting up our custom CAT */
			$categories['phpbb_studio'] = 'ACL_CAT_PHPBB_STUDIO';

			$event['categories'] = $categories;
		}

		$perms = [
			'a_set_priority',
			'm_set_priority',
		];

		foreach ($perms as $permission)
		{
			$permissions[$permission] = ['lang' => 'ACL_' . utf8_strtoupper($permission), 'cat' => 'phpbb_studio'];
		}

		$event['permissions'] = $permissions;
	}

	/**
	 * Modify the topic ordering if needed
	 *
	 * @event  core.viewforum_modify_topic_ordering
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 */
	public function tcs_viewforum_modify_topic_ordering(\phpbb\event\data $event)
	{
		$forum_id = $this->request->variable('f', 0);

		$event['sort_by_sql'] = array(
			'a' => array('t.topic_priority', 't.topic_first_poster_name'),
			't' => array('t.topic_priority', 't.topic_last_post_time', 't.topic_last_post_id'),
			'r' => (
				$this->auth->acl_get('m_approve', $forum_id)
				? array('t.topic_priority', 't.topic_posts_approved + t.topic_posts_unapproved + t.topic_posts_softdeleted')
				: array('t.topic_priority', 't.topic_posts_approved')
			),
			's' => array('t.topic_priority', 'LOWER(t.topic_title)'),
			'v' => array('t.topic_priority', 't.topic_views'),
		);
	}

	/**
	 * Modify the topic sort ordering if needed
	 *
	 * @event  core.viewforum_get_topic_ids_data
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 */
	public function tcs_viewforum_get_topic_ids_data(\phpbb\event\data $event)
	{
		$store_reverse = $event['store_reverse'];
		$sql_sort_order = $event['sql_sort_order'];

		$sql = $event['sql_ary'];

		$sql['ORDER_BY'] = 't.topic_type ' . ((!$store_reverse) ? 'DESC' : 'ASC') . ', t.topic_priority ' . ((!$store_reverse) ? 'DESC' : 'ASC') . ', ' . $sql_sort_order;

		$event['sql_ary'] = $sql;
	}

	/**
	 * Modify the topic sort ordering if needed
	 *
	 * @event  core.viewforum_get_announcement_topic_ids_data
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 */
	public function tcs_viewforum_get_announcement_topic_ids_data(\phpbb\event\data $event)
	{
		/**
		 * This event overrides the similar one of the 3Di's Topics Hierarchy extension, if enabled
		 */
		$sql_ary = $event['sql_ary'];

		$sql_ary['ORDER_BY'] = 't.topic_type DESC, t.topic_priority DESC, t.topic_time DESC';

		$event['sql_ary'] = $sql_ary;
	}

	/**
	 * Modifies template's data
	 *
	 * @event  core.posting_modify_template_vars
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 */
	public function tcs_topic_data_topic_priority(\phpbb\event\data $event)
	{
		if ($this->functions->tcs_is_authed())
		{
			$mode = $event['mode'];

			$page_data = $event['page_data'];

			$post_data = $event['post_data'];

			$post_data['topic_priority']		= (!empty($post_data['topic_priority']))	? $post_data['topic_priority']		: 0;
			$post_data['topic_color']			= (!empty($post_data['topic_color']))		? $post_data['topic_color']			: '';
			$post_data['topic_background']		= (!empty($post_data['topic_background']))	? $post_data['topic_background']	: '';
			$post_data['topic_font_size']		= (!empty($post_data['topic_font_size']))	? $post_data['topic_font_size']		: 0;
			$post_data['topic_font_weight']		= (!empty($post_data['topic_font_weight']))	? $post_data['topic_font_weight']	: '';
			$post_data['topic_font_style']		= (!empty($post_data['topic_font_style']))	? $post_data['topic_font_style']	: '';
			$post_data['topic_font_family']		= (!empty($post_data['topic_font_family']))	? $post_data['topic_font_family']	: '';

			/**
			 * Check if we are posting or editing the very first post of the topic
			 */
			if ($mode == 'post' || ($mode == 'edit' && $post_data['topic_first_post_id'] == $post_data['post_id']))
			{
				/* We do some queries in order to know where we are */
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

				$page_data['TOPIC_PRIORITY']		= $this->request->variable('topic_priority', $post_data['topic_priority']);
				$page_data['TOPIC_COLOR']			= $this->request->variable('topic_color', $post_data['topic_color']);
				$page_data['TOPIC_BCKG']			= $this->request->variable('topic_background', $post_data['topic_background']);
				$page_data['TOPIC_FONT_SIZE']		= $this->request->variable('topic_font_size', $post_data['topic_font_size']);
				$page_data['TOPIC_FONT_WEIGHT']		= $s_topic_font_weight;
				$page_data['TOPIC_FONT_STYLE']		= $s_topic_font_style;
				$page_data['TOPIC_FONT_FAMILY']		= $this->request->variable('topic_font_family', $post_data['topic_font_family']);

				/* Template switch */
				$page_data['S_TOPIC_PRIORITY']		= (bool) $this->functions->tcs_is_authed();
			}

			$event['page_data']	= $page_data;
		}
	}

	/**
	 * Errors handler
	 *
	 * @event  core.posting_modify_submission_errors
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 */
	public function tcs_topic_priority_add_to_post_data(\phpbb\event\data $event)
	{
		if ($this->functions->tcs_is_authed())
		{
			$error = $event['error'];

			if ((int) $event['post_data']['topic_priority'] > 4294967295)
			{
				$error[] = $this->language->lang('TCS_TOPIC_PRIORITY_MAX');
			}

			if (utf8_strlen($event['post_data']['topic_font_size']) > 40)
			{
				$error[] = $this->language->lang('TCS_TOPIC_FONT_SIZE_MAX_40');
			}

			if (utf8_strlen($event['post_data']['topic_font_family']) > 80)
			{
				$error[] = $this->language->lang('TCS_FONT_FAMILY_MAX_80');
			}

			$event['error'] = $error;

			$event['post_data'] = array_merge($event['post_data'], [
				'topic_priority'	=> $this->request->variable('topic_priority', 0),
				'topic_color'		=> $this->request->variable('topic_color', '', true),
				'topic_background'	=> $this->request->variable('topic_background', '', true),
				'topic_font_size'	=> $this->request->variable('topic_font_size', 0),
				'topic_font_weight'	=> $this->request->variable('topic_font_weight', '', true),
				'topic_font_style'	=> $this->request->variable('topic_font_style', '', true),
				'topic_font_family'	=> $this->functions->tcs_strip_emojis($this->request->variable('topic_font_family', '', true)),
			]);
		}
	}

	/**
	 * Handles the post submission before it happens
	 *
	 * @event  core.posting_modify_submit_post_before
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 */
	public function tcs_topic_priority_add(\phpbb\event\data $event)
	{
		if ($this->functions->tcs_is_authed())
		{
			$event['data'] = array_merge($event['data'], [
				'topic_priority'	=> $event['post_data']['topic_priority'],
				'topic_color'		=> $event['post_data']['topic_color'],
				'topic_background'	=> $event['post_data']['topic_background'],
				'topic_font_size'	=> $event['post_data']['topic_font_size'],
				'topic_font_weight'	=> $event['post_data']['topic_font_weight'],
				'topic_font_style'	=> $event['post_data']['topic_font_style'],
				'topic_font_family'	=> $event['post_data']['topic_font_family'],
			]);
		}
	}

	/**
	 * Modifies post data
	 *
	 * @event  core.posting_modify_message_text
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 */
	public function tcs_modify_topic_priority(\phpbb\event\data $event)
	{
		if ($this->functions->tcs_is_authed())
		{
			$event['post_data'] = array_merge($event['post_data'], [
				'topic_priority'	=> $this->request->variable('topic_priority', (!empty($event['post_data']['topic_priority']) ? $event['post_data']['topic_priority'] : 0), 0),
				'topic_color'		=> $this->request->variable('topic_color', (!empty($event['post_data']['topic_color']) ? $event['post_data']['topic_color'] : ''), true),
				'topic_background'	=> $this->request->variable('topic_background', (!empty($event['post_data']['topic_background']) ? $event['post_data']['topic_background'] : ''), true),
				'topic_font_size'	=> $this->request->variable('topic_font_size', (!empty($event['post_data']['topic_font_size']) ? $event['post_data']['topic_font_size'] : 0), 0),
				'topic_font_weight'	=> $this->request->variable('topic_font_weight', (!empty($event['post_data']['topic_font_weight']) ? $event['post_data']['topic_font_weight'] : ''), true),
				'topic_font_style'	=> $this->request->variable('topic_font_style', (!empty($event['post_data']['topic_font_style']) ? $event['post_data']['topic_font_style'] : ''), true),
				'topic_font_family'	=> $this->request->variable('topic_font_family', (!empty($event['post_data']['topic_font_family']) ? $event['post_data']['topic_font_family'] : ''), true),
			]);
		}
	}

	/**
	 * Modifies SQL data for post submision
	 *
	 * @event  core.submit_post_modify_sql_data
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 */
	public function tcs_submit_post_modify_sql_data(\phpbb\event\data $event)
	{
		$mode = $event['post_mode'];

		if (!in_array($mode, ['edit_topic', 'edit_first_post', 'post']))
		{
			return;
		}

		$topic_priority		= isset($event['data']['topic_priority'])		? $event['data']['topic_priority']		: '';
		$topic_color		= isset($event['data']['topic_color'])			? $event['data']['topic_color']			: '';
		$topic_background	= isset($event['data']['topic_background'])		? $event['data']['topic_background']	: '';
		$topic_font_size	= isset($event['data']['topic_font_size'])		? $event['data']['topic_font_size']		: '';
		$topic_font_weight	= isset($event['data']['topic_font_weight'])	? $event['data']['topic_font_weight']	: '';
		$topic_font_style	= isset($event['data']['topic_font_style'])		? $event['data']['topic_font_style']	: '';
		$topic_font_family	= isset($event['data']['topic_font_family'])	? $event['data']['topic_font_family']	: '';

		$data_sql = $event['sql_data'];

		if ($this->functions->tcs_is_authed())
		{
			$data_sql[TOPICS_TABLE]['sql']['topic_priority']		= (int) $topic_priority;
			$data_sql[TOPICS_TABLE]['sql']['topic_color']			= (string) $topic_color;
			$data_sql[TOPICS_TABLE]['sql']['topic_background']		= (string) $topic_background;
			$data_sql[TOPICS_TABLE]['sql']['topic_font_size']		= (int) $topic_font_size;
			$data_sql[TOPICS_TABLE]['sql']['topic_font_weight']		= (string) $topic_font_weight;
			$data_sql[TOPICS_TABLE]['sql']['topic_font_style']		= (string) $topic_font_style;
			$data_sql[TOPICS_TABLE]['sql']['topic_font_family']		= (string) $topic_font_family;
		}

		$event['sql_data'] = $data_sql;
	}

	/**
	 * Colorize the topic title in viewtopic
	 *
	 * @event  core.viewtopic_modify_page_title
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 */
	public function tcs_viewtopic_modify_page_title(\phpbb\event\data $event)
	{
		$this->functions->get_topic_color((int) $event['topic_data']['topic_id']);
	}

	/**
	 * If forum is enabled then apply our logic.
	 *
	 * @event  core.viewforum_modify_topics_data
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 */
	public function tcs_viewforum_modify_topics_data(\phpbb\event\data $event)
	{
		$topic_list = $event['topic_list'];
		$rowset = $event['rowset'];

		$this->functions->get_topic_color($topic_list, $rowset);

		$event['rowset'] = $rowset;
	}

	/**
	 * Add the topic title color to the topic_row - viewforum topic list
	 *
	 * @event  core.viewforum_modify_topicrow
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 */
	public function tcs_viewforum_modify_topicrow(\phpbb\event\data $event)
	{
		$event['topic_row'] = $this->functions->tcs_color_title_in_list($event['row'], $event['topic_row'], 'TOPIC_TITLE');
	}

	/**
	 * Take care of coloring topic titles for the last topic (not on index)
	 *
	 * @event  core.display_forums_before
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 */
	public function tcs_display_forums_before(\phpbb\event\data $event)
	{
		$forum_rows = $event['forum_rows'];

		if (!$event['forum_rows'] || !count($forum_rows))
		{
			return;
		}

		$forum_last_post_ids = [];

		foreach ($forum_rows as $row)
		{
			if ($row['forum_last_post_id'])
			{
				$forum_last_post_ids[] = $row['forum_last_post_id'];
			}
		}

		$sql_array = [
			'SELECT'	=> 't.topic_id, t.topic_color, t.topic_background, t.topic_font_size, t.topic_font_weight, t.topic_font_style, t.topic_font_family, p.post_id',
			'FROM'	 	=> [
				POSTS_TABLE		=> 'p',
				TOPICS_TABLE	=> 't',
			],
			'WHERE'		=> 'p.topic_id = t.topic_id AND ' . $this->db->sql_in_set('p.post_id', $forum_last_post_ids, false, true),
		];

		$result = $this->db->sql_query($this->db->sql_build_query('SELECT', $sql_array));
		$topic_color_rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		foreach ($forum_rows as $forum_id => $forum_data)
		{
			$post_id = (int) $forum_data['forum_last_post_id'];

			$topic_color = array_filter($topic_color_rows,
				function($color_row) use ($post_id)
				{
					return $color_row['post_id'] == $post_id;
				}
			);

			if (count($topic_color))
			{
				foreach ($topic_color as $key => $value)
				{
					if (!empty($topic_color[$key]['topic_color']))
					{
						$forum_rows[$forum_id]['topic_color']		= $topic_color[$key]['topic_color'];
						$forum_rows[$forum_id]['topic_background']	= $topic_color[$key]['topic_background'];
						$forum_rows[$forum_id]['topic_font_size']	= $topic_color[$key]['topic_font_size'];
						$forum_rows[$forum_id]['topic_font_weight']	= $topic_color[$key]['topic_font_weight'];
						$forum_rows[$forum_id]['topic_font_style']	= $topic_color[$key]['topic_font_style'];
						$forum_rows[$forum_id]['topic_font_family']	= $topic_color[$key]['topic_font_family'];
					}
				}
			}
		}

		$event['forum_rows'] = $forum_rows;
	}

	/**
	 * Colorize the last post (on index)
	 *
	 * @event  core.display_forums_modify_template_vars
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 */
	public function tcs_display_forums_modify_template_vars(\phpbb\event\data $event)
	{
		$event['forum_row'] = $this->functions->tcs_color_title_in_list($event['row'], $event['forum_row'], 'LAST_POST_SUBJECT_TRUNCATED');
	}
}
