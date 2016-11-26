AgereGrid = {
	body: $('body'),
	onlyOnce: {},

	attachEvents: function () {
		this.attachOnSelectRow();
		this.attachOnTabActivate();
		this.attachDataInitTrigger();
		this.attachActivateDatePicker();
	},

	// Show Print dialog
	attachOnSelectRow: function () {
		// Remove handler from existing elements
		this.body.off('jqGrid.onSelectRow', '.ui-jqgrid', this.selectRow);

		// Re-add event handler for all matching elements
		this.body.on('jqGrid.onSelectRow', '.ui-jqgrid', this.selectRow);
	},

	attachOnTabActivate: function () {
		// Remove handler from existing elements
		this.body.off('shown.bs.tab', 'a[data-toggle="tab"]', this.activateOnce);

		// Re-add event handler for all matching elements
		this.body.on('shown.bs.tab', 'a[data-toggle="tab"]', this.activateOnce);
	},

	attachDataInitTrigger: function () {
		//$('#cartItem_grid').bind('jqGridAfterGridComplete', this.actualiseTotalPrice);
		this.body.bind('jqGrid.loadComplete', this.dataInitTrigger);
	},

	attachActivateDatePicker: function () {
		// Remove handler from existing elements
		this.body.off('jqGrid.dataInit', this.activateDatePicker);

		// Re-add event handler for all matching elements
		this.body.on('jqGrid.dataInit', this.activateDatePicker);
	},

	selectRow: function (e, id, boolean, orgClickEvent) {
		var self = AgereGrid;
		var lastSelection = self.body.data('jqGrid.lastSelection');

		//if (id && id !== lastSelection) {
			var grid = $(e.target); // "this" means jqGrid @link http://www.trirand.com/jqgridwiki/doku.php?id=wiki:events
			grid.restoreRow(lastSelection);

			var editParameters = {
				keys: true,
				successfunc: self.editSuccessful,
				errorfunc: self.editFailed,
				aftersavefunc: self.reload,
				restoreAfterError: false
			};

			//grid.jqGrid('editRow', id, editParameters);

			// @link http://stackoverflow.com/a/2157888/1335142
			//grid.jqGrid('editRow', id, editParameters, '', '', '', '', aftersavefunc, errorfunc, afterrestorefunc);
			grid.editRow(id, editParameters);

			self.body.data('jqGrid.lastSelection', id);
		//}
	},

	editSuccessful: function (data, stat) {
		var response = data.responseJSON;
		if (response.hasOwnProperty('error')) {
			if (response.error.length) {
				return [false, response.error];
			}
		}
		return [true, "", ""];
	},

	editFailed: function (rowID, response) {
		$.jgrid.info_dialog(
			$.jgrid.regional["ru"].errors.errcap,
			'<div class="ui-state-error">RowID:' + rowID + ' :  ' + response.responseJSON.error + '</div>',
			$.jgrid.regional["ru"].edit.bClose,
			{buttonalign: 'right', styleUI: 'Bootstrap'}
		);
		//alert(response.responseJSON.error);
	},

	/**
	 * @link http://stackoverflow.com/a/2157888/1335142
	 * @param id
	 * @param result
	 */
	reload: function (id, result) {
		//console.log('reload is activate');
		$(this).trigger('reloadGrid');
	},

	/**
	 * Activate tab grid only once
	 */
	activateOnce: function() {
		var self = AgereGrid;
		var hash = arguments[0].target.hash;
		var grid = $(hash + '_grid');

		if (grid.length && self.isDeactivated(hash)) {
			grid.jqGrid('setGridParam', {
				datatype: 'json'
			}).trigger('reloadGrid');

			// re fit grid template
			grid.jqGrid('setGridWidth', grid.closest('.tab-pane').width());

			self.onlyOnce['activateOnce'][hash] = true;
		}
	},

	/**
	 * Is tab grid deactivated
	 *
	 * @param hash
	 * @returns {boolean}
	 */
	isDeactivated: function(hash) {
		var self = AgereGrid;

		if (self.onlyOnce['activateOnce'] == undefined) {
			self.onlyOnce['activateOnce'] = {};

			// First tab is loaded by default then mark its as activated
			var firstTab = $(hash + '_grid').closest('.tab-content').prev().find('li:first a');
			self.onlyOnce['activateOnce'][firstTab.attr('href')] = true;
		}

		return self.onlyOnce['activateOnce'][hash] == undefined;
	},

	dataInitTrigger: function() {
		var grid = $(arguments[0].target);
		var colModels = grid.jqGrid('getGridParam', 'colModel');

		$.each(colModels, function(i, model) {
			if (!model.editable) {
				return;
			}
			grid.setColProp(model.name, {
				editoptions: {
					dataInit: function (elem) {
						//$(elem).attr('size', 2);  // set the width which you need
						$(elem).trigger('jqGrid.dataInit', [model, elem]);
						$(elem).trigger('jqGrid.' + model.name + '.dataInit', [model, elem]);
					}
				}
			});
		});
	},

	activateDatePicker: function(e, model, elm) {
		if (model.formatter && model.formatter == 'date') {
			$(elm).addClass('datepicker');
		}
	}

};

jQuery(document).ready(function ($) {
	AgereGrid.attachEvents();

	// @todo Повішати на подію яка виникає після оновлення контенту через ajax - "refresh-content" element
	$('.ui-jqgrid-btable').bind('jqGrid.loadComplete', function() {
		AgereGrid.attachEvents(); // reattach print barcode button
	});
});