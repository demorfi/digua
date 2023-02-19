$(function () {
    $.ajaxSetup({
        dataType: 'json',
        complete: function (ajax) {
            ajax.then(function (data) {

                // Show messages
                let $messages = $('.alert-messages'),
                    $tpl = $messages.filter('.hidden:first');

                $messages.filter(':not(.hidden)').remove();
                if (data && ($.isPlainObject(data) && ('error' in data))) {
                    let $message = $tpl.clone().insertAfter($messages);
                    $message.removeClass('hidden')
                        .addClass('alert-danger')
                        .find('.message').html(data.error);
                }
            });
        }
    });

    /**
     * Toggle loading status.
     *
     * @param {String} method
     * @returns {$}
     */
    $.fn.loading = function (method) {
        $(this).find('.loading').remove();
        switch (method) {
            case ('show'):
                let $block = $('<div class="loading"><div class="loading-indicator">'
                               + '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>'
                               + '<span class="sr-only">Loading...</span></div></div>');
                $(this).append($block);
                break;
        }

        return this;
    };

    /**
     * Add data to page.
     * @param {Object|String} value
     * @returns {$}
     */
    $.fn.addData = function (value) {
        if (!value || ($.isPlainObject(value) && !('data' in value))) {
            return this;
        }

        value = ($.isPlainObject(value) ? value.data : value);
        let $output = ($(this).data('output') ? $($(this).data('output')) : $(this).find('.output'));
        if ($output.length) {
            if ($output.is('tbody')) {
                $output.empty();
                for (let index in value) {
                    if (value.hasOwnProperty(index)) {
                        let $tr = $('<tr>');
                        if ($.isPlainObject(value[index])) {
                            let keys = Object.keys(value[index]);
                            for (let x in keys) {
                                if (keys.hasOwnProperty(x) && (keys[x] in value[index])) {
                                    $tr.append($('<td>').html(value[index][keys[x]]));
                                }
                            }
                        } else {
                            if ($.isArray(value[index])) {
                                for (let y in value[index]) {
                                    if (value[index].hasOwnProperty(y)) {
                                        $tr.append($('<td>').html(value[index][y]));
                                    }
                                }
                            } else {
                                $tr.append($('<td>').html(value[index]));
                            }
                        }
                        $output.append($tr);
                    }
                }
            }

            return this;
        }

        $(this).html(value);
        return this;
    };

    /**
     * Add pagination pages.
     *
     * @param {Object} value
     * @returns {$}
     */
    $.fn.addPagination = function (value) {
        if (!$.isPlainObject(value)) {
            return this;
        }

        if ('pagination' in value) {
            let pagination = value.pagination,
                $pagination = $(this).find('.pagination'),
                $paginationPrev = $(this).find('.pagination-prev'),
                $paginationNext = $(this).find('.pagination-next');

            if ($pagination.length) {
                $pagination.addClass('hidden').find('li:not(.pagination-prev, .pagination-next)').remove();
                if (pagination.hasPages) {
                    $paginationPrev.find('a').attr('href', $pagination.data('url') + pagination.prevPage);
                    if (pagination.hasPrev) {
                        $paginationPrev.removeClass('disabled');
                    } else {
                        $paginationPrev.addClass('disabled');
                    }

                    for (let index = pagination.total; index >= 1; index--) {
                        let $item = $('<li>').addClass(index === pagination.current ? 'active' : ''),
                            $link = $('<a>').attr('href', $pagination.data('url') + '/page/' + index).text(index);
                        $paginationPrev.after($item.append($link));
                    }

                    $paginationNext.find('a').attr('href', $pagination.data('url') + pagination.nextPage);
                    if (pagination.hasNext) {
                        $paginationNext.removeClass('disabled');
                    } else {
                        $paginationNext.addClass('disabled');
                    }

                    $pagination.removeClass('hidden');
                }
            }
        }

        return this;
    };

    /**
     * Load data in tabs.
     */
    $(document).on('show.bs.tab', 'a[data-toggle="tab"]', function () {
        let url = $(this).data('url'),
            $target = $('.tab-pane' + $(this).attr('href'));

        $target.loading('show');
        if (url.length) {
            $.get(url, function (data) {
                $target.addData(data).addPagination(data).loading('hide');
                $.localStorage.set(url, data);
            })
        }
    });

    /**
     * Set active (focus) element submit form (filter :focus not working in chrome)
     */
    $(document).on('click', 'form.ajax [type="submit"]', function () {
        $(this).closest('form.ajax').trigger('submit', this);
        return false;
    });

    /**
     * Ajax submit forms.
     */
    $(document).on('submit', 'form.ajax', function (event, activeElement) {
        let $form = $(this),
            $target = ($form.data('root') ? $($form.data('root')) : $form),
            $submits = $form.find('[type="submit"]').prop('disabled', true),
            $submit = $(activeElement),
            data = $form.serializeArray();

        if ($submit.attr('name') && $submit.attr('value')) {
            data.push({name: $submit.attr('name'), value: $submit.attr('value')});
        }

        $target.loading('show');
        $.post($form.attr('action'), data, $.proxy(function ($form, $submits, $submit, data) {
            if (!$(this).hasClass('ajax')) {
                $(this).addData(data).addPagination(data);
            }
            $(this).loading('hide');
            $submits.prop('disabled', false);

            // clean input field for create domain (for search not clean for ability pagination)
            if ($submit.attr('name') === 'create') {
                $form.find('input[name="name"]').val('');
            }

            $form.trigger('form.ajax', [data]);
        }, $target, $form, $submits, $submit));
        return false;
    });

    /**
     * Ajax submit form trigger.
     */
    $(document).on('form.ajax', '.service-toggle', function (event, data) {
        if (data.success) {
            let $btn = $(this).find('[type="submit"]').removeClass('btn-primary btn-danger');
            $btn.addClass(data.status ? 'btn-primary' : 'btn-danger').text(data.status ? 'Enabled' : 'Disabled');
        }
    });

    /**
     * Trigger reset forms.
     */
    $(document).on('reset', 'form.ajax', function () {
        let id = $(this).attr('action'),
            data = $.localStorage.get(id);
        if (data) {
            $(this).addData(data);
        }
        return false;
    });

    /**
     * Load data to pagination links.
     */
    $(document).on('click', '.pagination a', function () {
        let $pagination = $(this).closest('.pagination'),
            $target = $($pagination.data('root')),
            data = $target.find('.search-form').serializeArray();

        let search = $(this).closest('.tab-pane').find('.search-form input[name="name"]');
        if (search.val()) {
            data.push({name: 'search', value: 1});
            data.push({name: 'name', value: search.val()});
        }

        $target.loading('show');
        $.post($(this).attr('href'), data, $.proxy(function (data) {
            $target.addData(data).addPagination(data).loading('hide');
        }, $target));
        return false;
    });

    /**
     * Loading first active tab.
     */
    (function () {
        let $tabs = $('[role="presentation"].active');
        if (!$tabs.length) {
            $('[role="presentation"]:first a').tab('show');
        }
    })();
});
