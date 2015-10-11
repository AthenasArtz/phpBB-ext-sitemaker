<?php
/**
 *
 * @package sitemaker
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace blitze\sitemaker\model\menu\mapper;

use blitze\sitemaker\model\base_mapper;
use blitze\sitemaker\model\menu\entity\item;

class menus extends base_mapper
{
	/** @var \blitze\sitemaker\model\menu\mapper\items */
	protected $items_mapper;

	/** @var string */
	protected $_entity_class = 'blitze\sitemaker\model\menu\entity\menu';

	/** @var string */
	protected $_entity_pkey = 'menu_id';

	public function load(array $condition = array())
	{
		$entity = parent::load($condition);

		if ($entity)
		{
			$this->items_mapper = $this->mapper_factory->create('menu', 'items');
			$collection = $this->items_mapper->find(array(
				'%smenu_id'	=> $entity->get_menu_id(),
			));
			$entity->set_items($collection);
		}

		return $entity;
	}

	public function delete($condition)
	{
		parent::delete($condition);

		// delete menu items associated with this menu
		if ($condition instanceof $this->_entity_class)
		{
			$this->items_mapper->delete(array(
				'menu_id'	=> $condition->get_menu_id(),
			));
		}
	}
}