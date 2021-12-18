(function () {

//** 
//show upload box.

    $("#add-tag").on('click', function () {
        showUploadModal();
    });

//** 
//modal trigger 
    function showUploadModal() {
        $('#myModal').modal('toggle');
    }

//File extension check
    $("#csvfile").change(function () {
        var fileExtension = ['xls', 'xlsx', 'csv', 'txt'];
        if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
            alert("Dosya tipi hatası. Kabul edilecek dosya tipleri : " + fileExtension.join(', '));
            $("#csvfile").val('');
        }
    });

    var loadingTimer;

//uploadForm
    $("#uploadForm").submit(function (event) {
        showLoading('since');
        //event.preventDefault();
    });

    $('.input').editable({
        type: 'text',
        url: '/tagupload/save',
        
        ajaxOptions: {
            type: "POST",
            //async: false,
            //data: parameter,
            dataType: "json"
        },
        success: function (response, newValue) {
            if (!response) {
                return "Unknown error!";
            }
            if (response.success === false) {
                return response.msg;
            }
        }
    });






    function showLoading(type, message) {
        var container = $('#myModal');
        var loading = $('.loading-' + type);
        var loadingMessage = loading.find('.loading-message');

        loadingMessage.text('Yükleniyor...');

        container.toggle();
        //container.css('opacity', 0.5);

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
        var container = $('.container');
        var loading = $('.loading-' + type);
        var loadingMessage = loading.find('.loading-message');

        container.css('opacity', 1);
        //loadingMessage.fadeOut(400);
        loading.fadeOut(400);
        // clear timeout
        window.clearTimeout(loadingTimer);
    }

})();