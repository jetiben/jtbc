<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc\universal\fragment {
  use jtbc\base;
  use jtbc\tpl;
  use jtbc\request;
  use jtbc\universal;
  trait upload
  {
    private static function doActionUpload()
    {
      $status = 0;
      $message = '';
      $para = '';
      $limit = base::getString(request::get('limit'));
      $account = self::account();
      if (!($account -> checkCurrentGenrePopedom('add') || $account -> checkCurrentGenrePopedom('edit')))
      {
        $message = tpl::take('::console.text-tips-error-403', 'lng');
      }
      else
      {
        $fileArray = array();
        $fileArray['file'] = request::getFile('file');
        $fileArray['fileSize'] = request::getPost('fileSize');
        $fileArray['chunkCount'] = request::getPost('chunkCount');
        $fileArray['chunkCurrentIndex'] = request::getPost('chunkCurrentIndex');
        $fileArray['timeStringRandom'] = request::getPost('timeStringRandom');
        $upResult = universal\upload::up2self($fileArray, $limit);
        $upResultArray = json_decode($upResult, 1);
        if (is_array($upResultArray))
        {
          $status = $upResultArray['status'];
          $message = $upResultArray['message'];
          $para = $upResultArray['param'];
          if ($status == 1)
          {
            $paraArray = json_decode($para, 1);
            if (is_array($paraArray))
            {
              $account -> creatCurrentGenreLog('manage.log-upload-1', array('filepath' => $paraArray['filepath']));
            }
          }
        }
      }
      $tmpstr = self::formatMsgResult($status, $message, $para);
      return $tmpstr;
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>