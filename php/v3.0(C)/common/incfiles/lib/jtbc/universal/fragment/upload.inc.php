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
      $param = '';
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
        $fileArray['chunkParam'] = request::getPost('chunkParam');
        $fileArray['timeStringRandom'] = request::getPost('timeStringRandom');
        $upResult = universal\upload::up2self($fileArray, $limit);
        $upResultArray = json_decode($upResult, 1);
        if (is_array($upResultArray))
        {
          $status = $upResultArray['status'];
          $message = $upResultArray['message'];
          $param = $upResultArray['param'];
          if ($status == 1)
          {
            $paramArray = json_decode($param, 1);
            if (is_array($paramArray))
            {
              if (array_key_exists('filepath', $paramArray)) $account -> creatCurrentGenreLog('manage.log-upload-1', array('filepath' => $paramArray['filepath']));
            }
          }
        }
      }
      $tmpstr = self::formatMsgResult($status, $message, $param);
      return $tmpstr;
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>