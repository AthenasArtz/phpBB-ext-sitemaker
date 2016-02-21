<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2015 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\sitemaker\tests\services\blocks;

use blitze\sitemaker\services\blocks\action_handler;

class action_handler_test extends \phpbb_test_case
{
	protected $blocks;

	/**
	 * Define the extension to be tested.
	 *
	 * @return string[]
	 */
	protected static function setup_extensions()
	{
		return array('blitze/sitemaker');
	}

	/**
	 * Get the action_handler object
	 *
	 * @return \blitze\sitemaker\services\blocks\action_handler
	 */
	public function get_action_handler()
	{
		$cache = new \phpbb_mock_cache();
		$config = new \phpbb\config\config(array());
		$phpbb_container = new \phpbb_mock_container_builder();
		$request = $this->getMock('\phpbb\request\request_interface');

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$translator = new \phpbb\language\language($lang_loader);

		$template = $this->getMockBuilder('\phpbb\template\template')
			->getMock();

		$block_factory = $this->getMockBuilder('\blitze\sitemaker\services\blocks\factory')
			->disableOriginalConstructor()
			->getMock();

		$groups = $this->getMockBuilder('\blitze\sitemaker\services\groups')
			->disableOriginalConstructor()
			->getMock();

		$mapper = $this->getMockBuilder('\blitze\sitemaker\model\mapper_factory')
			->disableOriginalConstructor()
			->getMock();

		$this->blocks = $this->getMockBuilder('\blitze\sitemaker\services\blocks\blocks')
			->setConstructorArgs(array($cache, $config, $template, $translator, $block_factory, $groups, $mapper))
			->setMethods(array('clear_cache'))
			->getMock();

		return new action_handler($config, $phpbb_container, $request, $translator, $this->blocks, $block_factory, $mapper);
	}

	/**
	 * Data set for test_create_action
	 *
	 * @return array
	 */
	public function create_action_test_data()
	{
		return array(
			array('add_block'),
			array('copy_route'),
			array('edit_block'),
			array('handle_custom_action'),
			array('save_block'),
			array('save_blocks'),
			array('set_default_route'),
			array('set_route_prefs'),
			array('set_startpage'),
			array('update_block'),
		);
	}

	/**
	 * Test create action
	 *
	 * @dataProvider create_action_test_data
	 */
	public function test_create_action($action)
	{
		$handler = $this->get_action_handler();

		$command = $handler->create($action);

		$this->assertInstanceOf('\\blitze\\sitemaker\\services\\blocks\\action\\' . $action, $command);
	}

	public function test_clear_cache()
	{
		$handler = $this->get_action_handler();

		$this->blocks->expects($this->once())
			->method('clear_cache');

		$handler->clear_cache();
	}
}
