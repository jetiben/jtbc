jtbc.console.manage = {
  obj: null,
  parent: jtbc.console,
  para: [],
  initList: function()
  {
    var tthis = this;
    tthis.obj.find('input.add').on('blur', function(){
      var thisObj = $(this);
      var trObj = thisObj.parent().parent();
      thisObj.addClass('hide');
      trObj.find('span.mainlink').find('label').removeClass('hide');
      if (thisObj.val() != thisObj.attr('rsvalue'))
      {
        var url = tthis.para['fileurl'] + '?type=action&action=addfolder&name=' + encodeURIComponent(thisObj.val()) + '&path=' + encodeURIComponent(thisObj.attr('rspath'));
        $.get(url, function(data){ tthis.parent.loadMainURLRefresh(); });
      };
    });
    tthis.obj.find('input.edit').on('blur', function(){
      var thisObj = $(this);
      var trObj = thisObj.parent().parent();
      thisObj.addClass('hide');
      trObj.find('span.mainlink').find('label').removeClass('hide');
      trObj.find('span.mainlink').find('a.link').addClass('block');
      if (thisObj.val() != thisObj.attr('rsvalue'))
      {
        var url = tthis.para['fileurl'] + '?type=action&action=rename&name=' + encodeURIComponent(thisObj.val()) + '&path=' + encodeURIComponent(thisObj.attr('rspath'));
        $.get(url, function(data){ tthis.parent.loadMainURLRefresh(); });
      };
    });
    tthis.obj.find('span.mainlink').find('icon.file').on('mouseover', function(){
      var thisObj = $(this);
      if (thisObj.attr('titleloading') != 'true')
      {
        thisObj.attr('titleloading', 'true');
        var url = tthis.para['fileurl'] + '?type=getinfo&val=' + thisObj.attr('val');
        $.get(url, function(data){ thisObj.attr('title', $(data).find('result').attr('message')); });
      };
    });
    tthis.obj.find('icon.edit').click(function(){
      var thisObj = $(this);
      var trObj = thisObj.parent().parent().parent();
      trObj.find('span.mainlink').find('label').addClass('hide');
      trObj.find('span.mainlink').find('a.link').removeClass('block');
      trObj.find('input.edit').removeClass('hide').each(function(){ this.select(); });
    });
    tthis.obj.find('icon.delete').click(function(){
      var thisObj = $(this);
      tthis.parent.lib.popupConfirm(thisObj.attr('confirm_text'), thisObj.attr('confirm_b2'), thisObj.attr('confirm_b3'), function(argObj){
        var myObj = argObj;
        var url = tthis.para['fileurl'] + '?type=action&action=delete&path=' + encodeURIComponent(thisObj.attr('rspath'));
        $.get(url, function(data){ tthis.parent.loadMainURLRefresh(); myObj.parent().find('button.b3').click(); });
      });
    });
    tthis.obj.find('button.addfolder').click(function(){
      tthis.obj.find('tr.add').removeClass('hide');
      tthis.obj.find('input.add').each(function(){ this.select(); });
    });
    tthis.obj.find('button.addfile').click(function(){
      var thisObj = $(this);
      if (!thisObj.hasClass('lock')) tthis.obj.find('.upload').trigger('click');
    });
    tthis.obj.find('.upload').on('change', function(){
      var thisObj = $(this);
      var url = tthis.para['fileurl'] + '?type=action&action=addfile&path=' + encodeURIComponent(thisObj.attr('rspath'));
      if (thisObj.attr('uploading') != 'true')
      {
        thisObj.attr('uploading', 'true');
        tthis.obj.find('button.addfile').addClass('lock');
        tthis.parent.lib.fileUp(this, tthis.obj.find('.fileup'), url, function(){ if (tthis.obj.find('.fileup').find('.item.error').length == 0) tthis.parent.loadMainURLRefresh(); });
      };
    });
  },
  initEdit: function()
  {
    var tthis = this;
    tthis.para['codemirror-timeout'] = setTimeout(function(){
      tthis.para['codemirror'] = CodeMirror.fromTextArea(document.getElementById('codemirror'), {mode: tthis.obj.attr('filemode'), lineNumbers: true, lineWrapping: true, styleActiveLine: true, theme: 'monokai', extraKeys: { 'F11': function(cm) { cm.setOption('fullScreen', !cm.getOption('fullScreen')); }, 'Esc': function(cm) { if (cm.getOption('fullScreen')) cm.setOption('fullScreen', false); }}});
    }, 50);
    tthis.obj.find('button.savefile').click(function(){
      var thisObj = $(this);
      if (!thisObj.hasClass('lock'))
      {
        thisObj.addClass('lock');
        var fileContent = tthis.obj.find('#codemirror').val();
        if (tthis.para['codemirror']) fileContent = tthis.para['codemirror'].getValue();
        var formObj = tthis.obj.find('form.savefile');
        var url = tthis.para['fileurl'] + formObj.attr('action');
        $.post(url, 'content=' + encodeURIComponent(fileContent), function(data){
          var dataObj = $(data);
          thisObj.removeClass('lock');
          tthis.parent.lib.popupAlert(dataObj.find('result').attr('message'), formObj.attr('text-ok'), function(){});
        });
      };
    });
  },
  ready: function()
  {
    var tthis = this;
    tthis.parent.lib.initMainCommon(tthis);
  }
}.ready();