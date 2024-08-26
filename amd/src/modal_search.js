import Modal from 'core/modal';
import $ from 'jquery';
import ajax from 'core/ajax';
import CustomEvents from 'core/custom_interaction_events';
import {init as ModalBookInit} from "./modal_book_handle";
import ModalVideo from './modal_video';

export default class ModalSearch extends Modal {
    static TYPE = 'mod_lanebs/modal_search';
    static TEMPLATE = 'mod_lanebs/modal_search';
    static SELECTORS = {
        SEARCH_TEXTBOX: "[data-action='search_text']",
        START_SEARCH: "[data-action='search_button']",
        START_SEARCH_CLEAR: "[name='clear_search']",
        CANCEL_BUTTON: "[data-action='cancel']",
        CLOSE_CROSS: ".close",
        CONTENT_BLOCK: "[data-action='content_block']",
        BREADCRUMBS: ".breadcrumbs p",
        CONTENT_NAME: "[name='content_name']",
        TRIGGER_BOOK: ".trigger_book",
        ADD_BOOK: "[name='add_book']",
        CATEGORY_BUTTON: "[data-action='category_tree']",
        CATEGORY_BLOCK: "[data-action='categories']",
        BOOK_PAGINATION: "#books_pagination li.page-item",
        START_PAGE_CLASS: "page-start",
        END_PAGE_CLASS: "page-end",
        PROGRESS: ".loader",
        BOOK_FILTER: ".book_filter",
        EDU_FILTER: ".edu_filter",
        SEARCH_FORM: "#search_form"
    };
    static OUTER_SELECTORS = {
        PAGE_NUMBER_FIELD: "#id_page_number",
        NAME_FIELD: "#id_name",
        CONTENT_FIELD: "[name='content']",
    };
    static LIMIT_ON_PAGE = 10;
    static breadcrumbs = {};
    static strings = {};


    configure(modalConfig) {
        modalConfig.removeOnClose = false;
        super.configure(modalConfig);
    }

