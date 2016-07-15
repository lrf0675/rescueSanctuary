<?php
// No direct access to this file
defined('_JEXEC') or die();
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;

JHtml::_('behavior.tooltip');
global $AZMAILER;

$item = &$this->item;
$langCode = 'en';//@JACK - for now we force EN version of editor
$elFinderUrl = 'index.php?option=' . $AZMAILER->getOption("com_name") . '&task=editor.elfinder&tmpl=component';
$tinymceBase = $AZMAILER->getOption("com_uri_admin") . '/assets/js/tiny_mce/';
AZMailerAdminInterfaceHelper::addAdditionalHeaderIncludes("js", "/assets/js/tiny_mce/jquery.tinymce.js");
$BLOB = $item->htmlblob;
?>


<div class="ui-widget-content">
	<form action="index.php" method="post" name="adminForm" id="adminForm">
		<textarea id="htmlblob" name="htmlblob" class="tinymce"><?php echo $BLOB; ?></textarea>
		<input type="hidden" name="option" value="<?php echo $AZMAILER->getOption("com_name"); ?>"/>
		<input type="hidden" name="task" value="<?php echo $AZMAILER->getOption("ctrl.task"); ?>"/>
		<input type="hidden" name="id" value="<?php echo $item->id; ?>"/>
		<input type="hidden" name="title" value="<?php echo $this->params->title; ?>"/>
		<input type="hidden" name="parent_type" value="<?php echo $this->params->parent_type; ?>"/>
		<input type="hidden" name="parent_id" value="<?php echo $this->params->parent_id; ?>"/>
		<input type="hidden" name="return_uri" value="<?php echo $this->params->return_uri; ?>"/>
		<?php echo JHTML::_('form.token'); ?>
	</form>
</div>


<style type="text/css">
	textarea#htmlblob {
		border: 1px solid #aaaaaa;
		width: 100%;
		height: 2000px;
	}
</style>

<script language="javascript" type="text/javascript">

	jQuery(document).ready(function ($) {

		$('textarea.tinymce').tinymce({
			// Location of TinyMCE script
			script_url: '<?php echo $tinymceBase; ?>tiny_mce.js',

			// General options
			language: "<?php echo $langCode; ?>",
			theme: "advanced",
			theme_advanced_resizing: true,
			theme_advanced_resizing_use_cookie: true,

			//
			theme_advanced_blockformats: "p,div,h1,h2,h3,h4,h5,h6",

			//plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",
			plugins: "aznl_editable,autolink,lists,style,table,advimage,advlink,inlinepopups,searchreplace,contextmenu,paste,visualchars,nonbreaking,xhtmlxtras,template,advlist",

			// Theme options
			theme_advanced_buttons1: "bold,italic,underline,strikethrough,sub,sup,|"
			+ ",bullist,numlist,|"
			+ ",justifyleft,justifycenter,justifyright,justifyfull,|"
			+ ",forecolor,backcolor,|"
			+ ",formatselect,fontselect,fontsizeselect,|"
			+ ",cut,copy,paste,pastetext,pasteword,|"
			+ ",charmap,|"
			+ ",search,replace,|"
			+ ",removeformat,cleanup,|"
			+ ",undo,redo,|"
			+ ",visualchars, visualaid,code,help"
			+ "",

			theme_advanced_buttons2: "image,|"
			+ ",link,unlink,anchor,|"
			+ ",tablecontrols,|"
			+ ",hr,styleprops,attribs,|"
			+ ",|,|,|,aznl_editable,|,|,|"
			+ "",

			theme_advanced_buttons3: "",
			theme_advanced_buttons4: "",

			theme_advanced_toolbar_location: "top",
			theme_advanced_toolbar_align: "left",
			theme_advanced_statusbar_location: "bottom",

			valid_elements: "@[id|class|style|title|dir<ltr?rtl|lang|xml::lang|onclick|ondblclick|"
			+ "onmousedown|onmouseup|onmouseover|onmousemove|onmouseout|onkeypress|"
			+ "onkeydown|onkeyup],a[rel|rev|charset|hreflang|tabindex|accesskey|type|"
			+ "name|href|target|title|class|onfocus|onblur],strong/b,em/i,strike,u,"
			+ "#p,-ol[type|compact],-ul[type|compact],-li,br,img[rel|longdesc|usemap|"
			+ "src|border|alt=|title|hspace|vspace|width|height|align],-sub,-sup,"
			+ "-blockquote,-table[border=0|cellspacing|cellpadding|width|frame|rules|"
			+ "height|align|summary|bgcolor|background|bordercolor],-tr[rowspan|width|"
			+ "height|align|valign|bgcolor|background|bordercolor],tbody,thead,tfoot,"
			+ "#td[colspan|rowspan|width|height|align|valign|bgcolor|background|bordercolor"
			+ "|scope],#th[colspan|rowspan|width|height|align|valign|scope],caption,-div[rel],"
			+ "-span,-code,-pre,address,-h1[rel],-h2[rel],-h3[rel],-h4[rel],-h5[rel],-h6[rel],hr[size|noshade],-font[face"
			+ "|size|color],dd,dl,dt,cite,abbr,acronym,del[datetime|cite],ins[datetime|cite],"
			+ "object[classid|width|height|codebase|*],param[name|value|_value],embed[type|width"
			+ "|height|src|*],script[src|type],map[name],area[shape|coords|href|alt|target],bdo,"
			+ "button,col[align|char|charoff|span|valign|width],colgroup[align|char|charoff|span|"
			+ "valign|width],dfn,fieldset,form[action|accept|accept-charset|enctype|method],"
			+ "input[accept|alt|checked|disabled|maxlength|name|readonly|size|src|type|value],"
			+ "kbd,label[for],legend,noscript,optgroup[label|disabled],option[disabled|label|selected|value],"
			+ "q[cite],samp,select[disabled|multiple|name|size],small,"
			+ "textarea[cols|rows|disabled|name|readonly],tt,var,big",

			//Content CSS
			//content_css : "css/content.css",

			// Drop lists for link/image/media/template dialogs
			//template_external_list_url : "lists/template_list.js",
			//external_link_list_url : "lists/link_list.js",
			//external_image_list_url : "lists/image_list.js",
			//media_external_list_url : "lists/media_list.js",

			visual: false, /*visual aid is off by default (table borders/etc)*/

			relative_urls: false,
			remove_script_host: true,
			convert_urls: false,
			document_base_url: "http://<?php echo $_SERVER['HTTP_HOST'] ?>/",

			file_browser_callback: 'elFinderBrowser'
		});
	});

	function elFinderBrowser(field_name, url, type, win) {
		var elfinder_url = '<?php echo $elFinderUrl; ?>';
		//alert(elfinder_url);
		tinyMCE.activeEditor.windowManager.open({
			file: elfinder_url,
			title: 'elFinder 2.0',
			width: 900,
			height: 450,
			resizable: 'yes',
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