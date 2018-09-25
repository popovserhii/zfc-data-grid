(function ($) {
  "use strict";

  //let linkFormatter = new DataGrid_LinkFormatter();

  $.extend($.fn.fmatter, {
    dropDown: function (cellValue, options, rowData) {
      let value = $(cellValue);



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

    /*<div class="btn-group" role="group" aria-label="Button group with nested dropdown">
        <button type="button" class="btn btn-secondary">1</button>
        <button type="button" class="btn btn-secondary">2</button>

        <div class="btn-group" role="group">
        <button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Dropdown
        </button>
        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
        <a class="dropdown-item" href="#">Dropdown link</a>
        <a class="dropdown-item" href="#">Dropdown link</a>
      </div>
      </div>
      </div>*/

      console.log(value/*, arguments*/);
      return cellValue;
    }
  });

  $.extend($.fn.fmatter.dropDown, {
    unformat: function (cellvalue, options, td) {
      return cellvalue;
    }
  });

})(jQuery);