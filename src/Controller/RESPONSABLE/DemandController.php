<?php

namespace App\Controller\RESPONSABLE;

use App\Entity\Demand;
use App\Entity\Notification;
use App\Entity\SupportingDocument;
use App\Form\DemandType;
use App\Form\SupportingDocumentType;
use App\Repository\DemandRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;



/**
 * @Route("RESPONSABLE/demand")
 * @IsGranted ("ROLE_RESPONSABLE", message="vous n'etes pas autoriser dans ce espace")
 */
class DemandController extends AbstractController
{
    private $mailer;

    public function __construct()
    {
        //$this->mailer=$mailer;
    }

    /**
     * @Route("/", name="demandResp_index", methods={"GET"})
     */
    public function index(DemandRepository $demandRepository): Response
    {
        return $this->render('TO/demand/index.html.twig', [
            'demands' => $demandRepository->findBy(['user'=>$this->getUser()]),
            'demande' => $demandRepository->findBy(['user' =>$this->getUser(), 'isCancled' => false])
        ]);
    }

    /**
     * @Route("/new", name="demandResp_new", methods={"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        $congeaccepted= $this->getDoctrine()->getRepository(Demand::class)->findBy(['user' => $this->getUser(),'status' => 3] );
        $congearefused= $this->getDoctrine()->getRepository(Demand::class)->findBy(['user' => $this->getUser(),'status' => 4] );
        $demand = new Demand();
        $form = $this->createForm(DemandType::class, $demand);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $startDate = $form->get('startDate')->getData();
            $endDate = $form->get('endDate')->getData();
            $date = date_diff($startDate, $endDate);
            $day_number = (int)$date->format('%R%a') + 1;
            if ($day_number < 0) {
                $this->addFlash('subs', 'La date fin ne doit pas être antérieure à la date début');

            }
            if ($form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $demand->setUser($this->getUser());
                $demand->setStatus(1);
                $demand->setFilingDate(new \DateTime('now'));
                $demand->setIsCancled(false);
                $notif = new Notification();
                $notif->setDemand($demand);
                $notif->setCreatedAt(new \DateTime('now'));
                $notif->setIsRead(false);
                $notif->setMessage(1);
                $entityManager->persist($notif);
                $ff = $form->getData()->getSupportingDocuments();
                if (!empty ($form->getData()->getSupportingDocuments())) {

                    $supportDocument = new SupportingDocument();
                    foreach ($ff as $f) {
                        $supportDocument->setName($f->getName());
                        $supportDocument->setFilename($f->getFilename());
                        $demand->addSupportingDocument($f);
                        $f->setDemand($demand);
                        //$this->sendConfirmationEmail('mohamed.hammami.1@esprit.tn', $this->getUser()->getfirstName(), $this->getUser()->getlastName());
                    }
                    $entityManager->persist($demand);

                } else {

                    $entityManager->persist($demand);

                }
                $entityManager->flush();

                return $this->redirectToRoute('demandResp_index');
            }
        }

        return $this->render('TO/demand/new.html.twig', [
            'accepted' => $congeaccepted,
            'refused' => $congearefused,
            'demand' => $demand,
            'form' => $form->createView(),
            'edit' => false,

        ]);
    }

    /**
     * @Route("/{id}", name="resp_demand_show", methods={"GET"})
     */
    public function show(Demand $demand): Response
    {
        return $this->render('TO/demand/show.html.twig', [
            'demand' => $demand,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="demandResp_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Demand $demand): Response
    {
        $congeaccepted= $this->getDoctrine()->getRepository(Demand::class)->findBy(['user' => $this->getUser(),'status' => 3] );
        $congearefused= $this->getDoctrine()->getRepository(Demand::class)->findBy(['user' => $this->getUser(),'status' => 4] );
        $form = $this->createForm(DemandType::class, $demand);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('demandResp_index');
        }

        return $this->render('TO/demand/edit.html.twig', [
            'demand' => $demand,
            'accepted' => $congeaccepted,
            'refused' => $congearefused,
            'form' => $form->createView(),
            'edit' => true,
        ]);
    }

    /**
     * @Route("/delete/{id}", name="demandResp_delete", methods={"POST","GET"})
     */
    public function delete(Request $request, Demand $demand): Response
    {
        //$demand=$this->getDoctrine()->getManager()->getRepository(Demand::class)->findOneBy('id' => $request->get
        $entityManager = $this->getDoctrine()->getManager();
        $demand->setIsCancled(true);
        $entityManager->persist($demand);
        $entityManager->flush();


        return $this->redirectToRoute('demandResp_index');
    }



    /*    public function sendConfirmationEmail(?string $destination, ?string $firstname, ?string $lastname)
        {
            //préciser l'objet
            $subject = 'Congé accepté';
            //la page twig.htm
            $message_html = $this->renderView('email/accepted_demand.html.twig', ['firstname' => $firstname, 'lastname' => $lastname]);
            $message_txt = "Bonjour,\n ceci est le message en txt\nMerci";
            //ajouter l'adresse mail du clinfile afin que le distanataire reçois un mail du clinfile
            $email_from = 'altrahammami@gmail.com';
            // l'ajout du destinataire
            $to = [$destination];
            //instanciation d'un nouveau object Swift
            $mail = new \Swift_Message();
            //Parametrage du l'objet
            $mail
                ->setFrom($email_from)
                ->setSubject($subject)
                ->setBody($message_txt)
                ->addPart($message_html, 'text/html')
                ->setTo($to);
            $this->mailer->send($mail);
        }*/

}
