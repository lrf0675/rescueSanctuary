<?php
namespace AZMailer\Helpers;
/**
 * @package    AZMailer
 * @subpackage Helpers
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use JFactory;
use JHtml;
use JText;

/**
 * Category Helper Class
 *
 * @author jackisback
 */
class AZMailerCategoryHelper {
	private static $categoryItemsList;

	/**
	 * @param string $jsonArray
	 * @return string
	 */
	public static function getCategoryItemsHumanReadableList($jsonArray) {
		self::checkLoadCategoryItems();
		$answer = "";
		if (count($itemIdArray = json_decode($jsonArray))) {
			$itemNameArray = array();
			foreach ($itemIdArray as $id) {
				foreach (self::$categoryItemsList as $catItem) {
					if ($catItem->id == $id) {
						$itemNameArray[] = $catItem->name;
						break;
					}
				}
			}
			$answer = implode(", ", $itemNameArray);
		};
		return ($answer);
	}

	private static function checkLoadCategoryItems() {
		if (!self::$categoryItemsList) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('a.id, a.name, a.category_id');
			$query->from('#__azmailer_category_item AS a');
			$query->order("a.item_order ASC");
			$db->setQuery($query);
			self::$categoryItemsList = $db->loadObjectList();
		}
	}

	/**
	 * @param integer $cat_id
	 * @return string
	 */
	public static function getCheckboxHtmlForSelectionCategory($cat_id) {
		global $AZMAILER;
		self::checkLoadCategoryItems();
		$cnt = 0;
		$html = '';
		$html .= '<fieldset><legend>' . $AZMAILER->getOption("category_name_" . $cat_id) . '</legend>';
		foreach (self::$categoryItemsList as $catItem) {
			if ($catItem->category_id == $cat_id) {
				$cnt++;
				$html .= '<input type="checkbox" name="nlsc_' . $cat_id . '" value="' . $catItem->id . '" />' . $catItem->name . '<br />';
			}
		}
		if ($cnt == 0) {
			$html .= JText::_('COM_AZMAILER_NEWSLETTER_DESC_NO_CAT_ELEMENTS');
		}
		$html .= '</fieldset>';
		return ($html);
	}

	/**
	 * TODO: Really we could use "self::$categoryItemsList" where we have already loaded all items
	 * @param      $cat_id
	 * @param bool $zeroOption
	 * @return array
	 */
	public static function getSelectOptions_CatItems($cat_id, $zeroOption = false) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id, a.name AS data');
		$query->from('#__azmailer_category_item AS a');
		$query->where('a.category_id = ' . $cat_id);
		$query->order("a.item_order ASC");
		$db->setQuery($query);
		$lst = array();
		if ($zeroOption !== false) {
			$lst[] = JHTML::_('select.option', '0', JText::_($zeroOption), 'id', 'data');
		}
		$lst = array_merge($lst, $db->loadObjectList());
		return ($lst);
	}

	/**
	 * @param int $cat_id
	 * @return mixed
	 */
	public static function getDefaultOptionsArrayForCategory($cat_id) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id');
		$query->from('#__azmailer_category_item AS a');
		$query->where(array('a.category_id = ' . $cat_id, 'a.is_default = 1'));
		$query->order("a.item_order ASC");
		$db->setQuery($query);
		$lst = $db->loadColumn();
		return ($lst);
	}

	//-----------------------------------------------------------------------------------FOR XLS IMPORTER
	/**
	 * @param int $itemId
	 * @return bool
	 */
	public static function getCategoryIDForItem($itemId) {
		$answer = false;
		self::checkLoadCategoryItems();
		foreach (self::$categoryItemsList as $catItem) {
			if ($catItem->id == $itemId) {
				$answer = $catItem->category_id;
				break;
			}
		}
		return ($answer);
	}

	/**
	 * Returns array for category item ids for the [1-5] category
	 * @param int    $cat_id [1-5]
	 * @param string $nameList
	 * @param bool   $registerIfNew
	 * @return array
	 */
	public static function getCategoryIdArrayByNames($cat_id, $nameList, $registerIfNew = false) {
		$answer = array();
		if (!empty($cat_id) && !empty($nameList)) {
			$catNames = explode(",", $nameList);
			if (count($catNames)) {
				foreach ($catNames as &$catName) {
					$catName = strtolower(trim($catName));
				}
				//try to get them all in one go
				$db = \JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('a.id');
				$query->from('#__azmailer_category_item AS a');
				$query->where('a.category_id = ' . $cat_id);
				$query->where('LOWER(a.name) IN ("' . implode('","', $catNames) . '")');
				$query->order("a.id ASC");
				$db->setQuery($query);
				$answer = $db->loadColumn();//loadResultArray is depreciated
				if ((count($catNames) > count($answer)) && $registerIfNew) {
					//we have category items that must be registered so we reset $answer and do it one by one
					$answer = array();
					$order = self::getHighestOrderNumberForCategory($cat_id);
					foreach ($catNames as $catName) {
						$query = $db->getQuery(true);
						$query->select('a.id');
						$query->from('#__azmailer_category_item AS a');
						$query->where('a.category_id = ' . $cat_id);
						$query->where('LOWER(a.name) = ' . $db->quote($catName));
						$db->setQuery($query);
						$cid = $db->loadResult();
						if (!$cid || empty($cid)) {
							$cid = 0;
						}
						if ($cid == 0) {//creating new one
							$data = array();
							$data["id"] = null;
							$data["category_id"] = $cat_id;
							$data["item_order"] = ++$order;
							$data["is_default"] = 0;
							$data["name"] = ucfirst($catName);
							/** @var \JTable $table */
							$table = \JTable::getInstance('azmailer_category_item', 'Table');
							if ($table->bind($data)) {
								if ($table->check()) {
									if ($table->store()) {
										$db = $table->getDBO();
										$cid = $db->insertid();
									}
								}
							}
						}
						if ($cid != 0) {
							array_push($answer, $cid);
						}
					}
				}
			}
		}
		return ($answer);
	}


	//-----------------------------------------------------------------------------------PRIVATE

	/**
	 * @param $cat_id
	 * @return int
	 */
	private static function getHighestOrderNumberForCategory($cat_id) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('SELECT MAX(a.item_order)');
		$query->from('#__azmailer_category_item AS a');
		$query->where('a.category_id = ' . $cat_id);
		$db->setQuery($query);
		$max = $db->loadResult();
		$max = ($max && $max > 0 ? $max : 1);
		return ($max);
	}

}

