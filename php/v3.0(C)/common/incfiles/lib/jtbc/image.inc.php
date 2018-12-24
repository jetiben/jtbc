<?php
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
namespace jtbc {
  class image
  {
    public static function resizeImage($argFilePath1, $argFilePath2, $argWidth, $argHeight, $argMode = 'cover', $argRotate = 0, $argQuality = 100)
    {
      $bool = false;
      $filePath1 = base::getString($argFilePath1);
      $filePath2 = base::getString($argFilePath2);
      $width = base::getNum($argWidth, 0);
      $height = base::getNum($argHeight, 0);
      $mode = base::getString($argMode);
      $rotate = base::getNum($argRotate, 0);
      $quality = base::getNum($argQuality, 0);
      if (!base::isEmpty($filePath1) && !base::isEmpty($filePath2) && $width != 0 && $height != 0)
      {
        $img = null;
        $imageType = base::getLRStr($filePath1, '.', 'right');
        if ($imageType == 'jpg' || $imageType == 'jpeg') $img = @imagecreatefromjpeg($filePath1);
        else if ($imageType == 'gif') $img = @imagecreatefromgif($filePath1);
        else if ($imageType == 'png') $img = @imagecreatefrompng($filePath1);
        if ($img && function_exists('imagecopyresampled'))
        {
          $imageX = 0;
          $imageY = 0;
          $imageSize = getimagesize($filePath1);
          $imageWidth = $imageSize[0];
          $imageHeight = $imageSize[1];
          $newImageWidth = $imageWidth;
          $newImageHeight = $imageHeight;
          if ($width == -1) $width = $imageWidth;
          if ($height == -1) $height = $imageHeight;
          if ($mode == 'cover')
          {
            $scNum1 = $imageWidth / $width;
            $scNum2 = $imageHeight / $height;
            if ($scNum1 >= $scNum2)
            {
              $newImageWidth = $scNum2 * $width;
              $imageX = round(abs($imageWidth - $newImageWidth) / 2);
            }
            else
            {
              $newImageHeight = $scNum1 * $height;
              $imageY = round(abs($imageHeight - $newImageHeight) / 2);
            }
          }
          else if ($mode == 'contain')
          {
            if ($imageWidth <= $width && $imageHeight <= $height)
            {
              $width = $imageWidth;
              $height = $imageHeight;
            }
            else
            {
              $scNum1 = $imageWidth / $width;
              $scNum2 = $imageHeight / $height;
              if ($imageWidth <= $width) $width = $imageWidth / $scNum2;
              else if ($imageHeight <= $height) $height = $imageHeight / $scNum1;
              else
              {
                if ($scNum1 >= $scNum2) $height = $imageHeight / $scNum1;
                else $width = $imageWidth / $scNum2;
              }
            }
          }
          $imgs = imagecreatetruecolor($width, $height);
          $bgColor = imagecolorallocate($imgs, 255, 255, 255);
          imagefill($imgs, 0, 0, $bgColor);
          imagecopyresampled($imgs, $img, 0, 0, $imageX, $imageY, $width, $height, $newImageWidth, $newImageHeight);
          if ($rotate != 0) $imgs = imagerotate($imgs, $rotate, $bgColor);
          if ($imageType == 'jpg' || $imageType == 'jpeg') $bool = imagejpeg($imgs, $filePath2, $quality);
          else if ($imageType == 'gif') $bool = imagegif($imgs, $filePath2);
          else if ($imageType == 'png') $bool = imagepng($imgs, $filePath2);
          imagedestroy($img);
          imagedestroy($imgs);
        }
      }
      return $bool;
    }

    public static function watermarkText($argFilePath1, $argFilePath2, $argText, $argTTFFilePath, $argOrigin = 0, $argX = null, $argY = null, $argFontSize = 14, $argColorRGBAlpha = null, $argQuality = 100)
    {
      $bool = false;
      $filePath1 = base::getString($argFilePath1);
      $filePath2 = base::getString($argFilePath2);
      $text = base::getString($argText);
      $TTFFilePath = base::getString($argTTFFilePath);
      $origin = base::getNum($argOrigin, 0);
      $x = $y = 20;
      $xAuto = $yAuto = false;
      if (is_null($argX)) $xAuto = true;
      else $x = base::getNum($argX, 0);
      if (is_null($argY)) $yAuto = true;
      else $y = base::getNum($argY, 0);
      $fontSize = base::getNum($argFontSize, 0);
      $quality = base::getNum($argQuality, 0);
      $colorRGBAlpha = $argColorRGBAlpha;
      $colorR = $colorG = $colorB = $alpha = 0;
      if (is_array($colorRGBAlpha))
      {
        if (count($colorRGBAlpha) == 4)
        {
          $colorR = base::getNum($colorRGBAlpha[0], 0);
          $colorG = base::getNum($colorRGBAlpha[1], 0);
          $colorB = base::getNum($colorRGBAlpha[2], 0);
          $alpha = base::getNum($colorRGBAlpha[3], 0);
          if ($colorR < 0 || $colorR > 255) $colorR = 0;
          if ($colorG < 0 || $colorG > 255) $colorG = 0;
          if ($colorB < 0 || $colorB > 255) $colorB = 0;
          if ($alpha < 0 || $alpha > 127) $alpha = 0;
        }
      }
      if (!base::isEmpty($filePath1) && !base::isEmpty($filePath2))
      {
        $img = null;
        $imageType = base::getLRStr($filePath1, '.', 'right');
        if ($imageType == 'jpg' || $imageType == 'jpeg') $img = @imagecreatefromjpeg($filePath1);
        else if ($imageType == 'gif') $img = @imagecreatefromgif($filePath1);
        else if ($imageType == 'png') $img = @imagecreatefrompng($filePath1);
        if ($img && function_exists('imagettftext'))
        {
          $currentX = 0;
          $currentY = 0;
          $imageSize = getimagesize($filePath1);
          $imageWidth = $imageSize[0];
          $imageHeight = $imageSize[1];
          $color = imagecolorallocatealpha($img, $colorR, $colorG, $colorB, $alpha);
          $fontBox = imagettfbbox($fontSize, 0, realpath($TTFFilePath), $text);
          if ($origin == -1)
          {
            $currentX = round(($imageWidth / 2) - ($fontBox[4] - $fontBox[6]) / 2);
            $currentY = round(($imageHeight / 2) - ($fontBox[7] + $fontBox[1]) / 2);
            if ($xAuto == false) $currentX + $x;
            if ($yAuto == false) $currentY + $y;
          }
          else if ($origin == 0)
          {
            $currentX = $x - $fontBox[6];
            $currentY = $y - $fontBox[7];
          }
          else if ($origin == 1)
          {
            $currentX = $imageWidth - $x - $fontBox[4];
            $currentY = $y - $fontBox[5];
          }
          else if ($origin == 2)
          {
            $currentX = $x - $fontBox[0];
            $currentY = $imageHeight - $y - $fontBox[1];
          }
          else if ($origin == 3)
          {
            $currentX = $imageWidth - $x - $fontBox[2];
            $currentY = $imageHeight - $y - $fontBox[3];
          }
          imagettftext($img, $fontSize, 0, $currentX, $currentY, $color, realpath($TTFFilePath), $text);
          if ($imageType == 'jpg' || $imageType == 'jpeg') $bool = imagejpeg($img, $filePath2, $quality);
          else if ($imageType == 'gif') $bool = imagegif($img, $filePath2);
          else if ($imageType == 'png') $bool = imagepng($img, $filePath2);
          imagedestroy($img);
        }
      }
      return $bool;
    }

