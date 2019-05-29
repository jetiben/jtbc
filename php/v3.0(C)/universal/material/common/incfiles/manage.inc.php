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
        $tmpstr = tpl::takeAndAssign('manage.edit', $rs);
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
    $batchAry = $account -> getCurrentGenreMySegmentAry(self::$batch);
    $variable['-batch-list'] = implode(',', $batchAry);
    $variable['-batch-show'] = empty($batchAry) ? 0 : 1;
    $dal = new dal();
    $dal -> lang = $account -> getLang();
    if ($filegroup != -1) $dal -> filegroup = $filegroup;
    $dal -> orderBy('time', 'desc');
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
    $param = '';
    $account = self::account();
    if (!($account -> checkCurrentGenrePopedom('add')))
    {
      $message = tpl::take('::console.text-tips-error-403', 'lng');
    }
    else
    {
      $fileArray = array();
      $fileArray['file'] = request::getFile('file');
      $fileArray['fileSize'] = request::getPost('fileSize');
      $fileArray['chunkCount'] = request::getPost('chunkCount');
      $fileArray['chunkCurrentIndex'] = request::getPost('chunkCurrentIndex');
      $fileArray['timeStringRandom'] = request::getPost('timeStringRandom');
      $upResult = universal\upload::upFile($fileArray, '', '', false);
      $upResultArray = json_decode($upResult, 1);
      if (is_array($upResultArray))
      {
        $status = $upResultArray['status'];
        $message = $upResultArray['message'];
        $param = $upResultArray['param'];
        if ($status == 1)
        {
          $paramArray = json_decode($param, 1);
          if (is_array($paramArray))
          {
            $preset = array();
            $preset['topic'] = $paramArray['filename'];
            $preset['filepath'] = $paramArray['filepath'];
            $preset['fileurl'] = $paramArray['fileurl'];
            $preset['filetype'] = $paramArray['filetype'];
            $preset['filesize'] = $paramArray['filesize'];
            $preset['filegroup'] = base::getFileGroup($paramArray['filetype']);
            $preset['lang'] = $account -> getLang();
            $preset['time'] = base::getDateTime();
            $re = auto::autoInsertByVars($preset);
            if (is_numeric($re))
            {
              $id = auto::$lastInsertId;
              $account -> creatCurrentGenreLog('manage.log-add-1', array('id' => $id, 'filepath' => $paramArray['filepath']));
            }
          }
        }
      }
    }
    $tmpstr = self::formatMsgResult($status, $message, $param);
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
        $fileArray = array();
        $fileArray['file'] = request::getFile('file');
        $fileArray['fileSize'] = request::getPost('fileSize');
        $fileArray['chunkCount'] = request::getPost('chunkCount');
        $fileArray['chunkCurrentIndex'] = request::getPost('chunkCurrentIndex');
        $fileArray['timeStringRandom'] = request::getPost('timeStringRandom');
        $rsFilePath = base::getString($dal -> val('filepath'));
        $upResult = universal\upload::upFile($fileArray, '', $rsFilePath, false);
        $upResultArray = json_decode($upResult, 1);
        if (is_array($upResultArray))
        {
          $status = $upResultArray['status'];
          $message = $upResultArray['message'];
          $param = $upResultArray['param'];
          if ($status == 1)
          {
            $paramArray = json_decode($param, 1);
            if (is_array($paramArray))
            {
              $preset = array();
              $preset['topic'] = $paramArray['filename'];
              $preset['filesize'] = $paramArray['filesize'];
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