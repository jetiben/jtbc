<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  use PDO;
  use PDOException;
  class db
  {
    public $conn;
    public $dbHost;
    public $dbUsername;
    public $dbPassword;
    public $dbDatabase;
    public $dbStructureCache = false;
    public $errStatus = 0;
    public $errMessage;
    public $lastInsertId;
    public static $querycount = 0;

    public function init()
    {
      try {
        $dsn = 'mysql:host=' . $this -> dbHost;
        if (!empty($this -> dbDatabase)) $dsn .= ';dbname=' . $this -> dbDatabase;
        $this -> conn = @new PDO($dsn, $this -> dbUsername, $this -> dbPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'', PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION));
      }
      catch (PDOException $e) {
        $this -> errStatus = 1;
        $this -> errMessage = $e -> getMessage();
      }
    }

    public function fetch($argSQLString, $argMode = 0)
    {
      $rs = null;
      $sqlString = $argSQLString;
      $mode = $argMode;
      $rq = $this -> query($sqlString);
      if ($mode == 1) $rs = $rq -> fetch();
      else $rs = $rq -> fetch(PDO::FETCH_ASSOC);
      return $rs;
    }

    public function fetchAll($argSQLString, $argMode = 0)
    {
      $rs = null;
      $sqlString = $argSQLString;
      $mode = $argMode;
      $rq = $this -> query($sqlString);
      if ($mode == 1) $rs = $rq -> fetchAll();
      else $rs = $rq -> fetchAll(PDO::FETCH_ASSOC);
      return $rs;
    }

    public function query($argSQLString)
    {
      $sqlString = $argSQLString;
      self::$querycount += 1;
      $query = $this -> conn -> query($sqlString);
      return $query;
    }

    public function exec($argSQLString)
    {
      $sqlString = $argSQLString;
      self::$querycount += 1;
      $exec = $this -> conn -> exec($sqlString);
      if (substr($sqlString, 0, 6) == 'insert') $this -> lastInsertId = $this -> conn -> lastInsertId();
      return $exec;
    }

    public function showFullColumns($argTable)
    {
      $table = $argTable;
      $fullColumns = null;
      $cacheName = 'db_structure_fullcolumns_' . $table;
      if ($this -> dbStructureCache == true) $fullColumns = cache::get($cacheName);
      if (empty($fullColumns))
      {
        $query = $this -> query('show full columns from ' . $table);
        $fullColumns = $query -> fetchAll(PDO::FETCH_ASSOC);
        foreach ($fullColumns as $i => $item)
        {
          $fieldType = $item['Type'];
          $fieldTypeName = $fieldType;
          $fieldTypeLength = null;
          if (is_numeric(strpos($fieldType, '(')))
          {
            $fieldTypeName = base::getLRStr($fieldType, '(', 'left');
            $fieldTypeLength = base::getNum(base::getLRStr(base::getLRStr($fieldType, '(', 'right'), ')', 'left'), 0);
          }
          $item['TypeName'] = $fieldTypeName;
          $item['TypeLength'] = $fieldTypeLength;
          $fullColumns[$i] = $item;
        }
        if ($this -> dbStructureCache == true) @cache::put($cacheName, $fullColumns);
      }
      return $fullColumns;
    }

    public function fieldSwitch($argTable, $argPrefix, $argField, $argIds, $argValue = null)
    {
      $exec = 0;
      $table = $argTable;
      $prefix = $argPrefix;
      $field = $argField;
      $ids = $argIds;
      $value = $argValue;
      if (base::checkIDAry($ids))
      {
        $sqlstr = "update " . $table . " set " . $prefix . $field . "=abs(" . $prefix . $field . "-1) where " . $prefix . "id in (" . $ids . ")";
        if (is_numeric($value)) $sqlstr = "update " . $table . " set " . $prefix . $field . "=" . base::getNum($value, 0) . " where " . $prefix . "id in (" . $ids . ")";
        $exec = $this -> exec($sqlstr);
      }
      return $exec;
    }

    public function fieldNumberAdd($argTable, $argPrefix, $argField, $argIds, $argValue = 1)
    {
      $exec = 0;
      $table = $argTable;
      $prefix = $argPrefix;
      $field = $argField;
      $ids = $argIds;
      $value = base::getNum($argValue, 0);
      if (base::checkIDAry($ids))
      {
        $sqlstr = "update " . $table . " set " . $prefix . $field . "=" . $prefix . $field . "+" . $value . " where " . $prefix . "id in (" . $ids . ")";
        $exec = $this -> exec($sqlstr);
      }
      return $exec;
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>