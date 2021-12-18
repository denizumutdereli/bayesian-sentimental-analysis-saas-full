(function() {


    $("#positive").on('click', function() {
        var text = $('#text').val();
        if (checkText(text) == false) {
            return false;
        }
        addBayesLive(text, '1');
    });

    $("#negative").on('click', function() {
        var text = $('#text').val();
        if (checkText(text) == false) {
            return false;
        }
        addBayesLive(text, '-1');
    });

    $("#neutral").on('click', function() {
        var text = $('#text').val();
        if (checkText(text) == false) {
            return false;
        }

        addBayesLive(text, '0');
    });

    $("#analysis").on('click', function() {
        var text = $('#text').val();
        if (checkText(text) == false) {
            return false;
        }

        showAnalysis(text);
    });

    function checkText(text) {
        if (text == '') {
            var alertData = {
                type: 'modal-sm',
                title: '<i class="fa fa-minus-circle"></i> Uyarı',
                text: 'Lütfen en az bir kelime veya metin giriniz!'
            }
            showAlertModal( alertData );
            return false;
        }
    }

    //Action
    function addBayesLive(text, state) {

        //if (checkNgamCheckbox() == false) {
        //    return false;
        //}

        var parameter = {
            text: text,
            state: state,
            source: 'manual',
            domain_id : getDomainCheckbox(),
            method: getNgramCheckbox()
        }

        var jqxhr = $.post('/traine', parameter)
            .done(function( data ) {
                if (data.response == 1) {
                    $('#progress').fadeIn(400);

                    animateProgressCount(data);
                    animateProgressBar(data);
                } else if(data.response == -1) {
                    var alertData = {
                        type: 'modal-md',
                        title: '<i class="fa fa-minus-circle"></i> Uyarı',
                        text: 'Girmiş olduğunuz metin veya kelime daha önceden kayıtlı <a href="/sentimental/'+data.sentimental+'/edit" target="_blank">burayı tıklayarak</a> düzenleyebilirsiniz.'
                    }
                    showAlertModal( alertData );
                } else if(data.response == 2) {
                    var alertData = {
                        type: 'modal-md',
                        title: '<i class="fa fa-minus-circle"></i> Uyarı',
                        text: data.message
                    }
                    showAlertModal( alertData );
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

    function getNgramCheckbox() {
        var method = [];
        $('.ngram:checked').each(function() {
            method.push($(this).val());
        });
        return method;
    }

    function getDomainCheckbox() {
        return $('#selectDomain').val();
    }

    //console.log(getDomainCheckbox());

    function showAnalysis(text) {

        //if (checkNgamCheckbox() == false) {
        //    return false;
        //}

        var parameter = {
            text: text,
            //method: getNgramCheckbox(),
            domain_id:  getDomainCheckbox()
        }

        var jqxhr = $.post('/analysis', parameter)
            .done(function( data ) {
                if (data.response == 1){
                    $('#progress').fadeIn(400);

                    animateProgressCount(data);
                    animateProgressBar(data);
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

    function animateProgressCount(data) {
//            $('#percentPositive').attr('data-percent', data.state.pos);
//            $('#percentNegative').attr('data-percent', data.state.neg);
//            $('#percentNeutral').attr('data-percent', data.state.ntr);
////            $('#percentPolitic').attr('data-percent', data.state.pol);

        $('#percentPositive').attr('data-percent', data.positive);
        $('#percentNegative').attr('data-percent', data.negative);
        $('#percentNeutral').attr('data-percent', data.neutral);
//            $('#percentPolitic').attr('data-percent', data.state.pol);

        $('.percent').each(function() {
            var percent = $(this);
            var percentCount = percent.find('.percent-count');
            var percentCountData = parseFloat(percentCount.attr('data-percent'));

            //console.log(percentCountData);

            var hSpan = percent.parents('.progress-striped').find('span');

            percent.css('left', '0');

            if (percentCountData <= 10)
            {
                var offsetWidth = 10;
                offsetWidth += hSpan.width();
                percent.css('left', offsetWidth).fadeIn(400);
            }
            else
            {
                percent.animate({
                    "left": "+=" + percentCountData + "%"
                }, 400).fadeIn(400);
            }

            $({percent: 0}).animate({percent: percentCountData}, {
                duration: 1000,
                easing:'swing', // can be anything
                step: function() { // called on every step
                    // Update the element's text with rounded-up value:
                    var fontSize = 100;
                    fontSize += this.percent;
                    percentCount.text(this.percent.toFixed(2));
                    percentCount.css('font-size', fontSize + '%');
                },
                complete: function() {
                    percentCount.text(this.percent.toFixed(2));
                }
            }, 400);
        });
    }

    function animateProgressBar(data) {

        $('#progressBarPositive').attr('data-percent', data.positive + '%');
        $('#progressBarNegative').attr('data-percent', data.negative + '%');
        $('#progressBarNeutral').attr('data-percent', data.neutral + '%');
//            $('#progressBarPolitic').attr('data-percent', data.state.pol + '%');

        $('#progress .progress-result').each(function() {
            var progresBar = $(this).find('.progress-bar');
            progresBar.animate({
                width: progresBar.attr('data-percent')
            }, 400);
        });
    }

    function getDataLearned() {
        var parameter = {
            domain_id:  getDomainCheckbox()
        }

        var jqxhr = $.post('/learned', parameter)
            .done(function( data ) {
                if (data.response == 1){
                    //$('#progress').fadeIn(400);

                    //animateProgressCount(data);
                    //animateProgressBar(data);
                    animateProgress(data.dataLearned);
                    setButtonNames(data.dataLearned.domainNames);
                    setLabelNames(data.dataLearned.domainNames);

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

    function setButtonNames(names) {
        $('#positive').find('.text').text(names[1]);
        $('#negative').find('.text').text(names[-1]);
        $('#neutral').find('.text').text(names[0]);
    }

    function setLabelNames(names) {
        $('#labelPositive').text(names[1]);
        $('#labelNegative').text(names[-1]);
        $('#labelNeutral').text(names[0]);

        var data = {
            positive: 0,
            negative: 0,
            neutral: 0
        }

        animateProgressCount(data);
        animateProgressBar(data);
    }

    getDataLearned();

    $('#selectDomain').change(function (e) {
        getDataLearned();
    });

})();