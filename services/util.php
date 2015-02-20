<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\core\services;

class util
{
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** array */
	protected $scripts;

	/** array */
	public $asset_path;

	/**
	 * Constructor
	 *
	 * @param \phpbb\path_helper					$path_helper	Path helper object
	 * @param \phpbb\template\template				$template		Template object
	 * @param \phpbb\user							$user			User object
	 */
	public function __construct(\phpbb\path_helper $path_helper, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->template = $template;
		$this->user = $user;
		$this->asset_path = $path_helper->get_web_root_path();
		$this->scripts = array(
			'js'	=> array(),
			'css'   => array(),
		);
	}

	/**
	 * include css/javascript
	 * receives an array of form: array('js' => array('test.js', 'test2.js'), 'css' => array())
	 */
	public function add_assets($scripts)
	{
		foreach ($scripts as $type => $paths)
		{
			$count = (isset($this->scripts[$type])) ? count($this->scripts[$type]) : 0;
			foreach ($paths as $key => $script)
			{
				if (isset($this->scripts[$type][$key]))
				{
					$this->scripts[$type][$count++] = $script;
				}
				else
				{
					$this->scripts[$type][$key] = $script;
				}
			}
		}
	}

	/**
	 * Pass assets to template
	 */
	public function set_assets()
	{
		if (isset($this->scripts['js']))
		{
			ksort($this->scripts['js']);
			$this->scripts['js'] = array_filter(array_unique($this->scripts['js']));
		}

		if (isset($this->scripts['css']))
		{
			ksort($this->scripts['css']);
			$this->scripts['css'] = array_filter(array_unique($this->scripts['css']));
		}

		$this->scripts = array_filter($this->scripts);

		foreach ($this->scripts as $type => $scripts)
		{
			foreach ($scripts as $file)
			{
				$this->template->assign_block_vars($type, array('UA_FILE' => trim($file)));
			}
		}

		$this->scripts = array();
	}

	/**
	 * Merge dbal query arrays
	 */
	public function merge_dbal_arrays($sql_ary1, $sql_ary2)
	{
		if (sizeof($sql_ary2))
		{
			$sql_ary1['SELECT'] .= (!empty($sql_ary2['SELECT'])) ? ', ' . $sql_ary2['SELECT'] : '';
			$sql_ary1['FROM'] += (!empty($sql_ary2['FROM'])) ? $sql_ary2['FROM'] : array();
			$sql_ary1['WHERE'] .= (!empty($sql_ary2['WHERE'])) ? ' AND ' . $sql_ary2['WHERE'] : '';

			if (!empty($sql_ary2['LEFT_JOIN']))
			{
				$sql_ary1['LEFT_JOIN'] = (!empty($sql_ary1['LEFT_JOIN'])) ? array_merge($sql_ary1['LEFT_JOIN'], $sql_ary2['LEFT_JOIN']) : $sql_ary2['LEFT_JOIN'];
			}

			if (!empty($sql_ary2['GROUP_BY']))
			{
				$sql_ary1['GROUP_BY'] = $sql_ary2['GROUP_BY'];
			}

			if (!empty($sql_ary2['ORDER_BY']))
			{
				$sql_ary1['ORDER_BY'] = $sql_ary2['ORDER_BY'];
			}
		}

		return $sql_ary1;
	}

	/**
	 * Add a secret token to the form (requires the S_FORM_TOKEN template variable)
	 * @param string  $form_name The name of the form; has to match the name used in check_form_key, otherwise no restrictions apply
	 */
	public function get_form_key($form_name)
	{
		global $phpbb_container;

		$tpl_context = $phpbb_container->get('template_context');

		add_form_key($form_name);

		$rootref = $tpl_context->get_root_ref();
		$s_form_token = $rootref['S_FORM_TOKEN'];

		return $s_form_token;
	}

	public function get_date_range($range)
	{
		$time = $this->user->create_datetime();
		$now = phpbb_gmgetdate($time->getTimestamp() + $time->getOffset());

		switch($range)
		{
			case 'today':
			$start = $this->user->create_datetime()
				->setDate($now['year'], $now['mon'], $now['mday'])
				->setTime(0, 0, 0)
				->getTimestamp();
			$stop = $start + 86399;
			$date = $this->user->format_date($start, 'Y-m-d', true);
			break;

			case 'week':
			$info = getdate($now[0] - (86400 * $now['wday']));
			$start = $this->user->create_datetime()
				->setDate($info['year'], $info['mon'], $info['mday'])
				->setTime(0, 0, 0)
				->getTimestamp();
			$stop = $start + 604799;
			$date = $this->user->format_date($start, 'Y-m-d', true) . '&amp;dsp=week';
			break;

			case 'month':
			$start = $this->user->create_datetime()
				->setDate($now['year'], $now['mon'], 1)
				->setTime(0, 0, 0)
				->getTimestamp();
			$num_days = gmdate('t', $start);
			$stop = $start + (86400 * $num_days) - 1;
			$date = $this->user->format_date($start, 'Y-m', true);
			break;

			case 'year':
			$start = $this->user->create_datetime()
				->setDate($now['year'], 1, 1)
				->setTime(0, 0, 0)
				->getTimestamp();
			$leap_year = gmdate('L', $start);
			$num_days = ($leap_year) ? 366 : 365;
			$stop = $start + (86400 * $num_days) - 1;
			$date = $this->user->format_date($start, 'Y', true);
			break;

			default:
			$start = $stop = 0;
			$date = '';
			break;
		}

		return array(
			'start'	=> $start,
			'stop'	=> $stop,
			'date'	=> $date,
		);
	}
}
