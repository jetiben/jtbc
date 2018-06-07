<?php
namespace jtbc;
class ui extends console\page {
  public static function consolePageInit()
  {
    parent::$checkCurrentGenre = false;
  }

  public static function ppGetFileJSON($argRs, $argPrefix = '')
  {
    $tmpstr = '';
    $rs = $argRs;
    $prefix = $argPrefix;
    if (is_array($rs))
    {
      $paraArray = array();
      $paraArray['filename'] = $rs[$prefix . 'topic'];
      $paraArray['filesize'] = $rs[$prefix . 'filesize'];
      $paraArray['filetype'] = $rs[$prefix . 'filetype'];
      $paraArray['filepath'] = $rs[$prefix . 'filepath'];
      $paraArray['fileurl'] = $rs[$prefix . 'fileurl'];
      $paraArray['filesizetext'] = base::formatFileSize(base::getNum($paraArray['filesize'], 0));
      $tmpstr = json_encode($paraArray);
    }
    return $tmpstr;
  }

  public static function moduleList()
  {
    $status = 1;
    $selectmode = 'single';
    $mode = base::getString(request::get('mode'));
    $keyword = base::getString(request::get('keyword'));
    $sort = base::getNum(request::get('sort'), 1);
    $filegroup = base::getNum(request::get('filegroup'), -1);
    if ($mode == 'multiple') $selectmode = 'multiple';
    $account = self::account();
    self::setPara('-keyword', $keyword);
    $variable['-selectmode'] = $selectmode;
    $variable['-filegroup'] = $filegroup;
    $variable['-sort'] = $sort;
    $variable['-keyword'] = $keyword;
    $dal = new dal();
    $dal -> lang = $account -> getLang();
    if ($filegroup != -1) $dal -> filegroup = $filegroup;
    if (!base::isEmpty($keyword)) $dal -> setFuzzyLike('topic', $keyword);
    if ($sort == 1) $dal -> orderBy('hot', 'desc');
    else $dal -> orderBy('time', 'desc');
    $dal -> limit(0, 100);
    $rsa = $dal -> selectAll();
    $tmpstr = tpl::takeAndAssign('managerapi.list', $rsa, $variable, null, function(&$loopLineString, $rs) use ($dal){
      $loopLineString = str_replace('{$-filejson}', base::htmlEncode(self::ppGetFileJSON($rs, $dal -> prefix)), $loopLineString);
    });
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleActionHot()
  {
    $tmpstr = '';
    $status = 0;
    $message = '';
    $id = base::getNum(request::get('id'), 0);
    $dal = new dal();
    $db = $dal -> db;
    if (!is_null($db))
    {
      if ($db -> fieldNumberAdd($dal -> table, $dal -> prefix, 'hot', $id)) $status = 1;
    }
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }
}
?>