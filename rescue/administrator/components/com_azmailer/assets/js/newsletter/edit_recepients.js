jQuery(document).ready(function($){

	//TRIGGER CONTACT CATEGORY SELECTION/CHANGE - CONTACTS RELOAD
	jQuery("#NL_CATEGORY_SELECTORS input[type=checkbox], input[type=radio][name=nlcb]").change(function() {//this is to select/deselect destination categories
		catCheckboxValueChange(jQuery(this));
	});

	//TRIGGER CONTACT CATEGORY RESET(NO SELECTION) - CONTACTS RELOAD
	jQuery("a[name=cat_selection_reset]").click(function() {//this is to select/deselect destination categories
		catCheckboxResetAll();
	});


	//TRIGGER XLS FILE UPLOAD
	jQuery("a[name=xls_upload]").click(function() {//this is to upload xls
		uploadExcelFile();
	});

	//TRIGGER ADDITIONAL CONTACT REMOVAL
	jQuery("a[name=additional_cnt_remove]").click(function() {//this is to remove all additional contacts
		var rel = jQuery(this).attr("rel");//either "all" or single e-mail to remove
		removeAdditionalContact(rel);
	});

	//TRIGGER LOCATION SELECT UPDATES
	$("select#nls_country_id, select#nls_region_id, select#nls_province_id").change(function() {
		locSelectionChanged($(this));
    });


});




/*-----------------------------------------------------------------------------------------------------------------SELEZIONE DESTINATARI---*/
function refreshSendtoOptions() {
    //SELECTIONS FORMAT: {selectionBehaviour:"PLUSOR", cat1:[],cat2:[],cat3:[],cat4:[],cat5:[], country:0, region:0, province:0}
    var NLSS = getNewsletterSendtoSelectionObject();

    //update category selection behaviour radios nlcb
    var behaviourType = NLSS.selectionBehaviour;
    if (!behaviourType || (behaviourType!="PLUSOR"&&behaviourType!="PLUSAND"&&behaviourType!="MINUSAND")) {
        behaviourType = "PLUSAND";
    }
    jQuery("input[name=nlcb][value="+behaviourType+"]").attr("checked",true);
    jQuery("div[name=catSelBehaviourDesc] span").html(CSBD[behaviourType]);

    //update checkbox selections checks
    //THE CATEGORY SELECTION LIST -  - lists are called nlsc_N
    for (var cn=1; cn <= 5; cn++) {
        if (NLSS["cat"+cn]) {
            if (NLSS["cat"+cn].length > 0) {
                for (var i = 0; i < NLSS["cat"+cn].length; i++) {
                    var val = NLSS["cat"+cn][i];
                    //alert(cn+" - "+i+" - "+val);
                    jQuery("#NL_CATEGORY_SELECTORS input[type=checkbox][name=nlsc_"+cn+"][value='"+val+"']").attr("checked",true);
                }
            }
        }
    }
    //
    getSendtoData();
    updateSelectBox_County();
}


function catCheckboxValueChange(el) {
    var eltype = el.attr("type");
    var elname = el.attr("name");
    var elValue = el.val();
    var elChecked = el.is(':checked');
    //NLSS FORMAT: {selectionBehaviour:"PLUSOR", cat1:[],cat2:[],cat3:[],cat4:[],cat5:[], country:0, region:0, province:0}
    var NLSS = getNewsletterSendtoSelectionObject();
    if (eltype == "radio") {//CATEGORY SELECTION BEHAVIOUR CHANGE
        setValueOnNewsletterSendtoSelection("selectionBehaviour", elValue);
        jQuery("div[name=catSelBehaviourDesc] span").html(CSBD[elValue]);
    } else {//CATEGORY ITEM CHECKBOX CHANGE
        var catNumber = parseInt(elname.substr(-1));//1-5
        if (catNumber>0&&catNumber<=5) {
            var catArray = (NLSS["cat"+catNumber]?NLSS["cat"+catNumber]:new Array());
            if (elChecked) {//adding
                catArray.push(elValue);
            } else {
                var i = catArray.indexOf(elValue);
                if (i!=-1) {
                    catArray.splice(i,1);
                }
            }
            setValueOnNewsletterSendtoSelection("cat"+catNumber, catArray);
        }
    }
    getSendtoData();
}

