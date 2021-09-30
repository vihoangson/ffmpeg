<?php
require 'vendor/autoload.php';
// document  https://packagist.org/packages/php-ffmpeg/php-ffmpeg
$ffmpeg = FFMpeg\FFMpeg::create();
$video = $ffmpeg->open('./output/out.mp4');
$video
    // ->save(new FFMpeg\Format\Video\X264(), 'export-x264.mp4')
    ->save(new FFMpeg\Format\Video\WMV(), 'export-wmv.wmv');
class PageConvert {

    private $return;

    function formatSizeUnits($bytes) {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    public function showReturn() {
        $files = scandir('./output');
        foreach ($files as $file) {
            if (preg_match('/\.(mp4|mp3|txt)$/', $file)) {
                $pathfile = './output/' . $file;
                $fileSize = $this->formatSizeUnits(filesize($pathfile));
                $fileMine = mime_content_type($pathfile);
                $filemtime  = date('y-m-d h:i:s',filemtime($pathfile));
                echo '<div><a href="' . $pathfile . '" download>' . $file . '(' . $fileSize . ') [' . $fileMine . '] ['.$filemtime.']</a></div>';
            }
        }
    }

    public function init() {

    }

    public function processConvertAudio() {
        if (isset($_POST['form_type']) && $_POST['form_type'] === 'convertAudio') {
            if ($_FILES) {
                $ffmeg = new Ffmpeg();
                $s     = microtime(true);
                var_dump($_FILES);
                $filename = str_replace([' ', '(', ')'], ['', '', ''], $_FILES['md4']['name']);
                move_uploaded_file($_FILES['md4']['tmp_name'], './input/' . $filename);
                $ffmeg->setSource('./input/' . $filename);
                $type_audio = $_POST['type_audio'];
                if (in_array($type_audio, ['mp3', 'wav'])) {
                    $ffmeg->convertAudio($type_audio);
                    $this->return = $ffmeg->return;
                } else {

                }
            }
        }
    }

    public function processAddToVideo() {
        if (isset($_POST['form_type']) && $_POST['form_type'] === 'addImgToVideo') {
            if ($_FILES) {
                $ffmeg      = new Ffmpeg();
                $inputAudio = './input/' . $_FILES['md4']['name'];
                move_uploaded_file($_FILES['md4']['tmp_name'], $inputAudio);
                $imgCover = './input/' . $_FILES['img']['name'];
                move_uploaded_file($_FILES['img']['tmp_name'], $imgCover);
                $ffmeg->setSource($inputAudio);
                $ffmeg->setImageCover($imgCover);
                $ffmeg->setImageToVideo();
                $this->return = $ffmeg->return;

            }

        }
    }

    public function processTrimAudio() {
        if (isset($_POST['form_type']) && $_POST['form_type'] === 'trimAudio') {
            if ($_FILES) {
                $ffmeg      = new Ffmpeg();
                $inputAudio = './input/' . $_FILES['md4']['name'];
                move_uploaded_file($_FILES['md4']['tmp_name'], $inputAudio);
                $ffmeg->setSource($inputAudio);
                $from = $_POST['from'];
                $to   = $_POST['to'];
                $ffmeg->trimAudio($from, $to);
                $this->return = $ffmeg->return;

            }
        }
    }
}

class Ffmpeg {

    private $source;
    private $imageCover;
    /**
     * @var array|string|string[]
     */
    private $pathInfoSource;
    public  $return;

    public function __construct() {
        !is_dir('input') ? mkdir('input') : null;
        !is_dir('output') ? mkdir('output') : null;

    }

    public function convertAudio($type = 'mp3') {
        if ($type === $this->pathInfoSource['extension']) {
            throw new Exception('Dont change extension');
        }

        $filename  = './output/' . $this->pathInfoSource['filename'];
        $extension = $type;
        $output    = exec("C:/bin/ffmpeg  -i $this->source -y  -codec:a libmp3lame -b:a 128k $filename.$extension 2>&1", $output1, $code);
        if ($code === 0) {
            $this->return = ['audio' => ['path' => $filename . '.' . $extension]];
        } else {
            echo 'error';
            var_dump($output1);
        }
    }

    public function setImageToVideo() {
        $fileSource                    = $this->source;
        $fileOutput                    = './output/' . $this->pathInfoSource['filename'] . '.mp4';
        $coverImg                      = $this->imageCover;
        $output                        = exec("C:/bin/ffmpeg -hide_banner -loop 1 -i $coverImg -i  $fileSource  -c:v libx264 -preset ultrafast -tune stillimage -c:a copy -pix_fmt yuv420p -shortest -y -vf scale=720:-2 $fileOutput 2>&1");
        $this->return['video']['path'] = $fileOutput;
    }

    public function setSource($string) {
        if (file_exists($string)) {
            $this->source         = $string;
            $this->pathInfoSource = pathinfo($string);
        } else {
            throw new Exception('do not have source');
        }
    }

