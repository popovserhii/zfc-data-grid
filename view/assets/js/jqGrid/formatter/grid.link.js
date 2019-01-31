(function ($) {
  "use strict";

  $.extend($.fn.fmatter, {
    link: function (cellValue, options, rowData) {
      let value = cellValue.trim();
      let name = options.colModel.name;

      // Check if value is HTML tag formatted on server
      /*if ('<' === value.substring(0, 1)) {
        let elm = $(value);
        let href = elm.find('a');
        if (!href.length && !elm.is('a')) {
          try {
            let row = Object.assign({}, rowData);
            let fOptions = options.colModel.formatoptions.link;

            row[name] = cellValue;

            linkFormatter.setLink(':marketplace_domain:/dp/:product_asin:');
            linkFormatter.setLinkColumnPlaceholders(fOptions.column);
            linkFormatter.setRowData(rowData);
            value = linkFormatter.getFormattedValue(name);


            //console.log(value, arguments);
          } catch(error) {
            console.error(error);
            // expected output: SyntaxError: unterminated string literal
            // Note - error messages will vary depending on browser
          }
        }
      }*/

      //console.log(value, arguments);
      return cellValue;
    }
  });

  $.extend($.fn.fmatter.link, {
    unformat: function (cellvalue, options, td) {
      return cellvalue;
    }
  });

})(jQuery);