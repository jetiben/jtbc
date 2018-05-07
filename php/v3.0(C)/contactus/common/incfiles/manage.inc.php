<?php
namespace jtbc;
class ui extends console\page {
  public static function moduleEdit()
  {
    $status = 1;
    $tmpstr = '';
    $account = self::account();
    if ($account -> checkCurrentGenrePopedom())
    {
      $tmpstr = tpl::take('manage.edit', 'tpl');
      $tmpstr = str_replace('{$-lang-text}', $account -> getLangText(), $tmpstr);
      $tmpstr = tpl::parse($tmpstr);
      $tmpstr = $account -> replaceAccountTag($tmpstr);
    }
    $tmpstr = self::formatResult($status, $tmpstr);
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
    $title = request::getPost('title');
    if (!$account -> checkCurrentGenrePopedom())
    {
      array_push($error, tpl::take('::console.text-tips-error-403', 'lng'));
    }
    else
    {
      if (base::isEmpty($title)) array_push($error, tpl::take('manage.text-tips-edit-error-1', 'lng'));
      if (count($error) == 0)
      {
        $langText = $account -> getLangText();
        $bool = tpl::bring('index.title', 'lng', request::getPost('title'), $langText);
        if ($bool) $bool = tpl::bring('index.content', 'lng', request::getPost('content'), $langText);
        if ($bool) $bool = tpl::bring('index.att', 'lng', request::getPost('att'), $langText);
        if ($bool)
        {
          $status = 1;
          universal\upload::statusReset(self::getPara('genre'), 0);
          universal\upload::statusUpdate(self::getPara('genre'), 0, request::getPost('att'));
          $message = tpl::take('manage.text-tips-edit-done', 'lng');
          $account -> creatCurrentGenreLog('manage.log-edit-1');
        }
      }
    }
    if (count($error) != 0) $message = implode('|', $error);
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function moduleActionUpload()
  {
    $status = 0;
    $message = '';
    $para = '';
    $account = self::account();
    if (!$account -> checkCurrentGenrePopedom())
    {
      $message = tpl::take('::console.text-tips-error-403', 'lng');
    }
    else
    {
      $upResult = universal\upload::up2self(@$_FILES['file']);
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
            $account -> creatCurrentGenreLog('manage.log-upload-1', array('filepath' => $paraArray['filepath']));
          }
        }
      }
    }
    $tmpstr = self::formatMsgResult($status, $message, $para);
    return $tmpstr;
  }
}
?>
