# Image library

This library is provided for resize, watermark images on fly.
Works with php 5.0 or higher.

Result images will be cached, so converting is made only one time,
if source or converted images are not modified.

## Requirements

- PHP 5.0+
- [GD extension](http://php.net/manual/en/book.image.php)

## Getting Started

Download zip package and unzip anywhere to your project.

### Example

```
$params = array(
    'width' => 100,
    'height' => 150,
    'crop' => false,
    'watermark' => '',
    'wm_position' => 'center',
    'wm_opacity' => 0.6,
    'quality' => 80,
    'wm_text' => '',
    'wm_text_color' => '#FFFFFF',
    'wm_text_size' => 64,
    'wm_text_font' => 'arial.ttf',
    'default' => ''
);
require_once 'Img/Img.php';
$src = Img::get('path/to/file.jpg', $params));
or simple
$src = Img::get('path/to/file.jpg', $width, [$height, [$crop]]);
```

### Parameters

```
number width - with to resize

number height - height to resize

mixed crop - boolean or number(1/0) or string with crop position        center|top|left|bottom|right|top left|top right|bottom left|bottom right

string watermark - path to watermark file_ext

string wm_position - watermark position center|top|left|bottom|right|top left|top right|bottom left|bottom right

float wm_opacity - opacity of watermar or watermark text

number quality - quality for result images

string wm_text - text for overlay on images

mixed wm_text_color - color of watermark text,
							maybe string('#FFFFFF') or array ['r'=>255,'g'=>255, 'b'=> 255, 'a'=>1] or array [255, 255, 255, 1]

number wm_text_size - font size of watermark text

string wm_text_font - name of font for watermark text, the font file mast be in same directory with Img.php

string default - path to placeholder, if defined and source image does not exists, this file will be converted

```


## Built With

* [Cory LaViska SimpleImage](https://github.com/claviska/SimpleImage)

## Authors

* **AlexSivka** - *Initial work* - [AlexSivka](https://github.com/alexSivka/)

## License

This project is licensed under the MIT License
