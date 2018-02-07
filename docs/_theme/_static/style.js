
$(document).ready(function () {
    var blocks = $("div.configuration-block");

    blocks.find("[class^=highlight-]").hide();
    blocks.addClass("jsactive clearfix");
    blocks.each(function () {
        var i = $("[class^=highlight-]:first", $(this));
        i.show();
        i.parents("ul:eq(0)").height(i.height() + 40);
    });
    blocks.find("li").each(function () {
        var i = $(":first", $(this)).html();
        $(":first ", $(this)).html(""), $(":first ", $(this)).append('<a href="#">' + i + "</a>"), $(":first", $(this)).bind("click", function () {
            $("[class^=highlight-]", $(this).parents("ul")).hide(), $("li", $(this).parents("ul")).removeClass("selected"), $(this).parent().addClass("selected");
            var i = $("[class^=highlight-]", $(this).parent("li"));
            return i.show(), i.parents("ul:eq(0)").height(i.height() + 40), !1
        })
    });
    blocks.each(function () {
        $("li:first", $(this)).addClass("selected")
    })
});
