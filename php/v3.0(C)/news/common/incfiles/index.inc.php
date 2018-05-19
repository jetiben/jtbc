<?php
namespace jtbc;
class ui extends page {
  public static function start()
  {
    self::setPara('adjunct_default', 'list');
    self::setPageTitle(tpl::take('index.title', 'lng'));
  }

  public static function moduleDetail()
  {
    $tmpstr = '';
    $id = base::getNum(request::get('id'), 0);
    $db = conn::db();
    if (!is_null($db))
    {
      $table = tpl::take('config.db_table', 'cfg');
      $prefix = tpl::take('config.db_prefix', 'cfg');
      $sql = new sql($db, $table, $prefix);
      $sql -> publish = 1;
      $sql -> id = $id;
      $sqlstr = $sql -> sql;
      $rs = $db -> fetch($sqlstr);
      if (is_array($rs))
      {
        $rsTopic = base::getString($rs[$prefix . 'topic']);
        self::setPageTitle(base::htmlEncode($rsTopic));
        $tmpstr = tpl::take('index.detail', 'tpl');
        $tmpstr = tpl::replaceTagByAry($tmpstr, $rs, 10);
        $tmpstr = tpl::parse($tmpstr);
      }
    }
    return $tmpstr;
  }

  public static function moduleList()
  {
    $status = 1;
    $tmpstr = '';
    $page = base::getNum(request::get('page'), 0);
    $category = base::getNum(request::get('category'), 0);
    $pagesize = base::getNum(tpl::take('config.pagesize', 'cfg'), 0);
    $db = conn::db();
    if (!is_null($db))
    {
      $tmpstr = tpl::take('index.list', 'tpl');
      $tpl = new tpl($tmpstr);
      $loopString = $tpl -> getLoopString('{@}');
      $table = tpl::take('config.db_table', 'cfg');
      $prefix = tpl::take('config.db_prefix', 'cfg');
      $sql = new sql($db, $table, $prefix, 'time');
      $sql -> publish = 1;
      $sql -> lang = self::getPara('lang');
      if ($category != 0)
      {
        self::setPageTitle(base::htmlEncode(universal\category::getCategoryTopicByID(self::getPara('genre'), self::getPara('lang'), $category)));
        $sql -> setIn('category', universal\category::getCategoryFamilyID(self::getPara('genre'), self::getPara('lang'), $category));
      }
      $sqlstr = $sql -> sql;
      $pagi = new pagi($db);
      $rsAry = $pagi -> getDataAry($sqlstr, $page, $pagesize);
      if (is_array($rsAry))
      {
        foreach($rsAry as $rs)
        {
          $loopLineString = tpl::replaceTagByAry($loopString, $rs, 10);
          $tpl -> insertLoopLine(tpl::parse($loopLineString));
        }
      }
      $variable['-category'] = $category;
      $tmpstr = $tpl -> assign($variable) -> assign($pagi -> getVars()) -> getTpl();
      $tmpstr = tpl::replaceTagByAry($tmpstr, $variable);
      $tmpstr = tpl::parse($tmpstr);
    }
    return $tmpstr;
  }
}
?>
