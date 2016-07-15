jQuery(document).ready(function($){

	// TRIGGER SEND TEST NEWSLETTER
	$("a[name=nl_sendtestmail]").click(function() {
		if (jQuery('form[name=adminForm] input[name=id]').val() != 0) {
			if (!is_modified) {
				sendTestNewsletter();
			} else {
				alert(TRANS["COM_AZMAILER_NEWSLETTER_DO_APPLY_MODDED"]);
			}
		} else {
			alert(TRANS["COM_AZMAILER_NEWSLETTER_DO_APPLY_UNSAVED"]);
		}
		return(false);
	});

	// TRIGGER NEWSLETTER TEMPLATE CHANGE
	$("a[name=nl_changetemplate]").click(function() {
		if (!is_modified) {
			changeNewsletterTemplate();
		} else {
			alert(TRANS["COM_AZMAILER_NEWSLETTER_DO_APPLY_MODDED"]);
		}
		return(false);
	});

	// TRIGGER NEWSLETTER CONTENT SIMPLE TEXT CONVERSION
	$("a[name=nl_html2txt]").click(function() {
		if (jQuery('form[name=adminForm] input[name=id]').val() != 0) {
			if (!is_modified) {
				getSimpleTextVersion();
			} else {
				alert(TRANS["COM_AZMAILER_NEWSLETTER_DO_APPLY_MODDED"]);
			}
		} else {
			alert(TRANS["COM_AZMAILER_NEWSLETTER_DO_APPLY_UNSAVED"]);
		}
		return(false);
	});


});




/*-----------------------------------------------------------------------------------------------------------------GESTIONE CONETNUTI---*/
function refreshNewsletterContent() {
	jQuery.post("index.php", {
            task:           "newsletter.getNewsletterSubstitutedContent",
            format:         "raw",
            option:         jQuery("form#adminForm input[name=option]").val(),
			tpl_id:			jQuery('form[name=adminForm] input[name=nl_template_id]').val(),
			tpl_subst:		jQuery('form[name=adminForm] input[name=nl_template_substitutions]').val()
		},
		function(data){
            var html = elaborateJsonResponse(data, true);
            //alert(html);
            var IFR = document.getElementById("iframednewslettercontent");
            var IFRC = (IFR.contentWindow || IFR.contentDocument);
            if (IFRC.document) {IFRC = IFRC.document};
            IFRC.body.innerHTML = '';
            IFRC.write(html);
			activateEditableTags();
			updateNLIframeHeight();
			interceptNLLinks();
		}
	);
}

function activateEditableTags() {
	var iframehtml = jQuery("iframe#iframednewslettercontent").contents();
    var highlighterDiv = jQuery("#highlighterDiv", iframehtml);
    if (highlighterDiv.length == 0) {
        highlighterDiv = jQuery('<div id="highlighterDiv">')
            .css({ 'background-color': 'rgba(240,140,30,.3)', 'position': 'absolute', 'z-index': '65535', 'cursor': 'pointer' })
            .appendTo(jQuery("body", iframehtml));
    }
	jQuery(".editable", iframehtml).each(function(i,element) {
        var el = jQuery(element);
        el.mouseover(function() {
            highlighterDiv.offset(el.offset()).width(el.width()).height(el.height());
            highlighterDiv.on("click.azmailer", function() {editTag(el);});
        });
        highlighterDiv.mouseleave(function() {
            highlighterDiv.offset({ top: -1, left: -1 }).width(1).height(1);
            highlighterDiv.off("click.azmailer");
        });
    });
}


function interceptNLLinks() {
	var iframehtml = jQuery("iframe#iframednewslettercontent").contents();
	jQuery("a", iframehtml).each(function() {
        var el = jQuery(this);
        el.click(function(ev) {
        	ev.preventDefault();
        	alert(TRANS["COM_AZMAILER_NEWSLETTER_LINKED_TO"] + el.attr("href"));
        	//return(false);
        });
    });
}

/**
 * setting iframe height -
 * need to do this after activateEditableTags - because css styling change on editable elements changes height
 * todo: it only expands but does not go smaller when content is shortened
 */
function updateNLIframeHeight() {
    var IFC = jQuery("iframe#iframednewslettercontent");
    var _doUpdateNLIframeHeight = function() {
        var NH = IFC.contents().find('body').height() + 25;
        IFC.height(NH);
    };
    _doUpdateNLIframeHeight();
    IFC.contents().find('img').last().bind('load',function(){
        _doUpdateNLIframeHeight();
	});
	// remove body margin
    IFC.contents().find('body').css("margin",0);
}






