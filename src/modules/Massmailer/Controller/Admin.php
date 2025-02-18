<?php

/**
 * FOSSBilling.
 *
 * @copyright FOSSBilling (https://www.fossbilling.org)
 * @license   Apache-2.0
 *
 * Copyright FOSSBilling 2022
 * This software may contain code previously used in the BoxBilling project.
 * Copyright BoxBilling, Inc 2011-2021
 *
 * This source file is subject to the Apache-2.0 License that is bundled
 * with this source code in the file LICENSE
 */

namespace Box\Mod\Massmailer\Controller;

class Admin implements \Box\InjectionAwareInterface
{
    protected $di;

    /**
     * @param mixed $di
     */
    public function setDi($di)
    {
        $this->di = $di;
    }

    /**
     * @return mixed
     */
    public function getDi()
    {
        return $this->di;
    }

    public function fetchNavigation()
    {
        return [
            'subpages' => [
                [
                    'location' => 'extensions',
                    'index' => 4000,
                    'label' => __trans('Mass mailer'),
                    'uri' => $this->di['url']->adminLink('massmailer'),
                    'class' => '',
                ],
            ],
        ];
    }

    public function register(\Box_App &$app)
    {
        $app->get('/massmailer', 'get_index', [], static::class);
        $app->get('/massmailer/message/:id', 'get_edit', ['id' => '[0-9]+'], static::class);
    }

    public function get_index(\Box_App $app)
    {
        $this->di['is_admin_logged'];

        return $app->render('mod_massmailer_index');
    }

    public function get_edit(\Box_App $app, $id)
    {
        $api = $this->di['api_admin'];
        $model = $api->massmailer_get(['id' => $id]);

        return $app->render('mod_massmailer_message', ['msg' => $model]);
    }
}
