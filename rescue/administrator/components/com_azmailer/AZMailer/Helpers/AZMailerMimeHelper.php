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

/* --- functions from here below were taken from XPertMailer's MIME5 class --- */
/* --- This is NOT the original class - it has been modified for AZMailer needs--- */

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
 * Class AZMailerMimeHelper
 */
class AZMailerMimeHelper {

	const LE = "\r\n";
	const HLEN = 52;
	const MLEN = 73;

	const HCHARSET = 'utf-8';
	const MCHARSET = 'utf-8';

	const HENCDEF = 'quoted-printable';
	const MENCDEF = 'base64';

	static public $hencarr = array('quoted-printable' => '', 'base64' => '');
	static public $mencarr = array('7bit' => '', '8bit' => '', 'quoted-printable' => '', 'base64' => '', 'binary' => '');

	static public $qpkeys = array("\x00", "\x01", "\x02", "\x03", "\x04", "\x05", "\x06", "\x07", "\x08", "\x09", "\x0A", "\x0B", "\x0C", "\x0D", "\x0E", "\x0F", "\x10", "\x11", "\x12", "\x13", "\x14", "\x15", "\x16", "\x17", "\x18", "\x19", "\x1A", "\x1B", "\x1C", "\x1D", "\x1E", "\x1F", "\x7F", "\x80", "\x81", "\x82", "\x83", "\x84", "\x85", "\x86", "\x87", "\x88", "\x89", "\x8A", "\x8B", "\x8C", "\x8D", "\x8E", "\x8F", "\x90", "\x91", "\x92", "\x93", "\x94", "\x95", "\x96", "\x97", "\x98", "\x99", "\x9A", "\x9B", "\x9C", "\x9D", "\x9E", "\x9F", "\xA0", "\xA1", "\xA2", "\xA3", "\xA4", "\xA5", "\xA6", "\xA7", "\xA8", "\xA9", "\xAA", "\xAB", "\xAC", "\xAD", "\xAE", "\xAF", "\xB0", "\xB1", "\xB2", "\xB3", "\xB4", "\xB5", "\xB6", "\xB7", "\xB8", "\xB9", "\xBA", "\xBB", "\xBC", "\xBD", "\xBE", "\xBF", "\xC0", "\xC1", "\xC2", "\xC3", "\xC4", "\xC5", "\xC6", "\xC7", "\xC8", "\xC9", "\xCA", "\xCB", "\xCC", "\xCD", "\xCE", "\xCF", "\xD0", "\xD1", "\xD2", "\xD3", "\xD4", "\xD5", "\xD6", "\xD7", "\xD8", "\xD9", "\xDA", "\xDB", "\xDC", "\xDD", "\xDE", "\xDF", "\xE0", "\xE1", "\xE2", "\xE3", "\xE4", "\xE5", "\xE6", "\xE7", "\xE8", "\xE9", "\xEA", "\xEB", "\xEC", "\xED", "\xEE", "\xEF", "\xF0", "\xF1", "\xF2", "\xF3", "\xF4", "\xF5", "\xF6", "\xF7", "\xF8", "\xF9", "\xFA", "\xFB", "\xFC", "\xFD", "\xFE", "\xFF");
	static public $qpvrep = array("=00", "=01", "=02", "=03", "=04", "=05", "=06", "=07", "=08", "=09", "=0A", "=0B", "=0C", "=0D", "=0E", "=0F", "=10", "=11", "=12", "=13", "=14", "=15", "=16", "=17", "=18", "=19", "=1A", "=1B", "=1C", "=1D", "=1E", "=1F", "=7F", "=80", "=81", "=82", "=83", "=84", "=85", "=86", "=87", "=88", "=89", "=8A", "=8B", "=8C", "=8D", "=8E", "=8F", "=90", "=91", "=92", "=93", "=94", "=95", "=96", "=97", "=98", "=99", "=9A", "=9B", "=9C", "=9D", "=9E", "=9F", "=A0", "=A1", "=A2", "=A3", "=A4", "=A5", "=A6", "=A7", "=A8", "=A9", "=AA", "=AB", "=AC", "=AD", "=AE", "=AF", "=B0", "=B1", "=B2", "=B3", "=B4", "=B5", "=B6", "=B7", "=B8", "=B9", "=BA", "=BB", "=BC", "=BD", "=BE", "=BF", "=C0", "=C1", "=C2", "=C3", "=C4", "=C5", "=C6", "=C7", "=C8", "=C9", "=CA", "=CB", "=CC", "=CD", "=CE", "=CF", "=D0", "=D1", "=D2", "=D3", "=D4", "=D5", "=D6", "=D7", "=D8", "=D9", "=DA", "=DB", "=DC", "=DD", "=DE", "=DF", "=E0", "=E1", "=E2", "=E3", "=E4", "=E5", "=E6", "=E7", "=E8", "=E9", "=EA", "=EB", "=EC", "=ED", "=EE", "=EF", "=F0", "=F1", "=F2", "=F3", "=F4", "=F5", "=F6", "=F7", "=F8", "=F9", "=FA", "=FB", "=FC", "=FD", "=FE", "=FF");

