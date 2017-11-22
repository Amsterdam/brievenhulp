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

namespace GemeenteAmsterdam\BrievenhulpBundle\Controller;

use GemeenteAmsterdam\BrievenhulpBundle\Form\Type\SubmitCompleteFormType;
use GemeenteAmsterdam\BrievenhulpBundle\Form\Type\SubmitDeleteFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use GemeenteAmsterdam\BrievenhulpBundle\Form\Type\ReassignHulpvraagFormType;
use GemeenteAmsterdam\BrievenhulpBundle\Form\Model\ProfileFormModel;
use GemeenteAmsterdam\BrievenhulpBundle\Form\Type\ProfileFormType;
use GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpvraag;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use GemeenteAmsterdam\BrievenhulpBundle\Form\Model\SendSmsFormModel;
use GemeenteAmsterdam\BrievenhulpBundle\Form\Type\SendSmsFormType;
use GemeenteAmsterdam\BrievenhulpBundle\Entity\VerzondenSmsBericht;
use GemeenteAmsterdam\BrievenhulpBundle\Entity\AuditLogEntry;
use GemeenteAmsterdam\BrievenhulpBundle\Entity\HulpverlenerToewijzing;
use Intervention\Image\ImageManagerStatic as Image;
use Symfony\Component\HttpFoundation\Response;
use GemeenteAmsterdam\BrievenhulpBundle\Form\Model\MetadataFormModel;
use GemeenteAmsterdam\BrievenhulpBundle\Form\Type\MetadataFormType;
use GemeenteAmsterdam\BrievenhulpBundle\Form\Model\ReassignHulpverlenerFormModel;
use GemeenteAmsterdam\BrievenhulpBundle\Form\Model\HulpverlenerFormModel;
use GemeenteAmsterdam\BrievenhulpBundle\Form\Type\HulpverlenerEditFormType;
use GemeenteAmsterdam\BrievenhulpBundle\Form\Type\HulpverlenerFormType;
use GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpverlener;

class HulpverlenerController extends Controller
{
    /**
     * @Route("/hulpverlener")
     */
    public function homeAction(Request $request)
    {
        /* @var $hulpvragenRepository \GemeenteAmsterdam\BrievenhulpBundle\Entity\HulpvraagRepository */
        $hulpvragenRepository = $this->getDoctrine()->getManager()->getRepository('GemeenteAmsterdamBrievenhulpBundle:Hulpvraag');

        /* @var $hulpverlener \GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpverlener */
        $hulpverlener = $this->getUser();

        $hulpvragen = $hulpvragenRepository->search($hulpverlener, [Hulpvraag::STATUS_TOEGEWEZEN], false);

        $page = $request->query->getInt('page', 1);
        $pageSize = $request->query->getInt('pageSize', 30);
        if ($pageSize > 100) {
            $pageSize = 100;
        }
        $firstResult = ($page - 1) * $pageSize;
        $hulpvragenAfgehandeld = $hulpvragenRepository->search($hulpverlener, [Hulpvraag::STATUS_AFGEHANDELD, Hulpvraag::STATUS_GEEN_ANTWOORD, Hulpvraag::STATUS_SPAM], false, null, $firstResult, $pageSize);
        $numberOfPages = ceil($hulpvragen->count() / $pageSize);

        return $this->render('GemeenteAmsterdamBrievenhulpBundle:Hulpverlener:home.html.twig', [
            'hulpverlener' => $hulpverlener,
            'hulpvragen' => $hulpvragen,
            'hulpvragenAfgehandeld' => $hulpvragenAfgehandeld,
            'page' => $page,
            'pageSize' => $pageSize,
            'numberOfPages' => $numberOfPages,
        ]);
    }

    /**
     * @Route("/hulpverlener/profiel")
     */
    public function profileAction(Request $request)
    {
        /* @var $hulpvragenRepository \GemeenteAmsterdam\BrievenhulpBundle\Entity\HulpvraagRepository */
        $hulpvragenRepository = $this->getDoctrine()->getManager()->getRepository('GemeenteAmsterdamBrievenhulpBundle:Hulpvraag');

        /* @var $hulpverlener \GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpverlener */
        $hulpverlener = $this->getUser();

        $data = new ProfileFormModel();
        $data->naam = $hulpverlener->getNaam();
        $data->email = $hulpverlener->getEmail();
        $data->organisatie = $hulpverlener->getOrganisatie();
        $data->telefoon = $hulpverlener->getTelefoon();
        $data->beschikbaarheid = $hulpverlener->getBeschikbaarheid();
        $data->smssjabloon = $hulpverlener->getSmsSjabloon();
        $data->updatesPerSms = $hulpverlener->getUpdatesPerSms();


        $form = $this->createForm(ProfileFormType::class, $data);

        $form->handleRequest($request);

        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $hulpverlener->setNaam($data->naam);
            $hulpverlener->setEmail($data->email);
            $hulpverlener->setOrganisatie($data->organisatie);
            $hulpverlener->setTelefoon($data->telefoon);
            $hulpverlener->setBeschikbaarheid($data->beschikbaarheid);
            $hulpverlener->setSmsSjabloon($data->smssjabloon);
            $hulpverlener->setUpdatesPerSms($data->updatesPerSms);


            $this->getDoctrine()->getManager()->flush($hulpverlener);

            $this->addFlash('info', 'Opgeslagen!');

            return $this->redirectToRoute('gemeenteamsterdam_brievenhulp_hulpverlener_profile');
        }

