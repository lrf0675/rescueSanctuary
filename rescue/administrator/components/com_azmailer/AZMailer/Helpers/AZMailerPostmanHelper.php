<?php
namespace AZMailer\Helpers;

use AZMailer\Core\AZMailerPostman;

/**
 * @package    AZMailer
 * @subpackage Helpers
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Class AZMailerPostmanHelper
 * @package AZMailer\Helpers
 */
class AZMailerPostmanHelper {
	/* "base64" or "quoted-printable" */
	static private $enc_body_txt = "base64";
	static private $enc_body_htm = "base64";

	/**
	 * @param string $str
	 * @return bool
	 */
	static public function is_valid_hostname($str = null) {
		$answer = false;
		$str = trim($str);
		if (!empty($str)) {
			$ipv4 = @gethostbyname($str);
			if (self::is_ipv4($ipv4)) {
				$answer = true;
			}
		}
		return ($answer);
	}

	/**
	 * @param string $str
	 * @return bool
	 */
	static public function is_ipv4($str = null) {
		$str = trim($str);
		return (is_string($str) && !empty($str) && ip2long($str) && count(explode('.', $str)) === 4);
	}

	/**
	 * @param array $MDATA
	 * @return string
	 */
	static public function composeMessageData($MDATA = array()) {
		$msgData = new \stdClass();
		$msgData->headers = array();
		$msgData->text = array();
		$msgData->html = array();
		$msgData->attachments = array();

		//FROM
		$HSTR = 'From: ';
		if (!empty($MDATA["fromname"])) {
			$fromName = AZMailerMimeHelper::encode_header($MDATA["fromname"]);
			if ($fromName == $MDATA["fromname"]) {
				$fromName = '"' . str_replace('"', '\\"', $MDATA["fromname"]) . '"';
			}
			$HSTR .= $fromName . ' <' . $MDATA["from"] . '>';
		} else {
			$HSTR .= $MDATA["from"];
		}
		array_push($msgData->headers, $HSTR);

		//TO
		$HSTR = 'To: ' . $MDATA["to"];
		array_push($msgData->headers, $HSTR);

		//SUBJECT
		$HSTR = 'Subject: ' . AZMailerMimeHelper::encode_header($MDATA["subject"]);
		array_push($msgData->headers, $HSTR);

		//RETURN PATH
		if (!empty($MDATA["returnpath"])) {
			$HSTR = 'Return-Path: <' . AZMailerMimeHelper::encode_header($MDATA["returnpath"]) . '>';
			array_push($msgData->headers, $HSTR);
		}

		//X-MAILER
		if (!empty($MDATA["xmailer"])) {
			$HSTR = 'X-Mailer: ' . AZMailerMimeHelper::encode_header($MDATA["xmailer"]);
			array_push($msgData->headers, $HSTR);
		}

		//MESSAGE-ID
		if (!empty($MDATA["messageid"])) {
			$HSTR = 'Message-ID: <' . AZMailerMimeHelper::unique($MDATA["messageid"]) . AZMailerMimeHelper::encode_header($MDATA["messageid"]) . '>';
			array_push($msgData->headers, $HSTR);
		}

		//CUSTOME HEADERS
		if (count($MDATA["headers"]) > 0) {
			foreach ($MDATA["headers"] as $HK => $HV) {
				$HSTR = $HK . ': ' . AZMailerMimeHelper::encode_header($HV);
				array_push($msgData->headers, $HSTR);
			}
		}

		//TEXT
		$name = null;
		$charset = null;
		//$encoding = self::$enc_body_txt;
		$msgData->text = AZMailerMimeHelper::message($MDATA["text"], 'text/plain', $name, $charset, self::$enc_body_txt);
		if (!$msgData->text) {
			AZMailerPostman::logThis("AZMailerPostmanHelper::message: unable to set message content(text)!");
			return (false);
		}

		//HTML
		$name = null;
		$charset = null;
		//$encoding = self::$enc_body_htm;
		$msgData->html = AZMailerMimeHelper::message($MDATA["html"], 'text/html', $name, $charset, self::$enc_body_htm);
		if (!$msgData->html) {
			AZMailerPostman::logThis("AZMailerPostmanHelper::message: unable to set message content(html)!");
			return (false);
		}

		//ATTACHMENTS - to be done
		if (count($MDATA["attachments"]) > 0) {
			$attArr = array();
			$err = false;
			foreach ($MDATA["attachments"] as &$attach) {
				if (isset($attach['content'])) {
					$att = AZMailerMimeHelper::message($attach['content'],
						isset($attach['type']) ? $attach['type'] : null,
						isset($attach['name']) ? $attach['name'] : null,
						isset($attach['charset']) ? $attach['charset'] : null,
						isset($attach['encoding']) ? $attach['encoding'] : null,
						isset($attach['disposition']) ? $attach['disposition'] : null,
						isset($attach['id']) ? $attach['id'] : null,
						null, null);
					if ($att) {
						array_push($attArr, $att);
					} else {
						$err = true;
						break;
					}
				} else {
					$err = true;
					break;
				}
			}
			if ($err) {
				AZMailerPostman::logThis("AZMailerPostmanHelper::message: unable to set message attachment!");
				return (false);
			}
			if (count($attArr)) {
				$msgData->attachments = &$attArr;
			}
		}

		//GET COMPOSED MESSAGE
		$msgData->message = AZMailerMimeHelper::compose($msgData->text, $msgData->html, $msgData->attachments);

		//ASSEMBLE MESSAGE FULL CONTENT
		$msgData->content = implode(AZMailerMimeHelper::LE, $msgData->headers)
			. AZMailerMimeHelper::LE
			. $msgData->message['header']
			. AZMailerMimeHelper::LE
			. AZMailerMimeHelper::LE
			. $msgData->message['content'];

		//RETURN MAIL CONTENT
		return ($msgData->content);
	}


