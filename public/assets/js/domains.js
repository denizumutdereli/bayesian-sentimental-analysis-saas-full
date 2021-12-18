var clipboard = new Clipboard('#copy');

clipboard.on('success', function (e) {
    //console.info('Action:', e.action);
    console.info('Text:', e.text);
    $('#msg').text('kopyalandı..');
    setTimeout(function () {
        $('#msg').text('');
    }, 1000);

    //console.info('Trigger:', e.trigger);

    e.clearSelection();
});

clipboard.on('error', function (e) {
    console.error('Action:', e.action);
    console.error('Trigger:', e.trigger);
});

function setListCount() {
    $('#rules .list-group-item').each(function (index) {
        count = index + 1;
        $(this).find('.count').text(count);
    });
}

function setListSourceCount() {
    $('#sourceslist .list-group-item').each(function (index) {
        count = index + 1;
        $(this).find('.count').text(count);
    });
}

function setListTagCount() {
    $('#tagslist .list-group-item').each(function (index) {
        count = index + 1;
        $(this).find('.count').text(count);
    });
}

//Domain Source Add
$("#add-source").on("click", function () {

    var count = $('#sourceslist .list-group-item').length + 1;
    var current = $("input[name='sources[]']")
            .map(function () {
                return $(this).val();
            }).get();

    names = []
    hidden_fields = []

    var item = $("#sourcesbox option:selected");

    //if empty
    if (item.val() == '') {
        var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Lütfen bir kaynak kategorisi seçin.'}
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

        hidden_fields.push("<input type='hidden' value='" + item.val() + "' name='sources[]' />");
        $('<li class="list-group-item"><span class="count">' + count + '</span>- ' + names.join(' + ') + ' <button type="button" class="close"><span aria-hidden="true">&times;</span><span class="sr-only">Kaldır</span></button>' + hidden_fields.join(' ') + '</li>').appendTo($("#sourceslist"));

    }
    $('#save_btn').attr("disabled", false);
    setListSourceCount();
});

$(document).on("click", "#sourceslist .close", function () {

    var count = $('#sourceslist .list-group-item').length - 1;

    $(this).parents("li").remove();
    //remove inputs
    setListSourceCount();

    if (count < 1) {

        var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Domain ekleyebilmeniz için, enaz bir adet kaynak kategorisi eklemelisiniz.'}

        showAlertModal(alertData);
        $('#save_btn').attr("disabled", true);
        return false;
    }
});
//Domain Source Add END

//Domain Tag Add
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

    $(this).parents("li").remove();
    //remove inputs
    setListTagCount();
});
//Domain Tag Add END

//Domain Rules Add
$("#add-rule").on("click", function () {

    var count = $('.list-group-item').length + 1;

    if ($("#rulesCheckboxContainer .checkbox input[type=checkbox]:checked").length <= 0) {
        return;
    }
    names = []
    hidden_fields = []
    $("#rulesCheckboxContainer .checkbox input[type=checkbox]:checked").each(function (idx, item) {

        names.push($(item).attr("name").charAt(0).toUpperCase() + $(item).attr("name").slice(1));
        hidden_fields.push("<input type='hidden' value='" + $(item).val() + "' name='rules[" + $("#rules .list-group-item").length + "][]' />")
    });

    $('<li class="list-group-item"><span class="count">' + count + '</span>- ' + names.join(' + ') + ' <button type="button" class="close"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>' + hidden_fields.join(' ') + '</li>').appendTo($("#rules"));

    $("#rulesCheckboxContainer .checkbox input[type=checkbox]:checked").attr("checked", false);

    setListCount();

    var count = $('#rules .list-group-item').length;

    if (count >= 1)
    {
        $('#save_btn').attr("disabled", false);
        return true;
    }

});

$(document).on("click", "#rules .close", function () {

    $(this).parents("li").remove();
    //rename all the inputs
    $("#rules .list-group-item").each(function (idx, item) {
        $(item).find("input[type=hidden]").attr("name", "rules[" + idx + "][]")
    });

    setListCount();

    var count = $('#rules .list-group-item').length;

    if (count < 1)
    {
        var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Domain\'in eklenebilmesi için en az bir kural eklemelisiniz.'}
        showAlertModal(alertData);
        $('#save_btn').attr("disabled", true);
        return false;
    }
});

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

//Domain Rules END

