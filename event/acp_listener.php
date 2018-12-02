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
 * ACP listener.
 */
class acp_listener implements EventSubscriberInterface
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
			'core.acp_manage_forums_request_data'		=> 'tcs_acp_manage_forums_request_data',
			'core.acp_manage_forums_initialise_data'	=> 'tcs_acp_manage_forums_initialise_data',
			'core.acp_manage_forums_display_form'		=> 'tcs_acp_manage_forums_display_form',
		);
	}

	/** @var \phpbb\request\request */
	protected $request;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\request\request $request Request object
	 * @return void
	 * @access public
	 */
	public function __construct(\phpbb\request\request $request)
	{
		$this->request = $request;
	}

	/**
	 * (Add/update actions) - Submit form.
	 *
	 * @event  core.acp_manage_forums_request_data
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function tcs_acp_manage_forums_request_data($event)
	{
		$forum_data = $event['forum_data'];

		$forum_data['forum_topic_priority'] = $this->request->variable('forum_topic_priority', 0);

		$event['forum_data'] = $forum_data;
	}

	/**
	 * New Forums added (default disabled).
	 *
	 * @event  core.acp_manage_forums_initialise_data
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function tcs_acp_manage_forums_initialise_data($event)
	{
		if ($event['action'] == 'add')
		{
			$forum_data = $event['forum_data'];

			$forum_data['forum_topic_priority'] = (bool) true;

			$event['forum_data'] = $forum_data;
		}
	}

	/**
	 * ACP forums (template data).
	 *
	 * @event  core.acp_manage_forums_display_form
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 * @access public
	 */
	public function tcs_acp_manage_forums_display_form($event)
	{
		$template_data = $event['template_data'];

		$template_data['S_FORUM_TOPIC_PRIORITY'] = $event['forum_data']['forum_topic_priority'];

		$event['template_data'] = $template_data;
	}
}