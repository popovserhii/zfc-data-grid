AgereGrid = {
	body: $('body'),
	onlyOnce: {},

	attachEvents: function () {
		this.attachOnSelectRow();
		this.attachOnTabActivate();
		this.attachDataInitTrigger();
		this.attachChangeSearchInputId();
		this.attachActivateModelOptions();
		this.attachActivateRowDatePicker();
		this.attachActivateSearchDatePicker();
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

	attachChangeSearchInputId: function () {
		//$('#cartItem_grid').bind('jqGridAfterGridComplete', this.actualiseTotalPrice);
		this.body.bind('jqGrid.loadComplete', this.changeSearchInputId);
	},

	attachActivateModelOptions: function () {
		if (this.onlyOnce['attachActivateModelOptions'] == undefined) {
			var grids = $('[id$="_grid"]');
			// Remove handler from existing elements
			grids.off('jqGridGridComplete', this.activateModelOptions);

			// Re-add event handler for all matching elements
			grids.on('jqGridGridComplete', this.activateModelOptions);

			this.onlyOnce['attachActivateModelOptions'] = true;
		}
	},

	attachActivateRowDatePicker: function () {
		// Remove handler from existing elements
		this.body.off('jqGrid.dataInit', this.activateRowDatePicker);

		// Re-add event handler for all matching elements
		this.body.on('jqGrid.dataInit', this.activateRowDatePicker);
	},

	attachActivateSearchDatePicker: function () {
		if (this.onlyOnce['attachActivateSearchDatePicker'] == undefined) {
			var grids = $('[id$="_grid"]');
			// Remove handler from existing elements
			//grids.off('jqGrid.activateModelOptions', this.activateSearchDatePicker);

			// Re-add event handler for all matching elements
			grids.on('jqGrid.activateModelOptions', this.activateSearchDatePicker);
			//grids.on('beforeProcessing', this.activateSearchDatePicker);

			this.onlyOnce['attachActivateSearchDatePicker'] = true;
		}
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
					dataInit: function (elm) {
						//$(elem).attr('size', 2);  // set the width which you need
						$(elm).trigger('jqGrid.dataInit', [model, elm]);
						$(elm).trigger('jqGrid.' + model.name + '.dataInit', [model, elm]);
					}
				}
			});
		});
	},

	changeSearchInputId: function() {
		var grid = $(arguments[0].target);
	},

	activateModelOptions: function() {
		var self = AgereGrid;
		var grid = $(arguments[0].target);
		var hash = 'activateModelOptions-' + grid.attr('id');

		//console.log(arguments);

		if (self.onlyOnce[hash] == undefined) {
			var colModels = grid.jqGrid('getGridParam', 'colModel');

			$.each(colModels, function (i, model) {
				//console.log(model);
				grid.trigger('jqGrid.activateModelOptions', [model]);
			});

			self.onlyOnce[hash] = true;
		}
	},

	activateRowDatePicker: function(e, model, element) {
		if (model.formatter && model.formatter == 'date') {
			var elm = $(element);
			if (!elm.hasClass('datepicker')) {
				elm.addClass('datepicker');
			}
		}
	},

	activateSearchDatePicker: function(e, model) {
		//console.log(model);
		//console.log('-----------------------');
		//console.log(elm);
		if (model.formatter && model.formatter == 'date') {
			var grid = $(arguments[0].target);

			//console.log(grid, model);
			//console.log('[name=' + model.name + ']');
			//console.log(grid.closest('.ui-jqgrid-view'));

			var input = grid.closest('.ui-jqgrid-view').find('[name="' + model.name + '"]');
			//console.log(input);
			input/*.attr('id', grid.attr('id') + '_search_' + model.name)*/.addClass('datepicker').change(function() {
				grid[0].triggerToolbar();
			});
			//$('#list').jqGrid('filterToolbar', { searchOnEnter: true, enableClear: false });

			/*grid.setColProp(model.name, {
				searchoptions: {
					//sopt: xxxx,
					//autosearch: true,
					//searchOnEnter: false,
					dataInit: function (elm) {
						console.log(elm);
						$(elm).addClass('datepicker');
					},
					dataEvents: [
						{
							type: 'change',
							fn: function(e) {
								console.log('change-fn');
								grid[0].triggerToolbar();
							}
						}
					]
				}
			});*/
		}
	}

};

jQuery(document).ready(function ($) {
	AgereGrid.attachEvents();

	//$('[id$="_grid"]').bind('jqGrid.loadComplete', function() {
	//	AgereGrid.attachEvents(); // reattach print barcode button
	//});
});