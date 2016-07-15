<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerException;
use AZMailer\Core\AZMailerView;
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;
use AZMailer\Helpers\AZMailerEditorHelper;

/**
 * Class AZMailerViewEditor
 */
class AZMailerViewEditor extends AZMailerView {
	/**
	 * @var object $params - the decoded parameters
	 */
	protected $params;

	/**
	 * @return mixed|void
	 * @throws AZMailerException
	 */
	public function edit() {
		/** @var AZMailerModelEditor $model */
		$model = $this->getModel();

		$JI = JFactory::getApplication()->input;
		$P = $JI->getString("params");

		/*(title, parent_type, parent_id, return_uri[base64encoded])*/
		$this->params = json_decode(base64_decode($P));

		if (gettype($this->params) != "object") {
			throw new AZMailerException("EDITOR PARAMETERS ERROR", 500);
		}

		$this->params->id = AZMailerEditorHelper::getBlobIdByParent($this->params->parent_type, $this->params->parent_id);
		$this->item = $model->getSpecificItem($this->params->id);
		$this->state = $this->get('State');

		AZMailerAdminInterfaceHelper::setHeaderTitle(JText::_("COM_AZMAILER_TOOLBARTITLE_EDITOR"), "editor");
		AZMailerAdminInterfaceHelper::addButtonsToToolBar(array(
			array("core.create", "editor.apply", 'apply', 'JTOOLBAR_APPLY', false), /*save&stay*/
			array("core.create", "editor.save", 'save', 'JTOOLBAR_SAVE', false), /*save&close*/
			array("core.manage", "editor.cancel", 'cancel', 'JTOOLBAR_CANCEL', false), /*cancel*/
		));
		$JI->set("hidemainmenu", 1);
		return (parent::display("edit"));
	}


	/**
	 * Launch the quick-edit interface
	 */
	public function quickEdit() {
		parent::display("quickedit");
	}

	/**
	 * Alias for save
	 */
	public function apply() {
		$this->save(true);
	}

	/**
	 * @param bool $isApply - save&stay
	 */
	public function save($isApply = false) {
		/** @var \AZMailer\AZMailerCore $AZMAILER */
		global $AZMAILER;
		/** @var AZMailerModelEditor $model */
		$model = $this->getModel();
		$JI = \JFactory::getApplication()->input;
		$data = $JI->getArray($_POST);
		//
		$filter = \JFilterInput::getInstance( array(), array(), 1, 1, 0 );
		$JI = new \JInput( null, array('filter' => $filter) );
		$unfilteredPostData = $JI->getArray($_POST);
		//
		$data["htmlblob"] = $unfilteredPostData["htmlblob"];
		$model->saveSpecificItem($data);
		if (!$isApply) {
			$this->cancel();
		} else {
			$ELP = new stdClass();
			$ELP->title = $data["title"];
			$ELP->parent_type = $data["parent_type"];
			$ELP->parent_id = $data["parent_id"];
			$ELP->return_uri = $data["return_uri"];
			$EDITOR_LINK = AZMailerEditorHelper::getEditorLink($ELP, true);
			$AZMAILER->getController()->setRedirect(JRoute::_($EDITOR_LINK, false));
		}
	}

	/**
	 * Cancel edit and go back to where we came from
	 */
	public function cancel() {
		/** @var \AZMailer\AZMailerCore $AZMAILER */
		global $AZMAILER;
		$redirectUrl = base64_decode(\JFactory::getApplication()->input->getString('return_uri', ""));
		$AZMAILER->getController()->setRedirect($redirectUrl);
	}

	/**
	 * Show Elfinder
	 */
	public function elfinder() {
		$this->state = $this->get('State');
		parent::display("elfinder");
	}

	/**
	 * Elfinder Ajax connection
	 * @throws Exception
	 */
	public function elfinder_conn() {
		/** @var \AZMailer\AZMailerCore $AZMAILER */
		global $AZMAILER;
		$com_path = $AZMAILER->getOption("com_path_admin");
		$efcp = $com_path . DS . 'assets' . DS . 'js' . DS . 'elfinder' . DS . 'php';
		require_once($efcp . DS . 'elFinderConnector.class.php');
		require_once($efcp . DS . 'elFinder.class.php');
		require_once($efcp . DS . 'elFinderVolumeDriver.class.php');
		require_once($efcp . DS . 'elFinderVolumeLocalFileSystem.class.php');

		$rootPath = JPATH_SITE . DS . 'images/';

		$JURI = \JUri::getInstance();
		$uriHost = ($JURI->isSSL() ? "https://" : "http://") . $JURI->getHost();
		$uriSiteBase = str_replace("administrator/", "", str_replace($uriHost, '', $JURI->base()));
		$rootUriImg = 'http://' . $JURI->getHost() . $uriSiteBase . "images/";
		//$rootURI = 'http://' . $_SERVER['SERVER_NAME'] . str_replace(JPATH_ROOT,JPATH_SITE,"") . '/images/';

		$opts = array(
			'debug' => false,
			'roots' => array(
				array(
					'driver' => 'LocalFileSystem', // driver for accessing file system (REQUIRED)
					'path' => $rootPath,
					'URL' => $rootUriImg
				)
			)
		);
		// run elFinder
		ob_clean();
		$connector = new elFinderConnector(new elFinder($opts));
		$connector->run();

		$app = \JFactory::getApplication();
		$app->close();
	}


}
