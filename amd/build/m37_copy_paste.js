define(["exports", "jquery", "core/ajax", "core/str"],
    function (exports, $, ajax, Str) {
        return {
            init: function(title) {
                let STORAGE_ID = 'lanebs_mod';
                let SELECTORS = {
                    PASTE_BUTTON: '#id_paste_mod',
                    COPY_BUTTON: '#id_copy_mod',
                    NAME_FIELD: '#id_name',
                    CONTENT_FIELD: '[name="content"]',
                    COVER_FIELD: '[name="cover"]',
                    BIBLIO_RECORD_FIELD: '[name="biblio_record"]',
                    DESCRIPTION_EDITOR_FIELD: '#id_introeditoreditable',
                    DESCRIPTION_TEXTFIELD: '#id_introeditor',
                    CONTENT_NAME_FIELD: '#id_content_name',
                    PAGE_NUMBER_FIELD: '#id_page_number',
                    VISIBLE_SELECT: '#id_visible',
                    IDNUMBER_FIELD: '#id_cmidnumber',
                };
                let issetStorage = localStorage.getItem(STORAGE_ID);
                if (!issetStorage) {
                    $(SELECTORS.PASTE_BUTTON).addClass('hidden');
                }
                $(SELECTORS.COPY_BUTTON).on('click', function(e) {
                    $(SELECTORS.PASTE_BUTTON).removeClass('hidden');
                    let query = window.location.search;
                    let urlParams = new URLSearchParams(query);
                    let modId = urlParams.get('update');
                    if (!modId) {
                        Str.get_string('lanebs_copy_attention', 'mod_lanebs').
                        done(function (str) {
                            console.log(str);
                        });
                    }
                    localStorage.setItem('lanebs_mod', modId);
                });
                $(SELECTORS.PASTE_BUTTON).on('click', function (e) {
                    let id = parseInt(localStorage.getItem(STORAGE_ID));
                    if (!id) {
                        Str.get_string('lanebs_copy_error', 'mod_lanebs').
                        done(function (str) {
                            console.log(str);
                        });
                    } else {
                        let args = {
                            'id': id
                        };
                        ajax.call([
                            {
                                methodname: 'mod_lanebs_lanebs_info',
                                args,
                            }
                        ])[0].then(function(response) {
                            return response;
                        }).done(function(response) {
                            let info = JSON.parse(response.body);
                            $(SELECTORS.BIBLIO_RECORD_FIELD).val(info.biblio_record);
                            $(SELECTORS.COVER_FIELD).val(info.cover);
                            $(SELECTORS.NAME_FIELD).val(info.name);
                            $(SELECTORS.CONTENT_NAME_FIELD).val(info.content_name);
                            $(SELECTORS.DESCRIPTION_EDITOR_FIELD).text(info.intro);
                            $(SELECTORS.DESCRIPTION_TEXTFIELD).val(info.intro);
                            $(SELECTORS.PAGE_NUMBER_FIELD).val(info.page_number);
                            $(SELECTORS.CONTENT_FIELD).val(info.content);
                            $(SELECTORS.VISIBLE_SELECT).val(info.visible);
                            return response;
                        }).fail(function (response) {
                            console.log(response);
                            return false;
                        });
                    }
                });
            },
        };
    });