        return $this->render('GemeenteAmsterdamBrievenhulpBundle:Hulpverlener:profile.html.twig', [
            'hulpverlener' => $hulpverlener,
            'form' => $form->createView(),
            'hulpvragen' => $hulpvragenRepository->search($hulpverlener, [Hulpvraag::STATUS_TOEGEWEZEN], false),
            'hulpvragenAfgehandeld' => $hulpvragenRepository->search($hulpverlener, [Hulpvraag::STATUS_AFGEHANDELD, Hulpvraag::STATUS_GEEN_ANTWOORD, Hulpvraag::STATUS_SPAM], false, null, 0, 30),
        ]);
    }

    /**
     * @Route("/hulpverlener/overview")
     */
    public function overviewAction(Request $request)
    {
        $repo = $this->get('gemeente_amsterdam_brievenhulp.repositories.hulpverlener');
        $hulpverleners = $repo->findBy(array(
            'isActive' => true
        ));

        $stats = $this->get('gemeente_amsterdam_brievenhulp.repositories.hulpvraag')->getAantalHulpvragenPerHulpverlener();

        return $this->render('GemeenteAmsterdamBrievenhulpBundle:Hulpverlener:overview.html.twig', [
            'hulpverleners' => $hulpverleners,
            'stats' => $stats
        ]);
    }

    /**
     * @Route("/hulpverlener/brieven")
     */
    public function brievenAction(Request $request)
    {
        /* @var $hulpvragenRepository \GemeenteAmsterdam\BrievenhulpBundle\Entity\HulpvraagRepository */
        $hulpvragenRepository = $this->getDoctrine()->getManager()->getRepository('GemeenteAmsterdamBrievenhulpBundle:Hulpvraag');
        /* @var $hulpverlener \GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpverlener */
        $hulpverlener = $this->getUser();

        $auditLog = new AuditLogEntry(new \DateTime(), $request->attributes->get('_route'), 'view', $request->getClientIp(), $request->headers->get('referer'), $hulpverlener, null);
        $this->getDoctrine()->getManager()->persist($auditLog);
        $this->getDoctrine()->getManager()->flush();

        $page = $request->query->get('page');
        if (null === $page) {
            $page = 1;
        }

        $q = $request->query->get('q', null);
        $page = $request->query->getInt('page', 1);
        $pageSize = $request->query->getInt('pageSize', 35);
        if ($pageSize > 100) {
            $pageSize = 100;
        }
        $firstResult = ($page - 1) * $pageSize;
        $sortColumn = $request->query->get('sortColumn', 'datum');
        $sortDirection = $request->query->get('sortDirection', 'DESC');
        $hulpvragen = $hulpvragenRepository->search(null, null, false, $q, $firstResult, $pageSize, $sortColumn, $sortDirection);
        $numberOfPages = ceil($hulpvragen->count() / $pageSize);

        return $this->render('GemeenteAmsterdamBrievenhulpBundle:Hulpverlener:brieven.html.twig', [
            'hulpvragen' => $hulpvragen,
            'page' => $page,
            'pageSize' => $pageSize,
            'numberOfPages' => $numberOfPages,
            'sortColumn' => $sortColumn,
            'sortDirection' => $sortDirection,
            'q' => $q
        ]);
    }

    /**
     * @Route("/hulpverlener/brieven/archief")
     */
    public function archiefAction(Request $request)
    {
        /* @var $hulpvragenRepository \GemeenteAmsterdam\BrievenhulpBundle\Entity\HulpvraagRepository */
        $hulpvragenRepository = $this->getDoctrine()->getManager()->getRepository('GemeenteAmsterdamBrievenhulpBundle:Hulpvraag');
        /* @var $hulpverlener \GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpverlener */
        $hulpverlener = $this->getUser();

        $auditLog = new AuditLogEntry(new \DateTime(), $request->attributes->get('_route'), 'view', $request->getClientIp(), $request->headers->get('referer'), $hulpverlener, null);
        $this->getDoctrine()->getManager()->persist($auditLog);
        $this->getDoctrine()->getManager()->flush();

        $page = $request->query->getInt('page', 1);
        $pageSize = $request->query->getInt('pageSize', 30);
        if ($pageSize > 100) {
            $pageSize = 100;
        }
        $firstResult = ($page - 1) * $pageSize;
        $sortColumn = $request->query->get('sortColumn', 'datum');
        $sortDirection = $request->query->get('sortDirection', 'DESC');
        $hulpvragen = $hulpvragenRepository->search(null, [], true, null, $firstResult, $pageSize, $sortColumn, $sortDirection);
        $numberOfPages = ceil($hulpvragen->count() / $pageSize);

        return $this->render('GemeenteAmsterdamBrievenhulpBundle:Hulpverlener:archief.html.twig', [
            'hulpvragen' => $hulpvragen,
            'page' => $page,
            'pageSize' => $pageSize,
            'numberOfPages' => $numberOfPages,
            'sortColumn' => $sortColumn,
            'sortDirection' => $sortDirection,
        ]);
    }

    /**
     * @Route("/hulpverlener/hulpvraag/{hulpvraagUuid}/submit")
     */
    public function submitCompleteAction(Request $request, $hulpvraagUuid)
    {
        /* @var $hulpvraag \GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpvraag */
        $hulpvraag = $this->getDoctrine()->getManager()->getRepository('GemeenteAmsterdamBrievenhulpBundle:Hulpvraag')->find($hulpvraagUuid);
        if ($hulpvraag === null)
            throw $this->createNotFoundException('Can not find "Hulpvraag"');

        /* @var $hulpverlener \GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpverlener */
        $hulpverlener = $this->getUser();

        // audit
        $auditLog = new AuditLogEntry(new \DateTime(), $request->attributes->get('_route'), 'view', $request->getClientIp(), $request->headers->get('referer'), $hulpverlener, $hulpvraag);
        $this->getDoctrine()->getManager()->persist($auditLog);

        $form = $this->createForm(SubmitCompleteFormType::class);
        $form->handleRequest($request);
        if ($hulpvraag->getToegewezenHulpverlener() === $hulpverlener && $form->isSubmitted() === true && $form->isValid() === true) {
            $data = $form->getData();
            $auditLog->setActie('update');
            $auditLog->setData('newStatus', Hulpvraag::STATUS_AFGEHANDELD);
            $hulpvraag->setStatus(Hulpvraag::STATUS_AFGEHANDELD);
            $hulpvraag->setEindDatumTijd(new \DateTime());
            $hulpvraag->setReactieVanHulpverlener($data['msg']);
            $this->getDoctrine()->getManager()->flush();

            $referentie = $hulpvraag->getReferentie();
            if (null !== $referentie) {
                $this->addFlash('info', 'Brief ' . $referentie->getId() . ' gemarkeerd als afgehandeld');
            } else {
                $this->addFlash('info', 'Brief gemarkeerd als afgehandeld');
            }

            // flush all (audit log)
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('gemeenteamsterdam_brievenhulp_hulpverlener_home', []);
        }

        // flush all (audit log)
        $this->getDoctrine()->getManager()->flush();

        return $this->render('GemeenteAmsterdamBrievenhulpBundle:Hulpverlener:submitComplete.html.twig', [
            'hulpvraag' => $hulpvraag,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/hulpverlener/hulpvraag/{hulpvraagUuid}/delete")
     */
    public function deleteCompleteAction(Request $request, $hulpvraagUuid)
    {
        /* @var $hulpvraag \GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpvraag */
        $hulpvraag = $this->getDoctrine()->getManager()->getRepository('GemeenteAmsterdamBrievenhulpBundle:Hulpvraag')->find($hulpvraagUuid);
        if ($hulpvraag === null)
            throw $this->createNotFoundException('Can not find "Hulpvraag"');

        /* @var $hulpverlener \GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpverlener */
        $hulpverlener = $this->getUser();

        // audit
        $auditLog = new AuditLogEntry(new \DateTime(), $request->attributes->get('_route'), 'view', $request->getClientIp(), $request->headers->get('referer'), $hulpverlener, $hulpvraag);
        $this->getDoctrine()->getManager()->persist($auditLog);

        $form = $this->createForm(SubmitDeleteFormType::class);
        $form->handleRequest($request);
        if ($hulpvraag->getToegewezenHulpverlener() === $hulpverlener && $form->isSubmitted() === true && $form->isValid() === true) {
            $data = $form->getData();
            $auditLog->setActie('update');
            $auditLog->setData('newStatus', Hulpvraag::STATUS_SPAM);
            $hulpvraag->setStatus(Hulpvraag::STATUS_SPAM);
            $hulpvraag->setEindDatumTijd(new \DateTime());
            $hulpvraag->setReactieVanHulpverlener($data['msg']);
            $this->getDoctrine()->getManager()->flush();

            $referentie = $hulpvraag->getReferentie();
            if (null !== $referentie) {
                $this->addFlash('info', 'Brief ' . $hulpvraag->getReferentie()->getId() . ' verwijderd');
            } else {
                $this->addFlash('info', 'Brief verwijderd');
            }

            // flush all (audit log)
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('gemeenteamsterdam_brievenhulp_hulpverlener_home', []);
        }

        // flush all (audit log)
        $this->getDoctrine()->getManager()->flush();

        return $this->render('GemeenteAmsterdamBrievenhulpBundle:Hulpverlener:submitDelete.html.twig', [
            'hulpvraag' => $hulpvraag,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/hulpverlener/hulpvraag/{hulpvraagUuid}/stuur-sms")
     */
    public function sendSmsAction(Request $request, $hulpvraagUuid)
    {
        /* @var $hulpvraag \GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpvraag */
        $hulpvraag = $this->getDoctrine()->getManager()->getRepository('GemeenteAmsterdamBrievenhulpBundle:Hulpvraag')->find($hulpvraagUuid);
        if ($hulpvraag === null)
            throw $this->createNotFoundException('Can not find "Hulpvraag"');

            /* @var $hulpverlener \GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpverlener */
            $hulpverlener = $this->getUser();

            $data = new SendSmsFormModel();
            $data->msg = $hulpverlener->getSmsSjabloon();
            $form = $this->createForm(SendSmsFormType::class, $data);
            $form->handleRequest($request);
            if ($hulpvraag->getToegewezenHulpverlener() === $hulpverlener && $form->isSubmitted() === true && $form->isValid() === true) {
                $verzondenSmsBericht = new VerzondenSmsBericht();
                $verzondenSmsBericht->setBericht($data->msg);
                $verzondenSmsBericht->setHulpverlener($this->getUser());
                $verzondenSmsBericht->setHulpvraag($hulpvraag);

                $auditLog = new AuditLogEntry(new \DateTime(), $request->attributes->get('_route'), 'update', $request->getClientIp(), $request->headers->get('referer'), $hulpverlener, $hulpvraag);
                $this->getDoctrine()->getManager()->persist($auditLog);

                $hulpvraag->setStatus(Hulpvraag::STATUS_GEEN_ANTWOORD);
                $hulpvraag->setEindDatumTijd(new \DateTime());
                $auditLog->setData('newStatus', Hulpvraag::STATUS_GEEN_ANTWOORD);

                if ($this->container->getParameter('messagebird_enable') === true) {
                    $sms = new \MessageBird\Objects\Message();
                    $sms->originator = $this->getParameter('messagebird_sms_originator');
                    $sms->recipients = array($hulpvraag->getTelefoon());
                    $sms->body = $data->msg;
                    $this->get('messagebird')->messages->create($sms);
                }

                $this->getDoctrine()->getManager()->persist($verzondenSmsBericht);
                $this->getDoctrine()->getManager()->flush();

                $this->addFlash('info', 'Bericht verstuurd naar ' . $hulpvraag->getTelefoon());

                return new Response('', Response::HTTP_NO_CONTENT);
            }

            return $this->render('GemeenteAmsterdamBrievenhulpBundle:Hulpverlener:sendSms.html.twig', [
                'hulpvraag' => $hulpvraag,
                'hulpverlener' => $hulpverlener,
                'form' => $form->createView()
            ]);
    }

    /**
     * @Route("/hulpverlener/hulpvraag/{hulpvraagUuid}")
     */
    public function detailAction(Request $request, $hulpvraagUuid)
    {
        /* @var $afzenderRepository \GemeenteAmsterdam\BrievenhulpBundle\Entity\AfzenderRepository */
        $afzenderRepository = $this->get('gemeente_amsterdam_brievenhulp.repositories.afzender');
        /* @var $tagRepository \GemeenteAmsterdam\BrievenhulpBundle\Entity\TagRepository */
        $tagRepository = $this->get('gemeente_amsterdam_brievenhulp.repositories.tag');
        /* @var $hulpvraagRepository \GemeenteAmsterdam\BrievenhulpBundle\Entity\HulpvraagRepository */
        $hulpvraagRepository = $this->get('gemeente_amsterdam_brievenhulp.repositories.hulpvraag');

        $hulpvraag = $hulpvraagRepository->find($hulpvraagUuid);
        if ($hulpvraag === null) {
            throw $this->createNotFoundException('Can not find "Hulpvraag"');
        }

        /* @var $hulpverlener \GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpverlener */
        $hulpverlener = $this->getUser();

        // voer audit loging uit
        $auditLog = new AuditLogEntry(new \DateTime(), $request->attributes->get('_route'), 'view', $request->getClientIp(), $request->headers->get('referer'), $hulpverlener, $hulpvraag);
        $this->getDoctrine()->getManager()->persist($auditLog);

        // als eerste toegewezen hulpverlener is die hulpvraag opent, sla dit op
        if ($hulpvraag->getEersteKeerGeopendDatumTijd() === null) {
            if ($hulpvraag->getToegewezenHulpverlener() === $hulpverlener) {
                $hulpvraag->setEersteKeerGeopendDatumTijd(new \DateTime());
                $this->getDoctrine()->getManager()->flush();
            }
        }

        // reassign form
        $reassignData = new ReassignHulpverlenerFormModel();
        $reassignForm = $this->createForm(ReassignHulpvraagFormType::class, $reassignData);
        $reassignForm->handleRequest($request);
        if ($reassignForm->isSubmitted() && $reassignForm->isValid() && $hulpvraag->getStatus() < Hulpvraag::STATUS_AFGEHANDELD) {
            $nieuweHulpverlener = $reassignData->hulpverlener;
            $vorigeHulpverlener = $hulpvraag->getToegewezenHulpverlener();

            // niks doen bij geen verandering
            if ($nieuweHulpverlener === $vorigeHulpverlener) {
                $this->getDoctrine()->getManager()->flush(); // flush for audit log
                return $this->redirectToRoute('gemeenteamsterdam_brievenhulp_hulpverlener_detail', ['hulpvraagUuid'=>$hulpvraag->getUuid()]);
            }

            // stel nieuwe hulpverlener in en verander status altijd naar toegewezen
            $hulpvraag->setStatus(Hulpvraag::STATUS_TOEGEWEZEN);
            $hulpvraag->setToegewezenHulpverlener($nieuweHulpverlener);

            // maak historie record
            $toewijzing = new HulpverlenerToewijzing();
            $toewijzing->setDatumtijd(new \DateTime());
            $toewijzing->setHulpverlener($nieuweHulpverlener);
            $toewijzing->setHulpvraag($hulpvraag);
            $toewijzing->setInfo('Handmatige toewijzing door ' . $hulpverlener->getNaam() . ' van ' . $hulpverlener->getOrganisatie());
            $toewijzing->setBericht($reassignData->bericht);
            $this->getDoctrine()->getManager()->persist($toewijzing);

            // audit logging bijwerken
            $auditLog->setData('assign-to', $nieuweHulpverlener->getId());

            // email + sms template handling
            $context = [
                'hulpvraag' => $hulpvraag,
                'hulpverlener' => $hulpverlener,
                'nieuweHulpverlener' => $nieuweHulpverlener,
                'vorigeHulpverlener' => $vorigeHulpverlener,
                'toewijzing' => $toewijzing
            ];

            // stuur notificties naar nieuwe hulpverlener als hij/zij niet de gebruiker is die de reassign uitvoert
            if ($nieuweHulpverlener !== $hulpverlener) {
                $message = \Swift_Message::newInstance();
                $message->setTo($nieuweHulpverlener->getEmail(), $nieuweHulpverlener->getNaam());
                $message->setFrom($this->container->getParameter('mail_from'));
                $message->setReplyTo($hulpverlener->getEmail(), $hulpverlener->getNaam());
                $message->setSubject($this->renderView('GemeenteAmsterdamBrievenhulpBundle:emails:reassignManualNew.subject.twig', $context));
                $message->setBody($this->renderView('GemeenteAmsterdamBrievenhulpBundle:emails:reassignManualNew.html.twig', $context), 'text/html');
                $message->addPart($this->renderView('GemeenteAmsterdamBrievenhulpBundle:emails:reassignManualNew.txt.twig', $context), 'text/plain');
                $this->container->get('mailer')->send($message);

                // stuur sms als dit aanstaat
                if ($this->container->getParameter('messagebird_enable') === true
                    && $nieuweHulpverlener->getUpdatesPerSms() === true
                    && $nieuweHulpverlener->getTelefoon() !== ''
                    && $nieuweHulpverlener->getTelefoon() !== null) {
                        $sms = new \MessageBird\Objects\Message();
                        $sms->originator = $this->container->getParameter('messagebird_sms_originator');
                        $sms->recipients = array($nieuweHulpverlener->getTelefoon());
                        $sms->body = $this->renderView('GemeenteAmsterdamBrievenhulpBundle:sms:reassignManualNew.txt.twig', $context);
                        $this->get('messagebird')->messages->create($sms);
                }
            }

            // stuur notificties naar oude hulpverlener als hij/zij niet de gebruiker is die de reassign uitvoert
            if ($vorigeHulpverlener !== $hulpverlener && $vorigeHulpverlener !== null) {
                // stuur oude hulpverlener notificatie want dat is niet de gebruiker die dit wijzigt
                $message = \Swift_Message::newInstance();
                $message->setTo($vorigeHulpverlener->getEmail(), $vorigeHulpverlener->getNaam());
                $message->setFrom($this->container->getParameter('mail_from'));
                $message->setReplyTo($hulpverlener->getEmail(), $hulpverlener->getNaam());
                $message->setSubject($this->renderView('GemeenteAmsterdamBrievenhulpBundle:emails:reassignManualOld.subject.twig', $context));
                $message->setBody($this->renderView('GemeenteAmsterdamBrievenhulpBundle:emails:reassignManualOld.html.twig', $context), 'text/html');
                $message->addPart($this->renderView('GemeenteAmsterdamBrievenhulpBundle:emails:reassignManualOld.txt.twig', $context), 'text/plain');
                $this->container->get('mailer')->send($message);

                if ($this->container->getParameter('messagebird_enable') === true
                    && $vorigeHulpverlener->getUpdatesPerSms() === true
                    && $vorigeHulpverlener->getTelefoon() !== ''
                    && $vorigeHulpverlener->getTelefoon() !== null) {
                        $sms = new \MessageBird\Objects\Message();
                        $sms->originator = $this->container->getParameter('messagebird_sms_originator');
                        $sms->recipients = array($vorigeHulpverlener->getTelefoon());
                        $sms->body = $this->renderView('GemeenteAmsterdamBrievenhulpBundle:sms:reassignManualOld.txt.twig', $context);
                        $this->get('messagebird')->messages->create($sms);
                }
            }

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('info', 'Brief ' . $hulpvraag->getReferentie()->getId() . ' opnieuw toegewezen');
            return $this->redirectToRoute('gemeenteamsterdam_brievenhulp_hulpverlener_detail', ['hulpvraagUuid'=>$hulpvraag->getUuid()]);
        }

        // meta data handler
        $metadataFormModel = new MetadataFormModel($hulpvraag->getAfzender(), $hulpvraag->getTags());
        $metadataForm = $this->createForm(MetadataFormType::class, $metadataFormModel);
        $metadataForm->handleRequest($request);
        if ($metadataForm->isSubmitted() && $metadataForm->isValid()) {
            $hulpvraag->setAfzender($afzenderRepository->getOrCreate($metadataFormModel->afzender));
            $hulpvraag->clearTags();
            $metadataFormModel->tags !== null ? $metadataFormModel->tags : ''; // null to empty string
            $tags = array_filter(array_map('trim', explode(',', $metadataFormModel->tags)), function ($string) {
                return $string !== '';
            });
            foreach ($tags as $tag) {
                $hulpvraag->addTag($tagRepository->getOrCreate($tag));
            }
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('info', 'Metadata opgeslagen');
            return $this->redirectToRoute('gemeenteamsterdam_brievenhulp_hulpverlener_detail', ['hulpvraagUuid'=>$hulpvraag->getUuid()]);
        }
        $afzenders = $afzenderRepository->findAll();
        $tags = $tagRepository->findAll();

        // flush all (audit log)
        $this->getDoctrine()->getManager()->flush();

        return $this->render('GemeenteAmsterdamBrievenhulpBundle:Hulpverlener:detailOntvanger.html.twig', [
            'hulpvraag' => $hulpvraag,
            'hulpverlener' => $hulpverlener,
            'reassignForm' => $reassignForm->createView(),
            'metadataForm' => $metadataForm->createView(),
            'afzenders' => $afzenders,
            'tags' => $tags,
        ]);
    }

    /**
     * @Route("/hulpverlener/hulpvraagBrief/{hulpvraagUuid}/{size}")
     */
    public function openBriefAction(Request $request, $hulpvraagUuid, $size = 'default')
    {
        /* @var $hulpvraag \GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpvraag */
        $hulpvraag = $this->getDoctrine()->getManager()->getRepository('GemeenteAmsterdamBrievenhulpBundle:Hulpvraag')->find($hulpvraagUuid);

        if ($hulpvraag === null)
            throw $this->createNotFoundException('Can not find "Hulpvraag"');

        /* @var $hulpverlener \GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpverlener */
        $hulpverlener = $this->getUser();

        if ($hulpverlener === null)
            throw $this->createNotFoundException('Can not find "Hulpverlener"');

        $fullFile = $this->container->getParameter('kernel.data_dir') . DIRECTORY_SEPARATOR . $hulpvraag->getBestandsnaam();

        $file = null;
        switch ($size)
        {
            case 'thumb-small':
                $dotPosition = strrpos($hulpvraag->getBestandsnaam(), '.');
                $fileName = substr($hulpvraag->getBestandsnaam(), 0, $dotPosition) . '_' . $size . substr($hulpvraag->getBestandsnaam(), $dotPosition);
                $file = $this->container->getParameter('kernel.data_dir') . DIRECTORY_SEPARATOR . $fileName;
                if (file_exists($file) === false) {
                    $imagine = new \Imagine\Gd\Imagine();
                    $size = new \Imagine\Image\Box(160, 160);
                    $mode = \Imagine\Image\ImageInterface::THUMBNAIL_INSET;
                    $imagine->open($fullFile)
                        ->thumbnail($size, $mode)
                        ->save($file);
                }
                break;
            case 'thumb-large':
                $dotPosition = strrpos($hulpvraag->getBestandsnaam(), '.');
                $fileName = substr($hulpvraag->getBestandsnaam(), 0, $dotPosition) . '_' . $size . substr($hulpvraag->getBestandsnaam(), $dotPosition);
                $file = $this->container->getParameter('kernel.data_dir') . DIRECTORY_SEPARATOR . $fileName;
                if (file_exists($file) === false) {
                    $imagine = new \Imagine\Gd\Imagine();
                    $size = new \Imagine\Image\Box(1000, 1000);
                    $mode = \Imagine\Image\ImageInterface::THUMBNAIL_INSET;
                    $imagine->open($fullFile)
                        ->thumbnail($size, $mode)
                        ->save($file);
                }
                break;
            case 'square':
                $dotPosition = strrpos($hulpvraag->getBestandsnaam(), '.');
                $fileName = substr($hulpvraag->getBestandsnaam(), 0, $dotPosition) . '_' . $size . substr($hulpvraag->getBestandsnaam(), $dotPosition);
                $file = $this->container->getParameter('kernel.data_dir') . DIRECTORY_SEPARATOR . $fileName;
                if (file_exists($file) === false) {
                    $img = Image::make($fullFile);
                    $img->fit(250);
                    $img->save($file);
                }
                break;
            case 'default':
            default:
                $file = $fullFile;
        }

        return new BinaryFileResponse($file);
    }

    /**
     * @Route("/hulpverlener/login")
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('GemeenteAmsterdamBrievenhulpBundle:Hulpverlener:login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/hulpverlener/logout")
     */
    public function logoutAction()
    {
        //
    }

    /**
     * @Route("/hulpverlener/{hulpverlenerId}/auditLog/{date}")
     */
    public function showAuditLogAction(Request $request, $hulpverlenerId, $date = null)
    {
        /* @var $hulpverlenerRepository \GemeenteAmsterdam\BrievenhulpBundle\Entity\HulpverlenerRepository */
        $hulpverlenerRepository = $this->getDoctrine()->getManager()->getRepository('GemeenteAmsterdamBrievenhulpBundle:Hulpverlener');
        /* @var $auditLogEntryRepository \GemeenteAmsterdam\BrievenhulpBundle\Entity\AuditLogEntryRepository */
        $auditLogEntryRepository = $this->getDoctrine()->getManager()->getRepository('GemeenteAmsterdamBrievenhulpBundle:AuditLogEntry');
        /* @var $hulpverlener \GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpverlener */
        $hulpverlener = $this->getUser();

        $auditLog = new AuditLogEntry(new \DateTime(), $request->attributes->get('_route'), 'view', $request->getClientIp(), $request->headers->get('referer'), $hulpverlener, null);
        $auditLog->setData('hulpverlenerId', $hulpverlenerId);
        $auditLog->setData('date', $date);
        $this->getDoctrine()->getManager()->persist($auditLog);
        $this->getDoctrine()->getManager()->flush();

        // get data
        $subject = $hulpverlenerRepository->find($hulpverlenerId);
        if ($subject === null)
            throw $this->createNotFoundException('Hulpverlener unknown');
        if ($date === null)
            $date = date('Y-m-d');
        $date = new \DateTime($date);
        $auditLogEntries = $auditLogEntryRepository->findBySubjectAndDate($subject, $date);

        $nextDate = clone $date;
        $nextDate->add(new \DateInterval('P1D'));
        $prevDate = clone $date;
        $prevDate->sub(new \DateInterval('P1D'));

        return $this->render('GemeenteAmsterdamBrievenhulpBundle:Hulpverlener:showAuditLog.html.twig', [
            'subject' => $subject,
            'date' => $date,
            'nextDate' => $nextDate,
            'prevDate' => $prevDate,
            'auditLogEntries' => $auditLogEntries,
        ]);
    }

    /**
     * @Route("/hulpverlener/accounts")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function accountIndexAction(Request $request)
    {
        /* @var $hulpverlenerRepository \GemeenteAmsterdam\BrievenhulpBundle\Entity\HulpverlenerRepository */
        $hulpverlenerRepository = $this->get('gemeente_amsterdam_brievenhulp.repositories.hulpverlener');
        $hulpverleners = $hulpverlenerRepository->findAll();

        return $this->render('GemeenteAmsterdamBrievenhulpBundle:Hulpverlener:accountIndex.html.twig', [
            'hulpverleners' => $hulpverleners
        ]);
    }

    /**
     * @Route("/hulpverlener/accounts/create")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function accountCreateAction(Request $request)
    {
        $hulpverlener = new Hulpverlener();
        $data = new HulpverlenerFormModel();
        $data->isActive = true;

        $form = $this->createForm(HulpverlenerFormType::class, $data);

        $form->handleRequest($request);

        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $hulpverlener->setNaam($data->naam);
            $hulpverlener->setEmail($data->email);
            $hulpverlener->setOrganisatie($data->organisatie);
            $hulpverlener->setTelefoon($data->telefoon);
            $hulpverlener->setBeschikbaarheid($data->beschikbaarheid);
            $hulpverlener->setRole($data->role);
            $hulpverlener->setIsActive($data->isActive);
            $hulpverlener->setSmsSjabloon('Wij hebben u vandaag gebeld. We konden u echter niet bereiken. Vriendelijke groet, Snap de Brief (U kunt niet reageren op dit bericht). We called you today. But we couldn\'t reach you. Kind regards, Snap de Brief (You cannot reply to this message)');
            $hulpverlener->setUpdatesPerSms($data->updatesPerSms);

            $hulpverlener->setSecret(md5(time() . microtime(true) . uniqid(null, true)));
            $encoder = $this->get('security.password_encoder');
            $encoded = $encoder->encodePassword($hulpverlener, $data->password);
            $hulpverlener->setPassword($encoded);

            $this->getDoctrine()->getManager()->persist($hulpverlener);
            try {
                $this->getDoctrine()->getManager()->flush($hulpverlener);
            } catch (\Exception $e) {
                $error = new FormError('Gebruikersnaam al in gebruik');
                $form->get('email')->addError($error);
                return $this->render('GemeenteAmsterdamBrievenhulpBundle:Hulpverlener:accountCreate.html.twig', [
                    'form' => $form->createView()
                ]);
            }

            $this->addFlash('info', 'Opgeslagen!');

            return $this->redirectToRoute('gemeenteamsterdam_brievenhulp_hulpverlener_accountindex');
        }

        return $this->render('GemeenteAmsterdamBrievenhulpBundle:Hulpverlener:accountCreate.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/hulpverlener/accounts/edit/{id}")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function accountEditAction(Request $request, $id)
    {
        /* @var $hulpverlenerRepository \GemeenteAmsterdam\BrievenhulpBundle\Entity\HulpverlenerRepository */
        $hulpverlenerRepository = $this->get('gemeente_amsterdam_brievenhulp.repositories.hulpverlener');

        $hulpverlener = $hulpverlenerRepository->find($id);
        if (null === $hulpverlener) {
            throw new \Exception('Onbekende hulpverlener');
        }

        $data = new HulpverlenerFormModel();
        $data->beschikbaarheid = $hulpverlener->getBeschikbaarheid();
        $data->email           = $hulpverlener->getEmail();
        $data->isActive        = $hulpverlener->getIsActive();
        $data->naam            = $hulpverlener->getNaam();
        $data->telefoon        = $hulpverlener->getTelefoon();
        $data->organisatie     = $hulpverlener->getOrganisatie();
        $data->role            = $hulpverlener->getRole();
        $data->updatesPerSms   = $hulpverlener->getUpdatesPerSms();

        $form = $this->createForm(HulpverlenerEditFormType::class, $data);

        $form->handleRequest($request);

        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $hulpverlener->setNaam($data->naam);
            $hulpverlener->setEmail($data->email);
            $hulpverlener->setOrganisatie($data->organisatie);
            $hulpverlener->setTelefoon($data->telefoon);
            $hulpverlener->setBeschikbaarheid($data->beschikbaarheid);
            $hulpverlener->setRole($data->role);
            $hulpverlener->setIsActive($data->isActive);
            $hulpverlener->setUpdatesPerSms($data->updatesPerSms);

            if (strlen($data->password)) {
                $hulpverlener->setSecret(md5(time() . microtime(true) . uniqid(null, true)));
                $encoder = $this->get('security.password_encoder');
                $encoded = $encoder->encodePassword($hulpverlener, $data->password);
                $hulpverlener->setPassword($encoded);
            }

            $this->getDoctrine()->getManager()->persist($hulpverlener);
            try {
                $this->getDoctrine()->getManager()->flush($hulpverlener);
            } catch (\Exception $e) {
                $error = new FormError('Gebruikersnaam al in gebruik');
                $form->get('email')->addError($error);
                return $this->render('GemeenteAmsterdamBrievenhulpBundle:Hulpverlener:accountEdit.html.twig', [
                    'form' => $form->createView()
                ]);
            }

            $this->addFlash('info', 'Opgeslagen!');

            return $this->redirectToRoute('gemeenteamsterdam_brievenhulp_hulpverlener_accountindex');
        }

        return $this->render('GemeenteAmsterdamBrievenhulpBundle:Hulpverlener:accountEdit.html.twig', [
            'form' => $form->createView()
        ]);
    }

}
