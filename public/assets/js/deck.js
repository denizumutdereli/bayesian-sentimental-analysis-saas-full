(function () {

//** 
//links.

    $("#select-domain").submit(function (e) {
       
        var item = $("#domain option:selected");
        
        //if empty
        if (item.val() == '') {
            var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Lütfen bir domain seçin.'}
            showAlertModal(alertData);
             e.preventDefault();     
        }
    });

    $("#add-domain").on('click', function () {
        window.location.href = '/domain/create';
        return false;
    });

})();