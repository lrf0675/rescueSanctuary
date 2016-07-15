<?php
namespace AZMailer\Core;

use AZMailer\Helpers\AZMailerMimeHelper;
use AZMailer\Helpers\AZMailerPostmanHelper;
use AZMailer\Helpers\AZMailerSubscriberHelper;

/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 **/
defined('_JEXEC') or die('RESTRICTED');

/**
 * Class AZMailerPostman
 */
class AZMailerPostman {
	const CRLF = "\r\n";
	const BLEN = 1024; //unknown error
	static private $logs = array();
	private $resultcode = 999; //this is the Mail Server Connection resource
	private $resultmsg = "Unknown error"; //mx hosts array
	private $mxc = null;
	private $mxhosts = array();
	private $mx_connection_timeout = 5;
	private $ACCEPTABLEMXCODES = array("220", "250", "354");
	private $php_connection_methods = array("stream_socket_client", "fsockopen");
	private $smtp_usable_methods = array();
	private $MDATA = array(
		"helo" => "",
		"xmailer" => "",
		"messageid" => "",
		"from" => "",
		"fromname" => "",
		"to" => "",
		"returnpath" => "",
		"subject" => "",
		"text" => "",
		"html" => "",
		"headers" => array(),
		"attachments" => array()
	);

	/**
	 * @param \stdClass $qs
	 */
	public function __construct($qs) {
		self::$logs = array();//this is static so we must clean it on new instance
		$this->logThis('AZMailerPostman: ' . Date("y-m-d G:i.s"));
		$this->setup($qs);
	}

	/**
	 * @param string $msg
	 * @param bool $cleanupSpecialChars
	 */
	public static function logThis($msg, $cleanupSpecialChars = true) {
		if (!empty($msg)) {
			if ($cleanupSpecialChars) {
				$msg = htmlspecialchars($msg);
			}
			self::$logs[] = $msg;
		}
	}

	//------------------------------------------------------------------------------MAIN PUBLIC FUNCTIONS
	/**
	 * @param \stdClass $qs
	 */
	private function setup($qs = null) {
		if ($qs) {
			//map quick setup date to MDATA
			foreach ($qs as $sk => $sv) {
				if (isset($this->MDATA[$sk])) {
					if (method_exists($this, "set_" . $sk)) {
						call_user_func_array(array($this, "set_" . $sk), array($sv));
					}
				}
			}
			$this->smtp_usable_methods = $this->smtp_get_connection_methods();
		}
	}

	/**
	 * Returns an array of usable(non disabled) functions ("stream_socket_client", "fsockopen")
	 * @return array
	 */
	private function smtp_get_connection_methods() {
		$answer = array();
		$php_disabled_functions = ini_get('disable_functions');
		foreach ($this->php_connection_methods as $phpfunc) {
			if (!preg_match('/' . $phpfunc . '/', $php_disabled_functions)) {
				if (function_exists($phpfunc)) {
					array_push($answer, $phpfunc);
				}
			}
		}
		return ($answer);
	}

	//------------------------------------------------------------------------------SETTERS
	/**
	 * @param string $key
	 * @return mixed|bool
	 */
	public function getMailData($key) {
		if (isset($this->MDATA[$key])) {
			return ($this->MDATA[$key]);
		}
		return (false);
	}

	/**
	 * The main send method
	 * @return bool
	 */
	public function sendMail() {
		if (!$this->check_subject()) {
			$this->setError(911, "sendMail[%s]: Mail subject is not set!");
			return (false);
		}
		if (!$this->check_text() || !$this->check_html()) {
			$this->setError(912, "sendMail[%s]: Mail message (text/html) is not set!");
			return (false);
		}
		if (!$this->controlRecipientMailAddress(true)) {
			$this->logThis("sendMail[$this->resultcode]: Mail recipient check failed!");
			$this->smtp_disconnect($this->mxc);
			return (false);
		}
		$messageBody = AZMailerPostmanHelper::composeMessageData($this->MDATA);
		if (!$messageBody) {
			$this->setError(913, "sendMail[%s]: Mail compose failed!");
			$this->smtp_disconnect($this->mxc);
			return (false);
		}

		$this->logThis("sendMail: sending mail to recipient(" . strlen($messageBody) . " bytes)...");
		$res = $this->smtp_send($this->mxc, $messageBody);
		$this->smtp_disconnect($this->mxc);
		return ($res);
	}

