<?php

$EM_CONF[$_EXTKEY] = [
    "title" => "HideByCountries",
    "description" => "restrict access to some CE by frontend user countries",
    "category" => "be",
    "state" => "stable",
    "version" => "1.0.0",
    "author" => "Oussema Harrabi",
    "author_email" => "contact@oussemaharrbi.tn",
    "author_company" => "Oussema ",
    "constraints" => [
        'depends' => [
            'php' => '8.1.0-8.4.99',
            'typo3' => '13.4.2-13.9.99',
            'backend' => '13.4.2-13.9.99',
            'extbase' => '13.4.2-13.9.99',
            'fluid' => '13.4.2-13.9.99',
            'frontend' => '13.4.2-13.9.99',
        ],
        "conflicts" => [],
        "suggests" => [],
    ],
];
