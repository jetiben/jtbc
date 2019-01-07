jtbc.console.manage = {
  obj: null,
  parent: jtbc.console,
  param: [],
  initList: function()
  {
    var tthis = this;
    tthis.obj.find('span.mainlink').click(function(){
      var thisObj = $(this);
      var fileURL = thisObj.attr('fileurl');
      if (!tthis.parent.parent.isAbsoluteURL(fileURL)) fileURL = tthis.obj.attr('folder') + fileURL;
      tthis.parent.lib.previewAtt(thisObj.attr('filetype'), thisObj.attr('filename'), fileURL, tthis.obj.attr('text-preview-link'), '0');
    });
    tthis.obj.find('button.add').click(function(){
      var thisObj = $(this);
      if (!thisObj.hasClass('lock')) tthis.obj.find('.upload').trigger('click');
    });
    tthis.obj.find('.upload').on('change', function(){
      var thisObj = $(this);
      var url = tthis.param['fileurl'] + '?type=action&action=add';
      if (thisObj.attr('uploading') != 'true')
      {
        thisObj.attr('uploading', 'true');
        tthis.obj.find('button.add').addClass('lock');
        tthis.parent.lib.fileUp(this, tthis.obj.find('.fileup'), url, function(){ if (tthis.obj.find('.fileup').find('.item.error').length == 0) tthis.parent.loadMainURLRefresh(); });
      };
    });
  },
  initEdit: function()
  {
    var tthis = this;
    tthis.obj.find('button.replace').click(function(){
      var thisObj = $(this);
      if (!thisObj.hasClass('lock')) tthis.obj.find('.upload').trigger('click');
    });
    tthis.obj.find('.upload').on('change', function(){
      var thisObj = $(this);
      var btnObj = tthis.obj.find('button.replace');
      var url = tthis.param['fileurl'] + '?type=action&action=replace&id=' + thisObj.attr('rsid');
      if (thisObj.attr('uploading') != 'true')
      {
        thisObj.attr('uploading', 'true');
        btnObj.addClass('lock').html(btnObj.attr('uploading'));
        if (this.files.length == 1)
        {
          tthis.parent.lib.fileUpSingle(this.files[0], url, function(result){
            if (result.find('result').attr('status') == '1') tthis.parent.loadMainURLRefresh();
            else
            {
              btnObj.removeClass('lock').html(btnObj.attr('text'));
              tthis.parent.lib.popupAlert(result.find('result').attr('message'), thisObj.attr('text-ok'), function(){ thisObj.attr('uploading', 'false'); });
            };
          }, function(percent) {});
        };
      };
    });
  },
  ready: function()
  {
    var tthis = this;
    tthis.parent.lib.initMainCommon(tthis);
  }
}.ready();