	/**
	 * @param string $str
	 * @param string $charset
	 * @param string $encoding
	 * @param integer $len
	 * @param string $end
	 * @return string
	 */
	static public function encode_header($str = null, $charset = null, $encoding = null, $len = null, $end = null) {
		if (!is_string($str)) {
			AZMailerPostman::logThis("AZMailerMime::encode_header: invalid argument type");
			return (false);
		}
		if ($charset == null) {
			$charset = self::HCHARSET;
		} else if (!is_string($charset)) {
			AZMailerPostman::logThis("AZMailerMime::encode_header: invalid charset type");
			return (false);
		} else if (!(strlen($charset) >= 2 && AZMailerPostmanHelper::is_alpha($charset, true, '-'))) {
			AZMailerPostman::logThis("AZMailerMime::encode_header: invalid charset value");
			return (false);
		}
		if ($encoding == null) {
			$encoding = self::HENCDEF;
		} else if (!is_string($encoding)) {
			AZMailerPostman::logThis("AZMailerMime::encode_header: invalid encoding type");
			return (false);
		} else {
			$encoding = strtolower(AZMailerPostmanHelper::str_clear($encoding));
			if (!isset(self::$hencarr[$encoding])) {
				AZMailerPostman::logThis("AZMailerMime::encode_header: invalid encoding value");
				return (false);
			}
		}
		if ($len == null) {
			$len = self::HLEN;
		} else if (!(is_int($len) && $len > 1)) {
			AZMailerPostman::logThis("AZMailerMime::encode_header: invalid line length value");
			return (false);
		}
		if ($end == null) {
			$end = self::LE;
		} else if (!is_string($end)) {
			AZMailerPostman::logThis("AZMailerMime::encode_header: invalid line end value");
			return (false);
		}

		if ($str == '')
			return $str;
		else {
			$enc = false;
			$dif = $len - strlen('=?' . $charset . '?X??=');
			if ($encoding == 'quoted-printable') {
				if (!self::is_printable($str)) {
					$new = (($dif - 4) > 2) ? ($dif - 4) : $len;
					$enc = self::qp_encode($str, $new, $end);
					$enc = str_replace(array('?', ' ', '=' . $end), array('=3F', '_', $end), $enc);
				}
			} else if ($encoding == 'base64') {
				$new = ($dif > 3) ? $dif : $len;
				if ($new > 3) {
					for ($i = $new; $i > 2; $i--) {
						$crt = '';
						for ($j = 0; $j <= $i; $j++)
							$crt .= 'x';
						if (strlen(base64_encode($crt)) <= $new) {
							$new = $i;
							break;
						}
					}
				}
				$cnk = rtrim(chunk_split($str, $new, $end));
				$imp = array();
				foreach (explode($end, $cnk) as $line)
					if ($line != '')
						$imp[] = base64_encode($line);
				$enc = implode($end, $imp);
			}
			$res = array();
			if ($enc) {
				$chr = ($encoding == 'base64') ? 'B' : 'Q';
				foreach (explode($end, $enc) as $val)
					if ($val != '')
						$res[] = '=?' . $charset . '?' . $chr . '?' . $val . '?=';
			} else {
				$cnk = rtrim(chunk_split($str, $len, $end));
				foreach (explode($end, $cnk) as $val)
					if ($val != '')
						$res[] = $val;
			}
			return implode($end . "\t", $res);
		}
	}

	/**
	 * @param string $str
	 * @return bool
	 */
	static public function is_printable($str = null) {
		if (!is_string($str)) {
			AZMailerPostman::logThis("AZMailerMime::is_printable: invalid argument type");
			return (false);
		}
		$contain = implode('', self::$qpkeys);
		return (strcspn($str, $contain) == strlen($str));
	}

