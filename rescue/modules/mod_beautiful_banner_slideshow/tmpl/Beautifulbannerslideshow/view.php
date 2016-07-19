<?php
/**
* @title		Mod joombig article compact news module
* @website		http://www.joombig.com
* @copyright	Copyright (C) 2013 joombig.com. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

    // no direct access
    defined('_JEXEC') or die;
?>
<script>
jQuery.noConflict(); 
</script>

<link rel="stylesheet" type="text/css" href="<?php echo $mosConfig_live_site; ?>/modules/mod_beautiful_banner_slideshow/tmpl/Beautifulbannerslideshow/css/wt-rotator.css"/>
<?php
// add your stylesheet
$document->addStyleSheet( 'modules/mod_beautiful_banner_slideshow/tmpl/Beautifulbannerslideshow/css/wt-rotator.css' );
// style declaration
$document->addStyleDeclaration( '
	.panel{
		width:'.($width_module+2).'px;
	}
' );
?>

<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/modules/mod_beautiful_banner_slideshow/tmpl/Beautifulbannerslideshow/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/modules/mod_beautiful_banner_slideshow/tmpl/Beautifulbannerslideshow/js/jquery.wt-rotator.min.js"></script>
<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/modules/mod_beautiful_banner_slideshow/tmpl/Beautifulbannerslideshow/js/preview.js"></script>   
<?php
$document->addScript('modules/mod_beautiful_banner_slideshow/tmpl/Beautifulbannerslideshow/js/preview.js');
$document->addScriptDeclaration('
		var calWidth, calHeight, cal_auto_play, cal_delay_time, cal_transition_speed;
		calWidth = '.$width_module.';
		calHeight = '.$height_module.';
		cal_auto_play = '.$auto_play.';
		cal_delay_time = '.$delay_time.';
		cal_transition_speed = '.$transition_speed.';
		
');
?>
<div id="main_beautiful_banner_slideshow">
<div class="panel">
<div class="beautifullcontainer">
        <div class="wt-rotator">
            <a href="#"></a>            
            <div class="desc"></div>
            <div class="preloader"></div>
            <div class="c-panel">
                <div class="buttons-beautifull">
                    <div class="prev-btn"></div>
                    <div class="play-btn"></div>    
                    <div class="next-btn"></div>               
                </div>
                <div class="thumbnails">
                    <ul>
					<?php foreach($data as $index=>$value) { ?>
                        <li>
                            <a href="<?php echo JURI::root().$value['image'] ?>"></a>
							<?php if($show_des == 1){?>
								<div style="left:15px; top:50px; width: auto; height: auto; opacity: 1;"> 
									<span class="cap-title"><?php echo $value['shortDesc']?></span><br/>
									<?php echo $value['introtext']?><br/>
									<div class="beutiful_readmore">
										<a href="<?php echo $value['link'] ?>"><?php echo $value['readmore']?></a>
									</div>
								</div>     
							<?php }?>	
                        </li>   
					<?php } ?>	
                    </ul>
                </div>     
            </div>
        </div>	
  </div>
  </div>
</div>