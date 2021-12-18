//Tag type switch

$("#tag_form :checkbox").on("change", function () {
    if($('#tag_form :checkbox').prop('checked')) {
       var alertData = {type: 'modal-md', title: '<i class="fa fa-minus-circle"></i> Uyarı', text: 'Kara Liste seçildiğinde, liste içeriğine sahip mention\'lar otomatik olarak Negatif olur!'}
        showAlertModal(alertData);
        return false;
    }
    
});