	/**
	 * @param integer $errNum
	 * @param string $errMsg
	 * @return integer
	 */
	private function setError($errNum, $errMsg) {
		$this->resultcode = $errNum; //last server error code
		$this->resultmsg = htmlspecialchars(sprintf($errMsg, $errNum));
		$errMsg = '<font style="color:#ff0000">' . $this->resultmsg . '</font>';
		$this->logThis($errMsg, false);
		return ($errNum);
	}

	/**
	 * @param string $subject
	 * @return bool|string
	 */
	private function check_subject($subject = null) {
		if (!$subject) {
			$subject = $this->MDATA["subject"];
		}
		$subject = trim($subject);
		if (empty($subject)) {
			$this->logThis("checkSubject: Subject must not be empty!");
			return (false);
		}
		return ($subject);
	}

	/**
	 * @param string $text
	 * @return bool|string
	 */
	private function check_text($text = null) {
		if (!$text) {
			$text = $this->MDATA["text"];
		}
		if (empty($text)) {
			$this->logThis("checkText: Text content is empty!");
			return (false);
		}
		return ($text);
	}

	/**
	 * @param string $html
	 * @return bool|string
	 */
	private function check_html($html = null) {
		if (!$html) {
			$html = $this->MDATA["html"];
		}
		if (empty($html)) {
			$this->logThis("checkHtml: Html content is empty!");
			return (false);
		}
		return ($html);
	}

	/**
	 * @param bool $keepConnection - if true, connection to server will not be destroyed after check(so it can be re-used)
	 * @return bool
	 */
	public function controlRecipientMailAddress($keepConnection = false) {
		if (!$this->check_helo()) {
			$this->setError(908, "Postman Check[%s]: Undefined parameter HELO - unable continue!");
			return (false);
		}
		if (!$this->check_from()) {
			$this->setError(909, "Postman Check[%s]: Undefined parameter FROM - unable continue!");
			return (false);
		}
		if (!$this->check_to()) {
			$this->setError(909, "Postman Check[%s]: Undefined parameter TO - unable continue!");
			return (false);
		}

		if ($this->getConnectionResource()) {
			$this->logThis("Checking email account(" . $this->MDATA["to"] . ")...");
			$goAhead = true;

			//FROM
			if ($goAhead) {
				if (!$this->smtp_command($this->mxc, "MAIL FROM:<" . $this->MDATA["from"] . ">")) {
					$goAhead = false;
				} else if (!in_array($this->smtp_read_from_handle($this->mxc), $this->ACCEPTABLEMXCODES)) {
					$goAhead = false;
				}
			}

			//TO
			if ($goAhead) {
				if (!$this->smtp_command($this->mxc, "RCPT TO:<" . $this->MDATA["to"] . ">")) {
					$goAhead = false;
				} else if (!in_array($this->smtp_read_from_handle($this->mxc), $this->ACCEPTABLEMXCODES)) {
					$goAhead = false;
				}
			}

			if (!$keepConnection) {
				$this->smtp_disconnect($this->mxc);
			}
			//ANSWER
			return ($goAhead);
		} else {
			return (false);
		}
	}

	/**
	 * @param string $helo
	 * @return bool|string
	 */
	private function check_helo($helo = null) {
		if (!$helo) {
			$helo = $this->MDATA["helo"];
		}
		$helo = strtolower(trim($helo));
		if (empty($helo)) {
			$this->logThis("checkHelo: Helo is empty!");
			return (false);
		}
		if (!is_string($helo)) {
			$this->logThis("checkHelo: Helo is not a string!");
			return (false);
		}
		if (!AZMailerPostmanHelper::is_valid_hostname($helo)) {
			$this->logThis("checkHelo: Helo[$helo] is not a valid hostname/ipv4!");
			return (false);
		}
		return ($helo);
	}

	/**
	 * @param string $from
	 * @return bool|string
	 */
	private function check_from($from = null) {
		if (!$from) {
			$from = $this->MDATA["from"];
		}
		$from = strtolower(trim($from));
		if (!AZMailerSubscriberHelper::checkIfEmailSyntaxIsValid($from)) {
			$this->logThis("checkFrom: From[$from] is not a semantically valid e-mail address!");
			return (false);
		}
		return ($from);
	}

