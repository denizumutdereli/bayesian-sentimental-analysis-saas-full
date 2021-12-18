$(document).ready(function () {

    var url = $.url(); // jQuery version

    console.log(url.segment(1));

    // cache scroll to top button
    var backTop = $('#back-top');
    // Hide scroll top button
    backTop.hide();

    // FadeIn or FadeOut scroll to top button on scroll
    $(window).on('scroll', function () {
        // if you scroll more then 400px then fadein goto top button
        if ($(this).scrollTop() > 400) {
            backTop.fadeIn();
            // otherwise fadeout button
        } else {
            backTop.fadeOut();
        }
    });

    function fixedSideBar() {
        var sidebar = $("#sidebar");
        var width = $(window).width();
        // FadeIn or FadeOut sidebar on scroll
        $(window).on('scroll', function () {
            // if you scroll more then 170px then fadein goto top button
            if ($(this).scrollTop() > 170 && width > 968) {
                sidebar.addClass('fixed-sidebar');
                // otherwise fadeout button
            } else {
                sidebar.removeClass('fixed-sidebar');
            }
        });
    }
    ;

    $("#menu-toggle").click(function (e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });

    // Animated scroll to top
    backTop.on('click', function () {
        $('html,body').animate({
            scrollTop: 0
        }, 500);
        return false;
    });
  
    $('.form-domain').submit(function (e) {
        var inputTotalValue = getInputSpinnerTotalValue();
        if (inputTotalValue < 100) {
            var alertData = {type: 'modal-sm', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Balans değerlerinin toplamı 100 olmak zorundadır. Lütfen 100 e tamamlayınız.'}

            showAlertModal(alertData);
            e.preventDefault();
            return false;
        }
    });

    $('.form-delete').submit(function (e) {
        var form = $(this);

        var parameters = {
            id: form.attr('id'),
            action: form.attr('action'),
            input: form.serialize()
        }

        deleteButtonAlert(parameters);

        e.preventDefault();
        return;
    });


    $(document).on('click', '.btn-confirm', function (e) {
        var btn = $(this);
        var action = btn.data('action');
        var input = btn.data('input');

        var jqxhr = $.post(action, input)
                .done(function (data) {
                    setTimeout(function () {
                        location.reload();
                    }, 400);
                })
                .fail(function () {
                    console.log('error');
                });

        // Set another completion function for the request above
        jqxhr.always(function () {
            //
        });
    });
});

function deleteButtonAlert(parameters) {
    var alertData = {
        type: 'modal-sm', title: '<i class="fa fa-minus-circle"></i> Uyarı',
        text: 'Kaydı silmek istediğinizden emin misiniz?',
        confirm: true,
        action: parameters.action,
        input: parameters.input
    };

    showAlertModal(alertData);
}

function scrollToTop() {
    $('html,body').animate({
        scrollTop: 0
    }, 500);
}

function alertNottify() {
    var alert = $('.alert');

    alert.fadeIn();

    setTimeout(function () {
        alert.fadeOut();
    }, 10000);
}

alertNottify();

var loadingTimer;

function showLoading(type, message) {
    var timeline = $('.timeline-centered');
    var loading = $('.loading-' + type);
    var loadingMessage = loading.find('.loading-message');

    var message = message || loadingMessage.data('message');

    timeline.css('opacity', 0.5);
    // set time out to loading message shown
    loadingTimer = setTimeout(function () {
        loadingMessage.fadeOut(400);
        setTimeout(function () {
            if (message != false) {
                loadingMessage.text(message);
            }
            loadingMessage.delay(400).fadeIn(400);
        }, 400);
    }, 5000);
    loading.fadeIn(400);
}

function hideLoading(type) {
    var timeline = $('.timeline-centered');
    var loading = $('.loading-' + type);
    var loadingMessage = loading.find('.loading-message');

    loadingMessage.text('Yükleniyor...');

    timeline.css('opacity', 1);
    //loadingMessage.fadeOut(400);
    loading.fadeOut(400);
    // clear timeout
    window.clearTimeout(loadingTimer);
}

function animateProgress(percent) {
    var progresBar = $('#learnStaticsProcess .progress-bar');
    var percentage = 0;
    if (percent.percentageLearning >= 100) {
        percentage = 100;
    } else {
        percentage = percent.percentageLearning;
    }
    var progressBarText = 'Öğrenim oranı: %' + percentage;
    if (percent.percentageLearning == 100) {
        progressBarText += " / " + percent.itemsLearned;
    } else if (percent.percentageLearning == 0) {
        progressBarText = '% 0';
    } else {
        progressBarText += ' (' + percent.itemsLearned + ' / ' + percent.limitsLearned + ')';
    }

    progresBar.animate({
        width: percentage + '%'
    }, 100);

    setTimeout(function () {
        $('.progress-bar-text').html(progressBarText);
    }, 1000);
    if (percentage <= 10)
    {
        var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Bu domainde öğrenme oranı % ' + percentage + '\'dır. Öğrenme oranı düşük olduğundan analiz sonuçları sağlıklı olmayabilir.'}
        showAlertModal(alertData);
    }
}

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

