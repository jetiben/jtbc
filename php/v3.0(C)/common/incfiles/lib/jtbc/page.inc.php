<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  class page
  {
    public static $counter = 0;
    public static $db = null;
    public static $init = false;
    public static $para = array();
    private static $title = array();

    public static function db()
    {
      $db = null;
      if (!is_null(self::$db)) $db = self::$db;
      else
      {
        $db = new db();
        $db -> dbHost = DB_HOST;
        $db -> dbUsername = DB_USERNAME;
        $db -> dbPassword = DB_PASSWORD;
        $db -> dbDatabase = DB_DATABASE;
        $db -> dbStructureCache = DB_STRUCTURE_CACHE;
        $db -> init();
        if ($db -> errStatus != 0) $db = null;
        else self::$db = $db;
      }
      return $db;
    }

    public static function breadcrumb($argAry = null)
    {
      $ary = $argAry;
      $genre = self::getPara('genre');
      $lang = self::getPara('lang');
      $baseHTML = tpl::take('global.config.breadcrumb', 'tpl');
      $baseArrowHTML = tpl::take('global.config.breadcrumb-arrow', 'tpl');
      $breadcrumb = $baseHTML;
      $breadcrumb = str_replace('{$text}', base::htmlEncode(tpl::take('global.public.homepage', 'lng')), $breadcrumb);
      $breadcrumb = str_replace('{$link}', base::htmlEncode(smart::getActualRoute('./')), $breadcrumb);
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
            $breadcrumb = str_replace('{$link}', base::htmlEncode(smart::getActualRoute($currentGenre)), $breadcrumb);
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

    public static function formatResult($argStatus, $argResult)
    {
      $status = $argStatus;
      $result = $argResult;
      $tmpstr = '<?xml version="1.0" encoding="utf-8"?>';
      if (!is_array($result))
      {
        $result = str_replace(']]>', ']]]]><![CDATA[>', $result);
        $tmpstr .= '<result status="' . base::getNum($status, 0) . '"><![CDATA[' . $result . ']]></result>';
      }
      else
      {
        $tmpstr .= '<result status="' . base::getNum($status, 0) . '">';
        if (count($result) == count($result, 1))
        {
          $tmpstr .= '<item';
          foreach ($result as $key => $val)
          {
            if (!is_numeric($key))
            {
              $tmpstr .= ' ' . base::htmlEncode(base::getLRStr($key, '_', 'rightr')) . '="' . base::htmlEncode($val) . '"';
            }
          }
          $tmpstr .= '></item>';
        }
        else
        {
          foreach ($result as $i => $item)
          {
            if (is_array($item))
            {
              $tmpstr .= '<item';
              foreach ($item as $key => $val)
              {
                if (!is_numeric($key))
                {
                  $tmpstr .= ' ' . base::htmlEncode(base::getLRStr($key, '_', 'rightr')) . '="' . base::htmlEncode($val) . '"';
                }
              }
              $tmpstr .= '></item>';
            }
          }
        }
        $tmpstr .= '</result>';
      }
      return $tmpstr;
    }

    public static function formatMsgResult($argStatus, $argMessage, $argPara = '')
    {
      $status = $argStatus;
      $message = $argMessage;
      $para = $argPara;
      $tmpstr = '<?xml version="1.0" encoding="utf-8"?><result status="' . base::getNum($status, 0) . '" message="' . base::htmlEncode($message) . '" para="' . base::htmlEncode($para) . '"></result>';
      return $tmpstr;
    }

    public static function getPara($argName)
    {
      $para = null;
      $name = $argName;
      if (self::$init == false)
      {
        self::$init = true;
        self::init();
      }
      if (array_key_exists($name, self::$para)) $para = self::$para[$name];
      return $para;
    }

    public static function getResult()
    {
      $tmpstr = '';
      $type = request::get('type');
      $action = request::get('action');
      if (base::isEmpty($type)) $type = 'default';
      $class = get_called_class();
      $module = 'module' . ucfirst($type);
      if ($type == 'action') $module = 'moduleAction' . ucfirst($action);
      if (method_exists($class, 'start')) call_user_func(array($class, 'start'));
      if (method_exists($class, $module)) $tmpstr = call_user_func(array($class, $module));
      else
      {
        if ($type == 'default')
        {
          $tmpstr = tpl::take('.default', 'tpl');
          $tmpstr = tpl::parse($tmpstr);
        }
      }
      return $tmpstr;
    }

    public static function getPagePara($argName)
    {
      $name = $argName;
      $para = self::getPara($name);
      if (base::isEmpty($para)) $para = tpl::take('global.public.' . $name, 'lng');
      return $para;
    }

    public static function getPageTitle()
    {
      $tmpstr = '';
      $title = self::$title;
      if (!empty($title))
      {
        foreach ($title as $key => $val)
        {
          $tmpstr = $val . SEPARATOR . $tmpstr;
        }
      }
      $tmpstr = $tmpstr . tpl::take('global.index.title', 'lng');
      return $tmpstr;
    }

    public static function setPara($argName, $argValue)
    {
      $name = $argName;
      $value = $argValue;
      self::$para[$name] = $value;
      return $value;
    }

    public static function setPagePara($argName, $argValue)
    {
      $name = $argName;
      $value = $argValue;
      return self::setPara($name, $value);
    }

    public static function setPageTitle($argTitle)
    {
      $title = $argTitle;
      if (!base::isEmpty($title)) array_push(self::$title, $title);
      return self::getPageTitle();
    }

    public static function init()
    {
      self::$para['http'] = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
      self::$para['http_host'] = $_SERVER['HTTP_HOST'];
      self::$para['route'] = smart::getRoute();
      self::$para['genre'] = smart::getActualGenre(self::$para['route']);
      self::$para['assetspath'] = ASSETSPATH;
      self::$para['global.assetspath'] = smart::getActualRoute(ASSETSPATH);
      self::$para['folder'] = base::getLRStr($_SERVER['SCRIPT_NAME'], '/', 'leftr') . '/';
      self::$para['filename'] = base::getLRStr($_SERVER['SCRIPT_NAME'], '/', 'right');
      self::$para['lang'] = smart::getForeLang();
      self::$para['referer'] = @$_SERVER['HTTP_REFERER'];
      self::$para['uri'] = $_SERVER['SCRIPT_NAME'];
      self::$para['urs'] = $_SERVER['QUERY_STRING'];
      self::$para['url'] = self::$para['uri'];
      self::$para['urlpre'] = self::$para['http'] . self::$para['http_host'];
      if (!base::isEmpty(self::$para['urs'])) self::$para['url'] .= '?' . self::$para['urs'];
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>
