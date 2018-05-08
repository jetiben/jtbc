<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc\universal\fragment {
  use jtbc\base;
  use jtbc\tpl;
  use jtbc\request;
  use jtbc\universal;
  trait category
  {
    private static function doCategory()
    {
      $status = 1;
      $tmpstr = '';
      $fid = base::getNum(request::get('fid'), 0);
      $account = self::account();
      if ($account -> checkCurrentGenrePopedom())
      {
        $prefix = universal\category::getPrefix();
        $myCategory = $account -> getCurrentGenrePopedom('category');
        $categoryAry = universal\category::getCategoryAryByGenre(self::getPara('genre'), $account -> getLang());
        $tmpstr = tpl::take('manage.category', 'tpl');
        $tpl = new tpl();
        $tpl -> tplString = $tmpstr;
        $loopString = $tpl -> getLoopString('{@}');
        foreach ($categoryAry as $myKey => $myVal)
        {
          if (is_array($myVal))
          {
            $rsid = base::getNum($myVal[$prefix . 'id'], -1);
            $rsfid = base::getNum($myVal[$prefix . 'fid'], -1);
            if ($rsfid == $fid && (base::isEmpty($myCategory) || base::checkInstr($myCategory, $rsid)))
            {
              $loopLineString = tpl::replaceTagByAry($loopString, $myVal, 10);
              $tpl -> insertLoopLine(tpl::parse($loopLineString));
            }
          }
        }
        $tmpstr = $tpl -> mergeTemplate();
        $tmpstr = tpl::parse($tmpstr);
      }
      $tmpstr = self::formatResult($status, $tmpstr);
      return $tmpstr;
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>