function editTag(el) {
	var attributes = "";
	if(el.attr("id")===undefined) {alert(TRANS["COM_AZMAILER_NEWSLETTER_MSG_ELEMENT_NOID"]); return;}
	var rel = el.attr("rel").replace(/'/gi,'"');
	try {attributes = jQuery.parseJSON(rel);} catch(e) {/* alert("???"+rel+e); */}
	if (typeof(attributes) != "object") {alert(TRANS["COM_AZMAILER_NEWSLETTER_MSG_ELEMENT_NOREL"]); return;}
	// alert("EDITING ELEMENT WITH TYPE:"+attributes.type);
	current["el"] = el;
	current["attributes"] = attributes;
    //alert("EL: " + JSON.stringify(attributes));
	//
	switch (attributes.type) {
		case "text":
			editTag_TEXT();
			break;
		case "html":
			editTag_HTML();
			break;
		case "image":
			editTag_IMAGE();
			break;
		default:
			//
			break;
	}
}

function editTag_TEXT() {
	var el = current["el"];
	var attributes = current["attributes"];
	//
	var id = el.attr("id");
	var txt = el.html();// ------------or would it be more secure to use nl_template_substitutions???
	txt = txt.replace(/"/gi,"'");// always remove
	//
	jQuery('#mDialog').dialog( "option", "title", TRANS["COM_AZMAILER_NEWSLETTER_TIT_MOD_TEXT"] );
	jQuery('#mDialog .title').html("");
	jQuery('#mDialog .text').html('<input name="modtxt" size="90" maxlength="255" value="'+txt+'" />');
	jQuery('#mDialog').dialog( "option", "buttons", [
            {
                text: TRANS["COM_AZMAILER_MODIFY"],
                click: function() {
                    var modtxt = jQuery("#mDialog .text input[name=modtxt]").val();
                    modtxt = modtxt.replace(/"/gi,"'");// always remove
                    elaborateNewsletterSubstitutions(id, modtxt);
                    jQuery(this).dialog("close");
                }
            },
            {
                text: TRANS["COM_AZMAILER_CANCEL"],
                click: function() { jQuery(this).dialog("close"); }
            }
	    ]
	);
	jQuery('#mDialog').dialog('open');
}

function editTag_HTML() {
	var el = current["el"];
	var attributes = current["attributes"];
	var editorUrl = 'index.php?option='+com_name+'&task=editor.quickEdit&tmpl=component';
	jQuery.fancybox(
		{
			"title":		TRANS["COM_AZMAILER_NEWSLETTER_TIT_MOD_HTML"],
			"modal": 		true,
			"scrolling": 	false,
			"width": 		800,
			"height": 		600,
			"padding":		0,
			"margin":		0,
			"type": 		"iframe",
			"href": 		editorUrl
		}
	);
}

function editTag_HTML_getContent() {//called by iframed(fancyboxed) JCE editor page load
	return(current["el"].html());
}

function editTag_HTML_setContent(html) {//called by iframed(fancyboxed) JCE on user accept
	elaborateNewsletterSubstitutions(current["el"].attr("id"), html);
}




function editTag_IMAGE() {
	//var el = current["el"];
	//var attributes = current["attributes"];
	//var id = el.attr("id");
	//
	jQuery('#mDialog').dialog( "option", "title", TRANS["COM_AZMAILER_NEWSLETTER_TIT_UPLOAD_FILE"] );
	jQuery('#mDialog .title').html("");
	var formhtml = 	'<form id="imgUploader">'
					+ '<input type="file" name="fileToUpload" id="fileToUpload" />'
					+ '</form>'
                    + ''
                    + '';
	jQuery('#mDialog .text').html(formhtml);
        //BUTTONS
        jQuery('#mDialog').dialog( "option", "buttons", [
            {
            text: TRANS["COM_AZMAILER_UPLOAD"],
            click: function() {
                //jQuery('#mDialog .text form#imgUploader').submit();
                doFileUpload();
            }},
            {
            text: TRANS["COM_AZMAILER_CANCEL"],
            click: function() { jQuery(this).dialog("close"); }
            }
	    ]
	);

    jQuery('input#fileToUpload').change(function() {
        var file = jQuery(this).get(0).files[0];
        var extension = file.name.substr(file.name.lastIndexOf(".")+1).toLowerCase();
        var allowed_newsletter_images = new Array("jpg", "jpeg");
        if(allowed_newsletter_images.indexOf(extension) == -1) {
            alert(TRANS["COM_AZMAILER_NEWSLETTER_FILEUPLOAD_ERR_NOJPG"]);
            jQuery('#mDialog').dialog("close");
            editTag_IMAGE();
        }
    });

    function doFileUpload() {
        var file = jQuery('input#fileToUpload').get(0).files[0];
        if(!file) {alert("Select a file first!"); return;}
        jQuery('#mDialog').dialog( "option", "buttons", {});
        jQuery('#mDialog').dialog( "option", "title", TRANS["COM_AZMAILER_UPLOADING"] );
        jQuery('#mDialog .text').html(''
            + 'Name: ' + file.name + '<br />'
            + 'Size: ' + getHumanReadableFileSize(file.size) + '<br />'
            + '<div id="uploadProgress">---</div>'
            + ''
        );
        //create FORM to send - action="index.php?option='+com_name+'&task=newsletter.changeNewsletterEditableImage&format=raw" method="post" enctype="multipart/form-data" encoding="multipart/form-data"
        var fd = new FormData();
        fd.append("fileToUpload", file);
        fd.append("elementid", current["el"].attr("id"));
        fd.append("elementattribs", jQuery.base64Encode(jQuery.toJSON(current["attributes"])));
        fd.append("elcurrsrc", current["el"].attr("src"));
        fd.append("option", com_name);
        fd.append("task", "newsletter.changeNewsletterEditableImage");
        fd.append("format", "raw");
        //
        var xhr = new XMLHttpRequest();
        xhr.upload.addEventListener("progress", xhrUploadProgress, false);
        xhr.addEventListener("load", xhrUploadComplete, false);
        xhr.addEventListener("error", xhrUploadError, false);
        xhr.addEventListener("abort", xhrUploadAbort, false);
        xhr.open("POST", "index.php");
        xhr.send(fd);
    }

    function xhrUploadError(ev) {alert("Error uploading file!\n" + ev);}
    function xhrUploadAbort(ev) {alert("File upload was aborted!\n" + ev);}
    function xhrUploadProgress(ev) {
        var perc = 0;
        if (ev.lengthComputable) {
            var perc = Math.round(ev.loaded * 100 / ev.total);
            jQuery("#uploadProgress").html("uploaded: "+perc+"%");
        }
    }
    function xhrUploadComplete(ev) {
        //jQuery('#mDialog .text').html(ev.target.responseText);
        var parsed_JSON = jQuery.parseJSON(ev.target.responseText);
        if (parsed_JSON.errors.length != 0) {
            jQuery('#mDialog').dialog("close");
            alert(parsed_JSON.errors[0]);
            editTag_IMAGE();//restart
        } else {
            if (typeof(parsed_JSON.NEWFILEURI) == "string") {
                elaborateNewsletterSubstitutions(current["el"].attr("id"), parsed_JSON.NEWFILEURI);
            } else {
                alert(TRANS["COM_AZMAILER_ERR_UNKNOWN"]);
            }
            jQuery('#mDialog').dialog("close");
        }
    }
	jQuery('#mDialog').dialog('open');

}



/*
 * the loaded jquery.json-2.3.min.js interface provides: $.toJSON() and
 * $.evalJSON()
 */
function elaborateNewsletterSubstitutions(sname, svalue) {
	var SO_STR_B64 = jQuery("form[name=adminForm] input[name=nl_template_substitutions]").val();
	var SO_STR_CLN;
	var SO;
	try {SO_STR_CLN = jQuery.base64Decode(SO_STR_B64);} catch(e) {/*alert("Not B64: " . SO_STR_B64);*/SO_STR_CLN = '{}';}
	try {SO = jQuery.evalJSON(SO_STR_CLN);} catch(e) {/* alert("???"+rel+e); */}
	if (typeof(SO) != "object" || SO == "" || SO === null){SO = new Object();}
	if (sname!==undefined && svalue!==undefined) {
		SO[sname]=svalue;
	}
	var JSON = jQuery.toJSON(SO);
	var JSON_B64 = jQuery.base64Encode(JSON);
	jQuery("form[name=adminForm] input[name=nl_template_substitutions]").val(JSON_B64);
	is_modified = true;// so we know that you modified NL since last save
	refreshNewsletterContent();
}





function changeNewsletterTitle() {
	jQuery('#mDialog').dialog( "option", "title", TRANS["COM_AZMAILER_NEWSLETTER_TIT_MOD_TITLE"] );
	jQuery('#mDialog .title').html("");
	var formhtml = '';
	formhtml += TRANS["COM_AZMAILER_NEWSLETTER_TITLE"] + '<br />';
	formhtml += '<input name="nl_title" size="70" maxlength="255" value="'+jQuery("form[name=adminForm] input[name=nl_title]").val()+'" />';
	formhtml += '<br /><br />';
	formhtml += TRANS["COM_AZMAILER_NEWSLETTER_TITLE_INTERNAL"] + '<br />';
	formhtml += '<input name="nl_title_internal" size="70" maxlength="255" value="'+jQuery("form[name=adminForm] input[name=nl_title_internal]").val()+'" />';
	jQuery('#mDialog .text').html(formhtml);
	jQuery('#mDialog').dialog( "option", "buttons", [
	    {
	    text: TRANS["COM_AZMAILER_MODIFY"],
		click: function() {
			//TITLE
			var nt = jQuery("#mDialog .text input[name=nl_title]").val();
			if(nt) {
				jQuery("form[name=adminForm] input[name=nl_title]").val(nt);
				jQuery("table[name=nl_data] td[name=nl_title]").html(nt);
			}
			//INTERNAL TITLE
			var nt = jQuery("#mDialog .text input[name=nl_title_internal]").val();
			jQuery("form[name=adminForm] input[name=nl_title_internal]").val(nt);
			jQuery("table[name=nl_data] td[name=nl_title_internal]").html(nt);
			//
			is_modified = true;// so we know that you modified NL since last save
			jQuery(this).dialog("close");
		}},
		{
		text: TRANS["COM_AZMAILER_CANCEL"],
		click: function() { jQuery(this).dialog("close"); }
		}
		]
	);
	jQuery('#mDialog').dialog('open');
}

function changeNewsletterSender() {
	jQuery('#mDialog').dialog( "option", "title", TRANS["COM_AZMAILER_NEWSLETTER_TIT_MOD_SENDER"] );
	jQuery('#mDialog .title').html("");
	var formhtml = '';
	formhtml += TRANS["COM_AZMAILER_NEWSLETTER_SENDER"] + '<br />';
	formhtml += '<input name="nl_sender" size="70" maxlength="128" value="'+jQuery("form[name=adminForm] input[name=nl_email_from]").val()+'" />';
	formhtml += '<br /><br />';
	formhtml += TRANS["COM_AZMAILER_NEWSLETTER_SENDER_NAME"] + '<br />';
	formhtml += '<input name="nl_sender_name" size="70" maxlength="128" value="'+jQuery("form[name=adminForm] input[name=nl_email_from_name]").val()+'" />';
	jQuery('#mDialog .text').html(formhtml);
	jQuery('#mDialog').dialog( "option", "buttons", [
	    {
	    text: TRANS["COM_AZMAILER_MODIFY"],
		click: function() {
			//SENDER MAIL
			var ns = jQuery("#mDialog .text input[name=nl_sender]").val();
			if(ns) {//@JACK - we need proper e-mail check here
				jQuery("form[name=adminForm] input[name=nl_email_from]").val(ns);
				jQuery("table[name=nl_data] td[name=nl_sender]").html(ns);
			}
			//SENDER NAME
			var ns = jQuery("#mDialog .text input[name=nl_sender_name]").val();
			jQuery("form[name=adminForm] input[name=nl_email_from_name]").val(ns);
			jQuery("table[name=nl_data] td[name=nl_sender_name]").html(ns);

			//
			is_modified = true;// so we know that you modified NL since last save
			jQuery(this).dialog("close");
		}},
		{
			text: TRANS["COM_AZMAILER_CANCEL"],
			click: function() { jQuery(this).dialog("close"); }
		}
		]
	);
	jQuery('#mDialog').dialog('open');
}

/**
 * TODO: this TPL change will kill all associations to currently inserted images in /media and so those images will stay there forever
 *      there should be a serverSide action for removing those images first
 */
function changeNewsletterTemplate() {
    jQuery.post("index.php", {
            task:           "newsletter.getSelectOptionsTemplates",
            format:         "raw",
            option:         jQuery("form#adminForm input[name=option]").val()
        },
        function(data){
            var response = elaborateJsonResponse(data, true);
            var templateSelector = jQuery('<select>').attr("id","template_id");
            var curr_tpl_id = jQuery('form[name=adminForm] input[name=nl_template_id]').val();
            jQuery.each(response, function(k,d) {
                templateSelector.append(jQuery('<option>', {value: d.id}).text(d.data));
                if (d.id==curr_tpl_id) {
                    jQuery("option[value="+d.id+"]", templateSelector).attr("selected","selected");
                }
            });

            jQuery('#mDialog').dialog( "option", "title", TRANS["COM_AZMAILER_NEWSLETTER_TIT_MOD_TEMPLATE"] );
            jQuery('#mDialog .title').html(TRANS["COM_AZMAILER_NEWSLETTER_DESC_MOD_TEMPLATE"]);
            jQuery('#mDialog .text').html("").append(templateSelector);
            jQuery('#mDialog').dialog( "option", "buttons", [
                {
                    text: TRANS["COM_AZMAILER_MODIFY"],
                    click: function() {
                        var new_tpl_id = jQuery("#mDialog .text select#template_id option:selected").val();
                        curr_tpl_id = jQuery('form[name=adminForm] input[name=nl_template_id]').val();
                        if (new_tpl_id != curr_tpl_id) {
                            jQuery('form[name=adminForm] input[name=nl_template_id]').val(new_tpl_id);
                            jQuery('form[name=adminForm] input[name=nl_template_substitutions]').val("e30=");
                            Joomla.submitbutton("newsletter.apply");
                        } else {
                            alert(TRANS["COM_AZMAILER_NEWSLETTER_MSG_MOD_TEMPLATE_NO_CHANGE"]);
                        }
                        jQuery(this).dialog("close");
                    }},
                {
                    text: TRANS["COM_AZMAILER_CANCEL"],
                    click: function() { jQuery(this).dialog("close"); }
                }
            ]
            );
            jQuery('#mDialog').dialog('open');

        }
    );
}

function sendTestNewsletter() {
	jQuery('#mDialog').dialog( "option", "title", TRANS["COM_AZMAILER_NEWSLETTER_SEND_TEST"] );
	jQuery('#mDialog .title').html(TRANS["COM_AZMAILER_NEWSLETTER_SEND_TEST_DESC"]);
	jQuery('#mDialog .text').html('<input name="emailto" size="70" maxlength="255" value="'+my_mail+'" />');
	jQuery('#mDialog').dialog( "option", "buttons", [
	    {
		text: TRANS["COM_AZMAILER_NEWSLETTER_SEND_TEST"],
		click: function() {
			jQuery.post("index.php", {
                    task:           "newsletter.sendTestNewsletter",
                    format:         "raw",
                    option:         jQuery("form#adminForm input[name=option]").val(),
					newsletter_id:	jQuery('form[name=adminForm] input[name=id]').val(),
					sendmailto:		jQuery('#mDialog .text input[name=emailto]').val()
				},
				function(data){
                    var response = elaborateJsonResponse(data, true);
					if (response == false) {
                        jQuery("#mDialog").dialog("close");
					} else {
                        jQuery('#mDialog .title').html(response);
                        jQuery('#mDialog').dialog( "option", "buttons", {
                            "Chiudi": function() {jQuery("#mDialog").dialog("close"); }
                        });
					}
				}
			);
			jQuery('#mDialog .title').html(TRANS["COM_AZMAILER_NEWSLETTER_SEND_TEST_MSG_SENDING"]);
			jQuery('#mDialog .text').html("");
			jQuery('#mDialog').dialog( "option", "buttons", {});
		}},
		{
			text: TRANS["COM_AZMAILER_CANCEL"],
			click: function() { jQuery(this).dialog("close"); }
		}
		]
	);
	jQuery('#mDialog').dialog('open');
}


function getSimpleTextVersion() {
	if(confirm(TRANS["COM_AZMAILER_NEWSLETTER_SIMPLETEXT_CONFIRM"])) {
		jQuery.post("index.php", {
                task:           "newsletter.getNewsletterSimpleTextVersion",
                format:         "raw",
                option:         jQuery("form#adminForm input[name=option]").val(),
				tpl_id:			jQuery('form[name=adminForm] input[name=nl_template_id]').val(),
				tpl_subst:		jQuery('form[name=adminForm] input[name=nl_template_substitutions]').val()
			},
			function(data){
                var text = elaborateJsonResponse(data, true);
				jQuery("form[name=adminForm] input[name=nl_textversion]").val(text);
				jQuery("textarea[name=nltxt]").html(text);
			}
		);
	}
}
