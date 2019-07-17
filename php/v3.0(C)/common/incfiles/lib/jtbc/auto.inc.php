<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  class auto
  {
    public static $lastInsertId = null;

    public static function autoInsert($argVars = null, $argSpecialField = null, $argTable = null, $argPrefix = null, $argDbLink = 'any', $argSource = null, $argNamePre = '', $argNameSuffix = '', $argMode = 0)
    {
      $result = null;
      $table = $argTable;
      $prefix = $argPrefix;
      $dbLink = $argDbLink;
      $specialField = $argSpecialField;
      $namePre = $argNamePre;
      $nameSuffix = $argNameSuffix;
      $vars = $argVars;
      $source = $argSource;
      $mode = base::getNum($argMode, 0);
      $dal = new dal($table, $prefix, $dbLink);
      $db = $dal -> db;
      if (!is_null($db))
      {
        $dalSource = array();
        $columns = $db -> showFullColumns($dal -> table);
        if (is_array($columns))
        {
          foreach ($columns as $i => $item)
          {
            $fieldValid = false;
            $fieldName = $item['Field'];
            $comment = base::getString($item['Comment']);
            $requestValue = null;
            $requestName = base::getLRStr($fieldName, '_', 'rightr');
            if (!base::isEmpty($namePre)) $requestName = $namePre . $requestName;
            if (!base::isEmpty($nameSuffix)) $requestName = $requestName . $nameSuffix;
            if (is_array($vars))
            {
              if (array_key_exists($requestName, $vars))
              {
                $fieldValid = true;
                $requestValue = $vars[$requestName];
              }
            }
            if ($mode == 0 && $fieldValid == false)
            {
              $inSpecialField = false;
              if (!is_null($specialField))
              {
                $newSpecialFieldAry = array();
                $specialFieldAry = explode(',', $specialField);
                foreach ($specialFieldAry as $key => $val) array_push($newSpecialFieldAry, $dal -> prefix . $val);
                if (in_array($fieldName, $newSpecialFieldAry)) $inSpecialField = true;
              }
              if ($inSpecialField == false)
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
                  if (is_null($requestValue))
                  {
                    if (is_array($source))
                    {
                      if (array_key_exists($requestName, $source)) $requestValue = $source[$requestName];
                    }
                  }
                }
              }
            }
            if ($fieldValid == true)
            {
              if (is_array($requestValue)) $requestValue = base::getString(implode(',', $requestValue));
              $dalSource[$fieldName] = $requestValue;
            }
          }
        }
        $result = $dal -> insert($dalSource);
        self::$lastInsertId = $dal -> lastInsertId;
      }
      return $result;
    }

    public static function autoInsertByVars($argVars = null, $argTable = null, $argPrefix = null, $argDbLink = 'any')
    {
      $table = $argTable;
      $prefix = $argPrefix;
      $dbLink = $argDbLink;
      $vars = $argVars;
      $result = self::autoInsert($vars, null, $table, $prefix, $dbLink, null, '', '', 1);
      return $result;
    }

    public static function autoInsertByRequest($argVars = null, $argSpecialField = null, $argTable = null, $argPrefix = null, $argDbLink = 'any')
    {
      $table = $argTable;
      $prefix = $argPrefix;
      $dbLink = $argDbLink;
      $vars = $argVars;
      $specialField = $argSpecialField;
      $result = self::autoInsert($vars, $specialField, $table, $prefix, $dbLink, request::post(), '', '', 0);
      return $result;
    }

    public static function autoUpdate($argId, $argVars = null, $argSpecialField = null, $argTable = null, $argPrefix = null, $argDbLink = 'any', $argSource = null, $argNamePre = '', $argNameSuffix = '', $argMode = 0)
    {
      $result = null;
      $table = $argTable;
      $prefix = $argPrefix;
      $dbLink = $argDbLink;
      $specialField = $argSpecialField;
      $id = base::getNum($argId, 0);
      $namePre = $argNamePre;
      $nameSuffix = $argNameSuffix;
      $vars = $argVars;
      $source = $argSource;
      $mode = base::getNum($argMode, 0);
      $dal = new dal($table, $prefix, $dbLink);
      $dal -> id = $id;
      $db = $dal -> db;
      if (!is_null($db))
      {
        $dalSource = array();
        $columns = $db -> showFullColumns($dal -> table);
        if (is_array($columns))
        {
          foreach ($columns as $i => $item)
          {
            $fieldValid = false;
            $fieldName = $item['Field'];
            $comment = base::getString($item['Comment']);
            $requestValue = null;
            $requestName = base::getLRStr($fieldName, '_', 'rightr');
            if (!base::isEmpty($namePre)) $requestName = $namePre . $requestName;
            if (!base::isEmpty($nameSuffix)) $requestName = $requestName . $nameSuffix;
            if (is_array($vars))
            {
              if (array_key_exists($requestName, $vars))
              {
                $fieldValid = true;
                $requestValue = $vars[$requestName];
              }
            }
            if ($mode == 0 && $fieldValid == false)
            {
              $inSpecialField = false;
              if (!is_null($specialField))
              {
                $newSpecialFieldAry = array();
                $specialFieldAry = explode(',', $specialField);
                foreach ($specialFieldAry as $key => $val) array_push($newSpecialFieldAry, $dal -> prefix . $val);
                if (in_array($fieldName, $newSpecialFieldAry)) $inSpecialField = true;
              }
              if ($inSpecialField == false)
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
                  if (is_null($requestValue))
                  {
                    if (is_array($source))
                    {
                      if (array_key_exists($requestName, $source)) $requestValue = $source[$requestName];
                    }
                  }
                }
              }
            }
            if ($fieldValid == true)
            {
              if (is_array($requestValue)) $requestValue = base::getString(implode(',', $requestValue));
              $dalSource[$fieldName] = $requestValue;
            }
          }
        }
        $result = $dal -> update($dalSource);
      }
      return $result;
    }

    public static function autoUpdateByVars($argId, $argVars, $argTable = null, $argPrefix = null, $argDbLink = 'any')
    {
      $table = $argTable;
      $prefix = $argPrefix;
      $dbLink = $argDbLink;
      $vars = $argVars;
      $id = base::getNum($argId, 0);
      $result = self::autoUpdate($id, $vars, null, $table, $prefix, $dbLink, null, '', '', 1);
      return $result;
    }

    public static function autoUpdateByRequest($argId, $argVars = null, $argSpecialField = null, $argTable = null, $argPrefix = null, $argDbLink = 'any')
    {
      $table = $argTable;
      $prefix = $argPrefix;
      $dbLink = $argDbLink;
      $vars = $argVars;
      $id = base::getNum($argId, 0);
      $specialField = $argSpecialField;
      $result = self::autoUpdate($id, $vars, $specialField, $table, $prefix, $dbLink, request::post(), '', '', 0);
      return $result;
    }

    public static function getAutoFieldFormat($argFieldArray, $argMode = 0, $argVars = null, $argTplPath = null)
    {
      $tmpstr = '';
      $fieldArray = $argFieldArray;
      $mode = $argMode;
      $vars = $argVars;
      $tplPath = $argTplPath;
      if (is_null($tplPath))
      {
        $tplPath = '';
        if (!is_null(env::$majorGenre)) $tplPath = '::' . env::$majorGenre;
      }
      if (is_array($fieldArray))
      {
        foreach ($fieldArray as $i => $item)
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
              $currentFieldVar1 = $currentFieldVar2 = $currentFieldVar3 = 0;
              if (array_key_exists('fieldVar1', $commentAry)) $currentFieldVar1 = base::getNum($commentAry['fieldVar1'], 0);
              if (array_key_exists('fieldVar2', $commentAry)) $currentFieldVar1 = base::getNum($commentAry['fieldVar2'], 0);
              if (array_key_exists('fieldVar3', $commentAry)) $currentFieldVar1 = base::getNum($commentAry['fieldVar3'], 0);
              $fieldFormatLine = str_replace('{$-fieldVar1}', $currentFieldVar1, $fieldFormatLine);
              $fieldFormatLine = str_replace('{$-fieldVar2}', $currentFieldVar2, $fieldFormatLine);
              $fieldFormatLine = str_replace('{$-fieldVar3}', $currentFieldVar3, $fieldFormatLine);
              if ($currentFieldType == 'att')
              {
                $fieldRelatedEditor = '';
                if (array_key_exists('fieldRelatedEditor', $commentAry))
                {
                  $fieldRelatedEditor = 'textarea.' . base::getString($commentAry['fieldRelatedEditor']);
                }
                $fieldFormatLine = str_replace('{$-fieldRelatedEditor}', $fieldRelatedEditor, $fieldFormatLine);
              }
              else if ($currentFieldType == 'checkbox' || $currentFieldType == 'radio' || $currentFieldType == 'select')
              {
                $fieldRelatedFile = '';
                if (array_key_exists('fieldRelatedFile', $commentAry))
                {
                  $fieldRelatedFile = base::getString($commentAry['fieldRelatedFile']);
                }
                $fieldFormatLine = str_replace('{$-fieldRelatedFile}', $fieldRelatedFile, $fieldFormatLine);
              }
              if (array_key_exists('fieldHasTips', $commentAry))
              {
                $fieldTipsKey = $simplifiedFieldName;
                $fieldHasTips = base::getString($commentAry['fieldHasTips']);
                $fieldFormatLineTips = tpl::take($tplPath . '.field-tips', 'tpl');
                if ($fieldHasTips != 'auto') $fieldTipsKey = $simplifiedFieldName;
                $currentFieldTips = tpl::take('.text-tips-field-' . $fieldTipsKey, 'lng');
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
              $currentFieldHideMode = -1;
              if (array_key_exists('fieldHideMode', $commentAry))
              {
                $currentFieldHideMode = base::getNum($commentAry['fieldHideMode'], 0);
              }
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

    public static function getAutoFieldFormatByTable($argMode = 0, $argTable = null, $argPrefix = null, $argDbLink = 'any', $argVars = null, $argTplPath = null)
    {
      $tmpstr = '';
      $mode = $argMode;
      $table = $argTable;
      $prefix = $argPrefix;
      $dbLink = $argDbLink;
      $vars = $argVars;
      $tplPath = $argTplPath;
      $dal = new dal($table, $prefix, $dbLink);
      $db = $dal -> db;
      if (!is_null($db))
      {
        $fieldArray = array();
        $columns = $db -> showFullColumns($dal -> table);
        foreach ($columns as $i => $item)
        {
          $comment = base::getString($item['Comment']);
          if (!base::isEmpty($comment))
          {
            $commentAry = json_decode($comment, true);
            if (!empty($commentAry) && array_key_exists('fieldType', $commentAry))
            {
              array_push($fieldArray, $item);
            }
          }
        }
      }
      if (is_null($tplPath)) $tmpstr = self::getAutoFieldFormat($fieldArray, $mode, $vars);
      else $tmpstr = self::getAutoFieldFormat($fieldArray, $mode, $vars, $tplPath);
      return $tmpstr;
    }

    public static function getAutoFieldFormatByList($argList, $argMode = 0, $argTable = null, $argPrefix = null, $argDbLink = 'any', $argVars = null, $argTplPath = null)
    {
      $tmpstr = '';
      $list = $argList;
      $mode = $argMode;
      $table = $argTable;
      $prefix = $argPrefix;
      $dbLink = $argDbLink;
      $vars = $argVars;
      $tplPath = $argTplPath;
      if (is_array($list))
      {
        $dal = new dal($table, $prefix, $dbLink);
        $db = $dal -> db;
        if (!is_null($db))
        {
          $fieldArray = array();
          $columns = $db -> showFullColumns($dal -> table);
          foreach ($list as $key => $val)
          {
            foreach ($columns as $i => $item)
            {
              $fieldName = $item['Field'];
              $simplifiedFieldName = base::getLRStr($fieldName, '_', 'rightr');
              if ($val == $fieldName || $val == $simplifiedFieldName)
              {
                $comment = base::getString($item['Comment']);
                if (!base::isEmpty($comment))
                {
                  $commentAry = json_decode($comment, true);
                  if (!empty($commentAry) && array_key_exists('fieldType', $commentAry))
                  {
                    array_push($fieldArray, $item);
                  }
                }
                break;
              }
            }
          }
        }
        if (is_null($tplPath)) $tmpstr = self::getAutoFieldFormat($fieldArray, $mode, $vars);
        else $tmpstr = self::getAutoFieldFormat($fieldArray, $mode, $vars, $tplPath);
      }
      return $tmpstr;
    }

    public static function pushAutoRequestErrorByTable(&$error, $argTable = null, $argPrefix = null, $argDbLink = 'any', $argNamePre = '', $argNameSuffix = '', $argTplPath = null)
    {
      $table = $argTable;
      $prefix = $argPrefix;
      $dbLink = $argDbLink;
      $namePre = $argNamePre;
      $nameSuffix = $argNameSuffix;
      $tplPath = $argTplPath;
      if (is_null($tplPath))
      {
        $tplPath = '';
        if (!is_null(env::$majorGenre)) $tplPath = '::' . env::$majorGenre;
      }
      $dal = new dal($table, $prefix, $dbLink);
      $db = $dal -> db;
      if (!is_null($db))
      {
        $columns = $db -> showFullColumns($dal -> table);
        foreach ($columns as $i => $item)
        {
          $fieldName = $item['Field'];
          $comment = base::getString($item['Comment']);
          $requestName = base::getLRStr($fieldName, '_', 'rightr');
          if (!base::isEmpty($namePre)) $requestName = $namePre . $requestName;
          if (!base::isEmpty($nameSuffix)) $requestName = $requestName . $nameSuffix;
          if (!base::isEmpty($comment))
          {
            $commentAry = json_decode($comment, true);
            if (!empty($commentAry) && array_key_exists('autoRequestFormat', $commentAry))
            {
              $errorPush = function($argName, $argFormat) use ($tplPath, &$error)
              {
                $name = $argName;
                $format = $argFormat;
                $errorMsg = tpl::take('.text-auto-request-error-' . $name . '-' . $format, 'lng');
                if (base::isEmpty($errorMsg))
                {
                  $errorMsg = tpl::take('.text-auto-request-error-' . $name, 'lng');
                  if (base::isEmpty($errorMsg))
                  {
                    $errorMsg = tpl::take($tplPath . '.text-auto-request-error-' . $name, 'lng');
                  }
                }
                array_push($error, $errorMsg);
              };
              $requestValue = request::getPost($requestName);
              $format = base::getString($commentAry['autoRequestFormat']);
              $formatAry = explode('|', $format);
              foreach ($formatAry as $key => $val)
              {
                $willPush = false;
                if ($val == 'email')
                {
                  if (!verify::isEmail($requestValue)) $willPush = true;
                }
                else if ($val == 'idcard')
                {
                  if (!verify::isIDCard($requestValue)) $willPush = true;
                }
                else if ($val == 'mobile')
                {
                  if (!verify::isMobile($requestValue)) $willPush = true;
                }
                else if ($val == 'number')
                {
                  if (!verify::isNumber($requestValue)) $willPush = true;
                }
                else if ($val == 'notEmpty')
                {
                  if (base::isEmpty($requestValue)) $willPush = true;
                }
                if ($willPush == true) $errorPush($requestName, $val);
              }
            }
          }
        }
      }
      else array_push($error, tpl::take($tplPath . '.text-error-db-102', 'lng'));
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>