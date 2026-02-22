<?php

return [

    'title' => 'Tetapkan semula kata laluan anda',

    'heading' => 'Tetapkan semula kata laluan anda',

    'form' => [

        'email' => [
            'label' => 'Alamat emel',
        ],

        'password' => [
            'label' => 'Kata laluan',
            'validation_attribute' => 'kata laluan',
        ],

        'password_confirmation' => [
            'label' => 'Sahkan kata laluan',
        ],

        'actions' => [

            'reset' => [
                'label' => 'Tetapkan semula kata laluan',
            ],

        ],

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'Terlalu banyak percubaan',
            'body' => 'Sila cuba lagi dalam :seconds saat.',
        ],

    ],

];
