<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc\universal {
  use jtbc\auto;
  use jtbc\base;
  use jtbc\dal;
  use jtbc\file;
  use jtbc\image;
  use jtbc\route;
  use jtbc\tpl;
  use jtbc\verify;
  class upload
  {
    public static function getUploadId($argFileInfo, $argGenre)
    {
      $fileInfo = $argFileInfo;
      $genre = $argGenre;
      $uploadid = 0;
      if (is_array($fileInfo))
      {
        $table = tpl::take('global.universal/upload:config.db_table', 'cfg');
        $prefix = tpl::take('global.universal/upload:config.db_prefix', 'cfg');
        if (!base::isEmpty($table) && !base::isEmpty($prefix))
        {
          $preset = array();
          $preset['topic'] = $fileInfo['filename'];
          $preset['filepath'] = $fileInfo['filepath'];
          $preset['fileurl'] = $fileInfo['fileurl'];
          $preset['filetype'] = $fileInfo['filetype'];
          $preset['filesize'] = $fileInfo['filesize'];
          $preset['filesizetext'] = $fileInfo['filesizetext'];
          $preset['genre'] = $genre;
          $preset['time'] = base::getDateTime();
          $re = auto::autoInsertByVars($preset, $table, $prefix);
          if (is_numeric($re)) $uploadid = auto::$lastInsertId;
        }
      }
      return $uploadid;
    }

    public static function statusReset($argGenre, $argAssociatedId, $argGroup = 0)
    {
      $bool = false;
      $genre = $argGenre;
      $associatedId = base::getNum($argAssociatedId, 0);
      $group = base::getNum($argGroup, 0);
      $table = tpl::take('global.universal/upload:config.db_table', 'cfg');
      $prefix = tpl::take('global.universal/upload:config.db_prefix', 'cfg');
      if (!base::isEmpty($table) && !base::isEmpty($prefix))
      {
        $dal = new dal($table, $prefix);
        $dal -> genre = $genre;
        $dal -> associated_id = $associatedId;
        $dal -> group = $group;
        $re = $dal -> update(array('status' => 2));
        if (is_numeric($re)) $bool = true;
      }
      return $bool;
    }

    public static function statusUpdate($argGenre, $argAssociatedId, $argFileInfo, $argGroup = 0)
    {
      $bool = false;
      $genre = $argGenre;
      $associatedId = base::getNum($argAssociatedId, 0);
      $fileInfo = $argFileInfo;
      $fileInfoArray = json_decode($fileInfo, true);
      $group = base::getNum($argGroup, 0);
      if (is_array($fileInfoArray))
      {
        $table = tpl::take('global.universal/upload:config.db_table', 'cfg');
        $prefix = tpl::take('global.universal/upload:config.db_prefix', 'cfg');
        if (!base::isEmpty($table) && !base::isEmpty($prefix))
        {
          $updateInfo = function($argUploadId) use ($genre, $table, $prefix, $associatedId, $group, &$bool)
          {
            $myUploadId = base::getNum($argUploadId, 0);
            $dal = new dal($table, $prefix);
            $dal -> id = $myUploadId;
            $preset = array();
            $preset['status'] = 1;
            $preset['genre'] = $genre;
            $preset['associated_id'] = $associatedId;
            $preset['group'] = $group;
            $re = $dal -> update($preset);
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
      return $bool;
    }

    public static function statusAutoUpdate($argGenre, $argAssociatedId, $argGroup = 0, $argTable = null, $argPrefix = null, $argDbLink = 'any')
    {
      $bool = true;
      $genre = $argGenre;
      $associatedId = base::getNum($argAssociatedId, 0);
      $group = base::getNum($argGroup, 0);
      $table = $argTable;
      $prefix = $argPrefix;
      $dbLink = $argDbLink;
      $dal = new dal($table, $prefix, $dbLink);
      $dal -> id = $associatedId;
      $rs = $dal -> select();
      if (is_array($rs))
      {
        self::statusReset($genre, $associatedId, $group);
        $columns = $dal -> db -> showFullColumns($dal -> table);
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
                if (self::statusUpdate($genre, $associatedId, $rs[$filedName], $group) == false) $bool = false;
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
      if (base::checkIDAry($ids))
      {
        $table = tpl::take('global.universal/upload:config.db_table', 'cfg');
        $prefix = tpl::take('global.universal/upload:config.db_prefix', 'cfg');
        if (!base::isEmpty($table) && !base::isEmpty($prefix))
        {
          $bool = true;
          $dal = new dal($table, $prefix);
          $dal -> setIn('id', $ids);
          $rsa = $dal -> selectAll('*', false);
          foreach ($rsa as $i => $rs)
          {
            $rsGenre = base::getString($dal -> val($rs, 'genre'));
            $rsFilepath = base::getString($dal -> val($rs, 'filepath'));
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

    public static function up2self($argFile, $argLimit = '', $argTargetPath = '', $argNeedUploadId = true, $argGenre = null)
    {
      $file = $argFile;
      $limit = $argLimit;
      $targetPath = $argTargetPath;
      $needUploadId = $argNeedUploadId;
      $genre = $argGenre;
      if (is_null($genre)) $genre = route::getCurrentGenre();
      $targetFileType = base::getLRStr($targetPath, '.', 'right');
      $limitFileResizeAry = null;
      $upResultArray = array();
      $upResultArray['status'] = 0;
      $upResultArray['message'] = tpl::take('::console.text-upload-error-others', 'lng');
      $upResultArray['param'] = '';
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
        $fileObject = $file['file'];
        $fileSize = base::getNum($file['fileSize'], 0);
        $chunkCount = base::getNum($file['chunkCount'], 0);
        $chunkCurrentIndex = base::getNum($file['chunkCurrentIndex'], 0);
        $timeStringRandom = base::getString($file['timeStringRandom']);
        if (strlen($timeStringRandom) == 28 && verify::isNumber($timeStringRandom))
        {
          $filename = $fileObject['name'];
          $tmp_filename = $fileObject['tmp_name'];
          $filetype = strtolower(base::getLRStr($filename, '.', 'right'));
          if (base::isEmpty($tmp_filename))
          {
            $upResultArray['message'] = tpl::take('::console.text-upload-error-1', 'lng');
          }
          else if (!base::checkInstr($allowFiletype, $filetype, ','))
          {
            $upResultArray['message'] = str_replace('{$allowfiletype}', $allowFiletype, tpl::take('::console.text-upload-error-2', 'lng'));
          }
          else if ($fileSize > $allowFilesize)
          {
            $upResultArray['message'] = str_replace('{$allowfilesize}', base::formatFileSize($allowFilesize), tpl::take('::console.text-upload-error-3', 'lng'));
          }
          else if (!base::isEmpty($targetPath) && $filetype != $targetFileType)
          {
            $upResultArray['message'] = str_replace('{$filetype}', $targetFileType, tpl::take('::console.text-upload-error-4', 'lng'));
          }
          else
          {
            $cacheChunkDir = route::getActualRoute(CACHEDIR) . '/' . $timeStringRandom;
            if (!is_dir($cacheChunkDir)) @mkdir($cacheChunkDir, 0777, true);
            if (is_dir($cacheChunkDir))
            {
              $status = 0;
              $cacheChunkPath = $cacheChunkDir . '/' . $chunkCurrentIndex . '.tmp';
              if (move_uploaded_file($tmp_filename, $cacheChunkPath))
              {
                $status = -1;
                if ($chunkCount == $chunkCurrentIndex)
                {
                  $uploadFullPath = $targetPath;
                  if (base::isEmpty($uploadFullPath))
                  {
                    $uploadPathDir = $uploadPath . base::formatDate(base::getDateTime(), '-1') . '/' . base::formatDate(base::getDateTime(), '-2') . base::formatDate(base::getDateTime(), '-3') . '/';
                    if (!is_dir($uploadPathDir)) @mkdir($uploadPathDir, 0777, true);
                    $uploadFullPath = $uploadPathDir . base::formatDate(base::getDateTime(), '11') . base::getRandomString(2) . '.' . $filetype;
                  }
                  $fileTempPath = $cacheChunkDir . '/temp.tmp';
                  $fpTemp = fopen($fileTempPath, 'ab');
                  $fileMergeError = false;
                  for ($i = 0; $i <= $chunkCurrentIndex; $i ++)
                  {
                    $currentCacheChunkPath = $cacheChunkDir . '/' . $i . '.tmp';
                    if (!is_file($currentCacheChunkPath))
                    {
                      $fileMergeError = true;
                      break;
                    }
                    else
                    {
                      $chunkHandle = fopen($currentCacheChunkPath, 'r');
                      fwrite($fpTemp, fread($chunkHandle, filesize($currentCacheChunkPath)));
                      fclose($chunkHandle);
                    }
                  }
                  if ($fileMergeError == true)
                  {
                    $upResultArray['message'] = tpl::take('::console.text-upload-error-5', 'lng');
                  }
                  else
                  {
                    $fileTrueSize = filesize($fileTempPath);
                    if ($fileTrueSize > $allowFilesize)
                    {
                      $upResultArray['message'] = str_replace('{$allowfilesize}', base::formatFileSize($allowFilesize), tpl::take('::console.text-upload-error-3', 'lng'));
                    }
                    else
                    {
                      $renameFile = @rename($fileTempPath, $uploadFullPath);
                      if ($renameFile == true)
                      {
                        $status = 1;
                        if (base::isImage($filetype) && !empty($limitFileResizeAry))
                        {
                          $resizeWidth = base::getNum($limitFileResizeAry['width'], 0);
                          $resizeHeight = base::getNum($limitFileResizeAry['height'], 0);
                          $resizeMode = base::getString($limitFileResizeAry['mode']);
                          $resizeQuality = base::getNum($limitFileResizeAry['quality'], 0);
                          image::resizeImage($uploadFullPath, $uploadFullPath, $resizeWidth, $resizeHeight, $resizeMode, 0, $resizeQuality);
                        }
                        $paraArray = array();
                        $paraArray['filename'] = $filename;
                        $paraArray['filesize'] = $fileTrueSize;
                        $paraArray['filetype'] = $filetype;
                        $paraArray['filepath'] = $uploadFullPath;
                        $paraArray['fileurl'] = $uploadFullPath;
                        $paraArray['filesizetext'] = base::formatFileSize($fileTrueSize);
                        $uploadid = 0;
                        if ($needUploadId == true) $uploadid = self::getUploadId($paraArray, $genre);
                        $paraArray['uploadid'] = $uploadid;
                        $upResultArray['status'] = $status;
                        $upResultArray['message'] = 'done';
                        $upResultArray['param'] = json_encode($paraArray);
                      }
                      else $upResultArray['message'] = tpl::take('::console.text-upload-error-6', 'lng');
                    }
                  }
                  file::removeDir($cacheChunkDir);
                }
                else
                {
                  $upResultArray['status'] = $status;
                  $upResultArray['message'] = 'continue';
                }
              }
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