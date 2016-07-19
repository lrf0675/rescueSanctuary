<?php
/**
 * Created by Adam Jakab.
 * Date: 8/6/13
 * Time: 10:55 AM
 */


/**
 * Class AZMailerJMailExtension extends the modified JMail class in /libraries/joomla/mail/mail.php
 *
 * Missing:
 *  attachments (is it necessary?)
 *
 * Mail language constants:
 *
 * Administrator registering user from back-end (/administrator/language/[LANG]/[LANG].plg_user_joomla.ini)
 * PLG_USER_JOOMLA_NEW_USER_EMAIL_BODY
 * PLG_USER_JOOMLA_NEW_USER_EMAIL_SUBJECT
 *
 */
class AZMailerJMailExtension extends JMailOriginal {

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function Send() {
		$JAPP = \JFactory::getApplication();

		/* we can do this because we will be sending single mails anyways */
		$mergedRecipients = array_merge($this->to, $this->cc, $this->bcc);
		$recipients = array();
		if(count($mergedRecipients)) {
			foreach($mergedRecipients as &$toArr) {
				if(isset($toArr[0])) {
					array_push($recipients, $toArr[0]);
				}
			}
		}

		//substitution array
		$MQSUBST = array();
		$MQSUBST["MAILBODY"] = nl2br($this->Body);


		//find and surround urls with a tag - this is quite weak
		if( ($this->ContentType = 'text/plain') ) {
			$MQSUBST["MAILBODY"] = preg_replace('@(?:[^"])(https?://?([-\w]+\.[-\w\.]+)+\w(:\d+)?(/([-\w/_\.]*(\?\S+)?)?)*)@i', ' <a href="$1">$1</a>', $MQSUBST["MAILBODY"]);
		}

		//common data
		$plgData = new \stdClass();
		$plgData->mq_type =         "Joomla";
		$plgData->mq_typeid =       7;//we will not send newsletter but a template
		//todo: make this an option (0===send it right away | 1===queue it with priority 1(this could cause unwanted delay on account activation mail))
		$plgData->mq_priority =     1;
		$plgData->mq_from =         '';//default AZmailer mail will be used
		$plgData->mq_from_name =    '';//default AZmailer mail name will be used
		$plgData->mq_subject =      $this->Subject;

		$answer = true;// ;-) very optimistic
		//loop recipients
		foreach($recipients as $recipient) {
			$plgData->mq_to = $recipient;
			$MQSUBST["FULLNAME"] = $this->getUserFullNameByEmail($recipient);
			$plgData->mq_substitutions = json_encode($MQSUBST);
			$plgResp = $JAPP->triggerEvent("AZMSYSPLG_queueMail", array($plgData));
			$plgResp = $plgResp[0];

			if($plgResp!==true) {
				if(JFactory::getApplication()->isAdmin()){
					$JAPP->enqueueMessage("AZMailer - Failed to send to recipient($plgResp): " . $recipient);
				}
				$answer = false;
			} else {
				if(JFactory::getApplication()->isAdmin()){
					$JAPP->enqueueMessage("AZMailer - Mail sent to recipient: " . $recipient);
				}
			}
		}
		return($answer);
	}

	/**
	 * @param string $mail
	 * @return mixed|string
	 */
	private function getUserFullNameByEmail($mail) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.name');
		$query->from('#__users AS a');
		$query->where('a.email = ' . $db->quote($db->escape(strtolower(trim($mail)), true), false));
		$db->setQuery($query);
		$answer = $db->loadResult();
		if(!$answer) {$answer="";}
		return($answer);
	}
}