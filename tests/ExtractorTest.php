<?php

namespace SSDTest;

use PHPUnit\Framework\TestCase;
use SSD\PathExtractor\Tags\Tag;
use SSD\PathExtractor\Tags\Link;
use SSD\PathExtractor\Extractor;
use SSD\PathExtractor\Tags\Image;
use SSD\PathExtractor\Tags\Anchor;
use SSD\PathExtractor\Tags\Script;
use SSD\PathExtractor\InvalidHtmlException;

class ExtractorTest extends TestCase
{
	/**
	 * @test
	 * @throws \SSD\PathExtractor\InvalidHtmlException
	 */
    public function extracts_image_paths(): void
    {
        $string = '<img src="/media/image/one.jpg" alt="Image one">';
        $string .= '<p>Some</p>';
        $string .= '<img src="/media/image/two.jpeg" alt="Image two" width="100%">';
        $string .= '<img src="/media/image/three.svg" alt="Image three">';

        $extractor = new Extractor($string);

        $this->assertEquals(
            [
                new Image([
                    'src' => '/media/image/one.jpg',
                    'alt' => 'Image one',
                    'width' => null,
                    'height' => null
                ]),
                new Image([
                    'src' => '/media/image/two.jpeg',
                    'alt' => 'Image two',
                    'width' => '100%',
                    'height' => null
                ]),
                new Image([
                    'src' => '/media/image/three.svg',
                    'alt' => 'Image three',
                    'width' => null,
                    'height' => null
                ]),
            ],
            $extractor->extract(Image::class)
        );

        $this->assertEquals(
            [
                new Image([
                    'src' => '/media/image/three.svg',
                    'alt' => 'Image three',
                    'width' => null,
                    'height' => null
                ]),
            ],
            $extractor->withExtensions('svg')->extract(Image::class)
        );
    }

    /**
     * @test
     */
    public function returns_image_tag(): void
    {
        $string = '<img src="/media/image/one.jpg" alt="Image one">';
        $string .= '<p>Some</p>';
        $string .= '<img src="/media/image/two.jpeg" alt="Image two">';
        $string .= '<img src="/media/image/three.svg" alt="Image three">';

        $this->assertEquals(
            '<img src="/media/image/three.svg" alt="Image three" />',
            (string)Extractor::make($string)->withExtensions('svg')->extract(Image::class)[0]
        );
    }

    /**
     * @test
     */
    public function extracts_script_paths(): void
    {
        $string = '<script src="/media/script/one.js"></script>';
        $string .= '<p>Some</p>';
        $string .= '<script src="/media/script/two.js" type="text/javascript" async defer></script>';
        $string .= '<script src="/media/script/three.js" async></script>';

        $this->assertEquals(
            [
                new Script([
                    'src' => '/media/script/one.js',
                    'async' => false,
                    'defer' => false,
                    'type' => null,
                    'charset' => null
                ]),
                new Script([
                    'src' => '/media/script/two.js',
                    'async' => true,
                    'defer' => true,
                    'type' => 'text/javascript',
                    'charset' => null
                ]),
                new Script([
                    'src' => '/media/script/three.js',
                    'async' => true,
                    'defer' => false,
                    'type' => null,
                    'charset' => null
                ]),
            ],
            Extractor::make($string)->extract(Script::class)
        );
    }

    /**
     * @test
     */
    public function returns_script_tag(): void
    {
        $string = '<script src="/media/script/one.js"></script>';
        $string .= '<p>Some</p>';
        $string .= '<script src="/media/script/two.js" async defer></script>';
        $string .= '<script src="/media/script/three.js" async></script>';

        $this->assertEquals(
            '<script src="/media/script/two.js" async defer></script>',
            (string)Extractor::make($string)->extract(Script::class)[1]
        );
    }

