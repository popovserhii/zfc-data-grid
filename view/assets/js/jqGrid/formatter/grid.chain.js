/**
 * Call formatters in chain.
 * By default jqGrid can use only one formatter.
 * Use this formatter carefully on your own risk.
 *
 * Usage:
 * ...
 * formatter: 'chain',
 * formatoptions: {'chain': ['number', 'link'], 'link': {'link options...'}}
 * ...
 */
(function ($) {
  "use strict";

  //$.fn.gridNavButton
  $.extend($.fn.fmatter, {
    chain: function (cellValue, options, rowData, action) {
      try {
        let value = cellValue;
        let formatterNames = options.colModel.formatoptions.chain;
        for (let name of formatterNames) {
          let option = options.colModel.formatoptions[name];
          value = $.fn.fmatter.call(this, name, value, option, rowData, action);
        }
        return value;
      } catch (error) {
        console.error(error);
      }
    }
  });

  $.extend($.fn.fmatter.chain, {
    unformat: function (cellValue, options, td) {
      try {
        let value = cellValue;
        let formatterNames = options.colModel.formatoptions.chain;
        for (let name of formatterNames) {
          let option = options.colModel.formatoptions[name];
          value = $.fn.fmatter[name].unformat(value, option, td);
          console.log(value);

        }

        return value;
      } catch (error) {
        console.error(error);
      }
    }
  });

})(jQuery);