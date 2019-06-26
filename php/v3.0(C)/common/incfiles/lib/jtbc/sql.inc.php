<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  class sql
  {
    private $db;
    private $table;
    private $prefix;
    private $pocket = array();
    private $orderBy = null;
    private $manualOrderBy = null;
    private $source = array();
    private $additionalSQL = null;
    private $limitStart = null;
    private $limitLength = null;
    public $err = 0;

    public function changeSource($argSource)
    {
      $source = $argSource;
      if (is_array($source)) $this -> source = $source;
      return $this;
    }

    public function clear()
    {
      $this -> pocket = array();
    }

    public function getFieldInfo($argfullColumns, $argField)
    {
      $fieldInfo = null;
      $fullColumns = $argfullColumns;
      $field = $argField;
      foreach ($fullColumns as $i => $item)
      {
        if ($item['Field'] == $field) $fieldInfo = $item;
      }
      return $fieldInfo;
    }

    public function getWhere($argAutoFilter = true)
    {
      $where = '';
      $autoFilter = $argAutoFilter;
      $db = $this -> db;
      $table = $this -> table;
      $prefix = $this -> prefix;
      $pocket = $this -> pocket;
      $additionalSQL = $this -> additionalSQL;
      $fullColumns = $db -> showFullColumns($table);
      $hasWhere = false;
      if ($autoFilter == true)
      {
        $deleteField = $prefix . 'delete';
        $deleteFieldInfo = $this -> getFieldInfo($fullColumns, $deleteField);
        if (is_array($deleteFieldInfo))
        {
          $hasWhere = true;
          $where .= " where " . $deleteField . "=0";
        }
      }
      if ($hasWhere != true) $where .= " where 1=1";
      if (!empty($pocket))
      {
        foreach ($pocket as $key => $val)
        {
          if (is_array($val) && count($val) == 2)
          {
            $currentKey = $val[0];
            $currentVal = $val[1];
            $currentField = null;
            $currentConcat = 'and';
            $currentRelation = '=';
            $keyType = gettype($currentKey);
            if ($keyType == 'string')
            {
              $currentField = $prefix . $currentKey;
            }
            else if ($keyType == 'array')
            {
              $keyCount = count($currentKey);
              if ($keyCount >= 1)
              {
                $currentField = $prefix . $currentKey[0];
              }
              if ($keyCount >= 2)
              {
                $tempRelation = strtolower($currentKey[1]);
                if ($tempRelation == 'in') $currentRelation = 'in';
                else if ($tempRelation == 'like') $currentRelation = 'like';
                else if ($tempRelation == '!=') $currentRelation = '!=';
                else if ($tempRelation == '>=') $currentRelation = '>=';
                else if ($tempRelation == '<=') $currentRelation = '<=';
              }
              if ($keyCount >= 3)
              {
                $tempConcat = strtolower($currentKey[2]);
                if ($tempConcat == 'or') $currentConcat = 'or';
              }
            }
            if (!is_null($currentField))
            {
              $currentFieldInfo = $this -> getFieldInfo($fullColumns, $currentField);
              if (is_array($currentFieldInfo))
              {
                $valType = gettype($currentVal);
                if ($currentRelation == 'in')
                {
                  if ($valType == 'integer' || $valType == 'double') $where .= " " . $currentConcat . " " . $currentField . " in (" . base::getNum($currentVal, 0) . ")";
                  else if ($valType == 'string')
                  {
                    if (base::checkIDAry($currentVal)) $where .= " " . $currentConcat . " " . $currentField  . " in (" . addslashes($currentVal) . ")";
                  }
                  else if ($valType == 'array')
                  {
                    $currentNewVal = '';
                    foreach ($currentVal as $newVal)
                    {
                      $currentNewVal .= "'" . addslashes($newVal) . "',";
                    }
                    if (!base::isEmpty($currentNewVal)) $where .= " " . $currentConcat . " " . $currentField  . " in (" . rtrim($currentNewVal, ',') . ")";
                  }
                  else $this -> err = 485;
                }
                else if ($currentRelation == 'like')
                {
                  if ($valType == 'integer' || $valType == 'double') $where .= " " . $currentConcat . " " . $currentField . " like " . base::getNum($currentVal, 0);
                  else if ($valType == 'string') $where .= " " . $currentConcat . " " . $currentField  . " like '" . addslashes($currentVal) . "'";
                  else $this -> err = 484;
                }
                else if ($currentRelation == '!=')
                {
                  if ($valType == 'integer' || $valType == 'double') $where .= " " . $currentConcat . " " . $currentField . "!=" . base::getNum($currentVal, 0);
                  else if ($valType == 'string') $where .= " " . $currentConcat . " " . $currentField  . "!='" . addslashes($currentVal) . "'";
                  else if ($valType == 'NULL') $where .= " " . $currentConcat . " " . $currentField  . " is not null";
                  else $this -> err = 483;
                }
                else if ($currentRelation == '>=')
                {
                  if ($valType == 'integer' || $valType == 'double') $where .= " " . $currentConcat . " " . $currentField . ">=" . base::getNum($currentVal, 0);
                  else $this -> err = 482;
                }
                else if ($currentRelation == '<=')
                {
                  if ($valType == 'integer' || $valType == 'double') $where .= " " . $currentConcat . " " . $currentField . "<=" . base::getNum($currentVal, 0);
                  else $this -> err = 481;
                }
                else if ($currentRelation == '=')
                {
                  if ($valType == 'integer' || $valType == 'double') $where .= " " . $currentConcat . " " . $currentField . "=" . base::getNum($currentVal, 0);
                  else if ($valType == 'string') $where .= " " . $currentConcat . " " . $currentField  . "='" . addslashes($currentVal) . "'";
                  else if ($valType == 'NULL') $where .= " " . $currentConcat . " " . $currentField  . " is null";
                  else $this -> err = 480;
                }
              }
              else $this -> err = 500;
            }
          }
        }
      }
      if (!is_null($additionalSQL)) $where .= $additionalSQL;
      return $where;
    }

    public function getSelectSQL($argField = null, $argAutoFilter = true)
    {
      $field = $argField;
      $autoFilter = $argAutoFilter;
      $db = $this -> db;
      $table = $this -> table;
      $prefix = $this -> prefix;
      $orderBy = $this -> orderBy;
      $manualOrderBy = $this -> manualOrderBy;
      $limitStart = $this -> limitStart;
      $limitLength = $this -> limitLength;
      $fullColumns = $db -> showFullColumns($table);
      $fieldStr = '*';
      if (is_array($field))
      {
        foreach ($field as $key => $val)
        {
          $field[$key] = $prefix . $val;
        }
        $fieldStr = implode(',', $field);
      }
      else if ($field == 'count(*)')
      {
        $fieldStr = 'count(*) as count';
      }
      $sql = "select " . $fieldStr . " from " . $table . $this -> getWhere($autoFilter);
      if (!is_null($manualOrderBy)) $sql .= " order by " . $manualOrderBy;
      else
      {
        if (!is_null($orderBy))
        {
          $orderByType = gettype($orderBy);
          if ($orderByType == 'string')
          {
            $currentField = $prefix . $orderBy;
            $currentFieldInfo = $this -> getFieldInfo($fullColumns, $currentField);
            if (is_array($currentFieldInfo)) $sql .= " order by " . $currentField . " desc";
          }
          else if ($orderByType == 'array')
          {
            $newOrderBy = array();
            foreach ($orderBy as $key => $val)
            {
              $currentVal = $val;
              if (is_array($currentVal))
              {
                $orderType = 'desc';
                $currentValCount = count($currentVal);
                if ($currentValCount >= 1)
                {
                  $currentField = $prefix . $currentVal[0];
                  if ($currentValCount >= 2)
                  {
                    if (strtolower($currentVal[1]) == 'asc') $orderType = 'asc';
                  }
                  $currentFieldInfo = $this -> getFieldInfo($fullColumns, $currentField);
                  if (is_array($currentFieldInfo)) array_push($newOrderBy, $currentField . ' ' . $orderType);
                }
              }
            }
            if (!empty($newOrderBy)) $sql .= " order by " . implode(',', $newOrderBy);
          }
        }
      }
      if (!is_null($limitStart) && !is_null($limitLength)) $sql .= " limit " . $limitStart . ", " . $limitLength;
      return $sql;
    }

    public function getInsertSQL($argFuzzy = true)
    {
      $sql = '';
      $fuzzy = $argFuzzy;
      $db = $this -> db;
      $table = $this -> table;
      $prefix = $this -> prefix;
      $source = $this -> source;
      $columns = $db -> showFullColumns($table);
      if (is_array($columns))
      {
        $matchCount = 0;
        $fieldString = '';
        $fieldValues = '';
        $sql = "insert into " . $table . " (";
        foreach ($columns as $i => $item)
        {
          $fieldValid = false;
          $fieldName = $item['Field'];
          $fieldTypeName = $item['TypeName'];
          $fieldTypeLength = base::getNum($item['TypeLength'], 0);
          $fieldValue = null;
          $sourceName = $fieldName;
          if (array_key_exists($sourceName, $source)) $fieldValue = $source[$sourceName];
          else
          {
            if ($fuzzy == true)
            {
              $sourceName = base::getLRStr($fieldName, '_', 'rightr');
              if (array_key_exists($sourceName, $source)) $fieldValue = $source[$sourceName];
            }
          }
          if (!is_null($fieldValue))
          {
            $matchCount +=1;
            if ($fieldTypeName == 'int' || $fieldTypeName == 'integer' || $fieldTypeName == 'double')
            {
              $fieldString .= $fieldName . ',';
              $fieldValues .= base::getNum($fieldValue, 0) . ',';
            }
            else if ($fieldTypeName == 'varchar')
            {
              $fieldString .= $fieldName . ',';
              $fieldValues .= "'" . addslashes(base::getLeft($fieldValue, $fieldTypeLength)) . "',";
            }
            else if ($fieldTypeName == 'datetime')
            {
              $fieldString .= $fieldName . ',';
              $fieldValues .= "'" . addslashes(base::getDateTime($fieldValue)) . "',";
            }
            else if ($fieldTypeName == 'text')
            {
              $fieldString .= $fieldName . ',';
              $fieldValues .= "'" . addslashes(base::getLeft($fieldValue, 20000)) . "',";
            }
            else if ($fieldTypeName == 'mediumtext')
            {
              $fieldString .= $fieldName . ',';
              $fieldValues .= "'" . addslashes(base::getLeft($fieldValue, 5000000)) . "',";
            }
            else if ($fieldTypeName == 'longtext')
            {
              $fieldString .= $fieldName . ',';
              $fieldValues .= "'" . addslashes(base::getLeft($fieldValue, 1000000000)) . "',";
            }
            else
            {
              $matchCount -= 1;
            }
          }
        }
        if ($matchCount == 0) $sql = '';
        else
        {
          $sql .= rtrim($fieldString, ',') . ") values (" . rtrim($fieldValues, ',') . ")";
        }
      }
      return $sql;
    }

    public function getTruncateSQL()
    {
      $table = $this -> table;
      $sql = "truncate table " . $table;
      return $sql;
    }

    public function getUpdateSQL($argAutoFilter = true, $argFuzzy = true)
    {
      $sql = '';
      $autoFilter = $argAutoFilter;
      $fuzzy = $argFuzzy;
      $db = $this -> db;
      $table = $this -> table;
      $prefix = $this -> prefix;
      $source = $this -> source;
      $columns = $db -> showFullColumns($table);
      if (is_array($columns))
      {
        $matchCount = 0;
        $fieldStringValues = '';
        $sql = 'update ' . $table . ' set ';
        foreach ($columns as $i => $item)
        {
          $fieldValid = false;
          $fieldName = $item['Field'];
          $fieldTypeName = $item['TypeName'];
          $fieldTypeLength = base::getNum($item['TypeLength'], 0);
          $fieldValue = null;
          $sourceName = $fieldName;
          if (array_key_exists($sourceName, $source)) $fieldValue = $source[$sourceName];
          else
          {
            if ($fuzzy == true)
            {
              $sourceName = base::getLRStr($fieldName, '_', 'rightr');
              if (array_key_exists($sourceName, $source)) $fieldValue = $source[$sourceName];
            }
          }
          if (!is_null($fieldValue))
          {
            $matchCount +=1;
            if ($fieldTypeName == 'int' || $fieldTypeName == 'integer' || $fieldTypeName == 'double')
            {
              $fieldStringValues .= $fieldName . '=' . base::getNum($fieldValue, 0) . ',';
            }
            else if ($fieldTypeName == 'varchar')
            {
              $fieldStringValues .= $fieldName . "='" . addslashes(base::getLeft($fieldValue, $fieldTypeLength)) . "',";
            }
            else if ($fieldTypeName == 'datetime')
            {
              $fieldStringValues .= $fieldName . "='" . addslashes(base::getDateTime($fieldValue)) . "',";
            }
            else if ($fieldTypeName == 'text')
            {
              $fieldStringValues .= $fieldName . "='" . addslashes(base::getLeft($fieldValue, 20000)) . "',";
            }
            else if ($fieldTypeName == 'mediumtext')
            {
              $fieldStringValues .= $fieldName . "='" . addslashes(base::getLeft($fieldValue, 5000000)) . "',";
            }
            else if ($fieldTypeName == 'longtext')
            {
              $fieldStringValues .= $fieldName . "='" . addslashes(base::getLeft($fieldValue, 1000000000)) . "',";
            }
            else
            {
              $matchCount -= 1;
            }
          }
        }
        if ($matchCount == 0) $sql = '';
        else
        {
          $sql .= rtrim($fieldStringValues, ',') . $this -> getWhere($autoFilter);
        }
      }
      return $sql;
    }

    public function getDeleteSQL($argAutoFilter = true)
    {
      $autoFilter = $argAutoFilter;
      $table = $this -> table;
      $sql = "delete from " . $table . $this -> getWhere($autoFilter);
      return $sql;
    }

    public function limit()
    {
      $start = 0;
      $length = 1;
      $args = func_get_args();
      $argsCount = count($args);
      if ($argsCount == 1) $length = base::getNum($args[0], 1);
      else if ($argsCount == 2)
      {
        $start = base::getNum($args[0], 0);
        $length = base::getNum($args[1], 1);
      }
      if ($start < 0) $start = 0;
      if ($length < 1) $length = 1;
      $this -> limitStart = $start;
      $this -> limitLength = $length;
    }

    public function orderBy($argField, $argDescOrAsc = 'desc')
    {
      $field = $argField;
      $descOrAsc = $argDescOrAsc;
      if (strtolower($descOrAsc) == 'asc') $descOrAsc = 'asc';
      $orderBy = $this -> orderBy;
      if (!is_array($orderBy))
      {
        if (!is_null($orderBy))
        {
          $tempOrderBy = $orderBy;
          $orderBy = array();
          array_push($orderBy, array($tempOrderBy));
        }
        else
        {
          $orderBy = array();
          array_push($orderBy, array($field, $descOrAsc));
        }
      }
      else
      {
        array_push($orderBy, array($field, $descOrAsc));
      }
      $this -> orderBy = $orderBy;
      return $this;
    }

    public function set($argName, $argValue)
    {
      $name = $argName;
      $value = $argValue;
      $pocket = $this -> pocket;
      array_push($pocket, array($name, $value));
      $this -> pocket = $pocket;
      return $this;
    }

    public function setMin($argName, $argValue)
    {
      $name = $argName;
      $value = $argValue;
      return $this -> set(array($name, '>='), $value);
    }

    public function setMax($argName, $argValue)
    {
      $name = $argName;
      $value = $argValue;
      return $this -> set(array($name, '<='), $value);
    }

    public function setIn($argName, $argValue)
    {
      $name = $argName;
      $value = $argValue;
      return $this -> set(array($name, 'in'), $value);
    }

    public function setLike($argName, $argValue)
    {
      $name = $argName;
      $value = $argValue;
      return $this -> set(array($name, 'like'), $value);
    }

    public function setFuzzyLike($argName, $argValue)
    {
      $name = $argName;
      $value = $argValue;
      $valueAry = explode(' ', $value);
      foreach ($valueAry as $key => $val)
      {
        if (!base::isEmpty($val)) $this -> setLike($name, '%' . $val . '%');
      }
      return $this;
    }

    public function setUnequal($argName, $argValue)
    {
      $name = $argName;
      $value = $argValue;
      return $this -> set(array($name, '!='), $value);
    }

    public function setAdditionalSQL($argAdditionalSQL)
    {
      $this -> additionalSQL = $argAdditionalSQL;
      return $this;
    }

    public function setManualOrderBy($argManualOrderBy)
    {
      $this -> manualOrderBy = $argManualOrderBy;
      return $this;
    }

    public function __set($argName, $argValue)
    {
      $this -> set($argName, $argValue);
    }

    public static function getCutKeywordSQL($argField, $argKeyword)
    {
      $sql = '';
      $field = $argField;
      $keyword = $argKeyword;
      if (!base::isEmpty($keyword))
      {
        $keywordAry = explode(' ', $keyword);
        foreach ($keywordAry as $key => $val)
        {
          if (!base::isEmpty($val)) $sql .= " and " . $field . " like '%" . addslashes($val) . "%'";
        }
      }
      return $sql;
    }

    function __construct($argDb, $argTable, $argPrefix, $argOrderBy = null)
    {
      $this -> db = $argDb;
      $this -> table = $argTable;
      $this -> prefix = $argPrefix;
      $this -> orderBy = $argOrderBy;
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>