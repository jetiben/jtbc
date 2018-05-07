<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc\console\snippet {
  use jtbc\base;
  use jtbc\conn;
  use jtbc\tpl;
  use jtbc\request;
  trait delete
  {
    public static function moduleActionDelete()
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
            $callback = 'moduleActionDeleteCallback';
            if (method_exists($class, $callback)) call_user_func(array($class, $callback), $id);
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
