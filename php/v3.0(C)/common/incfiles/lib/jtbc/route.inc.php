<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  class route
  {
    private static $currentFilename = null;
    private static $currentFolder = null;
    private static $currentGenre = null;
    private static $currentRoute = null;

    public static function breadcrumb($argAry = null)
    {
      $ary = $argAry;
      $genre = self::getCurrentGenre();
      $lang = request::getForeLang();
      $baseHTML = tpl::take('global.config.breadcrumb', 'tpl');
      $baseArrowHTML = tpl::take('global.config.breadcrumb-arrow', 'tpl');
      $breadcrumb = $baseHTML;
      $breadcrumb = str_replace('{$text}', base::htmlEncode(tpl::take('global.public.homepage', 'lng')), $breadcrumb);
      $breadcrumb = str_replace('{$link}', base::htmlEncode(self::getActualRoute('./')), $breadcrumb);
      if (!base::isEmpty($genre))
      {
        $baseGenre = '';
        $genreAry = explode('/', $genre);
        foreach ($genreAry as $key => $val)
        {
          if (!base::isEmpty($val))
          {
            $myClass = '';
            $currentGenre = $baseGenre . $val;
            $breadcrumb .= $baseArrowHTML . $baseHTML;
            $breadcrumb = str_replace('{$text}', base::htmlEncode(tpl::take('global.' . $currentGenre . ':index.title', 'lng')), $breadcrumb);
            $breadcrumb = str_replace('{$link}', base::htmlEncode(self::getActualRoute($currentGenre)), $breadcrumb);
            $baseGenre = $currentGenre . '/';
          }
        }
      }
      if (is_array($ary))
      {
        $ns = __NAMESPACE__;
        if (array_key_exists('category', $ary))
        {
          $category = base::getNum($ary['category'], 0);
          if (method_exists($ns . '\\universal\\category', 'getCategoryBreadcrumbByID'))
          {
            $breadcrumb .= universal\category::getCategoryBreadcrumbByID($genre, $lang, $category);
          }
        }
      }
      return $breadcrumb;
    }

    public static function createURL($argType, $argKey, $argVars = null, $argGenre = null)
    {
      $tmpstr = '';
      $type = $argType;
      $key = $argKey;
      $vars = $argVars;
      $genre = $argGenre;
      if (is_null($genre)) $genre = self::getCurrentGenre();
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

    public static function getActualRoute($argRoutestr = '', $argType = 0)
    {
      $route = '';
      $type = $argType;
      $routeStr = $argRoutestr;
      if ($type == 8 && !base::isEmpty(BASEDIR)) $route = BASEDIR . $routeStr;
      else
      {
        switch (self::getCurrentRoute())
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
      $genre = '';
      $route = $argRoute;
      $routeStr = $_SERVER['SCRIPT_NAME'];
      $routeStr = base::getLRStr($routeStr, '/', 'leftr');
      $ary = explode('/', $routeStr);
      $arycount = count($ary);
      switch ($route)
      {
        case 'greatgrandson':
          if ($arycount >= 4) $genre = $ary[$arycount - 4] . '/' . $ary[$arycount - 3] . '/' . $ary[$arycount - 2] . '/' . $ary[$arycount - 1];
          break;
        case 'grandson':
          if ($arycount >= 3) $genre = $ary[$arycount - 3] . '/' . $ary[$arycount - 2] . '/' . $ary[$arycount - 1];
          break;
        case 'child':
          if ($arycount >= 2) $genre = $ary[$arycount - 2] . '/' . $ary[$arycount - 1];
          break;
        case 'node':
          if ($arycount >= 1) $genre = $ary[$arycount - 1];
          break;
        default:
          $genre = '';
          break;
      }
      return $genre;
    }

    public static function getCurrentFilename()
    {
      $currentFilename = self::$currentFilename;
      if (is_null($currentFilename))
      {
        $currentFilename = self::$currentFilename = base::getLRStr($_SERVER['SCRIPT_NAME'], '/', 'right');
      }
      return $currentFilename;
    }

    public static function getCurrentFolder()
    {
      $currentFolder = self::$currentFolder;
      if (is_null($currentFolder))
      {
        $currentFolder = self::$currentFolder = base::getLRStr($_SERVER['SCRIPT_NAME'], '/', 'leftr') . '/';
      }
      return $currentFolder;
    }

    public static function getCurrentGenre()
    {
      $currentGenre = self::$currentGenre;
      if (is_null($currentGenre))
      {
        $currentGenre = self::$currentGenre = self::getActualGenre(self::getCurrentRoute());
      }
      return $currentGenre;
    }

    public static function getCurrentRoute()
    {
      $currentRoute = self::$currentRoute;
      if (is_null($currentRoute))
      {
        $currentRoute = self::$currentRoute = self::getRoute();
      }
      return $currentRoute;
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

    public static function getFolderArrayByGuide($argFilePrefix = 'guide')
    {
      $result = array();
      $fileprefix = $argFilePrefix;
      $folder = self::getFolderByGuide($fileprefix);
      $folderAry = explode('|+|', $folder);
      foreach($folderAry as $key => $val)
      {
        if (!base::isEmpty($val))
        {
          array_push($result, $val);
        }
      }
      return $result;
    }

    public static function getGenreByAppellation($argAppellation, $argOriGenre = '')
    {
      $genre = null;
      $appellation = $argAppellation;
      $oriGenre = $argOriGenre;
      if (base::isEmpty($oriGenre)) $oriGenre = self::getCurrentGenre();
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
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>
