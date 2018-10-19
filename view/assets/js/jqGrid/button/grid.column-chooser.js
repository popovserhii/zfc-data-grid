(function ($) {
  //"use strict";

  var idsOfSelectedRows = [],
    getColumnNamesFromColModel = function () {
      var colModel = this.jqGrid("getGridParam", "colModel");
      return $.map(colModel, function (cm, iCol) {
        // we remove "rn", "cb", "subgrid" columns to hold the column information
        // independent from other jqGrid parameters
        return $.inArray(cm.name, ["rn", "cb", "subgrid"]) >= 0 ? null : cm.name;
      });
    }

    function saveColumnState () {
    var p = this.jqGrid("getGridParam"), colModel = p.colModel, i, l = colModel.length, colItem, cmName,
      postData = p.postData,
      columnsState = {
        search: p.search,
        page: p.page,
        rowNum: p.rowNum,
        sortname: p.sortname,
        sortorder: p.sortorder,
        cmOrder: getColumnNamesFromColModel.call(this),
        selectedRows: idsOfSelectedRows,
        colStates: {}
      },
      colStates = columnsState.colStates;

    if (postData.filters !== undefined) {
      columnsState.filters = postData.filters;
    }

    for (i = 0; i < l; i++) {
      colItem = colModel[i];
      cmName = colItem.name;
      if (cmName !== "rn" && cmName !== "cb" && cmName !== "subgrid") {
        colStates[cmName] = {
          hidden: colItem.hidden
        };
      }
    }

    return colStates;
  }

  $.extend($.fn.navButton, {
    columnChooser: function (gridId, options) {

      var grid = "#" + gridId + "_grid";
      options = $.extend({
        done: function(perm) {
          if (perm) {
            this.jqGrid("remapColumns", perm, true);
            var columns = saveColumnState.call(this);
            $.ajax({
              url: "/admin/data-grid/buttons",
              type: "post",
              data: {"columns" : columns, "gridId" : gridId}
            });
          }
        }
      });

      $(grid).jqGrid("columnChooser", options);
    }
  });

})(jQuery);