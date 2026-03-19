<?php

declare(strict_types=1);

namespace Drupal\shared\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * @todo Add description for this subscriber.
 */
final class EventSubscriber implements EventSubscriberInterface {

  protected $currentUser;

  /**
   * Constructs a new EventSubscriber object.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   */
  public function __construct(AccountInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * Kernel request event handler.
   */
  public function onKernelRequest(RequestEvent $event) {

    if (!$event->isMainRequest()) {
      return;
    }
    $request = $event->getRequest();
    $route_name = $request->attributes->get('_route');
    $path = $request->getPathInfo();
    // Ignorer les pages suivantes
    $routes_OK = [
      'user.login',
      'user.logout',
      'user.password',
      'system.403',
      'system.404',
    ];
    if (in_array($route_name, $routes_OK)) {
      return;
    }

    if ($this->currentUser->hasRole('genealogist_only')) {
      if ($path !== '/genealogy') {
        $url = Url::fromRoute('system.403');
        $response = new RedirectResponse($url->toString());
        $event->setResponse($response); // ← C'était ça qui manquait    }
      }
    }
  }

  /**
   * Kernel response event handler.
   */
  public
  function onKernelResponse(ResponseEvent $event): void {
  }

  /**
   * {@inheritdoc}
   */
  public
  static function getSubscribedEvents(): array {
    return [
      // Priorité haute pour passer avant le routeur Drupal
      KernelEvents::REQUEST => ['onKernelRequest', 30],
      KernelEvents::RESPONSE => ['onKernelResponse'],
    ];
  }

}