	/**
	 * @param string $to
	 * @return bool|string
	 */
	private function check_to($to = null) {
		if (!$to) {
			$to = $this->MDATA["to"];
		}
		$to = strtolower(trim($to));
		if (!AZMailerSubscriberHelper::checkIfEmailSyntaxIsValid($to)) {
			$this->logThis("checkFrom: To[$to] is not a semantically valid e-mail address!");
			return (false);
		}
		return ($to);
	}

	/**
	 * @return bool|resource
	 */
	private function getConnectionResource() {
		if (!$this->mxc) {
			if ( ($this->mxhosts = $this->getMXHosts()) ) {
				$this->mxc = $this->getMXConnectionResource($this->mxhosts);
				if (!$this->mxc) {
					$this->logThis("getConnectionResource[$this->resultcode] - All MX hosts failed - no available connection!");
				}
			} else {
				$this->setError(911, "getConnectionResource[%s] - No available MX host to use!");
			}
		}
		return ($this->mxc?$this->mxc:false);
	}

	/**
	 * @return array|bool
	 */
	private function getMXHosts() {
		$answer = false;
		if (!empty($this->MDATA["to"])) {
			list($username, $domain) = explode('@', $this->MDATA["to"]);
			$this->logThis("getMXHosts: checking on domain: $domain");
			if (!@getmxrr($domain, $mx_records, $mx_weight)) {
				$mxs = array();
				$mxs[] = $domain;
				$this->logThis("getMXHosts: no MX hosts - will use domain name: $domain");
			} else {
				// Put the records together in a array we can sort them by weight
				for ($i = 0; $i < count($mx_records); $i++) {
					$mxs[$mx_records[$i]] = $mx_weight[$i];
				}
				asort($mxs);
				$mxs = array_keys($mxs);
				//$this->logThis("getMXHosts: MX hosts found: " . implode(", ", $answer));
			}
			//
			$answer = array();
			foreach ($mxs as $mx) {
				if (AZMailerPostmanHelper::is_valid_hostname($mx)) {
					$this->logThis("getMXHosts: found valid MX host: $mx");
					array_push($answer, $mx);
				} else {
					$this->logThis("getMXHosts: removing invalid MX host: $mx");
				}
			}
			if (!count($answer)) {
				$answer = false;
			}
		}
		return ($answer);
	}


	//------------------------------------------------------------------------------CHECKERS (return correct data to set or FALSE if incorrect)

	/**
	 * @param array $mxhosts
	 * @return bool|resource
	 */
	private function getMXConnectionResource($mxhosts) {
		$handle = false;
		foreach ($mxhosts as $mxhost) {
			$handle = $this->smtp_connect($mxhost, 25);
			if ($handle) {
				if (!$this->smtp_command($handle, "HELO " . $this->MDATA["helo"])) {
					$this->smtp_disconnect($handle);
					$handle = false;
				} else if (!in_array($this->smtp_read_from_handle($handle), $this->ACCEPTABLEMXCODES)) {
					$this->smtp_disconnect($handle);
					$handle = false;
				} else {
					break;
				}
			}
		}
		return ($handle);
	}

	/**
	 * @param string $host
	 * @param integer $port
	 * @return bool|resource
	 */
	private function smtp_connect($host, $port) {
		$handle = false;
		if (count($this->smtp_usable_methods) > 0) {
			try {
				foreach ($this->smtp_usable_methods as $connectionMethod) {
					$logMsg = 'SMTP(CONN)(' . $connectionMethod . ') - connecting to MX host: ' . $host . ':' . $port . '...';
					switch ($connectionMethod) {
						case 'stream_socket_client':
							$errno = 0;
							$errstr = "";
							$handle = @stream_socket_client('tcp://' . $host . ':' . $port, $errno, $errstr, $this->mx_connection_timeout);
							break;
						case 'fsockopen':
							$errno = 0;
							$errstr = "";
							$handle = @fsockopen($host, $port, $errno, $errstr, $this->mx_connection_timeout);
							break;
						default:
							$errno = 127;
							$errstr = "Unknown connection method($connectionMethod)!";
					}
					if (!$handle) {
						$this->setError($errno, $logMsg . "FAILED[%s] - " . $errstr);
					} else {
						$this->logThis($logMsg);
						$rcode = $this->smtp_read_from_handle($handle);
						if ($rcode == 220) {
							break;
						}
					}
				}
			} catch (\Exception $e) {
				$this->setError(950, "SMTP(CONN)[%s] - unexpected connection error - " . $e->getMessage());
				return (false);
			}
		} else {
			$this->setError(951, "SMTP(CONN)[%s] - Server does NOT support any of these connection methods: " . implode(", ", $this->php_connection_methods));
			return (false);
		}
		return $handle;
	}

