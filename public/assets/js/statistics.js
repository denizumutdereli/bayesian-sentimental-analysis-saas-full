(function() {

    var loadingTimer;

    jQuery.fn.checkboxVal = function(){
        if($(this).is(':checked')) {
            return 1;
        } else {
            return 0;
        }
    }

    jQuery.fn.textboxVal = function(){
        if($(this).val() != "") {
            return $(this).val();
        } else {
            return 0;
        }
    }

    function showLoadingAnalysis( type, message ) {
        var box = $('.panel-box');
        var loading = $('.loading-' + type);
        var loadingMessage = loading.find('.loading-message');

        var message = message || loadingMessage.data('message');

        box.css('opacity', 0.5);
        // set time out to loading message shown
        loadingTimer = setTimeout(function() {
            loadingMessage.fadeOut(400);
            setTimeout(function() {
                if (message != false) {
                    loadingMessage.text(message);
                }
                loadingMessage.delay(400).fadeIn(400);
            }, 400);
        }, 5000);
        loading.fadeIn(400);
    }

    function hideLoadingAnalysis( type ) {
        var box = $('.panel-box');
        var loading = $('.loading-' + type);
        var loadingMessage = loading.find('.loading-message');

        loadingMessage.text('Yükleniyor...');

        box.css('opacity', 1);
        loading.fadeOut(400);
        // clear timeout
        window.clearTimeout(loadingTimer);
    }

    function getSearchParameter() {
//        var textSearchValue = $('#textSearch').textboxVal();
//        var textUntilValue = $('#textUntil').textboxVal();
        var selectDomain = $("#selectDomain").val();
//        var selectPublished = $("#selectPublished").val();
//        var selectProcessed = $("#selectProcessed").val();
//        var checkAbuseValue = $('#checkAbuse').checkboxVal();

        var parameter = {
//            search: textSearchValue,
//            until: textUntilValue,
            domain_id: selectDomain,
//            is_published: selectPublished,
//            is_processed: selectProcessed,
//            include_abuse: checkAbuseValue
        };

        return parameter;
    }

    function hideCount() {
        $('.count').hide();
    }

    function showCount() {
        $('.count').fadeIn(400);
    }

    function getDomainCheckbox() {
        return $('#selectDomain').val();
    }

    function btnLoading( btn, reset ) {
        btn.button('loading');

        reset = reset || false;

        if (reset) {
            btn.button('reset');
        }
    }

    function getDataLearned() {
        var parameter = {
            domain_id:  getDomainCheckbox()
        }

        var jqxhr = $.post('/learned', parameter)
            .done(function( data ) {
                if (data.response == 1) {
                    animateProgress(data.dataLearned);
                } else {
                    var alertData = {
                        type: 'modal-md',
                        title: '<i class="fa fa-minus-circle"></i> Uyarı',
                        text: 'İşlem sırasında bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.'
                    }
                    showAlertModal( alertData );
                }
            })
            .fail(function() {
                console.log('error');
            });

        // Set another completion function for the request above
        jqxhr.always(function() {
            //
        });
    }

    function getChart(parameter) {
        var containerTotal = $('#chartStatisticTotal');
        var containerDaily = $('#chartStatisticDaily');

        containerTotal.html('');
        containerDaily.html('');

        var jqxhr = $.post('/statistics/chart', parameter)
            .done(function (response) {
                
                if(response.msg === 0) {
                   var alertData = {
                        type: 'modal-md',
                        title: '<i class="fa fa-minus-circle"></i> Uyarı',
                        text: 'Sadece size ait domain bilgilerini görebilirsiniz.'
                    }
                    showAlertModal( alertData );
                    return false;
                }
                
                var data = response.data;
                var learningLimit = response.learning_limit;

                if (data.total.length > 0) {
                    containerTotal.height(250);

                    var line = new Morris.Line({
                        // ID of the element in which to draw the chart.
                        element: 'chartStatisticTotal',
                        // Chart data records -- each entry in this array corresponds to a point on
                        // the chart.
                        data: data.total,
                        // The name of the data record attribute that contains x-values.
                        xkey: 'date',
                        // A list of names of data record attributes that contain y-values.
                        ykeys: ['total', 'positive', 'negative', 'neutral'],
                        // Labels for the ykeys -- will be displayed when you hover over the
                        // chart.
                        //labels: ['Toplam', 'Pozitif', 'Negatif', 'Nötr'],
                        labels: data.labels,
                        hideHover: 'auto',
                        stacked: true,
                        resize: true,
                        redraw: true,
                        goals: [0, learningLimit],
                        goalLineColors: ['#ccc', '#d9230f'],
                        lineColors: ['#666', '#469408', '#d9831f', '#029acf']
                    });
                }
                else {
                    containerTotal.height(30);
                    containerTotal.html('<span class="text-danger"><em>Henüz öğretilmiş (tonlanmış) data yok.</em></span>');
                }

                if (data.daily.length > 0) {
                    containerDaily.height(250);

                    var line = new Morris.Line({
                        // ID of the element in which to draw the chart.
                        element: 'chartStatisticDaily',
                        // Chart data records -- each entry in this array corresponds to a point on
                        // the chart.
                        data: data.daily,
                        // The name of the data record attribute that contains x-values.
                        xkey: 'date',
                        // A list of names of data record attributes that contain y-values.
                        ykeys: ['total', 'positive', 'negative', 'neutral'],
                        // Labels for the ykeys -- will be displayed when you hover over the
                        // chart.
                        //labels: ['Toplam', 'Pozitif', 'Negatif', 'Nötr'],
                        labels: data.labels,
                        hideHover: 'auto',
                        stacked: true,
                        resize: true,
                        redraw: true,
                        lineColors: ['#666', '#469408', '#d9831f', '#029acf']
                    });
                }
                else {
                    containerDaily.height(30);
                    containerDaily.html('<span class="text-danger"><em>Henüz öğretilmiş (tonlanmış) data yok.</em></span>');
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

    function getStatistics(parameter, btn) {
        var btn = btn || false;

        hideCount();

        showLoadingAnalysis( 'default' );

        var jqxhr = $.post('/statistics/analysis', parameter)
            .done(function (response) {
                var statistics;
                var broadcast;

                statistics = response.data; // an array includes objec
                broadcast = response.data.broadcast.data; // an array includes objec

                hideLoadingAnalysis('default');

                var processedTotal = parseInt(statistics.processed.total);

                var processedPositiveTotal = parseInt(statistics.processed.positive);
                var processedPositivePercent = 0;
                var processedNegativeTotal = parseInt(statistics.processed.negative);
                var processedNegativePercent = 0;
                var processedNeutralTotal = parseInt(statistics.processed.neutral);
                var processedNeutralPercent = 0;

                if (processedPositiveTotal > 0) {
                    processedPositivePercent = ((processedPositiveTotal / processedTotal) * 100).toFixed(2);
                }
                if (processedNegativeTotal > 0) {
                    processedNegativePercent = ((processedNegativeTotal / processedTotal) * 100).toFixed(2);
                }
                if (processedNeutralTotal > 0) {
                    processedNeutralPercent = ((processedNeutralTotal / processedTotal) * 100).toFixed(2);
                }

                var publishableTotal = parseInt(broadcast.publishable.total);
                var publishablePercent = 0;
                var unpublishableTotal = parseInt(broadcast.unpublishable.total);
                var unpublishablePercent = 0;
                var unpublishableAbuseTotal = parseInt(broadcast.unpublishable_abuse.total);
                var unpublishableAbusePercent = 0;

                var boardcastLimit = parseInt(broadcast.limit); // boardcast

                if (publishableTotal > 0) {
                    publishablePercent = ((publishableTotal / boardcastLimit) * 100).toFixed(2);
                }
                if (unpublishableTotal > 0) {
                    unpublishablePercent = ((unpublishableTotal / boardcastLimit) * 100).toFixed(2);
                }
                if (unpublishableAbuseTotal > 0) {
                    unpublishableAbusePercent = ((unpublishableAbuseTotal / boardcastLimit) * 100).toFixed(2);
                }

                $('.count-total').text(statistics.total);
                $('.count-processed').text(statistics.processed.total);
                $('.count-unprocessed').text(statistics.unprocessed.total);
                $('.count-abuse').text(statistics.abuse.total);

                $('.count-processed-positive')
                    .html(processedPositiveTotal + ' <small>%' + processedPositivePercent + '</small>');
                $('.count-processed-negative')
                    .html(processedNegativeTotal + ' <small>%' + processedNegativePercent + '</small>');
                $('.count-processed-neutral')
                    .html(processedNeutralTotal + ' <small>%' + processedNeutralPercent + '</small>');

                //$('.count-publishable-total')
                //    .html(publishableTotal + ' <small>%' + publishablePercent + '</small>');
                //$('.count-unpublishable-total')
                //    .html(unpublishableTotal + ' <small>%' + unpublishablePercent + '</small>');
                //$('.count-unpublishable-abuse-total')
                //    .html(unpublishableAbuseTotal + ' <small>%' + unpublishableAbusePercent + '</small>');

                //$('.count-publishable-total')
                //    .html(publishableTotal + ' <small>/' + boardcastLimit + '</small>');
                //$('.count-unpublishable-total')
                //    .html(unpublishableTotal + ' <small>/' + boardcastLimit + '</small>');
                //$('.count-unpublishable-abuse-total')
                //    .html(unpublishableAbuseTotal + ' <small>/' + boardcastLimit + '</small>');

                $('.count-publishable-total')
                    .html('503 <small>/' + boardcastLimit + '</small>');
                $('.count-unpublishable-total')
                    .html('211 <small>/' + boardcastLimit + '</small>');
                $('.count-unpublishable-abuse-total')
                    .html('227 <small>/' + boardcastLimit + '</small>');
                $('.count-neutral-total')
                    .html('59 <small>/' + boardcastLimit + '</small>');

                setTimeout(function () {
                    showCount();
                }, 1000);

                if (btn != false) {
                    btnLoading( btn, true );
                }
                //hideLoadingAnalysis('default');
            })
            .fail(function () {
                console.log('error');
            });

        // Set another completion function for the request above
        jqxhr.always(function () {
            if (btn != false) {
                btnLoading( btn, true );
            }
            //hideLoadingAnalysis('default');
            setTimeout(function () {
                hideLoadingAnalysis('default');
            }, 400);
        });
    }

    // search form submit event fire get tweets
    $('#formSearch').submit(function( event ) {

        var btn = $(this).find('.btn-loading');

        var parameter = getSearchParameter();
        /*
         if (checkTextSearchValue( parameter.q ) == false) {
         return false;
         }
         */
        //chartDestroy();
        getChart( parameter );
        getStatistics( parameter, btn );
        event.preventDefault();
    });

    $('#menu-toggle').click(function() {
        //lineChart.redraw();
    });

    var parameter = getSearchParameter();

    getDataLearned();

    $('#selectDomain').change(function (e) {
        getDataLearned();
    });

    getStatistics( parameter );

    getChart( parameter );

})();

