# Path extractor

Package, which extracts paths and attributes from the image, anchor and other tags of the provided html.

### Installation

```bash
composer require sebastiansulinski/path-extractor
```

### Basic usage

#### Instantiating

You can instantiate `Extractor` either by using `new` keyword or static `make` method.
Constructor takes and optional argument, which represents the string to be parsed.

```php
use SSD\PathExtractor\Extractor;


$extractor = new Extractor;

$extractor = new Extractor($html);

$extractor = Extractor::make();

$extractor = Extractor::make($html);
```

#### Specifying input html

Apart from being able to pass your string via constructor, you can also use the `Extractor::for` method to set it on the instance.

```php
$extractor = new Extractor;
$extractor->for($html);
```

#### Extracting images

To extract all images use the `Extractor::extract(Image::class)` method.

```php
use \SSD\PathExtractor\Tags\Image;

$html = '<img src="/media/image.jpg" alt="My image">';
$html = .'<img src="/media/image2.png" alt="My image 2">';

$images = Extractor::make($html)->extract(Image::class);
```

The above will return array containing the collection of `\SSD\PathExtractor\Tags\Image` class instances with properties `src` and `alt` available.

#### Extracting anchors

To extract all anchors use the `Extractor::extract(Anchor::class)` method.

```php
use \SSD\PathExtractor\Tags\Anchor;

$html = '<a href="/media/files/one.pdf" target="_blank">Document one</a>';
$html = .'<a href="/media/files/two.docx" title="Word document">Word document</a>';

$anchors = Extractor::make($html)->extract(Anchor::class);
```

The above will return array containing the collection of `\SSD\PathExtractor\Tags\Anchor` class instances with properties `href`, `target`, `title` and `nodeValue` available.

#### Extracting scripts

To extract all anchors use the `Extractor::extract(Script::class)` method.

```php
use \SSD\PathExtractor\Tags\Script;

$html = '<script src="/media/script/one.js" async></script>';
$html = .'<script src="/media/script/two.js" async defer></script>';
$html = .'<script src="/media/script/three.js"></script>';

$scripts = Extractor::make($html)->extract(Script::class);
```

The above will return array containing the collection of `\SSD\PathExtractor\Tags\Script` class instances with properties `src`, `async`, and `defer` available - last two with boolean `true` / `false` set based on whether they are present or not.

#### Limiting extensions

Sometimes you might want to only extract images or anchors with certain extensions.
To do this use the `Extractor::withExtensions()` method and pass the required extensions as argument.

```php
$images = Extractor::make($html)->withExtensions('jpg')->extract(Image::class);
$anchors = Extractor::make($html)->withExtensions(['pdf', 'docx'])->extract(Anchor::class);
$anchors = Extractor::make($html)->withExtensions('pdf', 'docx')->extract(Anchor::class);
```

#### Pre-pending url

Sometimes you might wish to prepend the protocol, domain name and even a port to the relative paths extracted from your html.
To do this, use the `Extractor::withUrl()` method.

```php
$html = '<img src="/media/image.jpg" alt="My image">';
$html .= '<img src="https://ssdtutorials.com/media/image2.jpg" alt="My image 2">';

$images = Extractor::make($html)->withUrl('https://mywebsite.com')->extract(Image::class);
```

The above will return an array containing two instances of `\SSD\PathExtractor\Tags\Image` - one with `src` set to `https://mywebsite.com/media/image.jpg` and the other to `https://ssdtutorials.com/media/image2.jpg`. **Please note** - it will not replace the paths which already contain protocol and domain.

#### Tidying / purifying input

If you'd like your input to first undergo the purification, you can use the `Extractor::withTidy()` method.
This method takes 2 optional arguments: `array $config = []`, which allows you to overwrite default `tidy` extension configuration as well as `string $encoding = 'utf8'` should you need to change the encoding.

By default config is set to

```php
[
    'clean' => 'yes',
    'output-html' => 'yes',
    'wrap' => 0,
]
```

