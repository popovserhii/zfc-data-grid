class DataGrid_LinkFormatter extends DataGrid_HtmlTagFormatter {
  constructor() {
    super();

    /**
     * @var Object
     */
    this.attributes = {
      'href': ''
    };

    /**
     * @var String
     */
    this.name = 'a';
  }
}
