<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  use DOMXPath;
  use DOMDocument;
  class tpl
  {
    public $tplString;
    public $tplAString = '';
    public $tplRString = '';
    private $tplCString = '<!--JTBC_CINFO-->';
    public static $counter = 0;
    public static $para = array();

    public function changeTemplate(&$templatestr, $argDistinstr)
    {
      $tmpstr = '';
      $distinstr = $argDistinstr;
      if (is_numeric(strpos($templatestr, $distinstr)))
      {
        $arys = explode($distinstr, $templatestr);
        if (count($arys) == 3)
        {
          $templatestr = $arys[0] . $this -> tplCString . $arys[2];
          $tmpstr = $arys[1];
        }
      }
      return $tmpstr;
    }

    public function getLoopString($argTag)
    {
      $tag = $argTag;
      $this -> tplAString = $this -> changeTemplate($this -> tplString, $tag);
      $tmpstr = $this -> tplAString;
      return $tmpstr;
    }

    public function insertLoopLine($argString)
    {
      $string = $argString;
      $this -> tplRString .= $string;
    }

    public function mergeTemplate()
    {
      $this -> tplString = str_replace($this -> tplCString, $this -> tplRString, $this -> tplString);
      $tmpstr = $this -> tplString;
      return $tmpstr;
    }

    public function strReplace($argString1, $argString2)
    {
      $string1 = $argString1;
      $string2 = $argString2;
      $tmpstr = $this -> tplString;
      $tmpstr = str_replace($string1, $string2, $tmpstr);
      $this -> tplString = $tmpstr;
    }

    public static function bring($argCodeName, $argType = 'tpl', $argValue = '', $argNodeName = null)
    {
      $bool = false;
      $type = $argType;
      $codename = $argCodeName;
      $value = $argValue;
      $nodeName = $argNodeName;
      $codename = self::getAbbrTransKey($codename);
      $routeStr = self::getXMLRoute($codename, $type);
      $key = base::getLRStr($codename, '.', 'right');
      $activeValue = self::getActiveValue($type);
      if (!base::isEmpty($nodeName)) $activeValue = $nodeName;
      $bool = self::setXMLInfo($routeStr, $activeValue, $key, $value);
      return $bool;
    }

    public static function getGlobalsVars($argName)
    {
      $tmpstr = '';
      $name = $argName;
      if (!base::isEmpty($name))
      {
        $tmpstr = base::getSwapString(@$GLOBALS[$name], base::getSwapString(@$GLOBALS['RS_' . $name], @$GLOBALS['RST_' . $name]));
      }
      return $tmpstr;
    }

    public static function getActiveValue($argType)
    {
      $tmpstr = '';
      $key = '';
      $type = $argType;
      switch($type)
      {
        case 'cfg':
          $key = 'language';
          break;
        case 'lng':
          $key = 'language';
          break;
        case 'sel':
          $key = 'language';
          break;
        case 'tpl':
          $key = 'template';
          break;
      }
      if (!base::isEmpty($key))
      {
        if ($key == 'language') $tmpstr = LANGUAGE;
        else if ($key == 'template') $tmpstr = TEMPLATE;
        $cookieValue = base::getString(@$_COOKIE[APPNAME . 'config'][$key]);
        if (!base::isEmpty($cookieValue)) $tmpstr = $cookieValue;
      }
      return $tmpstr;
    }

    public static function getAbbrTransKey($argCodeName)
    {
      $codename = $argCodeName;
      if (!base::isEmpty($codename))
      {
        if (substr($codename, 0, 3) == '../')
        {
          $parent = route::getGenreByAppellation('parent');
          $grandparent = route::getGenreByAppellation('grandparent');
          $greatgrandparent = route::getGenreByAppellation('greatgrandparent');
          if (substr($codename, 0, 9) == '../../../')
          {
            if (!is_null($greatgrandparent))
            {
              if (is_numeric(strpos($codename, ':'))) $codename = str_replace('../../../', 'global.' . $greatgrandparent . '/', $codename);
              else $codename = str_replace('../../../', 'global.' . $greatgrandparent . ':', $codename);
            }
          }
          else if (substr($codename, 0, 6) == '../../')
          {
            if (!is_null($grandparent))
            {
              if (is_numeric(strpos($codename, ':'))) $codename = str_replace('../../', 'global.' . $grandparent . '/', $codename);
              else $codename = str_replace('../../', 'global.' . $grandparent . ':', $codename);
            }
          }
          else if (substr($codename, 0, 3) == '../')
          {
            if (!is_null($parent))
            {
              if (is_numeric(strpos($codename, ':'))) $codename = str_replace('../', 'global.' . $parent . '/', $codename);
              else $codename = str_replace('../', 'global.' . $parent . ':', $codename);
            }
          }
        }
        else if (substr($codename, 0, 1) == '.')
        {
          if (substr_count($codename, '.') == 2) $codename = 'global.' . base::getLRStr($codename, '.', 'rightr');
        }
        else if (substr($codename, 0, 2) == '::') $codename = 'global.' . CONSOLEDIR . ':' . base::getLRStr($codename, '::', 'right');
        else if (substr($codename, 0, 2) == ':/') $codename = 'global.' . CONSOLEDIR . '/' . base::getLRStr($codename, ':/', 'right');
      }
      return $codename;
    }

    public static function getEvalValue($argString)
    {
      $tstr = '';
      $string = $argString;
      $ns = __NAMESPACE__;
      if (!base::isEmpty($string))
      {
        if (substr($string, 0, 1) == '$')
        {
          if (class_exists($ns . '\\page'))
          {
            $string = substr($string, 1, strlen($string) - 1);
            $tstr = page::getPara($string);
          }
        }
        else if (substr($string, 0, 1) == '#')
        {
          $string = substr($string, 1, strlen($string) - 1);
          $tstr = self::getGlobalsVars($string);
        }
        else
        {
          if (is_numeric(strpos($string, '(')))
          {
            $classArray = array('pagi', 'page', 'base', 'request', 'route', 'tpl', 'transfer');
            if (is_numeric(strpos($string, '$')))
            {
              $regm = preg_match_all('(\$(.[^\(]*)\()', $string, $innerFun);
              if ($regm)
              {
                for ($i = 0; $i <= count($innerFun[0]) - 1; $i ++)
                {
                  $funName = $innerFun[1][$i];
                  if (!function_exists($funName))
                  {
                    foreach ($classArray as $key => $val)
                    {
                      if (is_callable(array($ns . '\\' . $val, $funName)))
                      {
                        $string = str_replace('$' . $funName, $ns . '\\' . $val . '::' . $funName, $string);
                        break;
                      }
                    }
                  }
                }
              }
            }
            if (is_numeric(strpos($string, '#')))
            {
              $regm = preg_match_all('(#(.[^(\)|\,)]*))', $string, $innerVars);
              if ($regm)
              {
                for ($i = 0; $i <= count($innerVars[0]) - 1; $i ++)
                {
                  $varsName = trim($innerVars[1][$i]);
                  if (!base::isEmpty($varsName))
                  {
                    $varsName = str_replace('\'', '', $varsName);
                    $string = str_replace('#' . $varsName, $ns . '\\tpl::getGlobalsVars(\'' . $varsName . '\')', $string);
                  }
                }
              }
            }
            $fun = base::getLRStr($string, '(', 'left');
            if (function_exists($fun)) eval('$tstr = ' . $string . ';');
            else
            {
              foreach ($classArray as $key => $val)
              {
                if (is_callable(array($ns . '\\' . $val, $fun)))
                {
                  eval('$tstr = ' . $ns . '\\' . $val . '::' . $string . ';');
                  break;
                }
              }
            }
          }
          else eval('$tstr = ' . $string . ';');
        }
      }
      return $tstr;
    }

    public static function getXRootAtt($argSourcefile, $argAtt)
    {
      $sourceFile = $argSourcefile;
      $att = $argAtt;
      $rests = null;
      if (is_file($sourceFile))
      {
        $doc = new DOMDocument();
        $doc -> load($sourceFile);
        $xpath = new DOMXPath($doc);
        $query = '//xml';
        $rests = $xpath -> query($query) -> item(0) -> getAttribute($att);
      }
      return $rests;
    }

    public static function getXMLInfo($argSourcefile, $argKeyword, $argType = null)
    {
      $type = $argType;
      $keyword = $argKeyword;
      $sourceFile = $argSourcefile;
      $info = array();
      if (is_file($sourceFile))
      {
        $doc = new DOMDocument();
        $doc -> load($sourceFile);
        $xpath = new DOMXPath($doc);
        $query = '//xml/configure/node';
        $node = $xpath -> query($query) -> item(0) -> nodeValue;
        $query = '//xml/configure/field';
        $field = $xpath -> query($query) -> item(0) -> nodeValue;
        $query = '//xml/configure/base';
        $base = $xpath -> query($query) -> item(0) -> nodeValue;
        $fieldArys = explode(',', $field);
        $fieldLength = count($fieldArys);
        if ($fieldLength >= 2)
        {
          if (!in_array($keyword, $fieldArys)) $keyword = $fieldArys[1];
          $query = '//xml/' . $base . '/' . $node;
          $rests = $xpath -> query($query);
          foreach ($rests as $rest)
          {
            $nodeDom = $rest -> getElementsByTagName($keyword);
            if ($nodeDom -> length == 0) $nodeDom = $rest -> getElementsByTagName($fieldArys[1]);
            $nodeDomObj = $nodeDom -> item(0);
            $nodeDomValue = $nodeDomObj -> nodeValue;
            if (!is_null($type) && base::isEmpty($nodeDomValue))
            {
              if ($nodeDomObj -> hasAttribute('pointer')) $nodeDomValue = self::take($nodeDomObj -> getAttribute('pointer'), $type);
            }
            $info[$rest -> getElementsByTagName(current($fieldArys)) -> item(0) -> nodeValue] = $nodeDomValue;
          }
        }
      }
      return $info;
    }

    public static function getXMLDirByType($argType)
    {
      $dir = '';
      $type = $argType;
      switch($type)
      {
        case 'cfg':
          $dir = 'common';
          break;
        case 'lng':
          $dir = 'common/language';
          break;
        case 'sel':
          $dir = 'common/language';
          break;
        case 'tpl':
          $dir = 'common/template';
          break;
        default:
          $dir = 'common';
          break;
      }
      return $dir;
    }

    public static function getXMLRoute($argCodeName, $argType)
    {
      $type = $argType;
      $codename = $argCodeName;
      $dir = self::getXMLDirByType($type);
      $routeStr = base::getLRStr($codename, '.', 'leftr');
      if (substr($routeStr, 0, 7) == 'global.')
      {
        $routeStr = substr($routeStr, 7, strlen($routeStr) - 7);
        if (is_numeric(strpos($routeStr, ':')))
        {
          $routeStr = base::getLRStr($routeStr, ':', 'left') . '/' . $dir . '/' . base::getLRStr($routeStr, ':', 'right') . XMLSFX;
        }
        else
        {
          $routeStr = $dir . '/' . $routeStr . XMLSFX;
        }
      }
      else
      {
        $genre = route::getCurrentGenre();
        if (base::isEmpty($routeStr)) $routeStr = $dir . '/' . base::getLRStr(route::getCurrentFilename(), '.', 'left') . XMLSFX;
        else
        {
          if (is_numeric(strpos($routeStr, ':')))
          {
            $routeStr = base::getLRStr($routeStr, ':', 'left') . '/' . $dir . '/' . base::getLRStr($routeStr, ':', 'right') . XMLSFX;
          }
          else
          {
            $routeStr = $dir . '/' . $routeStr . XMLSFX;
          }
        }
        if (!base::isEmpty($genre)) $routeStr = $genre . '/' . $routeStr;
      }
      $routeStr = route::getActualRoute($routeStr);
      return $routeStr;
    }

    public static function parse($argString)
    {
      $tmpstr = $argString;
      if (!base::isEmpty($tmpstr))
      {
        $regtag = preg_match_all('/<jtbc[^>]*>(.*?)<\/jtbc>/is', $tmpstr, $regArys);
        if ($regtag)
        {
          for ($i = 0; $i <= count($regArys[0]) - 1; $i ++)
          {
            $tagtext = $regArys[0][$i];
            if (is_numeric(strpos($tagtext, '$function="')) && is_numeric(strpos($tagtext, '$parameter="')))
            {
              $function = base::getLRStr(base::getLRStr($tagtext, '$function="', 'rightr'), '"', 'left');
              $parameter = base::getLRStr(base::getLRStr($tagtext, '$parameter="', 'rightr'), '"', 'left');
              if ($function == 'transfer')
              {
                self::$counter += 1;
                self::$para['jtbctag' . self::$counter] = '{@}' . $regArys[1][$i] . '{@}';
                $evalfunction = $function . '(\'' . $parameter . ';jtbctag=jtbctag' . self::$counter . '\')';
                $tmpstr = str_replace($tagtext, self::getEvalValue($evalfunction), $tmpstr);
              }
            }
          }
        }
        $regm = preg_match_all('({\$=(.[^\}]*)})', $tmpstr, $regArys);
        if ($regm)
        {
          for ($i = 0; $i <= count($regArys[0]) - 1; $i ++)
          {
            $tmpstr = str_replace($regArys[0][$i], self::getEvalValue($regArys[1][$i]), $tmpstr);
          }
        }
      }
      return $tmpstr;
    }

    public static function replaceTagByAry($argString, $argAry, $argMode = 0, $argModeID = 0, $argEncode = 1)
    {
      $string = $argString;
      $ary = $argAry;
      $mode = $argMode;
      $modeid = $argModeID;
      $encode = $argEncode;
      if (!base::isEmpty($string) && is_array($ary))
      {
        foreach ($ary as $key => $val)
        {
          if ($mode >= 10 && $mode < 20)
          {
            $key = base::getLRStr($key, '_', 'rightr');
            if ($mode == 10) $GLOBALS['RS_' . $key] = $val;
            else if ($mode == 11)
            {
              if ($modeid == 0) $GLOBALS['RST_' . $key] = $val;
              else $GLOBALS['RST' . $modeid . '_' . $key] = $val;
            }
          }
          else if ($mode >= 20 && $mode < 30)
          {
            if ($mode == 21)
            {
              if ($modeid == 0) $GLOBALS['RST_' . $key] = $val;
              else $GLOBALS['RST' . $modeid . '_' . $key] = $val;
            }
          }
          if ($encode == 1) $val = base::htmlEncode($val);
          $string = str_replace('{$' . $key . '}', $val, $string);
        }
      }
      return $string;
    }

    public static function replaceOriginalTagByAry($argString, $argAry, $argMode = 0, $argModeID = 0)
    {
      $string = $argString;
      $ary = $argAry;
      $mode = $argMode;
      $modeid = $argModeID;
      $tmpstr = self::replaceTagByAry($string, $ary, $mode, $modeid, 0);
      return $tmpstr;
    }

    public static function setXRootAtt($argSourcefile, $argAtt, $argValue)
    {
      $bool = false;
      $sourceFile = $argSourcefile;
      $att = $argAtt;
      $value = $argValue;
      if (is_file($sourceFile))
      {
        $doc = new DOMDocument();
        $doc -> load($sourceFile);
        $xpath = new DOMXPath($doc);
        $query = '//xml';
        $xpath -> query($query) -> item(0) -> setAttribute($att, $value);
        $bool = $doc -> save($sourceFile);
      }
      return $bool;
    }

    public static function setXMLInfo($argSourcefile, $argKeyword, $argName, $argValue)
    {
      $bool = false;
      $keyword = $argKeyword;
      $sourceFile = $argSourcefile;
      $name = $argName;
      $value = $argValue;
      if (is_file($sourceFile))
      {
        $doc = new DOMDocument();
        $doc -> load($sourceFile);
        $xpath = new DOMXPath($doc);
        $query = '//xml/configure/node';
        $node = $xpath -> query($query) -> item(0) -> nodeValue;
        $query = '//xml/configure/field';
        $field = $xpath -> query($query) -> item(0) -> nodeValue;
        $query = '//xml/configure/base';
        $base = $xpath -> query($query) -> item(0) -> nodeValue;
        $fieldArys = explode(',', $field);
        $fieldLength = count($fieldArys);
        if ($fieldLength >= 2)
        {
          if (!in_array($keyword, $fieldArys)) $keyword = $fieldArys[1];
          $query = '//xml/' . $base . '/' . $node;
          $rests = $xpath -> query($query);
          foreach ($rests as $rest)
          {
            $nodeDom = $rest -> getElementsByTagName($keyword);
            if ($nodeDom -> length == 0) $nodeDom = $rest -> getElementsByTagName($fieldArys[1]);
            if ($rest -> getElementsByTagName(current($fieldArys)) -> item(0) -> nodeValue == $name)
            {
              $nodeDom -> item(0) -> nodeValue = '';
              $nodeDom -> item(0) -> appendChild($doc -> createCDATASection($value));
            }
          }
        }
        $bool = $doc -> save($sourceFile);
      }
      return $bool;
    }

    public static function take($argCodeName, $argType = null, $argParse = 0, $argVars = null, $argNodeName = null)
    {
      $result = '';
      $type = $argType;
      $codename = $argCodeName;
      $ns = __NAMESPACE__;
      $parse = base::getNum($argParse, 0);
      $vars = $argVars;
      $nodeName = base::getString($argNodeName);
      if (is_null($type))
      {
        $type = 'tpl';
        $parse = 1;
      }
      if (is_array($codename))
      {
        $codenameLength = count($codename);
        if ($codenameLength == 2)
        {
          $newcodename = $codename[0];
          if (base::isMobileAgent()) $newcodename = $codename[1];
          $codename = $newcodename;
        }
      }
      $codename = self::getAbbrTransKey($codename);
      $routeStr = self::getXMLRoute($codename, $type);
      $keywords = base::getLRStr($codename, '.', 'right');
      $activeValue = self::getActiveValue($type);
      if (!base::isEmpty($nodeName)) $activeValue = $nodeName;
      $globalStr = $routeStr;
      $globalStr = str_replace('../', '', $globalStr);
      $globalStr = str_replace(XMLSFX, '', $globalStr);
      $globalStr = str_replace('/', '_', $globalStr);
      $globalStr = APPNAME . $globalStr . '_' . $activeValue;
      if (!is_array(@$GLOBALS[$globalStr])) $GLOBALS[$globalStr] = self::getXMLInfo($routeStr, $activeValue, $type);
      if (isset($GLOBALS[$globalStr][$keywords]))
      {
        $result = $GLOBALS[$globalStr][$keywords];
        if ($type == 'cfg')
        {
          $result = str_replace('{$>db.table.prefix}', DB_TABLE_PREFIX, $result);
        }
        else if ($type == 'tpl')
        {
          $genre = route::getCurrentGenre();
          $tthis = base::getLRStr($codename, '.', 'leftr');
          $tthisGenre = $genre;
          if (is_numeric(strpos($codename, ':'))) $tthisGenre = base::getLRStr(base::getLRStr($codename, ':', 'leftr'), 'global.', 'right');
          $result = str_replace('{$>genre}', $genre, $result);
          $result = str_replace('{$>now}', base::getDateTime(), $result);
          $result = str_replace('{$>ns}', $ns . '\\', $result);
          $result = str_replace('{$>this}', $tthis, $result);
          $result = str_replace('{$>this.genre}', $tthisGenre, $result);
          $result = str_replace('{$>genre.parent}', route::getGenreByAppellation('parent', $genre), $result);
          $result = str_replace('{$>genre.grandparent}', route::getGenreByAppellation('grandparent', $genre), $result);
          $result = str_replace('{$>genre.greatgrandparent}', route::getGenreByAppellation('greatgrandparent', $genre), $result);
          $result = str_replace('{$>this.genre.parent}', route::getGenreByAppellation('parent', $tthisGenre), $result);
          $result = str_replace('{$>this.genre.grandparent}', route::getGenreByAppellation('grandparent', $tthisGenre), $result);
          $result = str_replace('{$>this.genre.greatgrandparent}', route::getGenreByAppellation('greatgrandparent', $tthisGenre), $result);
        }
        if (is_array($vars))
        {
          foreach ($vars as $key => $val) $result = str_replace('{$' . $key . '}', $val, $result);
        }
        else if (!empty($vars))
        {
          $jsonvars = json_decode($vars, 1);
          if (is_array($jsonvars))
          {
            foreach ($jsonvars as $key => $val) $result = str_replace('{$' . $key . '}', $val, $result);
          }
        }
        if ($parse == 1) $result = self::parse($result);
      }
      if (base::isEmpty($result))
      {
        if ($keywords == '*') $result = $GLOBALS[$globalStr];
        else if (is_numeric(strpos($keywords, ',')))
        {
          $result = array();
          $tempResult = $GLOBALS[$globalStr];
          $keywordsAry = explode(',', $keywords);
          foreach($keywordsAry as $key => $val)
          {
            $value = '';
            if (isset($tempResult[$val])) $value = $tempResult[$val];
            $result[$val] = $value;
          }
        }
      }
      return $result;
    }

    public static function takeByNode($argCodeName, $argNodeName = null, $argType = null, $argParse = 0, $argVars = null)
    {
      return self::take($argCodeName, $argType, $argParse, $argVars, $argNodeName);
    }

    public static function takeAndFormat($argCodeName, $argType, $argTpl)
    {
      $tmpstr = '';
      $type = $argType;
      $codename = $argCodeName;
      $tpl = $argTpl;
      $xmlAry = self::take($codename, $type);
      if (!is_array($xmlAry))
      {
        $value = $xmlAry;
        $xmlAry = array();
        $xmlAry[base::getLRStr($codename, '.', 'right')] = $value;
      }
      if (is_array($xmlAry))
      {
        $tmpstr = self::take($tpl, 'tpl');
        $tpl = new tpl($tmpstr);
        $loopString = $tpl -> getLoopString('{@}');
        foreach ($xmlAry as $key => $val)
        {
          $loopLineString = $loopString;
          $loopLineString = str_replace('{$key}', base::htmlEncode($key), $loopLineString);
          $loopLineString = str_replace('{$val}', base::htmlEncode($val), $loopLineString);
          $tpl -> insertLoopLine($loopLineString);
        }
        $tmpstr = $tpl -> mergeTemplate();
        $tmpstr = self::parse($tmpstr);
      }
      return $tmpstr;
    }

    public static function xmlSelect($argString, $argValue, $argTemplate, $argName = '')
    {
      $tmpstr = '';
      $string = $argString;
      $value = $argValue;
      $template = $argTemplate;
      $name = $argName;
      $xinfostr = $string;
      $selstr = '';
      if (is_numeric(strpos($string, '|')))
      {
        $xinfostr = base::getLRStr($string, '|', 'left');
        $selstr = base::getLRStr($string, '|', 'right');
      }
      $xmlAry = self::take($xinfostr, 'sel');
      if (is_array($xmlAry))
      {
        $optionUnselected = self::take('global.config.xmlselect_un' . $template, 'tpl');
        $optionselected = self::take('global.config.xmlselect_' . $template, 'tpl');
        foreach ($xmlAry as $key => $val)
        {
          if (base::isEmpty($selstr) || base::checkInstr($selstr, $key, ','))
          {
            if ($value == '*' || base::checkInstr($value, $key, ',')) $tmpstr .= $optionselected;
            else $tmpstr .= $optionUnselected;
            $tmpstr = str_replace('{$explain}', base::htmlEncode($val), $tmpstr);
            $tmpstr = str_replace('{$value}', base::htmlEncode($key), $tmpstr);
          }
        }
        $tmpstr = str_replace('{$name}', base::htmlEncode($name), $tmpstr);
        $tmpstr = self::parse($tmpstr);
      }
      return $tmpstr;
    }

    function __construct($argTplString = null)
    {
      $tplString = $argTplString;
      if (!is_null($tplString)) $this -> tplString = $tplString;
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>
