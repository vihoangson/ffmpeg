<?php

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
        $fileSource = $this->source;
        $fileOutput = './output/' . $this->pathInfoSource['filename'] . '.mp4';
        $coverImg   = $this->imageCover;
        $output     = exec("C:/bin/ffmpeg -hide_banner -loop 1 -i $coverImg -i  $fileSource  -c:v libx264 -preset ultrafast -tune stillimage -c:a copy -pix_fmt yuv420p -shortest -y -vf scale=720:-2 $fileOutput 2>&1");
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

        $stringName  = str_replace(':', '-', $string);
        $string1Name = str_replace(':', '-', $string1);
        $fileNew     = './output/'.$filename . '_' . $stringName . '_' . $string1Name . '.' . $ext;
        $output      = exec("C:/bin/ffmpeg -i $this->source -ss $string -to $string1 $fileNew  2>&1");
        $this->return = ['audio' => ['path' => $fileNew]];
    }

}

if (isset($_POST['form_type']) && $_POST['form_type'] === 'convertAudio') {
    if ($_FILES) {
        $ffmeg = new Ffmpeg();
        $s     = microtime(true);
        var_dump($_FILES);
        $filename = str_replace([' ', '(', ')'], ['', '', ''], $_FILES['md4']['name']);
        move_uploaded_file($_FILES['md4']['tmp_name'], './input/' . $filename);
        sleep(1);
        $ffmeg->setSource('./input/' . $filename);
        $type_audio = $_POST['type_audio'];
        if (in_array($type_audio, ['mp3', 'wav'])) {
            $ffmeg->convertAudio($type_audio);
        } else {

        }
        // $ffmeg->setImageCover('./cover.jpg');
        // $ffmeg->convertAudio();
        // $ffmeg->setImageToVideo();
        // $ffmeg->trimAudio('00:00:00','00:00:02');
    }
}
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
    }

}
if (isset($_POST['form_type']) && $_POST['form_type'] === 'trimAudio') {
    if ($_FILES) {
        $ffmeg      = new Ffmpeg();
        $inputAudio = './input/' . $_FILES['md4']['name'];
        move_uploaded_file($_FILES['md4']['tmp_name'], $inputAudio);
        $ffmeg->setSource($inputAudio);
        $from = $_POST['from'];
        $to   = $_POST['to'];
        $ffmeg->trimAudio($from, $to);
    }

}
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
    <div class="row">
        <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
            <h1>Convert Audio</h1>
            <form action="" method="POST" class="form-inline" role="form" enctype='multipart/form-data'>
                <div class="form-group">
                    <label class="sr-only" for="">label</label>
                    <input name="md4" type="file" class="form-control" id="" placeholder="Input field">
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
        <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
            <h1>Convert Video</h1>
            <form action="" method="POST" class="form-inline" role="form" enctype='multipart/form-data'>
                <div class="form-group">
                    <label class="" for="">Audio</label>
                    <input name="md4" type="file" class="form-control" id="" placeholder="Input field">
                </div>
                <div class="form-group">
                    <label class="" for="">Img</label>
                    <input name="img" type="file" class="form-control" id="" placeholder="Input field">
                </div>

                <button name="form_type" value="addImgToVideo" type="submit" class="btn btn-primary">Submit</button>
            </form>

            <?php
            if (isset($ffmeg)) {
                if (isset($ffmeg->return['video']['path'])) {
                    ?>
                    <video width="320" height="240" controls>
                        <source src = '<?= $ffmeg->return['video']['path'] ?>' type="video/mp4">
                    </video>
                    <?php
                    echo '<div><a href="' . $ffmeg->return['video']['path'] . '" download>Download output</a></div>';
                }
            }
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
            <h1>Trim Audio</h1>
            <form action="" method="POST" class="form-inline" role="form" enctype='multipart/form-data'>
                <div class="form-group">
                    <label class="sr-only" for="">label</label>
                    <input name="md4" type="file" class="form-control" id="" placeholder="Input field">

                </div>
                <div class="form-group">
                    <label class="" for="">From</label>
                    <input name="from" type="text" class="form-control" id="" placeholder="Input field">
                </div>
                <div class="form-group">
                    <label class="" for="">To</label>
                    <input name="to" type="text" class="form-control" id="" placeholder="Input field">
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
        <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">

        </div>
    </div>

</div>


</body>
</html>
