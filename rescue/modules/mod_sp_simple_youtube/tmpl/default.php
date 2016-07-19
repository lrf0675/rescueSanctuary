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
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<?php if ($youtube_id) { ?>

	<div class="sp_simple_youtube <?php echo ( $responsive ) ? 'sp_simple_youtube_responsive' : ''; ?>" data-width="<?php echo $width; ?>" data-height="<?php echo $height; ?>">
		<?php if($responsive){ ?>
			<iframe title="Simple youtube module by JoomShaper.com" id="sp-simple-youtube<?php echo $uniqid ?>" src="http://www.youtube.com/embed/<?php echo $youtube_id ?>?wmode=transparent" frameborder="0"></iframe>
		<?php } else { ?>
			<iframe title="Simple youtube module by JoomShaper.com" id="sp-simple-youtube<?php echo $uniqid ?>" src="http://www.youtube.com/embed/<?php echo $youtube_id ?>?wmode=transparent" width="<?php echo $width; ?>" height="<?php echo $height; ?>" frameborder="0"></iframe>
		<?php } ?>
	</div>

<?php } else { ?>
	<p>Please enter youtube id.</p>
<?php } ?>