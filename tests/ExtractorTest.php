<?php

namespace SSDTest;

use PHPUnit\Framework\TestCase;
use SSD\PathExtractor\Extractor;
use SSD\PathExtractor\Tags\Image;
use SSD\PathExtractor\Tags\Anchor;
use SSD\PathExtractor\Tags\Script;
use SSD\PathExtractor\InvalidHtmlException;

class ExtractorTest extends TestCase
{
    /**
     * @test
     */
    public function extracts_image_paths()
    {
        $string = '<img src="/media/image/one.jpg" alt="Image one">';
        $string .= '<p>Some</p>';
        $string .= '<img src="/media/image/two.jpeg" alt="Image two">';
        $string .= '<img src="/media/image/three.svg" alt="Image three">';

        $extractor = new Extractor($string);

        $this->assertEquals(
            [
                new Image([
                    'src' => '/media/image/one.jpg',
                    'alt' => 'Image one',
                ]),
                new Image([
                    'src' => '/media/image/two.jpeg',
                    'alt' => 'Image two',
                ]),
                new Image([
                    'src' => '/media/image/three.svg',
                    'alt' => 'Image three',
                ]),
            ],
            $extractor->img()
        );

        $this->assertEquals(
            [
                new Image([
                    'src' => '/media/image/three.svg',
                    'alt' => 'Image three',
                ]),
            ],
            $extractor->img(['svg'])
        );
    }

    /**
     * @test
     */
    public function returns_image_tag()
    {
        $string = '<img src="/media/image/one.jpg" alt="Image one">';
        $string .= '<p>Some</p>';
        $string .= '<img src="/media/image/two.jpeg" alt="Image two">';
        $string .= '<img src="/media/image/three.svg" alt="Image three">';

        $this->assertEquals(
            '<img src="/media/image/three.svg" alt="Image three" />',
            (string)(new Extractor($string))->img(['svg'])[0]
        );
    }

    /**
     * @test
     */
    public function extracts_script_paths()
    {
        $string = '<script src="/media/script/one.js"></script>';
        $string .= '<p>Some</p>';
        $string .= '<script src="/media/script/two.js" async defer></script>';
        $string .= '<script src="/media/script/three.js" async></script>';

        $this->assertEquals(
            [
                new Script([
                    'src' => '/media/script/one.js',
                    'async' => false,
                    'defer' => false,
                ]),
                new Script([
                    'src' => '/media/script/two.js',
                    'async' => true,
                    'defer' => true,
                ]),
                new Script([
                    'src' => '/media/script/three.js',
                    'async' => true,
                    'defer' => false,
                ]),
            ],
            (new Extractor($string))->script()
        );
    }

    /**
     * @test
     */
    public function returns_script_tag()
    {
        $string = '<script src="/media/script/one.js"></script>';
        $string .= '<p>Some</p>';
        $string .= '<script src="/media/script/two.js" async defer></script>';
        $string .= '<script src="/media/script/three.js" async></script>';

        $this->assertEquals(
            '<script src="/media/script/two.js" async defer></script>',
            (string)(new Extractor($string))->script()[1]
        );
    }

    /**
     * @test
     */
    public function extracts_anchors()
    {
        $string = '<a href="/media/files/one.pdf" target="_blank">Document one</a>';
        $string .= '<p>Some</p>';
        $string .= '<a href="/media/files/two.docx" title="Word document">Word document</a>';
        $string .= '<a href="/media/files/three.pdf">Document three</a>';
        $string .= '<a href="/four">Page link</a>';

        $extractor = new Extractor($string);

        $this->assertEquals(
            [
                new Anchor([
                    'href' => '/media/files/one.pdf',
                    'target' => '_blank',
                    'title' => null,
                    'nodeValue' => 'Document one',
                ]),
                new Anchor([
                    'href' => '/media/files/two.docx',
                    'target' => null,
                    'title' => 'Word document',
                    'nodeValue' => 'Word document',
                ]),
                new Anchor([
                    'href' => '/media/files/three.pdf',
                    'target' => null,
                    'title' => null,
                    'nodeValue' => 'Document three',
                ]),
                new Anchor([
                    'href' => '/four',
                    'target' => null,
                    'title' => null,
                    'nodeValue' => 'Page link',
                ]),
            ],
            $extractor->a()
        );

        $this->assertEquals(
            [
                new Anchor([
                    'href' => '/media/files/one.pdf',
                    'target' => '_blank',
                    'title' => null,
                    'nodeValue' => 'Document one',
                ]),
                new Anchor([
                    'href' => '/media/files/three.pdf',
                    'target' => null,
                    'title' => null,
                    'nodeValue' => 'Document three',
                ]),
            ],
            $extractor->a(['pdf'])
        );

        $this->assertEquals(
            [
                new Anchor([
                    'href' => '/media/files/two.docx',
                    'target' => null,
                    'title' => 'Word document',
                    'nodeValue' => 'Word document',
                ]),
            ],
            $extractor->a(['docx'])
        );
    }

    /**
     * @test
     */
    public function returns_anchor_tag()
    {
        $string = '<a href="/media/files/one.pdf" '.
            'target="_blank" title="Document">Document</a>';

        $this->assertEquals(
            $string,
            (string)(new Extractor($string))->a()[0]
        );
    }

    /**
     * @test
     */
    public function prepends_url_if_not_present()
    {
        $string = '<img src="/media/image/one.jpg" alt="Image one">';
        $string .= '<img src="https://mysite.com/media/image/two.jpg" alt="Image two">';
        $string .= '<a href="/media/files/two.pdf" '.
            'target="_blank" title="Document">Document</a>';
        $string .= '<script src="/media/script/three.js" async></script>';

        $extractor = new Extractor($string, false, 'https://demo.com');

        $this->assertEquals(
            [
                new Image([
                    'src' => 'https://demo.com/media/image/one.jpg',
                    'alt' => 'Image one',
                ]),
                new Image([
                    'src' => 'https://mysite.com/media/image/two.jpg',
                    'alt' => 'Image two',
                ]),
            ],
            $extractor->img()
        );

        $this->assertEquals(
            [
                new Anchor([
                    'href' => 'https://demo.com/media/files/two.pdf',
                    'target' => '_blank',
                    'title' => 'Document',
                    'nodeValue' => 'Document',
                ]),
            ],
            $extractor->a()
        );

        $this->assertEquals(
            [
                new Script([
                    'src' => 'https://demo.com/media/script/three.js',
                    'async' => true,
                    'defer' => false,
                ]),
            ],
            $extractor->script()
        );
    }

    /**
     * @test
     */
    public function throws_exception_with_invalid_input_and_validation_set_to_true()
    {
        $string = '<body<img src="/media/image/one.jpg" alt="Image one">';
        $string .= '<img src="https://mysite.com/media/image/two.jpg" alt="Image two">';

        $this->expectException(InvalidHtmlException::class);

        $extractor = new Extractor($string, true);
    }

    /**
     * @test
     */
    public function does_not_throw_exception_with_invalid_input_and_validation_set_to_false()
    {
        $string = '<body<img src="/media/image/one.jpg" alt="Image one">';
        $string .= '<img src="https://mysite.com/media/image/two.jpg" alt="Image two">';

        $extractor = new Extractor($string, false);

        $this->assertEquals(
            [
                new Image([
                    'src' => 'https://mysite.com/media/image/two.jpg',
                    'alt' => 'Image two',
                ]),
            ],
            $extractor->img()
        );
    }
}