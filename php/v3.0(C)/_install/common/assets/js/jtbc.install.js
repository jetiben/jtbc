var jtbc = window.jtbc || {};
jtbc.install = {
  initInstallEvents: function()
  {
    var obj = $('.install');
    var gotoStep = function(argNum)
    {
      var num = argNum;
      obj.find('div.msg').text('');
      obj.find('.step').find('span').removeClass('on').eq(num).addClass('on');
      obj.find('.tab_content').find('.item').removeClass('on').eq(num).addClass('on');
      obj.find('.handle').find('.item').removeClass('on').eq(num).addClass('on');
    };
    obj.find('input.agree').on('click', function(){
      var thisObj = $(this);
      if (thisObj.is(':checked')) obj.find('.step-1-next').removeClass('hide');
      else obj.find('.step-1-next').addClass('hide');
    });
    obj.find('button.submit').on('click', function(){ obj.find('.step-3-done').trigger('click'); });
    obj.find('.step-1-next').on('click', function(){ gotoStep(1); });
    obj.find('.step-2-prev').on('click', function(){ gotoStep(0); });
    obj.find('.step-2-next').on('click', function(){ gotoStep(2); });
    obj.find('.step-3-prev').on('click', function(){ gotoStep(1); });
    obj.find('.step-3-done').on('click', function(){
      var thisObj = $(this);
      if (!thisObj.hasClass('lock'))
      {
        thisObj.addClass('lock');
        obj.find('.handle').find('.item').removeClass('on');
        obj.find('div.msg').text(obj.find('div.msg').attr('loading'));
        var formObj = obj.find('form.form');
        $.post(formObj.attr('action'), formObj.serialize(), function(data){
          var dataObj = $(data);
          if (dataObj.find('result').attr('status') == '0')
          {
            thisObj.removeClass('lock');
            gotoStep(dataObj.find('result').attr('param'));
            obj.find('div.msg').text(dataObj.find('result').attr('message'));
          }
          else if (dataObj.find('result').attr('status') == '1')
          {
            location.href = dataObj.find('result').attr('param');
          };
        });
      };
    });
  },
  ready: function()
  {
    var tthis = this;
    tthis.initInstallEvents();
  }
};