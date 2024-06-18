<?php

use Illuminate\Support\Str;
use SimonHamp\TheOg\Image;
use SimonHamp\TheOg\Theme\Theme;
use SimonHamp\TheOg\Theme\Background;
use SimonHamp\TheOg\BorderPosition;
use SimonHamp\TheOg\Theme\Fonts\Inter;

function storeImage($title, $description, $path) { // [tl! collapse:start]
    (new Image())
        ->theme(new Theme(
            baseColor: '#FFFFFF',
            accentColor: '#FFFFFF',
            backgroundColor: '#000000',
            baseFont: Inter::light(),
            titleFont: Inter::black(),
        ))
        ->background(new Background('source/assets/og-background.png'))
        ->border(BorderPosition::None)
        ->title($title)
        ->description($description)
        ->save($path); // [tl! collapse:end]
}

return [
    'baseUrl' => 'http://localhost:8000',
    'production' => false,
    'siteName' => 'Blog Starter Template',
    'siteDescription' => 'Generate an elegant blog with Jigsaw',
    'siteAuthor' => 'Author Name',

    // collections
    'collections' => [
        'posts' => [
            'author' => 'Author Name', // Default author, if not provided in a post
            'sort' => '-date',
            'path' => 'blog/{filename}',
        ],
        'categories' => [
            'path' => '/blog/categories/{filename}',
            'posts' => function ($page, $allPosts) {
                return $allPosts->filter(function ($post) use ($page) {
                    return $post->categories ? in_array($page->getFilename(), $post->categories, true) : false;
                });
            },
        ],
    ],

    // helpers
    'getDate' => function ($page) {
        return Datetime::createFromFormat('U', $page->date);
    },
    'getExcerpt' => function ($page, $length = 255) {
        if ($page->excerpt) {
            return $page->excerpt;
        }

        $content = preg_split('/<!-- more -->/m', $page->getContent(), 2);
        $cleaned = trim(
            strip_tags(
                preg_replace(['/<pre>[\w\W]*?<\/pre>/', '/<h\d>[\w\W]*?<\/h\d>/'], '', $content[0]),
                '<code>'
            )
        );

        if (count($content) > 1) {
            return $cleaned;
        }

        $truncated = substr($cleaned, 0, $length);

        if (substr_count($truncated, '<code>') > substr_count($truncated, '</code>')) {
            $truncated .= '</code>';
        }

        return strlen($cleaned) > $length
            ? preg_replace('/\s+?(\S+)?$/', '', $truncated) . '...'
            : $cleaned;
    },
    'isActive' => function ($page, $path) {
        return Str::endsWith(trimPath($page->getPath()), trimPath($path));
    },

    'getOgImageUrl' => function ($page) {

        // If a page has no og_image defined, return the default one.
        if (!$page->og_image) {
            return $page->baseUrl . '/assets/img/default.png';
        }

        // Otherwise, use the value of og_image to define its filename path.
        $path = 'source/assets/og-images/' . $page->og_image . '.png';

        // If the file does not exist, create it.
        if (!file_exists($path)) {
            storeImage($page->title, $page->description, $path);
        }

        // Finally, return its absolute, public URL.
        return $page->baseUrl . str_replace('source', '', $path);
    },
];
