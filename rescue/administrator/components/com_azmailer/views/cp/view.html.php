<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerView;
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;

/**
 * ControlPanel View
 */
class AZMailerViewCp extends AZMailerView {
	/** @var  array */
	protected $cpbuttons;

	/** @var  array */
	protected $cpinfo;


	/**
	 * @param array $config
	 */
	function __construct($config = array()) {
		parent::__construct($config);
	}

	/**
	 * @param null $tpl
	 * @return bool|mixed|void
	 */
	function display($tpl = null) {
		/** @var AZMailerModelCp $model */
		$model = $this->getModel();
		$this->cpbuttons = $model->getCpButtons();
		$this->cpinfo = $model->getCpInfo();
		AZMailerAdminInterfaceHelper::setHeaderTitle(JText::_("COM_AZMAILER_TOOLBARTITLE_CP"), "azmailer");
		return (parent::display($tpl));
	}

}
