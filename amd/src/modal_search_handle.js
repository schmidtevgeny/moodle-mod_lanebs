define(["exports", "jquery", "core/modal_factory", "core/str", "mod_lanebs/modal_search"],
    function (exports, $, ModalFactory, Str, ModalSearch) {
        return {
            init: function () {
                let trigger = $('#id_modal_show_button');
                let strings = [
                    'lanebs_error_textbox',
                    'lanebs_error_search',
                    'lanebs_show_desc',
                    'lanebs_read_pg',
                    'lanebs_error_book',
                    'lanebs_cover',
                    'lanebs_add',
                    'lanebs_preshow',
                    'lanebs_error_empty_search',
                    'lanebs_BACK',
                    'lanebs_from',
                ];
                let getLangStrings = function () {
                    let names = [];
                    strings.forEach(function (value, index) {
                        names.push({key: value, component: 'mod_lanebs'});
                    });
                    return names;
                };
                let stringsTransform = function (langStrings) {
                    let resultStrings = [];
                    strings.forEach(function (value, index) {
                        resultStrings[value] = langStrings[index];
                    });
                    return resultStrings;
                };
                ModalFactory.create({type: ModalSearch.TYPE}, trigger).then(function (modal) {
                    Str.get_strings(getLangStrings()).
                        done(function (strs) {ModalSearch.prototype.strings = stringsTransform(strs);}).
                        fail(function (e) {console.log(e);});
                    $(modal.getRoot()).find('.modal-dialog').css('max-width', '1500px');
                    $(modal.getRoot()).find('.modal-body').css('height', '770px');
                    $(modal.getRoot()).find('.modal-body').css('overflow-y', 'auto');
                    ModalSearch.prototype.getAjaxCall('mod_lanebs_auth', [], function (data) {
                        if (data['code'] === 403 || data['code'] === 401) {
                            Str.get_string('lanebs_auth_error', 'mod_lanebs').then(function (str) {
                                console.log(str);
                            });
                        }
                    });
                });
            }
        };
    });