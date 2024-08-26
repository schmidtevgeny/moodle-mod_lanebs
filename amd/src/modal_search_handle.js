import $ from 'jquery';
import ModalSearch from "./modal_search";
import {getStrings} from 'core/str';
import {getString} from 'core/str';
import CustomEvents from 'core/custom_interaction_events';

export const init = () => {
    const strings = [
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

    const getLangStrings = () => {
        let names = [];
        strings.forEach(function (value) {
            names.push({key: value, component: 'mod_lanebs'});
        });
        return names;
    };
    const stringsTransform = (langStrings) => {
        let resultStrings = [];
        strings.forEach(function (value, index) {
            resultStrings[value] = langStrings[index];
        });
        return resultStrings;
    };
    const trigger = $('#id_modal_show_button');
    const modal = ModalSearch.create({});
    modal.then((resolve) => {
        const strings = getLangStrings();
        getStrings(strings).then((strs) => {
            ModalSearch.strings = stringsTransform({...strings, ...strs});
        });
        const modalRoot = resolve.getRoot();
        $(modalRoot).find('.modal-dialog').css('max-width', '1500px');
        $(modalRoot).find('.modal-body').css('height', '770px');
        $(modalRoot).find('.modal-body').css('overflow-y', 'auto');
        ModalSearch.getAjaxCall('mod_lanebs_auth', [], function (data) {
            if (data['code'] === 403 || data['code'] === 401) {
                getString('lanebs_auth_error', 'mod_lanebs').then((str) => {
                    window.console.log(str);
                });
            }
        });
        $(trigger).on(CustomEvents.events.activate,() => {
            resolve.show();
        });
    });
};