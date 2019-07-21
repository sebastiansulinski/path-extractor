<?php

namespace SSD\PathExtractor;

use tidy;
use Exception;
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
     * @param  bool $validate
     * @param  string|null $url
     * @throws \SSD\PathExtractor\InvalidHtmlException
     */
    public function __construct(string $string, bool $validate = false, string $url = null)
    {
        try {

            $this->dom = new DOMDocument;
            $this->dom->loadHTML($this->purify($string, $validate));

            $this->url = $url;

        } catch (Exception $exception) {
            throw new InvalidHtmlException($exception->getMessage());
        }
    }

    /**
     * Purify string.
     *
     * @param  string $string
     * @param  bool $validate
     * @return string
     */
    private function purify(string $string, bool $validate = false): string
    {
        if ($validate) {
            return $string;
        }

        return (new tidy)->repairString($string, [
            'clean' => 'yes',
            'output-html' => 'yes',
            'wrap' => 0,
        ], 'utf8');
    }

    /**
     * Extract anchors.
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
                'target' => $this->attribute($tag->getAttribute('target')),
                'title' => $this->attribute($tag->getAttribute('title')),
                'nodeValue' => $tag->nodeValue,
            ]);
        }

        return $this->filter($all, 'href', $extensions);
    }

    /**
     * Extract images.
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
                'alt' => $this->attribute($tag->getAttribute('alt')),
            ]);
        }

        return $this->filter($all, 'src', $extensions);
    }

    /**
     * Extract scripts.
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
                'async' => $this->attribute($tag->hasAttribute('async')),
                'defer' => $this->attribute($tag->hasAttribute('defer')),
            ]);
        }

        return $all;
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
     * Get attribute.
     *
     * @param  string $value
     * @return string|null
     */
    private function attribute(string $value): ?string
    {
        return $value === '' ? null : $value;
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