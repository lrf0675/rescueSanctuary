<?php
/********************************************************************
Product		: Simple Responsive Menu
Date		: 24 October 2014
Copyright	: Les Arbres Design 2010-2014
Contact		: http://extensions.lesarbresdesign.info
Licence		: GNU General Public License
*********************************************************************/
defined('_JEXEC') or die;

	echo "\n".'<div class="srm_position" style="'.$div_styles.'">';
	$onchange = 'onchange="var e=document.getElementById(\'srm_select_list\'); window.location.href=e.options[e.selectedIndex].value"';
	echo "\n".'<select id="srm_select_list" size="1" style="'.$select_styles.'" '.$onchange.'>';

	if ($fixedText != '')
		echo "\n".'<option value="#" selected="selected">'.$fixedText.'</option>';

	$depth = 0;
	foreach ($list as $i => &$item)
		{
		if ($item->id == $active_id)
			$selected = ' selected="selected"';
		else
			$selected = '';
			
		if ($fixedText != '')
			$selected = '';

		switch ($item->type)
			{
			case 'separator':
			case 'heading':
				continue; break;		// don't create a list item for these types
			case 'url':
			case 'component':
			default:
				$link = $item->flink;
				break;
			}
		
		echo "\n".'<option value="'.$link.'"'.$selected.'>';
		for ($i=0; $i < $depth; $i++)
			echo '- ';
		echo $item->title;
		echo '</option>';
		
		if ($item->deeper)
			$depth ++;
		if ($item->shallower)
			$depth -= $item->level_diff;
		}
		
	echo '</select>';
	echo '</div>';
