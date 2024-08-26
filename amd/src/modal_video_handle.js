import $ from 'jquery';
import {getStrings} from 'core/str';
import ModalVideo from './modal_video';
import CustomEvents from 'core/custom_interaction_events';
//import ModalEvents from 'core/modal_events';

export const init = () => {
    const strings = [
        'lanebs_read_pg'
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
    const trigger = $('#id_modal_video_button');
    const modal = ModalVideo.create({});
    modal.then(resolve => {
        const strings = getLangStrings();
        getStrings(strings).then(function(strs) {
            ModalVideo.strings = stringsTransform({...strings, ...strs});
        });
        let modalRoot = resolve.getRoot();
        $(modalRoot).find('.modal-dialog').css('max-width', '1500px');
        $(modalRoot).find('.modal-body').css('height', '770px');
        $(modalRoot).find('.modal-body').css('overflow-y', 'auto');
        $(modalRoot).find('.modal-dialog').addClass('modal_dialog_lan_reader');
        $(modalRoot).find('.modal-content').addClass('modal_content_lan_reader');
        ModalVideo.printTocs();
    });
    $(trigger).on(CustomEvents.events.activate, () => {
        modal.then((resolve) => {
            resolve.show().then(() => {
                ModalVideo.printTocs();
            });
        });
    });
};