	/**
	 * @test
	 * @throws \SSD\PathExtractor\InvalidHtmlException
	 */
    public function extracts_anchors(): void
    {
        $string = '<a href="/media/files/one.pdf" target="_blank">Document one</a>';
        $string .= '<p>Some</p>';
        $string .= '<a href="/media/files/two.docx" title="Word document" rel="nofollow">Word document</a>';
        $string .= '<a href="/media/files/three.pdf">Document three</a>';
        $string .= '<a href="/four">Page link</a>';

        $extractor = new Extractor($string);

        $this->assertEquals(
            [
                new Anchor([
                    'href' => '/media/files/one.pdf',
                    'target' => '_blank',
                    'title' => null,
                    'rel' => null,
                    'nodeValue' => 'Document one',
                ]),
                new Anchor([
                    'href' => '/media/files/two.docx',
                    'target' => null,
                    'title' => 'Word document',
                    'rel' => 'nofollow',
                    'nodeValue' => 'Word document',
                ]),
                new Anchor([
                    'href' => '/media/files/three.pdf',
                    'target' => null,
                    'title' => null,
                    'rel' => null,
                    'nodeValue' => 'Document three',
                ]),
                new Anchor([
                    'href' => '/four',
                    'target' => null,
                    'title' => null,
                    'rel' => null,
                    'nodeValue' => 'Page link',
                ]),
            ],
            $extractor->extract(Anchor::class)
        );

        $this->assertEquals(
            [
                new Anchor([
                    'href' => '/media/files/one.pdf',
                    'target' => '_blank',
                    'title' => null,
                    'rel' => null,
                    'nodeValue' => 'Document one',
                ]),
                new Anchor([
                    'href' => '/media/files/three.pdf',
                    'target' => null,
                    'title' => null,
                    'rel' => null,
                    'nodeValue' => 'Document three',
                ]),
            ],
            $extractor->withExtensions('pdf')->extract(Anchor::class)
        );

        $this->assertEquals(
            [
                new Anchor([
                    'href' => '/media/files/two.docx',
                    'target' => null,
                    'title' => 'Word document',
                    'rel' => 'nofollow',
                    'nodeValue' => 'Word document',
                ]),
            ],
            $extractor->withExtensions('docx')->extract(Anchor::class)
        );
    }

    /**
     * @test
     */
    public function returns_anchor_tag(): void
    {
        $string = '<a href="/media/files/one.pdf" '.
            'target="_blank" title="Document">Document</a>';

        $this->assertEquals(
            $string,
            (string)Extractor::make($string)->extract(Anchor::class)[0]
        );
    }

	/**
	 * @test
	 * @throws \SSD\PathExtractor\InvalidHtmlException
	 */
    public function prepends_url_if_not_present(): void
    {
        $string = '<img src="/media/image/one.jpg" alt="Image one">';
        $string .= '<img src="https://mysite.com/media/image/two.jpg" alt="Image two">';
        $string .= '<a href="/media/files/two.pdf" '.
            'target="_blank" title="Document">Document</a>';
        $string .= '<script src="/media/script/three.js" async></script>';

        $extractor = Extractor::make($string)->withTidy()->withUrl('https://demo.com');

        $this->assertEquals(
            [
                new Image([
                    'src' => 'https://demo.com/media/image/one.jpg',
                    'alt' => 'Image one',
                    'width' => null,
                    'height' => null
                ]),
                new Image([
                    'src' => 'https://mysite.com/media/image/two.jpg',
                    'alt' => 'Image two',
                    'width' => null,
                    'height' => null
                ]),
            ],
            $extractor->extract(Image::class)
        );

        $this->assertEquals(
            [
                new Anchor([
                    'href' => 'https://demo.com/media/files/two.pdf',
                    'target' => '_blank',
                    'title' => 'Document',
                    'rel' => null,
                    'nodeValue' => 'Document',
                ]),
            ],
            $extractor->extract(Anchor::class)
        );

        $this->assertEquals(
            [
                new Script([
                    'src' => 'https://demo.com/media/script/three.js',
                    'async' => true,
                    'defer' => false,
                    'type' => null,
                    'charset' => null
                ]),
            ],
            $extractor->extract(Script::class)
        );
    }

    /**
     * @test
     */
    public function throws_exception_with_invalid_input_and_purify_set_to_false(): void
    {
        $string = '<body<img src="/media/image/one.jpg" alt="Image one">';
        $string .= '<img src="https://mysite.com/media/image/two.jpg" alt="Image two">';

        $this->expectException(InvalidHtmlException::class);

        Extractor::make($string)->extract(Image::class);
    }

    /**
     * @test
     */
    public function does_not_throw_exception_with_invalid_input_and_purify_set_to_true_but_omits_tags_nearby_unclosed_tags(): void
    {
        $string = '<body<img src="/media/image/one.jpg" alt="Image one">';
        $string .= '<img src="https://mysite.com/media/image/two.jpg" alt="Image two">';

        $this->assertEquals(
            [
                new Image([
                    'src' => 'https://mysite.com/media/image/two.jpg',
                    'alt' => 'Image two',
                    'width' => null,
                    'height' => null
                ]),
            ],
            Extractor::make($string)->withTidy()->extract(Image::class)
        );
    }

