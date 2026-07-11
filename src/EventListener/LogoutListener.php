<?php

namespace App\EventListener;

use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutListener
{
    public function onLogout(LogoutEvent $event): void
    {
        // Logique à exécuter lors du logout si nécessaire
    }
}
