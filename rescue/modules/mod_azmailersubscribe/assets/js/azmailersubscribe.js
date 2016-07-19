/**
 * @package AZMailer subsription module
 * @author Adam Jakab
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
**/

if (typeof jQuery == 'undefined') {
    alert("AZMailer Subscription Module says:\njQuery is not loaded!\nModule will not work!");
}

jQuery(document).ready(function($) {
    var azmodule = $(".mod_azmailersubscribe");
    var azform = $("form[name=azmailersbcb]", azmodule);
    var infobox = $('<div><h3 class="infobox_title"></h3><div class="infobox_body"></div></div>');

    //OPEN PRIVACY INFO AND WELCOME IN FANCYBOX
    if($.fancybox) {
        $("a.fancybox", azmodule).fancybox({
            type : 'iframe',
            padding: 10,
            width: 600,
            height: 300
        });
    }


    //GEO SELECT BOXES
    var SBC = ($("select[name=country]", azform).length>0?$("select[name=country]", azform):false);
    var SBR = ($("select[name=region]", azform).length>0?$("select[name=region]", azform):false);
    var SBP = ($("select[name=province]", azform).length>0?$("select[name=province]", azform):false);

    //HANDLE SELECT BOXES
    if (SBP) {SBP.change(provinceChange);}
    if (SBR) {SBR.change(regionChange);}
    if (SBC) {SBC.change(countryChange); getCountries();}

    //HANDLE FORM SUBMISSION
    $("input[type=button]", azform).click(checkAndSubmit);


    //----------------------------------------------------------------------------------functions
    function checkAndSubmit() {
        //CHECK AND REGISTER SUBSCRIBER
        var token = $("span#azmailersbcbtoken input[type=hidden]", azform).attr("name");
        var postdata = {
            option:             $("input[name=option]", azform).val(),
            task:               "azmailer.registerNewsletterSubscriber",
            format:             "raw",
            nls_firstname:      $("input[name=firstname]", azform).val(),
            nls_lastname:       $("input[name=lastname]", azform).val(),
            nls_email:          $("input[name=email]", azform).val(),
            nls_country_id:     $("select[name=country]", azform).val(),
            nls_region_id:      $("select[name=region]", azform).val(),
            nls_province_id:    $("select[name=province]", azform).val(),
            nls_privacy:        ($("input[name=privacy]", azform).length>0?($("input[name=privacy]", azform).is(":checked")? 1:0):1)
        };
        postdata[token] = 1;
        $.post("index.php", postdata,
            function(data){
                var parsedData = elaborateJsonResponse(data, true);
                if(parsedData != false) {
                    resetForm();
                    //ok subscriber was registered
                    if ($("a.welcome_link", azmodule).length > 0) {
                        var wlink = $("a.welcome_link", azmodule);
                        if (wlink.hasClass("fancybox")) {
                            wlink.trigger('click');
                        } else {
                            window.location.href = wlink.attr("href");
                        }
                    } else {
                        if(parsedData!==false) {
                            alert(parsedData);//no link just show ok message
                        }
                    }
                }
            }
        );
    }


    function countryChange() {
        var cid = SBC.val();
        if (SBR) {getRegions(cid);}
    }
    function regionChange() {
        var rid = SBR.val();
        if (SBP) {getProvinces(rid);}
    }
    function provinceChange() {
        var pid = SBP.val();
    }


    function getCountries() {
        if (SBC) {
            SBC.html("").append($('<option></option>').val(0).html("..."));
            $.post("index.php", {
                    option:         $("input[name=option]", azform).val(),
                    task:           "azmailer.getSelectOptionsCountries",
                    format:         "raw"
                },
                function(data) {
                    var parsedData = elaborateJsonResponse(data, true);
                    if(parsedData!=false) {
                        SBC.html("");
                        parsedData.each(function(country,index) {
                            SBC.append($('<option></option>').val(country.id).html(country.data));
                        });
                        countryChange();
                    }
                }
            );
        }
    }

    function getRegions(cid) {
        if (SBR) {
            SBR.html("").append($('<option></option>').val(0).html("..."));
            $.post("index.php", {
                    option:         $("input[name=option]", azform).val(),
                    task:           "azmailer.getSelectOptionsRegions",
                    format:         "raw",
                    country_id:     cid
                },
                function(data){
                    var parsedData = elaborateJsonResponse(data, true);
                    if(parsedData!=false) {
                        SBR.html("");
                        parsedData.each(function(region,index) {
                            SBR.append($('<option></option>').val(region.id).html(region.data));
                        });
                        regionChange();
                    }
                }
            );
        }
    }

    function getProvinces(rid) {
        if (SBP) {
            SBP.html("").append($('<option></option>').val(0).html("..."));
            $.post("index.php", {
                    option:         $("input[name=option]", azform).val(),
                    task:           "azmailer.getSelectOptionsProvinces",
                    format:         "raw",
                    region_id:		rid
                },
                function(data){
                    var parsedData = elaborateJsonResponse(data, true);
                    if(parsedData!=false) {
                        SBP.html("");
                        parsedData.each(function(province,index) {
                            SBP.append($('<option></option>').val(province.id).html(province.data));
                        });
                        provinceChange();
                    }
                }
            );
        }
    }

    function elaborateJsonResponse(data, showErrors) {
        var errors = false;
        var answer = false;
        try {
            var jsonObj = (typeof data!="object"?JSON.parse(data):data);
            errors = jsonObj.errors;
            answer = jsonObj.result;
        } catch(e) {
            errors = "Unable to parse string!\n"+e;
        }
        if (showErrors && errors.length > 0) {
            var errMsg = "";
            resetInfoBox();
            errors.each(function(err,index) {
                addMessageToInfoBox(err.message);
            });
            openInfoBox("ERROR!");
            answer = false;
        }
        return(answer);
    }

    function resetForm() {
        $("input[name=firstname]", azform).val("").focus().blur();
        $("input[name=lastname]", azform).val("").focus().blur();
        $("input[name=email]", azform).val("").focus().blur();
        $("input[name=privacy]", azform).attr('checked', false);
        getCountries();
    }

    //infobox - var infobox = $('<h3 class="infobox_title"></h3><div class="infobox_body"></div>');
    function resetInfoBox() {
        $(".infobox_body", infobox).html('');
    }

    function addMessageToInfoBox(msg) {
        $(".infobox_body", infobox).append('<p>'+msg+'</p>');
    }

    function openInfoBox(title) {
        $(".infobox_title", infobox).html(title);
        $.fancybox(infobox, {
            padding: 10,
            autoSize: false,
            width: 400,
            height: 'auto'
        });
    }

});