$(function () {
    $(".btn-setting").on("click", function (event) {
        $("#addContactModal #btn-add").show();
        $("#addContactModal #btn-edit").hide();
        $("#addContactModal").modal("show");
    });
});
