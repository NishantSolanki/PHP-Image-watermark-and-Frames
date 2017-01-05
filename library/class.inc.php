<?php
# ========================================================================#
#  Author:    Nishant Solanki (nishant.solanki34@gmail.com)
#  Version:   1.0.0
#  Date:      5th January 2017
#  Requires : Requires PHP GD library, PHP Image Magician.
# ========================================================================#

require 'image_magician/php_image_magician.php';

class image_processor
{

    function watermark_image($target, $wtrmrk_file, $newcopy)
    {
        $watermark = imagecreatefrompng($wtrmrk_file);
        imagealphablending($watermark, false);
        imagesavealpha($watermark, true);
        $img       = imagecreatefromjpeg($target);
        $img_w     = imagesx($img);
        $img_h     = imagesy($img);
        $wtrmrk_w  = imagesx($watermark);
        $wtrmrk_h  = imagesy($watermark);
        $dst_x     = $img_w - $wtrmrk_w;
        $dst_y     = $img_h - $wtrmrk_h;
        imagecopy($img, $watermark, $dst_x, $dst_y, 0, 0, $wtrmrk_w, $wtrmrk_h);
        imagejpeg($img, $newcopy, 100);
        imagedestroy($img);
        imagedestroy($watermark);
    }

    function addWatermark($sourceImage, $watermark, $targetImage, $resize_watermark = false, $fullwidth_watermark = false, $fullheight_watermark = false, $transparency = 100, $quality = 90, $offsetX = 0, $offsetY = 0, $alignX = 'right', $alignY = 'bottom')
    {
        $new_watermark_name       = 'temp.png';
        $process_watermark_height = false;
        $process_watermark        = false;

        if (!$image = @imagecreatefromjpeg($sourceImage))
        {
            if (!$image = @imagecreatefrompng($sourceImage))
            {
                return false;
            }
        }
        if (!$imageWatermark = @imagecreatefrompng($watermark))
        {
            return false;
        }
        imagealphablending($imageWatermark, false);
        imagesavealpha($imageWatermark, true);
        list($imageWidth, $imageHeight) = getimagesize($sourceImage);
        list($watermarkWidth, $watermarkHeight) = getimagesize($watermark);

        if ($watermarkWidth > $imageWidth || $watermarkHeight > $imageHeight)
        {
            if ($resize_watermark)
            {
                $process_watermark = true;
            }
        }

        if ($fullwidth_watermark)
        {
            $process_watermark = true;
        }
        if ($fullheight_watermark)
        {
            $process_watermark_height = true;
        }

        if ($process_watermark)
        {
            $wdir = 'watermarks/temp';
            if (!file_exists($wdir))
            {
                mkdir($wdir, 0777, true);
            }
            $new_watermark_name = 'temp_' . substr($watermark, strrpos($watermark, '/') + 1);
            $wwidth             = $imageWidth;
            $wheight            = $imageHeight;
            if ($offsetX > 0)
            {
                $wwidth = $imageWidth - (2 * $offsetX);
            }
            if ($offsetY > 0)
            {
                $wheight = $imageHeight - (2 * $offsetY);
            }
            if ($process_watermark_height)
            {

                $this->make_thumb($watermark, 'watermarks/temp/' . $new_watermark_name, $wwidth, $wheight, 100, 0);
            }
            else
            {
                $this->make_thumb($watermark, 'watermarks/temp/' . $new_watermark_name, $wwidth, 0, 100);
            }

            if (!$imageWatermark = @imagecreatefrompng('watermarks/temp/' . $new_watermark_name))
            {
                return false;
            }

            imagealphablending($imageWatermark, false);
            imagesavealpha($imageWatermark, true);

            list($watermarkWidth, $watermarkHeight) = getimagesize('watermarks/temp/' . $new_watermark_name);
        }

        $alignmentsX = array('left', 'middle', 'right');
        $alignmentsY = array('top', 'middle', 'bottom');

        if (!in_array($alignX, $alignmentsX))
        {
            $alignX = 'right';
        }

        if (!in_array($alignY, $alignmentsY))
        {
            $alignY = 'bottom';
        }

        if ($alignX == 'middle')
        {
            $posX = $imageWidth / 2 - $watermarkWidth / 2 + $offsetX;
        }
        elseif ($alignX == 'left')
        {
            $posX = $offsetX;
        }
        elseif ($alignX == 'right')
        {
            $posX = $imageWidth - $watermarkWidth - $offsetX;
        }

        if ($alignY == 'middle')
        {
            $posY = $imageHeight / 2 - $watermarkHeight / 2 + $offsetY;
        }
        elseif ($alignY == 'top')
        {
            $posY = $offsetY;
        }
        elseif ($alignY == 'bottom')
        {
            $posY = $imageHeight - $watermarkHeight - $offsetY;
        }

        //imagecopymerge($image, $imageWatermark, $posX, $posY, 0, 0, $watermarkWidth, $watermarkHeight, 100);
        imagecopy($image, $imageWatermark, $posX, $posY, 0, 0, $watermarkWidth, $watermarkHeight);
        imagejpeg($image, $targetImage, $quality);
        imagedestroy($image);
        imagedestroy($imageWatermark);


        if (file_exists('watermarks/temp/' . $new_watermark_name))
        {
            unlink('watermarks/temp/' . $new_watermark_name);
        }

        return true;
    }

    function directories($directory, $type = '*')
    {
        $glob = glob($directory . '/' . $type);

        if ($glob === false)
        {
            return array();
        }

        if ($type == '*')
        {
            return array_filter($glob, function($dir) {
                return is_dir($dir);
            });
        }
        else
        {
            return array_filter($glob, function($dir) {
                return is_file($dir);
            });
        }
    }

    function png2jpg($filePath, $outputFile, $quality)
    {
        $image = imagecreatefrompng($filePath);
        $bg    = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
        imagealphablending($bg, TRUE);
        imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
        imagedestroy($image);
        imagejpeg($bg, $outputFile, $quality);
        imagedestroy($bg);
        unlink($filePath);
    }

    function make_thumb($src, $dest, $width, $height, $quality, $mode = 3)
    {
        //******* ----  mode ---- 0=exact,1=portrait,2=landscape,3=auto,4=crop ----- *******//
        $magicianObj = new imageLib($src);
        $magicianObj->resizeImage($width, $height, $mode);
        $magicianObj->saveImage($dest, $quality);
    }

    function shareFB($filepath = '')
    {
        if (!FB_SHARE)
        {
            return true;
        }
        $FB_ACCESS_TOKEN = FB_PAGE_TOKEN;
        $url             = "https://graph.facebook.com/" . PAGE_ID . "/photos";
        $attachment      = array(
            'access_token' => $FB_ACCESS_TOKEN,
            'message'      => MESSAGE,
            'source'       => new CURLFile($filepath)
                //'source'       => '@'.realpath('1.jpg')
        );
        $ch              = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 50);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $attachment);
        $result          = curl_exec($ch);
        $res             = json_decode($result, true);
        curl_close($ch);
        if (isset($res['id']) && $res['id'] != '')
        {
            return true;
        }
        else
        {
            return false;
        }
    }

}