    public function setImageCover($string) {
        if (file_exists($string)) {
            $this->imageCover = $string;
        }
    }

    public function trimAudio($string, $string1) {
        $pathinfo = pathinfo($this->source);
        $ext      = $pathinfo['extension'];
        $filename = $pathinfo['filename'];

        $stringName   = str_replace(':', '-', $string);
        $string1Name  = str_replace(':', '-', $string1);
        $fileNew      = './output/' . $filename . '_' . $stringName . '_' . $string1Name . '.' . $ext;
        $output       = exec("C:/bin/ffmpeg -i $this->source -ss $string -to $string1 $fileNew  2>&1");
        $this->return = ['audio' => ['path' => $fileNew]];
    }

}

$pageConvert = new PageConvert();
$pageConvert->init();
$pageConvert->processConvertAudio();
$pageConvert->processAddToVideo();
$pageConvert->processTrimAudio();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Convert Audio</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="bootstrap.min.css">
</head>
<body>
<div class="container">
    <?php $pageConvert->showReturn(); ?>
    <div role="tabpanel" id="myTab">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#home" aria-controls="home" role="tab" data-toggle="tab">Convert audio</a>
            </li>
            <li role="presentation">
                <a href="#tab" aria-controls="tab" role="tab" data-toggle="tab">Add Img to Video</a>
            </li>
            <li role="presentation">
                <a href="#tab2" aria-controls="tab" role="tab" data-toggle="tab">Trim audio</a>
            </li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="home">
                <h1>Convert Audio</h1>
                <form action="" method="POST" class="form-inline" role="form" enctype='multipart/form-data'>
                    <div class="form-group">
                        <label class="sr-only" for="">label</label>
                        <input name="md4" type="file" class="form-control" id="" placeholder="Input field"
                               accept="audio/mp3,audio/ogg">
                    </div>
                    <div>
                        <div class="form-group">
                            <div class="radio">
                                <label>
                                    <input type="radio" name="type_audio" id="input" value="mp3" checked>
                                    mp3
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="type_audio" id="input" value="wav">
                                    wav
                                </label>
                            </div>

                        </div>
                    </div>
                    <button name="form_type" value="convertAudio" type="submit" class="btn btn-primary">Submit</button>
                </form>
                <?php
                if (isset($ffmeg)) {
                    if (isset($ffmeg->return['audio']['path'])) {
                        echo '<a href="' . $ffmeg->return['audio']['path'] . '" download>Download output</a>';
                    }
                }
                ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab">
                <h1>Convert Video</h1>
                <form action="" method="POST" class="form-inline" role="form" enctype='multipart/form-data'>
                    <div class="form-group">
                        <label class="" for="">Audio</label>
                        <input name="md4" type="file" class="form-control" id="" placeholder="Input field"
                               accept="audio/mp3">
                    </div>
                    <div class="form-group">
                        <label class="" for="">Img</label>
                        <input name="img" type="file" class="form-control" id="" placeholder="Input field"
                               accept="image/png,image/jpg">
                    </div>

                    <button name="form_type" value="addImgToVideo" type="submit" class="btn btn-primary">Submit</button>
                </form>

                <?php
                if (isset($ffmeg)) {
                    if (isset($ffmeg->return['video']['path'])) {
                        ?>
                        <video width="320" height="240" controls>
                            <source src='<?=$ffmeg->return['video']['path']?>' type="video/mp4">
                        </video>
                        <?php
                        echo '<div><a href="' . $ffmeg->return['video']['path'] . '" download>Download output</a></div>';
                    }
                }
                ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab2">
                <h1>Trim Audio</h1>
                <form action="" method="POST" class="form-inline" role="form" enctype='multipart/form-data'>
                    <div class="form-group">
                        <label class="sr-only" for="">label</label>
                        <input name="md4" type="file" class="form-control" id="" placeholder="Input field"
                               accept="audio/mp3">

                    </div>
                    <div class="form-group">
                        <label class="" for="">From</label>
                        <input name="from" type="text" class="form-control" id="" value="00:00:00"
                               placeholder="00:00:00">
                    </div>
                    <div class="form-group">
                        <label class="" for="">To</label>
                        <input name="to" type="text" class="form-control" id="" value="00:00:10" placeholder="00:00:10">
                    </div>

                    <button name="form_type" value="trimAudio" type="submit" class="btn btn-primary">Submit</button>
                </form>
                <?php
                if (isset($ffmeg)) {
                    if (isset($ffmeg->return['audio']['path'])) {
                        echo '<a href="' . $ffmeg->return['audio']['path'] . '" download>Download output</a>';
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>
<script src="jquery.js"></script>
<script src="bootstrap.min.js"></script>
<script>
    $('#myTabs a').click(function (e) {
        e.preventDefault()
        $(this).tab('show')
    })
</script>
</body>
</html>
