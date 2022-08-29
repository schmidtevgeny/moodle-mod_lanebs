define(["exports", "jquery", "core/modal_factory", "core/str", "mod_lanebs/modal_video"],
    function (exports, $, ModalFactory, Str, ModalVideo) {
        return {
            init: function () {
                let trigger = $('#id_modal_video_button');
                let strings = [
                    'lanebs_read_pg'
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
                ModalFactory.create({type: ModalVideo.TYPE}, trigger).then(function (modal) {
                    Str.get_strings(getLangStrings()).
                    done(function (strs) {ModalVideo.prototype.strings = stringsTransform(strs);}).
                    fail(function (e) {console.log(e);});
                    let modalRoot = modal.getRoot();
                    $(modalRoot).find('.modal-dialog').css('max-width', '1500px');
                    $(modalRoot).find('.modal-body').css('height', '770px');
                    $(modalRoot).find('.modal-body').css('overflow-y', 'auto');
                    $(modalRoot).find(ModalVideo.CONTENT_BLOCK).trigger('cie:scrollBottom');
                })
            }
        };
    });