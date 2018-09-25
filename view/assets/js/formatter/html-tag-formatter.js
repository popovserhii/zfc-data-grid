class DataGrid_HtmlTagFormatter extends DataGrid_AbstractFormatter {

  static get ROW_ID_PLACEHOLDER() {
    return ':rowId:';
  }

  constructor() {
    super();

    /**
     * @var array
     */
    this.validRenderers = [
      'jqGrid',
      'bootstrapTable',
    ];

    /**
     * @var string
     */
    this.name = 'span';

    /**
     * @var AbstractColumn[]
     */
    this.linkColumnPlaceholders = [];

    /**
     * @var Array
     */
    this.attributes = [];
  }

  /**
   * @param name
   */
  setName(name) {
    this.name = name;
  }

  /**
   * @return string
   */
  getName() {
    return this.name;
  }

  /**
   * Set a HTML attributes.
   *
   * @param {string} name
   * @param {string} value
   */
  setAttribute(name, value) {
    this.attributes[name] = value;
  }

  /**
   * Get a HTML attribute.
   *
   * @param {string} name
   *
   * @return string
   */
  getAttribute(name) {
    if (this.attributes[name] === undefined) {
      return '';

    }
    return this.attributes[name];

  }

  /**
   * Removes an HTML attribute.
   *
   * @param {string} name
   */
  removeAttribute(name) {
    if (this.attributes[name] !== undefined) {
      delete this.attributes[name];
    }
  }

  /**
   * Get all HTML attributes.
   *
   * @return Array
   */
  getAttributes() {
    return this.attributes;
  }

  /**
   * Set the link.
   *
   * @param {string} href
   */
  setLink(href) {
    this.setAttribute('href', href);
  }

  /**
   * @return string
   */
  getLink() {
    return this.getAttribute('href');
  }

  /**
   * Get the column row value placeholder
   * $fmt->setLink('/myLink/something/'.$fmt->getColumnValuePlaceholder($myCol));.
   *
   * @param column
   *
   * @return string
   */
  /*getColumnValuePlaceholder(column) {
    this.linkColumnPlaceholders.push(column);

    return ':' + column + ':';
  }*/

  /**
   * @param columns
   * @returns {DataGrid_HtmlTagFormatter}
   */
  setLinkColumnPlaceholders(columns) {
    this.linkColumnPlaceholders = columns;

    return this;
  }

  /**
   * @return []
   */
  getLinkColumnPlaceholders() {
    return this.linkColumnPlaceholders;
  }

  /**
   * Returns the rowId placeholder.
   *
   * @return string
   */
  getRowIdPlaceholder() {
    return DataGrid_HtmlTagFormatter.ROW_ID_PLACEHOLDER;
    //return self::ROW_ID_PLACEHOLDER;
  }

  /**
   * @param column
   *
   * @return string
   */
  getFormattedValue(column) {
    // @see https://stackoverflow.com/a/31007976/1335142
    let r = function (p, c) {
      return p.replace(/%s/, c)
    };

    let row = this.getRowData();

    return [
      this.getName(),
      this.getAttributesString(column),
      row[column],
      this.getName()
    ].reduce(r, '<%s %s>%s</%s>');
  }


  /**
   * Get the string version of the attributes.
   *
   * @param column
   *
   * @return string
   */
  getAttributesString(column) {
    let attributes = [];
    for (let attrKey in this.getAttributes()) {
      let attrValue = this.getAttributes()[attrKey];
      if ('href' === attrKey) {
        attrValue = this.getLinkReplaced(column);
      }
      attributes.push(attrKey + '="' + attrValue + '"');
    }

    return attributes.join(' ');
  }

  /**
   * This is needed public for rowClickAction...
   *
   * @param column
   *
   * @return string
   */
  getLinkReplaced(column) {
    let row = this.getRowData();

    let link = this.getLink();
    if (link === '') {
      return row[column];
    }

    // Replace placeholders
    if (link.indexOf(DataGrid_HtmlTagFormatter.ROW_ID_PLACEHOLDER) !== false) {
      let id = '';
      if (row['idConcated'] !== undefined) {
        id = row['idConcated'];
      }
      link = link.replace(DataGrid_HtmlTagFormatter.ROW_ID_PLACEHOLDER, encodeURIComponent(id));
    }

    for (let col of this.getLinkColumnPlaceholders()) {
      link = link.replace(':' + col + ':', encodeURIComponent(row[col]));
    }

    return link;
  }
}