	/**
	 * @param string $str
	 * @param integer $len
	 * @param string $end
	 * @return string
	 */
	static public function qp_encode($str = null, $len = null, $end = null) {
		if (!is_string($str)) {
			AZMailerPostman::logThis("AZMailerMime::qp_encode: invalid argument type");
			return (false);
		}
		if ($len == null) {
			$len = self::MLEN;
		} else if (!(is_int($len) && $len > 1)) {
			AZMailerPostman::logThis("AZMailerMime::qp_encode: invalid line length value");
			return (false);
		}
		if ($end == null) {
			$end = self::LE;
		} else if (!is_string($end)) {
			AZMailerPostman::logThis("AZMailerMime::qp_encode: invalid line end value");
			return (false);
		}
		if ($str == '') {
			return $str;
		}

		$out = array();
		foreach (explode($end, $str) as $line) {
			if ($line == '')
				$out[] = '';
			else {
				$line = str_replace('=', '=3D', $line);
				$line = str_replace(self::$qpkeys, self::$qpvrep, $line);
				preg_match_all('/.{1,' . $len . '}([^=]{0,2})?/', $line, $match);
				$mcnt = count($match[0]);
				for ($i = 0; $i < $mcnt; $i++) {
					$line = (substr($match[0][$i], -1) == ' ') ? substr($match[0][$i], 0, -1) . '=20' : $match[0][$i];
					if (($i + 1) < $mcnt)
						$line .= '=';
					$out[] = $line;
				}
			}
		}
		return implode($end, $out);
	}

	/**
	 * @param string $str
	 * @return array
	 */
	static public function decode_header($str = null) {
		if (!is_string($str)) {
			AZMailerPostman::logThis("AZMailerMime::decode_header: invalid argument type");
			return (false);
		}
		$str = trim(AZMailerPostmanHelper::str_clear($str));
		$arr = array();
		if ($str == '')
			$arr[] = array('charset' => self::HCHARSET, 'value' => '');
		else {
			foreach (preg_split('/(?<!\\?(?i)q)\\?\\=/', $str, -1, PREG_SPLIT_NO_EMPTY) as $str1) {
				foreach (explode('=?', $str1, 2) as $str2) {
					$def = false;
					if (count($exp = explode('?B?', $str2)) == 2) {
						if (strlen($exp[0]) >= 2 && AZMailerPostmanHelper::is_alpha($exp[0], true, '-') && trim($exp[1]) != '')
							$def = array('charset' => $exp[0], 'value' => base64_decode(trim($exp[1])));
					} else if (count($exp = explode('?b?', $str2)) == 2) {
						if (strlen($exp[0]) >= 2 && AZMailerPostmanHelper::is_alpha($exp[0], true, '-') && trim($exp[1]) != '')
							$def = array('charset' => $exp[0], 'value' => base64_decode(trim($exp[1])));
					} else if (count($exp = explode('?Q?', $str2)) == 2) {
						if (strlen($exp[0]) >= 2 && AZMailerPostmanHelper::is_alpha($exp[0], true, '-') && $exp[1] != '')
							$def = array('charset' => $exp[0], 'value' => quoted_printable_decode(str_replace('_', ' ', $exp[1])));
					} else if (count($exp = explode('?q?', $str2)) == 2) {
						if (strlen($exp[0]) >= 2 && AZMailerPostmanHelper::is_alpha($exp[0], true, '-') && $exp[1] != '')
							$def = array('charset' => $exp[0], 'value' => quoted_printable_decode(str_replace('_', ' ', $exp[1])));
					}
					if ($def) {
						if ($def['value'] != '')
							$arr[] = array('charset' => $def['charset'], 'value' => $def['value']);
					} else {
						if ($str2 != '')
							$arr[] = array('charset' => self::HCHARSET, 'value' => $str2);
					}
				}
			}
		}
		return $arr;
	}

