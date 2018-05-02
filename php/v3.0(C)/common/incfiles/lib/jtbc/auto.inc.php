<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  class auto
  {
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
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>