	/**
	 * @param resource $handle
	 * @return int - the smtp server response code
	 */
	private function smtp_read_from_handle($handle) {
		if ($handle && is_resource($handle)) {
			$resp = array();
			do {
				if ( ($response = fgets($handle, self::BLEN)) ) {
					$resp[] = $response;
					preg_match("/^([0-9]{3})/", $response, $matches);
					if ($matches[1]) {
						$rcode = (int)$matches[1];
					} else {
						$rcode = $this->setError(996, "SMTP(R)[%s] - cannot determin exit code from: " . $response);
					}
				} else {
					$rcode = $this->setError(997, "SMTP(R)[%s] - cannot read from resource!");
					break;
				}
			} while ($response[3] == '-');
			if (!in_array($rcode, $this->ACCEPTABLEMXCODES) && !in_array($rcode, array("996", "997"))) {
				$this->setError($rcode, "SMTP(R)[%s] - " . implode(" : ", $resp));
			} else {
				$this->logThis("SMTP(R)[$rcode] - " . implode(" : ", $resp));
				$this->resultcode = $rcode; //last server error code
			}
		} else {
			$rcode = $this->setError(990, "SMTP(R)[%s] - no connection resource handle!");
		}
		return ($rcode);
	}

	/**
	 * @param resource $handle
	 * @param string $command
	 * @param bool $traceCommand
	 * @return bool
	 */
	private function smtp_command($handle, $command, $traceCommand = true) {
		$answer = false;
		if ($handle) {
			if (!fwrite($handle, $command . self::CRLF)) {
				$this->setError(998, "SMTP(W)[%s]>" . $command . " - cannot write to resource!");
			} else {
				if ($traceCommand) {
					$this->logThis("SMTP(W)> " . $command);
				}
				$answer = true;
			}
		} else {
			$this->setError(990, "SMTP(W)[%s] - no connection resource handle!");
		}
		return ($answer);
	}

	/**
	 * @param resource $handle
	 */
	private function smtp_disconnect($handle) {
		if ($handle) {
			if (!fwrite($handle, 'QUIT' . self::CRLF)) {
				$this->logThis("smtp_disconnect - Unable to QUIT to resource!");
			}
			fclose($handle);
			$this->mxc = null;
			$this->logThis("Disconnected.");
		}
	}

	/**
	 * Break up message into lines and pump it to the smtp server
	 * @param resource $handle
	 * @param string $mess
	 * @return bool
	 */
	private function smtp_send($handle, $mess = null) {
		$answer = false;
		if ($this->smtp_command($handle, "DATA")) {
			if ($this->smtp_read_from_handle($handle) == 354) {
				$continue = true;
				$lines = explode(self::CRLF, $mess);
				$lineCount = count($lines);
				foreach ($lines as $line) {
					if ($line != '' && $line[0] == '.') {
						$line = '.' . $line;
					}
					if (!$this->smtp_command($handle, $line, false)) {
						$continue = false;
						$this->logThis("smtp_send[$this->resultcode] - cannot write line to resource!");
						break;
					}
				}
				if ($continue) {
					$this->logThis("smtp_send - piped $lineCount lines - ok");
					if (!$this->smtp_command($handle, self::CRLF . '.')) {
						$this->logThis("smtp_send[$this->resultcode] - cannot close DATA connection!");
					} else if (!in_array($this->smtp_read_from_handle($handle), $this->ACCEPTABLEMXCODES)) {
						$this->logThis("smtp_send[$this->resultcode] - cannot close DATA connection!");
					} else {
						$this->logThis("smtp_send - DONE!");
						$answer = true;
					}
				}


			} else {
				$this->logThis("smtp_send[$this->resultcode] - DATA command failed!");
			}
		} else {
			$this->logThis("smtp_send[$this->resultcode] - DATA command failed!");
		}
		return ($answer);
	}