	/**
	 * @test
	 * @throws \SSD\PathExtractor\InvalidHtmlException
	 */
    public function does_not_throw_exception_with_invalid_input_and_purify_set_to_true_and_does_not_omit_tags_nearby_closed_tags(): void
    {
        $string = '<body><img src="/media/image/one.jpg" alt="Image one">';
        $string .= '<p><img src="https://mysite.com/media/image/two.jpg" alt="Image two">';

        $this->assertEquals(
            [
                new Image([
                    'src' => '/media/image/one.jpg',
                    'alt' => 'Image one',
                    'width' => null,
                    'height' => null
                ]),
                new Image([
                    'src' => 'https://mysite.com/media/image/two.jpg',
                    'alt' => 'Image two',
                    'width' => null,
                    'height' => null
                ]),
            ],
            Extractor::make($string)->withTidy()->extract(Image::class)
        );
    }

	/**
	 * @test
	 * @throws \SSD\PathExtractor\InvalidHtmlException
	 */
    public function returns_empty_array_with_empty_body(): void
    {
        $this->assertEquals(
            [],
            Extractor::make()->extract(Image::class)
        );

        $this->assertEquals(
            [],
            Extractor::make()->for('')->extract(Image::class)
        );

        $this->assertEquals(
            [],
            Extractor::make()->withTidy()->extract(Image::class)
        );

        $this->assertEquals(
            [],
            Extractor::make()->for('')->withTidy()->extract(Image::class)
        );
    }

	/**
	 * @test
	 * @throws \SSD\PathExtractor\InvalidHtmlException
	 */
    public function removes_records_with_empty_paths(): void
    {
        $string = '<img src="" alt="Image one">';
        $string .= '<p>Some</p>';
        $string .= '<img src="/media/image/two.jpeg" alt="Image two">';
        $string .= '<img src="" alt="Image three">';

        $extractor = new Extractor($string);

        $extractor->extract(Image::class);

        $this->assertEquals(
            [
                new Image([
                    'src' => '/media/image/two.jpeg',
                    'alt' => 'Image two',
                    'width' => null,
                    'height' => null
                ]),
            ],
            $extractor->extract(Image::class)
        );
    }

	/**
	 * @test
	 * @throws \SSD\PathExtractor\InvalidHtmlException
	 */
    public function returns_array_representation_of_the_tag(): void
    {
        $string = '<img src="/media/image/one.jpg" alt="Image one">';
        $string .= '<img src="https://mysite.com/media/image/two.jpg" alt="Image two">';
        $string .= '<a href="/media/files/two.pdf" '.
            'target="_blank" title="Document">Document</a>';
        $string .= '<script src="/media/script/three.js" async></script>';

        $extractor = Extractor::make($string);


        $images = array_map(function (Tag $tag) {
            return $tag->toArray();
        }, $extractor->extract(Image::class));

        $this->assertEquals(
            [
                [
                    'src' => '/media/image/one.jpg',
                    'alt' => 'Image one',
                    'width' => null,
                    'height' => null
                ],
                [
                    'src' => 'https://mysite.com/media/image/two.jpg',
                    'alt' => 'Image two',
                    'width' => null,
                    'height' => null
                ],
            ],
            $images
        );


        $anchors = array_map(function (Tag $tag) {
            return $tag->toArray();
        }, $extractor->extract(Anchor::class));

        $this->assertEquals(
            [
                [
                    'href' => '/media/files/two.pdf',
                    'target' => '_blank',
                    'title' => 'Document',
                    'rel' => null,
                    'nodeValue' => 'Document',
                ],
            ],
            $anchors
        );


        $scripts = array_map(function (Tag $tag) {
            return $tag->toArray();
        }, $extractor->extract(Script::class));

        $this->assertEquals(
            [
                [
                    'src' => '/media/script/three.js',
                    'async' => true,
                    'defer' => false,
                    'type' => null,
                    'charset' => null
                ],
            ],
            $scripts
        );
    }

	/**
	 * @test
	 * @throws \SSD\PathExtractor\InvalidHtmlException
	 */
    public function extracting_paths_only(): void
    {
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
    }
}