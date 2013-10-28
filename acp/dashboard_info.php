<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\primetime\acp;

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package module_install
*/
class dashboard_info
{
	function module()
	{
		return array(
			'filename'	=> '\primetime\primetime\acp\dashboard_module',
			'title'		=> 'ACP_PRIMETIME_DASHBOARD',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'dashboard'		=> array('title' => 'PRIMETIME_DASHBOARD', 'auth' => '', 'cat' => array('ACP_CAT_CMS')),
			),
		);
	}
}