	/**
	 * @param string $content
	 * @param string $type
	 * @param string $name
	 * @param string $charset
	 * @param string $encoding
	 * @param string $disposition
	 * @param string $id
	 * @param integer $len
	 * @param string $end
	 * @return array
	 */
	static public function message($content = null, $type = null, $name = null, $charset = null, $encoding = null, $disposition = null, $id = null, $len = null, $end = null) {
		if (!(is_string($content) && $content != '')) {
			AZMailerPostman::logThis("AZMailerMime::message: must be a string");
			return (false);
		}
		if ($type == null) {
			$type = 'application/octet-stream';
		} else if (is_string($type)) {
			$type = trim(AZMailerPostmanHelper::str_clear($type));
			if (strlen($type) < 4) {
				AZMailerPostman::logThis("AZMailerMime::message: invalid type value");
				return (false);
			}
		} else {
			AZMailerPostman::logThis("AZMailerMime::message: invalid type");
			return (false);
		}
		if (is_string($name)) {
			$name = trim(AZMailerPostmanHelper::str_clear($name));
			if ($name == '') {
				AZMailerPostman::logThis("AZMailerMime::message: invalid name value");
				return (false);
			}
		} else if ($name != null) {
			AZMailerPostman::logThis("AZMailerMime::message: invalid name type");
			return (false);
		}
		if ($charset == null) {
			$charset = self::MCHARSET;
		} else if (!is_string($charset)) {
			AZMailerPostman::logThis("AZMailerMime::message: invalid charset type");
			return (false);
		} else if (!(strlen($charset) >= 2 && AZMailerPostmanHelper::is_alpha($charset, true, '-'))) {
			AZMailerPostman::logThis("AZMailerMime::message: invalid charset value");
			return (false);
		}
		if ($encoding == null) {
			$encoding = self::MENCDEF;
		} else if (!is_string($encoding)) {
			AZMailerPostman::logThis("AZMailerMime::message: invalid encoding type");
			return (false);
		} else {
			$encoding = strtolower(AZMailerPostmanHelper::str_clear($encoding));
			if (!isset(self::$mencarr[$encoding])) {
				AZMailerPostman::logThis("AZMailerMime::message: invalid encoding value");
				return (false);
			}
		}
		if ($disposition == null) {
			$disposition = 'inline';
		} else if (is_string($disposition)) {
			$disposition = strtolower(AZMailerPostmanHelper::str_clear($disposition));
			if (!($disposition == 'inline' || $disposition == 'attachment')) {
				AZMailerPostman::logThis("AZMailerMime::message: invalid disposition value");
				return (false);
			}
		} else {
			AZMailerPostman::logThis("AZMailerMime::message: invalid disposition type");
			return (false);
		}
		if (is_string($id)) {
			$id = AZMailerPostmanHelper::str_clear($id, array(' '));
			if ($id == '') {
				AZMailerPostman::logThis("AZMailerMime::message: invalid id value");
				return (false);
			}
		} else if ($id != null) {
			AZMailerPostman::logThis("AZMailerMime::message: invalid id type");
			return (false);
		}
		if ($len == null) {
			$len = self::MLEN;
		} else if (!(is_int($len) && $len > 1)) {
			AZMailerPostman::logThis("AZMailerMime::message: invalid line length value");
			return (false);
		}
		if ($end == null) {
			$end = self::LE;
		} else if (!is_string($end)) {
			AZMailerPostman::logThis("AZMailerMime::message: invalid line end value");
			return (false);
		}
		$header = '' . 'Content-Type: ' . $type . ';' . $end . "\t" . 'charset="' . $charset . '"' . (($name == null) ? '' : ';' . $end . "\t" . 'name="' . $name . '"') . $end . 'Content-Transfer-Encoding: ' . $encoding . $end . 'Content-Disposition: ' . $disposition . (($name == null) ? '' : ';' . $end . "\t" . 'filename="' . $name . '"') . (($id == null) ? '' : $end . 'Content-ID: <' . $id . '>');
		if ($encoding == '7bit' || $encoding == '8bit')
			$content = wordwrap(self::fix_eol($content), $len, $end, true);
		else if ($encoding == 'base64')
			$content = rtrim(chunk_split(base64_encode($content), $len, $end));
		else if ($encoding == 'quoted-printable')
			$content = self::qp_encode(self::fix_eol($content), $len, $end);
		return array('header' => $header, 'content' => $content);
	}

	/**
	 * @param string $str
	 * @return string|boolean
	 */
	static public function fix_eol($str = null) {
		if (!(is_string($str) && $str != '')) {
			AZMailerPostman::logThis("AZMailerMime::fix_eol: invalid content value");
			return (false);
		}
		$str = str_replace("\r\n", "\n", $str);
		$str = str_replace("\r", "\n", $str);
		if (self::LE != "\n") {
			$str = str_replace("\n", self::LE, $str);
		}
		return $str;
	}

