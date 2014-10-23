<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spas;

use \Spas\Session;
use \Spas\FileSessionHandler;

use \Silex\Application;
use \Silex\ServiceProviderInterface;
use \Symfony\Component\HttpKernel\KernelEvents;
use \Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Symfony HttpFoundation component Provider for sessions.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SpasSessionProvider implements ServiceProviderInterface
{
    private $app;

    public function register(Application $app)
    {
        $this->app = $app;

        $app['session'] = $app->share(function ($app) {
            return new Session($app['session.handler']);
        });

        $app['session.handler'] = $app->share(function ($app) {
            return new FileSessionHandler($app['session.options']);
        });

        $app['session.default_locale'] = 'en';
    }

    public function onEarlyKernelRequest(GetResponseEvent $event)
    {
		$cookies = $event->getRequest()->cookies;
		$sess = clone $this->app['session'];
        $event->getRequest()->setSession($sess);
		if($session_id = $cookies->get($event->getRequest()->getSession()->getName())){
			$event->getRequest()->getSession()->setId($session_id);
		}
		$event->getRequest()->getSession()->start();
    }

    public function boot(Application $app)
    {
        $app['dispatcher']->addListener(KernelEvents::REQUEST, array($this, 'onEarlyKernelRequest'), 128);
    }
}
