<?php
namespace jtbc;
class ui extends page {
  public static function start()
  {
    self::setPara('adjunct_default', 'list');
    self::setPageTitle(tpl::take('index.title', 'lng'));
  }

  public static function moduleList()
  {
    $status = 1;
    $tmpstr = '';
    $page = base::getNum(request::get('page'), 0);
    $keyword = base::getString(request::get('keyword'));
    $pagesize = base::getNum(tpl::take('config.pagesize', 'cfg'), 0);
    $db = conn::db();
    if (!is_null($db))
    {
      $tmpstr = tpl::take('index.list', 'tpl');
      $tpl = new tpl($tmpstr);
      $loopString = $tpl -> getLoopString('{@}');
      $sqlstr = "select * from (";
      $folder = route::getFolderByGuide('search');
      $folderAry = explode('|+|', $folder);
      foreach($folderAry as $key => $val)
      {
        if (!base::isEmpty($val))
        {
          $searchMode = base::getNum(tpl::take('global.' . $val . ':search.mode', 'cfg'), 0);
          if ($searchMode == 1)
          {
            $table = tpl::take('global.' . $val . ':config.db_table', 'cfg');
            $prefix = tpl::take('global.' . $val . ':config.db_prefix', 'cfg');
            $sqlstr .= "select " . $prefix . "id as un_id, " . $prefix . "topic as un_topic, " . $prefix . "time as un_time, '" . addslashes($val) . "' as un_genre from " . $table . " where " . $prefix . "delete=0 and " . $prefix . "publish=1 and " . $prefix . "lang=" . base::getNum(self::getPara('lang'), 0) . " union all ";
          }
        }
      }
      $sqlstr = base::getLRStr($sqlstr, ' union all ', 'leftr');
      $sqlstr .= ") jtbc where 1=1" . sql::getCutKeywordSQL('un_topic', $keyword);
      $sqlstr .= " order by un_time desc";
      $pagi = new pagi($db);
      $rsAry = $pagi -> getDataAry($page, $pagesize, 0, $sqlstr);
      if (is_array($rsAry))
      {
        foreach($rsAry as $rs)
        {
          $rsTopic = base::getString($rs['un_topic']);
          $loopLineString = tpl::replaceTagByAry($loopString, $rs, 10);
          $loopLineString = str_replace('{$-topic-keyword-highlight}', base::replaceKeyWordHighlight(base::htmlEncode(base::replaceKeyWordHighlight($rsTopic, $keyword))), $loopLineString);
          $tpl -> insertLoopLine(tpl::parse($loopLineString));
        }
      }
      $tmpstr = $tpl -> assign($pagi -> getVars()) -> getTpl();
      $tmpstr = tpl::parse($tmpstr);
    }
    return $tmpstr;
  }
}
?>
