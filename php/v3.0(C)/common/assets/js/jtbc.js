var jtbc = {
  getTimeString: function()
  {
    var tthis = this;
    var currentTime = new Date();
    var timeString = currentTime.getFullYear().toString();
    timeString += tthis.zeroRepair(currentTime.getMonth() + 1, 2);
    timeString += tthis.zeroRepair(currentTime.getDate(), 2);
    timeString += tthis.zeroRepair(currentTime.getHours(), 2);
    timeString += tthis.zeroRepair(currentTime.getMinutes(), 2);
    timeString += tthis.zeroRepair(currentTime.getSeconds(), 2);
    timeString += tthis.zeroRepair(currentTime.getMilliseconds(), 3);
    return timeString;
  },
  getTimeStringAndRandom: function()
  {
    var tthis = this;
    var timeStringAndRandom = tthis.getTimeString();
    timeStringAndRandom += tthis.zeroRepair(Math.floor(Math.random() * 100000000000), 11);
    return timeStringAndRandom;
  },
  htmlEncode: function(argStrers)
  {
    var strers = argStrers;
    strers = strers.replace(/(\&)/g, '&amp;');
    strers = strers.replace(/(\>)/g, '&gt;');
    strers = strers.replace(/(\<)/g, '&lt;');
    strers = strers.replace(/(\")/g, '&quot;');
    return strers;
  },
  isAbsoluteURL: function(argURL)
  {
    var bool = false;
    var url = argURL;
    if (url.substring(0, 1) == '/' || url.substring(0, 5) == 'http:' || url.substring(0, 6) == 'https:') bool = true;
    return bool;
  },
  zeroRepair: function(argNum, argLength)
  {
    var num = argNum;
    var length = argLength;
    var result = null;
    if (!isNaN(num) && !isNaN(length))
    {
      result = (Array(length).join('0') + num).slice(-length)
    };
    return result;
  }
};