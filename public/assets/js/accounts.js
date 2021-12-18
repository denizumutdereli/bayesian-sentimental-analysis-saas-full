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

//Account Package Rules
$("#accountType").on("change", function () {
    setDefaultValues();
});

function setDefaultValues() {
    var item = $("#accountType option:selected").attr("value");
    if (item == 'pitching') {
        $("#package").append('<option value="50">50K - pitching</option>');

        $('#package').val('50').change();
        $('#package').attr("disabled", true);
    } else {
        $("#package option[value='50']").remove();
        $('#package').attr("disabled", false);
    }
}

//Revoke Access-Token
$("#revoke").on("click", function () {
    var id = $("#revoke").attr("data-id");
    updateAccessToken(id);
});


function updateAccessToken(id) {

    var parameters = {
        projectid: id,
        bwtoken: $('#bwtoken').val()
    }

    var jqxhr = $.get('/account/auth', parameters)
            .done(function (data) {
                console.log(data);
                if (data.status == true) {
                    $("access_token").html(data.access_code + '***');
                    var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Bilgi', text: data.msg}
                    showAlertModal(alertData);     
                } else {
                    var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: data.msg}
                    showAlertModal(alertData);
                    return false;
                }

            })
            .fail(function () {
                console.log('error:' + data.msg);
            });

    // Set another completion function for the request above
    jqxhr.always(function () {
        showLoading('since');
    });

}

$(document).ready(function () {
    setDefaultValues();
});