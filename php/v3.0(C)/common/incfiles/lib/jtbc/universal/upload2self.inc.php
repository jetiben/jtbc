<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc\universal {
  use jtbc\base;
  use jtbc\dal;
  use jtbc\file;
  use jtbc\image;
  use jtbc\route;
  use jtbc\tpl;
  use jtbc\verify;
  class upload2self extends upload
  {
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

    public static function upFile($argFile, $argLimit = '', $argTargetPath = '', $argNeedUploadId = true, $argGenre = null)
    {
      $file = $argFile;
      $limit = $argLimit;
      $targetPath = $argTargetPath;
      $needUploadId = $argNeedUploadId;
      $genre = $argGenre;
      $status = 0;
      $message = tpl::take('::console.text-upload-error-others', 'lng');
      $param = '';
      if (is_null($genre)) $genre = route::getCurrentGenre();
      $targetFileType = base::getLRStr($targetPath, '.', 'right');
      $limitFileResizeAry = null;
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
            $message = tpl::take('::console.text-upload-error-1', 'lng');
          }
          else if (!base::checkInstr($allowFiletype, $filetype, ','))
          {
            $message = str_replace('{$allowfiletype}', $allowFiletype, tpl::take('::console.text-upload-error-2', 'lng'));
          }
          else if ($fileSize > $allowFilesize)
          {
            $message = str_replace('{$allowfilesize}', base::formatFileSize($allowFilesize), tpl::take('::console.text-upload-error-3', 'lng'));
          }
          else if (!base::isEmpty($targetPath) && $filetype != $targetFileType)
          {
            $message = str_replace('{$filetype}', $targetFileType, tpl::take('::console.text-upload-error-4', 'lng'));
          }
          else
          {
            $cacheChunkDir = route::getActualRoute(CACHEDIR) . '/' . $timeStringRandom;
            if (!is_dir($cacheChunkDir)) @mkdir($cacheChunkDir, 0777, true);
            if (is_dir($cacheChunkDir))
            {
              $cacheChunkPath = $cacheChunkDir . '/' . $chunkCurrentIndex . '.tmp';
              if (move_uploaded_file($tmp_filename, $cacheChunkPath))
              {
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
                  fclose($fpTemp);
                  if ($fileMergeError == true)
                  {
                    $message = tpl::take('::console.text-upload-error-5', 'lng');
                  }
                  else
                  {
                    $fileTrueSize = filesize($fileTempPath);
                    if ($fileTrueSize > $allowFilesize)
                    {
                      $message = str_replace('{$allowfilesize}', base::formatFileSize($allowFilesize), tpl::take('::console.text-upload-error-3', 'lng'));
                    }
                    else
                    {
                      $renameFile = @rename($fileTempPath, $uploadFullPath);
                      if ($renameFile == true)
                      {
                        if (base::isImage($filetype) && !empty($limitFileResizeAry))
                        {
                          $resizeWidth = base::getNum($limitFileResizeAry['width'], 0);
                          $resizeHeight = base::getNum($limitFileResizeAry['height'], 0);
                          $resizeMode = base::getString($limitFileResizeAry['mode']);
                          $resizeQuality = base::getNum($limitFileResizeAry['quality'], 0);
                          image::resizeImage($uploadFullPath, $uploadFullPath, $resizeWidth, $resizeHeight, $resizeMode, 0, $resizeQuality);
                        }
                        $paramArray = array();
                        $paramArray['filename'] = $filename;
                        $paramArray['filesize'] = $fileTrueSize;
                        $paramArray['filetype'] = $filetype;
                        $paramArray['filepath'] = $uploadFullPath;
                        $paramArray['fileurl'] = $uploadFullPath;
                        $paramArray['filesizetext'] = base::formatFileSize($fileTrueSize);
                        $uploadid = 0;
                        if ($needUploadId == true) $uploadid = self::getUploadId($paramArray, $genre);
                        $paramArray['uploadid'] = $uploadid;
                        $status = 1;
                        $message = 'done';
                        $param = json_encode($paramArray);
                      }
                      else $message = tpl::take('::console.text-upload-error-6', 'lng');
                    }
                  }
                  file::removeDir($cacheChunkDir);
                }
                else
                {
                  $status = -1;
                  $message = 'continue';
                }
              }
            }
          }
        }
      }
      $tmpstr = json_encode(array('status' => $status, 'message' => $message, 'param' => $param));
      return $tmpstr;
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>