<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2015 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\sitemaker\tests\blocks;

use blitze\sitemaker\blocks\birthday;

class birthday_test extends blocks_base
{
	protected $ptemplate;

	/**
	 * Load required fixtures.
	 *
	 * @return mixed
	 */
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/birthday.xml');
	}

	/**
	 * Create the birthday block
	 * @param string $time
	 * @param integer $call_count
	 * @return \blitze\sitemaker\blocks\birthday
	 */
	protected function get_block($time = 'now', $call_count = 0)
	{
		global $auth, $cache, $db, $phpbb_dispatcher, $user;

		$auth = $this->getMock('\phpbb\auth\auth');
		$cache = new \phpbb_mock_cache();
		$db = $this->new_dbal();

		$template = $this->getMockBuilder('\phpbb\template\template')
			->getMock();

		$template->expects($this->exactly($call_count))
			->method('assign_var')
			->with(
				$this->equalTo('S_DISPLAY_BIRTHDAY_LIST'),
				$this->equalTo(false)
			);

		$user = new \phpbb\user('\phpbb\datetime');
		$user->timezone = new \DateTimeZone('UTC');
		$user->lang['datetime'] =  array();

		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

		$block = new birthday($cache, $db, $template, $user, $time);
		$block->set_template($this->ptemplate);

		return $block;
	}

	public function test_block_config()
	{
		$block = $this->get_block();
		$config = $block->get_config(array());

		$this->assertEquals(array(), $config);
	}

	/**
	 * Data set for test_block_display
	 *
	 * @return array
	 */
	public function block_test_data()
	{
		return array(
			array(
				'10 November 2015',
				'',
			),
			array(
				'7 October 2015',
				array(
					'birthday' => array(
						array(
							'USERNAME' => '<span class="username">admin</span>',
							'USER_AGE' => 20,
						),
						array(
							'USERNAME' => '<span class="username">demo1</span>',
							'USER_AGE' => '',
						),
					),
				),
			),
			array(
				'28 February 2015',
				array(
					'birthday' => array(
						array(
							'USERNAME' => '<span class="username">demo3</span>',
							'USER_AGE' => '',
						),
					),
				),
			),
		);
	}

	/**
	 * Test block display
	 *
	 * @dataProvider block_test_data
	 * @param string $time
	 * @param mixed $expected
	 */
	public function test_block_display($time, $expected)
	{
		$block = $this->get_block($time, 1);
		$result = $block->display(array());

		$this->assertEquals($expected, $result['content']);
	}
}
