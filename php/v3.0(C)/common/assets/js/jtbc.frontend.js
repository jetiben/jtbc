var jtbc = window.jtbc || {};
jtbc.frontend = {
  param: [],
  bindEventsByMode: function(argObj)
  {
    var tthis = this;
    var obj = argObj;
    obj.find('*[mode]').each(function(){
      var thisObj = $(this);
      if (thisObj.attr('modebinded') != 'true')
      {
        thisObj.attr('modebinded', 'true');
        if (thisObj.attr('mode') == 'ajaxpost')
        {
          thisObj.find('button.submit').on('click', function(){
            var myObj = $(this);
            if (!myObj.hasClass('lock'))
            {
              myObj.addClass('lock');
              var callback = eval(myObj.attr('callback') || 'alert');
              var msgCallback = eval(myObj.attr('msgcallback') || 'alert');
              $.post(thisObj.attr('action'), thisObj.serialize(), function(data){
                var dataObj = $(data);
                myObj.removeClass('lock');
                if (dataObj.find('result').attr('status') != '1') msgCallback(dataObj.find('result').attr('message').split('|')[0]);
                else
                {
                  thisObj.each(function(){ this.reset(); });
                  callback(dataObj.find('result').attr('message'));
                };
              });
            };
          });
        }
        else if (thisObj.attr('mode') == 'pitchon')
        {
          var pitchon = thisObj.attr('upitchon') || thisObj.attr('pitchon');
          if (pitchon) thisObj.find(pitchon).addClass('on');
        }
        else if (thisObj.attr('mode') == 'submenu')
        {
          thisObj.on('click', function(){
            var myObj = $(this);
            if (!myObj.hasClass('on'))
            {
              myObj.addClass('on');
              myObj.parent().find(myObj.attr('selector')).addClass('on');
            }
            else
            {
              myObj.removeClass('on');
              myObj.parent().find(myObj.attr('selector')).removeClass('on');
            };
          });
        };
      };
    });
  },
  ready: function()
  {
    var tthis = this;
    var obj = $(document.body);
    obj.find('dfn').each(function(){
      var myObj = $(this);
      if (myObj.attr('call')) eval(myObj.attr('call'));
    });
    tthis.bindEventsByMode(obj);
    $(document).on('scroll', function(){ obj.find('.header').css({'left': -$(document).scrollLeft() || 'auto'}); });
  }
};