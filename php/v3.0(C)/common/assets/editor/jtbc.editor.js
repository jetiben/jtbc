var jtbc = window.jtbc || {};
jtbc.editor = {
  baseHref: null,
  replace: function(argStr)
  {
    var str = argStr;
    var tthis = this;
    tinymce.remove();
    tinymce.init({
      autosave_ask_before_unload: false,
      branding: false,
      document_base_url: tthis.baseHref,
      selector: 'textarea.editor',
      height: 300,
      plugins: [
        "advlist autolink autosave link image lists charmap preview hr anchor pagebreak",
        "searchreplace wordcount code fullscreen insertdatetime media nonbreaking",
        "table contextmenu directionality textcolor paste textcolor colorpicker textpattern"
      ],
      toolbar1: "undo redo | bold italic underline strikethrough removeformat | subscript superscript | alignleft aligncenter alignright alignjustify | forecolor backcolor formatselect fontselect",
      toolbar2: "table searchreplace | ltr rtl | bullist numlist | outdent indent | link unlink anchor image media | insertdatetime charmap hr nonbreaking pagebreak | preview fullscreen code",
      menubar: false,
      toolbar_items_size: 'small',
      language:'zh_CN'
    });
    return null;
  },
  getHTML: function(argObj, argName)
  {
    var myObj = argObj;
    var myName = argName;
    return tinymce.get('editor-' + myName).getContent();
  },
  insertHTML: function(argObj, argName, argContent)
  {
    var myObj = argObj;
    var myName = argName;
    var myContent = argContent;
    return tinymce.get('editor-' + myName).insertContent(myContent);
  }
};