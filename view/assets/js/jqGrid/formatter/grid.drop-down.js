(function ($) {
  "use strict";

  $.extend($.fn.fmatter, {
    dropDown: function (cellValue, options, rowData) {
      let value = $(cellValue);

    cellValue = "<div class=\"dropdown\">" +
        "<button class=\"btn btn-default btn-xs dropdown-toggle\" type=\"button\" id=\"dropdownMenu1\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"true\">"+
        "More" +
        "<span class=\"caret\"></span></button>"+
        "<ul class=\"dropdown-menu\" style='position: inherit;' aria-labelledby=\"dropdownMenu1\">";
      for (var prop in value) {
        if ($(value[prop]).is('a')) {
          cellValue += "<li><a target='_blank' href=" + value[prop].getAttribute('href')  + "\>" + $(value[prop]).text() + "</a></li>";
        }
      }

      cellValue += "</ul>"+
      "</div>";

      return cellValue;
    }
  });

  $.extend($.fn.fmatter.dropDown, {
    unformat: function (cellvalue, options, td) {
      return cellvalue;
    }
  });

})(jQuery);

