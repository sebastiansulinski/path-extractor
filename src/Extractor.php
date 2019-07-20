<?php

namespace SSD\PathExtractor;

use DOMDocument;
use SSD\PathExtractor\Tags\Tag;
use SSD\PathExtractor\Tags\Image;
use SSD\PathExtractor\Tags\Anchor;
use SSD\PathExtractor\Tags\Script;

class Extractor
{
    /**
     * @var \DOMDocument
     */
    private $dom;

    /**
     * @var string|null
     */
    private $url;

    /**
     * Extract constructor.
     *
     * @param  string $string
     * @param  string|null $url
     */
    public function __construct(string $string, string $url = null)
    {
        $this->dom = new DOMDocument;
        $this->dom->loadHTML($string);

        $this->url = $url;
    }

    /**
     * Extract all images.
     *
     * @param  array|null $extensions
     * @return array
     */
    public function img(array $extensions = null): array
    {
        $tags = $this->dom->getElementsByTagName('img');

        if (empty($tags)) {
            return [];
        }

        $all = [];

        foreach ($tags as $tag) {
            $all[] = new Image([
                'src' => $this->path($tag->getAttribute('src')),
                'alt' => $tag->getAttribute('alt'),
            ]);
        }

        return $this->filter($all, 'src', $extensions);
    }

    /**
     * Extract all scripts.
     *
     * @return array
     */
    public function script(): array
    {
        $tags = $this->dom->getElementsByTagName('script');

        if (empty($tags)) {
            return [];
        }

        $all = [];

        foreach ($tags as $tag) {
            $all[] = new Script([
                'src' => $this->path($tag->getAttribute('src')),
                'async' => $tag->hasAttribute('async'),
                'defer' => $tag->hasAttribute('defer'),
            ]);
        }

        return $all;
    }

    /**
     * Extract all documents.
     *
     * @param  array $extensions
     * @return array
     */
    public function a(array $extensions = null): array
    {
        $tags = $this->dom->getElementsByTagName('a');

        if (empty($tags)) {
            return [];
        }

        $all = [];

        foreach ($tags as $tag) {
            $all[] = new Anchor([
                'href' => $this->path($tag->getAttribute('href')),
                'target' => $tag->getAttribute('target'),
                'title' => $tag->getAttribute('title'),
                'nodeValue' => $tag->nodeValue,
            ]);
        }

        return $this->filter($all, 'href', $extensions);
    }

    /**
     * Prepend url if not already present in the path.
     *
     * @param  string $path
     * @return string
     */
    private function path(string $path): string
    {
        if (is_null($this->url)) {
            return $path;
        }

        if (!is_null(parse_url($path, PHP_URL_HOST))) {
            return $path;
        }

        return rtrim($this->url, '/').'/'.ltrim($path, '/');
    }

    /**
     * Filter result by extension.
     *
     * @param  array $all
     * @param  string $attribute
     * @param  array|null $extensions
     * @return array
     */
    private function filter(array $all, string $attribute, ?array $extensions): array
    {
        if (is_null($extensions)) {
            return $all;
        }

        return array_values(array_filter(
            $all,
            function (Tag $item) use ($attribute, $extensions) {
                return in_array(pathinfo($item->{$attribute}, PATHINFO_EXTENSION), $extensions);
            }
        ));
    }
}