class DataGrid_AbstractFormatter {
  constructor() {
    this.data = [];

    /** @var string */
    this.rendererName = '';

    /** @var [] */
    this.validRenderers = [];

    if (new.target === DataGrid_AbstractFormatter) {
      throw new TypeError('Cannot construct Abstract instances directly');
    }
    // or maybe test typeof this.method === undefined

    /**
     * Get formatted value
     *
     * @return string
     */
    if (this.getFormattedValue === 'function') {
      throw new TypeError('Must override method "getFormattedValue(column)"');
    }
  }


  /**
   * @param data
   */
  setRowData(data) {
    this.data = data;
  }

  /**
   * @return array
   */
  getRowData() {
    return this.data;
  }

  /**
   * @param name
   */
  setRendererName(name = null) {
    this.rendererName = name;
  }

  /**
   * @return string
   */
  getRendererName() {
    return this.rendererName;
  }

  /**
   * @param validRendrerers
   */
  setValidRendererNames(validRendrerers) {
    this.validRenderers = validRendrerers;
  }

  /**
   * @return array
   */
  getValidRendererNames() {
    return this.validRenderers;
  }

  /**
   * @return boolean
   */
  isApply() {
    if (this.validRenderers.includes(this.getRendererName())) {
      return true;
    }

    return false;
  }

  /**
   * @param column
   *
   * @return string
   */
  format(column) {
    let data = this.getRowData();
    if (this.isApply() === true) {
      return this.getFormattedValue(column);
    }

    return data[column.getUniqueId()];
  }
}
