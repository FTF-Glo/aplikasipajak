$(function() {

    // formSppt
    $('body').on('click', '#formSppt button[type=button][name=submit]:not([disabled])', function() {
        let v = $(this);
        let form = v.closest('form');
        
        if (v.val() != 'inquiry') {
            form.attr('target', '_blank');
        }
        v.attr('type', 'submit');

        v.click();

        v.attr('type', 'button');
        form.removeAttr('target');
    })
})