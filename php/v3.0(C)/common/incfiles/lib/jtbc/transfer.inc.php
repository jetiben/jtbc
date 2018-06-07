<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  class transfer
  {
    public static function transfer($argPara, $argOthers = null)
    {
      $tmpstr = '';
      $para = $argPara;
      $others = $argOthers;
      $paraMethod = base::getParameter($para, 'method');
      if ($paraMethod == 'json') $tmpstr = self::transferJson($para, $others);
      else if ($paraMethod == 'sql') $tmpstr = self::transferSQL($para, $others);
      else if ($paraMethod == 'multigenre') $tmpstr = self::transferMultiGenre($para, $others);
      else $tmpstr = self::transferStandard($para, $others);
      return $tmpstr;
    }

    public static function transferJson($argPara, $argJson)
    {
      $tmpstr = '';
      $para = $argPara;
      $json = $argJson;
      $paraTpl = base::getParameter($para, 'tpl');
      $paraRowFilter = base::getParameter($para, 'rowfilter');
      $paraCache = base::getParameter($para, 'cache');
      $paraCacheTimeout = base::getNum(base::getParameter($para, 'cachetimeout'), 300);
      $paraVars = base::getParameter($para, 'vars');
      $paraLimit = base::getNum(base::getParameter($para, 'limit'), 0);
      $paraTransferID = base::getNum(base::getParameter($para, 'transferid'), 0);
      if ($paraLimit == 0) $paraLimit = 10;
      $cacheAry = null;
      if (!base::isEmpty($paraCache))
      {
        $cacheData = cache::get($paraCache);
        if (is_array($cacheData))
        {
          if (count($cacheData) == 2)
          {
            $cacheAry = $cacheData[1];
            $cacheTimeStamp = $cacheData[0];
            if ((time() - $cacheTimeStamp) >= $paraCacheTimeout) cache::remove($paraCache);
          }
        }
      }
      if (!base::isEmpty($paraTpl))
      {
        if (strpos($paraTpl, '.')) $tmpstr = tpl::take($paraTpl, 'tpl');
        else $tmpstr = tpl::take('global.transfer.' . $paraTpl, 'tpl');
      }
      if (!base::isEmpty($paraVars))
      {
        $paraVarsAry = explode('|', $paraVars);
        foreach ($paraVarsAry as $key => $val)
        {
          if (!base::isEmpty($val))
          {
            $valAry = explode('=', $val);
            if (count($valAry) == 2) $tmpstr = str_replace('{$' . $valAry[0] + '}', $valAry[1], $tmpstr);
          }
        }
      }
      $myAry = $cacheAry;
      if (!is_array($myAry))
      {
        $myAry = json_decode($json, true);
        if (!base::isEmpty($paraCache))
        {
          $cacheData = array();
          $cacheData[0] = time();
          $cacheData[1] = $myAry;
          @cache::put($paraCache, $cacheData);
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
          if ($paraLimit >= $rsindex)
          {
            if (base::isEmpty($paraRowFilter) || !base::checkInstr($paraRowFilter, $rsindex))
            {
              $loopLineString = $loopString;
              $loopLineString = tpl::replaceTagByAry($loopLineString, $rowAry, 21, $paraTransferID);
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

    public static function transferSQL($argPara, $argSQL)
    {
      $tmpstr = '';
      $db = conn::db();
      $para = $argPara;
      $sql = $argSQL;
      $paraTpl = base::getParameter($para, 'tpl');
      $paraRowFilter = base::getParameter($para, 'rowfilter');
      $paraCache = base::getParameter($para, 'cache');
      $paraCacheTimeout = base::getNum(base::getParameter($para, 'cachetimeout'), 300);
      $paraVars = base::getParameter($para, 'vars');
      $paraTransferID = base::getNum(base::getParameter($para, 'transferid'), 0);
      $cacheAry = null;
      if (!base::isEmpty($paraCache))
      {
        $cacheData = cache::get($paraCache);
        if (is_array($cacheData))
        {
          if (count($cacheData) == 2)
          {
            $cacheAry = $cacheData[1];
            $cacheTimeStamp = $cacheData[0];
            if ((time() - $cacheTimeStamp) >= $paraCacheTimeout) cache::remove($paraCache);
          }
        }
      }
      if (!base::isEmpty($paraTpl))
      {
        if (strpos($paraTpl, '.')) $tmpstr = tpl::take($paraTpl, 'tpl');
        else $tmpstr = tpl::take('global.transfer.' . $paraTpl, 'tpl');
      }
      if (!base::isEmpty($paraVars))
      {
        $paraVarsAry = explode('|', $paraVars);
        foreach ($paraVarsAry as $key => $val)
        {
          if (!base::isEmpty($val))
          {
            $valAry = explode('=', $val);
            if (count($valAry) == 2) $tmpstr = str_replace('{$' . $valAry[0] + '}', $valAry[1], $tmpstr);
          }
        }
      }
      $myAry = $cacheAry;
      if (!is_array($myAry))
      {
        if (!is_null($db))
        {
          $myAry = $db -> fetchAll($sql);
          if (!base::isEmpty($paraCache))
          {
            $cacheData = array();
            $cacheData[0] = time();
            $cacheData[1] = $myAry;
            @cache::put($paraCache, $cacheData);
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
          if (base::isEmpty($paraRowFilter) || !base::checkInstr($paraRowFilter, $rsindex))
          {
            $loopLineString = $loopString;
            $loopLineString = tpl::replaceTagByAry($loopLineString, $myVal, 11, $paraTransferID);
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

    public static function transferMultiGenre($argPara, $argOSQLAry = null)
    {
      $tmpstr = '';
      $db = conn::db();
      $lang = request::getForeLang();
      $para = $argPara;
      $osqlAry = $argOSQLAry;
      $paraTpl = base::getParameter($para, 'tpl');
      $paraJTBCTag = base::getParameter($para, 'jtbctag');
      $paraType = base::getParameter($para, 'type');
      $paraGenre = base::getParameter($para, 'genre');
      $paraField = base::getParameter($para, 'field');
      $paraOSQL = base::getParameter($para, 'osql');
      $paraOSQLOrder = base::getParameter($para, 'osqlorder');
      $paraRowFilter = base::getParameter($para, 'rowfilter');
      $paraBaseURL = base::getParameter($para, 'baseurl');
      $paraCache = base::getParameter($para, 'cache');
      $paraCacheTimeout = base::getNum(base::getParameter($para, 'cachetimeout'), 300);
      $paraVars = base::getParameter($para, 'vars');
      $paraLimit = base::getNum(base::getParameter($para, 'limit'), 0);
      $paraLang = base::getNum(base::getParameter($para, 'lang'), -100);
      $paraTransferID = base::getNum(base::getParameter($para, 'transferid'), 0);
      if ($paraLimit == 0) $paraLimit = 10;
      if ($paraLang == -100) $paraLang = $lang;
      $ns = __NAMESPACE__;
      $cacheAry = null;
      if (!base::isEmpty($paraCache))
      {
        $cacheData = cache::get($paraCache);
        if (is_array($cacheData))
        {
          if (count($cacheData) == 2)
          {
            $cacheAry = $cacheData[1];
            $cacheTimeStamp = $cacheData[0];
            if ((time() - $cacheTimeStamp) >= $paraCacheTimeout) cache::remove($paraCache);
          }
        }
      }
      if (!base::isEmpty($paraGenre))
      {
        $paraGenreAry = explode('&', $paraGenre);
        $paraFieldAry = explode('&', $paraField);
        $sqlstr = "select * from (";
        $sqlorderstr = '';
        foreach($paraGenreAry as $key => $val)
        {
          if (!base::isEmpty($val))
          {
            $table = tpl::take('global.' . $val . ':config.db_table', 'cfg');
            $prefix = tpl::take('global.' . $val . ':config.db_prefix', 'cfg');
            $sqlstr .= "select " . $prefix . "id as un_id, ";
            foreach($paraFieldAry as $keyF => $valF)
            {
              $sqlstr .= $prefix . $valF . " as un_" . $valF . ", ";
            }
            $sqlstr .= $prefix . "time as un_time, '" . addslashes($val) . "' as un_genre from " . $table;
            switch($paraType)
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
            if ($paraLang != -1) $sqlstr .= " and " . $prefix . "lang=" . $paraLang;
            $sqlstr .= " union all ";
          }
        }
        $sqlstr = base::getLRStr($sqlstr, ' union all ', 'leftr');
        $sqlstr .= ") jtbc where 1=1";
        if (!base::isEmpty($paraOSQL)) $sqlstr .= $paraOSQL;
        if (!base::isEmpty($paraOSQLOrder)) $sqlorderstr = $paraOSQLOrder;
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
        $sqlstr .= ' limit 0,' . $paraLimit;
        if (!base::isEmpty($paraTpl))
        {
          if (strpos($paraTpl, '.')) $tmpstr = tpl::take($paraTpl, 'tpl');
          else $tmpstr = tpl::take('global.transfer.' . $paraTpl, 'tpl');
        }
        else if (!base::isEmpty($paraJTBCTag))
        {
          if (array_key_exists($paraJTBCTag, tpl::$para)) $tmpstr = tpl::$para[$paraJTBCTag];
        }
        if (!base::isEmpty($paraVars))
        {
          $paraVarsAry = explode('|', $paraVars);
          foreach ($paraVarsAry as $key => $val)
          {
            if (!base::isEmpty($val))
            {
              $valAry = explode('=', $val);
              if (count($valAry) == 2) $tmpstr = str_replace('{$' . $valAry[0] + '}', $valAry[1], $tmpstr);
            }
          }
        }
        $myAry = $cacheAry;
        if (!is_array($myAry))
        {
          if (!is_null($db))
          {
            $myAry = $db -> fetchAll($sqlstr);
            if (!base::isEmpty($paraCache))
            {
              $cacheData = array();
              $cacheData[0] = time();
              $cacheData[1] = $myAry;
              @cache::put($paraCache, $cacheData);
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
            if (base::isEmpty($paraRowFilter) || !base::checkInstr($paraRowFilter, $rsindex))
            {
              $loopLineString = $loopString;
              $loopLineString = tpl::replaceTagByAry($loopLineString, $myVal, 11, $paraTransferID);
              $loopLineString = tpl::replaceTagByAry($loopLineString, array('-i' => $rsindex, '-lang' => $paraLang, '-baseurl' => $paraBaseURL));
              $tpl -> insertLoopLine(tpl::parse($loopLineString, 1));
            }
            $rsindex += 1;
          }
          $tmpstr = $tpl -> getTpl();
          $tmpstr = tpl::replaceTagByAry($tmpstr, array('-lang' => $paraLang, '-baseurl' => $paraBaseURL));
          $tmpstr = tpl::parse($tmpstr);
        }
        else $tmpstr = '';
      }
      return $tmpstr;
    }

    public static function transferStandard($argPara, $argOSQLAry = null)
    {
      $tmpstr = '';
      $db = conn::db();
      $genre = route::getCurrentGenre();
      $lang = request::getForeLang();
      $para = $argPara;
      $osqlAry = $argOSQLAry;
      $paraTpl = base::getParameter($para, 'tpl');
      $paraJTBCTag = base::getParameter($para, 'jtbctag');
      $paraType = base::getParameter($para, 'type');
      $paraGenre = base::getParameter($para, 'genre');
      $paraDBTable = base::getParameter($para, 'db_table');
      $paraDBPrefix = base::getParameter($para, 'db_prefix');
      $paraOSQL = base::getParameter($para, 'osql');
      $paraOSQLOrder = base::getParameter($para, 'osqlorder');
      $paraRowFilter = base::getParameter($para, 'rowfilter');
      $paraBaseURL = base::getParameter($para, 'baseurl');
      $paraCache = base::getParameter($para, 'cache');
      $paraCacheTimeout = base::getNum(base::getParameter($para, 'cachetimeout'), 300);
      $paraVars = base::getParameter($para, 'vars');
      $paraLimit = base::getNum(base::getParameter($para, 'limit'), 0);
      $paraCategory = base::getNum(base::getParameter($para, 'category'), 0);
      $paraGroup = base::getNum(base::getParameter($para, 'group'), 0);
      $paraLang = base::getNum(base::getParameter($para, 'lang'), -100);
      $paraTransferID = base::getNum(base::getParameter($para, 'transferid'), 0);
      if ($paraLimit == 0) $paraLimit = 10;
      if ($paraLang == -100) $paraLang = $lang;
      $ns = __NAMESPACE__;
      $cacheAry = null;
      if (!base::isEmpty($paraCache))
      {
        $cacheData = cache::get($paraCache);
        if (is_array($cacheData))
        {
          if (count($cacheData) == 2)
          {
            $cacheAry = $cacheData[1];
            $cacheTimeStamp = $cacheData[0];
            if ((time() - $cacheTimeStamp) >= $paraCacheTimeout) cache::remove($paraCache);
          }
        }
      }
      if (base::isEmpty($paraBaseURL))
      {
        if (!base::isEmpty($paraGenre) && $paraGenre != $genre)
        {
          $paraBaseURL = route::getActualRoute($paraGenre);
          if (base::getRight($paraBaseURL, 1) != '/') $paraBaseURL .= '/';
        }
      }
      if (base::isEmpty($paraGenre)) $paraGenre = $genre;
      if (base::isEmpty($paraDBTable)) $paraDBTable = tpl::take('global.' . $paraGenre . ':config.db_table', 'cfg');
      if (base::isEmpty($paraDBPrefix)) $paraDBPrefix = tpl::take('global.' . $paraGenre . ':config.db_prefix', 'cfg');
      if (!base::isEmpty($paraDBTable))
      {
        $sqlstr = '';
        $sqlorderstr = '';
        switch($paraType)
        {
          case 'count':
            $sqlstr = "select count(*) as rscount from " . $paraDBTable . " where " . $paraDBPrefix . "delete=0 and " . $paraDBPrefix . "publish=1";
            $sqlorderstr = " order by " . $paraDBPrefix . "id desc";
            break;
          case '@count':
            $sqlstr = "select count(*) as rscount from " . $paraDBTable . " where " . $paraDBPrefix . "delete=0";
            $sqlorderstr = " order by " . $paraDBPrefix . "id desc";
            break;
          case 'new':
            $sqlstr = "select * from " . $paraDBTable . " where " . $paraDBPrefix . "delete=0 and " . $paraDBPrefix . "publish=1";
            $sqlorderstr = " order by " . $paraDBPrefix . "time desc";
            break;
          case '@new':
            $sqlstr = "select * from " . $paraDBTable . " where " . $paraDBPrefix . "delete=0";
            $sqlorderstr = " order by " . $paraDBPrefix . "time desc";
            break;
          case 'top':
            $sqlstr = "select * from " . $paraDBTable . " where " . $paraDBPrefix . "delete=0 and " . $paraDBPrefix . "publish=1";
            $sqlorderstr = " order by " . $paraDBPrefix . "id desc";
            break;
          case '@top':
            $sqlstr = "select * from " . $paraDBTable . " where " . $paraDBPrefix . "delete=0";
            $sqlorderstr = " order by " . $paraDBPrefix . "id desc";
            break;
          case 'commendatory':
            $sqlstr = "select * from " . $paraDBTable . " where " . $paraDBPrefix . "delete=0 and " . $paraDBPrefix . "publish=1 and " . $paraDBPrefix . "commendatory=1";
            $sqlorderstr = " order by " . $paraDBPrefix . "time desc";
            break;
          case '@commendatory':
            $sqlstr = "select * from " . $paraDBTable . " where " . $paraDBPrefix . "delete=0 and " . $paraDBPrefix . "commendatory=1";
            $sqlorderstr = " order by " . $paraDBPrefix . "time desc";
            break;
          case 'order':
            $sqlstr = "select * from " . $paraDBTable . " where " . $paraDBPrefix . "delete=0 and " . $paraDBPrefix . "publish=1";
            $sqlorderstr = " order by " . $paraDBPrefix . "order asc";
            break;
          case '@order':
            $sqlstr = "select * from " . $paraDBTable . " where " . $paraDBPrefix . "delete=0";
            $sqlorderstr = " order by " . $paraDBPrefix . "order asc";
            break;
          default:
            $sqlstr = "select * from " . $paraDBTable . " where " . $paraDBPrefix . "delete=0";
            $sqlorderstr = " order by " . $paraDBPrefix . "id desc";
            break;
        }
        if ($paraLang != -1) $sqlstr .= " and " . $paraDBPrefix . "lang=" . $paraLang;
        if ($paraCategory != 0)
        {
          if (method_exists($ns . '\\universal\\category', 'getCategoryChildID'))
          {
            $sqlstr .= " and " . $paraDBPrefix . "category in (" . base::mergeIdAry($paraCategory, universal\category::getCategoryChildID($paraGenre, $paraLang, $paraCategory)) . ")";
          }
        }
        if ($paraGroup != 0) $sqlstr .= " and " . $paraDBPrefix . "group=" . $paraGroup;
        if (!base::isEmpty($paraOSQL)) $sqlstr .= $paraOSQL;
        if (!base::isEmpty($paraOSQLOrder)) $sqlorderstr = $paraOSQLOrder;
        if (is_array($osqlAry))
        {
          foreach ($osqlAry as $key => $val)
          {
            $valType = gettype($val);
            if ($valType == 'integer' || $valType == 'double') $sqlstr .= " and " . $paraDBPrefix . $key . "=" . base::getNum($val, 0);
            else if ($valType == 'string') $sqlstr .= " and " . $paraDBPrefix . $key . "='" . addslashes($val) . "'";
          }
        }
        $sqlstr .= $sqlorderstr;
        $sqlstr .= ' limit 0,' . $paraLimit;
        if (!base::isEmpty($paraTpl))
        {
          if (strpos($paraTpl, '.')) $tmpstr = tpl::take($paraTpl, 'tpl');
          else $tmpstr = tpl::take('global.transfer.' . $paraTpl, 'tpl');
        }
        else if (!base::isEmpty($paraJTBCTag))
        {
          if (array_key_exists($paraJTBCTag, tpl::$para)) $tmpstr = tpl::$para[$paraJTBCTag];
        }
        if (!base::isEmpty($paraVars))
        {
          $paraVarsAry = explode('|', $paraVars);
          foreach ($paraVarsAry as $key => $val)
          {
            if (!base::isEmpty($val))
            {
              $valAry = explode('=', $val);
              if (count($valAry) == 2) $tmpstr = str_replace('{$' . $valAry[0] + '}', $valAry[1], $tmpstr);
            }
          }
        }
        $myAry = $cacheAry;
        if (!is_array($myAry))
        {
          if (!is_null($db))
          {
            $myAry = $db -> fetchAll($sqlstr);
            if (!base::isEmpty($paraCache))
            {
              $cacheData = array();
              $cacheData[0] = time();
              $cacheData[1] = $myAry;
              @cache::put($paraCache, $cacheData);
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
            if (base::isEmpty($paraRowFilter) || !base::checkInstr($paraRowFilter, $rsindex))
            {
              $loopLineString = $loopString;
              $loopLineString = tpl::replaceTagByAry($loopLineString, $myVal, 11, $paraTransferID);
              $loopLineString = tpl::replaceTagByAry($loopLineString, array('-i' => $rsindex, '-genre' => $paraGenre, '-lang' => $paraLang, '-baseurl' => $paraBaseURL));
              $tpl -> insertLoopLine(tpl::parse($loopLineString, 1));
            }
            $rsindex += 1;
          }
          $tmpstr = $tpl -> getTpl();
          $tmpstr = tpl::replaceTagByAry($tmpstr, array('-genre' => $paraGenre, '-lang' => $paraLang, '-baseurl' => $paraBaseURL));
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