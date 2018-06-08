jtbc.console.manage = {
  obj: null,
  parent: jtbc.console,
  para: [],
  bindSelectPopedomEvents: function()
  {
    var tthis = this;
    var inputPopedomObj = tthis.obj.find('input[name=\'popedom\']');
    inputPopedomObj.on('update', function(){
      var popedomValue = '';
      var thisObj = $(this);
      tthis.obj.find('.popedom').find('input.genre').each(function(){
        var that = this;
        var thisObj = $(this);
        if (that.checked)
        {
          popedomValue += thisObj.val();
          popedomValue += ':';
          thisObj.parent().nextAll().each(function(){
            if ($(this).is('label')) $(this).find('input.genre_popedom').each(function(){ if (this.checked) popedomValue += this.value + ','; });
          });
          popedomValue += ':';
          thisObj.parent().parent().find('input.genre_category').each(function(){ popedomValue += $(this).val() + ','; });
          popedomValue += '|';
        };
      });
      thisObj.val(popedomValue);
    });
    tthis.obj.find('.popedom').find('input.genre').on('elder', function(){
      var that = this;
      var thisObj = $(this);
      if (that.checked)
      {
        if (thisObj.val().indexOf('/') != -1)
        {
          var genreArray = thisObj.val().split('/');
          for (var i = 0; i < genreArray.length; i ++)
          {
            var genreText = genreArray[i];
            if (i > 0)
            {
              for (var k = (i - 1); k >= 0; k --) genreText = genreArray[k] + '/' + genreText;
            };
            if (genreText != thisObj.val()) tthis.obj.find('.popedom').find('input[value=\'' + genreText + '\']').each(function(){ this.checked = true; });
          };
        };
      };
    });
    tthis.obj.find('.popedom').find('input.genre').click(function(){
      var that = this;
      var thisObj = $(this);
      if (that.checked)
      {
        thisObj.trigger('elder');
        thisObj.parent().parent().find('input.genre_popedom').each(function(){ this.checked = true; });
        thisObj.parent().parent().find('ul').find('input[type=\'checkbox\']').each(function(){ this.checked = true; });
      }
      else
      {
        thisObj.parent().parent().find('input.genre_popedom').each(function(){ this.checked = false; });
        thisObj.parent().parent().find('ul').find('input[type=\'checkbox\']').each(function(){ this.checked = false; });
      };
      inputPopedomObj.trigger('update');
    });
    tthis.obj.find('.popedom').find('input.genre_popedom').click(function(){
      var that = this;
      var thisObj = $(this);
      if (that.checked)
      {
        thisObj.parent().parent().find('input.genre').each(function(){ this.checked = true; $(this).trigger('elder'); });
      };
      inputPopedomObj.trigger('update');
    });
    tthis.obj.find('span.category').click(function(){
      var thisObj = $(this);
      var genre = thisObj.parent().find('input.genre').val();
      var genreCategory = thisObj.find('input.genre_category').val();
      var lang = tthis.parent.lib.getCheckBoxValue(tthis.obj.find('input[name=\'lang-select\']:checked'));
      if (thisObj.attr('loading') != 'true')
      {
        thisObj.attr('loading', 'true');
        var url = tthis.para['fileurl'] + '?type=category&genre=' + encodeURIComponent(genre);
        $.get(url, function(data){
          var dataObj = $(data);
          if (dataObj.find('result').attr('status') == '1')
          {
            thisObj.attr('loading', 'false');
            var pageObj = tthis.parent.lib.popupPage(dataObj.find('result').text());
            pageObj.find('.tab').find('.option label').each(function(){ if ($.inArray($(this).attr('val'), lang.split(',')) == -1) $(this).addClass('hide'); });
            pageObj.find('.tab').find('.option label:not(.hide)').eq(0).trigger('click');
            pageObj.find('input[name=\'category\']').each(function(){ if ($.inArray($(this).val(), genreCategory.split(',')) != -1) this.checked = true; });
            pageObj.find('input[name=\'category\']').on('click', function(){
              var that = this;
              var thisObj = $(this);
              if (!that.checked)
              {
                thisObj.parent().parent().find('input[name=\'category\']').each(function(){ this.checked = false; });
              }
              else
              {
                thisObj.parent().parent().find('input[name=\'category\']').each(function(){ this.checked = true; });
                pageObj.find('.popedom_category').find('li').has(thisObj).find('input[name=\'category\']').eq(0).each(function(){ this.checked = true; });
                pageObj.find('.popedom_category').find('dd').has(thisObj).find('input[name=\'category\']').eq(0).each(function(){ this.checked = true; });
              };
            });
            pageObj.find('button.b2').on('click', function(){
              pageObj.find('span.close').trigger('click');
              tthis.obj.find('.genre_category').val(tthis.parent.lib.getCheckBoxValue(pageObj.find('input[name=\'category\']:checked')));
              inputPopedomObj.trigger('update');
            });
          };
        });
      };
    });
    inputPopedomObj.trigger('update');
  },
  bindSelectLangEvents: function()
  {
    var tthis = this;
    tthis.obj.find('input[name=\'lang-select\']').on('click', function(){
      var that = this;
      var thisObj = $(this);
      if (!that.checked)
      {
        if (tthis.obj.find('input[name=\'lang-select\']:checked').length == 0)
        {
          that.checked = true;
          tthis.parent.lib.popupAlert(tthis.obj.attr('text-lang-1'), tthis.obj.attr('text-lang-ok'), function(){});
        };
      };
    });
    tthis.obj.find('input[name=\'lang\']').on('update', function(){
      tthis.obj.find('input[name=\'lang\']').val(tthis.parent.lib.getCheckBoxValue(tthis.obj.find('input[name=\'lang-select\']:checked')));
    });
  },
  initAdd: function()
  {
    var tthis = this;
    tthis.bindSelectPopedomEvents();
    tthis.bindSelectLangEvents();
    tthis.obj.find('.form_button').find('button.submit').on('before', function(){
      tthis.obj.find('input[name=\'lang\']').trigger('update');
    });
  },
  initEdit: function()
  {
    var tthis = this;
    tthis.bindSelectPopedomEvents();
    tthis.bindSelectLangEvents();
    tthis.obj.find('.form_button').find('button.submit').on('before', function(){
      tthis.obj.find('input[name=\'lang\']').trigger('update');
    });
  },
  ready: function()
  {
    var tthis = this;
    tthis.parent.lib.initMainCommon(tthis);
  }
}.ready();
