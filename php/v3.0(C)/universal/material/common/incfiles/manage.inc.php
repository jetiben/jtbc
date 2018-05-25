<?php
namespace jtbc;
class ui extends console\page {
  use console\fragment\util {
    doActionBatch as public moduleActionBatch;
    doActionDelete as public moduleActionDelete;
  }
  public static $batch = array('delete');

  public static function moduleEdit()
  {
    $status = 1;
    $tmpstr = '';
    $id = base::getNum(request::get('id'), 0);
    $account = self::account();
    if ($account -> checkCurrentGenrePopedom('edit'))
    {
      $dal = new dal();
      $dal -> id = $id;
      $rs = $dal -> select();
      if (is_array($rs))
      {
        $tmpstr = tpl::take('manage.edit', 'tpl');
        $tmpstr = tpl::replaceTagByAry($tmpstr, $rs, 10);
        $tmpstr = tpl::parse($tmpstr);
        $tmpstr = $account -> replaceAccountTag($tmpstr);
      }
    }
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleList()
  {
    $status = 1;
    $tmpstr = '';
    $page = base::getNum(request::get('page'), 0);
    $filegroup = base::getNum(request::get('filegroup'), -1);
    $pagesize = base::getNum(tpl::take('config.pagesize', 'cfg'), 0);
    $account = self::account();
    $tmpstr = tpl::take('manage.list', 'tpl');
    $tpl = new tpl($tmpstr);
    $loopString = $tpl -> getLoopString('{@}');
    $dal = new dal();
    $dal -> lang = $account -> getLang();
    if ($filegroup != -1) $dal -> filegroup = $filegroup;
    $dal -> orderBy('time', 'desc');
    $pagi = new pagi($dal);
    $rsAry = $pagi -> getDataAry($page, $pagesize);
    if (is_array($rsAry))
    {
      foreach($rsAry as $rs)
      {
        $loopLineString = tpl::replaceTagByAry($loopString, $rs, 10);
        $tpl -> insertLoopLine(tpl::parse($loopLineString));
      }
    }
    $batchAry = $account -> getCurrentGenreMySegmentAry(self::$batch);
    $variable['-batch-list'] = implode(',', $batchAry);
    $variable['-batch-show'] = empty($batchAry) ? 0 : 1;
    $tmpstr = $tpl -> assign($variable) -> assign($pagi -> getVars()) -> getTpl();
    $tmpstr = tpl::parse($tmpstr);
    $tmpstr = $account -> replaceAccountTag($tmpstr);
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleActionAdd()
  {
    $status = 0;
    $message = '';
    $para = '';
    $account = self::account();
    if (!($account -> checkCurrentGenrePopedom('add')))
    {
      $message = tpl::take('::console.text-tips-error-403', 'lng');
    }
    else
    {
      $upResult = universal\upload::up2self(@$_FILES['file'], '', '', false);
      $upResultArray = json_decode($upResult, 1);
      if (is_array($upResultArray))
      {
        $status = $upResultArray['status'];
        $message = $upResultArray['message'];
        $para = $upResultArray['para'];
        if ($status == 1)
        {
          $paraArray = json_decode($para, 1);
          if (is_array($paraArray))
          {
            $preset = array();
            $preset['topic'] = $paraArray['filename'];
            $preset['filepath'] = $paraArray['filepath'];
            $preset['fileurl'] = $paraArray['fileurl'];
            $preset['filetype'] = $paraArray['filetype'];
            $preset['filesize'] = $paraArray['filesize'];
            $preset['filegroup'] = base::getFileGroup($paraArray['filetype']);
            $preset['lang'] = $account -> getLang();
            $preset['time'] = base::getDateTime();
            $re = auto::autoInsertByVars($preset);
            if (is_numeric($re))
            {
              $id = auto::$lastInsertId;
              $account -> creatCurrentGenreLog('manage.log-add-1', array('id' => $id, 'filepath' => $paraArray['filepath']));
            }
          }
        }
      }
    }
    $tmpstr = self::formatMsgResult($status, $message, $para);
    return $tmpstr;
  }

  public static function moduleActionReplace()
  {
    $status = 0;
    $message = '';
    $account = self::account();
    $id = base::getNum(request::get('id'), 0);
    if (!($account -> checkCurrentGenrePopedom('edit')))
    {
      $message = tpl::take('::console.text-tips-error-403', 'lng');
    }
    else
    {
      $dal = new dal();
      $dal -> id = $id;
      $rs = $dal -> select();
      if (is_array($rs))
      {
        $rsFilePath = base::getString($dal -> val($rs, 'filepath'));
        $upResult = universal\upload::up2self(@$_FILES['file'], '', $rsFilePath, false);
        $upResultArray = json_decode($upResult, 1);
        if (is_array($upResultArray))
        {
          $status = $upResultArray['status'];
          $message = $upResultArray['message'];
          $para = $upResultArray['para'];
          if ($status == 1)
          {
            $paraArray = json_decode($para, 1);
            if (is_array($paraArray))
            {
              $preset = array();
              $preset['topic'] = $paraArray['filename'];
              $preset['filesize'] = $paraArray['filesize'];
              $re = auto::autoUpdateByVars($id, $preset);
              if (is_numeric($re))
              {
                $account -> creatCurrentGenreLog('manage.log-replace-1', array('id' => $id));
              }
            }
          }
        }
      }
    }
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function moduleActionEdit()
  {
    $tmpstr = '';
    $status = 0;
    $message = '';
    $error = array();
    $account = self::account();
    $id = base::getNum(request::get('id'), 0);
    $topic = request::getPost('topic');
    if (!$account -> checkCurrentGenrePopedom('edit'))
    {
      array_push($error, tpl::take('::console.text-tips-error-403', 'lng'));
    }
    else
    {
      if (base::isEmpty($topic)) array_push($error, tpl::take('manage.text-tips-edit-error-1', 'lng'));
      if (count($error) == 0)
      {
        $preset = array();
        $preset['lang'] = $account -> getLang();
        $re = auto::autoUpdateByRequest($id, $preset, 'filepath,fileurl,filetype,filesize,filegroup,hot');
        if (is_numeric($re))
        {
          $status = 1;
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
