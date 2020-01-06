/**
 * Created by Admininstrador on 05/07/2017.
 */

var tableOffset = $("#fixable_table").offset().top;
var $header = $("#fixable_table > thead");
var $cloned = $('#cloned').append($header.clone());
var $fixedHeader = $('#fixed').css({ "position":"fixed", "display":"none",
    "border-collapse":"collapse" });
/* var $fixedHeader = $('#fixed').css({ "position":"fixed", "top":"0", "display":"none",
    "border-collapse":"collapse" }); */
/*41px
 var $fixedHeader = $('#fixed').append($header.clone()).css({ "position":"fixed", "top":"0", "width":"98%",
 "display":"none", "border-collapse":"collapse" });
 */

$(window).bind("scroll", function() {
    var offset = $(this).scrollTop();

    if (offset >= tableOffset && $fixedHeader.is(":hidden")) {
        if (!$fixedHeader.hasClass("margenSuperior")) {
            $fixedHeader.addClass( "margenSuperior" );
        }
        
        $fixedHeader.show();

        $.each($header.find('tr > th'), function(ind,val){
            var original_width = $(val).width();
            var original_padding = $(val).css("padding");
            $($fixedHeader.find('tr > th')[ind])
                .width(original_width)
                .css("padding",original_padding);
        });
    }
    else if (offset < tableOffset) {
        $fixedHeader.removeClass( "margenSuperior" );
        $fixedHeader.hide();
    }
});