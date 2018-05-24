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
    $tmpstr = '';
    $selectmode = 'single';
    $mode = base::getString(request::get('mode'));
    $keyword = base::getString(request::get('keyword'));
    $sort = base::getNum(request::get('sort'), 1);
    $filegroup = base::getNum(request::get('filegroup'), -1);
    if ($mode == 'multiple') $selectmode = 'multiple';
    $account = self::account();
    $tmpstr = tpl::take('managerapi.list', 'tpl');
    $tpl = new tpl($tmpstr);
    $loopString = $tpl -> getLoopString('{@}');
    $dal = new dal();
    $dal -> lang = $account -> getLang();
    if ($filegroup != -1) $dal -> filegroup = $filegroup;
    if (!base::isEmpty($keyword)) $dal -> setFuzzyLike('topic', $keyword);
    if ($sort == 1) $dal -> orderBy('hot', 'desc');
    else $dal -> orderBy('time', 'desc');
    $dal -> limit(0, 100);
    $rsa = $dal -> selectAll();
    foreach ($rsa as $i => $rs)
    {
      $rsTopic = base::getString($dal -> val($rs, 'topic'));
      $loopLineString = tpl::replaceTagByAry($loopString, $rs, 10);
      $loopLineString = str_replace('{$-filejson}', base::htmlEncode(self::ppGetFileJSON($rs, $prefix)), $loopLineString);
      $loopLineString = str_replace('{$-topic-keyword-highlight}', base::replaceKeyWordHighlight(base::htmlEncode(base::replaceKeyWordHighlight($rsTopic, $keyword))), $loopLineString);
      $tpl -> insertLoopLine(tpl::parse($loopLineString));
    }
    $variable['-selectmode'] = $selectmode;
    $variable['-filegroup'] = $filegroup;
    $variable['-sort'] = $sort;
    $variable['-keyword'] = $keyword;
    $tmpstr = $tpl -> assign($variable) -> getTpl();
    $tmpstr = tpl::parse($tmpstr);
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