	/**
	 * @param string $helo
	 * @return bool
	 */
	public function set_helo($helo = null) {
		$helo = $this->check_helo($helo);
		if ($helo) {
			$this->MDATA["helo"] = $helo;
			$this->logThis('SET: "HELO/EHLO" = "' . $helo . '"');
			return (true);
		} else {
			return (false);
		}
	}

	/**
	 * @param string $xmailer
	 * @return bool
	 */
	public function set_xmailer($xmailer = null) {
		$xmailer = $this->check_xmailer($xmailer);
		if ($xmailer) {
			$this->MDATA["xmailer"] = $xmailer;
			$this->logThis('SET: "X-MAILER" = "' . $xmailer . '"');
			return (true);
		} else {
			return (false);
		}
	}

	/**
	 * @param string $xmailer
	 * @return bool|string
	 */
	private function check_xmailer($xmailer = null) {
		if (!$xmailer) {
			$xmailer = $this->MDATA["xmailer"];
		}
		$xmailer = trim($xmailer);
		if (empty($xmailer)) {
			$this->logThis("check_xmailer: XMailer is empty!");
			return (false);
		}
		if (!is_string($xmailer)) {
			$this->logThis("check_xmailer: XMailer is not a string!");
			return (false);
		}
		return ($xmailer);
	}

	/**
	 * @param string $messageid
	 * @return bool
	 */
	public function set_messageid($messageid = null) {
		$messageid = $this->check_messageid($messageid);
		if ($messageid) {
			$this->MDATA["messageid"] = $messageid;
			$this->logThis('SET: "MESSAGE-ID" = "' . $messageid . '"');
			return (true);
		} else {
			return (false);
		}
	}

	/**
	 * @param string $messageid
	 * @return bool|string
	 */
	private function check_messageid($messageid = null) {
		if (!$messageid) {
			$messageid = $this->MDATA["messageid"];
		}
		$messageid = strtolower(trim($messageid));
		if (empty($messageid)) {
			$this->logThis("check_messageid: MessageID is empty!");
			return (false);
		}
		if (!is_string($messageid)) {
			$this->logThis("check_messageid: MessageID is not a string!");
			return (false);
		}
		if ($messageid[0] != '@') {
			$this->logThis("check_messageid: MessageID must start with the at('@') character!");
			return (false);
		}
		return ($messageid);
	}

	/**
	 * @param string $from
	 * @return bool
	 */
	public function set_from($from = null) {
		$from = $this->check_from($from);
		if ($from) {
			$this->MDATA["from"] = $from;
			$this->logThis('SET: "FROM" = "' . $from . '"');
			return (true);
		} else {
			return (false);
		}
	}


	//------------------------------------------------------------------------------OTHER PUBLIC FUNCTIONS
	/**
	 * @param string $fromname
	 * @return bool
	 */
	public function set_fromname($fromname = null) {
		$fromname = $this->check_fromname($fromname);
		if ($fromname) {
			$this->MDATA["fromname"] = $fromname;
			$this->logThis('SET: "FROMNAME" = "' . $fromname . '"');
			return (true);
		} else {
			return (false);
		}
	}

	/**
	 * @param string $fromname
	 * @return bool|string
	 */
	private function check_fromname($fromname = null) {
		if (!$fromname) {
			$fromname = $this->MDATA["fromname"];
		}
		$fromname = AZMailerPostmanHelper::str_clear(trim($fromname));
		if (empty($fromname)) {
			$this->logThis("check_fromname: FromName is empty!");
			return (false);
		}
		if (!is_string($fromname)) {
			$this->logThis("check_fromname: FromName is not a string!");
			return (false);
		}
		return ($fromname);
	}

	/**
	 * @param string $to
	 * @return bool
	 */
	public function set_to($to = null) {
		$to = $this->check_to($to);
		if ($to) {
			$this->MDATA["to"] = $to;
			$this->logThis('SET: "TO" = "' . $to . '"');
			return (true);
		} else {
			return (false);
		}
	}

	/**
	 * @param string $returnpath
	 * @return bool
	 */
	public function set_returnpath($returnpath = null) {
		$returnpath = $this->check_returnpath($returnpath);
		if ($returnpath) {
			$this->MDATA["returnpath"] = $returnpath;
			$this->logThis('SET: "RETURNPATH" = "' . $returnpath . '"');
			return (true);
		} else {
			return (false);
		}
	}


