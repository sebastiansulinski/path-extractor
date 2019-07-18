<?php

namespace SSDTest;

use PHPUnit\Framework\TestCase;
use SSD\PathExtractor\Extractor;
use SSD\PathExtractor\Tags\Image;
use SSD\PathExtractor\Tags\Anchor;
use SSD\PathExtractor\Tags\Script;

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
            (new Extractor($string))->img()
        );

        $this->assertEquals(
            [
                new Image([
                    'src' => '/media/image/three.svg',
                    'alt' => 'Image three',
                ]),
            ],
            (new Extractor($string))->img(['svg'])
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
            (new Extractor($string))->a()
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
            (new Extractor($string))->a(['pdf'])
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
            (new Extractor($string))->a(['docx'])
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
}