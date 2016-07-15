jQuery(document).ready(function ($) {


    // ADD NEW ATTACHMENT BUTTON
    $("a[name=nl_addattachment]").click(function () {
        openAddAttachmentPanel();
        return(false);
    });

    loadAndListAttachments();






    function loadAndListAttachments() {
        var attDiv = $("div#NLATTACHMENTS");
        if (!$("form#adminForm input[name=id]").val()) {
            attDiv.html("<br />"+TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_SAVEFIRST"]+"<br /><br />");
            return;
        }
        attDiv.html("<br />...<br /><br />");
        //
        $.post("index.php", {
                task:           "newsletter.getNewsletterAttachments",
                format:         "raw",
                option:         $("form#adminForm input[name=option]").val(),
                nlid:           $("form#adminForm input[name=id]").val()
            },
            function(data){
                var respObj = elaborateJsonResponse(data, false, true);
                if(respObj===false || respObj.errors.length != 0) {
                    attDiv.html("<br />"+TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_ERR_LIST"]+"<br /><br />");
                } else {
                    if(respObj.result.length == 0) {
                        attDiv.html("<br />"+TRANS["COM_AZMAILER_NEWSLETTER_NO_ATTACHMENTS"]+"<br /><br />");
                    } else {
                        var attListHtml = '';
                        attListHtml += '<ul class="attachments">';
                        for(var i=0; i<respObj.result.length; i++) {
                            var attObj = respObj.result[i];
                            attListHtml += '<li>';
                            attListHtml += '<div class="fllft name">'+attObj.name+'</div>';
                            attListHtml += '<div class="fllft size">'+TRANS["COM_AZMAILER_SIZE"]+': '+getHumanReadableFileSize(attObj.size)+'</div>';
                            attListHtml += '<div class="fllft type">'+TRANS["COM_AZMAILER_TYPE"]+': '+attObj.type+'</div>';

                            attListHtml += '<div class="flrgt button"><a name="nl_openattachment" href="'+attObj.downloadUrl+'" target="_blank" title="'+TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_OPEN"]+'"><span class="ui-icon ui-icon-disk"></span></a></div>';
                            attListHtml += '<div class="flrgt button"><a name="nl_deleteattachment" data-name="'+attObj.name+'" data-filename="'+attObj.filename+'" style="cursor:pointer;" title="'+TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_DELETE"]+'"><span class="ui-icon ui-icon-trash"></span></a></div>';

                            attListHtml += '<div style="clear:both;"></div>';
                            attListHtml += '</li>';
                        }
                        attListHtml += '</ul>';

                        //attListHtml += '<hr />' + JSON.stringify(respObj);
                        attDiv.html(attListHtml);
                        //set handlers
                        $("a[name=nl_deleteattachment]", attDiv).click(function (el) {
                            deleteAttachmentFromNewsletter($(this));
                            return(false);
                        });
                    }
                }
            }
        );
    }



    function deleteAttachmentFromNewsletter(el) {
        if(el) {
            var name = $(el).attr("data-name");
            if(confirm(TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_DELETE_CONFIRM"] + "\n" + name)) {
                var filename = $(el).attr("data-filename");
                $.post("index.php", {
                        task:           "newsletter.removeNewsletterAttachment",
                        format:         "raw",
                        option:         $("form#adminForm input[name=option]").val(),
                        nlid:           $("form#adminForm input[name=id]").val(),
                        filename:       filename
                    },
                    function(data){
                        var res = elaborateJsonResponse(data, true);
                        //alert(JSON.stringify(res));
                        loadAndListAttachments();
                    }
                );
            }
        }

    }

    function openAddAttachmentPanel() {
        if (!$("form#adminForm input[name=id]").val()) {
            alert(TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_SAVEFIRST"]);
            return;
        }
        var mDialog = $('#mDialog');
        mDialog.dialog("option", "title", TRANS["COM_AZMAILER_NEWSLETTER_TIT_ADD_ATTACHMENT"]);
        $('.title', mDialog).html(TRANS["COM_AZMAILER_NEWSLETTER_TIT_DESC_ATTACHMENT"]);
        var formhtml = '<form id="attachmentUploader">'
            + '<input type="file" name="fileToUpload" id="fileToUpload" />'
            + '</form>'
            + '';
        $('.text', mDialog).html(formhtml);
        mDialog.dialog("option", "buttons", [
            {
                text: TRANS["COM_AZMAILER_CANCEL"],
                click: function () {
                    mDialog.dialog("close");
                }
            }
        ]);


        $('input#fileToUpload', mDialog).change(function() {
            if(getUploadableFile() !== false) {
                mDialog.dialog("option", "buttons", [
                    {
                        text: TRANS["COM_AZMAILER_UPLOAD"],
                        click: function () {
                            doAttachmentUpload();
                        }},
                    {
                        text: TRANS["COM_AZMAILER_CANCEL"],
                        click: function () {
                            mDialog.dialog("close");
                        }
                    }
                ]);
            } else {
                //BUTTONS
                mDialog.dialog("option", "buttons", [
                    {
                        text: TRANS["COM_AZMAILER_CANCEL"],
                        click: function () {
                            mDialog.dialog("close");
                        }
                    }
                ]);
            }
        });

        //check and return file if valid - if not valid return false
        function getUploadableFile() {
            var answer = false;
            //{"webkitRelativePath":"","lastModifiedDate":"2013-07-29T17:10:21.000Z","name":"2013-07-05 18.35.36.jpg","type":"image/jpeg","size":295170}
            var file = jQuery('input#fileToUpload').get(0).files[0];
            if(file) {
                answer = true;//from here on you have to negate
                var extension = file.name.substr(file.name.lastIndexOf(".")+1).toLowerCase();
                //CHECK FOR ALLOWED EXTENSIONS
                if(answer) {
                    if(allowed_attachment_extensions.indexOf(extension) == -1) {
                        answer = false;
                        alert(TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_BAD_EXTENSION"]);
                    }
                }

                //TODO:CHECK FOR ALLOWED MIME TYPES
                if(answer) {
                    //check...
                }

                //CHECK FOR SIZE
                if(answer) {
                    var fileSize = parseInt(file.size);
                    if(fileSize<=0 || fileSize>max_allowed_upload_size) {
                        answer = false;
                        alert(TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_BAD_FILESIZE"] + getHumanReadableFileSize(max_allowed_upload_size));
                    }
                }
            }
            return(answer?file:answer);
        }

        function doAttachmentUpload() {
            var file = getUploadableFile();
            if(file) {
                mDialog.dialog( "option", "buttons", {});
                mDialog.dialog( "option", "title", TRANS["COM_AZMAILER_UPLOADING"] );
                $('.text', mDialog).html(''
                    + TRANS["COM_AZMAILER_NAME"] + ': ' + file.name + '<br />'
                    + TRANS["COM_AZMAILER_SIZE"] + ': ' + getHumanReadableFileSize(file.size) + '<br />'
                    + '<div id="uploadProgress">---</div>'
                    + ''
                );
                //create FORM to send
                var fd = new FormData();
                fd.append("fileToUpload", file);
                fd.append("option", com_name);
                fd.append("task", "newsletter.uploadNewsletterAttachment");
                fd.append("format", "raw");
                fd.append("nlid", $("form#adminForm input[name=id]").val());
                //
                var xhr = new XMLHttpRequest();
                xhr.upload.addEventListener("progress", xhrUploadProgress, false);
                xhr.addEventListener("load", xhrUploadComplete, false);
                xhr.addEventListener("error", xhrUploadError, false);
                xhr.addEventListener("abort", xhrUploadAbort, false);
                xhr.open("POST", "index.php");
                xhr.send(fd);
            } else {
                alert("ERROR! File is undefined!");
            }
        }
        function xhrUploadError(ev) {alert("Error uploading file!\n" + ev);}
        function xhrUploadAbort(ev) {alert("File upload was aborted!\n" + ev);}
        function xhrUploadProgress(ev) {
            var perc = 0;
            if (ev.lengthComputable) {
                var perc = Math.round(ev.loaded * 100 / ev.total);
                $("#uploadProgress", mDialog).html(TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_UPLOADED"]+perc+"%");
            }
        }
        function xhrUploadComplete(ev) {
            var respObj = elaborateJsonResponse(ev.target.responseText, false, true);
            if(respObj===false) {
                mDialog.dialog( "option", "title", TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_TIT_UPLOAD_ERROR"] );
                $('.title', mDialog).html(TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_TIT_UPLOAD_ERROR"]);
                $('.text', mDialog).html(ev.target.responseText);
                mDialog.dialog("option", "buttons", [
                    {
                        text: TRANS["COM_AZMAILER_CANCEL"],
                        click: function () {
                            mDialog.dialog("close");
                        }
                    }
                ]);
            } else {
                if (respObj.errors.length != 0) {
                    mDialog.dialog( "option", "title", TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_TIT_UPLOAD_ERROR"] );
                    $('.title', mDialog).html(TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_TIT_UPLOAD_ERROR"]);
                    var errHtml = respObj.errors[0];
                    $('.text', mDialog).html(errHtml);
                    mDialog.dialog("option", "buttons", [
                        {
                            text: TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_BTN_UPLOAD_ANOTHER"],
                            click: function () {
                                mDialog.dialog("close");
                                openAddAttachmentPanel();//restart
                            }
                        },
                        {
                            text: TRANS["COM_AZMAILER_CANCEL"],
                            click: function () {
                                mDialog.dialog("close");
                            }
                        }
                    ]);
                } else {
                    jQuery('#mDialog').dialog("close");
                    loadAndListAttachments();
                }
            }
        }
        jQuery('#mDialog').dialog('open');
    }

});