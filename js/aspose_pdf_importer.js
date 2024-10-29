jQuery(document).ready(function($){

    jQuery.ajax
    ({
        type : "post",
        dataType : "html",
        url : AsposePdfParams['aspose_files_url'],
        data : {appSID: AsposePdfParams['appSID'], appKey : AsposePdfParams['appKey']},
        success: function(response) {
            $('#aspose_cloud_pdf').append(response);

        }
    });

    //$('#aspose_folder_name').on('change', null,function() {
    //    var selected_folder_name = $(this).val();
    //    if(selected_folder_name != '') {
    //        jQuery.ajax
    //        ({
    //            type : "post",
    //            dataType : "html",
    //            url : AsposePdfParams['aspose_files_url'],
    //            data : {appSID: AsposePdfParams['appSID'], appKey : AsposePdfParams['appKey'], aspose_folder : selected_folder_name},
    //            success: function(response) {
    //                $('#aspose_cloud_pdf').html(response);
	//
    //            }
    //        });
    //    }
    //});



    $('#tabs').tabs();
    $('#aspose_pdf_popup').on("click", null,function(){
        $("#aspose_pdf_popup_container").dialog('open');
    });
    $("#aspose_pdf_popup_container").dialog({ 
        autoOpen: false,
        resizable: false,
        modal: true,
        width:'auto',
        height:'300',
    });


    $('#insert_pdf_content').on('click',null,function(){

        var filename = $('#pdf_file_name').val();
		var fileurl =  $('#pdf_file_url').val();
		var pluginname = $('#aspose_pdf_importer_name').val();
		var pluginversion =  $('#aspose_pdf_importer_version').val();
        $("#aspose_pdf_popup_container").dialog('close');
        $body = $("body");
        $body.addClass("loading");

        jQuery.ajax
        ({
            type : "post",
            dataType : "html",
            url : AsposePdfParams['insert_pdf_url'],
            data : {appSID: AsposePdfParams['appSID'], appKey : AsposePdfParams['appKey'], filename : filename, fileurl : fileurl, pluginname : pluginname, pluginversion : pluginversion, uploadpath: AsposePdfParams['uploadpath'] , uploadURI: AsposePdfParams['uploadURI']},
            success: function(response) {
                $body.removeClass("loading");
                if(isGutenbergActivePdfImpoter()){
                    wp.data.dispatch( 'core/editor' ).resetBlocks([]);
                    var content = response;
                    //var content = content.replace(/(<([^>]+)>)/ig,"");//
                    var el = wp.element.createElement;
                    //var name = 'core/paragraph';
                    var name = 'core/html';
                    insertedBlock = wp.blocks.createBlock(name, {
                        content: content,
                    });
                    wp.data.dispatch('core/editor').insertBlocks(insertedBlock);
                }else{
                    window.send_to_editor(response);
                }

            }
        });
    });

    $('#insert_aspose_pdf_content').on('click',null,function(){
        var filename = $('input[name="aspose_filename"]:checked').val();
        $("#aspose_pdf_popup_container").dialog('close');
        $body = $("body");
        $body.addClass("loading");

        jQuery.ajax
        ({
            type : "post",
            dataType : "html",
            url : AsposePdfParams['insert_pdf_url'],
            data : {appSID: AsposePdfParams['appSID'], appKey : AsposePdfParams['appKey'], filename : filename, uploadpath: AsposePdfParams['uploadpath'] , uploadURI: AsposePdfParams['uploadURI'] , aspose : '1'},
            success: function(response) {
                $body.removeClass("loading");
                if(isGutenbergActivePdfImpoter()){
                    //alert(response);
                    wp.data.dispatch( 'core/editor' ).resetBlocks([]);
                    var content = response;
                    //var content = content.replace(/(<([^>]+)>)/ig,"");//
                    var el = wp.element.createElement;
                    //var name = 'core/paragraph';
                    var name = 'core/html';
                    insertedBlock = wp.blocks.createBlock(name, {
                        content: content,
                    });
                    wp.data.dispatch('core/editor').insertBlocks(insertedBlock);
                }else{
                    window.send_to_editor(response);
                }

            }
        });

    });


});
function isGutenbergActivePdfImpoter() {
    return typeof wp !== 'undefined' && typeof wp.blocks !== 'undefined';
}


