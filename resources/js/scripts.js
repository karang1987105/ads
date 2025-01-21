$(() => {
    $('.main-sidebar a.dropdown-toggle').click(function (e) {
        e.preventDefault();
        $(this).toggleClass('opened').next('ul').slideToggle();
    });
    $('.main-content a.toggle-sidebar, .main-sidebar a.toggle-sidebar').click(() => {
        $(".main-sidebar").toggleClass("open")
    });

    const sessionLifetime = document.querySelector('meta[name="session-lifetime"]')?.content || 0;
    if (sessionLifetime > 0) {
        setTimeout(() => {
            alert('Your session has expired. You will be redirected to the landing page.');
            window.location.href = "/";
        }, sessionLifetime * 60 * 1000);
    }

    $(document).ajaxError((event, jqxhr) => {
        if (jqxhr.status === 401) {
            alert('Your session has expired. Redirecting to the landing page...');
            window.location.href = "/";
        }
    });

    Ads.form.init($('form'));
    Ads.Utils.initTooltips(document.body);
    Ads.list.init($(document.body));
});

Ads = {
    loader: {
        get(element) {
            let $element = $(element);
            let form = $element.closest('form');
            if (form.length === 1) {
                let listHeader = form.closest('.list-header');
                if (listHeader.length === 1) {
                    let loader = listHeader.find('.list-loader');
                    Ads.display(loader, 'prepare', 'inline-block');
                    return loader;
                } else {
                    let overlay = form.closest('.row-extra').find('.overlay');
                    Ads.display(overlay, 'prepare', 'block');
                    return overlay;
                }
            } else {
                let listHeader = $element.closest('.list-header');
                if (listHeader.length === 1) {
                    let overlay = listHeader.next('.list-body').children('.overlay').first();
                    Ads.display(overlay, 'prepare', 'block');
                    return overlay;
                } else {
                    let rowContent = $element.closest('.row-content');
                    if (rowContent.length === 1) {
                        let overlay = rowContent.siblings('.overlay').first();
                        Ads.display(overlay, 'prepare', 'block');
                        return overlay;
                    } else {
                        let nav = $element.closest('nav');
                        if (nav.length === 1) {
                            let overlay = nav.next('.overlay');
                            Ads.display(overlay, 'prepare', 'block');
                            return overlay;
                        }
                    }
                }
            }
            return null;
        },
        show(element) {
            let loader = $(element).is('.list-loader , .overlay') ? $(element) : this.get(element);
            if (loader !== null) {
                Ads.display(loader, 'show');
            }
        },
        hide(element) {
            let loader = $(element).is('.list-loader , .overlay') ? $(element) : this.get(element);
            if (loader !== null) {
                loader.css('display', 'none');
            }
        }
    },

    list: {
        init(container) {
            $('[data-sorting]', container).css('cursor', 'pointer').click((e) => {
                let button = $(e.currentTarget);
                let list = button.closest('.list-body');
                let data = list.data('query') || {};
                data.sorting = button.data('sorting');
                if (button.data('sorting-desc')) {
                    data.sorting_desc = 1;
                } else {
                    delete data.sorting_desc;
                }
                Ads.list.refresh(list, data.page || null);
            });
        },
        openExtra(action, target, actionUrl, callback) {
            target = $(target);
            let $action = $(action);
            let listActions = $action.parent();
            let close = listActions.find('.list-close');
            let loader = Ads.loader.get($action);
            let slideDown = (target) => {
                listActions.children('.list-action').hide();
                target.slideDown({
                    start: () => target.css('display', 'flex'),
                    complete: () => {
                        Ads.loader.hide(loader);
                        close.css('display', 'inline');
                    }
                });
            }
            Ads.loader.show(loader);
            if (!actionUrl) {
                slideDown(target);
            } else {
                $.get({
                    url: CONFIG.BASE_URL + actionUrl,
                    dataType: 'html',
                    success(html) {
                        let newContent = $(html);
                        target.replaceWith(newContent);
                        Ads.Utils.initTooltips(newContent);
                        if (callback) {
                            callback.call(this, newContent, action);
                        }
                        slideDown(newContent);
                    },
                    error(data) {
                        console.log(data);
                    }
                });
            }
        },
        closeExtra(extra, listActions, callback) {
            extra.slideUp({
                complete() {
                    if (callback) {
                        callback.call(this, extra)
                    }
                }
            });
            listActions.find('.list-close').css('display', 'none');
            listActions.find('.list-loader').css('display', 'none');
            listActions.children('.list-action').fadeIn();
        },
        showForm(action, formName, formUrl) {
            Ads.list.openExtra(
                action,
                $(action).parent().siblings('form[data-name="' + formName + '"]').first(),
                formUrl,
                (newForm) => {
                    Ads.form.init(newForm);
                }
            );
        },
        closeForm(element) {
            let $element = $(element);
            let form, listActions;
            if ($element.is('form')) {
                form = $element;
                listActions = form.siblings('.list-actions');
            } else {
                listActions = $element.parent();
                form = listActions.siblings('form:visible');
            }

            Ads.list.closeExtra(form, listActions, (form) => {
                form.each((i, form) => Ads.form.reset(form, true));
            });

            //
            // form.slideUp({
            //     complete() {
            //         form.each((i, form) => Ads.form.reset(form, true));
            //     }
            // });
            // listActions.find('.list-close').css('display', 'none');
            // listActions.find('.list-loader').css('display', 'none');
            // listActions.children('.list-action').fadeIn();
        },
        page(element, pageNumber) {
            this.refresh(element, pageNumber);
        },
        refresh(element, pageNumber) {
            let $element = $(element);
            let listBody = $element.is('.list-body') ? $element : $element.closest('.list').find('.list-body');
            let url = listBody.data('url');
            let data = listBody.data('query') || {};
            if (pageNumber) {
                data.page = pageNumber;
            }

            Ads.loader.show($element);
            $.get({
                url: CONFIG.BASE_URL + url,
                data: data,
                dataType: 'html',
                success(html) {
                    let p = listBody.parent();
                    listBody.replaceWith(html);
                    Ads.list.init(p);
                    Ads.form.init(p.find('form'));
                    Ads.Utils.initTooltips(p);
                },
                error(data) {
                    console.log(data);
                }
            });
        },
        submitForm(button, method, success, closeForm, extraData) {
            let $button = $(button);
            let form = $button.closest('form');

            if (!form[0].checkValidity()) {
                form[0].reportValidity();
                return;
            }

            $button.attr('disabled', 'disabled');

            let ajaxOptions = {};
            if (form.is('[enctype="multipart/form-data"]')) {
                let formData = new FormData(form[0]);
                if (method.toUpperCase() === 'PUT') {
                    method = 'POST';
                    formData.append('_method', 'PUT');
                    if (extraData !== undefined) {
                        for (let k in extraData) {
                            formData.append(k, extraData[k]);
                        }
                    }
                }
                ajaxOptions = {
                    type: method,
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function () {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function (evt) {
                            if (evt.lengthComputable) {
                                let progressBar = $(".form-progress div", form);
                                progressBar.width(((evt.loaded / evt.total) * 100) + '%');
                                if (evt.total === evt.loaded) {
                                    progressBar.animate({opacity: 0}, 3000, () => progressBar.width(0).css('opacity', 1));
                                }
                            }
                        }, false);
                        return xhr;
                    },
                };
            } else {
                let data = form.serialize();
                if (extraData !== undefined) {
                    for (let k in extraData) {
                        if (extraData.hasOwnProperty(k)) {
                            data += "&" + k + "=" + extraData[k];
                        }
                    }
                }
                ajaxOptions = {type: method, data: data, dataType: 'json'};
            }

            Ads.loader.show(form);
            $.ajax($.extend(ajaxOptions, {
                url: CONFIG.BASE_URL + form.attr('action'),
                success(json) {
                    success(json.result);
                    if (!!$button) { // success may replace content and $button may be deleted
                        $button.removeAttr('disabled');
                    }
                    if (!!closeForm && !!form) {
                        Ads.list.closeForm(form);
                    }
                    if (json['alert']) {
                        $('.alerts').append(json.alert);
                    }
                },
                error(xhr, textStatus, errorThrown) {
                    let errors = xhr.responseJSON ? xhr.responseJSON.errors : {form: xhr.status + ': ' + textStatus + ' ' + errorThrown};
                    console.log(errors);
                    Object.keys(errors).forEach(key => {
                        let field = form.find('[name="' + key + '"]');
                        if (field.length > 0) {
                            field.addClass('is-invalid');
                            let message = ($.isArray(errors[key]) ? errors[key] : [errors[key]]).join('');
                            field.after('<span class="invalid-feedback" role="alert">' + message + '</span>');
                        }
                    });
                    if (errors['form']) {
                        form.prepend('<div role="alert" class="invalid-feedback d-block ml-3">' + errors.form + '</div>');
                    }
                    $button.removeAttr('disabled');
                }
            }));
        },
        submitAddForm(button) {
            this.submitForm(button, 'POST', html => $(button).closest('.list').find('.list-rows li.header').after(html), true);
        },
        submitSearchForm(button) {
            let form = $(button).prop("disabled", false).closest('form');
            let listBody = form.closest('.list').find('.list-body');
            listBody.data('url', form.attr('action'));
            listBody.data('query', Ads.Utils.serializeObject(form));
            this.refresh(listBody);
        },
    },

    item: {
        openExtra(action) {
            let $action = $(action);
            let data = $action.data('query') || {};
            let url = $action.data('url');
            let parent = $action.parent();
            let actions = parent.is('.row-actions') ? parent : parent.find('.row-actions').first();
            let extra = $action.closest('.row-content').siblings('.row-extra').first();
            let overlay = Ads.loader.get($action);

            Ads.loader.show(overlay);
            $.get({
                url: CONFIG.BASE_URL + url,
                data: data,
                dataType: 'html',
                success(html) {
                    html = $(html);
                    Ads.loader.hide(overlay);
                    extra.find('.extra-content').empty().append(html);
                    if (actions) {
                        actions.find('.row-action').css('visibility', 'hidden');
                    }
                    extra.slideDown('fast', () => {
                        extra.css('height', 'auto');
                        extra.find('.extra-close').css('display', 'block');
                        Ads.form.init(extra.find('form'));
                        Ads.Utils.initTooltips(extra);
                    });
                },
                error(data) {
                    console.log(data);
                }
            });
        },
        closeExtra(close, complete) {
            let $close = $(close).fadeOut('fast');
            let extra = $close.closest('.row-extra');
            extra.slideUp({
                duration: 'fast', complete: complete || (() => {
                    extra.find('.extra-content').empty()
                })
            });
            extra.prev('.row-content').find('.row-action').css('visibility', 'visible');
        },
        updateRow(action, confirmMsg) {
            if (!!confirmMsg && !confirm(confirmMsg)) {
                return;
            }
            let $action = $(action);
            let data = $action.data('query') || {};
            let url = $action.data('url');
            let content = $action.closest('.row-content');

            Ads.loader.show($action);
            $.ajax({
                type: 'PUT',
                url: CONFIG.BASE_URL + url,
                data: data,
                dataType: 'html',
                success(html) {
                    let li = content.parent('li');
                    let newLi = $(html);
                    li.animate({opacity: 0.05}, {
                        complete() {
                            li.replaceWith(newLi);
                            newLi.animate({opacity: 1});
                            Ads.Utils.initTooltips(newLi);
                        }
                    });
                },
                error(xhr) {
                    console.log(xhr);
                    let error = 'Error';
                    try {
                        let json = JSON.parse(xhr.responseText);
                        if (json.hasOwnProperty('errors')) {
                            if (json.errors.hasOwnProperty('form')) {
                                error = json.errors.form;
                            } else if (Object.keys(json.errors).length > 0) {
                                error = Object.keys(json.errors).map(k => k + ': ' + json.errors[k]).join('. ') + '. ';
                            }
                        }
                    } catch {
                        error = xhr.responseText || error;
                    }
                    Ads.loader.hide($action);
                    Ads.Utils.alert(error);
                }
            });
        },
        deleteRow(action) {
            if (confirm('Are you sure?')) {
                let $action = $(action);
                let url = $action.data('url');
                let data = $action.data('query') || {};

                Ads.loader.show(action);
                $.ajax({
                    type: 'DELETE',
                    url: CONFIG.BASE_URL + url,
                    data: data,
                    dataType: 'html',
                    success(result) {
                        if (result === '1') {
                            let li = $action.closest('li');
                            li.fadeOut({
                                complete() {
                                    li.remove();
                                }
                            });
                        } else {
                            Ads.loader.hide(action);
                            Ads.Utils.alert(result);
                        }
                    },
                    error(xhr, err, msg) {
                        Ads.loader.hide(action);
                        console.log(data);
                        Ads.Utils.alert(xhr.status + ': ' + msg);
                    }
                });
            }
        },
        submitForm(button, extraData) {
            let extra = $(button).closest('.row-extra');
            Ads.list.submitForm(button, 'PUT', (html) => {
                Ads.item.closeExtra(extra.find('.extra-close'), () => {
                    let li = extra.closest('li');
                    let newLi = $(html);
                    li.animate({opacity: 0.05}, {
                        complete() {
                            li.replaceWith(newLi);
                            newLi.animate({opacity: 1});
                            Ads.Utils.initTooltips(newLi);
                        }
                    });
                });
            }, undefined, extraData)
        }
    },

    form: {
        init(form) {
            $('select:not(.simpleselect)').selectpicker();
            $(".slider").each((i, slider) => {
                if (slider.noUiSlider === undefined) {
                    let $slider = $(slider);
                    let range = {min: parseFloat($slider.data('min')), max: parseFloat($slider.data('max'))};
                    if (range.min !== range.max) {
                        $slider.customSlider({
                            start: parseFloat($slider.data('start')),
                            range: range,
                            connect: 'lower',
                            pips: {
                                mode: "values",
                                values: $slider.data('values').split(",").map(v => parseFloat(v)),
                                density: 1
                            }
                        });
                        let field = $slider.prev('div').find('input');
                        field.on('change', () => slider.noUiSlider.set(field.val()));
                        slider.noUiSlider.on('update', (values, handle) => field.val(values[handle]));
                    }
                }
            });
            $(form).off('submit').on('submit', function (e) {
                let button = $('button.submit', this);
                if (button.length === 1) {
                    e.preventDefault();
                    button[0].click();
                }
            });
            $('.datepicker-field').datepicker({format: 'yyyy-mm-dd'});
            $('.rel-parent', form).trigger('change');
            $('select.trigger-change', form).trigger('change');
        },
        handleRelations(form) {
            $.each(form.find('[data-rel]'), (i, child) => {
                let $child = $(child);
                $.each($child.data('rel'), (key, val) => {
                    $child = $child.closest('.form-group');
                    let parent = form.find('[name="' + key + '"]').first();
                    let hide = !parent.is(":visible") || !($.isArray(val) ? val : [val]).includes(parent.val());
                    if (hide) {
                        if ($child.is(':visible')) {
                            if ($child.is('[required]')) {
                                $child.attr('data-required', true);
                                $child.removeAttr('required');
                            }
                            Ads.display($child, 'hide');
                        }
                    } else {
                        if (!$child.is(':visible')) {
                            if ($child.is('[data-required]')) {
                                $child.attr('required', true);
                                $child.removeAttr('data-required');
                            }
                            Ads.display($child, 'show');
                        }
                    }
                });
            });
        },
        reset(form, htmlReset) {
            let $form = $(form);
            $form.find('.invalid-feedback').remove();
            $form.find('.dynamic-info').remove();
            $form.find('.dynamic-info-container').each((i, el) => {
                if (!$(el).is('canvas')) {
                    $(el).empty();
                } else {
                    Ads.Utils.emptyCanvas(el);
                }
            });
            $form.find('.is-invalid').removeClass('is-invalid');
            $('select:not(.simpleselect)').val('').trigger("change");
            if (htmlReset) {
                form.reset();
            } else {
                $form.find('[onchange]').trigger('change');
            }
        },
        legendToggle(toggle) {
            $(toggle).closest('fieldset').find('input[type=checkbox]').not('.legend input').prop('checked', toggle.checked);
        },
        relations(parent) {
            let $parent = $(parent);
            let value = $parent.find(':selected').val();
            let dataKey = $parent.attr('name');
            $parent.closest('form').find('[data-' + dataKey + ']').each((i, field) => {
                let $field = $(field);
                let values = $field.data(dataKey).split(',');
                if (values.includes(value)) {
                    if ($field.is('[data-required]')) {
                        $field.attr('required', true).removeAttr('data-required');
                    }
                } else {
                    if ($field.is('[required]')) {
                        $field.attr('data-required', true).removeAttr('required');
                    }
                }
                let parent = $field.is('[data-ad-type-no-container]') ? $field : $field.closest('.form-group');
                Ads.display(parent, values.includes(value) ? 'show' : 'hide');
            });
        }
    },

    display(element, action, value) {
        if (action === undefined || action === 'show') {
            element.css('display', $.data(element, 'display-attribute') || 'block');

        } else if (action === 'hide') {
            this.display(element, 'prepare');
            element.css('display', 'none');

        } else if (action === 'prepare') {
            $.data(element, 'display-attribute', value || element.css('display'));
        }
    },

    Modules: {
        Ads: {
            form: {
                relations(adType) {
                    // TODO use Ads.form.relations instead
                    let $adType = $(adType);
                    let type = $adType.find(':selected').data('type');
                    $adType.closest('form').find('[data-ad-type]').each((i, field) => {
                        let $field = $(field);
                        let values = $field.data('ad-type').split(',');
                        if (values.includes(type)) {
                            if ($field.is('[data-required]')) {
                                $field.attr('required', true).removeAttr('data-required');
                            }
                        } else {
                            if ($field.is('[required]')) {
                                $field.attr('data-required', true).removeAttr('required');
                            }
                        }
                        let parent = $field.is('[data-ad-type-no-container]') ? $field : $field.closest('.form-group');
                        Ads.display(parent, values.includes(type) ? 'show' : 'hide');
                    });
                }
            }
        },
        Profile: {
            submitForm(button) {
                Ads.list.submitForm(button, 'put', (json) => {
                    $(button).closest('.list').replaceWith(json.list);
                    $('.alerts').append(json.alert);
                    Ads.form.init($('form'));
                });
            }
        },
        CampaignsCountries: {
            submitForm(button) {
                Ads.list.submitForm(button, 'put', () => Ads.item.closeExtra(button));
            },
            changeCategory(select) {
                let option = $('option:selected', $(select));
                let form = option.closest('form');
                let cleaning = option.val().length === 0;
                let cost = option.data('cost');
                let countries = JSON.parse(atob(option.data('countries') || 'e30='));
                let labels = $('label[for^=countries]', form);
                labels.each((i, label) => {
                    label = $(label);
                    let id = label.attr('for').substring(10, 12); // countries[XX]
                    let html = label.html().split(':');
                    label.html(html[0] + (cleaning ? '' : ': $' + (parseFloat(countries[id] || cost).toFixed(5))));
                });

                let tier4Label = $('label[for="Tier 4"]', form);
                let html = tier4Label.html().split(':');
                tier4Label.html(html[0] + (cleaning ? '' : ': $' + (parseFloat(option.data('tier4') || cost).toFixed(5))));
            }
        },
        Invoices: {
            showWalletInfo(currencySelect) {
                currencySelect = $(currencySelect);
                let walletInfo = currencySelect.closest('.form-group').next('.wallet-info');
                let option = currencySelect.find(":selected");
                let wallet = option.data('wallet');
                let h5 = walletInfo.find('h5');
                let canvas = walletInfo.find('canvas').first()[0];
                let form = currencySelect.closest('form');
                let hidden = form.find('[name=wallet]');
                if (hidden.length === 0) {
                    hidden = $('<input type="hidden" name="wallet"/>').appendTo(form);
                }
                hidden.val(wallet || '');

                if (wallet) {
                    h5.text(wallet);
                    QRCode.toCanvas(canvas, wallet, {errorCorrectionLevel: 'H', scale: 6});
                    this.invoicesAmountChange(form.find('[name=amount]'));
                } else {
                    h5.empty();
                    Ads.Utils.emptyCanvas(canvas);
                }
            },
            invoicesAmountChange(amountField) {
                amountField = $(amountField);
                let amount = amountField.val();
                let currency = amountField.closest('form').find('[name=currency] option:selected');
                if (currency.length > 0) {
                    let curr = currency.val();
                    let rate = currency.data('rate');
                    let desc = amountField.closest('.row').find('.crypto-amount');
                    let result = '';
                    if (!isNaN(amount) && !isNaN(rate)) {
                        result = parseFloat((amount * rate).toFixed(CONFIG.DECIMALS));
                    }
                    desc.html('<b>' + result + ' ' + curr + '</b>');
                }
            },
            currencySelectChange(select) {
                this.invoicesSelectChange($(select).closest('form').find('[name="invoices[]"]'));
            },
            invoicesSelectChange(select) {
                select = $(select);
                let total = 0.0;
                select.find('option:selected').each((i, o) => {
                    total += parseFloat(o.dataset['amount'])
                });
                let form = select.closest('form');
                form.find('.total-amount').text((total < 0 ? '-' : '') + '$' + Math.abs(total).toFixed(2));
                let currency = form.find('[name=currency] option:selected');
                if (currency.length > 0) {
                    let curr = currency.val();
                    let rate = currency.data('rate');
                    let desc = form.find('.crypto-amount');
                    let result = '';
                    if (!isNaN(rate)) {
                        result = parseFloat((total * rate).toFixed(CONFIG.DECIMALS));
                    }
                    desc.html(result + ' ' + curr);
                }
            },
            verifyPromo(button) {
                let $button = $(button);
                let $profit = $('#promo-profit');
                let code = $('#promo').val();
                if (code.length > 0) {
                    $.ajax({
                        method: 'GET',
                        url: $button.data('url'),
                        data: {code: code},
                        dataType: "json",
                        beforeSend() {
                            $profit.html('Verifying...');
                            $button.attr('disabled', true);
                        },
                        success(json) {
                            if (json && json.hasOwnProperty('result')) {
                                $profit.html('Bonus: %' + parseFloat(json.result));
                            }
                            $button.attr('disabled', false);
                        },
                        error(xhr) {
                            let error = 'Please try again later.';
                            try {
                                error = xhr.responseJSON.errors.form
                            } catch {
                            }
                            $profit.html(error);
                            $button.attr('disabled', false);
                        }
                    });
                }
            },
            showWithdrawalRequests(action, target, url) {
                if (target.css('display') === 'none') {
                    action = $(action);
                    let loader = Ads.loader.get(action);

                    Ads.loader.show(loader);
                    $.get({
                        url: CONFIG.BASE_URL + url,
                        dataType: 'html',
                        success(html) {
                            action.addClass('d-none');
                            target.html(html).removeClass('d-none');

                            $('html, body').animate({scrollTop: target.offset().top}, 1000);

                            Ads.Utils.initTooltips(target);
                            Ads.loader.hide(loader);
                        },
                        error(data) {
                            console.log(data);
                        }
                    });
                }
            }
        },
        EmailsTemplates: {
            attachments: {
                add(button) {
                    let parent = $(button).parent();
                    let maxIndex = -1;
                    parent.closest('form').find('[data-index]').each((i, el) => {
                        let index = parseInt(el.dataset.index);
                        maxIndex = index > maxIndex ? index : maxIndex;
                    });
                    let index = maxIndex + 1;
                    let cloned = parent.nextAll('.template').clone(true).removeClass('template').removeClass('d-none');

                    cloned.find('[for="attachment-name"] , [for="attachment-file"], [for="attachment-inline"]').each((i, el) => {
                        el.for = 'attachment[' + index + '][' + (el.for === "attachment-name" ? 'name' : (el.name === "attachment-file" ? 'file' : 'inline')) + ']';
                    });
                    cloned.find('[name="attachment-name"] , [name="attachment-file"], [name="attachment-inline"]').each((i, el) => {
                        let key = el.name === "attachment-name" ? 'name' : (el.name === "attachment-file" ? 'file' : 'inline');
                        el.id = 'attachment[' + index + '][' + key + ']';
                        el.name = 'attachment[' + index + '][' + key + ']';
                        el.dataset.index = index;
                    });
                    cloned.insertAfter(parent);
                },
                remove(button) {
                    let div2 = $(button).closest('.attach');
                    div2.prev('.attach').remove();
                    div2.remove();
                },
                removeFixed(button) {
                    $(button).closest('.attach').remove();
                }
            }
        },
        Captcha: {
            reload(container) {
                $(container).load(CONFIG.BASE_URL + '/refresh-captcha');
            }
        }
    },

    Utils: {
        emptyCanvas(canvas) {
            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
        },
        getFormData($form) {
            let formData = new FormData($form[0]);
            let deleteList = [];
            for (let pair of formData.entries()) {
                if (pair[1] === undefined || pair[1] === null || pair[1] === "") {
                    deleteList.push(pair[0]);
                }
            }
            deleteList.forEach((key) => formData.delete(key));
            return formData;
        },
        serializeObject($form) {
            let o = {};
            $form.serializeArray().forEach(item => {
                if (item.value.length > 0) {
                    if (o[item.name]) {
                        if (!o[item.name].push) {
                            o[item.name] = [o[item.name]];
                        }
                        o[item.name].push(item.value);
                    } else {
                        o[item.name] = item.value;
                    }
                }
            });
            return o;
        },
        go(url, newTab, confirmMsg) {
            if (!confirmMsg || confirm(confirmMsg)) {
                window.open(CONFIG.BASE_URL + url, newTab ? '_blank ' : '_self');
            }
        },
        countdown(element, millis, expired) {
            debugger;
            let t = setInterval(() => {
                $(element).html(String(Math.floor(millis / 60000)).padStart(2, '0') + ":" + String(Math.floor((millis % 60000) / 1000)).padStart(2, '0'));
                if (millis < 0) {
                    clearInterval(t);
                    $(element).html(expired);
                }
                millis -= 1000;
            }, 1000);
        },
        initTooltips(container) {
            $('[data-toggle="tooltip"]', container).each((i, el) => $(el).tooltip({container: el.parentElement}));
        },
        submitAction(button) {
            button = $(button);
            if (button.data('confirm') && !confirm(button.data('confirm'))) {
                return;
            }
            $.ajax({
                method: 'POST',
                url: button.data('url'),
                data: button.data('params') || [],
                dataType: "html",
                beforeSend() {
                    button.attr('disabled', true);
                },
                success(html) {
                    if (html) {
                        button.replaceWith(html);
                    } else {
                        button.attr('disabled', false);
                    }
                },
                error(xhr, textStatus, errorThrown) {
                    let errors = xhr.responseText ? xhr.responseText : xhr.status + ': ' + textStatus + ' ' + errorThrown;
                    console.log(errors);
                    Ads.Utils.alert(errors);
                    button.attr('disabled', false);
                }
            });
        },
        alert(msg, header) {
            header = header || 'Warning';
            let modalEl = $('#alert-modal');
            $('.modal-title', modalEl).html(header);
            $('.modal-message', modalEl).html(msg);
            modalEl.modal("show");
        }
    }
};

GDPR = {
    agree(key) {
        if (!key) {
            //GDPR.agree('gdpr-necessary-cookies');
            GDPR.agree('gdpr-functionality-cookies');
            GDPR.agree('gdpr-targeting-cookies');
            GDPR.agree('gdpr-tracking-cookies');

            $('.promo-popup').hide();
        } else {
            Cookies.set(key, true, {expires: 365, path: '/'});
            GDPR.flag();
        }
    },

    decline(key) {
        if (!key) {
            //GDPR.decline('gdpr-necessary-cookies');
            GDPR.decline('gdpr-functionality-cookies');
            GDPR.decline('gdpr-targeting-cookies');
            GDPR.decline('gdpr-tracking-cookies');

            $('.promo-popup').hide();
        } else {
            Cookies.remove(key);
            GDPR.flag();
        }
    },

    toggle(key, set) {
        if (!!set) {
            GDPR.agree(key);
        } else {
            GDPR.decline(key);
        }
    },

    flag() {
        Cookies.set('gdpr', true, {expires: 365, path: '/'});
    },

    get(key) {
        return !!Cookies.get(key);
    }
}
