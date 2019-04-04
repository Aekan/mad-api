<?php
return [
    'class' => 'yii\web\UrlManager',
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        // Api
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'api/v1/article',
            'only' => [
                'index',
                'view',
                'options'
            ]
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'api/Worker/worker',
            'only' => [
                'index',
                'view',
                'options'
            ]
        ],
    ]
];
