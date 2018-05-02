<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  class conn
  {
    public static $db = null;

    public static function db()
    {
      $db = null;
      if (!is_null(self::$db)) $db = self::$db;
      else
      {
        $db = new db();
        $db -> dbHost = DB_HOST;
        $db -> dbUsername = DB_USERNAME;
        $db -> dbPassword = DB_PASSWORD;
        $db -> dbDatabase = DB_DATABASE;
        $db -> dbStructureCache = DB_STRUCTURE_CACHE;
        $db -> init();
        if ($db -> errStatus != 0) $db = null;
        else self::$db = $db;
      }
      return $db;
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>