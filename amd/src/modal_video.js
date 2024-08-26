define([
    "exports",
    "jquery",
    "core/ajax",
    "core/modal_factory",
    "core/modal_events",
    "core/notification",
    "core/modal",
    "core/custom_interaction_events",
    "core/modal_registry",
    "mod_lanebs/modal_player_handle",
    "core/str",
], function (
    exports,
    $,
    ajax,
    ModalFactory,
    ModalEvents,
    Notification,
    Modal,
    CustomEvents,
    ModalRegistry,
    ModalPlayerHandle,
    Str,
) {
    let SELECTORS = {
        SUBMIT_BUTTON: "[data-action='submit_button']",
        OPEN_BUTTON: "#id_modal_video_button",
        OPEN_BUTTON_CONTAINER: "#id_modal_video_button",
        CANCEL_BUTTON: "[data-action='close_button']",
        CONTENT_BLOCK: "[data-action='video_content_block']",
        CLOSE_CROSS: ".close",
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

    /**
     * Constructor for the Modal
     *
     */
    let ModalVideo = function(root) {
        Modal.call(this, root);
        ModalVideo.prototype.modal = this;
        this.printTocs();
    };

    ModalVideo.TYPE = 'mod_lanebs-video';
    ModalVideo.CONTENT_BLOCK = SELECTORS.CONTENT_BLOCK;
    ModalVideo.prototype = Object.create(Modal.prototype);
    ModalVideo.prototype.constructor = ModalVideo;

    ModalVideo.prototype.registerEventListeners = function () {
        Modal.prototype.registerEventListeners.call(this);

        this.getModal().on(CustomEvents.events.activate, SELECTORS.SUBMIT_BUTTON, ModalVideo.prototype.updateVideoField);

        this.getRoot().on(CustomEvents.events.activate, SELECTORS.CANCEL_BUTTON+', '+SELECTORS.CLOSE_CROSS, function () {
            this.destroy();
            this.getBackdrop().then(function (backdrop) {
                backdrop.hide();
            });
        }.bind(this));
/*        this.getModal().on(CustomEvents.events.activate, SELECTORS.CANCEL_BUTTON, function () {
            $(this).trigger('hide');
        }.bind(this));*/
    };

    ModalVideo.prototype.printTocs = function () {
        let bookId = $(SELECTORS.BOOK_ID_SELECTOR).val();
        if (bookId !== '') {
            let tocArgs = {
                id: bookId,
                page: 0
            };
            ModalVideo.prototype.getAjaxCall('mod_lanebs_toc_name', tocArgs, function (tocs) {
                let videosArgs = {
                    id: $(SELECTORS.BOOK_ID_SELECTOR).val()
                };
                ModalVideo.prototype.getAjaxCall('mod_lanebs_toc_videos', videosArgs, function (videos) {
                    let body = ModalVideo.prototype.modal.getBody();
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
                                videos.forEach(function (video, videoIndex) {
                                    if (video['start_page'] === currentPage) {
                                        issetTocVideo[index] = true;
                                        uniqueIndex = video['unique_id'] + '_' + index;
                                        videosList +=
                                            '<li class="modal_video">' +
                                            '<label for="video_' + uniqueIndex + '" class="modal_video">' +
                                            '<p class="modal_video">' +
                                            '<input type="checkbox" data-unique="' + uniqueIndex + '" class="modal_video" id="video_' + uniqueIndex + '" data-url="' + video['link_url'] + '" />' +
                                            video['link_name'] +
                                            '</p>' +
                                            '<img src="' + ModalVideo.prototype.getYoutubePreview(video['link_url']) + '" alt="' + video['link_name'] + '" class="modal_video" data-id="' + ModalVideo.prototype.getYoutubeId(video['link_url']) + '" />' +
                                            '<div class="video_play_hover"></div>' +
                                            '</label>' +
                                            '</li>';
                                    } else if (video['start_page'] === '-1') {
                                        issetTocVideo[index] = undefined;
                                        $(SELECTORS.OPEN_BUTTON_CONTAINER).closest('.form-group').addClass('hidden');
                                        throw BreakException;
                                    }
                                });
                            } catch (e) {
                                if (e !== BreakException) throw e;
                            }
                            videosList += '</ul>';
                            if (issetTocVideo[index] === true) {
                                innerTocHtml +=
                                    '<div class="item-toc" style="margin-bottom:10px;" data-page="' + currentPage + '">' +
                                    '<span>' + value['title'] + ', ' + ModalVideo.prototype.strings['lanebs_read_pg'] + '. ' + value['page'] + '</span><br>' +
                                    '<a class="" data-toggle="collapse" href="#collapseDescription_' + currentPage + '_' + index + '" role="button">' +
                                    'Видео-рекомендации' +
                                    '</a>' +
                                    '<div class="collapse" id="collapseDescription_' + currentPage + '_' + index + '">' + videosList + '</div>' +
                                    '</div>';
                            }
                        });
                    }
                    if (issetTocVideo[0] !== undefined) {
                        $(SELECTORS.OPEN_BUTTON_CONTAINER).closest('.form-group').removeClass('hidden');
                        body.find(SELECTORS.DATA_TOC).html(innerTocHtml);
                        $(body.find(SELECTORS.TRIGGER_PLAYER)).on('click', function (e) {
                            let linkId = $(e.target).attr('data-id');
                            ModalPlayerHandle.init(e, linkId);
                        });
                        $(body.find(SELECTORS.PLAY_BUTTON_HOVER)).on('click', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            $(e.currentTarget).siblings(SELECTORS.TRIGGER_PLAYER).trigger('click');
                        });
                    }
                    let issetVideos = ModalVideo.prototype.getIssetVideos();
                    if (issetVideos) {
                        ModalVideo.prototype.printVideos(issetVideos);
                        ModalVideo.prototype.preCheckVideos(issetVideos);
                    }
                });
            });
        }
    };

    ModalVideo.prototype.getAjaxCall = function (methodname, args, callback) {
        return ajax.call([
            {
                methodname: methodname,
                args,
            }
        ])[0].then(function(response) {
            return response;
        }).done(function(response) {
            callback(JSON.parse(response['body']));
            return true;
        }).fail(function (response) {
            console.log(response);
            return false;
        });
    };

    ModalVideo.prototype.printVideos = function (videos) {
        let previewBlock = '<div class="col-md-3"></div><div class="container col-md-9">';
        videos.forEach(function (value, index) {
            previewBlock +=
                '<div class="item video-item row">' +
                '<div class="img-wraps">' +
                '<span class="closes" title="Delete" data-unique="'+value['unique']+'">×</span>' +
                '<img width="100px;" height="100px;" src="'+ModalVideo.prototype.getYoutubePreview(value['link'])+'" alt="'+value['name']+'"  class="img-responsive" src="images/image.jpg" data-id="'+ModalVideo.prototype.getYoutubeId(value['link'])+'"/>' +
                '</div>' +
                '<span class="video_preview">'+value['name']+'</span>' +
                '</div>';
        });
        previewBlock += '</div>';
        $(SELECTORS.PREVIEW_CONTAINER).html(previewBlock);
        ModalVideo.prototype.deleteSelectedEvent();
    };

    ModalVideo.prototype.getYoutubeId = function (link) {
        let urlParams = link.split('?')[1];
        return (new URLSearchParams(urlParams)).get('v');
    };

    ModalVideo.prototype.getYoutubePreview = function (link) {
        let id = ModalVideo.prototype.getYoutubeId(link);
        return 'https://img.youtube.com/vi/'+id+'/0.jpg';
    };

    ModalVideo.prototype.getIssetVideos = function () {
        let videos = $(SELECTORS.VIDEOS_FIELD).val();
        try {
            return JSON.parse(videos);
        } catch (e) {
            console.log(e.message);
            return '';
        }
    };

    ModalVideo.prototype.preCheckVideos = function (videos) {
        videos.forEach(function (value, index) {
            ModalVideo.prototype.modal.getBody().find('input[data-unique="'+value['unique']+'"]').trigger('click');
        });
    };

    ModalVideo.prototype.deleteSelectedEvent = function () {
        $(SELECTORS.DELETE_SELECTED).each(function (index, value) {
            $(value).on('click', function (e) {
                let videoItem = $(e.target).closest(SELECTORS.PREVIEW_CLASS);
                let videoUnique = $(e.target).attr('data-unique');
                ModalVideo.prototype.modal.getBody().find('input[data-unique="'+videoUnique+'"]').trigger('click');
                $(videoItem).remove();
                ModalVideo.prototype.updateVideoField();
            });
        });
    };

    ModalVideo.prototype.updateVideoField = function () {
        let videos = [];
        let inputs = ModalVideo.prototype.modal.getBody().find('ul'+SELECTORS.SEARCH_CLASS+' input:checked');
        inputs.each(function (index, value) {
            let url = $(value).attr('data-url');
            videos.push(
                {
                    link: url,
                    name: $(value).closest('p').text(),
                    unique: $(value).attr('data-unique'),
                    video_id: ModalVideo.prototype.getYoutubeId(url)
                });
        });
        $(SELECTORS.VIDEOS_FIELD).val(JSON.stringify(videos));
        ModalVideo.prototype.printVideos(videos);
        $(SELECTORS.CANCEL_BUTTON).trigger('click');
    }

    ModalVideo.prototype.clearAllData = function () {
        $(SELECTORS.VIDEOS_FIELD).val('[]');
        $(SELECTORS.PREVIEW_CONTAINER).html('');
    };

    ModalVideo.prototype.resetModal = function () {
        ModalVideo.prototype.clearAllData();
        ModalVideo.prototype.printTocs();
    }


    ModalRegistry.register(ModalVideo.TYPE, ModalVideo, 'mod_lanebs/modal_video');

    return ModalVideo;
});