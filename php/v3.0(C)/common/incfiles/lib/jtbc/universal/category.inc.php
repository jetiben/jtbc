<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc\universal {
  use jtbc\auto;
  use jtbc\base;
  use jtbc\cache;
  use jtbc\dal;
  use jtbc\route;
  use jtbc\sql;
  use jtbc\tpl;
  class category
  {
    public static function getAllGenre()
    {
      return route::getFolderArrayByGuide('category');
    }

    public static function getAllGenreSelect($argAllGenre = null, $argGenre = '')
    {
      $tmpstr = '';
      $allGenre = $argAllGenre;
      $genre = $argGenre;
      if (!is_array($allGenre)) $allGenre = self::getAllGenre();
      $optionUnselected = tpl::take('global.config.xmlselect_unselect', 'tpl');
      $optionselected = tpl::take('global.config.xmlselect_select', 'tpl');
      foreach ($allGenre as $key => $val)
      {
        if (self::isValidGenre($val))
        {
          if ($val == $genre) $tmpstr .= $optionselected;
          else $tmpstr .= $optionUnselected;
          $title = tpl::take('global.' . $val . ':category.title', 'cfg');
          $tmpstr = str_replace('{$explain}', base::htmlEncode($title . ' [' . $val . ']'), $tmpstr);
          $tmpstr = str_replace('{$value}', base::htmlEncode($val), $tmpstr);
        }
      }
      return $tmpstr;
    }

    public static function getCategoryAryByGenre($argGenre, $argLang)
    {
      $genre = $argGenre;
      $lang = base::getNum($argLang, 0);
      if (!base::isEmpty($genre))
      {
        $cacheName = 'universal-category-array-' . str_replace('/', '-', $genre) . '-' . $lang;
        $categoryAry = cache::get($cacheName);
        if (empty($categoryAry))
        {
          $categoryAry = self::getDBCategoryAryByGenre($genre, $lang);
          cache::put($cacheName, $categoryAry);
        }
      }
      return $categoryAry;
    }

    public static function getCategoryChildID($argGenre, $argLang, $argFID)
    {
      $tmpstr = '';
      $genre = $argGenre;
      $lang = base::getNum($argLang, 0);
      $fid = base::getNum($argFID, 0);
      if (!base::isEmpty($genre))
      {
        $categoryAry = self::getCategoryAryByGenre($genre, $lang);
        if (is_array($categoryAry))
        {
          $prefix = self::getPrefix();
          foreach ($categoryAry as $key => $val)
          {
            if (is_array($val))
            {
              $rsId = base::getNum($val[$prefix . 'id'], 0);
              $rsFid = base::getNum($val[$prefix . 'fid'], -1);
              if ($rsFid == $fid)
              {
                $tmpstr .= $rsId . ',';
                $tmpstr .= self::getCategoryChildID($genre, $lang, $rsId);
              }
            }
          }
        }
      }
      if (!base::isEmpty($tmpstr)) $tmpstr = base::getLRStr($tmpstr, ',', 'leftr');
      return $tmpstr;
    }

    public static function getCategoryFamilyID($argGenre, $argLang, $argID)
    {
      $genre = $argGenre;
      $lang = base::getNum($argLang, 0);
      $id = base::getNum($argID, 0);
      $tmpstr = base::mergeIdAry($id, self::getCategoryChildID($genre, $lang, $id));
      return $tmpstr;
    }

    public static function getCategorySelectByGenre($argGenre, $argLang, $argMyCategory, $argVars = '')
    {
      $tmpstr = '';
      $genre = $argGenre;
      $lang = base::getNum($argLang, 0);
      $myCategory = $argMyCategory;
      $vars = $argVars;
      $id = base::getNum(base::getParameter($vars, 'id'), 0);
      $fid = base::getNum(base::getParameter($vars, 'fid'), 0);
      $rank = base::getNum(base::getParameter($vars, 'rank'), -1);
      $categoryAry = self::getCategoryAryByGenre($genre, $lang);
      if (is_array($categoryAry))
      {
        $rank += 1;
        $prefix = self::getPrefix();
        foreach ($categoryAry as $key => $val)
        {
          if (is_array($val))
          {
            $rsId = base::getNum($val[$prefix . 'id'], 0);
            $rsFid = base::getNum($val[$prefix . 'fid'], -1);
            if ($rsFid == $fid && (base::isEmpty($myCategory) || base::checkInstr($myCategory, $rsId)))
            {
              $explain = base::getRepeatedString(tpl::take('global.config.spstr', 'lng'), $rank) . base::htmlEncode($val[$prefix . 'topic']);
              if ($rsId == $id) $tmpstr .= tpl::take('global.config.xmlselect_select', 'tpl', 0, array('explain' => $explain, 'value' => $rsId));
              else $tmpstr .= tpl::take('global.config.xmlselect_unselect', 'tpl', 0, array('explain' => $explain, 'value' => $rsId));
              $tmpstr .= self::getCategorySelectByGenre($genre, $lang, $myCategory, 'id=' . $id . ';fid=' . $rsId . ';rank=' . $rank);
            }
          }
        }
      }
      return $tmpstr;
    }

    public static function getCategoryBreadcrumbByID($argGenre, $argLang, $argID)
    {
      $tmpstr = '';
      $genre = $argGenre;
      $lang = base::getNum($argLang, 0);
      $id = base::getNum($argID, 0);
      $categoryAry = self::getCategoryAryByGenre($genre, $lang);
      if (is_array($categoryAry))
      {
        $prefix = self::getPrefix();
        $baseHTML = tpl::take('global.config.breadcrumb', 'tpl');
        $baseArrowHTML = tpl::take('global.config.breadcrumb-arrow', 'tpl');
        foreach ($categoryAry as $key => $val)
        {
          if (is_array($val))
          {
            $rsId = base::getNum($val[$prefix . 'id'], 0);
            $rsFid = base::getNum($val[$prefix . 'fid'], 0);
            $rsTopic = base::getString($val[$prefix . 'topic']);
            if ($rsId == $id)
            {
              $tmpstr = $baseArrowHTML . $baseHTML;
              $tmpstr = str_replace('{$text}', base::htmlEncode($rsTopic), $tmpstr);
              $tmpstr = str_replace('{$link}', base::htmlEncode(route::createURL('list', $rsId, null, $genre)), $tmpstr);
              if ($rsFid != 0) $tmpstr = self::getCategoryBreadcrumbByID($genre, $lang, $rsFid) . $tmpstr;
            }
          }
        }
      }
      return $tmpstr;
    }

    public static function getCategoryNavByID($argGenre, $argLang, $argID)
    {
      $tmpstr = '';
      $genre = $argGenre;
      $lang = base::getNum($argLang, 0);
      $id = base::getNum($argID, 0);
      $categoryAry = self::getCategoryAryByGenre($genre, $lang);
      if (is_array($categoryAry))
      {
        $prefix = self::getPrefix();
        foreach ($categoryAry as $key => $val)
        {
          if (is_array($val))
          {
            $rsId = base::getNum($val[$prefix . 'id'], 0);
            $rsFid = base::getNum($val[$prefix . 'fid'], 0);
            $rsTopic = base::getString($val[$prefix . 'topic']);
            if ($rsId == $id)
            {
              $tmpstr = tpl::take('::console.link-nav', 'tpl', 0, array('text' => base::htmlEncode($rsTopic), 'link' => base::htmlEncode('?type=list&category=' . $rsId)));
              if ($rsFid != 0) $tmpstr = self::getCategoryNavByID($genre, $lang, $rsFid) . $tmpstr;
            }
          }
        }
      }
      return $tmpstr;
    }

    public static function getCategoryTopicByID($argGenre, $argLang, $argID)
    {
      $genre = $argGenre;
      $lang = base::getNum($argLang, 0);
      $id = base::getNum($argID, 0);
      $tmpstr = self::getInfoByID($genre, $lang, $id, 'topic');
      return $tmpstr;
    }

    public static function getDBCategoryAryByGenre($argGenre, $argLang, $argFid = 0)
    {
      $genre = $argGenre;
      $lang = base::getNum($argLang, 0);
      $fid = base::getNum($argFid, 0);
      $categoryAry = array();
      $table = tpl::take('global.universal/category:config.db_table', 'cfg');
      $prefix = tpl::take('global.universal/category:config.db_prefix', 'cfg');
      if (!base::isEmpty($table) && !base::isEmpty($prefix))
      {
        $dal = new dal($table, $prefix);
        $dal -> lang = $lang;
        $dal -> fid = $fid;
        $dal -> genre = $genre;
        $dal -> orderBy('order', 'asc');
        $dal -> orderBy('id', 'asc');
        $rsa = $dal -> selectAll();
        foreach ($rsa as $i => $rs)
        {
          $rsId = base::getNum($dal -> val($rs, 'id'), 0);
          $categoryAry['id' . $rsId] = $rs;
          $categoryAry = array_merge($categoryAry, self::getDBCategoryAryByGenre($genre, $lang, $rsId));
        }
      }
      return $categoryAry;
    }

    public static function getFirstValidGenre($argAllGenre)
    {
      $genre = '';
      $allGenre = $argAllGenre;
      if (is_array($allGenre))
      {
        foreach ($allGenre as $key => $val)
        {
          if (self::isValidGenre($val))
          {
            $genre = $val;
            break;
          }
        }
      }
      return $genre;
    }

    public static function getInfoByID($argGenre, $argLang, $argID, $argName)
    {
      $tmpstr = '';
      $genre = $argGenre;
      $lang = base::getNum($argLang, 0);
      $id = base::getNum($argID, 0);
      $name = $argName;
      $categoryAry = self::getCategoryAryByGenre($genre, $lang);
      if (is_array($categoryAry))
      {
        $prefix = self::getPrefix();
        foreach ($categoryAry as $key => $val)
        {
          if (is_array($val))
          {
            $rsId = base::getNum($val[$prefix . 'id'], 0);
            if ($rsId == $id) $tmpstr = $val[$prefix . $name];
          }
        }
      }
      return $tmpstr;
    }

    public static function getPrefix()
    {
      $tmpstr = tpl::take('global.universal/category:config.db_prefix', 'cfg');
      return $tmpstr;
    }

    public static function isValidGenre($argGenre)
    {
      $bool = false;
      $genre = $argGenre;
      $title = @tpl::take('global.' . $genre . ':category.title', 'cfg');
      if (!base::isEmpty($title)) $bool = true;
      return $bool;
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>
