<?php
# ========================================================================#
#  Author:    Nishant Solanki (nishant.solanki34@gmail.com)
#  Version:   1.0.0
#  Date:      5th January 2017
#  Requires : Requires PHP GD library, PHP Image Magician.
# ========================================================================#

ini_set('memory_limit', '-1');
ini_set('display_errors', 1);


set_time_limit(0);
require 'library/class.inc.php';
$obj        = new image_processor();
$image_dirs = $obj->directories('images/');

if (isset($_POST) && isset($_POST['submit']) && isset($_POST['dir_name']) && $_POST['dir_name'] != '')
{

    $dirname         = $_POST['dir_name'];
    $watermark_image = 'watermarks/' . $_POST['watermarks'];
    $processing_dir  = 'images/' . $dirname . '/processed_images';
    if (!file_exists($processing_dir))
    {
        mkdir($processing_dir, 0777, true);
    }

    $resize_watermark     = true;
    $fullwidth_watermark  = false;
    $fullheight_watermark = false;
    if (isset($_POST['watermark_width']) && $_POST['watermark_width'] == 1)
    {
        $fullwidth_watermark = true;
    }
    if (isset($_POST['watermark_height']) && $_POST['watermark_height'] == 1)
    {
        $fullheight_watermark = true;
    }
    if (isset($_POST['fframe']) && $_POST['fframe'] == 1)
    {
        $fullwidth_watermark  = true;
        $fullheight_watermark = true;
    }


    $transparency = 100;
    $quality      = $_POST['quality'];
    $offsetX      = $_POST['offsetx'];
    $offsetY      = $_POST['offsety'];
    $alignX       = $_POST['alignx'];
    $alignY       = $_POST['aligny'];

    $images = $obj->directories('images/' . $dirname . "/", "*.jpg");

    foreach ($images as $image)
    {
        $new_image_name = 'processed_image_' . substr($image, strrpos($image, '/') + 1);
        $new_image_path = $processing_dir . '/' . $new_image_name;
        $obj->addWatermark($image, $watermark_image, $new_image_path, $resize_watermark, $fullwidth_watermark, $fullheight_watermark, $transparency, $quality, $offsetX, $offsetY, $alignX, $alignY);
    }

    unset($image);

    $images2 = $obj->directories('images/' . $dirname . "/", "*.png");

    foreach ($images2 as $image)
    {
        $new_image_name = 'processed_image_' . substr($image, strrpos($image, '/') + 1);
        $new_image_path = $processing_dir . '/' . $new_image_name;
        $obj->addWatermark($image, $watermark_image, $new_image_path, $resize_watermark, $fullwidth_watermark, $fullheight_watermark, $transparency, $quality, $offsetX, $offsetY, $alignX, $alignY);
    }
    header("location:index.php");
    exit;
}
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
        <title>
            Image Watermark
        </title>      
        <link rel="stylesheet" href="css/bootstrap.css" type="text/css" />
        <script src="js/jquery.js"></script>
        <script src="js/bootstrap.js"></script>
        <style>
            .container{
                margin-top:50px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <form method="post" action="" enctype="multipart/form-data">
                        <div class="form-group col-md-6">
                            <label>
                                Select Watermark to be added:
                            </label>
                            <select  required="required" class="form-control" id="watermarks" name="watermarks">

                            </select>
                        </div>
                        <?php
                        if (count($image_dirs) > 0)
                        {
                            ?>
                            <div class="form-group col-md-6">
                                <label>
                                    Select Folder to process:
                                </label>
                                <select name="dir_name" id="dir_name" required="required" class="form-control">
                                    <?php
                                    foreach ($image_dirs as $id)
                                    {
                                        $nm = substr($id, strrpos($id, '/') + 1);
                                        ?>
                                        <option value="<?= $nm; ?>"><?= $nm; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="clearfix"><br/></div>
                        <div class="form-group col-md-6">
                            <label>
                                Right Left Margin:
                            </label>
                            <input type="number" name="offsetx" class="form-control" id="offsetx" required="required" value="0" />
                        </div>
                        <div class="form-group col-md-6">
                            <label>
                                Top Bottom Margin:
                            </label>
                            <input type="number" class="form-control" name="offsety" id="offsety" required="required" value="0" />
                        </div>
                        <div class="form-group col-md-6">
                            <label>
                                Image Quality:
                            </label>
                            <input type="number" class="form-control" max="100" name="quality" id="quality" required="required" value="90" />
                            <small>(Quality should be max 100)</small>
                        </div>

                        <div class="form-group col-md-6">
                            <label>
                                Photo-frame:
                            </label><br/>
                            <input type="checkbox" name="fframe" id="fframe" value="1" />
                        </div>
                        <div class="clearfix"></div>
                        <div class="form-group col-md-6">
                            <label>
                                Full Width watermark:
                            </label><br/>
                            <input checked="checked" type="checkbox" name="watermark_width" id="watermark_width" value="1" />
                        </div>
                        <div class="form-group col-md-6">
                            <label>
                                Full Height watermark:
                            </label><br/>
                            <input type="checkbox" name="watermark_height" id="watermark_height" value="1" />
                        </div>
                        <div class="clearfix"></div>
                        <div class="form-group col-md-6">
                            <label>
                                Image X position:
                            </label>
                            <select  required="required" class="form-control" id="alignx" name="alignx">
                                <option selected="selected" value="right">Right</option>
                                <option value="middle">Middle</option>
                                <option value="left">Left</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>
                                Image Y position:
                            </label>
                            <select  required="required" class="form-control" id="aligny" name="aligny">
                                <option selected="selected" value="bottom">Right</option>
                                <option value="middle">Middle</option>
                                <option value="top">Top</option>
                            </select>
                        </div>
                        <div class="form-group col-md-12">
                            <input type="submit" name="submit" class="btn btn-primary" value="Process Images" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
    <script type="text/javascript">//<![CDATA[
        $(window).load(function () {
            var fileExt = ".png";
            $(document).ready(function () {

                $.ajax({
                    //This will retrieve the contents of the folder if the folder is configured as 'browsable'
                    url: 'watermarks/',
                    success: function (data) {
                        console.log(data);
                        $(data).find("a:contains(" + fileExt + ")").each(function () {
                            $("#watermarks").append('<option value="' + $(this).text() + '" >' + $(this).text() + '</option>');
                        });
                    }
                });
            });
        });
//]]>
    </script>
</html>