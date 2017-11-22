<?php
/*
 *  Copyright (C) 2016 X Gemeente
 *                     X Amsterdam
 *                     X Onderzoek, Informatie en Statistiek
 *
 *  This Source Code Form is subject to the terms of the Mozilla Public
 *  License, v. 2.0. If a copy of the MPL was not distributed with this
 *  file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace GemeenteAmsterdam\BrievenhulpBundle\Listener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use GemeenteAmsterdam\BrievenhulpBundle\Entity\AuditLogEntry;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use GemeenteAmsterdam\BrievenhulpBundle\Entity\HulpverlenerRepository;

class SecurityListener
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var HulpverlenerRepository
     */
    protected $hulpverlenerRepository;

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine, RequestStack $requestStack, HulpverlenerRepository $hulpverlenerRepository)
    {
        $this->doctrine = $doctrine;
        $this->requestStack = $requestStack;
        $this->hulpverlenerRepository = $hulpverlenerRepository;
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();

        $username = $event->getAuthenticationToken()->getUsername();
        $user = $event->getAuthenticationToken()->getUser();

        $auditLog = new AuditLogEntry(new \DateTime(), $request->attributes->get('_route'), 'login', $request->getClientIp(), $request->headers->get('referer'), $user);
        $auditLog->setData('success', $event->getAuthenticationToken()->isAuthenticated());
        $auditLog->setData('username', $username);

        $this->doctrine->getManager()->persist($auditLog);
        $this->doctrine->getManager()->flush($auditLog);

        $request->attributes->set('auditLog', $auditLog);
    }

    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();

        $username = $event->getAuthenticationToken()->getUser();
        $user = $this->hulpverlenerRepository->findOneBy(['email' => $username]);

        $auditLog = new AuditLogEntry(new \DateTime(), $request->attributes->get('_route'), 'login', $request->getClientIp(), $request->headers->get('referer'), $user);
        $auditLog->setData('success', false);
        $auditLog->setData('username', $username);
        $auditLog->setData('error', $event->getAuthenticationException()->getMessage());

        $this->doctrine->getManager()->persist($auditLog);
        $this->doctrine->getManager()->flush($auditLog);

        $request->attributes->set('auditLog', $auditLog);
    }
}