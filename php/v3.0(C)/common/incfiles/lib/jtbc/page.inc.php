<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  class page
  {
    public static $errorCode = 0;
    public static $init = false;
    public static $param = array();
    private static $title = array();

    public static function formatResult($argStatus, $argResult, $argParam = '')
    {
      $status = $argStatus;
      $result = $argResult;
      $param = $argParam;
      $tmpstr = '<?xml version="1.0" encoding="' . CHARSET . '"?>';
      if (!is_array($result))
      {
        $result = str_replace(']]>', ']]]]><![CDATA[>', $result);
        $tmpstr .= '<result status="' . base::getNum($status, 0) . '" param="' . base::htmlEncode($param) . '"><![CDATA[' . $result . ']]></result>';
      }
      else
      {
        $tmpstr .= '<result status="' . base::getNum($status, 0) . '" param="' . base::htmlEncode($param) . '">';
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

    public static function formatMsgResult($argStatus, $argMessage, $argParam = '')
    {
      $status = $argStatus;
      $message = $argMessage;
      $param = $argParam;
      $tmpstr = '<?xml version="1.0" encoding="' . CHARSET . '"?><result status="' . base::getNum($status, 0) . '" message="' . base::htmlEncode($message) . '" param="' . base::htmlEncode($param) . '"></result>';
      return $tmpstr;
    }

    public static function getParam($argName)
    {
      $param = null;
      $name = $argName;
      if (self::$init == false)
      {
        self::$init = true;
        self::init();
      }
      if (array_key_exists($name, self::$param)) $param = self::$param[$name];
      return $param;
    }

    public static function getPageParam($argName)
    {
      $name = $argName;
      $param = self::getParam($name);
      if (base::isEmpty($param)) $param = tpl::take('global.public.' . $name, 'lng');
      return $param;
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

    public static function getResult()
    {
      $tmpstr = '';
      $class = get_called_class();
      if (is_callable(array($class, 'start'))) call_user_func(array($class, 'start'));
      $allow = true;
      $remortIP = self::getParam('remort_ip');
      if (defined('ALLOW_IP'))
      {
        $allow = false;
        if (is_string(ALLOW_IP))
        {
          if (ALLOW_IP == $remortIP) $allow = true;
        }
        else if (is_array(ALLOW_IP))
        {
          if (in_array($remortIP, ALLOW_IP)) $allow = true;
        }
      }
      if (defined('DENY_IP'))
      {
        if (is_string(DENY_IP))
        {
          if (DENY_IP == $remortIP) $allow = false;
        }
        else if (is_array(DENY_IP))
        {
          if (in_array($remortIP, DENY_IP)) $allow = false;
        }
      }
      if ($allow == false) self::$errorCode = 403;
      else
      {
        $type = request::get('type');
        $action = request::get('action');
        if (base::isEmpty($type)) $type = 'default';
        $intercepted = false;
        if (is_callable(array($class, 'intercept')))
        {
          $interceptResult = call_user_func(array($class, 'intercept'));
          if (!is_null($interceptResult))
          {
            $intercepted = true;
            $tmpstr = $interceptResult;
          }
        }
        if ($intercepted != true)
        {
          $module = 'module' . ucfirst($type);
          if ($type == 'action') $module = 'moduleAction' . ucfirst($action);
          if (is_callable(array($class, $module))) $tmpstr = call_user_func(array($class, $module));
          else
          {
            if ($type != 'default') self::$errorCode = 404;
            else
            {
              $tmpstr = tpl::take('.default', 'tpl');
              $tmpstr = tpl::parse($tmpstr);
              if (base::isEmpty($tmpstr))
              {
                $adjunctDefault = self::getParam('adjunct_default');
                $adjunctDefaultModule = 'module' . ucfirst($adjunctDefault);
                if (!is_callable(array($class, $adjunctDefaultModule))) self::$errorCode = 404;
                else $tmpstr = call_user_func(array($class, $adjunctDefaultModule));
              }
            }
          }
        }
        self::setHeader();
        self::setParam('processtime', (microtime(true) - STARTTIME));
      }
      //$tmpstr .= '<!--Processed in ' . base::formatSecond(self::getParam('processtime')) . '-->';
      return $tmpstr;
    }

    public static function getErrorResult($argCode = 404)
    {
      $tmpstr = '';
      $code = base::getNum($argCode, 0);
      if ($code == 403)
      {
        http_response_code(403);
        $tmpstr = tpl::take('global.config.403', 'tpl');
      }
      else if ($code == 404)
      {
        http_response_code(404);
        $tmpstr = tpl::take('global.config.404', 'tpl');
      }
      return $tmpstr;
    }

    public static function setHeader()
    {
      $noCache = self::getParam('noCache');
      $allowOrigin = self::getParam('allowOrigin');
      $allowHeaders = self::getParam('allowHeaders');
      $contentType = self::getParam('contentType');
      if (base::isEmpty($contentType)) $contentType = 'text/html';
      $definedAllowOrigin = defined('ALLOW_ORIGIN')? ALLOW_ORIGIN: null;
      $currentAllowOrigin = is_null($allowOrigin)? $definedAllowOrigin: $allowOrigin;
      $definedAllowHeaders = defined('ALLOW_HEADERS')? ALLOW_HEADERS: null;
      $currentAllowHeaders = is_null($allowHeaders)? $definedAllowHeaders: $allowHeaders;
      $definedAllowCredentials = defined('ALLOW_CREDENTIALS')? ALLOW_CREDENTIALS: null;
      if ($noCache === true)
      {
        header('Pragma: no-cache');
        header('Cache-Control: no-cache, must-revalidate');
      }
      if (!is_null($currentAllowOrigin))
      {
        if (is_array($currentAllowOrigin))
        {
          $httpOrigin = request::getHeaderParam('origin');
          if (in_array($httpOrigin, $currentAllowOrigin)) $currentAllowOrigin = $httpOrigin;
        }
        if (is_string($currentAllowOrigin))
        {
          header('Access-Control-Allow-Origin: ' . $currentAllowOrigin);
          if ($definedAllowCredentials == true) header('Access-Control-Allow-Credentials: true');
        }
      }
      if (!is_null($currentAllowHeaders))
      {
        header('Access-Control-Allow-Headers: ' . $currentAllowHeaders);
      }
      if ($contentType == 'text/html' || $contentType == 'text/xml')
      {
        header('Content-Type: ' . $contentType . '; charset=' . CHARSET);
      }
      else
      {
        header('Content-Type: ' . $contentType);
      }
    }

    public static function setParam($argName, $argValue)
    {
      $name = $argName;
      $value = $argValue;
      self::$param[$name] = $value;
      return $value;
    }

    public static function setPageParam($argName, $argValue)
    {
      $name = $argName;
      $value = $argValue;
      return self::setParam($name, $value);
    }

    public static function setPageTitle($argTitle)
    {
      $title = $argTitle;
      if (!base::isEmpty($title)) array_push(self::$title, $title);
      return self::getPageTitle();
    }

    public static function init()
    {
      self::$param['http'] = request::isHTTPS()? 'https://': 'http://';
      self::$param['http_host'] = request::server('HTTP_HOST');
      self::$param['route'] = route::getRoute();
      self::$param['genre'] = route::getCurrentGenre();
      self::$param['assetspath'] = ASSETSPATH;
      self::$param['global.assetspath'] = route::getActualRoute(ASSETSPATH);
      self::$param['folder'] = route::getCurrentFolder();
      self::$param['filename'] = route::getCurrentFilename();
      self::$param['lang'] = request::getForeLang();
      self::$param['referer'] = request::server('HTTP_REFERER');
      self::$param['remort_ip'] = request::getRemortIP();
      self::$param['uri'] = route::getScriptName();
      self::$param['urs'] = request::server('QUERY_STRING');
      self::$param['url'] = base::isEmpty(self::$param['urs'])? self::$param['uri']: self::$param['uri'] . '?' . self::$param['urs'];
      self::$param['urlpre'] = self::$param['http'] . self::$param['http_host'];
      self::$param['visible_url'] = request::server('REQUEST_URI');
      self::$param['fulluri'] = self::$param['urlpre'] . self::$param['uri'];
      self::$param['fullurl'] = self::$param['urlpre'] . self::$param['url'];
      self::$param['full_visible_url'] = self::$param['urlpre'] . self::$param['visible_url'];
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>