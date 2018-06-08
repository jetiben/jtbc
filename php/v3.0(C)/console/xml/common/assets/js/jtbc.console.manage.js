jtbc.console.manage = {
  obj: null,
  parent: jtbc.console,
  para: [],
  initList: function()
  {
    var tthis = this;
    tthis.parent.lib.initSearchBoxEvents(tthis.obj);
    if (tthis.obj.find('.CodeMirrorContent').length == 1)
    {
      var currentSymbol = tthis.obj.find('btn.fileselect').attr('symbol');
      tthis.para['codemirror-timeout'] = setTimeout(function(){
        var codemirrorOption = {
          mode: 'htmlmixed',
          lineNumbers: true,
          lineWrapping: true,
          styleActiveLine: true,
          theme: 'monokai',
          extraKeys: { 'F11': function(cm) { cm.setOption('fullScreen', !cm.getOption('fullScreen')); }, 'Esc': function(cm) { if (cm.getOption('fullScreen')) cm.setOption('fullScreen', false); }}
        };
        if (typeof Promise !== 'undefined')
        {
          var currentHintBase = ['breadcrumb', 'createURL', 'encodeText', 'encodeTextArea', 'formatDate', 'formatFileSize', 'formatLine', 'get', 'getActualRoute', 'getDateTime', 'getJsonPara', 'getLeft', 'getLeftB', 'getLRStr', 'getNum', 'getPagePara', 'getPageTitle', 'getPara', 'getParameter', 'getRandomString', 'getRemortIP', 'getRepeatedString', 'getRight', 'getSwapString', 'htmlEncode', 'pagi', 'replaceQuerystring', 'take', 'takeAndFormat', 'takeByNode', 'transfer', 'xmlSelect'];
          currentHintBase.push('$address', '$author', '$att', '$category', '$color', '$content', '$date', '$description', '$email', '$file', '$group', '$height', '$keyword', '$keywords', '$intro', '$id', '$image', '$language', '$leader', '$length', '$linkurl', '$mobile', '$name', '$phone', '$photo', '$position', '$rank', '$size', '$source', '$subtitle', '$status', '$template', '$title', '$topic', '$time', '$type', '$url', '$upload', '$userip', '$width');
          var currentHintFunction = function(cm, option) {
            return new Promise(function(accept)
            {
              setTimeout(function() {
                var cursor = cm.getCursor(), line = cm.getLine(cursor.line);
                var start = cursor.ch, end = cursor.ch;
                while (start && /\$|\w/.test(line.charAt(start - 1))) --start;
                while (end < line.length && /\w/.test(line.charAt(end))) ++end;
                var word = line.slice(start, end);
                if (word)
                {
                  var showlist = [];
                  showlist.push(word);
                  for (var i = 0; i < currentHintBase.length; i++)
                  {
                    if (currentHintBase[i].indexOf(word) == 0 && currentHintBase[i] != word) showlist.push(currentHintBase[i]);
                  };
                  if (showlist.length >= 2)
                  {
                    return accept({list: showlist, from: CodeMirror.Pos(cursor.line, start), to: CodeMirror.Pos(cursor.line, end)});
                  }
                  else return accept(null);
                };
                return accept(null);
              }, 100);
            });
          };
          if (currentSymbol.indexOf('.tpl.') != -1) codemirrorOption.hintOptions = {hint: currentHintFunction};
        };
        tthis.para['codemirror'] = CodeMirror.fromTextArea(document.getElementById('codemirror'), codemirrorOption);
        if (typeof codemirrorOption.hintOptions == 'object')
        {
          tthis.para['codemirror'].on('keypress', function() { tthis.para['codemirror'].showHint(); });
        };
      }, 50);
    };
    tthis.obj.find('rightarea').find('select[name=\'node\']').on('change', function(){
      var thisObj = $(this);
      var url = tthis.para['fileurl'] + thisObj.attr('action') + '&node=' + encodeURIComponent(thisObj.val());
      tthis.parent.loadMainURL(url);
    });
    tthis.obj.find('span.nodeadd').click(function(){
      var thisObj = $(this);
      if (thisObj.attr('loading') != 'true')
      {
        thisObj.attr('loading', 'true');
        var url = tthis.para['fileurl'] + '?type=add&symbol=' + encodeURIComponent(thisObj.attr('symbol'));
        $.get(url, function(data){
          var dataObj = $(data);
          if (dataObj.find('result').attr('status') == '1')
          {
            var pageObj = tthis.parent.lib.popupPage(dataObj.find('result').text());
            pageObj.find('.tinyform').find('button.submit').attr('message', 'custom').on('message', function(){
              pageObj.find('span.tips').addClass('h').html($(this).attr('msg').split('|')[0]);
            }).attr('done', 'custom').on('done', function(){
              tthis.parent.loadMainURL(tthis.para['fileurl'] + '?type=list&symbol=' + encodeURIComponent(thisObj.attr('symbol')) + '&node=' + encodeURIComponent(pageObj.find('input[name=\'nodename\']').val()));
              pageObj.find('span.close').trigger('click');
            });
          };
          thisObj.attr('loading', 'false');
        });
      };
    });
    tthis.obj.find('btn.fileselect').click(function(){
      var thisObj = $(this);
      var loadFileList = function(argObj)
      {
        var obj = argObj;
        var olObj = obj.parent().parent().parent();
        olObj.find('select').attr('disabled', 'disabled');
        var currentGenre = olObj.find('select[name=\'genre\']').val();
        var currentChildGenreObj = olObj.find('select[name=\'child-genre\']');
        if (currentChildGenreObj.length >= 1) currentGenre = currentChildGenreObj.last().attr('parent') + currentChildGenreObj.last().val();
        var loadFileURL = tthis.para['fileurl'] + '?type=fileSelectFile&genre=' + encodeURIComponent(currentGenre) + '&mold=' + encodeURIComponent(olObj.find('select[name=\'mold\']').val());
        $.get(loadFileURL, function(data){
          var dataObj = $(data);
          var hasFile = false;
          var fileSelectHTML = '<select class="s1 full" name="file">';
          olObj.find('select').removeAttr('disabled');
          if (dataObj.find('result').attr('status') == '1')
          {
            var fileAry = JSON.parse(dataObj.find('result').text());
            for (var i in fileAry)
            {
              hasFile = true;
              var currentFile = fileAry[i];
              fileSelectHTML += '<option value="' + tthis.parent.parent.htmlEncode(i) + '">' + tthis.parent.parent.htmlEncode(currentFile) + '</option>';
            };
            if (hasFile == false) fileSelectHTML += '<option value="">' + tthis.parent.parent.htmlEncode(olObj.attr('text-file-null')) + '</option>';
          };
          fileSelectHTML += '</select>';
          olObj.find('span.select-file').html(fileSelectHTML);
          if (hasFile == false) olObj.find('span.select-file').find('select').attr('disabled', 'disabled');
          olObj.find('span.select-file').find('select').find('option').each(function(){
            var optionObj = $(this);
            if (optionObj.val() == olObj.attr('symbol-p3')) optionObj.parent().val(olObj.attr('symbol-p3'));
          });
        });
      };
      var appendChildGenre = function(argGenre, argObj)
      {
        var hasChild = false;
        var genre = argGenre;
        var obj = argObj;
        var genreAry = tthis.para['genreAry'];
        var symbolP1Ary = tthis.para['symbolP1Ary'];
        var childAry = [];
        for (var i in genreAry)
        {
          var currentGenre = genreAry[i];
          if (i.indexOf(genre + '/') == 0)
          {
            var childGenre = i.substr(genre.length + 1);
            childAry[childGenre] = currentGenre;
            hasChild = true;
          };
        };
        if (hasChild == true)
        {
          var childGenreSelectHTML = '<select class="s1 full" name="child-genre" parent="' + tthis.parent.parent.htmlEncode(genre + '/') + '"><option value="">/</option>';
          for (var i in childAry)
          {
            var currentGenre = childAry[i];
            if (i.indexOf('/') == -1)
            {
              childGenreSelectHTML += '<option value="' + tthis.parent.parent.htmlEncode(i) + '">' + tthis.parent.parent.htmlEncode(currentGenre) + '</option>';
            };
          };
          childGenreSelectHTML += '</select>';
          var currentLiObj = obj.parent().parent();
          var childGenreIndex = 0;
          var childGenreHTML = '<li class="li-genre-child" text-child="' + tthis.parent.parent.htmlEncode(currentLiObj.attr('text-child')) + '"><h6>' + tthis.parent.parent.htmlEncode(currentLiObj.attr('text-child')) + '</h6><span class="select-genre-child">' + childGenreSelectHTML + '</span></li>';
          currentLiObj.after(childGenreHTML);
          currentLiObj.parent().find('.li-genre-child').each(function(){
            var thisObj = $(this);
            childGenreIndex += 1;
            if (thisObj.attr('bind') != 'true')
            {
              thisObj.attr('bind', 'true');
              thisObj.find('select').on('change', function(){
                var thisObj = $(this);
                thisObj.nextAll('li.li-genre-child').remove();
                appendChildGenre(thisObj.attr('parent') + thisObj.val(), thisObj);
              });
              if (typeof(symbolP1Ary) == 'object')
              {
                if (childGenreIndex <= symbolP1Ary.length)
                {
                  var currentOptionValue = symbolP1Ary[childGenreIndex];
                  thisObj.find('select').find('option').each(function(){
                    var optionObj = $(this);
                    if (optionObj.val() == currentOptionValue) optionObj.parent().val(currentOptionValue).trigger('change');
                  });
                };
              };
            };
          });
        };
        loadFileList(obj);
      };
      if (thisObj.attr('loading') != 'true')
      {
        thisObj.attr('loading', 'true');
        var url = tthis.para['fileurl'] + '?type=fileSelect&symbol=' + encodeURIComponent(thisObj.attr('symbol'));
        $.get(url, function(data){
          var dataObj = $(data);
          if (dataObj.find('result').attr('status') == '1')
          {
            var genreSelectHTML = '';
            var pageObj = tthis.parent.lib.popupPage(dataObj.find('result').text());
            pageObj.find('select[name=\'mold\']').val(pageObj.find('ol').attr('symbol-p2')).on('change', function(){ loadFileList($(this)); });
            pageObj.find('button.iselected').on('click', function(){
              var thisObj = $(this);
              var symbolP1 = '';
              var symbolP2 = '';
              var symbolP3 = '';
              var currentGenre = pageObj.find('select[name=\'genre\']').val();
              var currentChildGenreObj = pageObj.find('select[name=\'child-genre\']');
              if (currentChildGenreObj.length >= 1) currentGenre = currentChildGenreObj.last().attr('parent') + currentChildGenreObj.last().val();
              symbolP1 = currentGenre;
              if (currentGenre.charAt(currentGenre.length - 1) == '/') symbolP1 = currentGenre.substr(0, currentGenre.length - 1);
              symbolP2 = pageObj.find('select[name=\'mold\']').val();
              symbolP3 = pageObj.find('select[name=\'file\']').val();
              if (!symbolP3) tthis.parent.lib.popupMiniAlert(thisObj.attr('text-error-1'));
              else
              {
                pageObj.find('span.close').trigger('click');
                tthis.obj.find('.searchbox').find('input.keyword').val(symbolP1 + '.' + symbolP2 + '.' + symbolP3);
                tthis.obj.find('.searchbox').find('input.search').trigger('click');
              };
            });
            var fileSelectGenreURL = tthis.para['fileurl'] + '?type=fileSelectGenre';
            genreSelectHTML += '<select class="s1 full" name="genre"><option value="">/</option>';
            $.get(fileSelectGenreURL, function(data){
              var dataObj = $(data);
              if (dataObj.find('result').attr('status') == '1')
              {
                var genreAry = JSON.parse(dataObj.find('result').text());
                tthis.para['genreAry'] = genreAry;
                for (var i in genreAry)
                {
                  var currentGenre = genreAry[i];
                  if (i.indexOf('/') == -1)
                  {
                    genreSelectHTML += '<option value="' + tthis.parent.parent.htmlEncode(i) + '">' + tthis.parent.parent.htmlEncode(currentGenre) + '</option>';
                  };
                };
                genreSelectHTML += '</select>';
                pageObj.find('span.select-genre').html(genreSelectHTML);
                pageObj.find('span.select-genre').find('select').on('change', function(){
                  var thisObj = $(this);
                  pageObj.find('li.li-genre-child').remove();
                  appendChildGenre(thisObj.val(), thisObj);
                });
                var symbolP1 = pageObj.find('ol').attr('symbol-p1');
                if (symbolP1 != '')
                {
                  tthis.para['symbolP1Ary'] = symbolP1.split('/');
                  var symbolP1Ary = tthis.para['symbolP1Ary'];
                  if (symbolP1Ary.length >= 1) pageObj.find('span.select-genre').find('select').val(symbolP1Ary[0]).trigger('change');
                };
                loadFileList(pageObj.find('span.select-genre').find('select'));
              };
            });
          };
          thisObj.attr('loading', 'false');
        });
      };
    });
    tthis.obj.find('button.nodeedit').click(function(){
      var thisObj = $(this);
      if (!thisObj.hasClass('lock'))
      {
        thisObj.addClass('lock');
        if (tthis.para['codemirror']) tthis.obj.find('#codemirror').val(tthis.para['codemirror'].getValue());
        var formObj = tthis.obj.find('form.nodeedit');
        var url = tthis.para['fileurl'] + formObj.attr('action');
        $.post(url, formObj.serialize(), function(data){
          var dataObj = $(data);
          thisObj.removeClass('lock');
          tthis.parent.lib.popupAlert(dataObj.find('result').attr('message'), formObj.attr('text-ok'), function(){});
        });
      };
    });
    tthis.obj.find('button.nodedelete').click(function(){
      var thisObj = $(this);
      if (thisObj.attr('loading') != 'true')
      {
        var pageObj = tthis.parent.lib.popupConfirm(thisObj.attr('confirm_text'), thisObj.attr('confirm_b2'), thisObj.attr('confirm_b3'), function(argObj){
          var btnObj = argObj;
          var postData = 'symbol=' + encodeURIComponent(thisObj.attr('symbol')) + '&nodename=' + encodeURIComponent(thisObj.attr('nodename'));
          var url = tthis.para['fileurl'] + '?type=action&action=delete';
          thisObj.attr('loading', 'true');
          $.post(url, postData, function(data){
            var dataObj = $(data);
            thisObj.attr('loading', 'false');
            if (dataObj.find('result').attr('status') == '0')
            {
              btnObj.parent().find('button.b2').removeClass('lock');
              tthis.parent.lib.popupMiniAlert(dataObj.find('result').attr('message').split('|')[0]);
            }
            else if (dataObj.find('result').attr('status') == '1')
            {
              tthis.parent.loadMainURLRefresh();
              btnObj.parent().find('button.b3').click();
            };
          });
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