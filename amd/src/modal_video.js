import Modal from 'core/modal';
import $ from 'jquery';
import ajax from 'core/ajax';
import CustomEvents from 'core/custom_interaction_events';
import {init as ModalPlayerHandle} from './modal_player_handle';

export default class ModalVideo extends Modal {
    static TYPE = 'mod_lanebs/modal_video';
    static TEMPLATE = 'mod_lanebs/modal_video';
    static SELECTORS = {
        SUBMIT_BUTTON: ".video_submit",
        OPEN_BUTTON: "#id_modal_video_button",
        OPEN_BUTTON_CONTAINER: "#id_modal_video_button",
        CANCEL_BUTTON: ".video_close",
        CONTENT_BLOCK: "[data-action='video_content_block']",
        CLOSE_CROSS: ".modal_content_lan_reader .close",
        ROOT_MODAL: "[data-region='modal-container']",
        DATA_TOC: "#data-toc",
        BOOK_ID_SELECTOR: "[name='content']",
        SEARCH_CLASS: ".modal_video",
        VIDEOS_FIELD: "input[name='videos']",
        DELETE_SELECTED: "span.closes",
        PREVIEW_CLASS: ".video-item",
        PREVIEW_TEXT: "span.video_preview",
        PREVIEW_CONTAINER: ".video_preview_container",
        TRIGGER_PLAYER: "img.modal_video",
        PLAY_BUTTON_HOVER: ".video_play_hover",
    };
    static strings = {};

    configure(modalConfig) {
        super.configure(modalConfig);
        ModalVideo.CONTENT_BLOCK = ModalVideo.SELECTORS.CONTENT_BLOCK;
    }

    registerEventListeners() {
        // Call the registerEventListeners method on the parent class.
        super.registerEventListeners();
        const modal = this;
        this.getModal().on(CustomEvents.events.activate, ModalVideo.SELECTORS.SUBMIT_BUTTON, ModalVideo.updateVideoField);

        this.getRoot().on(CustomEvents.events.activate, ModalVideo.SELECTORS.CANCEL_BUTTON, (e) => {
            e.stopPropagation();
            e.preventDefault();
            modal.hide();
        });
    }