    registerEventListeners() {
        // Call the registerEventListeners method on the parent class.
        super.registerEventListeners();
        const modal = this;

        const disabledEnterSubmit = (e) => {
            if (e.keyCode === 13) {
                submitFunction(e);
                return false;
            }
        };
        const disabledKeyUp = (e) => {
            if (e.keyCode !== false) {
                e.preventDefault();
                return false;
            }
        };
        const submitFunction = (e) => {
            $.fn.serializeAssoc = function() {
                var data = {};
                $.each( this.serializeArray(), function( key, obj ) {
                    var a = obj.name.match(/(.*?)\[(.*?)\]/);
                    if(a !== null)
                    {
                        var subName = a[1];
                        var subKey = a[2];

                        if( !data[subName] ) {
                            data[subName] = [ ];
                        }

                        if (!subKey.length) {
                            subKey = data[subName].length;
                        }

                        if( data[subName][subKey] ) {
                            if( $.isArray( data[subName][subKey] ) ) {
                                data[subName][subKey].push( obj.value );
                            } else {
                                data[subName][subKey] = [ ];
                                data[subName][subKey].push( obj.value );
                            }
                        } else {
                            data[subName][subKey] = obj.value;
                        }
                    } else {
                        if( data[obj.name] ) {
                            if( $.isArray( data[obj.name] ) ) {
                                data[obj.name].push( obj.value );
                            } else {
                                data[obj.name] = [ ];
                                data[obj.name].push( obj.value );
                            }
                        } else {
                            data[obj.name] = obj.value;
                        }
                    }
                });
                return data;
            };
            e.preventDefault();
            e.stopPropagation();
            const id = $(ModalSearch.SELECTORS.CATEGORY_BLOCK).attr('data-id');
            const args = {
                searchParam: $(e.target.form).serializeAssoc(),
                page: 1,
                limit: ModalSearch.LIMIT_ON_PAGE,
                catId: id
            };
            $(ModalSearch.SELECTORS.PROGRESS).toggleClass('hide');
            ModalSearch.getAjaxCall('mod_lanebs_search_books', args, ModalSearch.getSearchResult)
                .then(function () {
                    ModalSearch.resetPagination();
                    $(ModalSearch.SELECTORS.PROGRESS).toggleClass('hide');
                });
        };
        const getCategoryTree = (e) => {
            e.preventDefault();
            e.stopPropagation();
            let categoryId = [null];
            let args = {
                categoryId: categoryId
            };
            ModalSearch.getAjaxCall('mod_lanebs_category_tree', args, ModalSearch.printCategories);
        };
        const setPagination = (e) => {
            if ($(e.currentTarget).hasClass('disabled')) {
                return true;
            }
            let page, maxPage;
            if ($(ModalSearch.SELECTORS.CONTENT_BLOCK).attr('data-page') === undefined) {
                maxPage = 0;
            }
            else {
                maxPage = parseInt($(ModalSearch.SELECTORS.CONTENT_BLOCK).attr('data-page'));
            }
            if ($(e.currentTarget).hasClass(ModalSearch.SELECTORS.START_PAGE_CLASS)) {
                if (maxPage > 0) {
                    page = 1;
                }
                else {
                    page = 0;
                }
            }
            else if ($(e.currentTarget).hasClass(ModalSearch.SELECTORS.END_PAGE_CLASS)) {
                page = maxPage;
            }
            else {
                page = parseInt($(e.currentTarget).attr('data-page'));
            }
            let id = $(ModalSearch.SELECTORS.CATEGORY_BLOCK).attr('data-id');
            let args = {
                searchParam: $(ModalSearch.SELECTORS.SEARCH_FORM).serializeAssoc(),
                page: page,
                limit: ModalSearch.LIMIT_ON_PAGE,
                catId: id
            };
            let prevPage = $(ModalSearch.SELECTORS.BOOK_PAGINATION).find('a.prev').closest('li.page-item');
            let nextPage = $(ModalSearch.SELECTORS.BOOK_PAGINATION).find('a.next').closest('li.page-item');
            let currentPage = $(ModalSearch.SELECTORS.BOOK_PAGINATION).find('a.active').closest('li.page-item');
            if (page >= 2) {
                $(prevPage).attr('data-page', page - 1);
                $(prevPage).removeClass('disabled');
            }
            else if (page === 1) {
                $(prevPage).attr('data-page', page - 1);
                $(prevPage).addClass('disabled');
            }
            if (maxPage === page) {
                $(nextPage).addClass('disabled');
            }
            else {
                $(nextPage).removeClass('disabled');
            }
            $(nextPage).attr('data-page', page+1);
            $(currentPage).attr('data-page', page);
            $(currentPage).find('a.active').text(page+' '+ModalSearch.strings['lanebs_from']+' '+maxPage);
            if (page !== 0 && maxPage !== 0) {
                $(ModalSearch.SELECTORS.PROGRESS).toggleClass('hide');
                ModalSearch.getAjaxCall('mod_lanebs_search_books', args, ModalSearch.getSearchResult)
                    .then(function () {
                        $(ModalSearch.SELECTORS.PROGRESS).toggleClass('hide');
                    });
            }
        };

        $(ModalSearch.OUTER_SELECTORS.PAGE_NUMBER_FIELD).on('change', ModalSearch.nameFieldFill);
        this.getModal().on(CustomEvents.events.activate, ModalSearch.SELECTORS.START_SEARCH, submitFunction);
        this.getModal().on('click', ModalSearch.SELECTORS.START_SEARCH_CLEAR, function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(ModalSearch.SELECTORS.SEARCH_TEXTBOX).val('');
            $(ModalSearch.SELECTORS.START_SEARCH).trigger('click');

            return false;
        });
        this.getModal().on('keypress', ModalSearch.SELECTORS.SEARCH_TEXTBOX, disabledEnterSubmit);
        this.getModal().on(CustomEvents.events.activate, ModalSearch.SELECTORS.BOOK_PAGINATION, setPagination);
        this.getModal().on('keydown', ModalSearch.SELECTORS.CONTENT_NAME, disabledKeyUp);

