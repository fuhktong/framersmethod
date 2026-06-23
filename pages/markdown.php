<?php
/**
 * Minimal, safe Markdown renderer for post bodies.
 *
 * Supports: headings, bold, italic, inline code, links, images, unordered and
 * ordered lists, blockquotes, horizontal rules, fenced code blocks, paragraphs.
 * All text is HTML-escaped first, so raw HTML in the source is NOT rendered —
 * the only tags in the output are the ones this function generates.
 */

function render_markdown(string $text): string
{
    $text = str_replace(["\r\n", "\r"], "\n", $text);

    // Pull out fenced code blocks first so their contents are left untouched
    $codeBlocks = [];
    $text = preg_replace_callback('/```[ \t]*\n(.*?)\n```/s', function ($m) use (&$codeBlocks) {
        $key = '@@FENCED:' . count($codeBlocks) . '@@';
        $codeBlocks[$key] = '<pre><code>' . htmlspecialchars($m[1], ENT_QUOTES) . '</code></pre>';
        return $key;
    }, $text);

    $blocks = preg_split('/\n{2,}/', trim($text));
    $html = [];

    foreach ($blocks as $block) {
        $block = trim($block, "\n");
        if ($block === '') {
            continue;
        }

        // A lone fenced-code-block placeholder
        if (isset($codeBlocks[$block])) {
            $html[] = $codeBlocks[$block];
            continue;
        }

        $lines = explode("\n", $block);

        // Horizontal rule
        if (preg_match('/^(\-{3,}|\*{3,}|_{3,})$/', trim($block))) {
            $html[] = '<hr>';
            continue;
        }

        // Heading (single line)
        if (count($lines) === 1 && preg_match('/^(#{1,6})\s+(.*)$/', $lines[0], $m)) {
            $level = strlen($m[1]);
            $html[] = "<h{$level}>" . md_inline($m[2]) . "</h{$level}>";
            continue;
        }

        // Blockquote
        if (preg_match('/^>\s?/', $lines[0])) {
            $quote = array_map(fn ($l) => preg_replace('/^>\s?/', '', $l), $lines);
            $html[] = '<blockquote>' . md_inline(implode("\n", $quote)) . '</blockquote>';
            continue;
        }

        // Unordered list
        if (preg_match('/^[\-\*]\s+/', $lines[0])) {
            $items = '';
            foreach ($lines as $l) {
                $items .= '<li>' . md_inline(preg_replace('/^[\-\*]\s+/', '', $l)) . '</li>';
            }
            $html[] = '<ul>' . $items . '</ul>';
            continue;
        }

        // Ordered list
        if (preg_match('/^\d+\.\s+/', $lines[0])) {
            $items = '';
            foreach ($lines as $l) {
                $items .= '<li>' . md_inline(preg_replace('/^\d+\.\s+/', '', $l)) . '</li>';
            }
            $html[] = '<ol>' . $items . '</ol>';
            continue;
        }

        // Paragraph
        $html[] = '<p>' . md_inline($block) . '</p>';
    }

    $out = implode("\n", $html);

    // Restore any code-block placeholders that ended up nested inside a block
    foreach ($codeBlocks as $key => $value) {
        $out = str_replace($key, $value, $out);
    }

    return $out;
}

/**
 * Inline-level Markdown: escape HTML, then images, links, bold, italic, code,
 * and convert single newlines to <br>.
 */
function md_inline(string $text): string
{
    $text = htmlspecialchars($text, ENT_QUOTES);

    // Inline code first, protecting its contents from the other rules
    $codes = [];
    $text = preg_replace_callback('/`([^`]+)`/', function ($m) use (&$codes) {
        $key = '@@ICODE:' . count($codes) . '@@';
        $codes[$key] = '<code>' . $m[1] . '</code>';
        return $key;
    }, $text);

    // Images: ![alt](url)
    $text = preg_replace_callback('/!\[([^\]]*)\]\(([^)\s]+)\)/', function ($m) {
        $url = md_safe_url($m[2]);
        return $url === '' ? $m[0] : '<img src="' . $url . '" alt="' . $m[1] . '">';
    }, $text);

    // Links: [text](url)
    $text = preg_replace_callback('/\[([^\]]+)\]\(([^)\s]+)\)/', function ($m) {
        $url = md_safe_url($m[2]);
        return $url === '' ? $m[0] : '<a href="' . $url . '">' . $m[1] . '</a>';
    }, $text);

    // Bold, then italic
    $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
    $text = preg_replace('/__(.+?)__/s', '<strong>$1</strong>', $text);
    $text = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $text);
    $text = preg_replace('/(?<![a-zA-Z0-9])_(.+?)_(?![a-zA-Z0-9])/s', '<em>$1</em>', $text);

    // Single newlines -> <br>
    $text = nl2br($text, false);

    foreach ($codes as $key => $value) {
        $text = str_replace($key, $value, $text);
    }

    return $text;
}

/**
 * Allow only http(s), protocol-relative, or root-relative URLs (blocks
 * javascript: and other unsafe schemes).
 */
function md_safe_url(string $url): string
{
    $decoded = htmlspecialchars_decode($url, ENT_QUOTES);
    if (preg_match('#^(https?:)?//#i', $decoded) || str_starts_with($decoded, '/')) {
        return htmlspecialchars($decoded, ENT_QUOTES);
    }
    return '';
}
