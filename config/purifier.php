<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HTMLPurifier Encoding
    |--------------------------------------------------------------------------
    | Always UTF-8 for multi-language support.
    */
    'encoding'      => 'UTF-8',
    'finalize'      => true,
    'ignoreNonStrings' => false,

    /*
    |--------------------------------------------------------------------------
    | Cache Directory
    |--------------------------------------------------------------------------
    | HTMLPurifier caches its definition for performance.
    | The storage/app/purifier directory is writable and ignored by git.
    */
    'cachePath'     => storage_path('app/purifier'),
    'cacheFileMode' => 0755,

    /*
    |--------------------------------------------------------------------------
    | Profiles
    |--------------------------------------------------------------------------
    | We define different sanitization profiles for different use cases.
    | Each profile has different allowed HTML tags and attributes.
    |
    | PROFILE 1: 'post_content'
    |   For post body content — rich text with formatting allowed.
    |   Allows: headings, paragraphs, lists, links, bold, italic, code, etc.
    |   Removes: script, iframe, object, style attributes, javascript: hrefs
    |
    | PROFILE 2: 'comment'
    |   For user comments — minimal formatting only.
    |   Allows: bold, italic, links
    |   Removes: everything else
    |
    | PROFILE 3: 'plain_text'
    |   Strips ALL HTML — for names, bios, search queries.
    |   Returns plain text only.
    */
    'settings'      => [

        'post_content' => [
            /*
            | HTML.Allowed defines which tags and attributes are permitted.
            | Format: 'tag[attr1|attr2],tag2[attr1]'
            |
            | WHY these specific tags?
            | h2, h3, h4  → headings (h1 reserved for page title)
            | p            → paragraphs (basic text structure)
            | br           → line breaks
            | strong, b    → bold text (emphasis)
            | em, i        → italic text (emphasis)
            | u            → underline
            | s, strike    → strikethrough
            | blockquote   → quoted content
            | pre, code    → code blocks (essential for tech blog)
            | ul, ol, li   → lists
            | a[href]      → links (href allowed but javascript: stripped)
            | img[src|alt] → images with src and alt
            | table, thead, tbody, tr, th, td → tables
            | hr           → horizontal rule
            | span         → inline container (for formatting)
            */
            'HTML.Allowed' => 'h2,h3,h4,p,br,strong,b,em,i,u,s,strike,
                               blockquote,pre,code[class],
                               ul,ol,li,
                               a[href|title|target|rel],
                               img[src|alt|width|height],
                               table,thead,tbody,tr,th[colspan|rowspan],td[colspan|rowspan],
                               hr,span[class],div[class]',

            /*
            | URI.SafeIframeRegexp would allow iframes from specific domains.
            | We leave this disabled — no iframes in blog posts.
            */

            /*
            | AutoFormat.AutoParagraph wraps bare text in <p> tags.
            | This keeps output consistent even if the editor does not.
            */
            'AutoFormat.AutoParagraph' => true,

            /*
            | AutoFormat.RemoveEmpty removes empty tags.
            | Prevents: <p></p><p></p> cluttering the output.
            */
            'AutoFormat.RemoveEmpty' => true,

            /*
            | HTML.TargetBlank adds target="_blank" to external links.
            | Keeps readers on Synthia while opening external links.
            */
            'HTML.TargetBlank' => true,

            /*
            | URI.DisableExternalResources prevents loading resources
            | from external servers in img src etc.
            | Set to false to allow images from CDNs.
            */
            'URI.DisableExternalResources' => false,

            /*
            | CSS.AllowedProperties — restrict which CSS properties
            | can appear in style attributes.
            | Empty string = no style attributes allowed.
            */
            'CSS.AllowedProperties' => '',
        ],

        'comment' => [
            /*
            | Comments allow minimal formatting only.
            | Readers should not inject complex HTML into comments.
            */
            'HTML.Allowed' => 'p,br,strong,b,em,i,a[href|rel],code',

            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty'   => true,
            'HTML.TargetBlank'         => true,
            'CSS.AllowedProperties'    => '',
        ],

        'plain_text' => [
            /*
            | Strips absolutely everything.
            | Output is guaranteed to be plain text with no HTML at all.
            */
            'HTML.Allowed' => '',

            'AutoFormat.RemoveEmpty' => true,
        ],
    ],
];
