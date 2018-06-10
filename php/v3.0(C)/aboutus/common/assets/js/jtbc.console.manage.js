jtbc.console.manage = {
  obj: null,
  parent: jtbc.console,
  para: [],
  ready: function()
  {
    var tthis = this;
    tthis.parent.lib.initMainCommon(tthis);
  }
}.ready();