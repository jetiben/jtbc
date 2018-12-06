<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  class file
  {
    public static function getFolderInfo($argPath)
    {
      $path = $argPath;
      $size = 0;
      $folder = 0;
      $file = 0;
      if (is_dir($path))
      {
        $dir = dir($path);
        while($entry = $dir -> read())
        {
          if ($entry != '.' && $entry != '..')
          {
            if (is_dir($path . $entry))
            {
              $folder += 1;
              $info = self::getFolderInfo($path . $entry . '/');
              if (is_array($info))
              {
                $folder += $info['folder'];
                $file += $info['file'];
                $size += $info['size'];
              }
            }
            else if (is_file($path . $entry))
            {
              $file += 1;
              $size += filesize($path . $entry);
            }
          }
        }
      }
      $info = array('size' => $size, 'folder' => $folder, 'file' => $file);
      return $info;
    }

    public static function isImageFormat($argFilepath)
    {
      $bool = false;
      $filepath = $argFilepath;
      if (is_file($filepath))
      {
        $file = fopen($filepath, 'rb');
        $head = fread($file, 0x400);
        fclose($file);
        if (substr($head, 0, 3) == "\xFF\xD8\xFF") $bool = true;
        else if (substr($head, 0, 4) == 'GIF8') $bool = true;
        else if (substr($head, 0, 8) == "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A") $bool = true;
      }
      return $bool;
    }

    public static function removeDir($argDir)
    {
      $bool = false;
      $dir = $argDir;
      $dirs = opendir($dir);
      while ($file = readdir($dirs))
      {
        if($file != '.' && $file != '..')
        {
          $repath = $dir . '/' . $file;
          if(!is_dir($repath)) @unlink($repath);
          else self::removeDir($repath);
        }
      }
      closedir($dirs);
      if(@rmdir($dir)) $bool = true;
      return $bool;
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>