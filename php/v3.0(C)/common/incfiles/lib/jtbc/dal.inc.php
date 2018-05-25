<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  use Exception;
  class dal
  {
    public $db = null;
    public $err = 0;
    public $sql = null;
    public $table;
    public $prefix;
    public $lastInsertId = null;

    public function getRsCount()
    {
      $rscount = 0;
      $db = $this -> db;
      if (!is_null($db))
      {
        $sql = $this -> sql -> getSelectSQL(true, 'count(*)');
        if (!base::isEmpty($sql))
        {
          $rs = $db -> fetch($sql);
          $rscount = base::getNum($rs['count'], 0);
        }
      }
      return $rscount;
    }

    public function delete($argAutoFilter = true)
    {
      $result = null;
      $autoFilter = $argAutoFilter;
      $db = $this -> db;
      if (!is_null($db))
      {
        $sql = $this -> sql -> getDeleteSQL($autoFilter);
        if (!base::isEmpty($sql)) $result = $db -> exec($sql);
      }
      return $result;
    }

    public function insert($argSource, $argFuzzy = true)
    {
      $result = null;
      $source = $argSource;
      $fuzzy = $argFuzzy;
      $db = $this -> db;
      if (!is_null($db))
      {
        $sql = $this -> sql -> changeSource($source) -> getInsertSQL($fuzzy);
        if (!base::isEmpty($sql))
        {
          $result = $db -> exec($sql);
          $this -> lastInsertId = $db -> lastInsertId;
        }
      }
      return $result;
    }

    public function select($argField = null)
    {
      $result = null;
      $field = $argField;
      $db = $this -> db;
      if (!is_null($db))
      {
        $sql = $this -> sql -> getSelectSQL(true, $field);
        if (!base::isEmpty($sql)) $result = $db -> fetch($sql);
      }
      return $result;
    }

    public function selectAll($argField = null)
    {
      $result = null;
      $field = $argField;
      $db = $this -> db;
      if (!is_null($db))
      {
        $sql = $this -> sql -> getSelectSQL(true, $field);
        if (!base::isEmpty($sql)) $result = $db -> fetchAll($sql);
      }
      return $result;
    }

    public function truncate()
    {
      $result = null;
      $db = $this -> db;
      if (!is_null($db))
      {
        $sql = $this -> sql -> getTruncateSQL();
        if (!base::isEmpty($sql)) $result = $db -> exec($sql);
      }
      return $result;
    }

    public function update($argSource, $argAutoFilter = true, $argFuzzy = true)
    {
      $result = null;
      $source = $argSource;
      $autoFilter = $argAutoFilter;
      $fuzzy = $argFuzzy;
      $db = $this -> db;
      if (!is_null($db))
      {
        $sql = $this -> sql -> changeSource($source) -> getUpdateSQL($autoFilter, $fuzzy);
        if (!base::isEmpty($sql)) $result = $db -> exec($sql);
      }
      return $result;
    }

    public function val($argRs, $argField)
    {
      $val = '';
      $rs = $argRs;
      $field = $argField;
      if (is_array($rs))
      {
        $fullField = $this -> prefix . $field;
        if (array_key_exists($fullField, $rs)) $val = $rs[$fullField];
      }
      return $val;
    }

    public function set()
    {
      $args = func_get_args();
      $argsCount = count($args);
      if ($argsCount == 1)
      {
        $arg = $args[0];
        if (is_array($arg))
        {
          foreach ($arg as $key => $val)
          {
            $this -> sql -> set($key, $val);
          }
        }
      }
      else if ($argsCount == 2)
      {
        $this -> sql -> set($args[0], $args[1]);
      }
      else if ($argsCount == 4)
      {
        $this -> sql -> set($args[0], $args[1]);
        $this -> sql -> set($args[2], $args[3]);
      }
      return $this;
    }

    public function __call($argName, $argArgs) 
    {
      $name = $argName;
      $args = $argArgs;
      if (!method_exists($this, $name))
      {
        if (is_callable(array($this -> sql, $name))) return call_user_func_array(array($this -> sql, $name), $args);
      }
    }

    public function __set($argName, $argValue)
    {
      $this -> sql -> set($argName, $argValue);
    }

    public static function connTest($argDbLink = 'any')
    {
      $bool = false;
      $db = conn::db($dbLink);
      if (!is_null($db)) $bool = true;
      return $bool;
    }

    function __construct()
    {
      $dbLink = 'any';
      $table = null;
      $prefix = null;
      $args = func_get_args();
      $argsCount = count($args);
      if ($argsCount == 1) $dbLink = $args[0];
      else if ($argsCount == 2)
      {
        $table = $args[0];
        $prefix = $args[1];
      }
      else if ($argsCount == 3)
      {
        $table = $args[0];
        $prefix = $args[1];
        $dbLink = $args[2];
      }
      $db = conn::db($dbLink);
      if (!is_null($db))
      {
        if (is_null($table)) $table = tpl::take('config.db_table', 'cfg');
        if (is_null($prefix)) $prefix = tpl::take('config.db_prefix', 'cfg');
        $this -> db = $db;
        $this -> table = $table;
        $this -> prefix = $prefix;
        $this -> sql = new sql($this -> db, $this -> table, $this -> prefix);
        if (base::isEmpty($table)) throw new Exception('"table" cannot be empty.');
      }
      else $this -> err = 444;
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>