More on config options at [HTML Tidy Configuration Options](http://tidy.sourceforge.net/docs/quickref.html).

#### Invalid input exception

If you decide NOT to use `tidy` to purify your input, where for instance you will do this before passing the html to the constructor or `for` method and if the provided html contains invalid syntax, the `\SSD\PathExtractor\InvalidHtmlException` will be thrown - so make sure you catch it and act accordingly.

#### 

#### Accessing attributes of the `\SSD\PathExtractor\Tags\Tag` class instance.

Each implementation of `\SSD\PathExtractor\Tags\Tag` will have their own, unique set of properties available

```php
\SSD\PathExtractor\Tags\Anchor

- href
- target
- title
- rel
- nodeValue (represents text in between opening and closing a tag)

\SSD\PathExtractor\Tags\Image

- src
- alt
- width
- height

\SSD\PathExtractor\Tags\Script

- src
- type
- charset
- async
- defer

\SSD\PathExtractor\Tags\Link

- href
- type
- rel
```

#### Rendering tag for `\SSD\PathExtractor\Tags\Tag` class instance.

Once you have extracted the collection of resources, you can then return an html tag for each one by simply casting it to string or by calling the `tag()` method on it.

```php
$html = '<img src="/media/image.jpg" alt="My image">';
$html = .'<img src="/media/image2.png" alt="My image 2">';

$tag1 = (string)Extractor::make($html)->withExtensions('jpg')->extract(Image::class)[0];
$tag2 = Extractor::make($html)->withExtensions('jpg')->extract(Image::class)[0]->tag();
``` 

Both of the above will return

```php
<img src="/media/image.jpg" alt="My image">
```

You can also obtain array representation of each instance by calling `Tag::toArray()` method on it

```php
Extractor::make($html)->withExtensions('jpg')->extract(Image::class)[0]->toArray()
```

#### Adding more tag types

If you need more tag types i.e. `link` - simply add new class that extends `\SSD\PathExtractor\Tags\Tag` and implement the abstract methods required by it.

```php

use SSD\PathExtractor\Tags\Tag;
use SSD\PathExtractor\Tags\Type;

class Link extends Tag
{
    /**
     * Get tag name.
     *
     * @return string
     */
    static public function tagName(): string
    {
        return 'link';
    }

    /**
     * Get path attribute.
     *
     * @return string
     */
    static public function pathAttribute(): string
    {
        return 'href';
    }

    /**
     * Get available attributes.
     *
     * @return array
     */
    static public function availableAttributes(): array
    {
        return [
            'href' => Type::STRING,
            'type' => Type::STRING,
            'rel' => Type::STRING,
        ];
    }

    /**
     * Get formatted tag.
     *
     * @return string
     */
    public function tag(): string
    {
        return '<link'.$this->tagAttributes('href', 'type', 'rel').'>';
    }
}
```

#### Example of extracting only paths

```php
$string = '<img src="/media/image/one.jpg" alt="Image one">';
$string .= '<img src="https://mysite.com/media/image/two.jpg" alt="Image two">';
$string .= '<a href="/media/files/two.pdf" '.
    'target="_blank" title="Document">Document</a>';
$string .= '<script src="/media/script/three.js" async></script>';
$string .= '<link href="/media/link/three.css" rel="stylesheet">';

$extractor = Extractor::make($string);


$images = array_map(function (Tag $tag) {
    return $tag->path();
}, $extractor->extract(Image::class));

$anchors = array_map(function (Tag $tag) {
    return $tag->path();
}, $extractor->extract(Anchor::class));


$scripts = array_map(function (Tag $tag) {
    return $tag->path();
}, $extractor->extract(Script::class));

$links = array_map(function (Tag $tag) {
    return $tag->path();
}, $extractor->extract(Link::class));


$this->assertEquals([
    '/media/image/one.jpg',
    'https://mysite.com/media/image/two.jpg',
    '/media/files/two.pdf',
    '/media/script/three.js',
    '/media/link/three.css',
], array_merge($images, $anchors, $scripts, $links));
```