function catCheckboxResetAll() {
	//NLSS FORMAT: {selectionBehaviour:"PLUSOR", cat1:[],cat2:[],cat3:[],cat4:[],cat5:[], country:0, region:0, province:0}
	for(var i=1; i<=5; i++) {
        setValueOnNewsletterSendtoSelection("cat"+i, []);
	}
    jQuery("#NL_CATEGORY_SELECTORS input[type=checkbox]:checked").attr("checked", false);
    getSendtoData();
}

function getSendtoData() {
    jQuery.post("index.php", {
            task:                   "newsletter.getNewsletterSendtoData",
            format:                 "raw",
            option:                 jQuery("form#adminForm input[name=option]").val(),
            nl_sendto_selections:   jQuery('form[name=adminForm] input[name=nl_sendto_selections]').val(),
            nl_sendto_additional:   jQuery('form[name=adminForm] input[name=nl_sendto_additional]').val()
        },
        function(data){
            var result = elaborateJsonResponse(data, true);
            var CATSELCONTACTS = jQuery.parseJSON(jQuery.base64Decode(result.CATSELCONTACTS));
            var CATSELQUERY = jQuery.parseJSON(jQuery.base64Decode(result.CATSELQUERY));
            var XLSCONTACTS = jQuery.parseJSON(jQuery.base64Decode(result.XLSCONTACTS));
            var COUNT = result.COUNT;
            //
            jQuery('input[name=nl_selectcount]').val(COUNT);
            jQuery('span[name=numberOfRecepients]').html(COUNT);
            jQuery('div[name=catSelSql]').html(CATSELQUERY);


            //THE ADDITIONALLY UPLOADED XLS CONTACTS
            var html = '';
            jQuery.each(XLSCONTACTS, function(i, XC) {//XC has: nls_email, nls_firstname, nls_lastname
                var li_id = '';
                var li_class = '';
                var li_remove = '<a name="additional_cnt_remove_single" rel="'+XC["nls_email"]+'" style="float:right;width:16px; height:16px;cursor:pointer; " class="ui-icon ui-icon-circle-minus" ></a>';

                html += '<li '+li_id+' '+li_class+'>';
                html += '<div class="nla_mail">' + XC["nls_email"] + '</div>';
                html += '<div class="nla_name">' + XC["nls_lastname"] + ' ' + XC["nls_firstname"] + li_remove + '</div>';

                html += '</li>';
            });
            jQuery('ul#NL_additional').html(html);
            jQuery("a[name=additional_cnt_remove_single]").click(function() {
                var rel = jQuery(this).attr("rel");//single e-mail to remove
                removeAdditionalContact(rel);
            });

            //THE CATEGORY SELECTION LIST CONTACTS - NL_catselects
            //CATSELCONTACTS
            var html = '';
            jQuery.each(CATSELCONTACTS, function(i, XC) {//XC has: nls_email, nls_firstname, nls_lastname
                var li_id = '';
                var li_class = '';
                html += '<li '+li_id+' '+li_class+'>';
                html += '<div class="nla_mail">' + XC["nls_email"] + '</div>';
                html += '<div class="nla_name">' + XC["nls_lastname"] + ' ' + XC["nls_firstname"] + '</div>';
                html += '</li>';
            });
            jQuery('ul#NL_catselects').html(html);
        }
    );
}

