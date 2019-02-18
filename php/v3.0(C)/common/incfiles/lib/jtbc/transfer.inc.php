<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  class transfer
  {
    public static function transfer($argParam, $argOthers = null)
    {
      $tmpstr = '';
      $param = $argParam;
      $others = $argOthers;
      $paramMethod = base::getParameter($param, 'method');
      if ($paramMethod == 'json') $tmpstr = self::transferJson($param, $others);
      else if ($paramMethod == 'sql') $tmpstr = self::transferSQL($param, $others);
      else if ($paramMethod == 'multigenre') $tmpstr = self::transferMultiGenre($param, $others);
      else $tmpstr = self::transferStandard($param, $others);
      return $tmpstr;
    }

    public static function transferJson($argParam, $argJson)
    {
      $tmpstr = '';
      $param = $argParam;
      $json = $argJson;
      $paramTpl = base::getParameter($param, 'tpl');
      $paramRowFilter = base::getParameter($param, 'rowfilter');
      $paramCache = base::getParameter($param, 'cache');
      $paramCacheTimeout = base::getNum(base::getParameter($param, 'cachetimeout'), 300);
      $paramVars = base::getParameter($param, 'vars');
      $paramLimit = base::getNum(base::getParameter($param, 'limit'), 0);
      $paramTransferID = base::getNum(base::getParameter($param, 'transferid'), 0);
      if ($paramLimit == 0) $paramLimit = 10;
      $cacheAry = null;
      if (!base::isEmpty($paramCache))
      {
        $cacheData = cache::get($paramCache);
        if (is_array($cacheData))
        {
          if (count($cacheData) == 2)
          {
            $cacheAry = $cacheData[1];
            $cacheTimeStamp = $cacheData[0];
            if ((time() - $cacheTimeStamp) >= $paramCacheTimeout) cache::remove($paramCache);
          }
        }
      }
      if (!base::isEmpty($paramTpl))
      {
        if (strpos($paramTpl, '.')) $tmpstr = tpl::take($paramTpl, 'tpl');
        else $tmpstr = tpl::take('global.transfer.' . $paramTpl, 'tpl');
      }
      if (!base::isEmpty($paramVars))
      {
        $paramVarsAry = explode('|', $paramVars);
        foreach ($paramVarsAry as $key => $val)
        {
          if (!base::isEmpty($val))
          {
            $valAry = explode('=', $val);
            if (count($valAry) == 2) $tmpstr = str_replace('{$' . $valAry[0] . '}', $valAry[1], $tmpstr);
          }
        }
      }
      $myAry = $cacheAry;
      if (!is_array($myAry))
      {
        $myAry = json_decode($json, true);
        if (!base::isEmpty($paramCache))
        {
          $cacheData = array();
          $cacheData[0] = time();
          $cacheData[1] = $myAry;
          @cache::put($paramCache, $cacheData);
        }
      }
      if (is_array($myAry) && !empty($myAry))
      {
        $rsindex = 1;
        $tpl = new tpl($tmpstr);
        $loopString = $tpl -> getLoopString('{@}');
        foreach ($myAry as $myKey => $myVal)
        {
          $rowAry = $myVal;
          if (!is_array($rowAry)) $rowAry = json_decode($myVal, true);
          if ($paramLimit >= $rsindex)
          {
            if (base::isEmpty($paramRowFilter) || !base::checkInstr($paramRowFilter, $rsindex))
            {
              $loopLineString = $loopString;
              $loopLineString = tpl::replaceTagByAry($loopLineString, $rowAry, 21, $paramTransferID);
              $loopLineString = tpl::replaceTagByAry($loopLineString, array('-i' => $rsindex));
              $tpl -> insertLoopLine(tpl::parse($loopLineString));
            }
          }
          $rsindex += 1;
        }
        $tmpstr = $tpl -> getTpl();
        $tmpstr = tpl::parse($tmpstr);
      }
      else $tmpstr = '';
      return $tmpstr;
    }

    public static function transferSQL($argParam, $argSQL)
    {
      $tmpstr = '';
      $db = conn::db();
      $param = $argParam;
      $sql = $argSQL;
      $paramTpl = base::getParameter($param, 'tpl');
      $paramRowFilter = base::getParameter($param, 'rowfilter');
      $paramCache = base::getParameter($param, 'cache');
      $paramCacheTimeout = base::getNum(base::getParameter($param, 'cachetimeout'), 300);
      $paramVars = base::getParameter($param, 'vars');
      $paramTransferID = base::getNum(base::getParameter($param, 'transferid'), 0);
      $cacheAry = null;
      if (!base::isEmpty($paramCache))
      {
        $cacheData = cache::get($paramCache);
        if (is_array($cacheData))
        {
          if (count($cacheData) == 2)
          {
            $cacheAry = $cacheData[1];
            $cacheTimeStamp = $cacheData[0];
            if ((time() - $cacheTimeStamp) >= $paramCacheTimeout) cache::remove($paramCache);
          }
        }
      }
      if (!base::isEmpty($paramTpl))
      {
        if (strpos($paramTpl, '.')) $tmpstr = tpl::take($paramTpl, 'tpl');
        else $tmpstr = tpl::take('global.transfer.' . $paramTpl, 'tpl');
      }
      if (!base::isEmpty($paramVars))
      {
        $paramVarsAry = explode('|', $paramVars);
        foreach ($paramVarsAry as $key => $val)
        {
          if (!base::isEmpty($val))
          {
            $valAry = explode('=', $val);
            if (count($valAry) == 2) $tmpstr = str_replace('{$' . $valAry[0] . '}', $valAry[1], $tmpstr);
          }
        }
      }
      $myAry = $cacheAry;
      if (!is_array($myAry))
      {
        if (!is_null($db))
        {
          $myAry = $db -> fetchAll($sql);
          if (!base::isEmpty($paramCache))
          {
            $cacheData = array();
            $cacheData[0] = time();
            $cacheData[1] = $myAry;
            @cache::put($paramCache, $cacheData);
          }
        }
      }
      if (is_array($myAry) && !empty($myAry))
      {
        $rsindex = 1;
        $tpl = new tpl($tmpstr);
        $loopString = $tpl -> getLoopString('{@}');
        foreach ($myAry as $myKey => $myVal)
        {
          if (base::isEmpty($paramRowFilter) || !base::checkInstr($paramRowFilter, $rsindex))
          {
            $loopLineString = $loopString;
            $loopLineString = tpl::replaceTagByAry($loopLineString, $myVal, 11, $paramTransferID);
            $loopLineString = tpl::replaceTagByAry($loopLineString, array('-i' => $rsindex));
            $tpl -> insertLoopLine(tpl::parse($loopLineString, 1));
          }
          $rsindex += 1;
        }
        $tmpstr = $tpl -> getTpl();
        $tmpstr = tpl::parse($tmpstr);
      }
      else $tmpstr = '';
      return $tmpstr;
    }

    public static function transferMultiGenre($argParam, $argOSQLAry = null)
    {
      $tmpstr = '';
      $db = conn::db();
      $lang = request::getForeLang();
      $param = $argParam;
      $osqlAry = $argOSQLAry;
      $paramTpl = base::getParameter($param, 'tpl');
      $paramJTBCTag = base::getParameter($param, 'jtbctag');
      $paramType = base::getParameter($param, 'type');
      $paramGenre = base::getParameter($param, 'genre');
      $paramField = base::getParameter($param, 'field');
      $paramOSQL = base::getParameter($param, 'osql');
      $paramOSQLOrder = base::getParameter($param, 'osqlorder');
      $paramRowFilter = base::getParameter($param, 'rowfilter');
      $paramBaseURL = base::getParameter($param, 'baseurl');
      $paramCache = base::getParameter($param, 'cache');
      $paramCacheTimeout = base::getNum(base::getParameter($param, 'cachetimeout'), 300);
      $paramVars = base::getParameter($param, 'vars');
      $paramLimit = base::getNum(base::getParameter($param, 'limit'), 0);
      $paramLang = base::getNum(base::getParameter($param, 'lang'), -100);
      $paramTransferID = base::getNum(base::getParameter($param, 'transferid'), 0);
      if ($paramLimit == 0) $paramLimit = 10;
      if ($paramLang == -100) $paramLang = $lang;
      $ns = __NAMESPACE__;
      $cacheAry = null;
      if (!base::isEmpty($paramCache))
      {
        $cacheData = cache::get($paramCache);
        if (is_array($cacheData))
        {
          if (count($cacheData) == 2)
          {
            $cacheAry = $cacheData[1];
            $cacheTimeStamp = $cacheData[0];
            if ((time() - $cacheTimeStamp) >= $paramCacheTimeout) cache::remove($paramCache);
          }
        }
      }
      if (!base::isEmpty($paramGenre))
      {
        $paramGenreAry = explode('&', $paramGenre);
        $paramFieldAry = explode('&', $paramField);
        $sqlstr = "select * from (";
        $sqlorderstr = '';
        foreach($paramGenreAry as $key => $val)
        {
          if (!base::isEmpty($val))
          {
            $table = tpl::take('global.' . $val . ':config.db_table', 'cfg');
            $prefix = tpl::take('global.' . $val . ':config.db_prefix', 'cfg');
            $sqlstr .= "select " . $prefix . "id as un_id, ";
            foreach($paramFieldAry as $keyF => $valF)
            {
              $sqlstr .= $prefix . $valF . " as un_" . $valF . ", ";
            }
            $sqlstr .= $prefix . "time as un_time, '" . addslashes($val) . "' as un_genre from " . $table;
            switch($paramType)
            {
              case 'new':
                $sqlstr .= " where " . $prefix . "delete=0 and " . $prefix . "publish=1";
                $sqlorderstr = " order by un_time desc";
                break;
              case '@new':
                $sqlstr .= " where " . $prefix . "delete=0";
                $sqlorderstr = " order by un_time desc";
                break;
              case 'top':
                $sqlstr .= " where " . $prefix . "delete=0 and " . $prefix . "publish=1";
                $sqlorderstr = " order by un_id desc";
                break;
              case '@top':
                $sqlstr .= " where " . $prefix . "delete=0";
                $sqlorderstr = " order by un_id desc";
                break;
              case 'commendatory':
                $sqlstr .= " where " . $prefix . "delete=0 and " . $prefix . "publish=1 and " . $prefix . "commendatory=1";
                $sqlorderstr = " order by un_time desc";
                break;
              case '@commendatory':
                $sqlstr .= " where " . $prefix . "delete=0 and " . $prefix . "commendatory=1";
                $sqlorderstr = " order by un_time desc";
                break;
              default:
                $sqlstr .= " where " . $prefix . "delete=0";
                $sqlorderstr = " order by un_id desc";
                break;
            }
            if ($paramLang != -1) $sqlstr .= " and " . $prefix . "lang=" . $paramLang;
            $sqlstr .= " union all ";
          }
        }
        $sqlstr = base::getLRStr($sqlstr, ' union all ', 'leftr');
        $sqlstr .= ") jtbc where 1=1";
        if (!base::isEmpty($paramOSQL)) $sqlstr .= $paramOSQL;
        if (!base::isEmpty($paramOSQLOrder)) $sqlorderstr = $paramOSQLOrder;
        if (is_array($osqlAry))
        {
          foreach ($osqlAry as $key => $val)
          {
            $valType = gettype($val);
            if ($valType == 'integer' || $valType == 'double') $sqlstr .= " and un_" . $key . "=" . base::getNum($val, 0);
            else if ($valType == 'string') $sqlstr .= " and un_" . $key . "='" . addslashes($val) . "'";
          }
        }
        $sqlstr .= $sqlorderstr;
        $sqlstr .= ' limit 0,' . $paramLimit;
        if (!base::isEmpty($paramTpl))
        {
          if (strpos($paramTpl, '.')) $tmpstr = tpl::take($paramTpl, 'tpl');
          else $tmpstr = tpl::take('global.transfer.' . $paramTpl, 'tpl');
        }
        else if (!base::isEmpty($paramJTBCTag))
        {
          if (array_key_exists($paramJTBCTag, tpl::$param)) $tmpstr = tpl::$param[$paramJTBCTag];
        }
        if (!base::isEmpty($paramVars))
        {
          $paramVarsAry = explode('|', $paramVars);
          foreach ($paramVarsAry as $key => $val)
          {
            if (!base::isEmpty($val))
            {
              $valAry = explode('=', $val);
              if (count($valAry) == 2) $tmpstr = str_replace('{$' . $valAry[0] . '}', $valAry[1], $tmpstr);
            }
          }
        }
        $myAry = $cacheAry;
        if (!is_array($myAry))
        {
          if (!is_null($db))
          {
            $myAry = $db -> fetchAll($sqlstr);
            if (!base::isEmpty($paramCache))
            {
              $cacheData = array();
              $cacheData[0] = time();
              $cacheData[1] = $myAry;
              @cache::put($paramCache, $cacheData);
            }
          }
        }
        if (is_array($myAry) && !empty($myAry))
        {
          $rsindex = 1;
          $tpl = new tpl($tmpstr);
          $loopString = $tpl -> getLoopString('{@}');
          foreach ($myAry as $myKey => $myVal)
          {
            if (base::isEmpty($paramRowFilter) || !base::checkInstr($paramRowFilter, $rsindex))
            {
              $loopLineString = $loopString;
              $loopLineString = tpl::replaceTagByAry($loopLineString, $myVal, 11, $paramTransferID);
              $loopLineString = tpl::replaceTagByAry($loopLineString, array('-i' => $rsindex, '-lang' => $paramLang, '-baseurl' => $paramBaseURL));
              $tpl -> insertLoopLine(tpl::parse($loopLineString, 1));
            }
            $rsindex += 1;
          }
          $tmpstr = $tpl -> getTpl();
          $tmpstr = tpl::replaceTagByAry($tmpstr, array('-lang' => $paramLang, '-baseurl' => $paramBaseURL));
          $tmpstr = tpl::parse($tmpstr);
        }
        else $tmpstr = '';
      }
      return $tmpstr;
    }

    public static function transferStandard($argParam, $argOSQLAry = null)
    {
      $tmpstr = '';
      $db = conn::db();
      $genre = route::getCurrentGenre();
      $lang = request::getForeLang();
      $param = $argParam;
      $osqlAry = $argOSQLAry;
      $paramTpl = base::getParameter($param, 'tpl');
      $paramJTBCTag = base::getParameter($param, 'jtbctag');
      $paramType = base::getParameter($param, 'type');
      $paramGenre = base::getParameter($param, 'genre');
      $paramSubTable = base::getParameter($param, 'subtable');
      $paramDBTable = base::getParameter($param, 'db_table');
      $paramDBPrefix = base::getParameter($param, 'db_prefix');
      $paramOSQL = base::getParameter($param, 'osql');
      $paramOSQLOrder = base::getParameter($param, 'osqlorder');
      $paramRowFilter = base::getParameter($param, 'rowfilter');
      $paramBaseURL = base::getParameter($param, 'baseurl');
      $paramCache = base::getParameter($param, 'cache');
      $paramCacheTimeout = base::getNum(base::getParameter($param, 'cachetimeout'), 300);
      $paramVars = base::getParameter($param, 'vars');
      $paramLimit = base::getNum(base::getParameter($param, 'limit'), 0);
      $paramCategory = base::getNum(base::getParameter($param, 'category'), 0);
      $paramGroup = base::getNum(base::getParameter($param, 'group'), 0);
      $paramLang = base::getNum(base::getParameter($param, 'lang'), -100);
      $paramTransferID = base::getNum(base::getParameter($param, 'transferid'), 0);
      if ($paramLimit == 0) $paramLimit = 10;
      if ($paramLang == -100) $paramLang = $lang;
      $ns = __NAMESPACE__;
      $cacheAry = null;
      if (!base::isEmpty($paramCache))
      {
        $cacheData = cache::get($paramCache);
        if (is_array($cacheData))
        {
          if (count($cacheData) == 2)
          {
            $cacheAry = $cacheData[1];
            $cacheTimeStamp = $cacheData[0];
            if ((time() - $cacheTimeStamp) >= $paramCacheTimeout) cache::remove($paramCache);
          }
        }
      }
      if (base::isEmpty($paramBaseURL))
      {
        if (!base::isEmpty($paramGenre) && $paramGenre != $genre)
        {
          $paramBaseURL = route::getActualRoute($paramGenre);
          if (base::getRight($paramBaseURL, 1) != '/') $paramBaseURL .= '/';
        }
      }
      if (base::isEmpty($paramGenre)) $paramGenre = $genre;
      if (base::isEmpty($paramDBTable))
      {
        if (base::isEmpty($paramSubTable)) $paramDBTable = tpl::take('global.' . $paramGenre . ':config.db_table', 'cfg');
        else $paramDBTable = tpl::take('global.' . $paramGenre . ':config.db_table_' . $paramSubTable, 'cfg');
      }
      if (base::isEmpty($paramDBPrefix))
      {
        if (base::isEmpty($paramSubTable)) $paramDBPrefix = tpl::take('global.' . $paramGenre . ':config.db_prefix', 'cfg');
        else $paramDBPrefix = tpl::take('global.' . $paramGenre . ':config.db_prefix_' . $paramSubTable, 'cfg');
      }
      if (!base::isEmpty($paramDBTable))
      {
        $sqlstr = '';
        $sqlorderstr = '';
        switch($paramType)
        {
          case 'count':
            $sqlstr = "select count(*) as rscount from " . $paramDBTable . " where " . $paramDBPrefix . "delete=0 and " . $paramDBPrefix . "publish=1";
            $sqlorderstr = " order by " . $paramDBPrefix . "id desc";
            break;
          case '@count':
            $sqlstr = "select count(*) as rscount from " . $paramDBTable . " where " . $paramDBPrefix . "delete=0";
            $sqlorderstr = " order by " . $paramDBPrefix . "id desc";
            break;
          case 'new':
            $sqlstr = "select * from " . $paramDBTable . " where " . $paramDBPrefix . "delete=0 and " . $paramDBPrefix . "publish=1";
            $sqlorderstr = " order by " . $paramDBPrefix . "time desc";
            break;
          case '@new':
            $sqlstr = "select * from " . $paramDBTable . " where " . $paramDBPrefix . "delete=0";
            $sqlorderstr = " order by " . $paramDBPrefix . "time desc";
            break;
          case 'std':
            $sqlstr = "select * from " . $paramDBTable . " where " . $paramDBPrefix . "delete=0 and " . $paramDBPrefix . "publish=1";
            $sqlorderstr = " order by " . $paramDBPrefix . "time asc";
            break;
          case '@std':
            $sqlstr = "select * from " . $paramDBTable . " where " . $paramDBPrefix . "delete=0";
            $sqlorderstr = " order by " . $paramDBPrefix . "time asc";
            break;
          case 'top':
            $sqlstr = "select * from " . $paramDBTable . " where " . $paramDBPrefix . "delete=0 and " . $paramDBPrefix . "publish=1";
            $sqlorderstr = " order by " . $paramDBPrefix . "id desc";
            break;
          case '@top':
            $sqlstr = "select * from " . $paramDBTable . " where " . $paramDBPrefix . "delete=0";
            $sqlorderstr = " order by " . $paramDBPrefix . "id desc";
            break;
          case 'order':
            $sqlstr = "select * from " . $paramDBTable . " where " . $paramDBPrefix . "delete=0 and " . $paramDBPrefix . "publish=1";
            $sqlorderstr = " order by " . $paramDBPrefix . "order asc";
            break;
          case '@order':
            $sqlstr = "select * from " . $paramDBTable . " where " . $paramDBPrefix . "delete=0";
            $sqlorderstr = " order by " . $paramDBPrefix . "order asc";
            break;
          default:
            $sqlstr = "select * from " . $paramDBTable . " where " . $paramDBPrefix . "delete=0";
            $sqlorderstr = " order by " . $paramDBPrefix . "id desc";
            break;
        }
        if ($paramLang != -1) $sqlstr .= " and " . $paramDBPrefix . "lang=" . $paramLang;
        if ($paramCategory != 0)
        {
          if (method_exists($ns . '\\universal\\category', 'getCategoryChildID'))
          {
            $sqlstr .= " and " . $paramDBPrefix . "category in (" . base::mergeIdAry($paramCategory, universal\category::getCategoryChildID($paramGenre, $paramLang, $paramCategory)) . ")";
          }
        }
        if ($paramGroup != 0) $sqlstr .= " and " . $paramDBPrefix . "group=" . $paramGroup;
        if (!base::isEmpty($paramOSQL)) $sqlstr .= $paramOSQL;
        if (!base::isEmpty($paramOSQLOrder)) $sqlorderstr = $paramOSQLOrder;
        if (is_array($osqlAry))
        {
          foreach ($osqlAry as $key => $val)
          {
            $valType = gettype($val);
            if ($valType == 'integer' || $valType == 'double') $sqlstr .= " and " . $paramDBPrefix . $key . "=" . base::getNum($val, 0);
            else if ($valType == 'string') $sqlstr .= " and " . $paramDBPrefix . $key . "='" . addslashes($val) . "'";
          }
        }
        $sqlstr .= $sqlorderstr;
        $sqlstr .= ' limit 0,' . $paramLimit;
        if (!base::isEmpty($paramTpl))
        {
          if (strpos($paramTpl, '.')) $tmpstr = tpl::take($paramTpl, 'tpl');
          else $tmpstr = tpl::take('global.transfer.' . $paramTpl, 'tpl');
        }
        else if (!base::isEmpty($paramJTBCTag))
        {
          if (array_key_exists($paramJTBCTag, tpl::$param)) $tmpstr = tpl::$param[$paramJTBCTag];
        }
        if (!base::isEmpty($paramVars))
        {
          $paramVarsAry = explode('|', $paramVars);
          foreach ($paramVarsAry as $key => $val)
          {
            if (!base::isEmpty($val))
            {
              $valAry = explode('=', $val);
              if (count($valAry) == 2) $tmpstr = str_replace('{$' . $valAry[0] . '}', $valAry[1], $tmpstr);
            }
          }
        }
        $myAry = $cacheAry;
        if (!is_array($myAry))
        {
          if (!is_null($db))
          {
            $myAry = $db -> fetchAll($sqlstr);
            if (!base::isEmpty($paramCache))
            {
              $cacheData = array();
              $cacheData[0] = time();
              $cacheData[1] = $myAry;
              @cache::put($paramCache, $cacheData);
            }
          }
        }
        if (is_array($myAry) && !empty($myAry))
        {
          $rsindex = 1;
          $tpl = new tpl($tmpstr);
          $loopString = $tpl -> getLoopString('{@}');
          foreach ($myAry as $myKey => $myVal)
          {
            if (base::isEmpty($paramRowFilter) || !base::checkInstr($paramRowFilter, $rsindex))
            {
              $loopLineString = $loopString;
              $loopLineString = tpl::replaceTagByAry($loopLineString, $myVal, 11, $paramTransferID);
              $loopLineString = tpl::replaceTagByAry($loopLineString, array('-i' => $rsindex, '-genre' => $paramGenre, '-lang' => $paramLang, '-baseurl' => $paramBaseURL));
              $tpl -> insertLoopLine(tpl::parse($loopLineString, 1));
            }
            $rsindex += 1;
          }
          $tmpstr = $tpl -> getTpl();
          $tmpstr = tpl::replaceTagByAry($tmpstr, array('-genre' => $paramGenre, '-lang' => $paramLang, '-baseurl' => $paramBaseURL));
          $tmpstr = tpl::parse($tmpstr);
        }
        else $tmpstr = '';
      }
      return $tmpstr;
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>