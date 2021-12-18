(function () {

    var sliderDefaultValue = '0,100';


//Domain Tag Add
    function setListTagCount() {
        $('#tagslist .list-group-item').each(function (index) {
            count = index + 1;
            $(this).find('.count').text(count);
        });
    }

    $("#add-tag").on("click", function () {

        var count = $('#tagslist .list-group-item').length + 1;
        var current = $("input[name='tags[]']")
                .map(function () {
                    return $(this).val();
                }).get();

        names = []
        hidden_fields = []

        var item = $("#tagsbox option:selected");

        //if empty
        if (item.val() == '') {
            var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Lütfen bir kelime kategorisi seçin.'}
            showAlertModal(alertData);
            return false;
        }

        //check if exists
        if (jQuery.inArray(item.val(), current) != -1)
        {
            var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Bu kategorileri daha önce eklediniz.'}

            showAlertModal(alertData);
            return false;
        } else {

            names.push(item.text());

            hidden_fields.push("<input type='hidden' value='" + item.val() + "' name='tags[]' />");
            $('<li class="list-group-item"><span class="count">' + count + '</span>- ' + names.join(' + ') + ' <button type="button" class="close"><span aria-hidden="true">&times;</span><span class="sr-only">Kaldır</span></button>' + hidden_fields.join(' ') + '</li>').appendTo($("#tagslist"));

        }
        //$('#save_btn').attr("disabled", false);
        setListTagCount();
    });

    $(document).on("click", "#tagslist .close", function () {

        var count = $('#tagslist .list-group-item').length - 1;

        $(this).parents("li").fadeOut().remove();
        //remove inputs
        setListTagCount();

    });
//Domain Source Add END

    jQuery.fn.checkboxVal = function () {
        if ($(this).is(':checked')) {
            return 1;
        } else {
            return 0;
        }
    }

    jQuery.fn.textboxVal = function () {
        if ($(this).val() != "") {
            return $(this).val();
        } else {
            return 0;
        }
    }

    jQuery.fn.uncheckableRadio = function () {
        $(this).on("click", function (event) {
            var this_input = $(this);
            if (this_input.attr('checked1') == '11') {

                this_input.attr('checked1', '11')

            } else {
                this_input.attr('checked1', '22')
            }
            $('.radio-button').prop('checked', false);
            if (this_input.attr('checked1') == '11') {
                this_input.prop('checked', false);
                this_input.attr('checked1', '22')
            } else {
                this_input.prop('checked', true);
                this_input.attr('checked1', '11')
            }
        });
    }

    function getSearchParameter() {
        var textSearchValue = $('#textSearch').textboxVal();
        var selectCountValue = $('#selectCount').textboxVal();
        var textUntilValue = $('#textUntil').textboxVal();
        var selectDomain = $("#selectDomain").val();
        var selectPublished = $("#selectPublished").val();
        var selectProcessed = $("#selectProcessed").val();
        var selectOrder = $("#selectOrder").val();
        var checkTags = $('input[name="tags[]"]').map(function () {
            return $(this).val();
        }).get();
        var checkSlider = $('.radio-slider-check').checkboxVal();

        var parameter = {
            search: textSearchValue,
            count: selectCountValue,
            until: textUntilValue,
            domain_id: selectDomain,
            is_published: selectPublished,
            is_processed: selectProcessed,
            order: selectOrder,
            include_tags: checkTags,
            check_slider: checkSlider
        };

        if (checkSlider == 1) {
            parameter = $.extend({}, parameter, getSliderBarValues());
        }

        return parameter;
    }

    function getDomainCheckbox() {
        return $('#selectDomain').val();
    }

    function getSearchComments(parameter, btn) {
        // show loading first document load
        showLoading('since');

        btn = btn || false;

        if (btn != false) {
            btnLoading(btn);
        }

        var jqxhr = $.post('/comments/search', parameter)
                .done(function (data) {

                    var comments = data;

                    // When rending an underscore template, we want top-level
                    // variables to be referenced as part of an object. For
                    // technical reasons (scope-chain search), this speeds up
                    // rendering; however, more importantly, this also allows our
                    // templates to look / feel more like our server-side
                    // templates that use the rc (Request Context / Colletion) in
                    // order to render their markup.
                    _.templateSettings.variable = "rc";

                    // Grab the HTML out of our template tag and pre-compile it.
                    var template = _.template(
                            $('#commentTemplate').html()
                            );

                    var tableTemplate = _.template(
                            $('#commentTableTemplate').html()
                            );

                    // Define our render data (to be put into the 'rc' variable).
                    var templateData = {
                        listTitle: 'Akış',
                        listItems: comments,
                        names: data.lists
                    };

                    var tableTemplateData = {
                        listTitle: 'Akış Tablo',
                        listItems: comments
                    };


                    // Render the underscore template and inject it after the H1
                    // in our current DOM.
                    $('#comments').html(template(templateData)).hide().fadeIn(400);


                    $('#commentsTableBody').html(tableTemplate(tableTemplateData));

                    if (btn != false) {
                        btnLoading(btn, true);
                    }
                    hideLoading('since');
                    getBadword();

                    // refresh every 60 seconds time line for new tweets
                    // refreshTimeLine();
                })
                .fail(function () {
                    console.log('error:' + data.msg);
                });

        // Set another completion function for the request above
        jqxhr.always(function () {
            if (btn != false) {
                btnLoading(btn, true);
            }
            hideLoading('since');
        });

    }

    function updateSearchComments(parameter, btn) {
        btn = btn || false;

        if (btn != false) {
            btnLoading(btn);
        }

        var jqxhr = $.post('/comments/search', parameter)
                .done(function (data) {
                    var comments = data; // an array includes object

                    var commentCount = $.map(comments, function (n, i) {
                        return i;
                    }).length;

//               window.tweet_caches.push(data.statuses)
                    // When rending an underscore template, we want top-level
                    // variables to be referenced as part of an object. For
                    // technical reasons (scope-chain search), this speeds up
                    // rendering; however, more importantly, this also allows our
                    // templates to look / feel more like our server-side
                    // templates that use the rc (Request Context / Colletion) in
                    // order to render their markup.
                    _.templateSettings.variable = 'rc';

                    // Grab the HTML out of our template tag and pre-compile it.
                    var template = _.template(
                            $('#commentTemplate').html()
                            );

                    var tableTemplate = _.template(
                            $('#commentTableTemplate').html()
                            );

                    // Render the underscore template and inject it after the H1
                    // in our current DOM.
                    if (commentCount > 0) {
                        if (parameter.since) {

                            // Define our render data (to be put into the 'rc' variable).
                            var templateData = {
                                listTitle: 'Akış',
                                listItems: comments,
                                listItemHide: true,
                                names: data.lists,
                            };
                            $('#' + parameter.since_id).before(template(templateData));

                        } else if (parameter.max) {

                            // Define our render data (to be put into the 'rc' variable).
                            var templateData = {
                                listTitle: 'Akış',
                                listItems: comments,
                                listItemHide: false,
                                names: data.lists

                            };

                            var tableTemplateData = {
                                listTitle: 'Akış Tablo',
                                listItems: comments
                            };

                            //$('#' + parameter.max_id ).after( template( templateData ) );
                            $('#comments').append(template(templateData));

                            $('#commentsTableBody').append(tableTemplate(tableTemplateData));
                        }
                    }

                    if (btn != false) {
                        btnLoading(btn, true);
                    }
                    hideLoading('since');
                    hideLoading('max');
                    getBadword();
                })
                .fail(function () {
                    console.log('error');
                });

        // Set another completion function for the request above
        jqxhr.always(function () {
            if (btn != false) {
                btnLoading(btn, true);
            }
            hideLoading('since');
            hideLoading('max');
        });
    }

    // add text to db with its aVal return retVal true or false
    function addBayesLive(mediaText, mediaState, commentId, domainId) {

        if (commentId == '' || domainId == '') {
            var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Geçersiz bir işlem yürütüldü. Lütfen bir süre sonra tekrar deneyiniz.'}
            showAlertModal(alertData);
            retVal = false;
        }

        var retVal = false;

        var comment = $('#' + commentId);

        var url = '/traine';
        var parameter = {
            text: mediaText,
            state: mediaState,
            source_id: commentId,
            domain_id: domainId,
            source: 'comment'
        };

        var request = $.ajax({
            type: "POST",
            async: false,
            url: url,
            data: parameter,
            dataType: "json"
        });

        request.done(function (data) {
            if (data.response == 1) {
                comment.find('.positive-percent').text(data.positive);
                comment.find('.negative-percent').text(data.negative);
                comment.find('.neutral-percent').text(data.neutral);

                console.log(data);

                retVal = true;
            } else if (data.response == -1) {
                var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Daha önceden kayıtlı <a href="/sentimental/' + data.sentimental + '/edit" target="_blank">burayı tıklayarak</a> düzenleyebilirsiniz'}
                showAlertModal(alertData);
                retVal = false;
            } else if (data.response == 2) {
                var alertData = {
                    type: 'modal-md',
                    title: '<i class="fa fa-minus-circle"></i> Uyarı',
                    text: data.message
                }
                showAlertModal(alertData);
            } else {
                var alertData = {type: 'modal-sm', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Bir hata oluştu lütfen daha sonra tekrar deneyiniz!'}
                showAlertModal(alertData);
                retVal = false;
            }
        });
        request.fail(function (jqXHR, textStatus) {
            console.log("Request failed: " + textStatus);
        });

        return retVal;
    }

    // update date created tweets every 1 minute
    function updateDateCreated() {
        setInterval(function () {
            $('.created').each(function () {
                var createdAt = $(this).attr('data-created');
                $(this).text($.format.prettyDate(createdAt));
            });
        }, 30000);
    }

    function btnLoading(btn, reset) {
        btn.button('loading');

        reset = reset || false;

        if (reset) {
            btn.button('reset');
        }
    }

    function checkTextSearchValue(value) {
        if (value == "" || value == 0 || value.length == "") {
            var alertData = {type: 'modal-sm', title: '<i class="fa fa-minus-circle"></i> Hata', text: 'Lütfen Arama kriteri olarak bir <strong>"kelime"</strong> giriniz.'}
            showAlertModal(alertData);
            return false;
        }
    }

    // update every 60 second (60000 ms)
    function refreshTimeLine() {
        var time = 60000 * 5; // every 5 minutes

        setInterval(function () {
            var sinceId = $('.timeline-entry:first-child').data('id');

            var parameter = getSearchParameter();

            var sinceParameter = {
                max_id: sinceId,
                since: true,
                max: false
            };

            parameter = $.extend({}, parameter, sinceParameter);

            if (parameter.since_id != null) {
                updateSearchComments(parameter);
            }
        }, time);
    }

    function infiniteLoad() {
        var previousScroll = 0; // detect previos scroll position
        var loading = false; //to prevents multipal ajax loads
        var offset = 100; //scroll bottom offset margin

        $(window).scroll(function () { //detect page scroll

            if ($(window).scrollTop() >= $(document).height() - $(window).height() - offset) {

                var currentScroll = $(window).scrollTop(); //current scroll position

                if (currentScroll > previousScroll) { // check if window scroll down

                    if (loading == false) { //there's more data to load

                        loading = true; //prevent further ajax loading

                        var maxId = $('.timeline-entry:last-child').data('id');

                        if (maxId != undefined) {

                            var parameter = getSearchParameter();

                            var maxParameter = {
                                max_id: maxId,
                                since: false,
                                max: true
                            };

                            parameter = $.extend({}, parameter, maxParameter);

                            showLoading('max');
                            updateSearchComments(parameter);

                            setTimeout(function () {
                                loading = false;

                                //$('html, body').animate({
                                //    scrollTop: $(window).scrollTop() + 200
                                //}, 400);
                            }, 3000);
                        }
                    }
                }

                previousScroll = currentScroll;
            }
        });
    }

    function getBadword() {

        $("[rel=badword]").popover({
            trigger: 'click',
            animation: true,
            placement: 'auto right',
            html: true,
            content: ['<div id="mybadwordpopover" class="input-group">',
                '      <button type="button" class="btn btn-default badword"><span class="fa fa-recycle"></span> Kelimeyi listeden sil</button>',
                '</div>'].join(' ')
        });

        $("[rel=suspect]").popover({
            trigger: 'click',
            animation: true,
            placement: 'auto right',
            html: true,
            content: ['<div id="mysuspectpopover" class="input-group">',
                '<select id="tagform" class="form-control">' + $("#tagsbox").html() + '</select>',
                '<span class="input-group-btn">',
                '<button class="btn btn-success badword" type="submit"><span class="fa fa-search"></span>Ekle</button>',
                '</span>',
                //'<button class="btn btn-success btn-xs badword">Kara listeye ekle</button>',
                '</div>'].join(' ')
        });

        $("[rel=badword]").unbind('shown.bs.popover');
        $("[rel=suspect]").unbind('shown.bs.popover');
        $('[rel=badword]').on('shown.bs.popover', function () {

            $(".badword").click(function () {
                var self = $(this);
                _id = $(this).parents('.popover').attr('id');
                tag_id = $("a[aria-describedby=" + _id + "]").attr('data-id');
                tag = $("a[aria-describedby=" + _id + "]").attr('data-text');
                if (tag_id != undefined || tag_id != null) {
                    url = '/tag/remove';
                    $.post(url, {
                        'tag_id': tag_id,
                        'tag': tag
                    }).done(function (data) {

                        if (data.response == 1) {

                            $("a[href='#ynk']").filter("[data-id='" + tag_id + "']").attr('rel', 'suspect').removeClass('btn btn-default btn-xs').addClass('suspect').text(tag);
                            $('[rel=suspect]').popover('destroy');
                            $('[rel=badword]').popover('destroy');
                            getBadword();
                            bc = $(self).parents('article').find('[rel=badword]').length;
                            if (bc == 0) {
                                $(self).parents('article').find('button').attr('disabled', false);
                                //$("#pos-tagging").html();
                                $check = $(self).parents("article").find(".check");
                                $check.find(".positive-percent").text($check.find(".positive-percent").data("sentimental-positive"));
                                $check.find(".negative-percent").text($check.find(".negative-percent").data("sentimental-negative"));
                                $check.find(".neutral-percent").text($check.find(".neutral-percent").data("sentimental-neutral"));
                            }
                            markAsCommentNegative($(this));
                        } else {
                            var alertData = {
                                type: 'modal-md',
                                title: '<i class="fa fa-minus-circle"></i> Uyarı',
                                text: data.msg
                            }
                            showAlertModal(alertData);
                        }
                    });
                }
            });
        });

        $('[rel=suspect]').on('shown.bs.popover', function () {
            $('[rel=suspect]').not(this).popover('hide');
            $(".badword").click(function () {

                var item = $("#tagform option:selected");

                //alert(item.val());
                //if empty
                if (item.val() == '') {
                    var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Lütfen bir kelime kategorisi seçin.'}
                    showAlertModal(alertData);
                    return false;
                }

                var self = $(this);
                var _id = $(this).parents('.popover').attr('id');
                var tag = $("a[aria-describedby=" + _id + "]").text();
                if (tag != undefined || tag != null) {
                    var url = '/tag/add';
                    $.post(url, {
                        'tag_id': item.val(),
                        'tag': tag,
                    }).done(function (data) {

                        if (data.response == 1) {
                            $("a[aria-describedby=" + _id + "]").attr('rel', 'badword').addClass('btn btn-default btn-xs').removeClass('suspect').attr('data-id', data.id).html('<span class="fa fa-tag"></span> ' + tag);

                            $('[rel=suspect]').popover('destroy');
                            $('[rel=badword]').popover('destroy');
                            getBadword();
                            markAsCommentNegative(self);
                            self.parents('article').find('button').attr('disabled', true);
                            self.parents('article').find(".positive-percent").text(0);
                            self.parents('article').find(".negative-percent").text(100);
                            self.parents('article').find(".neutral-percent").text(0);
                        } else {
                            var alertData = {
                                type: 'modal-md',
                                title: '<i class="fa fa-minus-circle"></i> Uyarı',
                                text: data.msg
                            }
                            showAlertModal(alertData);
                        }

                    });
                }
            });
        });
    }

    function hideLoadNew() {
        var loadNew = $('.load-new');
        var loadNewCount = loadNew.find('.tweet-count');
        loadNew.fadeOut(400);
        loadNewCount.text(0);
    }

    function getSliderBarValues() {
        // get slider values
        var sliderPositiveValue = [0, 100];
        var sliderNegativeValue = [0, 100];
        var sliderNeutralValue = [0, 100];

        // check if slider bars active
        var sliderPositiveCheck = $('#sliderPositiveCheck:checked');
        var sliderNegativeCheck = $('#sliderNegativeCheck:checked');
        var sliderNeutralCheck = $('#sliderNeutralCheck:checked');

        if (sliderPositiveCheck.length > 0) {
            sliderPositiveValue = sliderPositive.getValue();
        }
        if (sliderNegativeCheck.length > 0) {
            sliderNegativeValue = sliderNegative.getValue();
        }
        if (sliderNeutralCheck.length > 0) {
            sliderNeutralValue = sliderNeutral.getValue();
        }

        var values = {
            'slider_positive': sliderPositiveValue,
            'slider_negative': sliderNegativeValue,
            'slider_neutral': sliderNeutralValue
        };

        return values;
    }

    // Set sliders percent value of text
    function setSliderPercent(elem, elemVal) {
        elem.parents('.slider-input').find('.slider-percent').text(elemVal);
    }

    function setSliderBarDefault() {
        var sliderPositiveValue = sliderPositive.getValue();
        var sliderNegativeValue = sliderNegative.getValue();
        var sliderNeutralValue = sliderNeutral.getValue();

        setSliderPercent($('#sliderPositive'), sliderPositiveValue);
        setSliderPercent($('#sliderNegative'), sliderNegativeValue);
        setSliderPercent($('#sliderNeutral'), sliderNeutralValue);
    }

    var sliderPositive = $('#sliderPositive').slider()
            .on('slide', function () {
                var slider = $(this);
                var sliderPositiveValue = sliderPositive.getValue();

                $('#sliderPositiveCheck').prop('checked', true);

                setSliderPercent(slider, sliderPositiveValue);
            })
            .data('slider');

    var sliderNegative = $('#sliderNegative').slider()
            .on('slide', function () {
                var slider = $(this);
                var sliderNegativeValue = sliderNegative.getValue();

                $('#sliderNegativeCheck').prop('checked', true);

                setSliderPercent(slider, sliderNegativeValue);
            })
            .data('slider');

    var sliderNeutral = $('#sliderNeutral').slider()
            .on('slide', function () {
                var slider = $(this);
                var sliderNeutralValue = sliderNeutral.getValue();

                $('#sliderNeutralCheck').prop('checked', true);

                setSliderPercent(slider, sliderNeutralValue);
            })
            .data('slider');

    // set sliders width 100%
    $('.slider').width('100%');

    // search form submit event fire get tweets
    $('#formSearch').submit(function (event) {

        var btn = $(this).find('.btn-loading');

        var parameter = getSearchParameter();
        /*
         if (checkTextSearchValue( parameter.q ) == false) {
         return false;
         }
         */
        hideLoadNew();
        showLoading('since');
        getSearchComments(parameter, btn);
        scrollToTop();
        event.preventDefault();
    });

    // tag filter button click event fire and get search tweets for parameter and tag
    $('#btnTag').click(function () {

        var btn = $(this);

        var textSearchElem = $('#textSearch');
        var radioTagValue = $('.radio-tag:checked').val();

        // change search text value with radio value
        textSearchElem.val(radioTagValue);

        var parameter = getSearchParameter();
        /*
         if (checkTextSearchValue( parameter.q ) == false) {
         return false;
         }
         */
        hideLoadNew();
        showLoading('since');
        getSearchComments(parameter, btn);
    });

    // more button click event fire and get more tweet
    $('#btnMore').click(function () {

        var maxId = $('.timeline-entry:last-child').data('id');

        var parameter = getSearchParameter();

        var maxParameter = {
            max_id: maxId,
            since: false,
            max: true
        };

        parameter = $.extend({}, parameter, maxParameter);
        /*
         if (checkTextSearchValue( parameter.q ) == false) {
         return false;
         }*/

        hideLoadNew();
        showLoading('max');
        updateSearchComments(parameter);
    });

    $('#btnRefresh').click(function () {

        var btn = $(this);

        var sinceId = $('.timeline-entry:first-child').data('id');

        var parameter = getSearchParameter();

        var sinceParameter = {
            since_id: sinceId,
            since: true,
            max: false
        };

        parameter = $.extend({}, parameter, sinceParameter);
        /*
         if (checkTextSearchValue( parameter.q ) == false) {
         return false;
         }
         */
        showLoading('since');
        updateSearchComments(parameter, btn);
    });

    function setButtonNames(names) {
        $('#sliderPositiveCheck').parents('label').find('b').text(names[1]);
        $('#sliderNegativeCheck').parents('label').find('b').text(names[-1]);
        $('#sliderNeutralCheck').parents('label').find('b').text(names[0]);
    }

    function updateTagsFilter(tags) {

        if (tags == null) {
            $('#tagsbox option:gt(0)').remove();
            //$('#tagsbox').prop( "disabled", false );
            $('#tagslist').empty();
        } else {
            $('#tagsbox option:gt(0)').remove();
            $('#tagslist').empty();

            var count = $('#tagslist .list-group-item').length;
            names = []
            hidden_fields = []

            $.each(tags, function (index, tag) {

                count = count + 1;
                //selectbox options
                $('#tagsbox').append($("<option></option>")
                        .attr("value", index).text(tag));

                hidden_fields.push("<input type='hidden' value='" + index + "' name='tags[]' />");
                $('<li class="list-group-item"><span class="count">' + count + '</span>- ' + tag + ' <button type="button" class="close"><span aria-hidden="true">&times;</span><span class="sr-only">Kaldır</span></button><input type="hidden" value="' + index + '" name="tags[]" /></li>').appendTo($("#tagslist"));

            });
        }

    }

    function getDataLearned() {
        var parameter = {
            domain_id: getDomainCheckbox()
        }

        //update domain credentials
        var jqxhr = $.post('/domain/tags', parameter)
                .done(function (data) {
                    if (data.response == 1) {
                        updateTagsFilter(data.tags);
                    } else {
                        var alertData = {
                            type: 'modal-md',
                            title: '<i class="fa fa-minus-circle"></i> Uyarı',
                            text: 'İşlem sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.'
                        }
                        showAlertModal(alertData);
                    }
                })
                .fail(function () {
                    console.log('error');
                });


        var jqxhr = $.post('/learned', parameter)
                .done(function (data) {
                    if (data.response == 1) {
                        animateProgress(data.dataLearned);

                        var btn = $(this).find('.btn-loading');

                        var parameter = getSearchParameter();
                        /*
                         if (checkTextSearchValue( parameter.q ) == false) {
                         return false;
                         }
                         */
                        hideLoadNew();
                        showLoading('since');
                        getSearchComments(parameter, btn);
                        scrollToTop();


                        setButtonNames(data.dataLearned.domainNames);
                    } else {
                        var alertData = {
                            type: 'modal-md',
                            title: '<i class="fa fa-minus-circle"></i> Uyarı',
                            text: 'İşlem sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.'
                        }
                        showAlertModal(alertData);
                    }
                })
                .fail(function () {
                    console.log('error');
                });

        // Set another completion function for the request above
        jqxhr.always(function () {
            //
        });
    }

    // positive button on click event fire mark as positive tweet
    $(document).on('click', '.btn-publish', function () {
        var btn = $(this);
        var commentId = $(this).parents('.timeline-entry').data('id');

        $.post('/comment/publish', {
            'comment_id': commentId
        }).done(function (data) {
            if (data.status == "ok") {
                if (data.published) {
                    var alertData = {type: 'modal-sm', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Yorum yayına alınmıştır'}
                    $(btn).removeClass("btn-default").addClass("btn-success").html('<span class="fa fa-send"></span> Yayından kaldır')
                } else
                {
                    var alertData = {type: 'modal-sm', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Yorum yayından kaldırılmıştır'}
                    $(btn).removeClass("btn-success").addClass("btn-default").html('<span class="fa fa-send"></span> Yayınla')
                }
                showAlertModal(alertData);
            }
        });

    });

    function markAsCommentPositive(btn) {
        btn.parents('.timeline-entry-inner').find('.timeline-icon').removeClass('bg-info bg-danger bg-warning').addClass('bg-success');
        btn.parents('.timeline-entry-inner').find('.fa-icon').removeClass('fa-minus-circle fa-times-circle').addClass('fa-check-circle');
        btn.parents('.btn-group-action').find('.btn').removeProp('disabled');
        btn.prop('disabled', true);
    }

    function getAddBayesValues() {
        var domainId = $('#selectDomain').val();
        var tweetId = $(this).parents('.timeline-entry').data('id');
        var text = $(this).parents('.media-body').find('.media-text').text();
        return {domainId: domainId, tweetId: tweetId, text: text};
    }

    // positive button on click event fire mark as positive tweet
    $(document).on('click', '.btn-positive', function () {
        var btn = $(this);
        var state = 1;

        var domainId = $('#selectDomain').val();
        var tweetId = btn.parents('.timeline-entry').data('id');
        var text = btn.parents('.media-body').find('.media-text').text();

        var response = addBayesLive(text, state, tweetId, domainId);

        if (response) {
            markAsCommentPositive(btn);
        }
    });

    function markAsCommentNegative(btn) {
        btn.parents('.timeline-entry-inner').find('.timeline-icon').removeClass('bg-info bg-success bg-warning').addClass('bg-danger');
        btn.parents('.timeline-entry-inner').find('.fa-icon').removeClass('fa-minus-circle fa-check-circle').addClass('fa-times-circle');
        btn.parents('.btn-group-action').find('.btn').removeProp('disabled');
        btn.prop('disabled', true);
    }

    // negative button on click event fire mark as negative tweet
    $(document).on('click', '.btn-negative', function () {
        var btn = $(this);
        var state = -1;

        var domainId = $('#selectDomain').val();
        var tweetId = btn.parents('.timeline-entry').data('id');
        var text = btn.parents('.media-body').find('.media-text').text();

        var response = addBayesLive(text, state, tweetId, domainId);

        if (response) {
            markAsCommentNegative(btn);
        }
    });

    function markAsCommentNeutral(btn) {
        btn.parents('.timeline-entry-inner').find('.timeline-icon').removeClass('bg-success bg-danger bg-warning').addClass('bg-info');
        btn.parents('.timeline-entry-inner').find('.fa-icon').removeClass('fa-times-circle fa-check-circle').addClass('fa-minus-circle');
        btn.parents('.btn-group-action').find('.btn').removeProp('disabled');
        btn.prop('disabled', true);
    }

    // neutral button on click event fire mark as neutral tweet
    $(document).on('click', '.btn-neutral', function () {
        var btn = $(this);
        var state = 0;

        var domainId = $('#selectDomain').val();
        var tweetId = btn.parents('.timeline-entry').data('id');
        var text = btn.parents('.media-body').find('.media-text').text();

        var response = addBayesLive(text, state, tweetId, domainId);

        if (response) {
            markAsCommentNeutral(btn);
        }
    });

    $(document).on('click', '.tooltip-show', function () {
        $(this).tooltip()
    });

    $(document).on('click', '.popover-show', function () {
        var btn = $(this);
        var id = btn.data('id');
        $(this).popover({
            html: true,
            content: function () {
                return $('#' + id).html();
            }
        })
    });

    // date picker
    $('.input-group.date').datepicker({
        format: "yyyy-mm-dd",
        language: "tr",
        todayHighlight: true
    });

    // load new message
    $('.load-new').click(function () {
        var loadNew = $(this);
        var loadNewCount = loadNew.find('.tweet-count');
        var timelineEntry = $('.timeline-entry');
        timelineEntry.fadeIn(400);
        loadNew.fadeOut(400);
        loadNewCount.text(0);
    });

    $('.radio-slider-check').uncheckableRadio();

    var parameter = getSearchParameter();

    // get default comments first document load
    getSearchComments(parameter);

    // update every 60 seconds dates
    updateDateCreated();

    // page scroll down load more comments
    infiniteLoad();

    // set slider default values
    setSliderBarDefault();

    getDataLearned();

    $('#selectDomain').change(function (e) {
        getDataLearned();
    });

    $('#selectProcessed').change(function (e) {
        checkProcessedRadio();
    });

    function checkProcessedRadio() {
        var checkProcessed = $('#selectProcessed');
        var processed = [1, 2, 3, 4];

        if (!!~$.inArray(parseInt(checkProcessed.val()), processed)) {
            $('#inputSlider').fadeOut(400);
            $('.radio-slider-check').prop('checked', false);
        } else {
            $('#inputSlider').fadeIn(400);
        }
    }

    checkProcessedRadio();


    //$('#checkAbuse').change(function() {
    //    checkAbuseCheckbox();
    //});
    //
    //function checkAbuseCheckbox() {
    //    var checkAbuse = $('#checkAbuse');
    //
    //    if (checkAbuse.is(':checked')) {
    //        $('#inputSlider').fadeOut(400);
    //        $('.radio-slider-check').prop('checked', false);
    //    }
    //    else {
    //        $('#inputSlider').fadeIn(400);
    //    }
    //}
    //
    //checkAbuseCheckbox();

})();