function uploadExcelFile() {
    alert("Temporarily disabled!");return;
	jQuery('#mDialog').dialog( "option", "title", TRANS["COM_AZMAILER_NEWSLETTER_TIT_UPLOAD_XLS"] );
	jQuery('#mDialog .title').html(TRANS["COM_AZMAILER_NEWSLETTER_DESC_UPLOAD_XLS"]);
	var formhtml = 	'<form id="xlsUploader" action="index.php?option='+com_name+'&controller='+controller_name+'&task=ajaxUploadXls&tmpl=component" method="post" enctype="multipart/form-data" encoding="multipart/form-data">'
					+ '<input type="file" size="60" name="file" />'
					+ '<input type="hidden" name="nl_sendto_additional" value="'+jQuery('form[name=adminForm] input[name=nl_sendto_additional]').val()+'" />'
					+ '</form>';
	jQuery('#mDialog .text').html(formhtml);
	jQuery('#mDialog').dialog( "option", "buttons", [
	    {
	    text: TRANS["COM_AZMAILER_UPLOAD"],
		click: function() {
			jQuery('#mDialog .text form#xlsUploader').submit();
			jQuery('#mDialog').dialog( "option", "buttons", {});
			jQuery('#mDialog .text').html('<h2>'+TRANS["COM_AZMAILER_UPLOADING"]+'</h2>');
		}},
		{
		text: TRANS["COM_AZMAILER_CANCEL"],
		click: function() { jQuery(this).dialog("close"); }
		}
	    ]
	);

	jQuery('#mDialog .text form#xlsUploader').ajaxForm(function(data) {
			//alert(data);
			var parsed_JSON = jQuery.parseJSON(data);
			if (parsed_JSON.error === false) {
				jQuery('form[name=adminForm] input[name=nl_sendto_additional]').val(parsed_JSON.ADDITIONAL);
				refreshSendtoOptions();
			} else {
				alert(parsed_JSON.error);
			}
			jQuery('#mDialog').dialog("close");
    });
	jQuery('#mDialog').dialog('open');

}

function removeAdditionalContact(rel) {//rel is either "all" or single e-mail to remove
    alert("Temporarily disabled!");return;
	var question = (rel=="all"?TRANS["COM_AZMAILER_NEWSLETTER_DEL_CONFIRM_ALL_CONTACTS"]:TRANS["COM_AZMAILER_NEWSLETTER_DEL_CONFIRM_SINGLE_CONTACT"] + rel + "?");
	if (confirm(question)) {
		jQuery.post("index.php", {
				option:							com_name,
				controller:						controller_name,
				task:							"removeAdditionalContact",
				tmpl:							"component",
				removeWhat:						rel,
				nl_sendto_additional:			jQuery('form[name=adminForm] input[name=nl_sendto_additional]').val()
			},
			function(data){
				//alert(data);
				var parsed_JSON = jQuery.parseJSON(data);
				jQuery('form[name=adminForm] input[name=nl_sendto_additional]').val(parsed_JSON.ADDITIONAL);
				refreshSendtoOptions();
			}
		);
	}
}

//----------------------------------------------------------------------------------------------GEO LOCATION SELECTIONS

function updateSelectBox_County() {//nls_country_id
	jQuery("select#nls_country_id").children().remove();
	jQuery.post("index.php", {
        task:           "newsletter.getSelectOptionsCountries",
        format:         "raw",
        option:         jQuery("form#adminForm input[name=option]").val()
        },
        function(data){
            var response = elaborateJsonResponse(data, true);
            var NLSS = getNewsletterSendtoSelectionObject();
            jQuery.each(response, function(k,d) {
                jQuery("select#nls_country_id").append(jQuery('<option>', {value: d.id}).text(d.data));
                if (d.id==NLSS.country) {
                    jQuery("select#nls_country_id option[value="+d.id+"]").attr("selected","selected");
                }
            });
            updateSelectBox_Region();
        }
    );
}


