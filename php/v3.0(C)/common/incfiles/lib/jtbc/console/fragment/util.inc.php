<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc\console\fragment {
  use jtbc\base;
  use jtbc\conn;
  use jtbc\hook;
  use jtbc\tpl;
  use jtbc\request;
  trait util
  {
    private static function doActionBatch()
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
              hook::trigger('doActionBatchDeleteDone', $ids);
            }
          }
          else if ($batch == 'dispose' && in_array('dispose', $batchAry) && $account -> checkCurrentGenrePopedom('dispose'))
          {
            if ($db -> fieldSwitch($table, $prefix, 'dispose', $ids))
            {
              $status = 1;
              hook::trigger('doActionBatchDisposeDone', $ids);
            }
          }
          else if ($batch == 'lock' && in_array('lock', $batchAry) && $account -> checkCurrentGenrePopedom('lock'))
          {
            if ($db -> fieldSwitch($table, $prefix, 'lock', $ids))
            {
              $status = 1;
              hook::trigger('doActionBatchLockDone', $ids);
            }
          }
          else if ($batch == 'publish' && in_array('publish', $batchAry) && $account -> checkCurrentGenrePopedom('publish'))
          {
            if ($db -> fieldSwitch($table, $prefix, 'publish', $ids))
            {
              $status = 1;
              hook::trigger('doActionBatchPublishDone', $ids);
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

    private static function doActionDelete()
    {
      $tmpstr = '';
      $status = 0;
      $message = '';
      $id = base::getNum(request::get('id'), 0);
      $account = self::account();
      $class = get_called_class();
      if (!$account -> checkCurrentGenrePopedom('delete'))
      {
        $message = tpl::take('::console.text-tips-error-403', 'lng');
      }
      else
      {
        $table = tpl::take('config.db_table', 'cfg');
        $prefix = tpl::take('config.db_prefix', 'cfg');
        $db = conn::db();
        if (!is_null($db))
        {
          if ($db -> fieldSwitch($table, $prefix, 'delete', $id, 1))
          {
            $status = 1;
            hook::trigger('doActionDeleteDone', $id);
            $account -> creatCurrentGenreLog('manage.log-delete-1', array('id' => $id));
          }
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