	/**
	 * @param string $text
	 * @param string $html
	 * @param array $attach
	 * @param integer $uniq
	 * @param string $end
	 * @return array
	 */
	static public function compose($text = null, $html = null, $attach = null, $uniq = null, $end = null) {
		if ($text == null && $html == null) {
			AZMailerPostman::logThis("AZMailerMime::message: message is not set");
			return (false);
		} else {
			if ($text != null) {
				if (!(is_array($text) && isset($text['header'], $text['content']) && is_string($text['header']) && is_string($text['content']) && self::isset_header($text['header'], 'content-type', 'text/plain'))) {
					AZMailerPostman::logThis("AZMailerMime::message: invalid text message type");
					return (false);
				}
			}
			if ($html != null) {
				if (!(is_array($html) && isset($html['header'], $html['content']) && is_string($html['header']) && is_string($html['content']) && self::isset_header($html['header'], 'content-type', 'text/html'))) {
					AZMailerPostman::logThis("AZMailerMime::message: invalid html message type");
					return (false);
				}
			}
		}

		if ($attach != null) {
			if (is_array($attach) && count($attach) > 0) {
				$err = false;
				foreach ($attach as $arr) {
					if (!(is_array($arr) && isset($arr['header'], $arr['content']) && is_string($arr['header']) && is_string($arr['content']) && (self::isset_header($arr['header'], 'content-disposition', 'inline') || self::isset_header($arr['header'], 'content-disposition', 'attachment')))) {
						$err = true;
						break;
					}
				}
				if ($err) {
					AZMailerPostman::logThis("AZMailerMime::message: invalid attachment type");
					return (false);
				}
			} else {
				AZMailerPostman::logThis("AZMailerMime::message: invalid attachment format");
				return (false);
			}
		}
		if ($end == null) {
			$end = self::LE;
		} else if (!is_string($end)) {
			AZMailerPostman::logThis("AZMailerMime::message: invalid line end value");
			return (false);
		}

		$multipart = false;
		if ($text && $html)
			$multipart = true;
		if ($attach)
			$multipart = true;
		$header = $body = array();
		$header[] = 'Date: ' . date('r');
		//$header[] = 'X-Mailer: XPM4 v.0.5 < www.xpertmailer.com >';//taken out so users can customize
		if ($multipart) {
			$uniq = ($uniq == null) ? 0 : intval($uniq);
			$boundary1 = '=_1.' . self::unique($uniq++);
			$boundary2 = '=_2.' . self::unique($uniq++);
			$boundary3 = '=_3.' . self::unique($uniq);
			$disp['inline'] = $disp['attachment'] = false;
			if ($attach != null) {
				foreach ($attach as $darr) {
					if (self::isset_header($darr['header'], 'content-disposition', 'inline'))
						$disp['inline'] = true;
					else if (self::isset_header($darr['header'], 'content-disposition', 'attachment'))
						$disp['attachment'] = true;
				}
			}
			$hstr = 'Content-Type: multipart/%s;' . $end . "\t" . 'boundary="%s"';
			$bstr = '--%s' . $end . '%s' . $end . $end . '%s';
			$body[] = 'This is a message in MIME Format. If you see this, your mail reader does not support this format.' . $end;
			if ($text && $html) {
				if ($disp['inline'] && $disp['attachment']) {
					$header[] = sprintf($hstr, 'mixed', $boundary1);
					$body[] = '--' . $boundary1;
					$body[] = sprintf($hstr, 'related', $boundary2) . $end;
					$body[] = '--' . $boundary2;
					$body[] = sprintf($hstr, 'alternative', $boundary3) . $end;
					$body[] = sprintf($bstr, $boundary3, $text['header'], $text['content']);
					$body[] = sprintf($bstr, $boundary3, $html['header'], $html['content']);
					$body[] = '--' . $boundary3 . '--';
					foreach ($attach as $desc)
						if (self::isset_header($desc['header'], 'content-disposition', 'inline'))
							$body[] = sprintf($bstr, $boundary2, $desc['header'], $desc['content']);
					$body[] = '--' . $boundary2 . '--';
					foreach ($attach as $desc)
						if (self::isset_header($desc['header'], 'content-disposition', 'attachment'))
							$body[] = sprintf($bstr, $boundary1, $desc['header'], $desc['content']);
					$body[] = '--' . $boundary1 . '--';
				} else if ($disp['inline']) {
					$header[] = sprintf($hstr, 'related', $boundary1);
					$body[] = '--' . $boundary1;
					$body[] = sprintf($hstr, 'alternative', $boundary2) . $end;
					$body[] = sprintf($bstr, $boundary2, $text['header'], $text['content']);
					$body[] = sprintf($bstr, $boundary2, $html['header'], $html['content']);
					$body[] = '--' . $boundary2 . '--';
					foreach ($attach as $desc)
						$body[] = sprintf($bstr, $boundary1, $desc['header'], $desc['content']);
					$body[] = '--' . $boundary1 . '--';
				} else if ($disp['attachment']) {
					$header[] = sprintf($hstr, 'mixed', $boundary1);
					$body[] = '--' . $boundary1;
					$body[] = sprintf($hstr, 'alternative', $boundary2) . $end;
					$body[] = sprintf($bstr, $boundary2, $text['header'], $text['content']);
					$body[] = sprintf($bstr, $boundary2, $html['header'], $html['content']);
					$body[] = '--' . $boundary2 . '--';
					foreach ($attach as $desc)
						$body[] = sprintf($bstr, $boundary1, $desc['header'], $desc['content']);
					$body[] = '--' . $boundary1 . '--';
				} else {
					$header[] = sprintf($hstr, 'alternative', $boundary1);
					$body[] = sprintf($bstr, $boundary1, $text['header'], $text['content']);
					$body[] = sprintf($bstr, $boundary1, $html['header'], $html['content']);
					$body[] = '--' . $boundary1 . '--';
				}
			} else if ($text) {
				$header[] = sprintf($hstr, 'mixed', $boundary1);
				$body[] = sprintf($bstr, $boundary1, $text['header'], $text['content']);
				foreach ($attach as $desc)
					$body[] = sprintf($bstr, $boundary1, $desc['header'], $desc['content']);
				$body[] = '--' . $boundary1 . '--';
			} else if ($html) {
				if ($disp['inline'] && $disp['attachment']) {
					$header[] = sprintf($hstr, 'mixed', $boundary1);
					$body[] = '--' . $boundary1;
					$body[] = sprintf($hstr, 'related', $boundary2) . $end;
					$body[] = sprintf($bstr, $boundary2, $html['header'], $html['content']);
					foreach ($attach as $desc)
						if (self::isset_header($desc['header'], 'content-disposition', 'inline'))
							$body[] = sprintf($bstr, $boundary2, $desc['header'], $desc['content']);
					$body[] = '--' . $boundary2 . '--';
					foreach ($attach as $desc)
						if (self::isset_header($desc['header'], 'content-disposition', 'attachment'))
							$body[] = sprintf($bstr, $boundary1, $desc['header'], $desc['content']);
					$body[] = '--' . $boundary1 . '--';
				} else if ($disp['inline']) {
					$header[] = sprintf($hstr, 'related', $boundary1);
					$body[] = sprintf($bstr, $boundary1, $html['header'], $html['content']);
					foreach ($attach as $desc)
						$body[] = sprintf($bstr, $boundary1, $desc['header'], $desc['content']);
					$body[] = '--' . $boundary1 . '--';
				} else if ($disp['attachment']) {
					$header[] = sprintf($hstr, 'mixed', $boundary1);
					$body[] = sprintf($bstr, $boundary1, $html['header'], $html['content']);
					foreach ($attach as $desc)
						$body[] = sprintf($bstr, $boundary1, $desc['header'], $desc['content']);
					$body[] = '--' . $boundary1 . '--';
				}
			}
		} else {
			if ($text) {
				$header[] = $text['header'];
				$body[] = $text['content'];
			} else if ($html) {
				$header[] = $html['header'];
				$body[] = $html['content'];
			}
		}
		$header[] = 'MIME-Version: 1.0';
		return array('header' => implode($end, $header), 'content' => implode($end, $body));
	}

