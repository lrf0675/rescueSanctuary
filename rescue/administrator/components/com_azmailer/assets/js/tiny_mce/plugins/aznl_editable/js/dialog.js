tinyMCEPopup.requireLangPack();

var AznlEditableDialog = {
	inst		: null,
	dom 		: null,
	el 			: null,
	eltag		: null, 
	f 			: null,
	ISEDITABLE	: false,
	ELREL		: {},
	
	init: function() {
		tinyMCEPopup.resizeToInnerSize();
		inst = tinyMCEPopup.editor;
		dom = inst.dom;
		el = inst.selection.getNode();
		eltag = jQuery(el).prop("tagName").toLowerCase();
		f = document.forms[0];
		//set checkbox to current element's state
		if (jQuery(el).hasClass("editable")) {
			jQuery(f.is_editable).prop("checked","checked");
		}
		//setup listener
		jQuery(f.is_editable).change(this.isEditableAttrChange);
		//first run changer func
		this.isEditableAttrChange();
	},
	
	isEditableAttrChange: function() {
		ISEDITABLE = jQuery(f.is_editable).is(':checked');
		var dialogHtml = '<hr />';
		if (!ISEDITABLE) {
			jQuery('div[name=ed_el_div]', f).html(dialogHtml);
			return;
		}
		//Element is editable - let's get REL attrib and check current/default values
		var relStr = jQuery(el).attr("rel");
		if (!relStr || relStr=="" || relStr=="undefined") {relStr='{}';}//relStr should be a valid stringified Json
		relStr = relStr.replace(/\'/g,'"');
		//alert(eltag + " - " + relStr);
		try {
			ELREL = jQuery.parseJSON(relStr);
		} catch(e) {alert("opps:"+e);/*ooops!*/}
		if (typeof ELREL!=="object") {ELREL = new Object();}
		//
		if (eltag == 'img') {
			ELREL.type = 'image';
			if (!ELREL.width) {
				ELREL.width = parseInt(jQuery(el).prop("width"));
			}
		} else {
			if (ELREL.type!='text'&&ELREL.type!='html') {
				ELREL.type = 'text';
			}
		}
		//let's get uniqueElementId
		var uid = jQuery(el).prop("id");
		if (!uid || uid=="" || uid=="undefined") {uid=dom.uniqueId("aznl_");}
		
		//
		if (ELREL.type == 'image') {
			dialogHtml += 'Element Type: <b>Image</b><br /><br />';
			dialogHtml += 'Element Id: <input type="text" name="el_uniqueid" value="'+uid+'"/><br /><br />';
			dialogHtml += 'Image Width: <input type="text" name="el_width" value="'+ELREL.width+'"/>';
			dialogHtml += '<a name="imgWreset" title="Reset width to current image size" style="cursor:pointer;"> Reset!</a><br />';
		} else {
			dialogHtml += 'Element Type: ';
			dialogHtml += '<select name="el_type">';
			dialogHtml += '<option value="text"'+(ELREL.type=='text'?' selected="selected"':'')+'>Text</option>';
			dialogHtml += '<option value="html"'+(ELREL.type=='html'?' selected="selected"':'')+'>Html</option>';
			dialogHtml += '</select><br /><br />';
			dialogHtml += 'Element Id: <input type="text" name="el_uniqueid" value="'+uid+'"/><br />';
		}
		
		jQuery('div[name=ed_el_div]', f).html(dialogHtml);
		
		//type select box change function
		if (jQuery('select[name=el_type]', f).length == 1) {
			jQuery('select[name=el_type]', f).change(function() {
				ELREL.type = jQuery(this).val();
			});
		}
		//image size reset change function
		if (jQuery('a[name=imgWreset]', f).length == 1) {
			jQuery('a[name=imgWreset]', f).click(function() {
				var w = parseInt(jQuery(el).prop("width"));
				ELREL.width = w;
				jQuery('input[name=el_width]', f).val(w);
			});
		}
	},

	
	/**/
	setAndClose: function() {
		this.setAllAttribs();
		tinyMCEPopup.execCommand("mceEndUndoLevel");
		tinyMCEPopup.close();
	},
	
	setAllAttribs: function() {
		//SET/UNSET EDITABLE CLASS
		if (ISEDITABLE) {
			if (!jQuery(el).hasClass("editable")) {
				var classes = (jQuery(el).prop('class')!="undefined"?jQuery(el).prop('class'):"");
				classes = classes + ' editable';
				dom.setAttrib(el, 'class', classes);
			}
		} else {
			if (jQuery(el).hasClass("editable")) {
				var classes = jQuery(el).prop('class').replace('editable','');
				classes = jQuery.trim(classes);
				dom.setAttrib(el, 'class', classes);
			}
		}
		//SET/UNSET THE REL ATTRIBUTE
		if (ISEDITABLE) {
			relStr = jQuery.toJSON(ELREL);//{"type":"image","width":229} 
			relStr = relStr.replace(/\"/g,"'");
			//alert(relStr);
			dom.setAttrib(el, 'rel', relStr);
		} else {
			dom.setAttrib(el, 'rel', null);
		}
		//SET THE ID ATTRIBUTE
		if (ISEDITABLE) {
			var uid = jQuery(f.el_uniqueid).val();
			if (!uid || uid=="" || uid=="undefined") {uid=dom.uniqueId("aznl_");}//@JACK - should check html content for unique
			dom.setAttrib(el, 'id', uid);
		}
	}
	
	
};

tinyMCEPopup.onInit.add(AznlEditableDialog.init, AznlEditableDialog);