        this.getModal().on(CustomEvents.events.activate, ModalSearch.SELECTORS.CANCEL_BUTTON+', '+ModalSearch.SELECTORS.CLOSE_CROSS,
            function (e) {
                e.preventDefault();
                e.stopPropagation();
                modal.hide();
        });

        this.getModal().on(CustomEvents.events.activate, ModalSearch.SELECTORS.CATEGORY_BUTTON, getCategoryTree);
    }

    static nameFieldFill = () => {
        const id = $(ModalSearch.OUTER_SELECTORS.CONTENT_FIELD).val();
        const name = $(ModalSearch.OUTER_SELECTORS.NAME_FIELD).val();
        if (id === '' && name !== '') {
            window.console.log(ModalSearch.strings['lanebs_error_book']);
        } else {
            let page = $(ModalSearch.OUTER_SELECTORS.PAGE_NUMBER_FIELD).val();
            let args = {
                id: id,
                page: page
            };
            ModalSearch.getAjaxCall('mod_lanebs_toc_name', args, function (response) {
                let tocName = response;
                if (tocName !== undefined && page !== '1') {
                    let name = $(ModalSearch.OUTER_SELECTORS.NAME_FIELD).val();
                    let pg = ModalSearch.strings['lanebs_read_pg'];
                    let regexp = new RegExp(', ' + pg + '.+', 'gi');
                    $(ModalSearch.OUTER_SELECTORS.NAME_FIELD).
                        val(name.replace(regexp, '') + ', ' + pg + '.' + page + ', ' + tocName);
                }
            });
        }
    };

    static padTo2Digits(num) {
        return num.toString().padStart(2, '0');
    }

    static formatDate(date) {
        return [
            ModalSearch.padTo2Digits(date.getDate()),
            ModalSearch.padTo2Digits(date.getMonth() + 1),
            date.getFullYear(),
        ].join('.');
    }

    static getSearchResult(response) {
        $(ModalSearch.SELECTORS.CONTENT_BLOCK).empty();
        let maxPage = Math.ceil(response.body.total / ModalSearch.LIMIT_ON_PAGE);
        if (response.body.items.length) {
            $.each(response.body.items, function(number, item) {
                let descriptionBlock =
                    '<a class="" data-toggle="collapse" href="#collapseDescription'+item.id+'" role="button">' +
                    ModalSearch.strings['lanebs_show_desc'] +
                    '</a>' +
                    '<div class="collapse" id="collapseDescription'+item.id+'">' + item.description + '</div>';
                if (item.description === null || item.description === '') {
                    item.description = '';
                    descriptionBlock = '';
                }
                $(ModalSearch.SELECTORS.CONTENT_BLOCK).append(
                    '<div class="item d-flex" data-id="' + item.id + '">' +
                    '<div class="cover" style="flex:0.2;">' +
                    '<img src="'+item.cover+'" class="book_cover" alt="'+ModalSearch.strings['lanebs_cover']+'">' +
                    '</div>' +
                    '<div class="item_content" style="flex:0.55;">' +
                    '<span class="book_biblio_record">' +
                    item.biblioRecord.replace(/00.00.0000/, ModalSearch.formatDate(new Date()))
                    +'</span><br>' +
                    '<span class="book_author hidden">' + item.author + '</span>' +
                    '<span class="book_title hidden">' + item.title + '</span>' +
                    descriptionBlock +
                    '</div>' +
                    '<div style="flex:0.25;">' +
                    '<button type="button" name="add_book" class="btn btn-sm ml-3" style="color: #174c8d;' +
                    'background-color: white;border-color: #4285f4;">'+ModalSearch.strings['lanebs_add']+'</button>' +
                    '<button type="button" class="trigger_book btn btn-sm ml-3 float-right" style="color: #174c8d;' +
                    'background-color: white;border-color: #4285f4;" data-page="'+ item.page_number +'">'+
                    ModalSearch.strings['lanebs_preshow']+'</button>'+
                    '<br>' +
                    '</div>' +
                    '</div><hr style="margin-top:5px;">');
            });
        }
        else {
            $(ModalSearch.SELECTORS.CONTENT_BLOCK).append('<div class="item">'+ModalSearch.strings['lanebs_error_empty_search']+
                '</div>');
        }
        $(ModalSearch.SELECTORS.CONTENT_BLOCK).attr('data-page', maxPage);
        $(ModalSearch.SELECTORS.TRIGGER_BOOK).on('click', function (e) {
            let id = $(e.target).closest('.item').attr('data-id');
            let pageNumber = $(ModalSearch.SELECTORS.BOOK_FILTER).val() === 'toc' ? $(e.target).attr('data-page') : null;
            ModalBookInit(e, id, pageNumber);
        });
        $(ModalSearch.SELECTORS.ADD_BOOK).on('click', function (e) {
            let id = $(e.target).closest('.item').attr('data-id');
            let contentName = $('[name="content_name"]');
            let biblioRecordField = $('[name="biblio_record"]');
            let coverField = $('[name="cover"]');
            let resourceNameField = $('#id_name');
            let bookTitle = $(e.target).closest('.item').find('.item_content .book_title').text();
            let bookAuthor = $(e.target).closest('.item').find('.item_content .book_author').text();
            let bookCover = $(e.target).closest('.item').find('.book_cover').attr('src');
            let bookBiblioRecord = $(e.target).closest('.item').find('.item_content .book_biblio_record').text();
            resourceNameField.val(bookAuthor+' - '+bookTitle);
            contentName.removeClass('is-invalid');
            contentName.siblings('#id_error_content_name').text('');
            contentName.val(bookTitle);
            $('[name="content"]').val(id);
            biblioRecordField.val(bookBiblioRecord);
            coverField.val(bookCover);
            $(ModalSearch.SELECTORS.CANCEL_BUTTON).trigger('click');
            ModalSearch.nameFieldFill();
            // clearing and updating video modal
            ModalVideo.resetModal();
        });
    }

    static printCategories = (response) => {
        $(ModalSearch.SELECTORS.CATEGORY_BLOCK).empty();
        $.each(response.body.items, function (number, item) {
            if (item.available === false) {
                return false;
            }
            $(ModalSearch.SELECTORS.CATEGORY_BLOCK).append(
                '<div style="cursor:pointer;color:#174c8d;background-color:white;" ' +
                'class="item btn-sm" data-id="'+item.id+'" data-expand="'+item.hasChild+'" ' +
                'data-parent="'+item.parent_id+'">' +
                '<span>'+item.name+'</span>' +
                '</div>');

            $(ModalSearch.SELECTORS.CATEGORY_BLOCK).find('[data-id="'+item.id+'"]').click({item: item}, function (e) {
                e.stopPropagation();
                e.preventDefault();
                let id = $(e.currentTarget).attr('data-id');
                $(ModalSearch.SELECTORS.CATEGORY_BLOCK).attr('data-id', id);
                ModalSearch.clearCurrentCrumb(response.body.items);
                ModalSearch.breadcrumbs[$(this).find('span').text()] = item;
                if (item.hasChild) {
                    let args = {
                        categoryId: [id]
                    };
                    ModalSearch.getAjaxCall('mod_lanebs_category_tree', args, ModalSearch.printCategories);
                }
                else {
                    if ($(this).hasClass('bg-primary')) {
                        $(this).removeClass('bg-primary');
                        let parentId = $(ModalSearch.SELECTORS.CATEGORY_BLOCK).find('.item:last').attr('data-parent');
                        $(ModalSearch.SELECTORS.CATEGORY_BLOCK).attr('data-id', parentId);
                        id = parentId;
                        ModalSearch.clearCurrentCrumb(response.body.items);
                    }
                    else {
                        $(this).addClass('bg-primary');
                        $(this).siblings().removeClass('bg-primary');
                        $(ModalSearch.SELECTORS.CATEGORY_BLOCK).attr('data-id', id);
                    }
                }
                ModalSearch.printBreadcrumbs();
                $(ModalSearch.SELECTORS.START_SEARCH).trigger('click');
            });
        });

        let parent_id = $(ModalSearch.SELECTORS.CATEGORY_BLOCK).find('.item:last').attr('data-parent');

        $(ModalSearch.SELECTORS.CATEGORY_BLOCK).prepend(
            '<div style="cursor:pointer;margin-bottom:15px;color:#174c8d;background-color:white;" ' +
            'class="btn-sm category_back" data-id="'+parent_id+'">' +
            '<span>'+ModalSearch.strings['lanebs_BACK']+'</span>' +
            '</div>');
        $(ModalSearch.SELECTORS.CATEGORY_BLOCK).find('.category_back').on('click', function () {
            let parent_id = $(this).attr('data-id');
            let id = ModalSearch.searchParentId(parent_id);
            if (id === null) {
                id = 'null';
            }
            $(ModalSearch.SELECTORS.CATEGORY_BLOCK).attr('data-id', id);
            let args = {
                categoryId: [id]
            };
            if ($(ModalSearch.SELECTORS.CATEGORY_BLOCK).find('.item.bg-primary').length > 0) {
                ModalSearch.clearCrumbs(2);
            }
            else {
                ModalSearch.clearCrumbs(1);
            }
            ModalSearch.printBreadcrumbs();
            ModalSearch.getAjaxCall('mod_lanebs_category_tree', args, ModalSearch.printCategories);
        });

    };

    static printBreadcrumbs = () => {
        let crumbs = ModalSearch.breadcrumbs;
        let html = '';
        $(ModalSearch.SELECTORS.BREADCRUMBS).empty();
        $.each(crumbs, function (item, number) {
            html += '<span class="item" data-id="'+number.id+'">'+item+'</span> -> ';
        });
        html = html.slice(0, -3);
        $(ModalSearch.SELECTORS.BREADCRUMBS).append(html);
    };

    static getAjaxCall = (methodname, args, callback) => {
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
            window.console.log(response);
            return false;
        });
    };

    static resetPagination = () => {
        let maxPage = parseInt($(ModalSearch.SELECTORS.CONTENT_BLOCK).attr('data-page'));
        $(ModalSearch.SELECTORS.BOOK_PAGINATION).find('a.prev').closest('li.page-item').
            addClass('disabled');
        $(ModalSearch.SELECTORS.BOOK_PAGINATION).find('a.prev').closest('li.page-item').
            attr('data-page', 0);
        $(ModalSearch.SELECTORS.BOOK_PAGINATION).find('a.active').closest('li.page-item').
            attr('data-page', 1);
        $(ModalSearch.SELECTORS.BOOK_PAGINATION).find('a.next').closest('li.page-item').
            attr('data-page', 2);
        if (maxPage > 1) {
            $(ModalSearch.SELECTORS.BOOK_PAGINATION).find('a.active').
                text(1+' '+ModalSearch.strings['lanebs_from']+' '+maxPage);
            $(ModalSearch.SELECTORS.BOOK_PAGINATION).find('a.next').closest('li.page-item').removeClass('disabled');
        }
        else {
            $(ModalSearch.SELECTORS.BOOK_PAGINATION).find('a.active').
                text(maxPage+' '+ModalSearch.strings['lanebs_from']+' '+maxPage);
            $(ModalSearch.SELECTORS.BOOK_PAGINATION).find('a.next').closest('li.page-item').addClass('disabled');
        }
    };

    static clearCurrentCrumb = (data) => {
        const tmp = ModalSearch.breadcrumbs;
        const lastCrumb = tmp[Object.keys(tmp)[Object.keys(tmp).length - 1]];
        $.each(data, function (number, item) {
            if (undefined !== lastCrumb) {
                if (item.id === lastCrumb.id) {
                    delete ModalSearch.breadcrumbs[item.name];
                }
            }
        });
    };

    static clearCrumbs = (count) => {
        let keys = null;
        let last = null;
        for (let i = 0; i < count; i++) {
            keys = Object.keys(ModalSearch.breadcrumbs);
            last = keys[keys.length-1];
            delete ModalSearch.breadcrumbs[last];
        }
    };

    static searchParentId = (id) => {
        let parentId = null;
        $.each(ModalSearch.breadcrumbs, function (number, item) {
            if (item.id === id) {
                parentId = item.parent_id;
            }
        });
        return parentId;
    };
}
