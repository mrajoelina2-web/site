<?php

return [
    'survetement' => [
        'label' => 'Taille',
        'choices' => ['S', 'M', 'L', 'XL', 'XXL'],
    ],
    'veste' => [
        'label' => 'Taille',
        'choices' => ['S', 'M', 'L', 'XL', 'XXL'],
    ],
    'pantalon' => [
        'label' => 'Taille',
        'choices' => ['S', 'M', 'L', 'XL', 'XXL'],
    ],
    'bottes' => [
        'label' => 'Pointure',
        'choices' => array_map('strval', range(38, 46)),
    ],
    'sac' => [
        'label' => 'Niveau',
        'choices' => ['Level 1', 'Level 2', 'Level 3'],
        // Supplément de prix par niveau, en Ariary (0 = même prix)
        'extra' => [
            'Level 1' => 0,
            'Level 2' => 0,
            'Level 3' => 0,
        ],
    ],
];