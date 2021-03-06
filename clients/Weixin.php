<?php

namespace lonelythinker\yii2\authclient\clients;

use yii\authclient\OAuth2;
use yii\web\HttpException;
use Yii;

/**
 * Weixin(Wechat) allows authentication via Weixin(Wechat) OAuth.
 *
 * In order to use Weixin(Wechat) OAuth you must register your application at <https://open.weixin.qq.com/>.
 *
 * Example application configuration:
 *
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'weixin' => [   // for account of https://open.weixin.qq.com/
 *                 'class' => 'lonelythinker\yii2\authclient\Weixin',
 *                 'clientId' => 'weixin_appid',
 *                 'clientSecret' => 'weixin_appkey',
 *             ],
 *             'weixinmp' => [  // for account of https://mp.weixin.qq.com/
 *                 'class' => 'lonelythinker\yii2\authclient\Weixin',
 *                 'type' => 'mp',
 *                 'clientId' => 'weixin_appid',
 *                 'clientSecret' => 'weixin_appkey',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @see https://open.weixin.qq.com/
 * @see https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&lang=zh_CN
 *
 * @author : lonelythinker
 * @email : 710366112@qq.com
 * @homepage : www.lonelythinker.cn
 */
class Weixin extends OAuth2
{

    /**
     * @inheritdoc
     */
    public $authUrl = 'https://open.weixin.qq.com/connect/qrconnect';
    public $authUrlMp = 'https://open.weixin.qq.com/connect/oauth2/authorize';
    /**
     * @inheritdoc
     */
    public $tokenUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'https://api.weixin.qq.com';

    public $type = null;
    public $validateAuthState = false;
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->scope === null) {
            //$this->scope = implode(',', $this->type == 'mp' ? ['snsapi_userinfo',] : ['snsapi_login']);
            $this->scope = implode(',', ['snsapi_login']);
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        return [
            'id' => 'openid',
            'username' => 'nickname',
        ];
    }

    /**
     * @inheritdoc
     */
    public function buildAuthUrl(array $params = [])
    {
        $authState = $this->generateAuthState();
        $this->setState('authState', $authState);
        $defaultParams = [
            'appid' => $this->clientId,
            'redirect_uri' => $this->getReturnUrl(),
            'response_type' => 'code',
        ];
        if (!empty($this->scope)) {
            $defaultParams['scope'] = $this->scope;
        }
        $defaultParams['state'] = $authState;
        $url = $this->type == 'mp'?$this->authUrlMp:$this->authUrl;
        return $this->composeUrl($url, array_merge($defaultParams, $params));
    }

    /**
     * @inheritdoc
     */
    public function fetchAccessToken($authCode, array $params = [])
    {
        $authState = $this->getState('authState');
        // if (!isset($_REQUEST['state']) || empty($authState) || strcmp($_REQUEST['state'], $authState) !== 0) {
        //     throw new HttpException(400, 'Invalid auth state parameter.');
        // } else {
        //     $this->removeState('authState');
        // }

        $params['appid'] = $this->clientId;
        $params['secret'] = $this->clientSecret;
        return parent::fetchAccessToken($authCode, $params);

    }

    /**
     * @inheritdoc
     */
    public function applyAccessTokenToRequest($request, $accessToken)
    {
        $data = $request->getData();
        $data['access_token'] = $accessToken->getToken();
        $data['openid'] = $accessToken->getParam('openid');
        $data['lang'] = 'zh_CN';
        $request->setData($data);
    }

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        $userAttributes = $this->api('sns/userinfo');
        
//        $userAttributes['id'] = $userAttributes['unionid'];
        return $userAttributes;
    }

    /**
     * @inheritdoc
     */
    protected function defaultReturnUrl()
    {
        $params = $_GET;
        unset($params['code']);
        unset($params['state']);
        $params[0] = Yii::$app->controller->getRoute();

        return Yii::$app->getUrlManager()->createAbsoluteUrl($params);
    }

    /**
     * Generates the auth state value.
     * @return string auth state value.
     */
    protected function generateAuthState()
    {
        return sha1(uniqid(get_class($this), true));
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'weixin';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'Weixin';
    }

    /**
     * @inheritdoc
     */
    protected function defaultViewOptions()
    {
        return [
            'popupWidth' => 800,
            'popupHeight' => 500,
        ];
    }
}