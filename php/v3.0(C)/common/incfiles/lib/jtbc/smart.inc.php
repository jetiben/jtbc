<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  class smart
  {
    public static function createURL($argType, $argKey, $argVars = null, $argGenre = null)
    {
      $tmpstr = '';
      $type = $argType;
      $key = $argKey;
      $vars = $argVars;
      $genre = $argGenre;
      if (is_null($genre)) $genre = page::getPara('genre');
      $urltype = base::getNum(tpl::take('global.' . $genre . ':config.urltype', 'cfg'), 0);
      switch($urltype)
      {
        case 0:
          switch($type)
          {
            case 'list':
              $tmpstr = '?type=list&category=' . base::getNum($key, 0);
              if (is_array($vars))
              {
                if (array_key_exists('page', $vars)) $tmpstr .= '&page=' . base::getString($vars['page']);
              }
              break;
            case 'detail':
              $tmpstr = '?type=detail&id=' . base::getNum($key, 0);
              if (is_array($vars))
              {
                if (array_key_exists('page', $vars)) $tmpstr .= '&page=' . base::getString($vars['page']);
              }
              break;
          }
          break;
        case 1:
          switch($type)
          {
            case 'list':
              $tmpstr = 'list-' . base::getNum($key, 0);
              if (is_array($vars))
              {
                if (array_key_exists('page', $vars)) $tmpstr .= '-' . base::getString($vars['page']);
              }
              $tmpstr .= '.html';
              break;
            case 'detail':
              $tmpstr = 'detail-' . base::getNum($key, 0);
              if (is_array($vars))
              {
                if (array_key_exists('page', $vars)) $tmpstr .= '-' . base::getString($vars['page']);
              }
              $tmpstr .= '.html';
              break;
          }
          break;
      }
      return $tmpstr;
    }

    public static function getRoute()
    {
      $route = '';
      if (is_file('common/root.jtbc')) $route = 'root';
      else if (is_file('../common/root.jtbc')) $route = 'node';
      else if (is_file('../../common/root.jtbc')) $route = 'child';
      else if (is_file('../../../common/root.jtbc')) $route = 'grandson';
      else if (is_file('../../../../common/root.jtbc')) $route = 'greatgrandson';
      return $route;
    }

    public static function getForeLang()
    {
      $language = LANGUAGE;
      $lang = base::getNum(tpl::take('global.config.lang-' . $language, 'cfg'), 0);
      $cookieValue = base::getString(@$_COOKIE[APPNAME . 'config']['language']);
      if (!base::isEmpty($cookieValue))
      {
        $cookieLang = base::getNum(tpl::take('global.config.lang-' . $cookieValue, 'cfg'), -1);
        if ($cookieLang != -1) $lang = $cookieLang;
      }
      return $lang;
    }

    public static function getActualRoute($argRoutestr = '', $argType = 0)
    {
      $route = '';
      $type = $argType;
      $routeStr = $argRoutestr;
      if ($type == 8 && !base::isEmpty(BASEDIR)) $route = BASEDIR . $routeStr;
      else
      {
        switch (page::getPara('route'))
        {
          case 'greatgrandson':
            $route = '../../../../' . $routeStr;
            break;
          case 'grandson':
            $route = '../../../' . $routeStr;
            break;
          case 'child':
            $route = '../../' . $routeStr;
            break;
          case 'node':
            $route = '../' . $routeStr;
            break;
          default:
            $route = $routeStr;
            break;
        }
      }
      return $route;
    }

    public static function getActualGenre($argRoute)
    {
      $tgenre = '';
      $route = $argRoute;
      $routeStr = $_SERVER['SCRIPT_NAME'];
      $routeStr = base::getLRStr($routeStr, '/', 'leftr');
      $ary = explode('/', $routeStr);
      $arycount = count($ary);
      switch ($route)
      {
        case 'greatgrandson':
          if ($arycount >= 4) $tgenre = $ary[$arycount - 4] . '/' . $ary[$arycount - 3] . '/' . $ary[$arycount - 2] . '/' . $ary[$arycount - 1];
          break;
        case 'grandson':
          if ($arycount >= 3) $tgenre = $ary[$arycount - 3] . '/' . $ary[$arycount - 2] . '/' . $ary[$arycount - 1];
          break;
        case 'child':
          if ($arycount >= 2) $tgenre = $ary[$arycount - 2] . '/' . $ary[$arycount - 1];
          break;
        case 'node':
          if ($arycount >= 1) $tgenre = $ary[$arycount - 1];
          break;
        default:
          $tgenre = '';
          break;
      }
      return $tgenre;
    }

    public static function getFolderByGuide($argFilePrefix = 'guide', $argPath = '', $argCacheName = '', $argPrefixVal = '')
    {
      $list = '';
      $order = '';
      $got = false;
      $path = $argPath;
      $fileprefix = $argFilePrefix;
      $cacheName = $argCacheName;
      $prefixVal = $argPrefixVal;
      $cacheMode = base::getNum(tpl::take('global.config.folder-guide-mode', 'cfg'), 0);
      $cacheTimeout = base::getNum(tpl::take('global.config.folder-guide-timeout', 'cfg'), 60);
      if (base::isEmpty($path))
      {
        $path = self::getActualRoute('./');
        if (base::isEmpty($cacheName))
        {
          $cacheName = 'folder-guide';
          if ($fileprefix != 'guide') $cacheName .= '-' . $fileprefix;
        }
      }
      if ($cacheMode == 1 && !base::isEmpty($cacheName))
      {
        $cacheData = cache::get($cacheName);
        if (is_array($cacheData))
        {
          if (count($cacheData) == 2)
          {
            $cacheVal = $cacheData[1];
            $cacheTimeStamp = $cacheData[0];
            if ((time() - $cacheTimeStamp) >= $cacheTimeout) cache::remove($cacheName);
            else
            {
              $got = true;
              $list = $cacheVal;
            }
          }
        }
      }
      if ($got == false)
      {
        $webdir = dir($path);
        $myguide = $path . '/common/guide' . XMLSFX;
        if (file_exists($myguide)) $order = tpl::getXRootAtt($myguide, 'order');
        while($entry = $webdir -> read())
        {
          if (!(is_numeric(strpos($entry, '.'))))
          {
            if (!(base::checkInstr($order, $entry, ',')))
            {
              $order .= ',' . $entry;
            }
          }
        }
        $webdir -> close();
        $orderary = explode(',', $order);
        if (is_array($orderary))
        {
          foreach($orderary as $key => $val)
          {
            if (!base::isEmpty($val))
            {
              $filename = $path . $val . '/common/' . $fileprefix . XMLSFX;
              if (file_exists($filename))
              {
                $list .= $prefixVal . $val . '|+|';
                if (tpl::getXRootAtt($filename, 'mode') == 'jtbcf') $list .= self::getFolderByGuide($fileprefix, $path . $val . '/', '', $prefixVal . $val . '/');
              }
            }
          }
        }
        if ($cacheMode == 1 && !base::isEmpty($cacheName))
        {
          $cacheData = array();
          $cacheData[0] = time();
          $cacheData[1] = $list;
          @cache::put($cacheName, $cacheData);
        }
      }
      return $list;
    }

    public static function getGenreByAppellation($argAppellation, $argOriGenre = '')
    {
      $genre = null;
      $appellation = $argAppellation;
      $oriGenre = $argOriGenre;
      if (base::isEmpty($oriGenre)) $oriGenre = page::getPara('genre');
      if (is_numeric(strpos($oriGenre, '/')))
      {
        $oriGenreAry = explode('/', $oriGenre);
        $oriGenreAryCount = count($oriGenreAry);
        if ($oriGenreAryCount == 2)
        {
          if ($appellation == 'parent') $genre = $oriGenreAry[0];
        }
        else if ($oriGenreAryCount == 3)
        {
          if ($appellation == 'grandparent') $genre = $oriGenreAry[0];
          else if ($appellation == 'parent') $genre = $oriGenreAry[0] . '/' . $oriGenreAry[1];
        }
        else if ($oriGenreAryCount == 4)
        {
          if ($appellation == 'greatgrandparent') $genre = $oriGenreAry[0];
          else if ($appellation == 'grandparent') $genre = $oriGenreAry[0] . '/' . $oriGenreAry[1];
          else if ($appellation == 'parent') $genre = $oriGenreAry[0] . '/' . $oriGenreAry[1] . '/' . $oriGenreAry[2];
        }
      }
      return $genre;
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

    public static function getAutoInsertSQLByVars($argTable, $argVars)
    {
      $table = $argTable;
      $vars = $argVars;
      $tmpstr = self::getAutoRequestInsertSQL($table, $vars, null, null, '', '', 1);
      return $tmpstr;
    }

    public static function getAutoRequestInsertSQL($argTable, $argVars = null, $argSpecialField = null, $argSource = null, $argNamePre = '', $argNameSuffix = '', $argMode = 0)
    {
      $tmpstr = '';
      $table = $argTable;
      $specialField = $argSpecialField;
      $namePre = $argNamePre;
      $nameSuffix = $argNameSuffix;
      $vars = $argVars;
      $source = $argSource;
      $mode = base::getNum($argMode, 0);
      $db = page::db();
      if (!is_null($db))
      {
        $columns = $db -> showFullColumns($table);
        if (is_array($columns))
        {
          $fieldString = '';
          $fieldValues = '';
          $tmpstr = 'insert into ' . $table . ' (';
          foreach ($columns as $i => $item)
          {
            $fieldValid = false;
            $fieldName = $item['Field'];
            $fieldType = $item['Type'];
            $comment = base::getString($item['Comment']);
            $fieldTypeN = $fieldType;
            $fieldTypeL = null;
            if (is_numeric(strpos($fieldType, '(')))
            {
              $fieldTypeN = base::getLRStr($fieldType, '(', 'left');
              $fieldTypeL = base::getNum(base::getLRStr(base::getLRStr($fieldType, '(', 'right'), ')', 'left'), 0);
            }
            $requestValue = '';
            $requestName = base::getLRStr($fieldName, '_', 'rightr');
            if (!base::isEmpty($namePre)) $requestName = $namePre . $requestName;
            if (!base::isEmpty($nameSuffix)) $requestName = $requestName . $nameSuffix;
            if (is_array($vars)) $requestValue = base::getString(@$vars[$fieldName]);
            if ($mode == 0)
            {
              if (!base::checkInstr($specialField, $fieldName, ','))
              {
                $manual = false;
                if (!base::isEmpty($comment))
                {
                  $commentAry = json_decode($comment, true);
                  if (!empty($commentAry) && array_key_exists('manual', $commentAry))
                  {
                    if ($commentAry['manual'] == 'true') $manual = true;
                  }
                }
                if ($manual == false)
                {
                  $fieldValid = true;
                  if (base::isEmpty($requestValue))
                  {
                    if (is_array($source)) $requestValue = base::getString($source[$requestName]);
                    else
                    {
                      $requestValue = request::getPost($requestName);
                      if (!is_array($requestValue)) $requestValue = base::getString($requestValue);
                      else $requestValue = base::getString(implode(',', $requestValue));
                    }
                  }
                }
              }
            }
            else if ($mode == 1)
            {
              if (is_array($vars))
              {
                if (array_key_exists($fieldName, $vars)) $fieldValid = true;
              }
            }
            if ($fieldValid == true)
            {
              if ($fieldTypeN == 'int' || $fieldTypeN == 'integer' || $fieldTypeN == 'double')
              {
                $fieldString .= $fieldName . ',';
                $fieldValues .= base::getNum($requestValue, 0) . ',';
              }
              else if ($fieldTypeN == 'varchar')
              {
                $fieldString .= $fieldName . ',';
                $fieldValues .= '\'' . addslashes(base::getLeft($requestValue, $fieldTypeL)) . '\',';
              }
              else if ($fieldTypeN == 'datetime')
              {
                $fieldString .= $fieldName . ',';
                $fieldValues .= '\'' . addslashes(base::getDateTime($requestValue)) . '\',';
              }
              else if ($fieldTypeN == 'text')
              {
                $fieldString .= $fieldName . ',';
                $fieldValues .= '\'' . addslashes(base::getLeft($requestValue, 20000)) . '\',';
              }
              else if ($fieldTypeN == 'mediumtext')
              {
                $fieldString .= $fieldName . ',';
                $fieldValues .= '\'' . addslashes(base::getLeft($requestValue, 5000000)) . '\',';
              }
              else if ($fieldTypeN == 'longtext')
              {
                $fieldString .= $fieldName . ',';
                $fieldValues .= '\'' . addslashes(base::getLeft($requestValue, 1000000000)) . '\',';
              }
            }
          }
          if (!base::isEmpty($fieldString)) $fieldString = base::getLRStr($fieldString, ',', 'leftr');
          if (!base::isEmpty($fieldValues)) $fieldValues = base::getLRStr($fieldValues, ',', 'leftr');
          $tmpstr .= $fieldString;
          $tmpstr .= ') values (';
          $tmpstr .= $fieldValues;
          $tmpstr .= ')';
        }
      }
      return $tmpstr;
    }

    public static function getAutoUpdateSQLByVars($argTable, $argIdField, $argId, $argVars)
    {
      $table = $argTable;
      $vars = $argVars;
      $idField = $argIdField;
      $id = base::getNum($argId, 0);
      $tmpstr = self::getAutoRequestUpdateSQL($table, $idField, $id, $vars, null, null, '', '', 1);
      return $tmpstr;
    }

    public static function getAutoRequestUpdateSQL($argTable, $argIdField, $argId, $argVars = null, $argSpecialField = null, $argSource = null, $argNamePre = '', $argNameSuffix = '', $argMode = 0)
    {
      $tmpstr = '';
      $table = $argTable;
      $specialField = $argSpecialField;
      $idField = $argIdField;
      $id = base::getNum($argId, 0);
      $namePre = $argNamePre;
      $nameSuffix = $argNameSuffix;
      $vars = $argVars;
      $source = $argSource;
      $mode = base::getNum($argMode, 0);
      $db = page::db();
      if (!is_null($db))
      {
        $columns = $db -> showFullColumns($table);
        if (is_array($columns))
        {
          $fieldStringValues = '';
          $tmpstr = 'update ' . $table . ' set ';
          foreach ($columns as $i => $item)
          {
            $fieldValid = false;
            $fieldName = $item['Field'];
            $fieldType = $item['Type'];
            $comment = base::getString($item['Comment']);
            $fieldTypeN = $fieldType;
            $fieldTypeL = null;
            if (is_numeric(strpos($fieldType, '(')))
            {
              $fieldTypeN = base::getLRStr($fieldType, '(', 'left');
              $fieldTypeL = base::getNum(base::getLRStr(base::getLRStr($fieldType, '(', 'right'), ')', 'left'), 0);
            }
            $requestValue = '';
            $requestName = base::getLRStr($fieldName, '_', 'rightr');
            if (!base::isEmpty($namePre)) $requestName = $namePre . $requestName;
            if (!base::isEmpty($nameSuffix)) $requestName = $requestName . $nameSuffix;
            if (is_array($vars)) $requestValue = base::getString(@$vars[$fieldName]);
            if ($mode == 0)
            {
              if (!base::checkInstr($specialField, $fieldName, ','))
              {
                $manual = false;
                if (!base::isEmpty($comment))
                {
                  $commentAry = json_decode($comment, true);
                  if (!empty($commentAry) && array_key_exists('manual', $commentAry))
                  {
                    if ($commentAry['manual'] == 'true') $manual = true;
                  }
                }
                if ($manual == false)
                {
                  $fieldValid = true;
                  if (base::isEmpty($requestValue))
                  {
                    if (is_array($source)) $requestValue = base::getString($source[$requestName]);
                    else
                    {
                      $requestValue = request::getPost($requestName);
                      if (!is_array($requestValue)) $requestValue = base::getString($requestValue);
                      else $requestValue = base::getString(implode(',', $requestValue));
                    }
                  }
                }
              }
            }
            else if ($mode == 1)
            {
              if (is_array($vars))
              {
                if (array_key_exists($fieldName, $vars)) $fieldValid = true;
              }
            }
            if ($fieldValid == true)
            {
              if ($fieldTypeN == 'int' || $fieldTypeN == 'integer' || $fieldTypeN == 'double')
              {
                $fieldStringValues .= $fieldName . '=' . base::getNum($requestValue, 0) . ',';
              }
              else if ($fieldTypeN == 'varchar')
              {
                $fieldStringValues .= $fieldName . '=\'' . addslashes(base::getLeft($requestValue, $fieldTypeL)) . '\',';
              }
              else if ($fieldTypeN == 'datetime')
              {
                $fieldStringValues .= $fieldName . '=\'' . addslashes(base::getDateTime($requestValue)) . '\',';
              }
              else if ($fieldTypeN == 'text')
              {
                $fieldStringValues .= $fieldName . '=\'' . addslashes(base::getLeft($requestValue, 20000)) . '\',';
              }
              else if ($fieldTypeN == 'mediumtext')
              {
                $fieldStringValues .= $fieldName . '=\'' . addslashes(base::getLeft($requestValue, 5000000)) . '\',';
              }
              else if ($fieldTypeN == 'longtext')
              {
                $fieldStringValues .= $fieldName . '=\'' . addslashes(base::getLeft($requestValue, 1000000000)) . '\',';
              }
            }
          }
          if (!base::isEmpty($fieldStringValues)) $fieldStringValues = base::getLRStr($fieldStringValues, ',', 'leftr');
          $tmpstr .= $fieldStringValues;
          $tmpstr .= ' where ' . $idField . '=' . $id;
        }
      }
      return $tmpstr;
    }

    public static function getAutoFieldFormatByTable($argTable, $argMode = 0, $argVars = null, $argTplPath = '::console')
    {
      $tmpstr = '';
      $table = $argTable;
      $mode = $argMode;
      $vars = $argVars;
      $tplPath = $argTplPath;
      $db = page::db();
      $filename = page::getPara('filename');
      $filePrefix = base::getLRStr($filename, '.', 'left');
      if (!is_null($db))
      {
        $columns = $db -> showFullColumns($table);
        foreach ($columns as $i => $item)
        {
          $fieldName = $item['Field'];
          $fieldDefault = $item['Default'];
          $comment = base::getString($item['Comment']);
          $simplifiedFieldName = base::getLRStr($fieldName, '_', 'rightr');
          if (!base::isEmpty($comment))
          {
            $commentAry = json_decode($comment, true);
            if (!empty($commentAry) && array_key_exists('fieldType', $commentAry))
            {
              $currentFieldRequired = '';
              if (array_key_exists('autoRequestFormat', $commentAry)) $currentFieldRequired = tpl::take($tplPath . '.required', 'tpl');
              $currentFieldType = base::getString($commentAry['fieldType']);
              if (strpos($currentFieldType, '.')) $fieldFormatLine = tpl::take($currentFieldType, 'tpl');
              else $fieldFormatLine = tpl::take($tplPath . '.fieldformat-' . $currentFieldType, 'tpl');
              $fieldFormatLine = str_replace('{$-required}', $currentFieldRequired, $fieldFormatLine);
              $fieldFormatLine = str_replace('{$fieldname}', base::htmlEncode($simplifiedFieldName), $fieldFormatLine);
              if ($currentFieldType == 'att')
              {
                $fieldRelatedEditor = base::getString(@$commentAry['fieldRelatedEditor']);
                if (!base::isEmpty($fieldRelatedEditor)) $fieldRelatedEditor = 'textarea.' . $fieldRelatedEditor;
                $fieldFormatLine = str_replace('{$-fieldRelatedEditor}', $fieldRelatedEditor, $fieldFormatLine);
              }
              else if ($currentFieldType == 'checkbox' || $currentFieldType == 'radio' || $currentFieldType == 'select')
              {
                $fieldRelatedFile = base::getString(@$commentAry['fieldRelatedFile']);
                $fieldFormatLine = str_replace('{$-fieldRelatedFile}', $fieldRelatedFile, $fieldFormatLine);
              }
              if (array_key_exists('fieldHasTips', $commentAry))
              {
                $fieldTipsKey = $simplifiedFieldName;
                $fieldHasTips = base::getString($commentAry['fieldHasTips']);
                $fieldFormatLineTips = tpl::take($tplPath . '.field-tips', 'tpl');
                if ($fieldHasTips != 'auto') $fieldTipsKey = $simplifiedFieldName;
                $currentFieldTips = @tpl::take($filePrefix . '.text-tips-field-' . $fieldTipsKey, 'lng');
                if (base::isEmpty($currentFieldTips)) $currentFieldTips = tpl::take($tplPath . '.text-tips-field-' . $fieldTipsKey, 'lng');
                $fieldFormatLineTips = str_replace('{$tips}', base::htmlEncode($currentFieldTips), $fieldFormatLineTips);
                $fieldFormatLine .= $fieldFormatLineTips;
              }
              if ($mode == 0)
              {
                $bindDefault = true;
                if (base::isEmpty($fieldDefault))
                {
                  if (array_key_exists('fieldDefault', $commentAry))
                  {
                    $fieldDefault = base::getString($commentAry['fieldDefault']);
                    if (base::isEmpty($fieldDefault)) $bindDefault = false;
                  }
                }
                else
                {
                  if (array_key_exists('fieldBindDefault', $commentAry))
                  {
                    $fieldBindDefault = base::getString($commentAry['fieldBindDefault']);
                    if ($fieldBindDefault == 'false') $bindDefault = false;
                  }
                }
                if ($bindDefault == false)
                {
                  $fieldFormatLine = str_replace('{$' . $simplifiedFieldName . '}', '', $fieldFormatLine);
                }
                else
                {
                  if ($fieldDefault == '$NOW') $fieldDefault = base::getDateTime();
                  else if ($fieldDefault == '$CURRENT_TIMESTAMP') $fieldDefault = strtotime(base::getDateTime());
                  else if ($fieldDefault == '$REMOTE_IP') $fieldDefault = request::getRemortIP();
                  else if ($fieldDefault == '$RANDOM_STRING') $fieldDefault = base::getRandomString();
                  else if ($fieldDefault == '$RANDOM_STRING_8') $fieldDefault = base::getRandomString(8);
                  else if ($fieldDefault == '$RANDOM_STRING_16') $fieldDefault = base::getRandomString(16);
                  else if ($fieldDefault == '$RANDOM_STRING_32') $fieldDefault = base::getRandomString(32);
                  else if ($fieldDefault == '$RANDOM_STRING_N4') $fieldDefault = base::getRandomString(4, 'number');
                  else if ($fieldDefault == '$RANDOM_STRING_N6') $fieldDefault = base::getRandomString(6, 'number');
                  else if ($fieldDefault == '$RANDOM_STRING_N8') $fieldDefault = base::getRandomString(8, 'number');
                  $fieldFormatLine = str_replace('{$' . $simplifiedFieldName . '}', base::htmlEncode($fieldDefault), $fieldFormatLine);
                }
              }
              $currentFieldHideMode = base::getNum(@$commentAry['fieldHideMode'], -1);
              if ($currentFieldHideMode != $mode) $tmpstr .= $fieldFormatLine;
            }
          }
        }
        if (is_array($vars))
        {
          foreach ($vars as $key => $val)
          {
            $tmpstr = str_replace('{$' . $key . '}', $val, $tmpstr) . $key;
          }
        }
      }
      return $tmpstr;
    }

    public static function pushAutoRequestErrorByTable(&$error, $argTable, $argTplPath = '::console')
    {
      $table = $argTable;
      $tplPath = $argTplPath;
      $db = page::db();
      $filename = page::getPara('filename');
      $filePrefix = base::getLRStr($filename, '.', 'left');
      if (!is_null($db))
      {
        $columns = $db -> showFullColumns($table);
        foreach ($columns as $i => $item)
        {
          $fieldName = $item['Field'];
          $comment = base::getString($item['Comment']);
          $requestName = base::getLRStr($fieldName, '_', 'rightr');
          if (!base::isEmpty($comment))
          {
            $commentAry = json_decode($comment, true);
            if (!empty($commentAry) && array_key_exists('autoRequestFormat', $commentAry))
            {
              $errorBool = false;
              $requestValue = request::getPost($requestName);
              $format = base::getString($commentAry['autoRequestFormat']);
              if ($format == 'notEmpty')
              {
                if (base::isEmpty($requestValue)) $errorBool = true;
              }
              else if ($format == 'email')
              {
                if (!verify::isEmail($requestValue)) $errorBool = true;
              }
              else if ($format == 'mobile')
              {
                if (!verify::isMobile($requestValue)) $errorBool = true;
              }
              if ($errorBool == true)
              {
                $errorMsg = @tpl::take($filePrefix . '.text-auto-request-error-' . $requestName, 'lng');
                if (base::isEmpty($errorMsg)) $errorMsg = tpl::take($tplPath . '.text-auto-request-error-' . $requestName, 'lng');
                array_push($error, $errorMsg);
              }
            }
          }
        }
      }
      else array_push($error, tpl::take($tplPath . '.text-error-db-102', 'lng'));
    }

    public static function replaceKeyWordHighlight($argString, $argKeyword = null)
    {
      $string = $argString;
      $keyword = $argKeyword;
      $spkey = 'jtbc~$~key~$~jtbc';
      if (!base::isEmpty($string))
      {
        if (!is_null($keyword))
        {
          $keywordAry = explode(' ', $keyword);
          $string = str_replace('*', $spkey, $string);
          foreach ($keywordAry as $key => $val)
          {
            if (!base::isEmpty($val))
            {
              $string = str_replace($val, '*key*' . $val . '*yek*', $string);
            }
          }
        }
        else
        {
          $string = str_replace('*key*', '<span class="highlight">', $string);
          $string = str_replace('*yek*', '</span>', $string);
          $string = str_replace($spkey, '*', $string);
        }
      }
      return $string;
    }

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
        $tpl = new tpl();
        $tpl -> tplString = $tmpstr;
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
        $tmpstr = $tpl -> mergeTemplate();
        $tmpstr = tpl::parse($tmpstr);
      }
      else $tmpstr = '';
      return $tmpstr;
    }

    public static function transferSQL($argPara, $argSQL)
    {
      $tmpstr = '';
      $db = page::db();
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
        $tpl = new tpl();
        $tpl -> tplString = $tmpstr;
        $loopString = $tpl -> getLoopString('{@}');
        foreach ($myAry as $myKey => $myVal)
        {
          if (base::isEmpty($paraRowFilter) || !base::checkInstr($paraRowFilter, $rsindex))
          {
            $loopLineString = $loopString;
            $loopLineString = tpl::replaceTagByAry($loopLineString, $myVal, 11, $paraTransferID);
            $loopLineString = tpl::replaceTagByAry($loopLineString, array('-i' => $rsindex));
            $tpl -> insertLoopLine(tpl::parse($loopLineString));
          }
          $rsindex += 1;
        }
        $tmpstr = $tpl -> mergeTemplate();
        $tmpstr = tpl::parse($tmpstr);
      }
      else $tmpstr = '';
      return $tmpstr;
    }

    public static function transferMultiGenre($argPara, $argOSQLAry = null)
    {
      $tmpstr = '';
      $db = page::db();
      $genre = page::getPara('genre');
      $lang = page::getPara('lang');
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
        else if (!base::isEmpty($paraJTBCTag)) $tmpstr = page::getPara($paraJTBCTag);
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
          $tpl = new tpl();
          $tpl -> tplString = $tmpstr;
          $loopString = $tpl -> getLoopString('{@}');
          foreach ($myAry as $myKey => $myVal)
          {
            if (base::isEmpty($paraRowFilter) || !base::checkInstr($paraRowFilter, $rsindex))
            {
              $loopLineString = $loopString;
              $loopLineString = tpl::replaceTagByAry($loopLineString, $myVal, 11, $paraTransferID);
              $loopLineString = tpl::replaceTagByAry($loopLineString, array('-i' => $rsindex, '-lang' => $paraLang, '-baseurl' => $paraBaseURL));
              $tpl -> insertLoopLine(tpl::parse($loopLineString));
            }
            $rsindex += 1;
          }
          $tmpstr = $tpl -> mergeTemplate();
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
      $db = page::db();
      $genre = page::getPara('genre');
      $lang = page::getPara('lang');
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
          $paraBaseURL = self::getActualRoute($paraGenre);
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
        else if (!base::isEmpty($paraJTBCTag)) $tmpstr = page::getPara($paraJTBCTag);
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
          $tpl = new tpl();
          $tpl -> tplString = $tmpstr;
          $loopString = $tpl -> getLoopString('{@}');
          foreach ($myAry as $myKey => $myVal)
          {
            if (base::isEmpty($paraRowFilter) || !base::checkInstr($paraRowFilter, $rsindex))
            {
              $loopLineString = $loopString;
              $loopLineString = tpl::replaceTagByAry($loopLineString, $myVal, 11, $paraTransferID);
              $loopLineString = tpl::replaceTagByAry($loopLineString, array('-i' => $rsindex, '-genre' => $paraGenre, '-lang' => $paraLang, '-baseurl' => $paraBaseURL));
              $tpl -> insertLoopLine(tpl::parse($loopLineString));
            }
            $rsindex += 1;
          }
          $tmpstr = $tpl -> mergeTemplate();
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