	/**
	 * @param string $str
	 * @param string $name
	 * @param mixed $value
	 * @return bool
	 */
	static public function isset_header($str = null, $name = null, $value = null) {
		if (!(is_string($str) && $str != '')) {
			AZMailerPostman::logThis("AZMailerMime::isset_header: invalid header type");
			return (false);
		}
		if (!(is_string($name) && strlen($name) > 1 && AZMailerPostmanHelper::is_alpha($name, true, '-'))) {
			AZMailerPostman::logThis("AZMailerMime::isset_header: invalid name type");
			return (false);
		}
		if ($value != null && !is_string($value)) {
			AZMailerPostman::logThis("AZMailerMime::isset_header: invalid value type");
			return (false);
		}
		$ret = false;
		if ( ($exp = self::split_header($str)) ) {
			foreach ($exp as $harr) {
				if (strtolower($harr['name']) == strtolower($name)) {
					if ($value != null)
						$ret = (strtolower($harr['value']) == strtolower($value)) ? $harr['value'] : false;
					else
						$ret = $harr['value'];
					if ($ret)
						break;
				}
			}
		}
		return $ret;
	}

	/**
	 * @param string $str
	 * @return array|bool
	 */
	static public function split_header($str = null) {
		if (!(is_string($str) && $str != '')) {
			AZMailerPostman::logThis("AZMailerMime::split_header: invalid header value");
			return (false);
		}
		$str = str_replace(array(";\r\n\t", "; \r\n\t", ";\r\n ", "; \r\n "), '; ', $str);
		$str = str_replace(array(";\n\t", "; \n\t", ";\n ", "; \n "), '; ', $str);
		$str = str_replace(array("\r\n\t", "\r\n "), '', $str);
		$str = str_replace(array("\n\t", "\n "), '', $str);
		$arr = array();
		foreach (explode("\n", $str) as $line) {
			$line = trim(AZMailerPostmanHelper::str_clear($line));
			if ($line != '') {
				if (count($exp1 = explode(':', $line, 2)) == 2) {
					$name = rtrim($exp1[0]);
					$val1 = ltrim($exp1[1]);
					if (strlen($name) > 1 && AZMailerPostmanHelper::is_alpha($name, true, '-') && $val1 != '') {
						$name = ucfirst($name);
						$exp2 = array();
						$hadd = array();
						if (substr(strtolower($name), 0, 8) == 'content-') {
							$exp2 = explode('; ', $val1);
							$cnt2 = count($exp2);
							if ($cnt2 > 1) {
								for ($i = 1; $i < $cnt2; $i++) {
									if (count($exp3 = explode('=', $exp2[$i], 2)) == 2) {
										$hset = trim($exp3[0]);
										$hval = trim($exp3[1], ' "');
										if ($hset != '' && $hval != '')
											$hadd[strtolower($hset)] = $hval;
									}
								}
							}
						}
						$val2 = (count($hadd)&&count($exp2)) ? trim($exp2[0]) : $val1;
						$arr[] = array('name' => $name, 'value' => $val2, 'content' => $hadd);
					}
				}
			}
		}
		if (count($arr) > 0) {
			return $arr;
		} else {
			AZMailerPostman::logThis("AZMailerMime::split_header: invalid header value");
			return (false);
		}
	}

