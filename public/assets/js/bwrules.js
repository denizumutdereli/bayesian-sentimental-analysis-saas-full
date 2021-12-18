$(document).ready(function () {

    $('#save').on('click', function (e) {

        var name = $("#name").val();
        if (name == '') {
            e.preventDefault();
            var alertData = {type: 'modal-sm', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Lütfen kural adını belirtin.'}
            showAlertModal(alertData);
            $("#name:text:visible:first").focus();
        }
    });




    $("#project").on("change", function () {
        resetAllValues();
        hideAllDivs();
        var item = $("#project option:selected").attr("value");
        if (item == '') {
            $("#project").val('');
            var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Kural eklenebilmesi için en az bir adet proje seçilmelidir.'}
            showAlertModal(alertData);
            $('#save').attr("disabled", true);
            return false;
        } else {
            return getQueries(item);
        }
    });

    function resetAllValues() {
        showLoading('since');
        $("#search").empty();
        $("#search_to").empty();
        $("#count").text('0');
        resetDomainValues();
        return false;
    }

    function hideAllDivs() {
        trigger('queriesContainer', 'hide');
        trigger('domainContainer', 'hide');
        trigger('whenContainer', 'hide');
        trigger('actionContainer', 'hide');
        trigger('categoriesContainer', 'hide');
        trigger('subcategoriesContainer', 'hide');
        trigger('sentimentContainer', 'hide');
        trigger('deleteContainer', 'hide');
        trigger('tagContainer', 'hide');
    }

    function resetDomainValues() {
        $("#domain").val('');
        resetActionValues('0');
        return false;
    }

    function resetActionValues(type) {

        if (type == 0) {
            $("#action").val('');
        }

        resetCategoryValues();
        $("#sentiment").val('0');
        $('#delete').attr('checked', false);
        //$("#tags").empty();
        return false;
    }

    function resetCategoryValues() {
        $("#category").val('');
        $("#subcategoriesContainer select").each(function () {
            $(this).val($(this).find("option[selected]").val());
        });
        return false;
    }

    function trigger(item, action) {
        if (action == 'hide') {
            $('#' + item).hide().fadeOut(1000);
        } else {
            $('#' + item).show().fadeIn(1000);
        }

    }

    function getQueries(id) {

        var parameters = {
            projectid: id,
            bwtoken: $('#bwtoken').val(),
            endpoint: 'queries'
        }

        var jqxhr = $.get('/bwrules/queries', parameters)
                .done(function (data) {
                    if (data.status == true) {
                        showQueries(data.data);
                    } else {
                        resetAllValues();
                        var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Bu projede bir Query bulunamadı.<br>Lütfen farklı bir proje seçin.'}
                        showAlertModal(alertData);
                        return false;
                    }

                })
                .fail(function () {
                    resetAllValues();
                    var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: data.msg}
                    showAlertModal(alertData);
                    return false;
                });

        // Set another completion function for the request above
        jqxhr.always(function () {
            showLoading('since');
        });

    }

    function showQueries(data) {

        $.each(data, function (index, val) {
            $("#search").append('<option value="' + index + '">' + val.name + '</option>');
        });
        trigger('queriesContainer', 'show');
        setCounter();
    }

    function PreGetQueries(id) {

        var parameters = {
            projectid: id,
            bwtoken: $('#bwtoken').val(),
            endpoint: 'queries'
        }

        var jqxhr = $.get('/bwrules/queries', parameters)
                .done(function (data) {
                    if (data.status == true) {
                        PreShowQueries(data.data);
                    } else {
                        resetAllValues();
                        var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Bu projede bir Query bulunamadı.<br>Lütfen farklı bir proje seçin.'}
                        showAlertModal(alertData);
                        return false;
                    }

                })
                .fail(function () {
                    resetAllValues();
                    var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: data.msg}
                    showAlertModal(alertData);
                    return false;
                });

        // Set another completion function for the request above
        jqxhr.always(function () {
            //showLoading('since');
        });

    }

    function PreShowQueries(data) {

        var selectedQueries = $('#selectedQueries').val().split(',');

        var temp = [];
        $.each(data, function (index, val) {

            $("#search").append('<option value="' + index + '">' + val.name + '</option>');
            temp[index] = val.name;
        });

        $.each(selectedQueries, function (s, id) { //for speed concern
            
            $("#search option[value='"+id+"']").remove();
            $("#search_to").append('<option value="' + id + '">' + temp[id] + '</option>');
        });





        trigger('queriesContainer', 'show');
        setCounter();

    }

    $('#search').multiselect({
        search: {
            left: '<input type="text" name="q" class="form-control" placeholder="Bul..." />',
            right: '<input type="text" name="q" class="form-control" placeholder="Bul..." />',
        },
        afterMoveToRight: function (left, right) {
            return setCounter();
        },
        afterMoveToLeft: function (left, right) {
            return setCounter();
        },
    });



    function PreGetBwTags(id) {

        var parameters = {
            projectid: id,
            bwtoken: $('#bwtoken').val(),
            endpoint: 'tags'
        }

        var jqxhr = $.get('/bwrules/queries', parameters)
                .done(function (data) {
                    console.log(data);
                    if (data.status == true) {
                        PreShowTags(data.data);
                    } else {
                        trigger('tagContainer', 'hide');
                        var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Bu projede bir Tag bulunamadı.<br>Lütfen farklı bir işlem seçin.'}
                        showAlertModal(alertData);
                        return false;
                    }

                })
                .fail(function () {
                    var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: data.msg}
                    showAlertModal(alertData);
                    return false;
                });

        // Set another completion function for the request above
        jqxhr.always(function () {
            //showLoading('since');
        });
    }

    function PreShowTags(data) {

        var tags = [];
        trigger('tagContainer', 'show');
        $("#tags").remove();


        $.each(data, function (index, val) {
            tags.push(val);
        });

        var input = '<input type="text" id="tags" class="form-control">';

        $('#tagContainer div').append(input);

        //console.log(tags);

        var tagselect = $('#tags').magicSuggest({
            placeholder: 'Lütfen etiketleri girin..',
            name: 'tagging',
            allowDuplicates: false,
            sortOrder: 'name',
            //expandOnFocus: true,
            maxSelection: 4,
            resultAsStringDelimiter: '*:',
            //resultAsString: true,
            allowFreeEntries: false,
            useTabKey: true,
            useCommaKey: true,
            value: getPreTagData(),
            data: tags,
            valueField: 'id',
            displayField: 'name'
        });


        $(tagselect).on('selectionchange', function (e, m) {
            if (("#tags .ms-sel-ctn .ms-sel-item").length > 0) {
                showLoading('done');
            } else {
                showLoading('since');
            }
        });


    }

    function getPreTagData() {
        var tags = $('#selectedTags').val().split(',');
        if (tags.length == 0) {
            var tags = ['2964163'];//Default All Mentions tag.
        }
        return tags;
    }


    function setCounter() {

        var counter = countSelectedQueries();
        $("#count").text(counter);

        if (counter > 0) {
            trigger('domainContainer', 'show');
            // console.log(counter);
        } else {
            trigger('domainContainer', 'hide');
            hideDomainDivs();
            hideActionDivs();
            resetDomainValues();
            resetActionValues('0');
        }

    }

    function countSelectedQueries() {
        var numberOfOptions = $('select#search_to option').length
        return numberOfOptions;
    }

    $("#domain").on("change", function () {

        var item = $("#domain option:selected").attr("value");
        if (item == '') {
            var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Lütfen bir domain seçin.'}
            showAlertModal(alertData);
            resetDomainValues();
            resetActionValues('0');
            hideDomainDivs();
            return false;
        } else {
            trigger('whenContainer', 'show');
            trigger('actionContainer', 'show');
        }

    });

    function hideDomainDivs() {
        showLoading('since');
        trigger('whenContainer', 'hide');
        trigger('actionContainer', 'hide');
        hideActionDivs();
        return false;
    }

    function hideActionDivs() {
        showLoading('since');
        trigger('categoriesContainer', 'hide');
        trigger('subcategoriesContainer', 'hide');
        trigger('sentimentContainer', 'hide');
        trigger('deleteContainer', 'hide');
        trigger('tagContainer', 'hide');
        return false;
    }

    $("#action").on("change", function () {

        var item = $("#action option:selected").attr("value");
        if (item == '') {
            var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Lütfen bir işlem seçin.'}
            showAlertModal(alertData);
            resetActionValues('0');
            hideActionDivs();
            return false;
        } else {
            hideActionDivs();
            resetActionValues();
            var projectid = $("#project option:selected").attr("value");
            switch (item) {
                case 'category':
                    getBwCategories(projectid);
                    return false;
                    break;
                case 'sentiment':
                    showLoading('done');
                    trigger('sentimentContainer', 'show');
                    return false;
                    break;
                case 'delete':
                    trigger('deleteContainer', 'show');
                    return false;
                    break;
                case 'tag':
                    trigger('tagContainer', 'show');
                    getBwTags(projectid);
                    return false;
                    break;
            }
        }

    });

    function getBwCategories(id) {

        var parameters = {
            projectid: id,
            bwtoken: $('#bwtoken').val(),
            endpoint: 'categories'
        }

        var jqxhr = $.get('/bwrules/queries', parameters)
                .done(function (data) {
                    console.log(data);
                    if (data.status == true) {
                        showCategories(data.data);
                    } else {
                        trigger('subcategoriesContainer', 'hide');
                        var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Bu projede bir Kategori bulunamadı.<br>Lütfen farklı bir işlem seçin.'}
                        showAlertModal(alertData);
                        return false;
                    }

                })
                .fail(function () {
                    var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: data.msg}
                    showAlertModal(alertData);
                    return false;
                });

        // Set another completion function for the request above
        jqxhr.always(function () {
            showLoading('since');
        });
    }

    function showCategories(data) {

        trigger('categoriesContainer', 'show');
        $("#category").empty();
        $("#category").append('<option value="">Lütfen bir kategori seçin</option>');

        $.each(data, function (index, val) {

            if (val.children.length > 0) {

                var subcategories = $("<select></select>").attr("id", "sub_" + val.id).attr("name", "sub_" + val.id).attr("multiple", val.multiple).attr("style", "display:none").attr("class", "form-control subcat");
                $.each(val.children, function (i, sub) {
                    subcategories.append('<option value="' + sub.id + '">' + sub.name + '</option>');
                });
            }
            $("#category").append('<option value="' + index + '" selected="selected">' + val.name + '</option>');

            $("#subcategoriesContainer div").append(subcategories);

        });

    }

    $("#category").on("change", function () {
        showLoading('since');
        var item = $("#category option:selected").attr("value");
        $("#subcategoriesContainer select").each(function () {
            $(this).val($(this).find("option[selected]").val());
        });
        $("select[id^='sub_']").hide();
        $("#subcategoriesContainer").show().fadeIn(1000);
        $("#sub_" + item).show().fadeIn(1000);

    });

    function getBwTags(id) {

        var parameters = {
            projectid: id,
            bwtoken: $('#bwtoken').val(),
            endpoint: 'tags'
        }

        var jqxhr = $.get('/bwrules/queries', parameters)
                .done(function (data) {
                    console.log(data);
                    if (data.status == true) {
                        showTags(data.data);
                    } else {
                        trigger('tagContainer', 'hide');
                        var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Bu projede bir Tag bulunamadı.<br>Lütfen farklı bir işlem seçin.'}
                        showAlertModal(alertData);
                        return false;
                    }

                })
                .fail(function () {
                    var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: data.msg}
                    showAlertModal(alertData);
                    return false;
                });

        // Set another completion function for the request above
        jqxhr.always(function () {
            showLoading('since');
        });
    }

    function showTags(data) {

        var tags = [];
        trigger('tagContainer', 'show');
        $("#tags").remove();


        $.each(data, function (index, val) {
            tags.push(val);
        });

        var input = '<input type="text" id="tags" class="form-control">';

        $('#tagContainer div').append(input);

        //console.log(tags);

        var tagselect = $('#tags').magicSuggest({
            placeholder: 'Lütfen etiketleri girin..',
            name: 'tagging',
            allowDuplicates: false,
            sortOrder: 'name',
            //expandOnFocus: true,
            maxSelection: 4,
            resultAsStringDelimiter: '*:',
            //resultAsString: true,
            allowFreeEntries: false,
            useTabKey: true,
            useCommaKey: true,
            data: tags,
            valueField: 'id',
            displayField: 'name'
        });


        $(tagselect).on('selectionchange', function (e, m) {
            if (("#tags .ms-sel-ctn .ms-sel-item").length > 0) {
                showLoading('done');
            } else {
                showLoading('since');
            }
        });


    }

    $("#delete").on("change", function () {
        if ($('#delete').is(':checked')) {
            showLoading('done');
        } else {
            showLoading('since');
        }
    });

    function showLoading(type) {

        switch (type) {
            case 'since':
                $('#save').attr("disabled", true);
                break;
            case 'done':
                $('#save').attr("disabled", false);
                break;
            default:
                $('#save').attr("disabled", true);
                break;
        }

    }

    function getInputSpinnerTotalValue(input) {
        var input = input || false;

        var totalValue = 0;

        if (input == false) {
            $('.input-spinner').each(function () {
                totalValue += parseInt($(this).val());
            });
        } else {
            totalValue = $('input').val();
        }

        return totalValue;
    }

    function setInputSpinnerValue(input, val) {
        $(input).val(val);
    }

    $('.spinner .btn:first-of-type').click(function (e) {
        var input = $(this).closest('.form-group').find('.spinner input');

        var inputMaxValue = parseInt(input.data('max'));
        var inputCurrentVal = parseInt(input.val());

        //console.log(inputCurrentVal);

        var inputTotalValue = getInputSpinnerTotalValue();

        if (inputTotalValue <= inputMaxValue) {
            if (inputCurrentVal < inputMaxValue) {
                var inputNeutralValue = parseInt($('.input-spinner-neutral').val());

                if (inputNeutralValue > 0) {
                    input.val(parseInt(input.val(), 10) + 1);
                    setInputSpinnerValue('.input-spinner-neutral', (inputNeutralValue - 1));
                } else {
                    var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Balans değerlerinin toplamı 100 olmak zorundadır.'}

                    showAlertModal(alertData);
                }
            }
        } else {
            var alertData = {type: 'modal-sm', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Balans değerlerinin toplamı 100 olmak zorundadır, 100 den fazla olamaz'}

            showAlertModal(alertData);
        }

        e.preventDefault();
    });

    $('.spinner .btn:last-of-type').click(function (e) {
        var input = $(this).closest('.form-group').find('.spinner input');

        var inputMinValue = parseInt(input.data('min'));
        var inputCurrentVal = parseInt(input.val());

        //console.log(inputCurrentVal);

        if (inputCurrentVal > inputMinValue) {
            var inputNeutralValue = parseInt($('.input-spinner-neutral').val());

            if (inputNeutralValue < 100) {
                input.val(parseInt(input.val(), 10) - 1);
                setInputSpinnerValue('.input-spinner-neutral', (inputNeutralValue + 1));
            } else {
                var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Balans değerlerinin toplamı 100 olmak zorundadır.'}

                showAlertModal(alertData);
            }
        } else {
            var alertData = {type: 'modal-sm', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Balans değerlerinin toplamı 100 olmak zorundadır.'}

            showAlertModal(alertData);
        }

        e.preventDefault();
    });






//Build procudure

    var projectid = $("#project").val();
    if (projectid) {
        var queries = PreGetQueries(projectid);
        $("#project").append('<option value="">Lütfen bir proje seçin</option>');
        $("#domain").append('<option value="">Lütfen bir domain seçin</option>');
        var selectedAction = $('#selectedAction').val();

        var selectedDelete = $('#selectedDelete').val();
        var selectedTags = $('#selectedTags').val();
        var selectedDomain = $('#selectedDomain').val();
        $('#domain option[value=' + selectedDomain + ']').attr('selected', 'selected');

//        $('#param1').val($('#selectedParam1').val());
//        $('#param2').val($('#selectedParam2').val());
//        $('#param3').val($('#selectedParam3').val());
        trigger('queriesContainer', 'show');
        trigger('whenContainer', 'show');
        trigger('actionContainer', 'show');
        switch (selectedAction) {
            case "sentiment":
                trigger('sentimentContainer', 'show');
                break;

            case "delete":
                trigger('deleteContainer', 'show');
                break;

            case "tag":
                trigger('tagContainer', 'show');
                PreGetBwTags(projectid);

                break;
        }
        showLoading('done');
    } else
    {
        setDefaultValues();
        resetAllValues();
    }

});