function updateSelectBox_Region() {
    jQuery("select#nls_region_id").children().remove();
    jQuery.post("index.php", {
            task:           "newsletter.getSelectOptionsRegions",
            format:         "raw",
            option:         jQuery("form#adminForm input[name=option]").val(),
            country_id:     jQuery("select#nls_country_id").val()
        },
        function(data){
            var response = elaborateJsonResponse(data, true);
            var NLSS = getNewsletterSendtoSelectionObject();
            jQuery.each(response, function(k,d) {
                jQuery("select#nls_region_id").append(jQuery('<option>', {value: d.id}).text(d.data));
                if (d.id==NLSS.region) {
                    jQuery("select#nls_region_id option[value="+d.id+"]").attr("selected","selected");
                }
            });
            updateSelectBox_Province();
        }
    );
}


function updateSelectBox_Province() {
    jQuery("select#nls_province_id").children().remove();
    jQuery.post("index.php", {
            task:           "newsletter.getSelectOptionsProvinces",
            format:         "raw",
            option:         jQuery("form#adminForm input[name=option]").val(),
            region_id:      jQuery("select#nls_region_id").val()
        },
        function(data){
            var response = elaborateJsonResponse(data, true);
            var NLSS = getNewsletterSendtoSelectionObject();
            jQuery.each(response, function(k,d) {
                jQuery("select#nls_province_id").append(jQuery('<option>', {value: d.id}).text(d.data));
                if (d.id==NLSS.province) {
                    jQuery("select#nls_province_id option[value="+d.id+"]").attr("selected","selected");
                }
            });
        }
    );
}


function locSelectionChanged(el) {
	var elName = el.attr("name");
    var elValue = jQuery("option:selected", el).val();
    //alert("SEL-"+elName+" - " + elValue);
    if (elName == "nls_country_id") {
    	elType = "country";
    	locationSelectValueChange("region", 0, false);
    	locationSelectValueChange("province", 0, false);
    	updateSelectBox_Region();
    } else if (elName == "nls_region_id") {
    	elType = "region";
    	locationSelectValueChange("province", 0, false);
    	updateSelectBox_Province();
    } else if (elName == "nls_province_id") {
    	elType = "province";
    }
    locationSelectValueChange(elType, elValue, true);
}

function locationSelectValueChange(name, value, doQuery) {
    setValueOnNewsletterSendtoSelection(name, value);
    if (doQuery) {
    	getSendtoData();
    }
}


function getNewsletterSendtoSelectionObject() {
    var NLSS = jQuery.parseJSON(jQuery.base64Decode(jQuery("form[name=adminForm] input[name=nl_sendto_selections]").val()));
    var doSet = false;
    if(!NLSS.selectionBehaviour){NLSS.selectionBehaviour = "PLUSAND";doSet = true;}
    if(!NLSS.cat1){NLSS.cat1 = [];doSet = true;}
    if(!NLSS.cat2){NLSS.cat2 = [];doSet = true;}
    if(!NLSS.cat3){NLSS.cat3 = [];doSet = true;}
    if(!NLSS.cat4){NLSS.cat4 = [];doSet = true;}
    if(!NLSS.cat5){NLSS.cat5 = [];doSet = true;}
    if(!NLSS.country){NLSS.country = 0;doSet = true;}
    if(!NLSS.region){NLSS.region = 0;doSet = true;}
    if(!NLSS.province){NLSS.province = 0;doSet = true;}
    //alert(JSON.stringify(NLSS));
    if(doSet) {
        jQuery("form[name=adminForm] input[name=nl_sendto_selections]").val(jQuery.base64Encode(jQuery.toJSON(NLSS)));
    }
    return(NLSS);
}

function setValueOnNewsletterSendtoSelection(key, val) {
    var NLSS = getNewsletterSendtoSelectionObject();
    NLSS[key] = val;
    jQuery("form[name=adminForm] input[name=nl_sendto_selections]").val(jQuery.base64Encode(jQuery.toJSON(NLSS)));
}