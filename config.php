<?php
// Site settings. Edit these; nothing else needs changing to run the site.
return [
    'title'       => 'madptr',
    'description' => 'Rants on computer graphics, programming and other stuff.',
    'url'         => 'https://madptr.com',   // no trailing slash
    'author'      => 'Abilash Joseph Rajarethinam',
    'logo'        => '/res/tri_logo.png',
    'timezone'    => 'Asia/Kolkata',     // used to show post dates

    // Links shown in the header, in order.
    'nav' => [
        'Posts'    => '/',
        'Projects' => '/projects/',
        'Dog ears' => '/dogears/',
        'About'    => '/about/',
        'Tags'     => '/tags/',
    ],

    'footer_links' => [
        'Mastodon' => 'https://mastodon.gamedev.place/@madptr',
        'GitHub'   => 'https://github.com/madptr',
        'RSS'      => '/index.xml',
    ],
];
