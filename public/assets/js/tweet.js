(function() {

    var title = getTitle();
    var refreshTimer;

    jQuery.fn.checkboxVal = function(){
        if($(this).is(':checked')) {
            return 1;
        } else {
            return 0;
        }
    }

    jQuery.fn.textboxVal = function(){
//        if($(this).val() != "") {
//            return $(this).val();
//        } else {
//            return "";
//        }
        return $.trim($(this).val());
    }

    jQuery.fn.uncheckableRadio = function () {
        $(this).on("click", function () {
            var this_input = $(this);
            if (this_input.attr('checked1') == '11') {
                this_input.attr('checked1', '11')
            }
            else {
                this_input.attr('checked1', '22')
            }
            $('.radio-button').prop('checked', false);
            if (this_input.attr('checked1') == '11') {
                this_input.prop('checked', false);
                this_input.attr('checked1', '22')
            }
            else {
                this_input.prop('checked', true);
                this_input.attr('checked1', '11')
            }
        });
    }

    function issetVal( val ){
        if(typeof(val) === "undefined")
            //do this
            return false;
        else
            //do this
            return true;
    }

    function getTitle()
    {   // update the title tag with the value from the .get call
        return document.title;
    }

    function updateTitle( title )
    {   // update the title tag with the value from the .get call
        document.title = title;
    }

    function getSearchParameter() {
        var textSearchValue = $('#textSearch').textboxVal();
        var selectDomain = $('#selectDomain').val();
        var selectCountValue = $('#selectCount').textboxVal();
        var checkRetweetValue = $('#checkRetweet').checkboxVal();
        var textUntilValue = $('#textUntil').textboxVal();
        var textFromValue = $('#textFrom').textboxVal();
        var textExtractValue = $('#textExtract').textboxVal();
        var selectResultTypeValue = $('#selectResultType').textboxVal();
        var checkSlider = $('.radio-slider-check').checkboxVal();

        if( textSearchValue == "" ) {
            textSearchValue = "@Digiturk";
            $('#textSearch').val(textSearchValue);
        }

        var parameter = {
            q: textSearchValue,
            domain_id: selectDomain,
            count: selectCountValue,
            retweet: checkRetweetValue,
            until: textUntilValue,
            from: textFromValue,
            extract: textExtractValue,
            result_type: selectResultTypeValue,
            check_slider: checkSlider
        };

        if (checkSlider == 1) {
            parameter = $.extend({}, parameter, getSliderBarValues());
        }

        //console.log(parameter);

        return parameter;
    }

    function setUserMentionsHref() {
        $('.user-mentions').each(function() {
            var href = $(this).attr('href');
            var to = $('#textSearch').val().replace(new RegExp(/@/), "");
            if(href.indexOf(to) == -1){
                var newHref = href + '/' + to;
                $(this).attr('href', newHref);
            }
        });
    }

    function getSearchTweets( parameter, btn ) {
        // reset progress bar
        resetProgressBar();

        // show loading first document load
        showLoading('since');

        btn = btn || false;

        if (btn != false) {
            btnLoading( btn );
        }

        var jqxhr = $.post('tweets/search', parameter)
            .done(function( data ) {

                var tweets = data.statuses;

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
                    $('#tweetTemplate').html()
                );

                var tableTemplate = _.template(
                    $('#tweetTableTemplate').html()
                );

                // Define our render data (to be put into the 'rc' variable).
                var templateData = {
                    listTitle: 'Akış',
                    listItems: tweets
                };

                var tableTemplateData = {
                    listTitle: 'Akış Tablo',
                    listItems: tweets
                };


                // Render the underscore template and inject it after the H1
                // in our current DOM.
                $('#tweets').html( template( templateData ) ).hide().fadeIn(400);

                $('#tweetTableBody').html( tableTemplate( tableTemplateData ) );

                // animate data learned progress bar
                animateProgress(data.dataLearned);

                if (btn != false) {
                    btnLoading( btn, true );
                }
                // get tags for filter
                getFilterTags();
                hideLoading('since');
                getBadword();
                setUserMentionsHref();

                // clear timeout
                window.clearTimeout(refreshTimer);
                // refresh every 60 seconds time line for new tweets
                refreshTimeLine();
            })
            .fail(function() {
                console.log('error');
            });

        // Set another completion function for the request above
        jqxhr.always(function() {
            if (btn != false) {
                btnLoading( btn, true );
            }
            hideLoading('since');
        });
    }

    function updateSearchTweets( parameter, btn ) {
        btn = btn || false;

        if (btn != false) {
            btnLoading( btn );
        }

        var jqxhr = $.post('/tweets/search', parameter )
            .done(function( data ) {
                var tweets = data.statuses; // an array includes object

                var tweetCount = $.map(tweets, function(n, i) { return i; }).length;

                if ( tweetCount > 0 ) {
                    if ( parameter.max ) {
                        tweets.shift(); // removing its last element
                    }
                }
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
                    $('#tweetTemplate').html()
                );

                var tableTemplate = _.template(
                    $('#tweetTableTemplate').html()
                );

                // Render the underscore template and inject it after the H1
                // in our current DOM.
                if (tweetCount > 0) {
                    if ( parameter.since ) {

                        // Define our render data (to be put into the 'rc' variable).
                        var templateData = {
                            listTitle: 'Akış',
                            listItems: tweets,
                            listItemHide: true
                        };

                        var tableTemplateData = {
                            listTitle: 'Akış Tablo',
                            listItems: tweets
                        };

                        $('#' + parameter.since_id ).before( template( templateData ) );

                        $('#tweetTableBody').prepend( tableTemplate( tableTemplateData ) );

                        var loadNew = $('.load-new');
                        var loadNewCount = loadNew.find('.tweet-count');
                        var loadNewCountText = loadNew.find('.tweet-count').text();
                        loadNewCountText = parseInt(tweetCount) + parseInt(loadNewCountText);
                        loadNewCount.text(loadNewCountText);
                        loadNew.fadeIn(400);
                        // update document title
                        updateTitle('Akış (' + loadNewCountText + ' yeni tweet var)');
                    } else if ( parameter.max ) {

                        // Define our render data (to be put into the 'rc' variable).
                        var templateData = {
                            listTitle: 'Akış',
                            listItems: tweets,
                            listItemHide: false
                        };

                        var tableTemplateData = {
                            listTitle: 'Akış Tablo',
                            listItems: tweets
                        };

                        $('#' + parameter.max_id ).after( template( templateData ) );

                        $('#tweetTableBody').append( tableTemplate( tableTemplateData ) );
                    }
                }

                if (btn != false) {
                    btnLoading( btn, true );
                }
                hideLoading('since');
                hideLoading('max');
                getBadword();
                setUserMentionsHref();
            })
            .fail(function() {
                console.log('error');
            });

        // Set another completion function for the request above
        jqxhr.always(function() {
            if (btn != false) {
                btnLoading( btn, true );
            }
            hideLoading('since');
            hideLoading('max');
        });
    }

    function getFilterTags() {
        var jqxhr = $.post('/filter/tags')
            .done(function( data ) {
                var textSearchValue = $('#textSearch').val();
                var tagList = $('#tagFilterList');
                tagList.empty();

                $.each(data, function( index, tag ) {
                    var div = $('<div/>')
                        .addClass('radio')
                        .appendTo(tagList);
                    var label = $('<label/>')
                        .appendTo(div);
                    if (textSearchValue == tag) {
                        var input = $('<input/>')
                            .addClass('radio-tag')
                            .attr('name', 'tag')
                            .val(tag)
                            .attr('type', 'radio')
                            .attr('checked', true)
                            .appendTo(label);
                    } else {
                        var input = $('<input/>')
                            .addClass('radio-tag')
                            .attr('name', 'tag')
                            .val(tag)
                            .attr('type', 'radio')
                            .appendTo(label);
                    }

                    var text = ' ' + tag;

                    input.after(text);
                });
            })
            .fail(function() {
                console.log('error');
            });

        // Set another completion function for the request above
        jqxhr.always(function() {
            // do something
        });
    }

    // add text to db with its aVal return retVal true or false
    function addBayesLive(mediaText, mediaState, tweetId, domainId) {
        tweetId = tweetId || null;
        domainId = domainId || 1;

        var retVal = false;

        var tweet = $('#' + tweetId);

        var url = '/traine';
        var parameter = {
            text: mediaText,
            state: mediaState,
            source_id: tweetId,
            domain_id: domainId,
            source: 'twitter'
        };

        var request = $.ajax({
            type: "POST",
            async: false,
            url: url,
            data: parameter,
            dataType: "json"
        });

        request.done(function( data ) {
            if ( data.response == 1 ) {
                tweet.find('.positive-percent').text(data.positive);
                tweet.find('.negative-percent').text(data.negative);
                tweet.find('.neutral-percent').text(data.neutral);
                retVal = true;
            } else if (data.response == -1) {
                var alertData = { type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Daha önceden kayıtlı <a href="/sentimental/'+data.sentimental+'/edit" target="_blank">burayı tıklayarak</a> düzenleyebilirsiniz' }
                showAlertModal( alertData );
                retVal = false;
            } else {
                var alertData = { type: 'modal-sm', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Bir hata oluştu lütfen daha sonra tekrar deneyiniz!' }
                showAlertModal( alertData );
                retVal = false;
            }
        });
        request.fail(function( jqXHR, textStatus ) {
            console.log( "Request failed: " + textStatus );
        });

        return retVal;
    }

    // update date created tweets every 1 minute
    function updateDateCreated() {
        setInterval(function() {
            //        $.each( $('.created'), function() {
            $('.created').each(function() {
                var createdAt = $(this).attr('data-created');
                $(this).text($.format.prettyDate(createdAt));
            });
        }, 30000);
    }

    function btnLoading( btn, reset ) {
        btn.button('loading');

        reset = reset || false;

        if (reset) {
            btn.button('reset');
        }
    }

    function checkTextSearchValue( value ) {
        if (value == "" || value == 0 || value.length == "") {
            var alertData = { type: 'modal-sm', title: '<i class="fa fa-minus-circle"></i> Hata', text: 'Lütfen Arama kriteri olarak bir <strong>"kelime"</strong> giriniz.' }
            showAlertModal( alertData );
            return false;
        }
    }

    // update every 60 second (60000 ms)
    function refreshTimeLine() {
        var time = 60000*5; // 5 dk
        refreshTimer = setInterval(function() {

            var sinceId = $('.timeline-entry:first-child').data('id');

            var parameter = getSearchParameter();

            var sinceParameter = {
                since_id: sinceId,
                since: true,
                max: false
            };

            parameter = $.extend({}, parameter, sinceParameter);

            if (checkTextSearchValue( parameter.q ) == false || parameter.since_id == null) {
                return false;
            }

            if (parameter.since_id != null) {
                updateSearchTweets( parameter );
            }
        }, time);
    }

    function infiniteLoad() {
        var previousScroll = 0; // detect previos scroll position
        var loading = false; //to prevents multipal ajax loads
        var offset = 100; //scroll bottom offset margin

        $(window).scroll(function() { //detect page scroll

            if ( ($(window).scrollTop() + $(window).height()) > ($(document).height() - offset) ) { //user scrolled to bottom of the page?
//            if( $(window).scrollTop() >= $(document).height() - $(window).height() - offset ) {

                var currentScroll = $(window).scrollTop(); //current scroll position

                if ( currentScroll > previousScroll ) { // check if window scroll down

                    if ( loading == false ) { //there's more data to load

                        loading = true; //prevent further ajax loading

                        var maxId = $('.timeline-entry:last-child').data('id');

                        if (maxId != null) {

                            var parameter = getSearchParameter();

                            var maxParameter = {
                                max_id: maxId,
                                since: false,
                                max: true
                            };

                            parameter = $.extend({}, parameter, maxParameter);

                            if (checkTextSearchValue( parameter.q ) == false) {
                                return false;
                            }

                            showLoading('max');
                            updateSearchTweets( parameter );

                            setTimeout(function() {
                                loading = false;

    //                            $('html, body').animate({
    //                                scrollTop: $(window).scrollTop() + 200
    //                            }, 400);
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
            html : true,
            content:  ['<div id="mybadwordpopover">',
                '<button class="btn label label-success badword">Küfür değil</button>',
                '</div>'].join(' ')
        });

        $("[rel=suspect]").popover({
            html : true,
            content:  ['<div id="mysuspectpopover">',
                '<button class="btn label label-success badword">Küfür</button>',
                '</div>'].join(' ')
        });
        $("[rel=badword]").unbind('shown.bs.popover');
        $("[rel=suspect]").unbind('shown.bs.popover');
        $('[rel=badword]').on('shown.bs.popover', function () {

            $(".badword").click(function(){
                var self = $(this);
                _id = $(this).parents('.popover').attr('id');
                word = $("span[aria-describedby="+_id+"]").text();
                if (word != undefined || word != null) {
                    url = '/abuse/remove';
                    $.post(url, {
                        'word': word
                    }).done(function(){
                        $("span[aria-describedby="+_id+"]").attr('rel', 'suspect').removeClass('strong').removeClass('text-danger').addClass('suspect');
                        $('[rel=suspect]').popover('destroy');
                        $('[rel=badword]').popover('destroy');
                        getBadword();
                        bc = $(self).parents('article').find('[rel=badword]').length;
                        if(bc == 0){
                            $(self).parents('article').find('button').attr('disabled', false);
                        }
                        markAsTweetNegative( $(this) );
                    });
                }
            });
        });

        $('[rel=suspect]').on('shown.bs.popover', function () {
            $('[rel=suspect]').not(this).popover('hide');

            $(".badword").click(function(){
                var self = $(this);
                var _id = $(this).parents('.popover').attr('id');
                var tweet_id = $(this).parents('article').attr('id');
                var word = $("span[aria-describedby="+_id+"]").text();
                var text = $(this).parents('.media-text').text();
                var list = $(this).hasClass('whitelist') ? 0 : 1;
                if (word != undefined || word != null) {
                    var url = '/abuse/add';
                    $.post(url, {
                        'word': word,
                        'list': list,
                        'source_id': tweet_id,
                        'source':  'twitter',
                        'domain_id': $("#selectDomain").val()
                    }).done(function(){
                        $("span[aria-describedby="+_id+"]").attr('rel', 'badword').addClass('strong').addClass('text-danger').removeClass('suspect');
                        $('[rel=suspect]').popover('destroy');
                        $('[rel=badword]').popover('destroy');
                        getBadword();
                        markAsTweetNegative( self );
                        self.parents('article').find('button').attr('disabled', true);
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
        updateTitle(title);
    }

    var sliderDefaultValue = '0,100';

    function getSliderBarValues() {
        // get slider values
        var sliderPositiveValue = [0,100];
        var sliderNegativeValue = [0,100];
        var sliderNeutralValue = [0,100];

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
    function setSliderPercent( elem, elemVal ) {
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

    function getSliderTotal() {
        var sliderPositiveValue = sliderPositive.getValue();
        var sliderNegativeValue = sliderNegative.getValue();
        var sliderNeutralValue = sliderNeutral.getValue();
        var sliderTotalValue = sliderPositiveValue + sliderNegativeValue + sliderNeutralValue;
        return sliderTotalValue;
    }

    var sliderPositive = $('#sliderPositive').slider({
        value: 0
    })
        .on('slide', function() {
            var slider = $(this);
            var sliderPositiveValue = sliderPositive.getValue();
            var sliderNegativeValue = sliderNegative.getValue();
            var sliderNeutralValue = sliderNeutral.getValue();
            var sliderTotalValue = getSliderTotal();

            //updateInputRadioCountValue();

//            setSliderPercent($('#sliderNegative'), sliderDefaultValue);
//            setSliderPercent($('#sliderNeutral'), sliderDefaultValue);
//            sliderNegative.setValue(sliderDefaultValue);
//            sliderNeutral.setValue(sliderDefaultValue);
//
            setSliderPercent(slider, sliderPositiveValue);

            $('#sliderPositiveCheck').prop('checked', true);

//            if ( sliderTotalValue <= 100 ) {
//                setSliderPercent(slider, sliderPositiveValue);
//            } else {
//                slider.slider('setValue', 100 - (sliderNegativeValue + sliderNeutralValue) );
//            }
        })
        .data('slider');

    var sliderNegative = $('#sliderNegative').slider({
        value: 0
    })
        .on('slide', function() {
            var slider = $(this);
            var sliderPositiveValue = sliderPositive.getValue();
            var sliderNegativeValue = sliderNegative.getValue();
            var sliderNeutralValue = sliderNeutral.getValue();
            var sliderTotalValue = getSliderTotal();

            //updateInputRadioCountValue();

//            setSliderPercent($('#sliderPositive'), sliderDefaultValue);
//            setSliderPercent($('#sliderNeutral'), sliderDefaultValue);
//            sliderPositive.setValue(sliderDefaultValue);
//            sliderNeutral.setValue(sliderDefaultValue);
//
            setSliderPercent(slider, sliderNegativeValue);

            $('#sliderNegativeCheck').prop('checked', true);

//            if ( sliderTotalValue <= 100 ) {
//                setSliderPercent(slider, sliderNegativeValue);
//            } else {
//                slider.slider('setValue', 100 - (sliderPositiveValue + sliderNeutralValue) );
//            }
        })
        .data('slider');

    var sliderNeutral = $('#sliderNeutral').slider({
        value: 0
    })
        .on('slide', function() {
            var slider = $(this);
            var sliderPositiveValue = sliderPositive.getValue();
            var sliderNegativeValue = sliderNegative.getValue();
            var sliderNeutralValue = sliderNeutral.getValue();
            var sliderTotalValue = getSliderTotal();

            //updateInputRadioCountValue();

//            setSliderPercent($('#sliderPositive'), sliderDefaultValue);
//            setSliderPercent($('#sliderNegative'), sliderDefaultValue);
//            sliderPositive.setValue(sliderDefaultValue);
//            sliderNegative.setValue(sliderDefaultValue);
//
            setSliderPercent(slider, sliderNeutralValue);

            $('#sliderNeutralCheck').prop('checked', true);

//            console.log(sliderNeutral.getValue());

//            if ( sliderTotalValue <= 100 ) {
//                setSliderPercent(slider, sliderNeutralValue);
//            } else {
//                slider.slider('setValue', 100 - (sliderPositiveValue + sliderNegativeValue) );
//            }
        })
        .data('slider');

    // set sliders width 100%
    $('.slider').width('100%');

    // search form submit event fire get tweets
    $('#formSearch').submit(function( event ) {
        
        window.tweet_caches = []
        
        var btn = $(this).find('.btn-loading');

        var parameter = getSearchParameter();

        if (checkTextSearchValue( parameter.q ) == false) {
            return false;
        }

        hideLoadNew();
        showLoading('since');
        getSearchTweets( parameter, btn );
        scrollToTop();
        event.preventDefault();
    });

    // tag filter button click event fire and get search tweets for parameter and tag
    $('#btnTag').click(function() {

        var btn = $(this);

        var textSearchElem = $('#textSearch');
        var radioTagValue = $('.radio-tag:checked').val();

        // change search text value with radio value
        textSearchElem.val( radioTagValue );

        var parameter = getSearchParameter();

        if (checkTextSearchValue( parameter.q ) == false) {
            return false;
        }

        hideLoadNew();
        showLoading('since');
        getSearchTweets( parameter, btn );
    });

    // more button click event fire and get more tweet
    $('#btnMore').click(function() {

        var maxId = $('.timeline-entry:last-child').data('id');

        var parameter = getSearchParameter();

        var maxParameter = {
            max_id: maxId,
            since: false,
            max: true
        };

        parameter = $.extend({}, parameter, maxParameter);

        if (checkTextSearchValue( parameter.q ) == false || parameter.max_id == null) {
            return false;
        }

        hideLoadNew();
        showLoading('max');
        updateSearchTweets( parameter );
    });

    $('#btnRefresh').click(function() {

        var btn = $(this);

        var sinceId = $('.timeline-entry:first-child').data('id');

        var parameter = getSearchParameter();

        var sinceParameter = {
            since_id: sinceId,
            since: true,
            max: false
        };

        parameter = $.extend({}, parameter, sinceParameter);

        if (checkTextSearchValue( parameter.q ) == false || parameter.since_id == null) {
            return false;
        }

        showLoading('since');
        updateSearchTweets( parameter, btn );
    });

    function markAsTweetPositive( btn ) {
        btn.parents('.timeline-entry-inner').find('.timeline-icon').removeClass('bg-info bg-danger bg-warning').addClass('bg-success');
        btn.parents('.timeline-entry-inner').find('.fa-icon').removeClass('fa-minus-circle fa-times-circle').addClass('fa-check-circle');
        btn.parents('.btn-group-action').find('.btn').removeProp('disabled');
//            $(this).prop('disabled', true);
    }

    // positive button on click event fire mark as positive tweet
    $( document ).on('click', '.btn-positive', function() {
        var btn = $(this);
        var state = 1;

        var domainId = $('#selectDomain').val();
        var tweetId = btn.parents('.timeline-entry').data('id');
        var text = btn.parents('.media-body').find('.media-text').text();

        var response = addBayesLive(text, state, tweetId, domainId);

        if ( response ) {
            markAsTweetPositive( btn );
        }
    });

    function markAsTweetNegative( btn ) {
        btn.parents('.timeline-entry-inner').find('.timeline-icon').removeClass('bg-info bg-success bg-warning').addClass('bg-danger');
        btn.parents('.timeline-entry-inner').find('.fa-icon').removeClass('fa-minus-circle fa-check-circle').addClass('fa-times-circle');
        btn.parents('.btn-group-action').find('.btn').removeProp('disabled');
//            $(this).prop('disabled', true);
    }

    // negative button on click event fire mark as negative tweet
    $( document ).on('click', '.btn-negative', function() {
        var btn = $(this);
        var state = -1;

        var domainId = $('#selectDomain').val();
        var tweetId = btn.parents('.timeline-entry').data('id');
        var text = btn.parents('.media-body').find('.media-text').text();

        var response = addBayesLive(text, state, tweetId, domainId);

        if ( response ) {
            markAsTweetNegative( btn );
        }
    });

    function markAsTweetNeutral( btn ) {
        btn.parents('.timeline-entry-inner').find('.timeline-icon').removeClass('bg-success bg-danger bg-warning').addClass('bg-info');
        btn.parents('.timeline-entry-inner').find('.fa-icon').removeClass('fa-times-circle fa-check-circle').addClass('fa-minus-circle');
        btn.parents('.btn-group-action').find('.btn').removeProp('disabled');
//            $(this).prop('disabled', true);
    }

    // neutral button on click event fire mark as neutral tweet
    $( document ).on('click', '.btn-neutral', function() {
        var btn = $(this);
        var state = 0;

        var domainId = $('#selectDomain').val();
        var tweetId = btn.parents('.timeline-entry').data('id');
        var text = btn.parents('.media-body').find('.media-text').text();

        var response = addBayesLive(text, state, tweetId, domainId);

        if ( response ) {
            markAsTweetNeutral( btn );
        }
    });

    $( document ).on('click', '.tooltip-show', function() {
        $(this).tooltip()
    });

    $( document ).on('click', '.popover-show', function() {
        var btn = $(this);
        var id = btn.data('id');
        $(this).popover({
            html: true,
            content: function() {
                return $('#' + id ).html();
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
    $('.load-new').click(function() {
        var loadNew = $(this);
        var loadNewCount = loadNew.find('.tweet-count');
        var timelineEntry = $('.timeline-entry');
        timelineEntry.fadeIn(400);
        loadNew.fadeOut(400);
        loadNewCount.text(0);
        updateTitle(title);
    });

    $('.radio-slider-check').uncheckableRadio();

    function updateInputRadioCountValue() {
        $('#selectCount').val(100);
    }

    var parameter = getSearchParameter();

    if (parameter.q == "") {
        $('.user-mentions').hide();
    } else {
        $('.user-mentions').show();
    }

    // get default tweets first document load
    getSearchTweets( parameter );

    // update every 60 seconds dates
    updateDateCreated();

    // page scroll down load more tweets
    infiniteLoad();

    // set slider default values
    setSliderBarDefault();

    // animate bayes learn bar
    //animateProgress();

})();