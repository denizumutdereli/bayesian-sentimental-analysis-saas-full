(function() {

    var pathArray = window.location.pathname.split( '/' );

    var title = getTitle();

    var refreshTimer;

    jQuery.fn.checkboxVal = function(){
        if($(this).is(':checked')) {
            return 0;
        } else {
            return 1;
        }
    }

    jQuery.fn.textboxVal = function(){
//        if($(this).val() != "") {
//            return $(this).val();
//        } else {
//            return "";
//        }
        return $(this).val();
    }

    jQuery.fn.uncheckableRadio = function () {
        $(this).on("click", function (event) {
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

    function getTitle()
    {
        return document.title;
    }

    function updateTitle( title )
    {
        document.title = title;
    }

    function goBack() {
        window.history.back()
    }

    var loadingTimer;

    function showLoading( type, message ) {
        var timeline = $('.timeline-centered');
        var loading = $('.loading-' + type);
        var loadingMessage = loading.find('.loading-message');

        var message = message || loadingMessage.data('message');

        if (message != false) {
            loadingMessage.text(message);
        }

        timeline.css('opacity', 0.5);
        // set time out to loading message shown
        loadingTimer = setTimeout(function() {
            loadingMessage.fadeIn(400);
        }, 5000);
        loading.fadeIn(400);
    }

    function hideLoading( type ) {
        var timeline = $('.timeline-centered');
        var loading = $('.loading-' + type);
        var loadingMessage = loading.find('.loading-message');

        loadingMessage.text('');

        timeline.css('opacity', 1);
        loadingMessage.fadeOut(400);
        loading.fadeOut(400);
        // clear timeout
        window.clearTimeout(loadingTimer);
    }

    function animateProgress() {
        $('.progress').each(function() {
            var progresBar = $(this).find('.progress-bar');
            var progresBarText = progresBar.data('text');

            progresBar.animate({
                width: progresBar.data('percent') + '%'
            }, 100);

            setTimeout(function() {
                progresBar.html(progresBarText);
            }, 1000);
        });
    }

    /**
     * Parameters :
     * - user_id
     * - screen_name
     * - since_id
     * - count (1-200)
     * - include_rts (0|1)
     * - max_id
     * - trim_user (0|1)
     * - exclude_replies (0|1)
     * - contributor_details (0|1)
     * - include_entities (0|1)
     */

    function getSearchParameter() {

        var from = pathArray[3];
        var to = pathArray[4];

        var query = "from:" + from + " @" + to;
        var selectDomain = $("#selectDomain").val();
        var screenName = from;
        var selectCountValue = $('#selectCount').textboxVal();
        var checkRetweetValue = $('#checkRetweet').checkboxVal();
        var checkSlider = $('.radio-slider-check').checkboxVal();

        if (pathArray.length == 5) {
            var parameter = {
                q: query,
                domain_id: selectDomain,
                lang: "tr",
                locale: "tr",
                result_type: "recent",
                count: selectCountValue,
                include_entities: 1,
                screen_name: screenName,
                check_slider: checkSlider
            };

        } else {
            var parameter = {
                domain_id: selectDomain,
                screen_name: screenName,
                count: selectCountValue,
                include_rts: checkRetweetValue
            };
        }

        parameter = $.extend({}, parameter, getSliderBarValues());

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
        // show loading first document load
        showLoading('since');

        btn = btn || false;

        if (btn != false) {
            btnLoading( btn );
        }

        var jqxhr = $.post('/tweets/feeds', parameter)
            .done(function( data ) {

                var tweets = data.statuses;
                var users = data.users;

//                window.tweet_caches.push(data.statuses)
               
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

                var userTemplate = _.template(
                    $('#userTemplate').html()
                );

                // Define our render data (to be put into the 'rc' variable).
                var templateData = {
                    listTitle: 'Kullanıcı Tweetleri (' + users.name +')',
                    listItems: tweets
                };

                // Define our render data (to be put into the 'rc' variable).
                var userTemplateData = {
                    users: users
                };

                // Render the underscore template and inject it after the H1
                // in our current DOM.
                $('#tweets').html( template( templateData ) ).hide().fadeIn(400);

                if (btn != false) {
                    btnLoading( btn, true );
                }
                else {
                    $('#profile').html( userTemplate( userTemplateData ) ).hide().fadeIn(400);
                }
                hideLoading('since');
                getBadword();
                setUserMentionsHref();

                // clear timeout
                window.clearTimeout(refreshTimer);
                // refresh every 60 seconds time line for new tweets
                //refreshTimeLine();
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

        var jqxhr = $.post('/tweets/feeds', parameter )
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
                        $('#' + parameter.since_id ).before( template( templateData ) );

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
                        $('#' + parameter.max_id ).after( template( templateData ) );
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

    // add text to db with its aVal return retVal true or false
    function addBayesLive(mediaText, mediaState, tweetId) {
        tweetId = tweetId || null;

        var retVal = false;

        var tweet = $('#' + tweetId);

        var url = '/traine';
        var parameter = {
            text: mediaText,
            state: mediaState,
            sourceId: tweetId,
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
                '<button class="btn label label-danger badword whitelist">Küfür değil</button>',
                '</div>'].join(' ')
        });
        $("[rel=badword]").unbind('shown.bs.popover');
        $("[rel=suspect]").unbind('shown.bs.popover');
        $('[rel=badword]').on('shown.bs.popover', function () {

            $(".badword").click(function(){
                var self = $(this);
                _id = $(this).parents('.popover').attr('id');
                word = $("a[aria-describedby="+_id+"]").text();
                if (word != undefined || word != null) {
                    url = '/abuse/remove';
                    $.post(url, {
                        'word': word
                    }).done(function(){
                        $("a[aria-describedby="+_id+"]").attr('rel', 'suspect').removeClass('strong').removeClass('text-danger').addClass('suspect');
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
                var word = $("a[aria-describedby="+_id+"]").text();
                var text = $(this).parents('.media-text').text();
                var list = $(this).hasClass('whitelist') ? 0 : 1;
                if (word != undefined || word != null) {
                    var url = '/abuse/add';
                    $.post(url, {
                        'word': word,
                        'list': list,
                        'source_id': tweet_id,
                        'source':  'twitter'
                    }).done(function(){
                        $("a[aria-describedby="+_id+"]").attr('rel', 'badword').addClass('strong').addClass('text-danger').removeClass('suspect');
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

    var sliderPositive = $('#sliderPositive').slider()
        .on('slide', function() {
            var slider = $(this);
            var sliderPositiveValue = sliderPositive.getValue();
            var sliderNegativeValue = sliderNegative.getValue();
            var sliderNeutralValue = sliderNeutral.getValue();
            var sliderTotalValue = getSliderTotal();

//            setSliderPercent($('#sliderNegative'), 100);
//            setSliderPercent($('#sliderNeutral'), 100);
//            sliderNegative.setValue(100);
//            sliderNeutral.setValue(100);

            $('#sliderPositiveCheck').prop('checked', true);

            setSliderPercent(slider, sliderPositiveValue);

//            if ( sliderTotalValue <= 100 ) {
//                setSliderPercent(slider, sliderPositiveValue);
//            } else {
//                slider.slider('setValue', 100 - (sliderNegativeValue + sliderNeutralValue) );
//            }
        })
        .data('slider');

    var sliderNegative = $('#sliderNegative').slider()
        .on('slide', function() {
            var slider = $(this);
            var sliderPositiveValue = sliderPositive.getValue();
            var sliderNegativeValue = sliderNegative.getValue();
            var sliderNeutralValue = sliderNeutral.getValue();
            var sliderTotalValue = getSliderTotal();

//            setSliderPercent($('#sliderPositive'), 100);
//            setSliderPercent($('#sliderNeutral'), 100);
//            sliderPositive.setValue(100);
//            sliderNeutral.setValue(100);

            $('#sliderNegativeCheck').prop('checked', true);

            setSliderPercent(slider, sliderNegativeValue);

//            if ( sliderTotalValue <= 100 ) {
//                setSliderPercent(slider, sliderNegativeValue);
//            } else {
//                slider.slider('setValue', 100 - (sliderPositiveValue + sliderNeutralValue) );
//            }
        })
        .data('slider');

    var sliderNeutral = $('#sliderNeutral').slider()
        .on('slide', function() {
            var slider = $(this);
            var sliderPositiveValue = sliderPositive.getValue();
            var sliderNegativeValue = sliderNegative.getValue();
            var sliderNeutralValue = sliderNeutral.getValue();
            var sliderTotalValue = getSliderTotal();

//            setSliderPercent($('#sliderPositive'), 100);
//            setSliderPercent($('#sliderNegative'), 100);
//            sliderPositive.setValue(100);
//            sliderNegative.setValue(100);

            $('#sliderNeutralCheck').prop('checked', true);

            setSliderPercent(slider, sliderNeutralValue);

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

        hideLoadNew();
        showLoading('since');
        getSearchTweets( parameter, btn );
        event.preventDefault();
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

        console.log(parameter.since_id)

        if (parameter.since_id != null) {
            showLoading('since');
            updateSearchTweets( parameter, btn );
        }
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
        var tweetId = $(this).parents('.timeline-entry').data('id');
        var text = $(this).parents('.media-body').find('.media-text').text();
        var response = addBayesLive(text, '1', tweetId);

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
        var tweetId = $(this).parents('.timeline-entry').data('id');
        var text = $(this).parents('.media-body').find('.media-text').text();
        var response = addBayesLive(text, '-1', tweetId);

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
        var tweetId = $(this).parents('.timeline-entry').data('id');
        var text = $(this).parents('.media-body').find('.media-text').text();
        var response = addBayesLive(text, '0', tweetId);

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
    });

    $('.radio-slider-check').uncheckableRadio();

    $('#btnBack').click(function() {
        goBack();
    });

    var parameter = getSearchParameter();

    // get default tweets first document load
    getSearchTweets( parameter );

    // update every 60 seconds dates
    updateDateCreated();

    // page scroll down load more tweets
    infiniteLoad();

    // set slider default values
    setSliderBarDefault();

    // animate bayes learn bar
    animateProgress();

})();