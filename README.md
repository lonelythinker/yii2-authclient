# yii2-authclient
"yii authclient","QQ Auth","Wechat Auth","Weibo Auth"

**Config Setting**

```
'components' => [
    'authClientCollection' => [
        'class' => 'yii\authclient\Collection',
        'clients' => [
            'qq' => [
                'class' => 'lonelythinker\yii2\authclient\Qq',
                'clientId' => 'qq_appid',
                'clientSecret' => 'qq_appkey',
            ],
            'weixin' => [
                'class' => 'lonelythinker\yii2\authclient\Weixin',
                'clientId' => 'weixin_appid',
                'clientSecret' => 'weixin_appkey',
            ],
            'weibo' => [
                'class' => 'lonelythinker\yii2\authclient\Weibo',
                'clientId' => 'wb_key',
                'clientSecret' => 'wb_secret',
            ],
        ],
    ]
    // other components
]
```
