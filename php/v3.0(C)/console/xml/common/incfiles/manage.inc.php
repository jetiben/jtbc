<?php
namespace jtbc;
use DOMXPath;
use DOMDocument;
class ui extends console\page {
  protected static function ppGetPathAryBySymbol($argSymbol)
  {
    $pathAry = array();
    $symbol = $argSymbol;
    if (!base::isEmpty($symbol))
    {
      $symbolAry = explode('.', $symbol);
      if (count($symbolAry) == 3)
      {
        $filepath = route::getActualRoute($symbolAry[0]);
        if (base::getRight($filepath, 1) != '/') $filepath .= '/';
        $filepath .= tpl::getXMLDirByType($symbolAry[1]) . '/';
        $filepath .= $symbolAry[2] . XMLSFX;
        $pathAry['filepath'] = $filepath;
        $pathAry['activevalue'] = tpl::getActiveValue($symbolAry[1]);
      }
    }
    return $pathAry;
  }

  public static function moduleAdd()
  {
    $status = 1;
    $tmpstr = '';
    $account = self::account();
    $symbol = base::getString(request::get('symbol'));
    if ($account -> checkCurrentGenrePopedom('add'))
    {
      $tmpstr = tpl::take('manage.add', 'tpl');
      $tmpstr = str_replace('{$-symbol}', base::htmlEncode($symbol), $tmpstr);
      $tmpstr = tpl::parse($tmpstr);
      $tmpstr = $account -> replaceAccountTag($tmpstr);
    }
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleList()
  {
    $status = 1;
    $tmpstr = '';
    $currentNode = '';
    $currentValue = '';
    $node = base::getString(request::get('node'));
    $symbol = base::getString(request::get('symbol'));
    if (base::isEmpty($symbol)) $symbol = '.tpl.index';
    $account = self::account();
    $tmpstr = tpl::take('manage.list-disabled', 'tpl');
    $pathAry = self::ppGetPathAryBySymbol($symbol);
    if (!empty($pathAry))
    {
      $nodeIndex = 0;
      $filepath = $pathAry['filepath'];
      $tplActiveValue = $pathAry['activevalue'];
      $xmlAry = tpl::getXMLInfo($filepath, $tplActiveValue);
      if (!empty($xmlAry))
      {
        $tmpstr = tpl::take('manage.list', 'tpl');
        $tpl = new tpl($tmpstr);
        $loopString = $tpl -> getLoopString('{@}');
        foreach ($xmlAry as $key => $val)
        {
          if ($nodeIndex == 0)
          {
            $currentNode = $key;
            $currentValue = $val;
          }
          if ($key == $node)
          {
            $currentNode = $key;
            $currentValue = $val;
          }
          $loopLineString = $loopString;
          $loopLineString = str_replace('{$key}', base::htmlEncode($key), $loopLineString);
          $tpl -> insertLoopLine($loopLineString);
          $nodeIndex += 1;
        }
        $tmpstr = $tpl -> getTpl();
        $tmpstr = str_replace('{$-current-key}', base::htmlEncode($currentNode), $tmpstr);
        $tmpstr = str_replace('{$-current-val}', base::htmlEncode($currentValue), $tmpstr);
      }
      $tmpstr = str_replace('{$-symbol}', base::htmlEncode($symbol), $tmpstr);
      $tmpstr = str_replace('{$-filepath}', base::htmlEncode($filepath), $tmpstr);
      $tmpstr = str_replace('{$-realpath}', base::htmlEncode(route::formatPath($filepath)), $tmpstr);
    }
    $tmpstr = tpl::parse($tmpstr);
    $tmpstr = $account -> replaceAccountTag($tmpstr);
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleFileSelect()
  {
    $status = 1;
    $tmpstr = '';
    $account = self::account();
    $symbol = base::getString(request::get('symbol'));
    if ($account -> checkCurrentGenrePopedom())
    {
      if (!base::isEmpty($symbol))
      {
        $symbolAry = explode('.', $symbol);
        if (count($symbolAry) == 3)
        {
          $tmpstr = tpl::take('manage.fileselect', 'tpl');
          $tmpstr = str_replace('{$-symbol}', base::htmlEncode($symbol), $tmpstr);
          $tmpstr = str_replace('{$-symbol-p1}', base::htmlEncode($symbolAry[0]), $tmpstr);
          $tmpstr = str_replace('{$-symbol-p2}', base::htmlEncode($symbolAry[1]), $tmpstr);
          $tmpstr = str_replace('{$-symbol-p3}', base::htmlEncode($symbolAry[2]), $tmpstr);
          $tmpstr = tpl::parse($tmpstr);
          $tmpstr = $account -> replaceAccountTag($tmpstr);
        }
      }
    }
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleFileSelectGenre()
  {
    $status = 1;
    $tmpstr = '';
    $account = self::account();
    if ($account -> checkCurrentGenrePopedom())
    {
      $ary = array();
      $folder = route::getFolderByGuide();
      $folderAry = explode('|+|', $folder);
      foreach($folderAry as $key => $val)
      {
        if (!base::isEmpty($val))
        {
          if (!base::isEmpty($val))
          {
            $guide = json_decode(tpl::take('global.' . $val . ':guide.guide', 'cfg'), true);
            $ary[$val] = $guide['text'];
          }
        }
      }
      $tmpstr = json_encode($ary);
    }
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleFileSelectFile()
  {
    $status = 1;
    $tmpstr = '';
    $account = self::account();
    $genre = base::getString(request::get('genre'));
    $mold = base::getString(request::get('mold'));
    if ($account -> checkCurrentGenrePopedom())
    {
      $ary = array();
      if (base::isEmpty($genre)) $genre = './';
      else if (base::getRight($genre, 1) != '/') $genre .= '/';
      $path = route::getActualRoute($genre);
      $path .= tpl::getXMLDirByType($mold) . '/';
      if (is_dir($path))
      {
        $dir = @dir($path);
        while($entry = $dir -> read())
        {
          if ($entry != '.' && $entry != '..')
          {
            if (is_file($path . $entry))
            {
              $ary[base::getLRStr($entry, '.', 'leftr')] = $entry;
            }
          }
        }
      }
      $tmpstr = json_encode($ary);
    }
    $tmpstr = self::formatResult($status, $tmpstr);
    return $tmpstr;
  }

  public static function moduleActionAdd()
  {
    $status = 0;
    $message = '';
    $error = array();
    $account = self::account();
    $nodename = base::getString(request::getPost('nodename'));
    $symbol = base::getString(request::getPost('symbol'));
    if (!$account -> checkCurrentGenrePopedom('add'))
    {
      array_push($error, tpl::take('::console.text-tips-error-403', 'lng'));
    }
    else
    {
      if (base::isEmpty($nodename)) array_push($error, tpl::take('manage.text-tips-add-error-1', 'lng'));
      else if (!verify::isNatural($nodename)) array_push($error, tpl::take('manage.text-tips-add-error-1s', 'lng'));
      if (count($error) == 0)
      {
        $pathAry = self::ppGetPathAryBySymbol($symbol);
        if (!empty($pathAry))
        {
          $nodeIndex = 0;
          $filepath = $pathAry['filepath'];
          if (is_file($filepath))
          {
            $doc = new DOMDocument();
            $doc -> formatOutput = true;
            $doc -> preserveWhiteSpace = false;
            $doc -> load($filepath);
            $xpath = new DOMXPath($doc);
            $query = '//xml/configure/node';
            $node = $xpath -> query($query) -> item(0) -> nodeValue;
            $query = '//xml/configure/field';
            $field = $xpath -> query($query) -> item(0) -> nodeValue;
            $query = '//xml/configure/base';
            $base = $xpath -> query($query) -> item(0) -> nodeValue;
            $fieldArys = explode(',', $field);
            $query = '//xml/' . $base . '/' . $node . '/' . current($fieldArys) . '[text()=\'' . $nodename . '\']';
            $rests = $xpath -> query($query);
            $matchLength = base::getNum($rests -> length, 0);
            if ($matchLength >= 1) array_push($error, tpl::take('manage.text-tips-add-error-3', 'lng'));
            else
            {
              $baseQuery = '//xml/' . $base;
              $baseDom = $xpath -> query($baseQuery) -> item(0);
              $newNode = $doc -> createElement($node);
              $newNodeName = $doc -> createElement(current($fieldArys));
              $newNodeName -> appendChild($doc -> createCDATASection($nodename));
              $newNode -> appendChild($newNodeName);
              for ($ti = 1; $ti < count($fieldArys); $ti ++)
              {
                $newNodeField = $doc -> createElement($fieldArys[$ti]);
                $newNodeField -> appendChild($doc -> createCDATASection(''));
                $newNode -> appendChild($newNodeField);
              }
              $baseDom -> appendChild($newNode);
              $bool = $doc -> save($filepath);
              if ($bool == false) array_push($error, tpl::take('manage.text-tips-add-error-4', 'lng'));
              else
              {
                $status = 1;
                $message = tpl::take('manage.text-tips-add-done', 'lng');
                $account -> creatCurrentGenreLog('manage.log-add-1', array('symbol' => $symbol, 'node' => $nodename));
              }
            }
          }
          else array_push($error, tpl::take('manage.text-tips-add-error-2', 'lng'));
        }
        else array_push($error, tpl::take('manage.text-tips-add-error-2', 'lng'));
      }
    }
    if (!empty($error)) $message = implode('|', $error);
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function moduleActionEdit()
  {
    $status = 0;
    $message = '';
    $error = array();
    $account = self::account();
    $node = base::getString(request::getPost('node'));
    $symbol = base::getString(request::getPost('symbol'));
    $content = base::getString(request::getPost('content'));
    if (!$account -> checkCurrentGenrePopedom('edit'))
    {
      array_push($error, tpl::take('::console.text-tips-error-403', 'lng'));
    }
    else
    {
      $pathAry = self::ppGetPathAryBySymbol($symbol);
      if (!empty($pathAry))
      {
        $filepath = $pathAry['filepath'];
        $tplActiveValue = $pathAry['activevalue'];
        $bool = tpl::setXMLInfo($filepath, $tplActiveValue, $node, $content);
        if ($bool == false) array_push($error, tpl::take('manage.text-tips-edit-error-1', 'lng'));
        else
        {
          $status = 1;
          $message = tpl::take('manage.text-tips-edit-done', 'lng');
          $account -> creatCurrentGenreLog('manage.log-edit-1', array('symbol' => $symbol, 'node' => $node));
        }
      }
      else array_push($error, tpl::take('manage.text-tips-edit-error-1', 'lng'));
    }
    if (!empty($error)) $message = implode('|', $error);
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }

  public static function moduleActionDelete()
  {
    $status = 0;
    $message = '';
    $error = array();
    $account = self::account();
    $nodename = base::getString(request::getPost('nodename'));
    $symbol = base::getString(request::getPost('symbol'));
    if (!$account -> checkCurrentGenrePopedom('delete'))
    {
      array_push($error, tpl::take('::console.text-tips-error-403', 'lng'));
    }
    else
    {
      if (base::isEmpty($nodename)) array_push($error, tpl::take('manage.text-tips-delete-error-1', 'lng'));
      else if (!verify::isNatural($nodename)) array_push($error, tpl::take('manage.text-tips-delete-error-1s', 'lng'));
      if (count($error) == 0)
      {
        $pathAry = self::ppGetPathAryBySymbol($symbol);
        if (!empty($pathAry))
        {
          $nodeIndex = 0;
          $filepath = $pathAry['filepath'];
          if (is_file($filepath))
          {
            $doc = new DOMDocument();
            $doc -> load($filepath);
            $xpath = new DOMXPath($doc);
            $query = '//xml/configure/node';
            $node = $xpath -> query($query) -> item(0) -> nodeValue;
            $query = '//xml/configure/field';
            $field = $xpath -> query($query) -> item(0) -> nodeValue;
            $query = '//xml/configure/base';
            $base = $xpath -> query($query) -> item(0) -> nodeValue;
            $fieldArys = explode(',', $field);
            $query = '//xml/' . $base . '/' . $node . '/' . current($fieldArys) . '[text()=\'' . $nodename . '\']';
            $rests = $xpath -> query($query);
            $matchLength = base::getNum($rests -> length, 0);
            if ($matchLength >= 1)
            {
              foreach ($rests as $rest)
              {
                $rest -> parentNode -> parentNode -> removeChild($rest -> parentNode);
              }
              $bool = $doc -> save($filepath);
              if ($bool == false) array_push($error, tpl::take('manage.text-tips-delete-error-4', 'lng'));
              else
              {
                $status = 1;
                $message = tpl::take('manage.text-tips-delete-done', 'lng');
                $account -> creatCurrentGenreLog('manage.log-delete-1', array('symbol' => $symbol, 'node' => $nodename));
              }
            }
            else array_push($error, tpl::take('manage.text-tips-delete-error-3', 'lng'));
          }
          else array_push($error, tpl::take('manage.text-tips-delete-error-2', 'lng'));
        }
        else array_push($error, tpl::take('manage.text-tips-delete-error-2', 'lng'));
      }
    }
    if (!empty($error)) $message = implode('|', $error);
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }
}
?>
