<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2015 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\sitemaker\tests\event;

use phpbb\event\data;
use Symfony\Component\EventDispatcher\EventDispatcher;

class init_sitemaker_test extends listener_base
{
	/**
	 * @return null
	 */
	public function init_sitemaker_test_data()
	{
		return array(
			array(
				array(),
				array(
					array(
						'ext_name' => 'blitze/sitemaker',
						'lang_set' => 'common',
					),
				),
			),
			array(
				array(
					array(
						'ext_name' => 'phpbb/pages',
						'lang_set' => 'pages_common',
					),
				),
				array(
					array(
						'ext_name' => 'phpbb/pages',
						'lang_set' => 'pages_common',
					),
					array(
						'ext_name' => 'blitze/sitemaker',
						'lang_set' => 'common',
					),
				),
			),
		);
	}

	/**
	 * @dataProvider init_sitemaker_test_data
	 *
	 * @param array $lang_set_ext
	 * @param array $expected_contains
	 */
	public function test_init_sitemaker(array $lang_set_ext, array $expected_contains)
	{
		$listener = $this->get_listener();

		$dispatcher = new EventDispatcher();
		$dispatcher->addListener('core.user_setup', array($listener, 'init_sitemaker'));

		$event_data = array('lang_set_ext');
		$event = new data(compact($event_data));
		$dispatcher->dispatch('core.user_setup', $event);

		extract($event->get_data_filtered($event_data));

		foreach ($expected_contains as $expected)
		{
			$this->assertContains($expected, $lang_set_ext);
		}
	}
}
