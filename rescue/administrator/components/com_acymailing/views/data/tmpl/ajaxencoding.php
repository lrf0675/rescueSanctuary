<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	4.8.0
 * @author	acyba.com
 * @copyright	(C) 2009-2014 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>	<legend><?php echo JText::_('ACY_MATCH_DATA'); ?></legend>
<?php
	$config = acymailing_config();
	$encodingHelper = acymailing_get('helper.encoding');

	$filename = strtolower(JRequest::getCmd('filename'));

	jimport('joomla.filesystem.file');
	$extension = '.'.JFile::getExt($filename);
	$uploadPath = ACYMAILING_MEDIA.'import'.DS.str_replace(array('.',' '), '_', substr($filename, 0, strpos($filename, $extension))).$extension;

	if(file_exists($uploadPath)){
		$this->content = file_get_contents($uploadPath);
		$encoding = JRequest::getCmd('encoding');
		if(empty($encoding)){
			$encoding = $encodingHelper->detectEncoding($this->content);
		}
		$content = $encodingHelper->change($this->content, $encoding, 'UTF-8');
	}else{
		acymailing_display(JText::sprintf( 'FAIL_OPEN','<b><i>'.htmlspecialchars($uploadPath, ENT_COMPAT, 'UTF-8').'</i></b>'), 'error');
		return;
	}

	$content = str_replace(array("\r\n","\r"), "\n", $content);
	$this->lines = explode("\n", $content);
	$nbPreviewLines = 0;
	foreach($this->lines as $i => $oneLine){
		if(empty($oneLine)) unset($this->lines[$i]);
		else $nbPreviewLines++;

		if($nbPreviewLines == 10) break;
	}
	$this->lines = array_values($this->lines);
	$nbLines = count($this->lines);
	if(empty($this->lines[$nbLines-1])){
		unset($this->lines[$nbLines-1]);
		$nbLines = count($this->lines);
	}

?>
	<table class="adminlist" cellspacing="10" cellpadding="10" align="center" id="importdata">
		<?php
			if(strpos($this->lines[0], '@')) $firstLine = 1;
			else $firstLine = 0;

			$this->lines[0] = trim(str_replace(array('";"', '","', ';', ','), '</td><td>', $this->lines[0]), '"');
			$columns = '<tr><td>'.$this->lines[0].'</td></tr>';
			$columnNames = explode('</td><td>', $this->lines[0]);
			$nbColumns = count($columnNames);
			if($firstLine){
				$secondColumn = $columnNames;
			}else{
				if(!empty($this->lines[1])){
					$this->lines[1] = trim(str_replace(array('";"', '","', ';', ','), '</td><td>', $this->lines[1]), '"');
					$secondColumn = explode('</td><td>', $this->lines[1]);
				}
			}

			$fieldAssignment = array();
			$fieldAssignment[] = JHTML::_('select.option', "0", '- - -');
			$fieldAssignment[] = JHTML::_('select.option', "1", JText::_('ACY_IGNORE'));
			$createField = JHTML::_('select.option', "2", JText::_('ACY_CREATE_FIELD'));
			if(!acymailing_level(3)){
				$createField->disable = true;
				$createField->text .= ' ('.JText::_('ONLY_FROM_ENTERPRISE').')';
			}
			$fieldAssignment[] = $createField;
			$separator = JHTML::_('select.option', "3", '______________________________');
			$separator->disable = true;
			$fieldAssignment[] = $separator;

			$fields = array_keys(acymailing_getColumns('#__acymailing_subscriber'));
			$fields[] = 'listids';

			foreach($fields as $oneField){
				$fieldAssignment[] = JHTML::_('select.option', $oneField, $oneField);
			}

			$fields[] = '1';

			echo '<tr class="row0"><td align="center" valign="top"><strong>'.acymailing_tooltip(JText::_('ACY_ASSIGN_COLUMNS_DESC'), null, null, JText::_('ACY_ASSIGN_COLUMNS')).'</strong></td>';
			$alreadyFound = array();
			foreach($columnNames as $key => $oneColumn){
				$customValue = '';
				$default = JRequest::getCmd('fieldAssignment'.$key);
				if(empty($default) && $default !== 0){
					$default = (in_array($oneColumn, $fields) ? $oneColumn : '0');
					if(!$default && !empty($secondColumn)){
						if(strpos($secondColumn[$key], '@')) $default = 'email';
						elseif($nbColumns == 2) $default = 'name';
					}
					if(in_array($default, $alreadyFound)) $default = '0';
					$alreadyFound[] = $default;
				}elseif($default == 2){
					$customValue = JRequest::getCmd('newcustom'.$key);
				}
				echo '<td valign="top">'.JHTML::_('select.genericlist', $fieldAssignment, 'fieldAssignment'.$key , 'size="1" onchange="checkNewCustom('.$key.')" style="width:180px;"', 'value', 'text', $default).'<br />';
				if(empty($customValue)){
					echo '<input style="display:none;width:170px;" type="text" id="newcustom'.$key.'" name="newcustom" placeholder="'.JText::_('FIELD_COLUMN').'..."/></td>';
				}else{
					echo '<input style="width:170px;" value="'.$customValue.'" required type="text" id="newcustom'.$key.'" name="newcustom" placeholder="'.JText::_('FIELD_COLUMN').'..."/></td>';
				}
			}
			echo '</tr>';

			if(!$firstLine){
				foreach($columnNames as &$oneColumn){
					$oneColumn = htmlspecialchars($oneColumn, ENT_COMPAT | ENT_IGNORE, 'UTF-8');
				}
				echo '<tr class="row1"><td align="center"><strong>'.JText::_('ACY_IGNORE_LINE').'</strong></td><td align="center">['.implode(']</td><td align="center">[', $columnNames).']</td></tr>';
			}

			for($i = 1-$firstLine ; $i < 11-$firstLine && $i < $nbLines ; $i++){
				$line = trim(str_replace(array('";"', '","', ';', ','), '</td><td>', $this->lines[$i]), '"');
				$values = explode('</td><td>', $line);

				foreach($values as &$oneValue){
					$oneValue = htmlspecialchars($oneValue, ENT_COMPAT | ENT_IGNORE, 'UTF-8');
				}
				echo '<tr class="row'.($i%2).'"><td align="center"><strong>'.($i+$firstLine).'</strong></td><td>'.implode('</td><td>', $values).'</td></tr>';
			}
		?>
	</table>
