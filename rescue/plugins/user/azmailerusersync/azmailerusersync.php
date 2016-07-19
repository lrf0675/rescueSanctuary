<?php
/**
 * @package    AZMailerUserSync
 * @subpackage Base
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 17-May-2013
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('=;)');
jimport('joomla.plugin.plugin');
use \AZMailer\Entities\AZMailerSubscriber;
use \AZMailer\Helpers\AZMailerSubscriberHelper;
/**
 * User Plugin.
 *
 * @package    AZMailerUserSync
 * @subpackage Plugin
 */
class plgUserAZMailerUserSync extends JPlugin {
	private $originalUserData;
    /**
     * Method is called before user data is stored in the database.
     *
     * @param array  $user Holds the original user data before it was changed)
     * @param boolean  $isnew True if a new user is stored.
     * @param array  $new Holds the new user data.
     *
     * @return void
     * @since 1.6
     * @throws Exception on error.
     */
    public function onUserBeforeSave($user, $isnew, $new) {
	    $this->originalUserData = $user;
    }

    /**
     * Method is called after user data is stored in the database.
     *
     * @param array  $user  Holds the new user data.
     * @param boolean  $isnew  True if a new user is stored.
     * @param boolean  $success True if user was succesfully stored in the database.
     * @param string  $msg  Message.
     *
     * @return void
     * @since 1.6
     * @throws Exception on error.
     */
    public function onUserAfterSave($user, $isnew, $success, $msg) {
	    if($success!==true) return;
	    require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_azmailer".DS."includes".DS."defines.php");
	    require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_azmailer".DS."includes".DS."loader.php");
	    global $AZMAILER;
	    if(!$AZMAILER){$AZMAILER = new \AZMailer\AZMailerCore();}
	    //
	    $subscriber = array();
	    if (!AZMailerSubscriberHelper::checkIfEmailSyntaxIsValid($user["email"])) {
		    return;
	    }
	    //If we are modifying a user we will look for the subscriber by the mail before it changed
	    //and so we set subscriber id and values will be updated that subscriber (instead of creating new one)
	    //If no original user there is still a chance that subscriber already exists so we check on new mail
	    $mailToCheck = isset($this->originalUserData["email"])?$this->originalUserData["email"]:$user["email"];
	    if(isset($mailToCheck)) {
		    $NLS = AZMailerSubscriberHelper::getNewsletterSubscriberByMail($mailToCheck);
		    if ($NLS) {
			    $subscriber["id"] = $NLS->id;
		    }
	    }

	    $subscriber["nls_email"] = $user["email"];
	    //split name in firstname and lastname
	    $nameParts = explode(" ", $user["name"]);
	    $firstname = $user["name"];
	    $lastname = "";
	    if(is_array($nameParts) && count($nameParts)) {
		    $firstname = implode("", array_splice($nameParts, 0, 1));
		    $lastname = implode(" ", $nameParts);
	    }
	    $subscriber["nls_firstname"] = $firstname;
	    $subscriber["nls_lastname"] = $lastname;
		$subscriber = AZMailerSubscriberHelper::checkAndBeautifyNLSData($subscriber);
		if($subscriber === false) {
			return;
		}
	    //
	    $NLS = new AZMailerSubscriber($subscriber);
		$NLS->sync();
    }


	/**
	 * Once the $user array passed to the onUserAfterDelete method held ONLY the id (in J!3 it's not like this)
	 * but to be on the safe side we store the user before deletion
	 * @param array $user Holds the user data.
	 */
	public function onUserBeforeDelete($user) {
		$this->originalUserData = $user;
	}
    /**
     * option: remove_subscriber_on_userdelete
     * Method is called after user data is deleted from the database.
     *
     * @param array  $user Holds the user data.
     * @param boolean  $success True if user was succesfully deleted from the database.
     * @param string  $msg Message.
     *
     * @return void
     * @since 1.6
     */
    public function onUserAfterDelete($user, $success, $msg) {
	    if($success!==true) return;
	    require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_azmailer".DS."includes".DS."defines.php");
	    require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_azmailer".DS."includes".DS."loader.php");
	    global $AZMAILER;
	    if(!$AZMAILER){$AZMAILER = new \AZMailer\AZMailerCore();}
	    if($AZMAILER->getOption("remove_subscriber_on_userdelete") == 1) {
		    if(isset($this->originalUserData["email"])) {
			    $NLS = AZMailerSubscriberHelper::getNewsletterSubscriberByMail($this->originalUserData["email"]);
			    if ($NLS && isset($NLS->id)) {
				    /** @var JTable $table */
				    $table = \JTable::getInstance('azmailer_subscriber', 'Table');
				    $table->delete($NLS->id);
			    }
		    }
	    }
    }
}
