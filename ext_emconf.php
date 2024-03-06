<?php

$EM_CONF['avif'] = [
    'title' => 'Creates AVIF copies for images',
    'description' => 'Creates AVIF copies of all jpeg and png images',
    'category' => 'fe',
    'author' => 'Daniel Corn',
    'author_email' => 'cod@iresults.li',
    'state' => 'stable',
    'author_company' => 'iresults GmbH',
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-12.9.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    'suggests' => [
    ],
];
