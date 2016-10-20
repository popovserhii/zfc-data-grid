//$(document).on('click', '.modal .ui-jqgrid-btable tr, .modal .ui-jqgrid-btable tr a', function (e) {
/** @todo Подумати над реалізацією, коли клік відбувається на посиланні @see http://screencast.com/t/lHRm7fH1a **/
$(document).on('click', '.modal .ui-jqgrid-btable tr', function (e) {
	//var elm = $(this);
	var elm = $(e.target);
	var grid = elm.closest('table');
	var modal = grid.closest('.modal');

	if (elm.is('a')) {
		grid.jqGrid('setSelection', elm.closest('tr').attr('id'));
	}

	//var dataType = grid.jqGrid('getGridParam', 'datatype');
	var rowId = grid.jqGrid('getGridParam', 'selrow');
	var rowData = grid.getRowData(rowId);

	//$.data(document.body, 'agereSelectedRowData', rowData);
	$.data(document.body, 'jqGrid.lastSelectedRowData', rowData);

	if (elm.is('button')) {
		return; // skip if click on row button
	}

	var idName = modal.attr('id').split('-')[0] + '_id';
	// insert selected row id to form input by name
	if (modal.data('select-to') && modal.data('select-to').length) {
		$(modal.data('select-to')).val(rowData[idName]);
	}

	// refresh block content
	if (modal.data('refresh') && modal.data('refresh').length) {
		var elmRefresh = $(modal.data('refresh'));
		var href = elmRefresh.data('href') + '/' + rowData[idName];
		elmRefresh.data('href', href).trigger('refresh');
	}

	modal.modal('hide');

	return false;
});