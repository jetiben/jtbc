<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  class conn
  {
    public static $dbW = null;
    public static $dbR = null;

    public static function db($argDbLink = 'any')
    {
      $db = null;
      $dbLink = $argDbLink;
      if (!is_null(self::$dbW)) $db = self::$dbW;
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
        else self::$dbW = $db;
      }
      return $db;
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>