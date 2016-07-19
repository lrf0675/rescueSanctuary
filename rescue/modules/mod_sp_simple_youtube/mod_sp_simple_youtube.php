<?php
/*------------------------------------------------------------------------
# mod_sp_simple_youtube - Youtube Module by JoomShaper.com
# ------------------------------------------------------------------------
# author    JoomShaper http://www.joomshaper.com
# Copyright (C) 2010 - 2014 JoomShaper.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.joomshaper.com
-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die('Restricted access');
//Parameters
$doc 					= JFactory::getDocument();
$uniqid 				= $module->id;
$youtube_id				= $params->get ('youtube_id');
$responsive				= $params->get('responsive', 1);
$width					= $params->get ('width',300);
$height					= $params->get ('height',225);

// Load the method jquery script.
if( $responsive )
{
	JHtml::_('jquery.framework');
	?>
	<script type="text/javascript">
		jQuery(function($) {
			$('.sp_simple_youtube_responsive').each(function(){
				var $that = $(this);
				$('#sp-simple-youtube<?php echo $uniqid ?>').css({
					'width': $(this).width(),
					'height': ( $(this).data('height')*$(this).width() ) / $(this).data('width')
				});

				$(window).resize(function(){
					$('#sp-simple-youtube<?php echo $uniqid ?>').css({
						'width': $that.width(),
						'height': ( $that.data('height')*$that.width() ) / $that.data('width')
					});
				});
			});
		});
	</script>
	<?php
}

require(JModuleHelper::getLayoutPath('mod_sp_simple_youtube'));