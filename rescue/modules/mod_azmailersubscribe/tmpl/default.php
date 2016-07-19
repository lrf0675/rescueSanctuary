<?php
/**
 * @package AZ Newsletter subsription module for Joomla! 1.5
 * @author Adam Jakab
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
**/
defined( '_JEXEC' ) or die( 'Restricted access' );

//LOAD CSS/JS
/** @var $jdoc \JDocumentHTML */
$jdoc = \JFactory::getDocument();
$jdoc->addStyleSheet( '/modules/mod_azmailersubscribe/assets/css/default.css' );
$jdoc->addScript( '/modules/mod_azmailersubscribe/assets/js/azmailersubscribe.js' );


//TRANSLATIONS
$firstname = JText::_("FIRSTNAME");
$lastname = JText::_("LASTNAME");
$email = JText::_("EMAIL");
$privacyText = JText::_("PRIVACYTEXT");
$subscribe = JText::_("SUBSCRIBE_BUTTON");

//PRIVACY LINK
$privacyLink = $privacyText;
if (!empty($privacy_article_url)) {
    $privacyLink = '<a'
		.' href="'.$privacy_article_url.'"'
	    .' target="_blank"'
	    .' class="privacy_link' .($load_fancybox!=0?" fancybox":"") .'"'
	    .'>'
	    .$privacyText
	    .'</a>';
}

//WELCOME PAGE URL
$welcomeLink = '';
if (!empty($welcome_page_url)) {
    $welcomeLink = '<a'
		.' href="'.$welcome_page_url.'"'
	    .' target="_blank"'
		.' style="display:none;"'
		.' class="welcome_link '.($popup_welcome_page!=0 && $load_fancybox!=0?"fancybox":"").'"'
	    .'>'
	    .'&nbsp;'
	    .'</a>';
}

//DEFAULT VALUES(for testing)
$_firstname = "";
$_lastname = "";
$_email = "";
$_privacy_checked = "";//'checked="checked"';
?>

<div class="mod_azmailersubscribe <?php echo $moduleclass_sfx; ?>">
	<?php if (!empty($module_pretext)) : ?>
        <div class="pretext"><?php echo $module_pretext; ?></div>
	<?php endif; ?>
    <form name="azmailersbcb" method="post" action="index.php">
       <!-- <input class="inputbox" type="text" name="firstname" placeholder="<?php echo $firstname; ?>" value="<?php echo $_firstname; ?>"/>
        <input class="inputbox" type="text" name="lastname" placeholder="<?php echo $lastname; ?>" value="<?php echo $_lastname; ?>"/> -->
        <input class="inputbox" type="text" name="email" placeholder="<?php echo $email; ?>" value="<?php echo $_email; ?>"/>
       <?php /*?> <?php if ($request_country == 1) : ?>
            <select class="inputbox_select" name="country"></select>
        <?php endif; ?>
        <?php if ($request_country == 1 && $request_region == 1) : ?>
            <select class="inputbox_select" name="region"></select>
        <?php endif; ?>
        <?php if ($request_country == 1 && $request_region == 1 && $request_province == 1) : ?>
            <select class="inputbox_select" name="province"></select>
        <?php endif; ?>

        <?php if ($request_privacy == 1) : ?>
            <div class="privacytext">
                <input class="inputbox_check" type="checkbox" name="privacy"  value="1" <?php echo $_privacy_checked; ?> /><?php echo $privacyLink; ?>
            </div>
        <?php endif; ?><?php */?>

        <input type="button" class="inputbox_button btn" name="<?php echo $subscribe; ?>" value="<?php echo $subscribe; ?>" />
        <input type="hidden" name="option" value="<?php echo $com_name; ?>" />
        <input type="hidden" name="task" value="" />
        <span id="azmailersbcbtoken"><?php echo JHTML::_( 'form.token' );?></span>
    </form>
    <?php echo $welcomeLink; ?>
</div>
