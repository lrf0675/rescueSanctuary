<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;

JHtml::_('behavior.tooltip');
global $AZMAILER;

//INCLUSIONS + tinyMcePopup + Elfinder
AZMailerAdminInterfaceHelper::addAdditionalHeaderIncludes("js", "/assets/js/tiny_mce/tiny_mce_popup.js");
AZMailerAdminInterfaceHelper::addAdditionalHeaderIncludes("js", "/assets/js/elfinder/js/elfinder.min.js");
//AZMailerAdminInterfaceHelper::addAdditionalHeaderIncludes("js","/assets/js/elfinder/js/i18n/elfinder.it.js"); - MISSING MAKE ONE
AZMailerAdminInterfaceHelper::addAdditionalHeaderIncludes("css", "/assets/js/elfinder/css/elfinder.min.css");
AZMailerAdminInterfaceHelper::addAdditionalHeaderIncludes("css", "/assets/js/elfinder/css/theme.css");
$elfinderConnectorUrl = 'index.php?option=' . $AZMAILER->getOption("com_name") . '&task=editor.elfinder_conn&tmpl=component';

?>
<div id="elfinder"></div>
<script language="javascript" type="text/javascript">
	jQuery(document).ready(function ($) {
		var FileBrowserDialogue = {
			init: function () {
				// Here goes your code for setting your custom things onLoad.
			},

			mySubmit: function (URL) {
				var win = tinyMCEPopup.getWindowArg('window');

				// pass selected file path to TinyMCE
				var siteRelUri = URL.replace('http://<?php echo $_SERVER['SERVER_NAME']; ?>', '');//=== /images/something.png
				win.document.getElementById(tinyMCEPopup.getWindowArg('input')).value = siteRelUri;

				// are we an image browser?
				if (typeof(win.ImageDialog) != 'undefined') {
					// update image dimensions
					if (win.ImageDialog.getImageData) {
						win.ImageDialog.getImageData();
					}
					// update preview if necessary
					if (win.ImageDialog.showPreviewImage) {
						win.ImageDialog.showPreviewImage(URL);
					}
				}

				// close popup window
				tinyMCEPopup.close();
			}
		};
		try {
			tinyMCEPopup.onInit.add(FileBrowserDialogue.init, FileBrowserDialogue);
		} catch (e) {
			///
		}

		var elf = $('#elfinder').elfinder({
			url: '<?php echo $elfinderConnectorUrl; ?>',
			//lang: 'it',
			getFileCallback: function (url) { // editor callback
				FileBrowserDialogue.mySubmit(url); // pass selected file path to TinyMCE
			}
		}).elfinder('instance');

	});
</script>