    public static function watermarkImage($argFilePath1, $argFilePath2, $argImageFilePath, $argOrigin = 0, $argX = null, $argY = null, $argOpacity = 100, $argQuality = 100)
    {
      $bool = false;
      $filePath1 = base::getString($argFilePath1);
      $filePath2 = base::getString($argFilePath2);
      $imageFilePath = base::getString($argImageFilePath);
      $origin = base::getNum($argOrigin, 0);
      $x = $y = 20;
      $xAuto = $yAuto = false;
      if (is_null($argX)) $xAuto = true;
      else $x = base::getNum($argX, 0);
      if (is_null($argY)) $yAuto = true;
      else $y = base::getNum($argY, 0);
      $opacity = base::getNum($argOpacity, 0);
      $quality = base::getNum($argQuality, 0);
      if (!base::isEmpty($filePath1) && !base::isEmpty($filePath2) && !base::isEmpty($imageFilePath))
      {
        if (is_file($imageFilePath))
        {
          $img = null;
          $imgFile = null;
          $imageType = base::getLRStr($filePath1, '.', 'right');
          $imageFileType = base::getLRStr($imageFilePath, '.', 'right');
          if ($imageType == 'jpg' || $imageType == 'jpeg') $img = @imagecreatefromjpeg($filePath1);
          else if ($imageType == 'gif') $img = @imagecreatefromgif($filePath1);
          else if ($imageType == 'png') $img = @imagecreatefrompng($filePath1);
          if ($imageFileType == 'jpg' || $imageFileType == 'jpeg') $imgFile = @imagecreatefromjpeg($imageFilePath);
          else if ($imageFileType == 'gif') $imgFile = @imagecreatefromgif($imageFilePath);
          else if ($imageFileType == 'png') $imgFile = @imagecreatefrompng($imageFilePath);
          if ($img && $imgFile && function_exists('imagecopy') && function_exists('imagecopymerge'))
          {
            $currentX = 0;
            $currentY = 0;
            $imageSize = getimagesize($filePath1);
            $imageWidth = $imageSize[0];
            $imageHeight = $imageSize[1];
            $imageFileSize = getimagesize($imageFilePath);
            $imageFileWidth = $imageFileSize[0];
            $imageFileHeight = $imageFileSize[1];
            if ($origin == -1)
            {
              $currentX = round($imageWidth / 2 - $imageFileWidth / 2);
              $currentY = round($imageHeight / 2 - $imageFileHeight / 2);
              if ($xAuto == false) $currentX + $x;
              if ($yAuto == false) $currentY + $y;
            }
            else if ($origin == 0)
            {
              $currentX = $x;
              $currentY = $y;
            }
            else if ($origin == 1)
            {
              $currentX = $imageWidth - $imageFileWidth - $x;
              $currentY = $y;
            }
            else if ($origin == 2)
            {
              $currentX = $x;
              $currentY = $imageHeight - $imageFileHeight - $y;
            }
            else if ($origin == 3)
            {
              $currentX = $imageWidth - $imageFileWidth - $x;
              $currentY = $imageHeight - $imageFileHeight - $y;
            }
            if ($opacity >= 100) imagecopy($img, $imgFile, $currentX, $currentY, 0, 0, $imageFileWidth, $imageFileHeight);
            else imagecopymerge($img, $imgFile, $currentX, $currentY, 0, 0, $imageFileWidth, $imageFileHeight, $opacity);
            if ($imageType == 'jpg' || $imageType == 'jpeg') $bool = imagejpeg($img, $filePath2, $quality);
            else if ($imageType == 'gif') $bool = imagegif($img, $filePath2);
            else if ($imageType == 'png') $bool = imagepng($img, $filePath2);
            imagedestroy($img);
            imagedestroy($imgFile);
          }
        }
      }
      return $bool;
    }
  }
}
//******************************//
// JTBC Powered by jtbc.cn      //
//******************************//
?>