	/**
	 * @param string $returnpath
	 * @return bool|string
	 */
	private function check_returnpath($returnpath = null) {
		if (!$returnpath) {
			$returnpath = $this->MDATA["returnpath"];
		}
		$returnpath = strtolower(trim($returnpath));
		if (!AZMailerSubscriberHelper::checkIfEmailSyntaxIsValid($returnpath)) {
			$this->logThis("checkReturnpath: Returnpath[$returnpath] is not a semantically valid e-mail address!");
			return (false);
		}
		return ($returnpath);
	}

	/**
	 * @param string $subject
	 * @return bool
	 */
	public function set_subject($subject = null) {
		$subject = $this->check_subject($subject);
		if ($subject) {
			$this->MDATA["subject"] = $subject;
			$this->logThis('SET: "SUBJECT" = "' . $subject . '"');
			return (true);
		} else {
			return (false);
		}
	}

	/**
	 * @param string $text
	 * @return bool
	 */
	public function set_text($text = null) {
		$text = $this->check_text($text);
		if ($text) {
			$this->MDATA["text"] = $text;
			$this->logThis('SET: "TEXT" => OK(length:' . strlen($text) . ' bytes)');
			return (true);
		} else {
			return (false);
		}
	}

	/**
	 * @param string $html
	 * @return bool
	 */
	public function set_html($html = null) {
		$html = $this->check_html($html);
		if ($html) {
			$this->MDATA["html"] = $html;
			$this->logThis('SET: "HTML" => OK(length:' . strlen($html) . ' bytes)');
			return (true);
		} else {
			return (false);
		}
	}

	/**
	 * @param array $headers
	 * @return bool
	 */
	public function set_headers($headers = null) {
		$headers = $this->check_headers($headers);
		if ($headers) {
			$this->MDATA["headers"] = $headers;
			foreach ($headers as $HN => $HV) {
				$this->logThis('SET HEADER: "' . $HN . '" = "' . $HV . '"');
			}
			return (true);
		} else {
			return (false);
		}
	}

	/**
	 * @param array $headers
	 * @return array|bool
	 */
	private function check_headers($headers = null) {
		if (!$headers) {
			$headers = $this->MDATA["headers"];
		}
		if (!is_array($headers)) {
			$this->logThis("checkHeaders: Headers must be an array!");
			return (false);
		}
		if (!count($headers)) {
			$this->logThis("checkHeaders: the supplied Headers array is empty!");
			return (false);
		}
		$validHeaders = array();
		foreach ($headers as $HK => $HV) {
			$header = $this->check_header($HK, $HV);
			if ($header) {
				$validHeaders[$header->name] = $header->value;
			}
		}
		if (!count($validHeaders)) {
			$this->logThis("checkHeaders: the validated Headers array is empty!");
			return (false);
		}
		return ($validHeaders);
	}

