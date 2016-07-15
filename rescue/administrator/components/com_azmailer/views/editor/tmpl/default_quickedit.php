<?php
// No direct access to this file
defined('_JEXEC') or die();
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;

JHtml::_('behavior.tooltip');
global $AZMAILER;
$langCode = 'en';//@JACK - for now we force EN version of editor
$elFinderUrl = 'index.php?option=' . $AZMAILER->getOption("com_name") . '&task=editor.elfinder&tmpl=component';
$tinymceBase = $AZMAILER->getOption("com_uri_admin") . '/assets/js/tiny_mce/';
AZMailerAdminInterfaceHelper::addAdditionalHeaderIncludes("js", "/assets/js/tiny_mce/jquery.tinymce.js");
?>

<div id="editor_container">
	<textarea id="htmlblob" name="htmlblob" class="tinymce"></textarea>
</div>
<div id="editor_buttons">
	<button name="close"><?php echo \JText::_("COM_AZMAILER_CANCEL"); ?></button>
	<button name="accept"><?php echo \JText::_("COM_AZMAILER_SET"); ?></button>
</div>


<style type="text/css" media="screen">
	body {
		padding: 0;
		margin: 0;
	}

	textarea#htmlblob {
		border: 1px solid #aaaaaa;
		width: 778px;
		height: 520px;
	}

	#editor_buttons {
		position: relative;
		height: 50px;
		width: 778px;
	}

	button {
		cursor: pointer;
	}

	button[name=close] {
		position: absolute;
		bottom: 3px;
		left: 30px;
	}

	button[name=accept] {
		position: absolute;
		bottom: 0;
		right: 30px;
		font-size: 18px !important;
	}
</style>


<script language="javascript" type="text/javascript">
	jQuery(document).ready(function ($) {
		var html = parent.editTag_HTML_getContent();
		$("textarea#htmlblob").html(html);

		$('textarea.tinymce').tinymce({
			// Location of TinyMCE script
			script_url: '<?php echo $tinymceBase; ?>tiny_mce.js',

			// General options
			language: "<?php echo $langCode; ?>",
			theme: "advanced",
			theme_advanced_resizing: false,
			theme_advanced_resizing_use_cookie: false,

			//
			theme_advanced_blockformats: "p,div,h1,h2,h3,h4,h5,h6",

			//plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",
			plugins: "autolink,lists,style,advimage,advlink,inlinepopups,searchreplace,paste,visualchars,nonbreaking,xhtmlxtras,template,advlist",

			// Theme options
			theme_advanced_buttons1: "bold,italic,underline,strikethrough,sub,sup,|"
			+ ",bullist,numlist,|"
			+ ",justifyleft,justifycenter,justifyright,justifyfull,|"
			+ ",forecolor,backcolor,|"
			+ ",formatselect,fontselect,fontsizeselect,|"
			+ ",removeformat,cleanup,|"
			+ ",undo,redo"
			+ "",

			theme_advanced_buttons2: "image,|"
			+ ",cut,copy,paste,pastetext,pasteword,|"
			+ ",charmap,|"
			+ ",search,replace,|"
			+ ",link,unlink,anchor,|"
			+ ",hr,styleprops,attribs,|"
			+ ",visualchars, visualaid,code,help"
			+ "",

			theme_advanced_buttons3: "",
			theme_advanced_buttons4: "",

			theme_advanced_toolbar_location: "top",
			theme_advanced_toolbar_align: "left",
			theme_advanced_statusbar_location: "bottom",

			//Content CSS
			//content_css : "css/content.css",

			// Drop lists for link/image/media/template dialogs
			//template_external_list_url : "lists/template_list.js",
			//external_link_list_url : "lists/link_list.js",
			//external_image_list_url : "lists/image_list.js",
			//media_external_list_url : "lists/media_list.js",

			relative_urls: false,
			remove_script_host: true,
			convert_urls: false,
			document_base_url: "http://<?php echo $_SERVER['HTTP_HOST'] ?>/",

			file_browser_callback: 'elFinderBrowser'
		});

		//BUTTON-CLOSE
		$("button[name=close]").button().click(function () {
			parent.jQuery.fancybox.close();
		});
		//BUTTON-ACCEPT
		$("button[name=accept]").button().click(function () {
			var html = $("textarea#htmlblob").html();
			parent.editTag_HTML_setContent(html);
			parent.jQuery.fancybox.close();
		});

	});

	function elFinderBrowser(field_name, url, type, win) {
		var elfinder_url = '<?php echo $elFinderUrl; ?>';
		//alert(elfinder_url);
		tinyMCE.activeEditor.windowManager.open({
			file: elfinder_url,
			title: 'elFinder 2.0',
			width: 750,
			height: 500,
			resizable: 'no',
			scrollable: 'no',
			inline: 'yes',    // This parameter only has an effect if you use the inlinepopups plugin!
			popup_css: false, // Disable TinyMCE's default popup CSS
			close_previous: 'no'
		}, {
			window: win,
			input: field_name
		});
		return false;
	}

</script>