// alert modal generate html template and render data
function showAlertModal(alertData) {
    _.templateSettings.variable = 'rc';

    var defaultData = {
        type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı',
        text: 'Uyarı mesajı',
        confirm: false
    }

    alertData = $.extend({}, defaultData, alertData);

    // Grab the HTML out of our template tag and pre-compile it.
    var template = _.template(
            $('#modalTemplate').html()
            );

    // Define our render data (to be put into the 'rc' variable).
    var templateData = {
        modal: alertData
    };

    // Render the underscore template and inject in our current DOM.
    $('#modal').html(template(templateData));
    $('#alertModal').modal('show');
}

//var alertData = {
//    type: 'modal-sm',
//    title: '<i class="fa fa-minus-circle"></i> Uyarı',
//    text: 'Lütfen en az bir analiz metodu seçiniz!'
//}
//showAlertModal( alertData );


function resetProgressBar() {
    var progressBar = $('.progress-bar-learning');
    var progressBarText = '';

    progressBar.animate({
        width: 0 + '%'
    }, 100);

    setTimeout(function () {
        $('.progress-bar-text').html(progressBarText);
    }, 1000);
}

function roundFix(orig, target) {

    var i = orig.length, j = 0, total = 0, change, newVals = [], next, factor1, factor2, len = orig.length, marginOfErrors = [];

    // map original values to new array
    while (i--) {
        total += newVals[i] = Math.round(orig[i]);
    }

    change = total < target ? 1 : -1;

    while (total !== target) {

        // select number that will be less affected by change determined
        // in terms of itself e.g. Incrementing 10 by 1 would mean
        // an error of 10% in relation to itself.
        for (i = 0; i < len; i++) {

            next = i === len - 1 ? 0 : i + 1;

            factor2 = errorFactor(orig[next], newVals[next] + change);
            factor1 = errorFactor(orig[i], newVals[i] + change);

            if (factor1 > factor2) {
                j = next;
            }
        }

        newVals[j] += change;
        total += change;
    }


    for (i = 0; i < len; i++) {
        marginOfErrors[i] = newVals[i] && Math.abs(orig[i] - newVals[i]) / orig[i];
    }

    for (i = 0; i < len; i++) {
        for (j = 0; j < len; j++) {
            if (j === i)
                continue;

            var roundUpFactor = errorFactor(orig[i], newVals[i] + 1) + errorFactor(orig[j], newVals[j] - 1);
            var roundDownFactor = errorFactor(orig[i], newVals[i] - 1) + errorFactor(orig[j], newVals[j] + 1);
            var sumMargin = marginOfErrors[i] + marginOfErrors[j];

            if (roundUpFactor < sumMargin) {
                newVals[i] = newVals[i] + 1;
                newVals[j] = newVals[j] - 1;
                marginOfErrors[i] = newVals[i] && Math.abs(orig[i] - newVals[i]) / orig[i];
                marginOfErrors[j] = newVals[j] && Math.abs(orig[j] - newVals[j]) / orig[j];
            }

            if (roundDownFactor < sumMargin) {
                newVals[i] = newVals[i] - 1;
                newVals[j] = newVals[j] + 1;
                marginOfErrors[i] = newVals[i] && Math.abs(orig[i] - newVals[i]) / orig[i];
                marginOfErrors[j] = newVals[j] && Math.abs(orig[j] - newVals[j]) / orig[j];
            }

        }
    }


    function errorFactor(oldNum, newNum) {
        return Math.abs(oldNum - newNum) / oldNum;
    }

    //console.log( newVals );
    return newVals;
}
