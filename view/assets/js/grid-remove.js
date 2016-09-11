// single row remove action
$(document).on('click', '.ui-jqgrid-btable tr a[class^="remove"], .ui-jqgrid-btable tr a[class^="delete"]', function (e) {
	var elm = $(this);
	elm.closest('tr').click();
	var grid = elm.closest('table');
	var rowId = grid.jqGrid('getGridParam','selrow');
	//var rowData = grid.getRowData(rowId);

	// @link https://www.experts-exchange.com/questions/26929615/jqgrid-delete-row-related-info-needed.html
	grid.jqGrid('delGridRow', rowId, {
		url: elm.attr('href'),
		reloadAfterSubmit: true,
		caption: 'Удаление записи',
		msg: 'Вы действительно хотите удалить запись №' + rowId + '?',
		bSubmit: 'Удалить',
		bCancel: 'Отмена',
		height: 'auto',
		width: 'auto',

		beforeShowForm: function(form)  { // positioning form in center
			var dlgDiv = $("#delmod" + grid[0].id);
			var parentDiv = dlgDiv.parent(); // div#gbox_list
			var dlgWidth = dlgDiv.width();
			var parentWidth = parentDiv.width();
			var dlgHeight = dlgDiv.height();
			var parentHeight = parentDiv.height();

			// Grabbed jQuery for grabbing offsets from here:
			//http://stackoverflow.com/questions/3170902/select-text-and-then-calculate-its-distance-from-top-with-javascript
			var parentTop = parentDiv.offset().top;
			var parentLeft = parentDiv.offset().left;

			// HINT: change parentWidth and parentHeight in case of the grid
			//       is larger as the browser window
			dlgDiv[0].style.top = Math.round(parentTop + (parentHeight - dlgHeight) / 2) + "px";
			dlgDiv[0].style.left = Math.round(parentLeft + (parentWidth - dlgWidth) / 2) + "px";

			/*console.log(form);
			form.closest(".ui-jqdialog").position({
				of: window, // or any other element
				my: "center center",
				at: "center center"
			});*/
		},
		errorTextFormat: function (response) {
			var json = jQuery.parseJSON(response.responseText);
			return json.message;
		}
	});

	return false;
});
