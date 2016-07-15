<?php
namespace AZMailer\Core;
/**
 * @package    AZMailer
 * @subpackage Core
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.modellist');

//use Joomla\Registry\Registry;

/**
 * Class AZMailerModel
 * @package AZMailer\Core
 */
class AZMailerModel extends \JModelList {
	/** @var string */
	protected $event_before_save;

	/** @var string */
	protected $event_after_save;

	/**
	 * Override of Method to get a AZMailerPagination(JPagination) object for the data set. - REMOVING ALL option
	 *
	 * @return  AZMailerPagination  A JPagination object for the data set.
	 *
	 * @since   11.1
	 */
	public function getPagination() {
		// Get a storage key.
		$store = $this->getStoreId('getPagination');
		// Try to load the data from internal storage.
		if (!isset($this->cache[$store])) {
			// Create the pagination object.
			jimport('joomla.html.pagination');
			$limit = (int)$this->getState('list.limit') - (int)$this->getState('list.links');
			$page = new AZMailerPagination($this->getTotal(), $this->getStart(), $limit);
			// Add the object to the internal cache.
			$this->cache[$store] = $page;
		}
		return $this->cache[$store];
	}

	/**
	 * Returns a specific item ... duh!?
	 * @param integer $id
	 * @return object|bool
	 * @throws \Exception
	 */
	protected function _getSpecificItem($id = null) {
		$answer = false;
		$id = (int)$id;
		$table = $this->getTable();
		if ($table) {
			if ($id > 0) {
				$table->load($id);
				$properties = $table->getProperties(true);
				$answer = \JArrayHelper::toObject($properties, 'JObject');
				if (property_exists($answer, 'params')) {
					$registryClassName = '\JRegistry';
					/** @var \JRegistry|\Joomla\Registry\Registry $registry */
					$registry = new $registryClassName;
					$registry->loadString($answer->params);
					$answer->params = $registry->toArray();
				}
			}
		}
		return ($answer);
	}

	/**
	 * @param array $data
	 * @return bool
	 * @throws \Exception
	 */
	protected function _saveSpecificItem($data) {
		if (IS_J3) {
			$dispatcher = \JEventDispatcher::getInstance();//J!3
		} else {
			$dispatcher = \JDispatcher::getInstance();//J!25
		}
		$table = $this->getTable();
		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int)$this->getState($this->getName() . '.id');
		$isNew = true;

		// Include the content plugins for the on save events.
		\JPluginHelper::importPlugin('content');

		// Allow an exception to be thrown.
		try {
			// Load the row if saving an existing record.
			if ($pk > 0) {
				$table->load($pk);
				$isNew = false;
			}

			// Bind the data.
			if (!$table->bind($data)) {
				\JFactory::getApplication()->enqueueMessage("Bind error on table " . $table->name);
				return false;
			}

			// Check the data.
			if (!$table->check()) {
				\JFactory::getApplication()->enqueueMessage("Check error on table " . $table->name);
				return false;
			}

			// Trigger the onContentBeforeSave event.
			$result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $isNew));

			// Store the data.
			if (!$table->store()) {
				\JFactory::getApplication()->enqueueMessage("Store error on table " . $table->name);
				return false;
			}

			// Clean the cache.
			$this->cleanCache();

			// Trigger the onContentAfterSave event.
			$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $isNew));
		} catch (\Exception $e) {
			\JFactory::getApplication()->enqueueMessage($e->getMessage());

			return false;
		}

		$pkName = $table->getKeyName();

		if (isset($table->$pkName)) {
			$this->setState($this->getName() . '.id', $table->$pkName);
		}
		$this->setState($this->getName() . '.new', $isNew);

		return true;
	}

}

?>
