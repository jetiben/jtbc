jtbc.console.managerapi = {
  obj: null,
  parent: jtbc.console,
  param: [],
  initList: function()
  {
    var tthis = this;
    var substanceObj = tthis.obj.find('.substance');
    var hotAdd = function(argId)
    {
      var id = argId;
      var url = tthis.parent.param['root'] + substanceObj.attr('genre') + '/' + substanceObj.attr('filename') + '?type=action&action=hot&id=' + encodeURIComponent(id);
      $.get(url, function(data){});
    };
    var reloadContent = function()
    {
      var getParam = 'filegroup=' + encodeURIComponent(substanceObj.find('.option').attr('val')) + '&sort=' + encodeURIComponent(substanceObj.find('.sortselect').attr('sort')) + '&keyword=' + encodeURIComponent(substanceObj.find('input.keyword').val());
      substanceObj.attr('reloadparam', getParam).trigger('reload');
    };
    substanceObj.find('input[name=\'material\']').on('update', function(){
      var thisObj = $(this);
      var selectedObj = new Array();
      substanceObj.find('.material_list').find('.item.on').each(function(){ selectedObj.push($(this).attr('filejson')); });
      thisObj.val(JSON.stringify(selectedObj));
    });
    substanceObj.find('.material_list').on('click', '.item', function(){
      var thisObj = $(this);
      if (substanceObj.attr('selectmode') != 'multiple') thisObj.parent().find('.item').removeClass('on');
      if (thisObj.hasClass('on')) thisObj.removeClass('on');
      else
      {
        thisObj.addClass('on');
        hotAdd(thisObj.attr('rsid'));
      };
      substanceObj.find('input[name=\'material\']').trigger('update');
    });
    substanceObj.find('.searchbox').find('input.search').on('click', function(){ reloadContent(); });
    substanceObj.find('.sortselect').find('li').on('click', function(){
      substanceObj.find('.sortselect').attr('sort', $(this).attr('sort'));
      reloadContent();
    });
    substanceObj.find('.option').find('label').on('click', function(){
      var thisObj = $(this);
      thisObj.parent().attr('val', thisObj.attr('val'));
      reloadContent();
    });
    substanceObj.find('.option').each(function(){
      var thisObj = $(this);
      thisObj.find('label[val=\'' + thisObj.attr('val') + '\']').addClass('on');
    });
  },
  initCommon: function()
  {
    var tthis = this;
    tthis.obj = $('.material.managerapi');
  },
  ready: function()
  {
    var tthis = this;
    tthis.initCommon();
    var myModule = tthis.obj.find('.substance').attr('module');
    if (myModule == 'list') tthis.initList();
  }
}.ready();