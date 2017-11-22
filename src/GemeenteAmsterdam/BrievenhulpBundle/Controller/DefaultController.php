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

use GemeenteAmsterdam\BrievenhulpBundle\Entity\Referentie;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use GemeenteAmsterdam\BrievenhulpBundle\Form\Type\SubmitRequestFormType;
use GemeenteAmsterdam\BrievenhulpBundle\Form\Model\SubmitRequestFormModel;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use GemeenteAmsterdam\BrievenhulpBundle\Entity\Hulpvraag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Debug\Exception\FlattenException;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function languageSelectAction(Request $request)
    {
        $bestLocale = $request->getPreferredLanguage(['nl', 'en']);
        return $this->redirectToRoute('gemeenteamsterdam_brievenhulp_default_index', ['_locale' => $bestLocale]);
    }

    /**
     * @Route(
     *  "/{_locale}",
     *  requirements={"_locale"="en|nl"}
     * )
     */
    public function indexAction(Request $request, $_locale)
    {
        $data = new SubmitRequestFormModel();
        $data->methode = 'telefoon';
        $data->taal = $_locale;

        $form = $this->createForm(SubmitRequestFormType::class, $data, []);

        $uploadStyle = Hulpvraag::UPLOADSTYLE_CLASSIC;
        // rewrite request
        if ($request->request->has($form->getName()) === true) { // form is submitted
            $postData = $request->request->get($form->getName());
            if (isset($postData['foto']) === true) { // submitted via base64
                $uploadStyle = Hulpvraag::UPLOADSTYLE_BASE64;

                // data:image/png;base64,
                $encodedDataStartPos = strpos($postData['foto'], ',');
                $metaData = explode(';', substr($postData['foto'], 0, $encodedDataStartPos));
                $encodedData = substr($postData['foto'], $encodedDataStartPos + 1);

                $mimeType = 'application/bin';
                $originalName = 'foto.bin';
                switch ($metaData[0]) {
                    case 'data:image/jpeg': $mimeType = 'image/jpeg'; $originalName = 'foto.jpg'; break;
                    case 'data:image/jpg': $mimeType = 'image/jpeg'; $originalName = 'foto.jpg'; break;
                    case 'data:image/png': $mimeType = 'image/png'; $originalName = 'foto.png'; break;
                    case 'data:application/pdf': $mimeType = 'application/pdf'; $originalName = 'foto.pdf'; break;
                }
                $tempFile = tempnam(sys_get_temp_dir(), 'brievenhulp_upload_');
                file_put_contents($tempFile, base64_decode($encodedData));
                $size = filesize($tempFile);

                $request->files->set($form->getName(), ['foto' => new \GemeenteAmsterdam\BrievenhulpBundle\HttpFoundation\UploadedFile($tempFile, $originalName, $mimeType, $size, \UPLOAD_ERR_OK, false)]);
                unset($postData['foto']);
                $request->request->set($form->getName(), $postData);
            }
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $hulpvraag = new Hulpvraag();
            $hulpvraag->setEmail($data->emailadres);
            $hulpvraag->setInkomstDatumTijd(new \DateTime());
            $hulpvraag->setMethode($data->methode);
            $hulpvraag->setTelefoon($data->telefoon);
            $hulpvraag->setVraag($data->vraag);
            $hulpvraag->setTaal($data->taal);
            $hulpvraag->setSecret(sha1(json_encode(get_object_vars($data)) . time() . $this->container->getParameter('secret')));
            $hulpvraag->setInterfaceTaal($_locale);
            $hulpvraag->setStatus(Hulpvraag::STATUS_NIETTOEGEWEZEN);
            $hulpvraag->setUploadStyle($uploadStyle);

            $this->getDoctrine()->getManager()->persist($hulpvraag);


            $referentie = new Referentie();
            $this->getDoctrine()->getManager()->persist($referentie);
            $referentie->setHulpvraag($hulpvraag);
            $hulpvraag->setReferentie($referentie);
            $this->getDoctrine()->getManager()->flush($hulpvraag);


            // find the best extension
            $ext = 'bin';
            if ($form['foto']->getData()->getMimeType() === 'image/jpg' || $form['foto']->getData()->getMimeType() === 'image/jpeg')
                $ext = 'jpg';
            if ($form['foto']->getData()->getMimeType() === 'image/png')
                $ext = 'png';
            if ($form['foto']->getData()->getMimeType() === 'application/pdf')
                $ext = 'pdf';

            // generate file and directory names
            $prefixDirectory = date('Y-m');
            $fileName = $hulpvraag->getUuid() . '.' . $ext;
            $relativePath = $prefixDirectory . DIRECTORY_SEPARATOR . $fileName;
            $targetDirectory = $this->container->getParameter('kernel.data_dir') . DIRECTORY_SEPARATOR . $prefixDirectory;

            // move foto to the target location
            /* @var $foto UploadedFile */
            $foto = $form['foto']->getData();
            $mimeType = $foto->getMimeType();
            $foto->move($targetDirectory, $fileName);

            // add filename to database
            $hulpvraag->setBestandsnaam($relativePath);
            $this->getDoctrine()->getManager()->flush($hulpvraag);

            // send cc
            $message = \Swift_Message::newInstance()
                ->setSubject('[' . $this->container->getParameter('router.request_context.host') . '] Hulpvraag ingediend - ' . $hulpvraag->getInkomstDatumTijd()->format('c'))
                ->setFrom($this->container->getParameter('mail_from'))
                ->setTo($this->container->getParameter('mail_cc'))
                ->setBody($this->generateUrl('gemeenteamsterdam_brievenhulp_hulpverlener_detail', ['hulpvraagUuid' => $hulpvraag->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL),'text/plain');
            $this->get('mailer')->send($message);

            return $this->render('GemeenteAmsterdamBrievenhulpBundle:Default:thanks.html.twig', [
                'data' => $data,
                'referentie' => $referentie
            ]);
        }

        return $this->render('GemeenteAmsterdamBrievenhulpBundle:Default:index.html.twig', [
            'form' => $form->createView(),
            'data' => $data
        ]);
    }

    /**
     * @Route("/disclaimer")
     */
    public function disclaimerLanguageSelectAction(Request $request)
    {
        $bestLocale = $request->getPreferredLanguage(['nl', 'en']);
        return $this->redirectToRoute('gemeenteamsterdam_brievenhulp_default_disclaimer', ['_locale' => $bestLocale]);
    }

    /**
     * @Route(
     *  "/{_locale}/disclaimer",
     *  requirements={"_locale"="en|nl"}
     * )
     */
    public function disclaimerAction(Request $request)
    {
        return $this->render('GemeenteAmsterdamBrievenhulpBundle:Default:disclaimer.html.twig');
    }

    /**
     * @Route("/faq")
     */
    public function faqLanguageSelectAction(Request $request)
    {
        $bestLocale = $request->getPreferredLanguage(['nl', 'en']);
        return $this->redirectToRoute('gemeenteamsterdam_brievenhulp_default_faq', ['_locale' => $bestLocale]);
    }

    /**
     * @Route(
     *  "/{_locale}/faq",
     *  requirements={"_locale"="en|nl"}
     * )
     */
    public function faqAction(Request $request, $_locale)
    {
        return $this->render('GemeenteAmsterdamBrievenhulpBundle:Default:faq.html.twig');
    }
}
