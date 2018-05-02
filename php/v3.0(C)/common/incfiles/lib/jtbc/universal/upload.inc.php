<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc\universal {
  use jtbc\auto;
  use jtbc\base;
  use jtbc\image;
  use jtbc\page;
  use jtbc\route;
  use jtbc\sql;
  use jtbc\tpl;
  class upload
  {
    public static function getUploadId($argFileInfo)
    {
      $fileInfo = $argFileInfo;
      $uploadid = 0;
      if (is_array($fileInfo))
      {
        $db = page::db();
        if (!is_null($db))
        {
          $table = tpl::take('global.universal/upload:config.db_table', 'cfg');
          $prefix = tpl::take('global.universal/upload:config.db_prefix', 'cfg');
          if (!base::isEmpty($table) && !base::isEmpty($prefix))
          {
            $preset = array();
            $preset[$prefix . 'topic'] = $fileInfo['filename'];
            $preset[$prefix . 'filepath'] = $fileInfo['filepath'];
            $preset[$prefix . 'fileurl'] = $fileInfo['fileurl'];
            $preset[$prefix . 'filetype'] = $fileInfo['filetype'];
            $preset[$prefix . 'filesize'] = $fileInfo['filesize'];
            $preset[$prefix . 'filesizetext'] = $fileInfo['filesizetext'];
            $preset[$prefix . 'genre'] = page::getPara('genre');
            $preset[$prefix . 'time'] = base::getDateTime();
            $sqlstr = auto::getAutoInsertSQLByVars($table, $preset);
            $re = $db -> exec($sqlstr);
            if (is_numeric($re)) $uploadid = $db -> lastInsertId;
          }
        }
      }
      return $uploadid;
    }

    public static function statusReset($argGenre, $argAssociatedId)
    {
      $bool = false;
      $genre = $argGenre;
      $associatedId = base::getNum($argAssociatedId, 0);
      $db = page::db();
      if (!is_null($db))
      {
        $table = tpl::take('global.universal/upload:config.db_table', 'cfg');
        $prefix = tpl::take('global.universal/upload:config.db_prefix', 'cfg');
        if (!base::isEmpty($table) && !base::isEmpty($prefix))
        {
          $sqlstr = "update " . $table . " set " . $prefix . "status=2 where " . $prefix . "genre='" . addslashes($genre) . "' and " . $prefix . "associated_id=" . $associatedId;
          $re = $db -> exec($sqlstr);
          if (is_numeric($re)) $bool = true;
        }
      }
      return $bool;
    }

    public static function statusUpdate($argGenre, $argAssociatedId, $argFileInfo)
    {
      $bool = false;
      $genre = $argGenre;
      $associatedId = base::getNum($argAssociatedId, 0);
      $fileInfo = $argFileInfo;
      $fileInfoArray = json_decode($fileInfo, true);
      if (is_array($fileInfoArray))
      {
        $db = page::db();
        if (!is_null($db))
        {
          $table = tpl::take('global.universal/upload:config.db_table', 'cfg');
          $prefix = tpl::take('global.universal/upload:config.db_prefix', 'cfg');
          if (!base::isEmpty($table) && !base::isEmpty($prefix))
          {
            $updateInfo = function($argUploadId) use ($db, $genre, $table, $prefix, $associatedId, &$bool)
            {
              $myUploadId = base::getNum($argUploadId, 0);
              $preset = array();
              $preset[$prefix . 'status'] = 1;
              $preset[$prefix . 'genre'] = $genre;
              $preset[$prefix . 'associated_id'] = $associatedId;
              $sqlstr = auto::getAutoUpdateSQLByVars($table, $prefix . 'id', $myUploadId, $preset);
              $re = $db -> exec($sqlstr);
              if (is_numeric($re)) $bool = true;
            };
            $uploadid = base::getNum(@$fileInfoArray['uploadid'], 0);
            if ($uploadid != 0) $updateInfo($uploadid);
            else
            {
              foreach ($fileInfoArray as $key => $val)
              {
                $newFileInfoArray = json_decode($val, true);
                if (is_array($newFileInfoArray))
                {
                  $uploadid = base::getNum(@$newFileInfoArray['uploadid'], 0);
                  if ($uploadid != 0) $updateInfo($uploadid);
                }
              }
            }
          }
        }
      }
      return $bool;
    }

    public static function statusAutoUpdate($argGenre, $argAssociatedId, $argTable, $argPrefix)
    {
      $bool = true;
      $genre = $argGenre;
      $associatedId = base::getNum($argAssociatedId, 0);
      $table = $argTable;
      $prefix = $argPrefix;
      $db = page::db();
      if (!is_null($db))
      {
        $sql = new sql($db, $table, $prefix);
        $sql -> id = $associatedId;
        $sqlstr = $sql -> sql;
        $rs = $db -> fetch($sqlstr);
        if (is_array($rs))
        {
          self::statusReset($genre, $associatedId);
          $columns = $db -> showFullColumns($table);
          foreach ($columns as $i => $item)
          {
            $filedName = $item['Field'];
            $comment = base::getString($item['Comment']);
            if (!base::isEmpty($comment))
            {
              $commentAry = json_decode($comment, true);
              if (!empty($commentAry) && array_key_exists('uploadStatusAutoUpdate', $commentAry))
              {
                $autoUpdate = base::getString($commentAry['uploadStatusAutoUpdate']);
                if ($autoUpdate == 'true')
                {
                  if (self::statusUpdate($genre, $associatedId, $rs[$filedName]) == false) $bool = false;
                }
              }
            }
          }
        }
      }
      return $bool;
    }

    public static function unlinkByIds($argIds)
    {
      $bool = false;
      $ids = $argIds;
      $db = page::db();
      if (!is_null($db) && base::checkIDAry($ids))
      {
        $table = tpl::take('global.universal/upload:config.db_table', 'cfg');
        $prefix = tpl::take('global.universal/upload:config.db_prefix', 'cfg');
        if (!base::isEmpty($table) && !base::isEmpty($prefix))
        {
          $bool = true;
          $sqlstr = "select * from " . $table . " where " . $prefix . "id in (" . $ids . ")";
          $rsa = $db -> fetchAll($sqlstr);
          foreach ($rsa as $i => $rs)
          {
            $rsGenre = base::getString($rs[$prefix . 'genre']);
            $rsFilepath = base::getString($rs[$prefix . 'filepath']);
            $fileFullPath = route::getActualRoute($rsGenre . '/' . $rsFilepath);
            if (is_file($fileFullPath))
            {
              if (!unlink($fileFullPath)) $bool = false;
            }
          }
        }
      }
      return $bool;
    }

    public static function up2self($argFile, $argLimit = '', $argTargetPath = '', $argNeedUploadId = true)
    {
      $file = $argFile;
      $limit = $argLimit;
      $targetPath = $argTargetPath;
      $needUploadId = $argNeedUploadId;
      $limitFileResizeAry = null;
      $upResultArray = array();
      $upResultArray['status'] = 0;
      $upResultArray['message'] = tpl::take('::console.text-upload-error-others', 'lng');
      $upResultArray['para'] = '';
      $uploadPath = tpl::take('config.upload_path', 'cfg');
      $allowFiletype = tpl::take('config.upload_filetype', 'cfg');
      $allowFilesize = base::getNum(tpl::take('config.upload_filesize', 'cfg'), 0);
      if (base::isEmpty($allowFiletype)) $allowFiletype = tpl::take('global.config.upload_filetype', 'cfg');
      if ($allowFilesize == 0) $allowFilesize = base::getNum(tpl::take('global.config.upload_filesize', 'cfg'), 0);
      if (!base::isEmpty($limit))
      {
        $limitFiletype = tpl::take('config.upload_filetype_limit_' . $limit, 'cfg');
        $limitFilesize = base::getNum(tpl::take('config.upload_filesize_limit_' . $limit, 'cfg'), 0);
        $limitFileResize = tpl::take('config.upload_fileresize_limit_' . $limit, 'cfg');
        if (!base::isEmpty($limitFiletype)) $allowFiletype = $limitFiletype;
        if ($limitFilesize != 0) $allowFilesize = $limitFilesize;
        if (!base::isEmpty($limitFileResize)) $limitFileResizeAry = json_decode($limitFileResize, true);
      }
      if (is_array($file))
      {
        $filename = $file['name'];
        $tmp_filename = $file['tmp_name'];
        $filesize = base::getNum($file['size'], 0);
        $filetype = strtolower(base::getLRStr($filename, '.', 'right'));
        if (base::isEmpty($tmp_filename))
        {
          $upResultArray['message'] = tpl::take('::console.text-upload-error-1', 'lng');
        }
        else if (!base::checkInstr($allowFiletype, $filetype, ','))
        {
          $upResultArray['message'] = str_replace('{$allowfiletype}', $allowFiletype, tpl::take('::console.text-upload-error-2', 'lng'));
        }
        else if ($filesize > $allowFilesize)
        {
          $upResultArray['message'] = str_replace('{$allowfilesize}', base::formatFileSize($allowFilesize), tpl::take('::console.text-upload-error-3', 'lng'));
        }
        else
        {
          $canMove = false;
          if (!base::isEmpty($targetPath))
          {
            if ($filetype != base::getLRStr($targetPath, '.', 'right')) $upResultArray['message'] = str_replace('{$filetype}', $filetype, tpl::take('::console.text-upload-error-4', 'lng'));
            else
            {
              $canMove = true;
              $uploadFullPath = $targetPath;
            }
          }
          else
          {
            $canMove = true;
            $uploadPath = $uploadPath . base::formatDate(base::getDateTime(), '-1') . '/' . base::formatDate(base::getDateTime(), '-2') . base::formatDate(base::getDateTime(), '-3') . '/';
            $uploadFullPath = $uploadPath . base::formatDate(base::getDateTime(), '11') . base::getRandomString(2) . '.' . $filetype;
            if (!is_dir($uploadPath)) @mkdir($uploadPath, 0777, true);
          }
          if ($canMove == true)
          {
            if (move_uploaded_file($tmp_filename, $uploadFullPath))
            {
              if (base::isImage($filetype) && !empty($limitFileResizeAry))
              {
                $resizeWidth = base::getNum($limitFileResizeAry['width'], 0);
                $resizeHeight = base::getNum($limitFileResizeAry['height'], 0);
                $resizeScale = base::getNum($limitFileResizeAry['scale'], 0);
                $resizeQuality = base::getNum($limitFileResizeAry['quality'], 0);
                image::resizeImage($uploadFullPath, $uploadFullPath, $resizeWidth, $resizeHeight, $resizeScale, 0, $resizeQuality);
              }
              $paraArray = array();
              $paraArray['filename'] = $filename;
              $paraArray['filesize'] = $filesize;
              $paraArray['filetype'] = $filetype;
              $paraArray['filepath'] = $uploadFullPath;
              $paraArray['fileurl'] = $uploadFullPath;
              $paraArray['filesizetext'] = base::formatFileSize($filesize);
              $uploadid = 0;
              if ($needUploadId == true) $uploadid = self::getUploadId($paraArray);
              $paraArray['uploadid'] = $uploadid;
              $upResultArray['status'] = 1;
              $upResultArray['message'] = 'done';
              $upResultArray['para'] = json_encode($paraArray);
            }
          }
        }
      }
      $tmpstr = json_encode($upResultArray);
      return $tmpstr;
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>
