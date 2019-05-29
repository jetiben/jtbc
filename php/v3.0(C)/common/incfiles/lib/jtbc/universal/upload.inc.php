<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc\universal {
  use jtbc\auto;
  use jtbc\base;
  use jtbc\dal;
  use jtbc\tpl;
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

    public static function getTargetClass()
    {
      $targetClass = null;
      $ns = __NAMESPACE__;
      if (!defined('UPLOAD_MODE')) $targetClass = $ns . '\\upload2self';
      else
      {
        if (UPLOAD_MODE == 'OSS' && class_exists($ns . '\\upload2oss')) $targetClass = $ns . '\\upload2oss';
      }
      return $targetClass;
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
      return self::getTargetClass()::unlinkByIds($argIds);
    }

    public static function upFile($argFile, $argLimit = '', $argTargetPath = '', $argNeedUploadId = true, $argGenre = null)
    {
      return self::getTargetClass()::upFile($argFile, $argLimit, $argTargetPath, $argNeedUploadId, $argGenre);
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>