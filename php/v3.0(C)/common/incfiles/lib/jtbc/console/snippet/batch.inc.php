<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc\console\snippet {
  use jtbc\base;
  use jtbc\conn;
  use jtbc\tpl;
  use jtbc\request;
  trait batch
  {
    public static function moduleActionBatch()
    {
      $tmpstr = '';
      $status = 0;
      $message = '';
      $account = self::account();
      $class = get_called_class();
      $ids = base::getString(request::get('ids'));
      $batch = base::getString(request::get('batch'));
      $batchAry = self::$batch;
      if (is_array($batchAry) && base::checkIDAry($ids))
      {
        $table = tpl::take('config.db_table', 'cfg');
        $prefix = tpl::take('config.db_prefix', 'cfg');
        $db = conn::db();
        if (!is_null($db))
        {
          if ($batch == 'delete' && in_array('delete', $batchAry) && $account -> checkCurrentGenrePopedom('delete'))
          {
            if ($db -> fieldSwitch($table, $prefix, 'delete', $ids))
            {
              $status = 1;
              $callback = 'moduleActionBatchDeleteCallback';
              if (method_exists($class, $callback)) call_user_func(array($class, $callback), $ids);
            }
          }
          else if ($batch == 'dispose' && in_array('dispose', $batchAry) && $account -> checkCurrentGenrePopedom('dispose'))
          {
            if ($db -> fieldSwitch($table, $prefix, 'dispose', $ids))
            {
              $status = 1;
              $callback = 'moduleActionBatchDisposeCallback';
              if (method_exists($class, $callback)) call_user_func(array($class, $callback), $ids);
            }
          }
          else if ($batch == 'lock' && in_array('lock', $batchAry) && $account -> checkCurrentGenrePopedom('lock'))
          {
            if ($db -> fieldSwitch($table, $prefix, 'lock', $ids))
            {
              $status = 1;
              $callback = 'moduleActionBatchLockCallback';
              if (method_exists($class, $callback)) call_user_func(array($class, $callback), $ids);
            }
          }
          else if ($batch == 'publish' && in_array('publish', $batchAry) && $account -> checkCurrentGenrePopedom('publish'))
          {
            if ($db -> fieldSwitch($table, $prefix, 'publish', $ids))
            {
              $status = 1;
              $callback = 'moduleActionBatchPublishCallback';
              if (method_exists($class, $callback)) call_user_func(array($class, $callback), $ids);
            }
          }
        }
        if ($status == 1)
        {
          $account -> creatCurrentGenreLog('manage.log-batch-1', array('id' => $ids, 'batch' => $batch));
        }
      }
      $tmpstr = self::formatMsgResult($status, $message);
      return $tmpstr;
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>
