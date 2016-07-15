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
use AZMailer\Entities\AZMailerNewsletter;
use AZMailer\Entities\AZMailerSubscriber;

/**
 * Newsletter Helper Class
 *
 * @author jackisback
 */
class AZMailerNewsletterHelper {

	/**
	 * @param integer $tplid
	 * @return integer
	 */
	public static function countNewslettersWithTemplateId($tplid) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__azmailer_newsletter AS a');
		$query->where('a.nl_template_id = ' . $db->quote($tplid, true));
		$db->setQuery($query);
		$cnt = (int)$db->loadResult();
		return ($cnt);
	}

	/**
	 * @param integer $tpl_id
	 * @param string $tpl_subst - this is a base64 encoded json encoded stdClass object
	 * @return mixed
	 */
	public static function getNewsletterSimpleTextVersion($tpl_id = 0, $tpl_subst = null) {
		$txtver = strip_tags(self::getNewsletterSubstitutedContentFromTemplate($tpl_id, $tpl_subst));
		$txtver = preg_replace("/(\r\n|\n|\r){2,}/m", "\n", $txtver);//remove double++ line breaks
		return ($txtver);
	}

	/**
	 * @param integer $tpl_id
	 * @param string $tpl_subst - this is a base64 encoded json encoded stdClass object
	 * @return string
	 */
	public static function getNewsletterSubstitutedContentFromTemplate($tpl_id = 0, $tpl_subst = null) {
		/** @var \AZMailer\AZMailerCore $AZMAILER */
		global $AZMAILER;
		if (!empty($tpl_id)) {
			//$tpl = AZMailerTemplateHelper::getTemplateById($tpl_id);
			$blob = AZMailerBlobHelper::getBlob("template", $tpl_id);
			$html = (!is_null($blob) ? $blob->htmlblob : '<p></p>');
			$html = preg_replace('/src="images\//i', 'src="/images/', $html);//fix images
			//
			$SUBSTITUTIONS = json_decode(base64_decode($tpl_subst));
			if (!is_object($SUBSTITUTIONS)) {
				$SUBSTITUTIONS = new \stdClass();
			}
			//
			$doc = new \DOMDocument();
			$doc->preserveWhiteSpace = false;
			@$doc->loadHTML($html);
			$xpath = new \DOMXPath($doc);
			//
			$deployFolder = $AZMAILER->getOption('j_deploy_folder');
			//
			foreach ($SUBSTITUTIONS as $SK => $SV) {
				/** @var \DOMElement $xEl */
				$xEl = $xpath->query('//*[@id="' . $SK . '"]')->item(0);
				if ($xEl) {
					//if (substr($SK,0,5) == 'image') {
					if ($xEl->tagName == "img") {
						//When Joomla is installed in subfolder we need to prefix image path with it
						$imgSrc = $deployFolder . (substr($SV, 0, 1) != "/" ? "/" : "") . $SV;
						$xEl->setAttribute("src", $imgSrc);
						//adding real image width and height to img tag
						if (file_exists(JPATH_SITE . $SV)) {
							$imgFile = JPATH_SITE . $SV;
							$img = imagecreatefromjpeg($imgFile);
							$xEl->setAttribute("width", imagesx($img));
							$xEl->setAttribute("height", imagesy($img));
						}
					} else {
						$xEl->nodeValue = $SV;
					}
				}
			}
			$html = $doc->saveHTML();

			//removing doctype + html + body tags (resolves K2 bug and they are not needed anyways)
			$html = preg_replace(array(
				'/<!DOCTYPE.+?>/i',
				'/<\/?html>/i',
				'/<\/?body>/i'
			), '', $html);

			//$html = html_entity_decode($html);//this breaks with èòù...
			$html = str_replace('&lt;', '<', $html);
			$html = str_replace('&gt;', '>', $html);

			$html = trim($html);

		} else {
			$html = '';
		}
		return ($html);
	}

	/**
	 * @param string $nl_sendto_selections - a base64 + json encoded stdClass
	 * @param $nl_sendto_additional - a base64 + json encoded stdClass
	 * @return array
	 */
	public static function getNewsletterSendtoData($nl_sendto_selections, $nl_sendto_additional) {
		global $AZMAILER;
		$answer = array();
		$answer["COUNT"] = 0;
		$answer["CATSELCONTACTS"] = array();//we will send back the list of registered&selected newsletter contacts array
		$answer["XLSCONTACTS"] = array();//we will send back the list of XLS CONTACTS ARRAY
		//
		$NL_CATSEL = json_decode(base64_decode($nl_sendto_selections));///
		if (!is_object($NL_CATSEL)) {
			$NL_CATSEL = new \stdClass();
		}
		//
		$NL_ADDITIONAL = json_decode(base64_decode($nl_sendto_additional));
		if (!is_object($NL_ADDITIONAL)) {
			$NL_ADDITIONAL = new \stdClass();
		}
		if (!isset($NL_ADDITIONAL->subscribers) || !is_array($NL_ADDITIONAL->subscribers)) {
			$NL_ADDITIONAL->subscribers = array();
		}

		//OK FIRST LET'S SEE IF WE HAVE ADDITIONAL XLS UPLOADED CONTACTS
		if (isset($NL_ADDITIONAL->subscribers) && is_array($NL_ADDITIONAL->subscribers)) {
			$answer["COUNT"] += count($NL_ADDITIONAL->subscribers);
			$answer["XLSCONTACTS"] = $NL_ADDITIONAL->subscribers;
		}

		//list of contacts selected by category selection
		$NLS_SELECTION_OBJECT = self::getNLSubscribersForCategorySelections($NL_CATSEL);//returns ->list and ->sql
		$answer["COUNT"] += count($NLS_SELECTION_OBJECT->list);
		$answer["CATSELQUERY"] = $NLS_SELECTION_OBJECT->sql;

		//
		$maxContacts = $AZMAILER->getOption("nl_show_max_contacts");
		$answer["CATSELCONTACTS"] = array();
		if (count($NLS_SELECTION_OBJECT->list) > $maxContacts) {

			$fakeLine = new \stdClass();
			$fakeLine->nls_email = '';
			$fakeLine->nls_lastname = '';
			$fakeLine->nls_firstname = '<b>' . \JText::sprintf('COM_AZMAILER_NEWSLETTER_SENDTO_LIMITED', count($NLS_SELECTION_OBJECT->list)) . ' Showing only the first ' . $maxContacts . '</b>';
			$answer["CATSELCONTACTS"][] = $fakeLine;
		}
		//
		if (count($NLS_SELECTION_OBJECT->list) <= $maxContacts) {
			$answer["CATSELCONTACTS"] = $NLS_SELECTION_OBJECT->list;
		} else {
			$contactCount = 0;
			foreach ($NLS_SELECTION_OBJECT->list as &$contactObject) {
				$contactCount++;
				if ($contactCount > $maxContacts) {
					break;
				}
				array_push($answer["CATSELCONTACTS"], $contactObject);
			}
		}


		$answer["CATSELCONTACTS"] = base64_encode(json_encode($answer["CATSELCONTACTS"]));
		$answer["CATSELQUERY"] = base64_encode(json_encode($answer["CATSELQUERY"]));
		$answer["XLSCONTACTS"] = base64_encode(json_encode($answer["XLSCONTACTS"]));

		return ($answer);
	}

	/**
	 * TODO: with new query object the whole WHERE stuff would be much clearer
	 * $NL_CATSEL -> json_decode(base64_decode(nl_sendto_selections))-ed field object
	 * $NL_CATSEL -> FORMAT: {selectionBehaviour:"PLUSOR", cat1:[],cat2:[],cat3:[],cat4:[],cat5:[], country:0, region:0, province:0}
	 *
	 * @param $NL_CATSEL -
	 * @return \stdClass
	 */
	public static function getNLSubscribersForCategorySelections($NL_CATSEL) {
		//WHERE - GENERAL CONDITIONS
		$whereGen = array();
		$whereGen[] = 'res.nls_blacklisted = 0';//SELECT ONLY NON-BLACKLISTED SUBSCRIBERS
		$whereGenSql = count($whereGen) ? ' ' . implode(' AND ', $whereGen) : '';


		//WHERE - GEO LOCATIONS
		$whereLoc = array();
		if (!isset($NL_CATSEL->country)) {
			$NL_CATSEL->country = 0;
		}
		if (!isset($NL_CATSEL->region)) {
			$NL_CATSEL->region = 0;
		}
		if (!isset($NL_CATSEL->province)) {
			$NL_CATSEL->province = 0;
		}
		//
		if ($NL_CATSEL->country) {
			$whereLoc[] = 'res.nls_country_id = ' . $NL_CATSEL->country;
		}
		if ($NL_CATSEL->region) {
			$whereLoc[] = 'res.nls_region_id = ' . $NL_CATSEL->region;
		}
		if ($NL_CATSEL->province) {
			$whereLoc[] = 'res.nls_province_id = ' . $NL_CATSEL->province;
		}
		//
		$whereLocSql = count($whereLoc) ? ' ' . implode(' AND ', $whereLoc) : '';


		//WHERE - CATEGORIES
		$whereCat = array();
		$selectionBehaviour = (isset($NL_CATSEL->selectionBehaviour) && in_array($NL_CATSEL->selectionBehaviour, array("PLUSOR", "PLUSAND", "MINUSAND")) ? $NL_CATSEL->selectionBehaviour : "PLUSAND");
		for ($cn = 1; $cn <= 5; $cn++) {
			$catname = "cat" . $cn;
			if (isset($NL_CATSEL->$catname)) {
				$CNA = &$NL_CATSEL->$catname;
				if (count($CNA) > 0) {
					foreach ($CNA as &$CNAV) {
						if (in_array($selectionBehaviour, array("PLUSOR", "PLUSAND"))) {//POSITIVE
							$whereCat[] = 'res.nls_cat_' . $cn . ' REGEXP "\"' . $CNAV . '\""';
						} else if (in_array($selectionBehaviour, array("MINUSAND"))) {//NEGATIVE
							$whereCat[] = 'res.nls_cat_' . $cn . ' NOT REGEXP "\"' . $CNAV . '\""';
						}
					}
				}
			}
		}
		//
		$whereCatSql = '';
		if ($selectionBehaviour == "PLUSOR") {//ONLY THOSE WHO MATCH ANY CATS
			if (count($whereCat) == 0) {
				$whereCatSql = '';//no selection no record
			} else {
				$whereCatSql = count($whereCat) ? implode(' OR ', $whereCat) : '';
			}
		} else if ($selectionBehaviour == "PLUSAND") {//ONLY THOSE WHO MATCH ALL CATS
			if (count($whereCat) == 0) {
				$whereCatSql = '';//no selection no record
			} else {
				$whereCatSql = count($whereCat) ? implode(' AND ', $whereCat) : '';
			}
		} else if ($selectionBehaviour == "MINUSAND") {//ALL THAT NOT MATCH ALL CATS
			$whereCatSql = count($whereCat) ? implode(' AND ', $whereCat) : '';
		}


		//IF NO SELECTION HAVE BEEN MADE ON $whereLoc OR $whereCat THEN WE DO NOT WANT ANY RESULTS
		$where = '';
		if ((count($whereLoc) + count($whereCat)) > 0) {
			//JOIN WHERE SQL STATEMENTS ;)
			if ((count($whereGen) + count($whereLoc) + count($whereCat)) > 0) {
				$joinWord_1_open = ((count($whereGen) && (count($whereLoc) || count($whereCat))) ? ' AND (' : ' ');
				$joinWord_1_close = ((count($whereGen) && (count($whereLoc) || count($whereCat))) ? ')' : '');
				$joinWord_2_open = ((count($whereLoc) && count($whereCat)) ? ' AND (' : ' ');
				$joinWord_2_close = ((count($whereLoc) && count($whereCat)) ? ')' : '');
				$where = $whereGenSql . $joinWord_1_open . $whereLocSql . $joinWord_2_open . $whereCatSql . $joinWord_2_close . $joinWord_1_close;
			}
		}


		//WE ONLY QUERY IF WE HAVE WHERE FILTER
		//old: $sql = 'SELECT res.nls_email, res.nls_lastname, res.nls_firstname FROM #__aznl_newsletter_subscribers as res'.$where.' ORDER BY res.nls_lastname';
		$lst = array();
		if (!empty($where)) {
			$db = \JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('res.nls_email, res.nls_lastname, res.nls_firstname');
			$query->from('#__azmailer_subscriber AS res');
			$query->where($where);
			$query->order('res.nls_lastname');
			$db->setQuery($query);
			$lst = $db->loadObjectList();
			$sql = "" . $db->getQuery();//using __toString() magic method ;)
		} else {
			$sql = 'No subscribers have been selected because you have not made selections on the left!';
		}
		$answer = new \stdClass();
		$answer->list = $lst;
		$answer->sql = $sql;
		return ($answer);
	}

	/**
	 * @param $file
	 * @return mixed
	 */
	public static function changeNewsletterEditableImage($file) {
		/** @var \AZMailer\AZMailerCore $AZMAILER */
		global $AZMAILER;
		jimport('joomla.filesystem.file');
		$NLIMGCACHEBASE = $AZMAILER->getOption("newsletter_cache_image_base");// = /images/newsletter/cache
		//
		$attribs = json_decode(base64_decode($file->elementattribs));
		//
		if (is_object($attribs) && isset($attribs->width)) {
			if (!is_null($file->uploadedfile) && $file->uploadedfile["error"] == 0) {
				$file->TMPFOLDER = JPATH_SITE . DS . 'tmp';
				$file->IMAGEFOLDER = JPATH_SITE . DS . $NLIMGCACHEBASE;
				$file->EXTENSION = strtolower(\JFile::getExt($file->uploadedfile["name"]));
				if ($file->EXTENSION == 'jpg' || $file->EXTENSION == 'jpeg') {
					$file->EXTENSION = 'jpg';
					$file->FILENAME = md5('image_for_newsletter_' . rand(10, 100000000) . '_' . date('U')) . '.' . $file->EXTENSION;
					$tmpfilepath = $file->TMPFOLDER . DS . $file->FILENAME;
					$dstfilepath = $file->IMAGEFOLDER . DS . $file->FILENAME;
					if (\JFile::upload($file->uploadedfile["tmp_name"], $tmpfilepath)) {
						//RESIZE IMAGE
						$tmp_image = imagecreatefromjpeg($tmpfilepath);
						$original_width = imagesx($tmp_image);
						$original_height = imagesy($tmp_image);
						$original_ratio = ($original_width / $original_height);
						$target_width = $attribs->width;
						if ($original_width >= $target_width) {
							$new_width = ceil($target_width);
							$new_height = ceil($new_width / $original_ratio);
							$target_width = $new_width;
							$target_height = $new_height;
							$dst_x = 0;
							$dst_y = 0;
							$new_image = imagecreatetruecolor($target_width, $target_height);
							imagecopyresampled($new_image, $tmp_image, $dst_x, $dst_y, 0, 0, $new_width, $new_height, $original_width, $original_height);
							if (imagejpeg($new_image, $tmpfilepath, 90)) {
								//ok resized file has been saved over $tmpfilepath
								if (\JFile::move($tmpfilepath, $dstfilepath)) {
									//ok - the file was moved to $dstfilepath
									$file->NEWFILEURI = '/' . $NLIMGCACHEBASE . '/' . $file->FILENAME;
									//ok now let's get rid of the old file
									if (strpos($file->elcurrsrc, $NLIMGCACHEBASE . '/') !== false) {
										$oldfilename = str_replace($AZMAILER->getOption('j_deploy_folder') . "/" . $NLIMGCACHEBASE . '/', '', $file->elcurrsrc);
										$oldfilepath = $file->IMAGEFOLDER . DS . $oldfilename;
										\JFile::delete($oldfilepath);
										$file->removedOld = $oldfilepath;
									}
								} else {
									$file->errors[] = \JText::_('COM_AZMAILER_NEWSLETTER_UPLOAD_ERR_MOVE');//"Impossibile spostare il file.";
								}
							} else {
								$file->errors[] = \JText::_('COM_AZMAILER_NEWSLETTER_FILEUPLOAD_ERR_REDIM');//"Impossibile ridimensionare il file.";
							}
						} else {
							$file->errors[] = \JText::sprintf('COM_AZMAILER_NEWSLETTER_FILEUPLOAD_ERR_DIM_W', $original_width, $target_width);//"Le dimensioni del file caricato sono inferiori delle dimensioni richieste (Larghezza min: ".$target_width."px).";
						}
					} else {
						$file->errors[] = \JText::_('COM_AZMAILER_NEWSLETTER_UPLOAD_ERR_MOVE');//"Impossibile spostare il file.";
					}
				} else {
					$file->errors[] = \JText::_('COM_AZMAILER_NEWSLETTER_FILEUPLOAD_ERR_NOJPG');//"Il file caricato non è un JPEG.";
				}
			} else {
				$file->errors[] = \JText::_('COM_AZMAILER_NEWSLETTER_UPLOAD_ERR_UPLOAD');//"Impossibile caricare il file.";
			}
		} else {
			$file->errors[] = \JText::sprintf('COM_AZMAILER_NEWSLETTER_FILEUPLOAD_ERR_ATTRIB', print_r($attribs));//"Attributi dell'elemento non validi: " . print_r($attribs);
		}
		return ($file);
	}

	//todo: check this $qty and $unit - use array for units
	/**
	 * @return int
	 */
	public static function getMaxAllowedUploadSizeBytes() {
		$value = ini_get('upload_max_filesize');
		$value_length = strlen($value);
		$qty = substr($value, 0, $value_length - 1);
		$unit = strtolower(substr($value, $value_length - 1));
		switch ($unit) {
			case 'k':
				$qty *= 1024;
				break;
			case 'm':
				$qty *= 1048576;
				break;
			case 'g':
				$qty *= 1073741824;
				break;
		}
		return (int)$qty;
	}

	/**
	 * @param \stdClass $file
	 * @return \stdClass
	 */
	public static function uploadNewsletterAttachment($file) {
		global $AZMAILER;
		jimport('joomla.filesystem.file');
		$NLATTACHMENTBASE = $AZMAILER->getOption("newsletter_attachment_base");// = /images/newsletter/attachments
		if ($file->nlid) {
			if (!is_null($file->uploadedfile) && $file->uploadedfile["error"] == 0) {
				$file->ATTACHMENTFOLDER = JPATH_SITE . DS . $NLATTACHMENTBASE;
				$file->EXTENSION = strtolower(\JFile::getExt($file->uploadedfile["name"]));
				$allowedExtensions = self::getAllowedAttachmentExtensions();
				if (in_array($file->EXTENSION, $allowedExtensions)) {
					$file->FILENAME = md5('attachment_for_newsletter_' . rand(10, 100000000) . '_' . date('U')) . '.' . $file->EXTENSION;
					$dstfilepath = $file->ATTACHMENTFOLDER . DS . $file->FILENAME;
					if (\JFile::upload($file->uploadedfile["tmp_name"], $dstfilepath)) {
						$file->NEWFILEURI = '/' . $NLATTACHMENTBASE . '/' . $file->FILENAME;
						//now we save this to newsletter
						$NL = self::getNewsletter($file->nlid);
						$ATTACHMENTS = json_decode(base64_decode($NL->nl_attachments));
						if (!is_object($ATTACHMENTS)) {
							$ATTACHMENTS = new \stdClass();
						}
						if (!isset($ATTACHMENTS->attachments) || !is_array($ATTACHMENTS->attachments)) {
							$ATTACHMENTS->attachments = array();
						}
						//
						$newAttachment = json_decode(json_encode($file->uploadedfile));//creating duplicate
						unset($newAttachment->tmp_name);
						unset($newAttachment->error);
						$newAttachment->filename = $file->FILENAME;
						$newAttachment->fileuri = $file->NEWFILEURI;
						//
						array_push($ATTACHMENTS->attachments, $newAttachment);
						$MODIFIED_ATTACHMENTS = base64_encode(json_encode($ATTACHMENTS));
						//
						$NL = new AZMailerNewsletter($NL);
						$NL->set("nl_attachments", $MODIFIED_ATTACHMENTS);
						if ($NL->save()) {
							//we are done!
						} else {
							$file->errors[] = \JText::_('COM_AZMAILER_NEWSLETTER_ATTACHMENT_UPLOAD_ERR_DB_SAVE');//"Unable to save modifications to newsletter!";
						}
					} else {
						$file->errors[] = \JText::_('COM_AZMAILER_NEWSLETTER_UPLOAD_ERR_MOVE');//"Impossibile spostare il file.";
					}
				} else {
					$file->errors[] = \JText::sprintf('COM_AZMAILER_NEWSLETTER_ATTACHMENT_UPLOAD_ERR_BAD_EXTENSION', $file->EXTENSION, implode(",", $allowedExtensions));//"The extension of the selected file(%s) is not allowed. Available file extensions: %s";
				}
			} else {
				$file->errors[] = \JText::_('COM_AZMAILER_NEWSLETTER_UPLOAD_ERR_UPLOAD');//"Impossibile caricare il file.";
			}
		} else {
			$file->errors[] = \JText::_('COM_AZMAILER_NEWSLETTER_ATTACHMENT_UPLOAD_ERR_NO_NLID');//"No newsletter id was defined!";
		}
		return ($file);
	}

	//todo: this will need to be moved when needed by others
	/**
	 * @return array
	 */
	public static function getAllowedAttachmentExtensions() {
		global $AZMAILER;
		$answer = array();
		$allowed_extensions = $AZMAILER->getOption("nl_attachment_allowed_extensions");
		if (!empty($allowed_extensions)) {
			$AE = explode(",", $allowed_extensions);
			if (count($AE)) {
				foreach ($AE as $ext) {
					$answer[] = trim($ext);
				}
			}
		}
		return ($answer);
	}

	/**
	 * @param int $nlid
	 * @return mixed
	 */
	public static function getNewsletter($nlid) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*');
		$query->from('#__azmailer_newsletter AS a');
		$query->where('a.id = ' . $db->quote($nlid, true));
		$db->setQuery($query);
		return($db->loadObject());
	}

	/**
	 * @param int $nlid
	 * @param string $filename
	 * @return \stdClass
	 */
	public static function removeNewsletterAttachment($nlid, $filename) {
		$answer = new \stdClass();
		$answer->errors = array();
		$answer->result = array();
		if ($nlid) {
			$NL = self::getNewsletter($nlid);
			$ATTACHMENTS = json_decode(base64_decode($NL->nl_attachments));
			$found = false;
			$attachment = new \stdClass();
			$attIndex = 0;
			if (is_object($ATTACHMENTS) && isset($ATTACHMENTS->attachments) && is_array($ATTACHMENTS->attachments) && count($ATTACHMENTS->attachments)) {
				foreach ($ATTACHMENTS->attachments as $attachment) {
					if ($attachment->filename == $filename) {
						$found = true;
						break;
					}
					$attIndex++;
				}
			}
			if ($found) {
				array_splice($ATTACHMENTS->attachments, $attIndex, 1);
				$NL = new AZMailerNewsletter($NL);
				$MODIFIED_ATTACHMENTS = base64_encode(json_encode($ATTACHMENTS));
				$NL->set("nl_attachments", $MODIFIED_ATTACHMENTS);
				if ($NL->save()) {
					jimport('joomla.filesystem.file');
					$ATTPATH = JPATH_ROOT . $attachment->fileuri;
					\JFile::delete($ATTPATH);
				} else {
					$answer->errors[] = \JText::_('COM_AZMAILER_NEWSLETTER_ATTACHMENT_UPLOAD_ERR_DB_SAVE');//"Unable to save modifications to newsletter!";
				}
			} else {
				$answer->errors[] = \JText::_('COM_AZMAILER_NEWSLETTER_ATTACHMENT_DELETE_ERR_NO_ATT_FOUND');//"The attachment was not found in this newsletter!";
			}
		} else {
			$answer->errors[] = \JText::_('COM_AZMAILER_NEWSLETTER_ATTACHMENT_UPLOAD_ERR_NO_NLID');//"No newsletter id was defined!";
		}
		return ($answer);
	}

	/**
	 * @param int $nlid
	 * @param string $email
	 * @return \stdClass
	 */
	public static function getNewsletterAttachments($nlid, $email = null) {
		$answer = new \stdClass();
		$answer->errors = array();
		$answer->result = array();
		if ($nlid) {
			$NL = self::getNewsletter($nlid);
			$ATTACHMENTS = json_decode(base64_decode($NL->nl_attachments));
			if (!is_object($ATTACHMENTS)) {
				$ATTACHMENTS = new \stdClass();
			}
			if (!isset($ATTACHMENTS->attachments) || !is_array($ATTACHMENTS->attachments)) {
				$ATTACHMENTS->attachments = array();
			}
			//todo: inject file existence check, mime type icons
			foreach ($ATTACHMENTS->attachments as &$attachment) {
				$attachment->downloadUrl = self::getDownloadUrlForAttachment($nlid, $attachment, $email);
			}
			$answer->result = $ATTACHMENTS->attachments;
		} else {
			$answer->errors[] = \JText::_('COM_AZMAILER_NEWSLETTER_ATTACHMENT_UPLOAD_ERR_NO_NLID');//"No newsletter id was defined!";
		}
		return ($answer);
	}

	//todo: we need an unified link which can handle recognising user and checking if he should have access to that specific attachment
	/**
	 * @param int $nlid
	 * @param \stdClass $attachment
	 * @param string $email
	 * @return string
	 */
	public static function getDownloadUrlForAttachment($nlid, $attachment, $email = null) {
		global $AZMAILER;
		$LNK = '#';
		if ($email) {
			$NLS = AZMailerSubscriberHelper::getNewsletterSubscriberByMail($email);
			$CTRL = urlencode(base64_encode($email));
			if (!is_null($NLS)) {
				$CTRLSTR = strtolower($NLS->id . '#' . $NLS->nls_email . '#' . $NLS->nls_firstname . '#' . $NLS->nls_lastname);
				$CTRL .= ':' . sha1(md5($CTRLSTR));
			}

			$LNK = ''
				. $AZMAILER->getOption('newsletter_http_host')
				. '/index.php?option=' . $AZMAILER->getOption('com_name')
				. '&task=azmailer.getAttachment'
				. '&file=' . urlencode(base64_encode($attachment->filename))
				. '&nlid=' . $nlid
				. '&format=raw'
				. '&ctrl=' . $CTRL;
		}
		return ($LNK);
	}

	/**
	 * @param $email
	 * @return string
	 */
	public static function getNewsletterRemoveMeUrlForMail($email) {
		global $AZMAILER;
		$NLS = AZMailerSubscriberHelper::getNewsletterSubscriberByMail($email);
		$CTRL = urlencode(base64_encode($email));
		if (!is_null($NLS)) {
			$CTRLSTR = strtolower($NLS->id . '#' . $NLS->nls_email . '#' . $NLS->nls_firstname . '#' . $NLS->nls_lastname);
			$CTRL .= ':' . sha1(md5($CTRLSTR));
		}
		$LNK = '' . $AZMAILER->getOption('newsletter_http_host')
			. '/index.php?option=' . $AZMAILER->getOption('com_name')
			. '&task=azmailer.removeMeFromNewsletter'
			. '&ctrl=' . $CTRL;
		return ($LNK);
	}


	/**
	 * @param string $CTRL
	 * @return bool
	 */
	public static function checkAndGetValidMailToRemoveFromNewsletter($CTRL) {
		$answer = false;
		$CA = explode(":", $CTRL);
		$email = base64_decode(urldecode($CA[0]));
		$NLS = AZMailerSubscriberHelper::getNewsletterSubscriberByMail($email);
		if (!is_null($NLS)) {
			if ($NLS->nls_blacklisted == 0) {
				$CTRLSTR = strtolower($NLS->id . '#' . $NLS->nls_email . '#' . $NLS->nls_firstname . '#' . $NLS->nls_lastname);
				$CTRL = sha1(md5($CTRLSTR));
				if ($CA[1] == $CTRL) {
					$answer = $NLS->nls_email;
				}
			}
		}
		return ($answer);
	}

	//TODO: Why is this here and not in AZMailerSubscriberHelper???
	/**
	 * @param string $email
	 * @return bool
	 */
	public static function blacklistNewsletterSubscriber($email) {
		$answer = false;
		$NLS = AZMailerSubscriberHelper::getNewsletterSubscriberByMail($email);
		if (!is_null($NLS)) {
			$NLS = new AZMailerSubscriber($NLS);
			$NLS->setup(false);
			$NLS->set("nls_blacklisted", 1);
			$answer = $NLS->sync();
		}
		return ($answer);
	}

	/**
	 * @param array $arr
	 * @param string $checkField
	 * @return array
	 */
	public static function cleanupArrayOfObjectsFromDuplicates($arr, $checkField = "nls_email") {
		$tmp = array();
		while ( ($popped = array_pop($arr)) ) {
			if (!array_key_exists($popped->$checkField, $tmp)) {
				$tmp[$popped->$checkField] = $popped;
			}
		}
		return ($tmp);
	}


	/**
	 * @param int $nlid
	 */
	public static function deleteImagesForNewsletter($nlid) {
		jimport('joomla.filesystem.file');
		$NL = self::getNewsletter($nlid);
		$SUBSTITUTIONS = json_decode(base64_decode($NL->nl_template_substitutions));
		if (is_object($SUBSTITUTIONS)) {
			foreach ($SUBSTITUTIONS as $SK => $SV) {
				//\JFactory::getApplication()->enqueueMessage("subst: " . $SK . " - " . $SV);
				if (substr($SK, 0, 5) == 'image') {
					$IMAGEPATH = JPATH_ROOT . $SV;
					//\JFactory::getApplication()->enqueueMessage("deleting image: " . $IMAGEPATH);
					\JFile::delete($IMAGEPATH);
				}
			}
		}
	}

	/**
	 * @param int $nlid
	 * @return string
	 */
	public static function duplicateImagesForNewsletter($nlid) {
		global $AZMAILER;
		jimport('joomla.filesystem.file');
		$NL = self::getNewsletter($nlid);
		$SUBSTITUTIONS = json_decode(base64_decode($NL->nl_template_substitutions));
		$cacheFolder = $AZMAILER->getOption("newsletter_cache_image_base");
		if (is_object($SUBSTITUTIONS)) {
			foreach ($SUBSTITUTIONS as $SK => &$SV) {
				if (substr($SK, 0, 5) == 'image') {
					//$SV = "/media/com_azmailer/cache/86f2fb605d09f42e5d05bf85901c1374.jpg" - in newsletter_cache_image_base
					$PI = pathinfo(JPATH_SITE . $SV);
					$fullPath = $PI["dirname"] . DS;
					$oldFilePath = $fullPath . $PI["basename"];
					$newFileName = md5('image_for_newsletter_' . rand(10, 100000000) . '_' . date('U')) . '.jpg';
					$newFilePath = $fullPath . $newFileName;
					\JFile::copy($oldFilePath, $newFilePath);
					$SV = DS . $cacheFolder . DS . $newFileName;
				}
			}
		}
		$NEWSUBSTITUTIONS = base64_encode(json_encode($SUBSTITUTIONS));//repack
		return ($NEWSUBSTITUTIONS);
	}

	/**
	 * called by model when deleting newletter
	 * @param int $nlid
	 */
	public static function deleteAttachmentsForNewsletter($nlid) {
		global $AZMAILER;
		jimport('joomla.filesystem.file');
		$NL = self::getNewsletter($nlid);
		$ATTACHMENTS = json_decode(base64_decode($NL->nl_attachments));
		$attachmentFolder = $AZMAILER->getOption("newsletter_attachment_base");
		if (is_object($ATTACHMENTS) && isset($ATTACHMENTS->attachments) && is_array($ATTACHMENTS->attachments) && count($ATTACHMENTS->attachments)) {
			foreach ($ATTACHMENTS->attachments as &$attachment) {
				\JFile::delete(JPATH_SITE . DS . $attachmentFolder . DS . $attachment->filename);
			}
		}
	}

	/**
	 * //called by model when duplicating newsletter
	 * @param int $nlid
	 * @return string
	 */
	public static function duplicateAttachmentsForNewsletter($nlid) {
		global $AZMAILER;
		jimport('joomla.filesystem.file');
		$NL = self::getNewsletter($nlid);
		$ATTACHMENTS = json_decode(base64_decode($NL->nl_attachments));
		$attachmentFolder = $AZMAILER->getOption("newsletter_attachment_base");
		if (!is_object($ATTACHMENTS)) {
			$ATTACHMENTS = new \stdClass();
		}
		if (!isset($ATTACHMENTS->attachments) || !is_array($ATTACHMENTS->attachments)) {
			$ATTACHMENTS->attachments = array();
		}
		foreach ($ATTACHMENTS->attachments as &$attachment) {
			$extension = strtolower(\JFile::getExt($attachment->filename));
			$newFileName = md5('attachment_for_newsletter_' . rand(10, 100000000) . '_' . date('U')) . '.' . $extension;
			\JFile::copy(JPATH_SITE . DS . $attachmentFolder . DS . $attachment->filename, JPATH_SITE . DS . $attachmentFolder . DS . $newFileName);
			$attachment->filename = $newFileName;
		}
		$NEWATTACHMENTS = base64_encode(json_encode($ATTACHMENTS));//repack
		return ($NEWATTACHMENTS);
	}

}
