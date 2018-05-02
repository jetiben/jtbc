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
    private $orderby = null;
    private $additionalSQL = null;

    public function getFieldInfo($argDesc, $argField)
    {
      $fieldInfo = null;
      $desc = $argDesc;
      $field = $argField;
      foreach ($desc as $i => $item)
      {
        if ($item['Field'] == $field) $fieldInfo = $item;
      }
      return $fieldInfo;
    }

    public function getSQL($argField = null, $argAutoFilter = true)
    {
      $field = $argField;
      $autoFilter = $argAutoFilter;
      $db = $this -> db;
      $table = $this -> table;
      $prefix = $this -> prefix;
      $pocket = $this -> pocket;
      $orderby = $this -> orderby;
      $additionalSQL = $this -> additionalSQL;
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
      $hasWhere = false;
      $sql = "select " . $fieldStr . " from " . $table;
      $desc = $db -> desc($table);
      if ($autoFilter == true)
      {
        $deleteField = $prefix . 'delete';
        $deleteFieldInfo = $this -> getFieldInfo($desc, $deleteField);
        if (is_array($deleteFieldInfo))
        {
          $hasWhere = true;
          $sql .= " where " . $deleteField . "=0";
        }
      }
      if ($hasWhere != true) $sql .= " where 1=1";
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
              $currentFieldInfo = $this -> getFieldInfo($desc, $currentField);
              if (is_array($currentFieldInfo))
              {
                $valType = gettype($currentVal);
                if ($currentRelation == 'in')
                {
                  if ($valType == 'integer' || $valType == 'double') $sql .= " " . $currentConcat . " " . $currentField . " in (" . base::getNum($currentVal, 0) . ")";
                  else if ($valType == 'string')
                  {
                    if (base::checkIDAry($currentVal)) $sql .= " " . $currentConcat . " " . $currentField  . " in (" . addslashes($currentVal) . ")";
                  }
                }
                else if ($currentRelation == 'like')
                {
                  if ($valType == 'integer' || $valType == 'double') $sql .= " " . $currentConcat . " " . $currentField . " like " . base::getNum($currentVal, 0);
                  else if ($valType == 'string') $sql .= " " . $currentConcat . " " . $currentField  . " like '" . addslashes($currentVal) . "'";
                }
                else if ($currentRelation == '!=')
                {
                  if ($valType == 'integer' || $valType == 'double') $sql .= " " . $currentConcat . " " . $currentField . "!=" . base::getNum($currentVal, 0);
                  else if ($valType == 'string') $sql .= " " . $currentConcat . " " . $currentField  . "!='" . addslashes($currentVal) . "'";
                }
                else if ($currentRelation == '>=')
                {
                  if ($valType == 'integer' || $valType == 'double') $sql .= " " . $currentConcat . " " . $currentField . ">=" . base::getNum($currentVal, 0);
                }
                else if ($currentRelation == '<=')
                {
                  if ($valType == 'integer' || $valType == 'double') $sql .= " " . $currentConcat . " " . $currentField . "<=" . base::getNum($currentVal, 0);
                }
                else if ($currentRelation == '=')
                {
                  if ($valType == 'integer' || $valType == 'double') $sql .= " " . $currentConcat . " " . $currentField . "=" . base::getNum($currentVal, 0);
                  else if ($valType == 'string') $sql .= " " . $currentConcat . " " . $currentField  . "='" . addslashes($currentVal) . "'";
                }
              }
            }
          }
        }
      }
      if (!is_null($additionalSQL))
      {
        $sql .= $additionalSQL;
      }
      if (!is_null($orderby))
      {
        $orderbyType = gettype($orderby);
        if ($orderbyType == 'string')
        {
          $currentField = $prefix . $orderby;
          $currentFieldInfo = $this -> getFieldInfo($desc, $currentField);
          if (is_array($currentFieldInfo)) $sql .= " order by " . $currentField . " desc";
        }
        else if ($orderbyType == 'array')
        {
          $newOrderBy = array();
          foreach ($orderby as $key => $val)
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
                $currentFieldInfo = $this -> getFieldInfo($desc, $currentField);
                if (is_array($currentFieldInfo)) array_push($newOrderBy, $currentField . ' ' . $orderType);
              }
            }
          }
          if (!empty($newOrderBy)) $sql .= " order by " . implode(',', $newOrderBy);
        }
      }
      return $sql;
    }

    public function orderBy($argField, $argDescOrAsc = 'desc')
    {
      $field = $argField;
      $descOrAsc = $argDescOrAsc;
      if (strtolower($descOrAsc) == 'asc') $descOrAsc = 'asc';
      $orderby = $this -> orderby;
      if (!is_array($orderby))
      {
        if (!is_null($orderby))
        {
          $tempOrderby = $orderby;
          $orderby = array();
          array_push($orderby, array($tempOrderby));
        }
        else
        {
          $orderby = array();
          array_push($orderby, array($field, $descOrAsc));
        }
      }
      else
      {
        array_push($orderby, array($field, $descOrAsc));
      }
      $this -> orderby = $orderby;
    }

    public function set($argName, $argValue)
    {
      $name = $argName;
      $value = $argValue;
      $pocket = $this -> pocket;
      array_push($pocket, array($name, $value));
      $this -> pocket = $pocket;
    }

    public function setMin($argName, $argValue)
    {
      $name = $argName;
      $value = $argValue;
      $this -> set(array($name, '>='), $value);
    }

    public function setMax($argName, $argValue)
    {
      $name = $argName;
      $value = $argValue;
      $this -> set(array($name, '<='), $value);
    }

    public function setIn($argName, $argValue)
    {
      $name = $argName;
      $value = $argValue;
      $this -> set(array($name, 'in'), $value);
    }

    public function setLike($argName, $argValue)
    {
      $name = $argName;
      $value = $argValue;
      $this -> set(array($name, 'like'), $value);
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
    }

    public function setUnequal($argName, $argValue)
    {
      $name = $argName;
      $value = $argValue;
      $this -> set(array($name, '!='), $value);
    }

    public function setAdditionalSQL($argAdditionalSQL)
    {
      $this -> additionalSQL = $argAdditionalSQL;
    }

    public function __get($argName)
    {
      $tmpstr = null;
      $name = $argName;
      if ($name == 'sql') $tmpstr = $this -> getSQL();
      return $tmpstr;
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

    public function __set($argName, $argValue)
    {
      $this -> set($argName, $argValue);
    }

    function __construct($argDb, $argTable, $argPrefix, $argOrderBy = null)
    {
      $this -> db = $argDb;
      $this -> table = $argTable;
      $this -> prefix = $argPrefix;
      $this -> orderby = $argOrderBy;
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>
