jtbc.console.manage = {
  obj: null,
  parent: jtbc.console,
  param: [],
  ready: function()
  {
    var tthis = this;
    tthis.parent.lib.initMainCommon(tthis);
  }
}.ready();