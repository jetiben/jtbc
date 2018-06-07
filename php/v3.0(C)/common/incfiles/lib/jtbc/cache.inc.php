<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  class cache
  {
    private $filename;
    private $cachename;

    private function getFileText()
    {
      $fileText = file_get_contents($this -> filename);
      return $fileText;
    }

    private function putFileText($argData)
    {
      $data = $argData;
      $bool = file_put_contents($this -> filename, $data);
      return $bool;
    }

    private function getFileArray()
    {
      $fileArray = array();
      $bool = @include_once($this -> filename);
      if ($bool) $fileArray = $GLOBALS['cache-' . $this -> cachename];
      return $fileArray;
    }

    private function setFileArray($argData)
    {
      $bool = false;
      $data = $argData;
      if (is_array($data))
      {
        $arrayText = 'array(';
        foreach($data as $key => $val)
        {
          if (is_array($val)) $arrayText = $arrayText . '\'' . $key . '\' => ' . $this -> setFileArray($val) . ',';
          else $arrayText = $arrayText . '\'' . $key . '\' => \'' . str_replace('\'', '\\\'', $val) . '\',';
        }
        $arrayText = $arrayText . ')';
        $bool = $arrayText;
      }
      return $bool;
    }

    private function putFileArray($argData)
    {
      $bool = false;
      $data = $argData;
      $text = '<?php' . chr(13) . chr(10);
      $text = $text . '$GLOBALS[\'cache-' . $this -> cachename . '\'] = ';
      $text = $text . $this -> setFileArray($data) . ';' . chr(13) . chr(10);
      $text = $text . '?>';
      $bool = file_put_contents($this -> filename, $text);
      return $bool;
    }

    public static function exist($argName)
    {
      $bool = false;
      $name = $argName;
      $cacheFilename = route::getActualRoute(CACHEDIR) . '/' . $name . '.inc.php';
      if (is_file($cacheFilename)) $bool = true;
      return $bool;
    }

    public static function get($argName, $argType = 1)
    {
      $name = $argName;
      $type = $argType;
      $cacheData = null;
      $cache = new cache();
      $cache -> cachename = $name;
      $cache -> filename = route::getActualRoute(CACHEDIR) . '/' . $name . '.inc.php';
      switch ($type)
      {
        case -1:
          $cacheData = $cache -> getFileText();
          break;
        case 1:
          $cacheData = $cache -> getFileArray();
          break;
        default:
          $cacheData = $cache -> getFileText();
          break;
      }
      return $cacheData;
    }

    public static function put($argName, $argData, $argType = 0)
    {
      $name = $argName;
      $type = $argType;
      $data = $argData;
      $cacheBool = false;
      $dir = route::getActualRoute(CACHEDIR);
      if (!(is_dir($dir))) @mkdir($dir, 0777);
      $cache = new cache();
      $cache -> cachename = $name;
      $cache -> filename = $dir . '/' . $name . '.inc.php';
      switch ($type)
      {
        case -1:
          $cacheBool = $cache -> putFileText($data);
          break;
        case 1:
          $cacheBool = $cache -> putFileArray($data);
          break;
        default:
          if (is_array($data)) $cacheBool = $cache -> putFileArray($data);
          else $cacheBool = $cache -> putFileText($data);
          break;
      }
      return $cacheBool;
    }

    public static function remove($argName = '')
    {
      $name = $argName;
      $cacheBool = false;
      $dir = route::getActualRoute(CACHEDIR);
      if (!base::isEmpty($name))
      {
        $cacheFilename = $dir . '/' . $name . '.inc.php';
        $cacheBool = unlink($cacheFilename);
      }
      else
      {
        $cacheBool = true;
        $cdirs = dir($dir);
        while($entry = $cdirs -> read())
        {
          $filename = $dir . '/' . $entry;
          if (is_file($filename))
          {
            if (!unlink($dir . '/' . $entry)) $cacheBool = false;
          }
        }
        $cdirs -> close();
      }
      return $cacheBool;
    }

    public static function removeByKey($argKey, $argMode = 0)
    {
      $key = $argKey;
      $mode = base::getNum($argMode, 0);
      $cacheBool = false;
      $dir = route::getActualRoute(CACHEDIR);
      if (!base::isEmpty($key))
      {
        $cacheBool = true;
        $cdirs = dir($dir);
        while($entry = $cdirs -> read())
        {
          $strpos = base::getNum(strpos($entry, $key), -1);
          if (($mode == 0 && $strpos == 0) || ($mode == 1 && $strpos >= 0))
          {
            $filename = $dir . '/' . $entry;
            if (is_file($filename))
            {
              if (!unlink($dir . '/' . $entry)) $cacheBool = false;
            }
          }
        }
        $cdirs -> close();
      }
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>