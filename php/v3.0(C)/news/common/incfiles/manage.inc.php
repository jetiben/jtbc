<?php
namespace jtbc;
class ui extends console\page {
  use console\fragment\util {
    doActionBatch as public moduleActionBatch;
    doActionDelete as public moduleActionDelete;
  }
  use universal\fragment\category { doCategory as public moduleCategory; }
  use universal\fragment\upload { doActionUpload as public moduleActionUpload; }
  public static $batch = array('publish', 'delete');

  public static function moduleAdd()
  {
    $status = 1;
    $tmpstr = '';
    $category = base::getNum(request::get('category'), 0);
    $account = self::account();
    if ($account -> checkCurrentGenrePopedom('add'))
    {
      $variable['-category'] = $category;
      $variable['-nav-category'] = $category;
      $variable['-my-category'] = $account -> getCurrentGenrePopedom('category');
      $variable['-lang'] = $account -> getLang();
      $tmpstr = tpl::takeAndAssign('manage.add', null, $variable);
      $tmpstr = $account -> replaceAccountTag($tmpstr);
    }
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleEdit()
  {
    $status = 1;
    $tmpstr = '';
    $id = base::getNum(request::get('id'), 0);
    $category = base::getNum(request::get('category'), 0);
    $account = self::account();
    if ($account -> checkCurrentGenrePopedom('edit'))
    {
      $dal = new dal();
      $dal -> id = $id;
      $rs = $dal -> select();
      if (is_array($rs))
      {
        $rsCategory = base::getNum($dal -> val('category'), 0);
        $variable['-category'] = $rsCategory;
        $variable['-nav-category'] = $category;
        $variable['-my-category'] = $account -> getCurrentGenrePopedom('category');
        $variable['-lang'] = $account -> getLang();
        $tmpstr = tpl::takeAndAssign('manage.edit', $rs, $variable);
        $tmpstr = $account -> replaceAccountTag($tmpstr);
      }
    }
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleList()
  {
    $status = 1;
    $page = base::getNum(request::get('page'), 0);
    $publish = base::getNum(request::get('publish'), -1);
    $category = base::getNum(request::get('category'), 0);
    $keyword = base::getString(request::get('keyword'));
    $pagesize = base::getNum(tpl::take('config.pagesize', 'cfg'), 0);
    $account = self::account();
    $myCategory = $account -> getCurrentGenrePopedom('category');
    self::setParam('-keyword', $keyword);
    $batchAry = $account -> getCurrentGenreMySegmentAry(self::$batch);
    $variable['-batch-list'] = implode(',', $batchAry);
    $variable['-batch-show'] = empty($batchAry) ? 0 : 1;
    $variable['-keyword'] = $keyword;
    $variable['-nav-category'] = $category;
    $variable['-lang'] = $account -> getLang();
    $dal = new dal();
    $dal -> lang = $account -> getLang();
    if ($publish != -1) $dal -> publish = $publish;
    $dal -> orderBy('time', 'desc');
    if (!base::isEmpty($myCategory) && base::checkIDAry($myCategory)) $dal -> setIn('category', $myCategory);
    if ($category != 0) $dal -> setIn('category', universal\category::getCategoryFamilyID(self::getParam('genre'), $account -> getLang(), $category));
    if (!base::isEmpty($keyword)) $dal -> setFuzzyLike('topic', $keyword);
    $pagi = new pagi($dal);
    $rsAry = $pagi -> getDataAry($page, $pagesize);
    $variable = array_merge($variable, $pagi -> getVars());
    $tmpstr = tpl::takeAndAssign('manage.list', $rsAry, $variable);
    $tmpstr = $account -> replaceAccountTag($tmpstr);
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleActionAdd()
  {
    $status = 0;
    $message = '';
    $error = array();
    $account = self::account();
    $category = base::getNum(request::getPost('category'), 0);
    if (!$account -> checkCurrentGenrePopedom('add') || !$account -> checkCurrentGenrePopedomByCategory($category))
    {
      array_push($error, tpl::take('::console.text-tips-error-403', 'lng'));
    }
    else
    {
      auto::pushAutoRequestErrorByTable($error);
      if (count($error) == 0)
      {
        $preset = array();
        $preset['publish'] = 0;
        $preset['lang'] = $account -> getLang();
        $preset['time'] = base::getDateTime();
        if ($account -> checkCurrentGenrePopedom('publish')) $preset['publish'] = base::getNum(request::getPost('publish'), 0);
        $re = auto::autoInsertByRequest($preset);
        if (is_numeric($re))
        {
          $status = 1;
          $id = auto::$lastInsertId;
          universal\upload::statusAutoUpdate(self::getParam('genre'), $id);
          $account -> creatCurrentGenreLog('manage.log-add-1', array('id' => $id));
        }
        else array_push($error, tpl::take('::console.text-tips-error-others', 'lng'));
      }
    }
    if (!empty($error)) $message = implode('|', $error);
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function moduleActionEdit()
  {
    $status = 0;
    $message = '';
    $error = array();
    $account = self::account();
    $id = base::getNum(request::get('id'), 0);
    $category = base::getNum(request::getPost('category'), 0);
    if (!$account -> checkCurrentGenrePopedom('edit') || !$account -> checkCurrentGenrePopedomByCategory($category))
    {
      array_push($error, tpl::take('::console.text-tips-error-403', 'lng'));
    }
    else
    {
      auto::pushAutoRequestErrorByTable($error);
      if (count($error) == 0)
      {
        $preset = array();
        $preset['publish'] = 0;
        $preset['lang'] = $account -> getLang();
        if ($account -> checkCurrentGenrePopedom('publish')) $preset['publish'] = base::getNum(request::getPost('publish'), 0);
        $re = auto::autoUpdateByRequest($id, $preset);
        if (is_numeric($re))
        {
          $status = 1;
          universal\upload::statusAutoUpdate(self::getParam('genre'), $id);
          $message = tpl::take('manage.text-tips-edit-done', 'lng');
          $account -> creatCurrentGenreLog('manage.log-edit-1', array('id' => $id));
        }
        else array_push($error, tpl::take('::console.text-tips-error-others', 'lng'));
      }
    }
    if (!empty($error)) $message = implode('|', $error);
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }
}
?>