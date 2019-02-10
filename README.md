# Image library

This library is provided for resize, watermark images on fly.
Works with php 5.0 or higher.

Result images will be cached, so converting is made only one time,
if source or converted images are not modified.

This is legacy variant without namespaces for compatibility to older php versions.

## Requirements

- PHP 5.0+
- [GD extension](http://php.net/manual/en/book.image.php)

## Installation

```
composer require sivka/img-5
```

Or [Download](https://github.com/alexSivka/Img-5/releases) zip package and unzip anywhere to your project.

### Example

```php
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

//without setting cache directory
$src = Img::get('path/to/file.jpg', $params));

$src = Img::get('path/to/file.jpg', array('width' => 100, 'height' => 150)));
//returns /path/to/thumbs/100x150/file.jpg

//or simple
$src = Img::get('path/to/file.jpg', $width, [$height, [$crop]]);
$src = Img::get('path/to/file.jpg', 100, 100, true);
//returns /path/to/thumbs/100x100cp/file.jpg

//with setting cache directory
Img::setCacheDirectory('cache');
$src = Img::get('path/to/file.jpg', 100, 100, true);
//returns /cache/path/to/thumbs/100x100cp/file.jpg
```

### Parameters

```
number width - with to resize
number height - height to resize
mixed crop - boolean or number(1/0) or string with crop position center|top|left|//bottom|right|top left|top right|bottom left|bottom right

string watermark - path to watermark file_ext

string wm_position - watermark position center|top|left|bottom|right|top|left|top right|bottom left|bottom right

float wm_opacity - opacity of watermar or watermark text

number quality - quality for result images

string wm_text - text for overlay on images

mixed wm_text_color - color of watermark text, maybe string('#FFFFFF') 
or array ['r'=>255,'g'=>255, 'b'=> 255, 'a'=>1] or array [255, 255, 255, 1]

number wm_text_size - font size of watermark text

string wm_text_font - name of font for watermark text, the font file mast be in same directory with Img.php

string default - path to placeholder, if defined and source image does not exists, this file will be converted

```

### methods

```php
static get($filepath, $params);
static get($filepath, $width, [$height, [$crop]]);
convert image and returns relative path to converted images

static setCacheDirectory($path);
sets path to cache directory

static setDocRoot($docRoot);
set DOCUMENT_ROOT, use this if $_SERVER['DOCUMENT_ROOT'] is not real absolute path to home directory

static setPlaceholder($filepath);
set path to placeholder image. If source image does not exists, path to placeholder will be returned.
```


## Built With

* [Cory LaViska SimpleImage](https://github.com/claviska/SimpleImage)

## Authors

* **AlexSivka** - *Initial work* - [AlexSivka](https://github.com/alexSivka/)

## License

This project is licensed under the [MIT License](LICENSE.md)
