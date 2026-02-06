// jQuery(window).on(
//         'load',
//         function () {
//             // View Sheet
//         elementor.channels.editor.on('namespace:editor:gsceviewsheet', function (view) {
//             const isManual = jQuery('[data-setting="enable_manual_sheet_settings"]').val() === 'yes';

//             const sheetId = isManual
//                 ? jQuery('[data-setting="manual_sheet_id"]').val()
//                 : jQuery('[data-setting="gs_spreadsheet_id"]').val();

//             const tabId = isManual
//                 ? jQuery('[data-setting="manual_tab_id"]').val()
//                 : jQuery('[data-setting="gs_spreadsheet_tab_name"] option:selected').val();

//             const url = `https://docs.google.com/spreadsheets/d/${sheetId}/edit#gid=${tabId}`;
//             window.open(url, '_blank');
//         });
//         }

// );

jQuery(window).on('load', function () {

    elementor.channels.editor.on('namespace:editor:gsceviewsheet', function () {

        let sheetId = jQuery('[data-setting="gs_spreadsheet_id"]').val();

        if (!sheetId) {
            sheetId = jQuery('[data-setting="manual_sheet_id"]').val();
        }

        let tabId = jQuery('[data-setting="gs_spreadsheet_tab_name"] option:selected').val();

        if (!tabId) {
            tabId = jQuery('[data-setting="manual_tab_id"]').val();
        }

        if (!sheetId || !tabId) {
            alert('Sheet ID or Tab ID is missing');
            return;
        }

        const url = `https://docs.google.com/spreadsheets/d/${sheetId}/edit#gid=${tabId}`;
        window.open(url, '_blank');
    });

});




jQuery(window).on('load', function () {
    elementor.channels.editor.on('namespace:editor:gscfetchsheet', function (view) {
        jQuery(".elementor-button-gscfetchsheet").text("Loading..................");

        var data = {
            action: 'sync_google_account_gscelementor_page', // unified
            isinit: 'yes',
            security: jQuery('[data-setting=gs-ajax-nonce-ele]').val()
        };

        jQuery.post(ajaxurl, data, function (response) {
            if (response == -1 || !response.success) {
                jQuery(".elementor-button-gscfetchsheet").text("Something wrong!");
                return;
            }

            if (response.data.success === "yes") {
                jQuery(".elementor-button-gscfetchsheet").text("Successfully fetched!");
            } else {
                jQuery(".elementor-button-gscfetchsheet").text("Something wrong!");
            }
            setTimeout(function () { location.reload(); }, 1000);
        });
    });
});



jQuery(window).on(
        'load',
        function () {
            elementor.channels.editor.on(
                    'change',
                    function (view) {
                        var changed = view.elementSettingsModel.changed;
                        if (changed.gs_spreadsheet_id != "" && changed.gs_spreadsheet_id != undefined) {
                            var sheetnameId = jQuery("[data-setting = gs_spreadsheet_id]").val();
                            var tabs_arr_val = jQuery("[data-setting = gs_elmentor_all_sheet_data]").val();
                            tabs_arr_val = jQuery.parseJSON(tabs_arr_val);
                            if (tabs_arr_val[sheetnameId] == undefined || tabs_arr_val == "") {
                              fetch_tabs_api(sheetnameId, 0);
                            } else {
                              fetch_tabs_api(sheetnameId, 0);
                               //options_selected_sheet(sheetnameId);
                            }
                         }

                    }
            );
        }
);


function fetch_tabs_api(sheetnameId, refresh) {
    jQuery(".loading-gs-fetch-tabs").addClass("loading");
   jQuery("[data-setting =  gs_spreadsheet_tab_name]").attr('disabled', true);
   jQuery(".tabselectionloading").css('display','inline-block');


    //jQuery("#gsheet_tabs_arr").val('');
    var refresh = 0;
    var data = {
       action: "get_google_tab_list_by_sheetname",
       sheetname: sheetnameId,
       refresh: refresh,
       security: jQuery("[data-setting = gs-ajax-nonce-ele]").val(),
    };
 
    jQuery.post(ajaxurl, data, function (response) {
       if (response == -1) {
          return false; // Invalid nonce
       }
       if (response) {
          jQuery("[data-setting = gs_elmentor_all_sheet_data]").val("");
          jQuery("[data-setting=gs_elmentor_all_sheet_data]").val(JSON.stringify(response.data));
          options_selected_sheet(sheetnameId);
         jQuery("[data-setting =  gs_spreadsheet_tab_name]").attr('disabled', false);
         jQuery(".tabselectionloading").css('display','none');
       }
    });
 }
 
function options_selected_sheet(sheetnameId) {
    let all_tab_array = jQuery("[data-setting=gs_elmentor_all_sheet_data]").val();

    // Check if it's a string that needs parsing
    if (typeof all_tab_array === "string" && all_tab_array.trim().charAt(0) === '{') {
        try {
            all_tab_array = JSON.parse(all_tab_array);
        } catch (e) {
            console.error("Invalid JSON in gs_elmentor_all_sheet_data:", all_tab_array);
            return;
        }
    }

    // If still not an object, return
    if (typeof all_tab_array !== "object") {
        console.error("gs_elmentor_all_sheet_data is not a valid object:", all_tab_array);
        return;
    }

    const tabs_arr = all_tab_array[sheetnameId];

    if (tabs_arr && typeof tabs_arr === "object") {
        let tabs_option = "";
        jQuery.each(tabs_arr, function (key, value) {
            tabs_option += '<option value="' + key + '">' + value + "</option>";
        });

        if (tabs_option) {
            jQuery("[data-setting=gs_spreadsheet_tab_name]").html(tabs_option);
            jQuery("[data-setting=gs_spreadsheet_tab_name]").trigger("change");
        }
    }
}

