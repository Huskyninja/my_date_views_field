<?php

/**
 * @param array $data
 */
function my_date_views_field_views_data_alter(array &$data) {

    $tables = [
        'one',
        'two',
        'three',
        'four',
    ];
    foreach ($tables as $name) {
        $data[$name]['createDate'] = [
            'title' => t('Date Created'),
            'field' => [
                'title' => t('Date Created'),
                'help' => t('Display the Date Created'),
                'id' => 'my_date',
            ],
            'sort' => [
                'id' => 'standard',
            ],
        ];

        if (isset($data[$name]['updateDate'])) {
            $data[$name]['updateDate'] = [
                'title' => t('Date Updated'),
                'field' => [
                    'title' => t('Date Updated'),
                    'help' => t('Display the Last Date Updated'),
                    'id' => 'my_date',
                ],
                'sort' => [
                    'id' => 'standard',
                ],
            ];
        }
    }

}