	/* --- functions from here below were taken from XPertMailer's FUNC5 class --- */

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *                                                                                         *
	 *  XPertMailer is a PHP Mail Class that can send and read messages in MIME format.        *
	 *  This file is part of the XPertMailer package (http://xpertmailer.sourceforge.net/)     *
	 *  Copyright (C) 2007 Tanase Laurentiu Iulian                                             *
	 *                                                                                         *
	 *  This library is free software; you can redistribute it and/or modify it under the      *
	 *  terms of the GNU Lesser General Public License as published by the Free Software       *
	 *  Foundation; either version 2.1 of the License, or (at your option) any later version.  *
	 *                                                                                         *
	 *  This library is distributed in the hope that it will be useful, but WITHOUT ANY        *
	 *  WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A        *
	 *  PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.        *
	 *                                                                                         *
	 *  You should have received a copy of the GNU Lesser General Public License along with    *
	 *  this library; if not, write to the Free Software Foundation, Inc., 51 Franklin Street, *
	 *  Fifth Floor, Boston, MA 02110-1301, USA                                                *
	 *                                                                                         *
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	/**
	 * @param string $str
	 * @param boolean $num
	 * @param string $add
	 * @return bool
	 */
	static public function is_alpha($str = null, $num = true, $add = '') {
		if (!is_string($str)) {
			AZMailerPostman::logThis("AZMailerPostmanHelper::is_alpha: invalid argument type");
			return (false);
		}
		if (!is_bool($num)) {
			AZMailerPostman::logThis("AZMailerPostmanHelper::is_alpha: invalid numeric type");
			return (false);
		}
		if (!is_string($add)) {
			AZMailerPostman::logThis("AZMailerPostmanHelper::is_alpha: invalid additional type");
			return (false);
		}
		if ($str != '') {
			$lst = 'abcdefghijklmnoqprstuvwxyzABCDEFGHIJKLMNOQPRSTUVWXYZ' . $add;
			if ($num) $lst .= '1234567890';
			$len1 = strlen($str);
			$len2 = strlen($lst);
			$match = true;
			for ($i = 0; $i < $len1; $i++) {
				$found = false;
				for ($j = 0; $j < $len2; $j++) {
					if ($lst{$j} == $str{$i}) {
						$found = true;
						break;
					}
				}
				if (!$found) {
					$match = false;
					break;
				}
			}
			return $match;
		} else {
			return false;
		}
	}

	/**
	 * @param string $name
	 * @return string
	 */
	static public function mime_type($name = null) {
		if (!is_string($name)) {
			AZMailerPostman::logThis("AZMailerPostmanHelper::mime_type: invalid filename type");
			return (false);
		}
		$name = self::str_clear($name);
		$name = trim($name);
		if ($name == '') {
			AZMailerPostman::logThis("AZMailerPostmanHelper::mime_type: invalid filename value");
			return (false);
		}
		$ret = 'application/octet-stream';
		$arr = array(
			'z' => 'application/x-compress',
			'xls' => 'application/x-excel',
			'gtar' => 'application/x-gtar',
			'gz' => 'application/x-gzip',
			'cgi' => 'application/x-httpd-cgi',
			'php' => 'application/x-httpd-php',
			'js' => 'application/x-javascript',
			'swf' => 'application/x-shockwave-flash',
			'tar' => 'application/x-tar',
			'tgz' => 'application/x-tar',
			'tcl' => 'application/x-tcl',
			'src' => 'application/x-wais-source',
			'zip' => 'application/zip',
			'kar' => 'audio/midi',
			'mid' => 'audio/midi',
			'midi' => 'audio/midi',
			'mp2' => 'audio/mpeg',
			'mp3' => 'audio/mpeg',
			'mpga' => 'audio/mpeg',
			'ram' => 'audio/x-pn-realaudio',
			'rm' => 'audio/x-pn-realaudio',
			'rpm' => 'audio/x-pn-realaudio-plugin',
			'wav' => 'audio/x-wav',
			'bmp' => 'image/bmp',
			'fif' => 'image/fif',
			'gif' => 'image/gif',
			'ief' => 'image/ief',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'png' => 'image/png',
			'tif' => 'image/tiff',
			'tiff' => 'image/tiff',
			'css' => 'text/css',
			'htm' => 'text/html',
			'html' => 'text/html',
			'txt' => 'text/plain',
			'rtx' => 'text/richtext',
			'vcf' => 'text/x-vcard',
			'xml' => 'text/xml',
			'xsl' => 'text/xsl',
			'mpe' => 'video/mpeg',
			'mpeg' => 'video/mpeg',
			'mpg' => 'video/mpeg',
			'mov' => 'video/quicktime',
			'qt' => 'video/quicktime',
			'asf' => 'video/x-ms-asf',
			'asx' => 'video/x-ms-asf',
			'avi' => 'video/x-msvideo',
			'vrml' => 'x-world/x-vrml',
			'wrl' => 'x-world/x-vrml');
		if (count($exp = explode('.', $name)) >= 2) {
			$ext = strtolower($exp[count($exp) - 1]);
			if (trim($exp[count($exp) - 2]) != '' && isset($arr[$ext])) $ret = $arr[$ext];
		}
		return $ret;
	}

	/**
	 * @param string $str
	 * @param array $addrep
	 * @return bool|mixed|string
	 */
	static public function str_clear($str = null, $addrep = null) {
		$rep = array("\r", "\n", "\t");
		if (!is_string($str)) {
			AZMailerPostman::logThis("AZMailerPostmanHelper::str_clear: invalid argument type");
			return (false);
		}
		if ($addrep == null) {
			$addrep = array();
		}
		if (is_array($addrep)) {
			if (count($addrep) > 0) {
				$err = false;
				foreach ($addrep as $strrep) {
					if (is_string($strrep) && $strrep != '') {
						$rep[] = $strrep;
					} else {
						$err = true;
						break;
					}
				}
				if ($err) {
					AZMailerPostman::logThis("AZMailerPostmanHelper::str_clear: nvalid array value");
					return (false);
				}
			}
		} else {
			AZMailerPostman::logThis("AZMailerPostmanHelper::str_clear: invalid array type");
			return (false);
		}
		return ($str == '') ? '' : str_replace($rep, '', $str);
	}


}