	/**
	 * @param string $add
	 * @return string
	 */
	static public function unique($add = null) {
		return md5(microtime(true) . $add);
	}

	/**
	 * @param string $str
	 * @param array  $headers
	 * @param array  $body
	 * @return bool
	 */
	static public function split_mail($str = null, &$headers, &$body) {
		$headers = $body = false;
		if (!$part = self::split_message($str))
			return false;
		if (!$harr = self::split_header($part['header']))
			return false;
		$type = $boundary = false;
		foreach ($harr as $hnum) {
			if (strtolower($hnum['name']) == 'content-type') {
				$type = strtolower($hnum['value']);
				foreach ($hnum['content'] as $hnam => $hval) {
					if (strtolower($hnam) == 'boundary') {
						$boundary = $hval;
						break;
					}
				}
				if ($boundary)
					break;
			}
		}
		$headers = $harr;
		$body = array();
		if (substr($type, 0, strlen('multipart/')) == 'multipart/' && $boundary && strstr($part['content'], '--' . $boundary . '--'))
			$body = self::_parts($part['content'], $boundary, strtolower(substr($type, strlen('multipart/'))));
		if (count($body) == 0)
			$body[] = self::_content($str);
		return true;
	}

	/**
	 * @param string $str
	 * @return array|bool
	 */
	static public function split_message($str = null) {
		if (!(is_string($str) && $str != '')) {
			AZMailerPostman::logThis("AZMailerMime::split_message: invalid message value");
			return (false);
		}
		$ret = false;
		if (strpos($str, "\r\n\r\n")) {
			$ret = explode("\r\n\r\n", $str, 2);
		} else if (strpos($str, "\n\n")) {
			$ret = explode("\n\n", $str, 2);
		}
		if ($ret) {
			return array('header' => trim($ret[0]), 'content' => $ret[1]);
		} else {
			return false;
		}
	}