/*

    function getCategoryItemsHumanReadableList($jsonArray) {
        global $AZNL_CORE;
        if (!$this->categoryItemsList) {
            $db = $AZNL_CORE->get('_jdb');
            $sql = 'SELECT res.id, res.name FROM #__aznl_category_items AS res ORDER BY res.category_id';
            $db->setQuery($sql);
            $this->categoryItemsList = $db->loadObjectList();
        }
        $itemIdArray = json_decode($jsonArray);
        $itemNameArray = array();
        foreach($itemIdArray as $id) {
            foreach($this->categoryItemsList as $catItem) {
                if ($catItem->id == $id) {
                    $itemNameArray[] = $catItem->name;
                    break;
                }
            }
        }
        return(implode(", " ,$itemNameArray));
    }

    function countNLSinCategoryItem($cat_id, $item_id) {
        global $AZNL_CORE;
        $db = $AZNL_CORE->get('_jdb');
        $sql =  'SELECT COUNT(*)'
                .' FROM #__aznl_newsletter_subscribers AS res'
                .' WHERE res.nls_cat_'.$cat_id.' REGEXP "\"' . $item_id . '\""';
        $db->setQuery($sql);
        $answer = $db->loadResult();
        return($answer);
    }

    function getDefaultOptionsArrayForCategory($cat_id) {
        global $AZNL_CORE;
        $db = $AZNL_CORE->get('_jdb');
        $sql = 'SELECT res.id FROM #__aznl_category_items AS res WHERE res.category_id = "'.$cat_id.'" AND res.is_default = 1 ORDER BY res.item_order';
        $db->setQuery($sql);
        $lst = $db->loadResultArray();
        return ($lst);
    }


*/
?>