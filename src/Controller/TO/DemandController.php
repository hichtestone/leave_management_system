<?php

namespace App\Controller\TO;

use App\Command\SoldeCommand;
use App\Entity\Demand;
use App\Entity\Notification;
use App\Entity\SupportingDocument;
use App\Form\DemandType;
use App\Form\SupportingDocumentType;
use App\Repository\DemandRepository;
use App\Repository\UserRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use Grpc\Server;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;



/**
 * @Route("TO/demand")
 * @IsGranted ("ROLE_TO", message="vous n'etes pas autoriser dans ce espace")
 */
class DemandController extends AbstractController
{
    private $mailer;

    public function __construct()
    {
        //$this->mailer=$mailer;
    }

    /**
     * @Route("/", name="demand_index", methods={"GET"})
     */
    public function index(DemandRepository $demandRepository): Response
    {
        return $this->render('TO/demand/index.html.twig', [
            'demands' => $demandRepository->findBy(['user'=>$this->getUser()]),
            'demande' => $demandRepository->findBy(['user' =>$this->getUser(), 'isCancled' => false])
        ]);
    }
    /**
     * @Route("/cancelDemand", name="cancelDemand", methods={"GET"})
     */
    public function cancelDemand(DemandRepository $demandRepository,Request $request): Response
    {
        $entityManager= $this->getDoctrine()->getManager();
        $demand=$demandRepository->findBy(['id'=>$request->get('id')]);
       // dd($demand[0]);
        $demand[0]->setStatus(5);
        $entityManager->persist($demand[0]);
        $entityManager->flush();
        return $this->render('TO/demand/index.html.twig', [
            'demands' => $demandRepository->findBy(['user'=>$this->getUser()]),
            'demande' => $demandRepository->findBy(['user' =>$this->getUser(), 'isCancled' => false])
        ]);
    }
    /**
     * @Route("/pdfDemand", name="pdfDemand", methods={"GET"})
     */
    public function pdfDemand(DemandRepository $demandRepository,Request $request,UserRepository $userRepository): Response
    {
        //$signature= $this->getUser()->getFirstName()." ".$this->getUser()->getLastName();
        $demand=$demandRepository->findBy(['id'=>$request->get('id')]);
        $diff = $demand[0]->getEndDate()->diff($demand[0]->getStartDate())->format("%a")+1;
        $logo=$_SERVER["DOCUMENT_ROOT"].'/images/companies/'.$demand[0]->getUser()->getDepartment()->getCompany()->getLogo();
  //dd($_SERVER["DOCUMENT_ROOT"].'/images/companies/'.$demand[0]->getUser()->getDepartment()->getCompany()->getLogo());

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($pdfOptions);

        //$responsable=$userRepository->getResp()
        $resp=$demand[0]->getUser()->getUser()->getFirstName()." ".$demand[0]->getUser()->getUser()->getLastName();


//        dd($logo);
       // $html = "<img src='$logo' height='100' width='100' >"
        $html = "<img src= '$logo' height='100' width='100' >"
            ."<h2  style='text-align: center'>Demande de congé</h2>"
            ."<h5  style='text-align: center'>".$demand[0]->getRef()."</h5>"
            ."<h4  style='text-decoration: underline'>Demandeur</h4>"
            ."<p>Nom et prénom:  ".$demand[0]->getUser()->getFirstName() ." ".$demand[0]->getUser()->getLastName()
            ."<p>Responsable hiérarchique: ".$resp."</p>"
       ."<p>Date de Début:  ".($demand[0]->getStartDate())->format('Y-m-d')."</p>"
       ."<p>Date de Fin:  ".($demand[0]->getEndDate())->format('Y-m-d')."</p>"
        ."<p>Soit: ".$diff." Jours"."</p>"
       ."<p>Raison:  ".($demand[0]->getReason())."</p>"
       ."<p style='margin-bottom: 100px'>Date de demande:  ".($demand[0]->getFilingDate()->format('Y-m-d'))."</p>"
        ."<hr>"
        ."<h4  style='text-decoration: underline'>Direction</h4>";
            if($demand[0]->getStatus()=="3"){
                $html =$html."<p>Accordé : <span style='color: #1e7e34'>X</span></p>"
                ."<p style='margin-bottom: 100px'>Refusé : </p>";
            }else if($demand[0]->getStatus()=="4"){
                $html =$html."<p>Accordé : </p>"
                    ."<p style='margin-bottom: 100px'>Refusé : <span style='color: #1e7e34'>X</span></p>";
            };

//            echo ($html);
//            dd();
         $html=$html."<hr>"
//        ."<p>Signature: <span>$resp</span></p>"
        ."<footer style='position: fixed;text-align: center; bottom: 0px; height:70px;font-size: 10px'>  
         <p>".$demand[0]->getUser()->getDepartment()->getCompany()->getName() ."</p>"
        ."<p>"."Adresse:".$demand[0]->getUser()->getDepartment()->getCompany()->getAdress()."-"
        ."Téléphone:".$demand[0]->getUser()->getDepartment()->getCompany()->getPhoneNumber()."-"
        ."Fax:".$demand[0]->getUser()->getDepartment()->getCompany()->getFax()."-"
        ."Email:".$demand[0]->getUser()->getDepartment()->getCompany()->getEmail()."-"
        ."</p>"
        ."<p>"
        ."Site:".$demand[0]->getUser()->getDepartment()->getCompany()->getSite()
        ."</p>"
        ."</footer>"
        ;

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();
        $name=$demand[0]->getUser()->getFirstName() ." ".$demand[0]->getUser()->getLastName();
        // Output the generated PDF to Browser (force download)
        $dompdf->stream("congé_".$name.".pdf", [
            "Attachment" => true
        ]);
    }
    /**
     * @Route("/new", name="demand_new", methods={"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        $number=[];
        $ref="C-";

        $congeaccepted= $this->getDoctrine()->getRepository(Demand::class)->findBy(['user' => $this->getUser(),'status' => 3] );
        $congearefused= $this->getDoctrine()->getRepository(Demand::class)->findBy(['user' => $this->getUser(),'status' => 4] );
        $dateDepot=new \DateTime('now');
        $number= $this->getDoctrine()->getRepository(Demand::class)->findAll();
      //  dd($number);
        if( $number!=[]){
           $length=str_pad(sizeof($number)+1,5,"0",STR_PAD_LEFT);
           $ref=$ref.$dateDepot->format("Y").$dateDepot->format("m").$dateDepot->format("d")."-".$length;

        }else{
            $length=str_pad("1",5,"0",STR_PAD_LEFT);
            $ref=$ref.$dateDepot->format("Y").$dateDepot->format("m").$dateDepot->format("d")."-".$length;
            //dd($ref);
        }
        $demand = new Demand();

        $form = $this->createForm(DemandType::class, $demand);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $startDate = $form->get('startDate')->getData();
            $endDate = $form->get('endDate')->getData();
            $date = date_diff($startDate, $endDate);
            $date2 = date_diff($dateDepot,$startDate);
           // dd($date);
            $day_number = (int)$date->format('%R%a') + 1;
     $day_number2 = (int)$date2->format('%R%a');
        //
            if ($day_number2 < 15) {
                $this->addFlash('subs', 'Vous devez déposer votre demande 15 jours en avance');

            }

            if ($day_number < 0) {
                $this->addFlash('subs', 'La date fin ne doit pas être antérieure à la date début');

            }
            if ($form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $demand->setUser($this->getUser());
                $demand->setStatus(1);
                $demand->setFilingDate(new \DateTime('now'));
                $demand->setIsCancled(false);
                $demand->setRef($ref);
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

                return $this->redirectToRoute('demand_index');
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
     * @Route("/{id}", name="t_o_demand_show", methods={"GET"})
     */
    public function show(Demand $demand): Response
    {
        return $this->render('TO/demand/show.html.twig', [
            'demand' => $demand,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="demand_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Demand $demand): Response
    {
        $congeaccepted= $this->getDoctrine()->getRepository(Demand::class)->findBy(['user' => $this->getUser(),'status' => 3] );
        $congearefused= $this->getDoctrine()->getRepository(Demand::class)->findBy(['user' => $this->getUser(),'status' => 4] );
        $form = $this->createForm(DemandType::class, $demand);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('demand_index');
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
     * @Route("/delete/{id}", name="demand_delete", methods={"POST","GET"})
     */
    public function delete(Request $request, Demand $demand): Response
    {
        //$demand=$this->getDoctrine()->getManager()->getRepository(Demand::class)->findOneBy('id' => $request->get
        $entityManager = $this->getDoctrine()->getManager();
        $demand->setIsCancled(true);
        $entityManager->persist($demand);
        $entityManager->flush();


        return $this->redirectToRoute('demand_index');
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