	/**
	 * @param string $headerName
	 * @param string $headerValue
	 * @return bool|\stdClass
	 */
	private function check_header($headerName = null, $headerValue = null) {
		//header name
		if (!is_string($headerName)) {
			$this->logThis("checkHeader: HeaderName must be a string!");
			return (false);
		}
		$headerName = ucfirst(strtolower(trim(str_replace(array("\r", "\n", "\t"), '', $headerName))));
		if (strlen($headerName) < 2) {
			$this->logThis("checkHeader: HeaderName[$headerName] is too short!");
			return (false);
		}
		//header value
		if (!is_string($headerValue)) {
			$this->logThis("checkHeader: HeaderValue must be a string!");
			return (false);
		}
		$headerValue = trim(str_replace(array("\r", "\n", "\t"), '', $headerValue));
		//header name exclusions
		if (strtolower($headerName) == 'from') {
			$this->logThis("checkHeader: HeaderName From will be set automatically!");
			return (false);
		}
		if (strtolower($headerName) == 'to') {
			$this->logThis("checkHeader: HeaderName To will be set automatically!");
			return (false);
		}
		if (strtolower($headerName) == 'cc') {
			$this->logThis("checkHeader: HeaderName Cc will be set automatically!");
			return (false);
		}
		if (strtolower($headerName) == 'bcc') {
			$this->logThis("checkHeader: HeaderName Bcc will be set automatically!");
			return (false);
		}
		if (strtolower($headerName) == 'subject') {
			$this->logThis("checkHeader: HeaderName Subject will be set automatically!");
			return (false);
		}
		if (strtolower($headerName) == 'x-priority') {
			$this->logThis("checkHeader: HeaderName X-priority will be set automatically!");
			return (false);
		}
		if (strtolower($headerName) == 'x-msmail-priority') {
			$this->logThis("checkHeader: HeaderName X-msmail-priority will be set automatically!");
			return (false);
		}
		if (strtolower($headerName) == 'x-mimeole') {
			$this->logThis("checkHeader: HeaderName X-mimeole will be set automatically!");
			return (false);
		}
		if (strtolower($headerName) == 'date') {
			$this->logThis("checkHeader: HeaderName Date will be set automatically!");
			return (false);
		}
		if (strtolower($headerName) == 'content-type') {
			$this->logThis("checkHeader: HeaderName Content-type will be set automatically!");
			return (false);
		}
		if (strtolower($headerName) == 'content-transfer-encoding') {
			$this->logThis("checkHeader: HeaderName Content-transfer-encoding will be set automatically!");
			return (false);
		}
		if (strtolower($headerName) == 'content-disposition') {
			$this->logThis("checkHeader: HeaderName Content-disposition will be set automatically!");
			return (false);
		}
		if (strtolower($headerName) == 'mime-version') {
			$this->logThis("checkHeader: HeaderName Mime-version will be set automatically!");
			return (false);
		}
		if (strtolower($headerName) == 'x-mailer') {
			$this->logThis("checkHeader: HeaderName X-mailer will be set automatically!");
			return (false);
		}
		if (strtolower($headerName) == 'message-id') {
			$this->logThis("checkHeader: HeaderName Message-id will be set automatically!");
			return (false);
		}
		//ok
		$header = new \stdClass();
		$header->name = $headerName;
		$header->value = $headerValue;
		return ($header);
	}

	/**
	 * @param string $file
	 * @param string $fileName
	 * @param string $disposition
	 * @param string $uid
	 * @return bool
	 */
	public function add_attachment($file = null, $fileName = null, $disposition = null, $uid = null) {
		if (!file_exists($file)) {
			$this->logThis("add_attachment: File does not exist! - $file");
			return (false);
		}
		if (!$FC = file_get_contents($file)) {
			$this->logThis("add_attachment: Error getting file contents! - $file");
			return (false);
		}
		if (!$MT = AZMailerPostmanHelper::mime_type($file)) {
			$this->logThis("add_attachment: Error getting file mime-type! - $file");
			return (false);
		}
		if (empty($fileName)) {
			$FI = pathinfo($file);
			$fileName = $FI["basename"];
		}
		$fileName = trim(AZMailerPostmanHelper::str_clear($fileName));
		if (!$fileName) {
			$this->logThis("add_attachment: Invalid or missing attachment fileName");
			return (false);
		}
		if ($disposition == null) {
			$disposition = 'attachment';
		}
		if (!in_array($disposition, array('attachment', 'inline'))) {
			$this->logThis("add_attachment: Invalid disposition value! - $disposition");
			return (false);
		}
		if (empty($uid)) {
			$uid = AZMailerMimeHelper::unique();
		}
		$uid = AZMailerPostmanHelper::str_clear($uid, array(" "));
		if (!$uid) {
			$this->logThis("add_attachment: Invalid or missing attachment unique identifier");
			return (false);
		}
		//
		$charset = null;
		$encoding = "base64";
		//
		$att = array('content' => $FC, 'type' => $MT, 'name' => $fileName, 'charset' => $charset, 'encoding' => $encoding, 'disposition' => $disposition, 'id' => $uid);
		array_push($this->MDATA["attachments"], $att);
		$this->logThis('ADD: "ATTACHMENT(' . $disposition . ')" => OK: ' . "Type($MT), Name($fileName), Uid($uid)");
		return (true);
	}

	/**
	 * @return array
	 */
	public function getLogs() {
		return (self::$logs);
	}

	/**
	 * @return int
	 */
	public function getResultCode() {
		return ($this->resultcode);
	}

	/**
	 * @return string
	 */
	public function getResultMessage() {
		return ($this->resultmsg);
	}
}