    static printTocs = () => {
        const bookId = $(ModalVideo.SELECTORS.BOOK_ID_SELECTOR).val();
        if (bookId !== '') {
            let tocArgs = {
                id: bookId,
                page: 0
            };
            ModalVideo.getAjaxCall('mod_lanebs_toc_name', tocArgs, function (tocs) {
                let videosArgs = {
                    id: $(ModalVideo.SELECTORS.BOOK_ID_SELECTOR).val()
                };
                ModalVideo.getAjaxCall('mod_lanebs_toc_videos', videosArgs, function (videos) {
                    let innerTocHtml = '';
                    let issetTocVideo = [];
                    let BreakException = {};
                    if (tocs) {
                        tocs.forEach(function (value, index) {
                            let videosList = '<ul class="modal_video">';
                            let currentPage = value['page'];
                            issetTocVideo[index] = false;
                            let uniqueIndex;
                            try {
                                videos.forEach(function (video) {
                                    if (video['start_page'] === currentPage) {
                                        issetTocVideo[index] = true;
                                        uniqueIndex = video['unique_id'] + '_' + index;
                                        videosList +=
                                            '<li class="modal_video">' +
                                            '<label for="video_' + uniqueIndex + '" class="modal_video">' +
                                            '<p class="modal_video">' +
                                            '<input type="checkbox" data-unique="' + uniqueIndex + '" class="modal_video"' +
                                            ' id="video_' + uniqueIndex + '" data-url="' + video['link_url'] + '" />' +
                                            video['link_name'] +
                                            '</p>' +
                                            '<img src="' + ModalVideo.getYoutubePreview(video['link_url']) + '" alt="' +
                                            video['link_name'] + '" class="modal_video" data-id="' +
                                            ModalVideo.getYoutubeId(video['link_url']) + '" />' +
                                            '<div class="video_play_hover"></div>' +
                                            '</label>' +
                                            '</li>';
                                    } else if (video['start_page'] === '-1') {
                                        issetTocVideo[index] = undefined;
                                        $(ModalVideo.SELECTORS.OPEN_BUTTON_CONTAINER).closest('.form-group').addClass('hidden');
                                        throw BreakException;
                                    }
                                });
                            } catch (e) {
                                if (e !== BreakException) {
                                    throw e;
                                }
                            }
                            videosList += '</ul>';
                            if (issetTocVideo[index] === true) {
                                innerTocHtml +=
                                    '<div class="item-toc" style="margin-bottom:10px;" data-page="' + currentPage + '">' +
                                    '<span>' + value['title'] + ', ' + ModalVideo.strings['lanebs_read_pg'] + '. ' +
                                    value['page'] + '</span><br>' +
                                    '<a class="" data-toggle="collapse" href="#collapseDescription_' + currentPage + '_' +
                                    index + '" role="button">' +
                                    'Видео-рекомендации' +
                                    '</a>' +
                                    '<div class="collapse" id="collapseDescription_' + currentPage + '_' + index + '">' +
                                    videosList + '</div>' +
                                    '</div>';
                            }
                        });
                    }
                    if (issetTocVideo[0] !== undefined) {
                        $(ModalVideo.SELECTORS.OPEN_BUTTON_CONTAINER).closest('.form-group').removeClass('hidden');
                        $(ModalVideo.SELECTORS.DATA_TOC).html(innerTocHtml);
                        $(ModalVideo.SELECTORS.TRIGGER_PLAYER).on('click', function (e) {
                            let linkId = $(e.target).attr('data-id');
                            ModalPlayerHandle(e, linkId);
                        });
                        $(ModalVideo.SELECTORS.PLAY_BUTTON_HOVER).on('click', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            $(e.currentTarget).siblings(ModalVideo.SELECTORS.TRIGGER_PLAYER).trigger('click');
                        });
                    }
                    let issetVideos = ModalVideo.getIssetVideos();
                    if (issetVideos) {
                        ModalVideo.printVideos(issetVideos);
                        ModalVideo.preCheckVideos(issetVideos);
                    }
                });
            });
        }
    };

    static getAjaxCall = (methodname, args, callback) => {
        return ajax.call([
            {
                methodname: methodname,
                args,
            }
        ])[0].then(function(response) {
            callback(JSON.parse(response['body']));
            return true;
        }).fail(function (response) {
            window.console.log(response);
            return false;
        });
    };

    static printVideos = (videos) => {
        let previewBlock = '<div class="col-md-3"></div><div class="container col-md-9">';
        videos.forEach(function (value) {
            previewBlock +=
                '<div class="item video-item row">' +
                '<div class="img-wraps">' +
                '<span class="closes" title="Delete" data-unique="'+value['unique']+'">×</span>' +
                '<img width="100px;" height="100px;" src="'+
                ModalVideo.getYoutubePreview(value['link'])+'" alt="'+value['name']+'"' +
                ' class="img-responsive" src="images/image.jpg" data-id="'+ModalVideo.getYoutubeId(value['link'])+'"/>' +
                '</div>' +
                '<span class="video_preview">'+value['name']+'</span>' +
                '</div>';
        });
        previewBlock += '</div>';
        $(ModalVideo.SELECTORS.PREVIEW_CONTAINER).html(previewBlock);
        ModalVideo.deleteSelectedEvent();
    };

    static getYoutubeId = (link) => {
        let urlParams = link.split('?')[1];
        return (new URLSearchParams(urlParams)).get('v');
    };

    static getYoutubePreview = (link) => {
        let id = ModalVideo.getYoutubeId(link);
        return 'https://img.youtube.com/vi/'+id+'/0.jpg';
    };

    static getIssetVideos = () => {
        let videos = $(ModalVideo.SELECTORS.VIDEOS_FIELD).val();
        try {
            return JSON.parse(videos);
        } catch (e) {
            window.console.log(e.message);
            return '';
        }
    };

    static preCheckVideos = (videos) => {
        videos.forEach(function (value) {
            $(ModalVideo.SELECTORS.ROOT_MODAL).find('input[data-unique="'+value['unique']+'"]').trigger('click');
        });
    };

    static deleteSelectedEvent = () => {
        $(ModalVideo.SELECTORS.DELETE_SELECTED).each(function (index, value) {
            $(value).on('click', function (e) {
                let videoItem = $(e.target).closest(ModalVideo.SELECTORS.PREVIEW_CLASS);
                let videoUnique = $(e.target).attr('data-unique');
                $(ModalVideo.SELECTORS.ROOT_MODAL).find('input[data-unique="'+videoUnique+'"]').trigger('click');
                $(videoItem).remove();
                ModalVideo.updateVideoField();
            });
        });
    };

    static updateVideoField = () => {
        let videos = [];
        let inputs = $(ModalVideo.SELECTORS.ROOT_MODAL).find('ul'+ModalVideo.SELECTORS.SEARCH_CLASS+' input:checked');
        inputs.each(function (index, value) {
            let url = $(value).attr('data-url');
            videos.push(
                {
                    link: url,
                    name: $(value).closest('p').text(),
                    unique: $(value).attr('data-unique'),
                    video_id: ModalVideo.getYoutubeId(url)
                });
        });
        $(ModalVideo.SELECTORS.VIDEOS_FIELD).val(JSON.stringify(videos));
        ModalVideo.printVideos(videos);
        $(ModalVideo.SELECTORS.CANCEL_BUTTON).trigger('click');
    };

    static resetModal = () => {
        ModalVideo.clearAllData();
        ModalVideo.printTocs();
    };

    static clearAllData = () => {
        $(ModalVideo.SELECTORS.VIDEOS_FIELD).val('[]');
        $(ModalVideo.SELECTORS.PREVIEW_CONTAINER).html('');
    };
}