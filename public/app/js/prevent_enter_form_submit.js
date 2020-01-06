/**
 * Created by Admininstrador on 20/07/2017.
 */

$("input").not($(":button")).keypress(function (evt) {
    if (evt.keyCode == 13) {
        itype = $(this).attr('type');
        if (itype !== 'submit'){
            var fields = $(this).parents('form:eq(0),body').find('button, input, textarea, select');
            var index = fields.index(this);
            if (index > -1 && (index + 1) < fields.length) {
                fields.eq(index + 1).focus();
            }
            return false;
        }
    }
});