	/**
	 * @param string $str
	 * @param string $boundary
	 * @param string $multipart
	 * @return array
	 */
	static private function _parts($str = null, $boundary = null, $multipart = null) {
		if (!(is_string($str) && $str != '')) {
			AZMailerPostman::logThis("AZMailerMime::_parts: invalid content value");
			return (false);
		}
		if (!(is_string($boundary) && $boundary != '')) {
			AZMailerPostman::logThis("AZMailerMime::_parts: invalid boundary value");
			return (false);
		}
		if (!(is_string($multipart) && $multipart != '')) {
			AZMailerPostman::logThis("AZMailerMime::_parts: invalid multipart value");
			return (false);
		}
		$ret = array();
		if (count($exp = explode('--' . $boundary . '--', $str)) == 2) {
			if (count($exp = explode('--' . $boundary, $exp[0])) > 2) {
				$cnt = 0;
				foreach ($exp as $split) {
					$cnt++;
					if ($cnt > 1 && $part = self::split_message($split)) {
						if ( ($harr = self::split_header($part['header'])) ) {
							$type = $newb = false;
							foreach ($harr as $hnum) {
								if (strtolower($hnum['name']) == 'content-type') {
									$type = strtolower($hnum['value']);
									foreach ($hnum['content'] as $hnam => $hval) {
										if (strtolower($hnam) == 'boundary') {
											$newb = $hval;
											break;
										}
									}
									if ($newb)
										break;
								}
							}
							if (substr($type, 0, strlen('multipart/')) == 'multipart/' && $newb && strstr($part['content'], '--' . $newb . '--'))
								$ret = self::_parts($part['content'], $newb, $multipart . '|' . strtolower(substr($type, strlen('multipart/'))));
							else {
								$res = self::_content($split);
								$res['multipart'] = $multipart;
								$ret[] = $res;
							}
						}
					}
				}
			}
		}
		return $ret;
	}

	/**
	 * @param string $str
	 * @return array
	 */
	static private function _content($str = null) {
		if (!(is_string($str) && $str != '')) {
			AZMailerPostman::logThis("AZMailerMime::_content: invalid content value");
			return (false);
		}
		if (!$part = self::split_message($str))
			return null;
		if (!$harr = self::split_header($part['header']))
			return null;
		$body = array();
		$clen = strlen('content-');
		$encoding = false;
		foreach ($harr as $hnum) {
			if (substr(strtolower($hnum['name']), 0, $clen) == 'content-') {
				$name = strtolower(substr($hnum['name'], $clen));
				if ($name == 'transfer-encoding')
					$encoding = strtolower($hnum['value']);
				else if ($name == 'id')
					$body[$name] = array('value' => trim($hnum['value'], '<>'), 'extra' => $hnum['content']);
				else
					$body[$name] = array('value' => $hnum['value'], 'extra' => $hnum['content']);
			}
		}
		if ($encoding == 'base64' || $encoding == 'quoted-printable')
			$body['content'] = self::decode_content($part['content'], $encoding);
		else {
			if ($encoding)
				$body['transfer-encoding'] = $encoding;
			$body['content'] = $part['content'];
		}
		if (substr($body['content'], -2) == "\r\n")
			$body['content'] = substr($body['content'], 0, -2);
		else if (substr($body['content'], -1) == "\n")
			$body['content'] = substr($body['content'], 0, -1);
		return $body;
	}

	/**
	 * @param string $str
	 * @param string $encoding
	 * @return bool|null|string
	 */
	static public function decode_content($str = null, $encoding = null) {
		if (!is_string($str)) {
			AZMailerPostman::logThis("AZMailerMime::decode_content: invalid content type");
			return (false);
		}
		if ($encoding == null) {
			$encoding = '7bit';
		} else if (!is_string($encoding)) {
			AZMailerPostman::logThis("AZMailerMime::decode_content: invalid encoding type");
			return (false);
		} else {
			$encoding = strtolower($encoding);
			if (!isset(self::$mencarr[$encoding])) {
				AZMailerPostman::logThis("AZMailerMime::decode_content: invalid encoding value");
				return (false);
			}
		}
		if ($encoding == 'base64') {
			$str = trim(AZMailerPostmanHelper::str_clear($str));
			return base64_decode($str);
		} else if ($encoding == 'quoted-printable') {
			return quoted_printable_decode($str);
		} else {
			return $str;
		}
	}

}
