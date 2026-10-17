<?php

namespace App\Support\NewsImport;

use DOMElement;
use DOMNode;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Làm sạch HTML trước khi lưu: chỉ giữ tag/attribute an toàn, bỏ script/iframe.
 */
class ContentSanitizer
{
    private const DROP_TAGS = [
        'script', 'style', 'iframe', 'form', 'noscript', 'ins', 'object',
        'embed', 'svg', 'link', 'meta', 'template', 'head',
    ];

    private const ALLOWED_TAGS = [
        'p', 'br', 'h2', 'h3', 'h4', 'h5', 'ul', 'ol', 'li', 'strong', 'b',
        'em', 'i', 'u', 's', 'blockquote', 'table', 'thead', 'tbody', 'tr',
        'th', 'td', 'figure', 'figcaption', 'img', 'a', 'hr', 'code', 'pre',
        'span', 'div', 'sup', 'sub',
    ];

    /**
     * @var array<string, list<string>>
     */
    private const ALLOWED_ATTRIBUTES = [
        'img' => ['src', 'alt', 'width', 'height'],
        'a' => ['href', 'title'],
        'th' => ['colspan', 'rowspan'],
        'td' => ['colspan', 'rowspan'],
        'ol' => ['start'],
    ];

    public function sanitize(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $crawler = new Crawler($html);

        $this->removeDropTags($crawler);
        $this->unwrapDisallowedTags($crawler);
        $this->cleanAttributes($crawler);
        $this->cleanLinks($crawler);
        $this->removeEmptyParagraphs($crawler);

        $output = trim($crawler->filter('body')->html());
        $output = preg_replace('/[ \t]+\n/', "\n", $output) ?? $output;

        return trim($output);
    }

    private function removeDropTags(Crawler $crawler): void
    {
        $crawler->filter(implode(',', self::DROP_TAGS))->each(static function (Crawler $node): void {
            self::detach($node->getNode(0));
        });
    }

    private function unwrapDisallowedTags(Crawler $crawler): void
    {
        $crawler->filter('body *')->each(static function (Crawler $node): void {
            $dom = $node->getNode(0);

            if (! $dom instanceof DOMElement) {
                return;
            }

            if (in_array(strtolower($dom->tagName), self::ALLOWED_TAGS, true)) {
                return;
            }

            // Giữ nội dung con, chỉ bỏ thẻ bọc ngoài.
            while ($dom->firstChild) {
                $dom->parentNode?->insertBefore($dom->firstChild, $dom);
            }

            self::detach($dom);
        });
    }

    private function cleanAttributes(Crawler $crawler): void
    {
        $crawler->filter('body *')->each(static function (Crawler $node): void {
            $dom = $node->getNode(0);

            if (! $dom instanceof DOMElement) {
                return;
            }

            $tag = strtolower($dom->tagName);
            $allowed = self::ALLOWED_ATTRIBUTES[$tag] ?? [];

            foreach (iterator_to_array($dom->attributes) as $attribute) {
                if (! in_array(strtolower($attribute->nodeName), $allowed, true)) {
                    $dom->removeAttribute($attribute->nodeName);
                }
            }

            if ($tag === 'img') {
                $dom->setAttribute('loading', 'lazy');
            }
        });
    }

    private function cleanLinks(Crawler $crawler): void
    {
        $crawler->filter('a')->each(static function (Crawler $node): void {
            $dom = $node->getNode(0);

            if (! $dom instanceof DOMElement) {
                return;
            }

            $href = trim($dom->getAttribute('href'));
            $scheme = strtolower((string) parse_url($href, PHP_URL_SCHEME));

            if ($href === '' || ($scheme !== '' && ! in_array($scheme, ['http', 'https', 'mailto'], true))) {
                self::detach($dom);

                return;
            }

            $dom->setAttribute('rel', 'noopener noreferrer');
            $dom->setAttribute('target', '_blank');
        });
    }

    private function removeEmptyParagraphs(Crawler $crawler): void
    {
        $crawler->filter('p')->each(static function (Crawler $node): void {
            $dom = $node->getNode(0);

            if (! $dom instanceof DOMElement) {
                return;
            }

            $text = trim(preg_replace('/\s+/u', ' ', $dom->textContent) ?? '');

            if ($text === '') {
                self::detach($dom);
            }
        });
    }

    private static function detach(?DOMNode $node): void
    {
        $node?->parentNode?->removeChild($node);
    }
}
