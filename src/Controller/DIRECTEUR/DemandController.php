<?php

namespace App\Controller\DIRECTEUR;

use App\Command\SoldeCommand;
use App\Entity\Demand;
use App\Entity\Notification;
use App\Entity\SupportingDocument;
use App\Entity\User;
use App\Form\DemandType;
use App\Repository\DemandRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


/**
 * @Route ("DIRECTEUR/demand")
 * @IsGranted ("ROLE_DIRECTEUR", message="vous n'etes pas autoriser dans ce espace")
 */
class DemandController extends AbstractController
{
    private $em;
    private $parameters;
    public function __construct(EntityManagerInterface $em, ParameterBagInterface $parameters)
    {
        $this->em=$em;
        $this->parameters=$parameters;
    }
    /**
     * @Route("/excelDemand", name="excelDemand")
     * * @param Request $request
     */
    public function excelDemand(Request $request,UserRepository $userRepository)
    {

        $file = $request->files->get('filedemand'); // get the file from the sent request
        $fileFolder = __DIR__ . '/../../public/uploads/';  //choose the folder in which the uploaded file will be stored

        $filePathName = md5(uniqid()) . $file->getfilename();
      //  dd($filePathName);
        // apply md5 function to generate an unique identifier for the file and concat it with the file extension
        try {
            $file->move($fileFolder, $filePathName);
        } catch (FileException $e) {
            dd($e);
        }
        $spreadsheet = IOFactory::load($fileFolder . $filePathName); // Here we are able to read from the excel file
        $row = $spreadsheet->getActiveSheet()->removeRow(1); // I added this to be able to remove the first file line
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true); // here, the read data is turned into an array
      //  dd($sheetData);
        // add to DB
        $entityManager = $this->getDoctrine()->getManager();
        foreach ($sheetData as $Row)
        {
            $username = $Row['B'];

            $startDate = $Row['C'];
            $endDate = $Row['D'];
            $reason= $Row['E'];
            $status = $Row['F'];
            $fillingDate = $Row['G'];
            $emergency = $Row['H'];
            $iscancelled= $Row['I'];
            $cause = $Row['J'];

            $user = $userRepository->findUserByUserName($username) ;
            $user_id=$user->getId();
            $demand = new Demand();
            $demand->setStartDate(new \DateTime($startDate));
            $demand->setUser($user);
            $demand->setEndDate(new \DateTime($endDate));
            $demand->setReason($reason);
            $demand->setStatus($status);
            $demand->setFilingDate(new \DateTime($fillingDate));
//            $demand->setEmergency($emergency);
            $demand->setIsCancled($iscancelled);
            $demand->setCause($cause);
//dd($demand);
            //add notif

            $notif = new Notification();
            $notif->setDemand($demand);
            $notif->setCreatedAt(new \DateTime('now'));
            $notif->setIsRead(false);
            $notif->setMessage(1);
            $entityManager->persist($notif);

            //end notif
                $entityManager->persist($demand);
                $entityManager->flush();
                // here Doctrine checks all the fields of all fetched data and make a transaction to the database.

        }
        $date1 = null;
        $date2= null;
        $users=$this->getDoctrine()->getManager()->getRepository(User::class)->getUserNotDirecteurAdmin();
        $demands=$this->getDoctrine()->getManager()->getRepository(Demand::class)->findallDemandUserNotAdmin($users);
        //dd($demands);
        $analyse= $this->getDoctrine()->getManager()->getRepository(Demand::class)->analyse($date1,$date2);
        $result=(count($analyse));

        return $this->render('DIRECTEUR/demand/index.html.twig', [
            'demands' => $demands,
            'anaylse' => $analyse,
            'result' => $result
        ]);
    }
    /**
     * @Route("/uploaddemand", name="uploaddemand")
     */
    public function uploaddemand()
    {
        return $this->render('DIRECTEUR/demand/file.html.twig', []);
    }
    /**
     * @Route("/Historique", name="Historique")
     */
    public function Historique():Response
    {
        $date1 = null;
        $date2= null;
        $users=$this->getDoctrine()->getManager()->getRepository(User::class)->getUserNotDirecteurAdmin();
        $demands=$this->getDoctrine()->getManager()->getRepository(Demand::class)->findNotNew(1);
        //dd($demands);
        $analyse= $this->getDoctrine()->getManager()->getRepository(Demand::class)->analyse($date1,$date2);
        $result=(count($analyse));

        return $this->render('DIRECTEUR/demand/historique.html.twig', [
            'demands' => $demands,
            'anaylse' => $analyse,
            'result' => $result
        ]);
    }
    /**
     * @Route("/", name="demand")
     */
    public function index(): Response
    {
        $date1 = null;
        $date2= null;
        $users=$this->getDoctrine()->getManager()->getRepository(User::class)->getUserNotDirecteurAdmin();
        $demands=$this->getDoctrine()->getManager()->getRepository(Demand::class)->findallDemandUserNotAdmin($users);
        //dd($demands);
        $analyse= $this->getDoctrine()->getManager()->getRepository(Demand::class)->analyse($date1,$date2);
        $result=(count($analyse));

        return $this->render('DIRECTEUR/demand/index.html.twig', [
            'demands' => $demands,
            'anaylse' => $analyse,
            'result' => $result
        ]);
    }
    /**
     * @Route("/pdfDemandHist", name="pdfDemandHist", methods={"GET"})
     */
    public function pdfDemandHist(DemandRepository $demandRepository,Request $request,UserRepository $userRepository): Response
    {
        $demand=$demandRepository->findBy(['id'=>$demand_id=$request->get('id')]);
      //  dd($demand);
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
        }else{
            $html =$html."<p>Accordé : </p>"
                ."<p style='margin-bottom: 100px'>Refusé : <span style='color: #1e7e34'>X</span></p>";
        };

//            echo ($html);
//            dd();
        $html=$html."<hr>"
            ."<p>Signature: </p>"

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
     * @Route("/demands", name="demandDR", methods={"GET"})
     */
    public function listeDemand(DemandRepository $demandRepository): Response
    {
        return $this->render('TO/demand/index.html.twig', [
            'demands' => $demandRepository->findBy(['user'=>$this->getUser()]),
            'demande' => $demandRepository->findBy(['user' =>$this->getUser(), 'isCancled' => false])
        ]);
    }

    /**
     * @Route ("/{id}/approuved", name="approuve_demand", methods = {"GET", "POST"})
     */
    public function approuveDemand(Request $request, Demand $demand,DemandRepository $demandRepository,UserRepository $userRepository)
    {
        $signature= $this->getUser()->getFirstName()." ".$this->getUser()->getLastName();
        $today=date("Y-m-d");
        //dd($today);
        //dd($signature);
        ///
        $entityManager= $this->getDoctrine()->getManager();
        $solde = $demand->getUser()->getSolde();
        $date = date_diff($demand->getStartDate(), $demand->getEndDate());
        $day_number= (int)$date->format('%R%a') +1;
        if ($solde  >= $day_number ) {




            $demand->setStatus(3);
            $demand->setResponseDate(new \Datetime($today));
            $user = $demand->getUser();
            if ($day_number >= 5) {
                $number_add = (int)($day_number / 5);
                $user->setSolde($solde - ($day_number + $number_add));
            }
            else{
                $user->setSolde($solde - $day_number);
            }
            $notif = new Notification();
            $notif->setDemand($demand);
            $notif->setCreatedAt(new \DateTime('now'));
            $notif->setIsRead(false);
            $notif->setMessage(4);
            $entityManager->persist($notif);
            $entityManager->persist($demand);
            $entityManager->persist($user);
            $this->addFlash('success', 'Aprés avoir verifié le solde , la demande est acceptée');
            $entityManager->flush();
            //
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

            $html =$html."<p>Accordé : <span style='color: #1e7e34'>X</span></p>"
                ."<p style='margin-bottom: 100px'>Refusé :     <span style='margin-left: 100px'>Motif:..............................................</span> </p>"
                ."<p >Date de réponse :".$today." </p>";

//            echo ($html);
//            dd();
            $html=$html."<hr>"
                ."<p>Signature: <span>$signature</span></p>"

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
            //
        }
        else{
            $demand=$demandRepository->findBy(['id'=>$request->get('id')]);
            $this->addFlash('warning', 'Le solde est inférieur à la periode souhaitée?');
            $date1 = null;
            $date2= null;
            $users=$this->getDoctrine()->getManager()->getRepository(User::class)->getUserNotDirecteurAdmin();
            $demands=$this->getDoctrine()->getManager()->getRepository(Demand::class)->findallDemandUserNotAdmin($users);
            //dd($demands);
            $analyse= $this->getDoctrine()->getManager()->getRepository(Demand::class)->analyse($date1,$date2);
            $result=(count($analyse));

            return $this->render('DIRECTEUR/demand/index.html.twig', [
                'demands' => $demands,
                'anaylse' => $analyse,
                'result' => $result,
                'demand'=>$demand[0]
            ]);

        }
        return $this->redirectToRoute('demand');

    }    /**
 * @Route ("/{id}/forcerDemand", name="forcerDemand", methods = {"GET", "POST"})
 */
    public function forcerDemand(Request $request, Demand $demand,DemandRepository $demandRepository,UserRepository $userRepository)
    {
        $signature= $this->getUser()->getFirstName()." ".$this->getUser()->getLastName();
        $today=date("Y-m-d");
        // dd($today);
        $entityManager= $this->getDoctrine()->getManager();
        $demand->setStatus(3);
        $demand->setResponseDate(new \Datetime($today));
        $solde = $demand->getUser()->getSolde();
        $date = date_diff($demand->getStartDate(), $demand->getEndDate());
        $day_number= (int)$date->format('%R%a') +1;
        $demand->getUser()->setSolde($solde - $day_number);

        $notif = new Notification();
        $notif->setDemand($demand);
        $notif->setCreatedAt(new \DateTime('now'));
        $notif->setIsRead(false);
        $notif->setMessage(5);
        $entityManager->persist($notif);
        $entityManager->persist($demand);
        $entityManager->flush();
        $this->addFlash('danger', 'Aprés avoir verifié le solde , la demande est refusée');
        //
       // $demand=$demandRepository->findBy(['id'=>$request->get('id')]);
        $diff = $demand->getEndDate()->diff($demand->getStartDate())->format("%a")+1;
        $logo=$_SERVER["DOCUMENT_ROOT"].'/images/companies/'.$demand->getUser()->getDepartment()->getCompany()->getLogo();
       // dd($_SERVER["DOCUMENT_ROOT"].'/images/companies/'.$demand[0]->getUser()->getDepartment()->getCompany()->getLogo());

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($pdfOptions);

        //$responsable=$userRepository->getResp()
        $resp=$demand->getUser()->getUser()->getFirstName()." ".$demand->getUser()->getUser()->getLastName();


////        dd($logo);
        // $html = "<img src='$logo' height='100' width='100' >"
        $html = "<img src= '$logo' height='100' width='100' >"
            ."<h2  style='text-align: center'>Demande de congé</h2>"
            ."<h5  style='text-align: center'>".$demand->getRef()."</h5>"
            ."<h4  style='text-decoration: underline'>Demandeur</h4>"
            ."<p>Nom et prénom:  ".$demand->getUser()->getFirstName() ." ".$demand->getUser()->getLastName()
            ."<p>Responsable hiérarchique: ".$resp."</p>"
            ."<p>Date de Début:  ".($demand->getStartDate())->format('Y-m-d')."</p>"
            ."<p>Date de Fin:  ".($demand->getEndDate())->format('Y-m-d')."</p>"
            ."<p>Soit: ".$diff." Jours"."</p>"
            ."<p>Raison:  ".($demand->getReason())."</p>"
            ."<p style='margin-bottom: 100px'>Date de demande:  ".($demand->getFilingDate()->format('Y-m-d'))."</p>"
            ."<hr>"
            ."<h4  style='text-decoration: underline'>Direction</h4>";

        $html =$html."<p>Accordé : <span style='color: #1e7e34'>X</span></p>"
            ."<p style='margin-bottom: 100px'>Refusé :     <span style='margin-left: 100px'>Motif:..............................................</span> </p>"
            ."<p >Date de réponse :".$today." </p>";

//            echo ($html);
//            dd();
        $html=$html."<hr>"
            ."<p>Signature: <span></span></p>"

            ."<footer style='position: fixed;text-align: center; bottom: 0px; height:70px;font-size: 10px'>

            <p>".$demand->getUser()->getDepartment()->getCompany()->getName() ."</p>"
            ."<p>"."Adresse:".$demand->getUser()->getDepartment()->getCompany()->getAdress()."-"
            ."Téléphone:".$demand->getUser()->getDepartment()->getCompany()->getPhoneNumber()."-"
            ."Fax:".$demand->getUser()->getDepartment()->getCompany()->getFax()."-"
            ."Email:".$demand->getUser()->getDepartment()->getCompany()->getEmail()."-"
            ."</p>"
            ."<p>"
            ."Site:".$demand->getUser()->getDepartment()->getCompany()->getSite()
            ."</p>"
            ."</footer>"
        ;

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
         $dompdf->render();

        $name=$demand->getUser()->getFirstName() ." ".$demand->getUser()->getLastName();
        // Output the generated PDF to Browser (force download)
        $dompdf->stream("congé_".$name.".pdf", [
            "Attachment" => true
        ]);

        return $this->redirectToRoute('demand');
    }
    /**
     * @Route ("/{id}/reject", name="reject_demand", methods = {"GET", "POST"})
     */
    public function rejectDemand(Request $request, Demand $demand,DemandRepository $demandRepository,UserRepository $userRepository)
    {
        $signature= $this->getUser()->getFirstName()." ".$this->getUser()->getLastName();
        $today=date("Y-m-d");
       // dd($today);
        $entityManager= $this->getDoctrine()->getManager();
        $demand->setStatus(4);
        $demand->setResponseDate(new \Datetime($today));
        $notif = new Notification();
        $notif->setDemand($demand);
        $notif->setCreatedAt(new \DateTime('now'));
        $notif->setIsRead(false);
        $notif->setMessage(5);
        $entityManager->persist($notif);
        $entityManager->persist($demand);
        $entityManager->flush();
        $this->addFlash('danger', 'Aprés avoir verifié le solde , la demande est refusée');
        //
        $demand=$demandRepository->findBy(['id'=>$request->get('id')]);
        $diff = $demand[0]->getEndDate()->diff($demand[0]->getStartDate())->format("%a")+1;
        $logo=$_SERVER["DOCUMENT_ROOT"].'/images/companies/'.$demand[0]->getUser()->getDepartment()->getCompany()->getLogo();
        //dd($_SERVER["DOCUMENT_ROOT"].'/images/companies/'.$demand[0]->getUser()->getDepartment()->getCompany()->getLogo());

//        $pdfOptions = new Options();
//        $pdfOptions->set('defaultFont', 'Arial');
//        $pdfOptions->set('isRemoteEnabled', true);
//        $pdfOptions->set('isHtml5ParserEnabled', true);
//        $dompdf = new Dompdf($pdfOptions);

        //$responsable=$userRepository->getResp()
        $resp=$demand[0]->getUser()->getUser()->getFirstName()." ".$demand[0]->getUser()->getUser()->getLastName();


////        dd($logo);
//        // $html = "<img src='$logo' height='100' width='100' >"
//        $html = "<img src= '$logo' height='100' width='100' >"
//            ."<h2  style='text-align: center'>Demande de congé</h2>"
//            ."<h4  style='text-decoration: underline'>Demandeur</h4>"
//            ."<p>Nom et prénom:  ".$demand[0]->getUser()->getFirstName() ." ".$demand[0]->getUser()->getLastName()
//            ."<p>Responsable hiérarchique: ".$resp."</p>"
//            ."<p>Date de Début:  ".($demand[0]->getStartDate())->format('Y-m-d')."</p>"
//            ."<p>Date de Fin:  ".($demand[0]->getEndDate())->format('Y-m-d')."</p>"
//            ."<p>Soit: ".$diff." Jours"."</p>"
//            ."<p>Raison:  ".($demand[0]->getReason())."</p>"
//            ."<p style='margin-bottom: 100px'>Date de demande:  ".($demand[0]->getFilingDate()->format('Y-m-d'))."</p>"
//            ."<hr>"
//            ."<h4  style='text-decoration: underline'>Direction</h4>";
//
//        $html =$html."<p>Accordé : </p>"
//            ."<p style='margin-bottom: 100px'>Refusé :  <span style='color: #1e7e34'>X</span>   <span style='margin-left: 100px'>Motif:..............................................</span> </p>"
//            ."<p >Date de réponse :".$today." </p>";
//
////            echo ($html);
////            dd();
//        $html=$html."<hr>"
//            ."<p>Signature: <span>$resp</span></p>"
//
//            ."<footer style='position: fixed;text-align: center; bottom: 0px; height:70px;font-size: 10px'>
//
//            <p>".$demand[0]->getUser()->getDepartment()->getCompany()->getName() ."</p>"
//            ."<p>"."Adresse:".$demand[0]->getUser()->getDepartment()->getCompany()->getAdress()."-"
//            ."Téléphone:".$demand[0]->getUser()->getDepartment()->getCompany()->getPhoneNumber()."-"
//            ."Fax:".$demand[0]->getUser()->getDepartment()->getCompany()->getFax()."-"
//            ."Email:".$demand[0]->getUser()->getDepartment()->getCompany()->getEmail()."-"
//            ."</p>"
//            ."<p>"
//            ."Site:".$demand[0]->getUser()->getDepartment()->getCompany()->getSite()
//            ."</p>"
//            ."</footer>"
//        ;
//
//        // Load HTML to Dompdf
//        $dompdf->loadHtml($html);
//
//        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
//        $dompdf->setPaper('A4', 'portrait');
//
//        // Render the HTML as PDF
//      //  $dompdf->render();

        $name=$demand[0]->getUser()->getFirstName() ." ".$demand[0]->getUser()->getLastName();
        // Output the generated PDF to Browser (force download)
//        $dompdf->stream("congé_".$name.".pdf", [
//            "Attachment" => true
//        ]);
        //
        return $this->redirectToRoute('demand');
    }

    /**
     * @Route ("/{id}/analyse", name="demand_analyse", methods={"GET", "POST"})
     */
    public function analyse(Request $request, Demand $demand)
    {
        $demands = [];
        $users=$this->getDoctrine()->getManager()->getRepository(User::class)->getUserNotDirecteurAdmin();
        $demands=$this->getDoctrine()->getManager()->getRepository(Demand::class)->findallDemandUserNotAdmin($users);
      //  dd($demand->getUser());
        $company= $demand->getUser()->getDepartment()->getCompany()->getId();

        $date1= $demand->getStartDate();
        $date2= $demand->getEndDate();
        $analyse= $this->getDoctrine()->getManager()->getRepository(Demand::class)->analyse1($date1,$date2,$company);
        $result=(count($analyse));
        $this->addFlash('info', 'Durant cette periode vous avez que ' .$result. ' demande accepté');
        if (null ==$result){
            return $this->redirectToRoute('demand');
        }

        return $this->render('DIRECTEUR/demand/index.html.twig', [
            'anaylse'=>$analyse,
            'demands' => $demands,
            'result' => $result
        ]);
    }

    /**
     * @Route ("ajax", name= "update_demand_send", methods={"POST", "GET"})
     */
    public function update_demand_send(DemandRepository $repository, Request $request){

        if ($request->isXmlHttpRequest()){
            $id=$request->get('id');
            $repository->update_demand_progress($id);
            $notif = new Notification();
            $entityManager= $this->getDoctrine()->getManager();
            $demand= $this->getDoctrine()->getRepository(Demand::class)->findOneBy(['id' => $id] );
            $notif->setDemand($demand);
            $notif->setCreatedAt(new \DateTime('now'));
            $notif->setIsRead(false);
            $notif->setMessage(3);
            $entityManager->persist($notif);
            $entityManager->flush();

        }
    }

    /**
     * @Route("/{id}/edit", name="demandDredit", methods={"GET","POST"})
     */
    public function edit(Request $request, Demand $demand): Response
    {
        $congeaccepted= $this->getDoctrine()->getRepository(Demand::class)->findBy(['user' => $this->getUser(),'status' => 3] );
        $congearefused= $this->getDoctrine()->getRepository(Demand::class)->findBy(['user' => $this->getUser(),'status' => 4] );
        $form = $this->createForm(DemandType::class, $demand);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('demandDR');
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
     * @Route("/new", name="demandDrnew", methods={"GET", "POST"})
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

                return $this->redirectToRoute('demand');
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
     * @Route("/delete/{id}", name="demandDr_delete", methods={"POST","GET"})
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

    /**
     * @Route("/getSolde", name="Setsolde", methods={"GET","POST"})
     */
    public function soldes() : Response {
        $process = new Process('ls -lsa');
        $process->disableOutput();
        $that= $this;
        $process->start(function ($type, $buffer) use ($that) {
            $command = new SoldeCommand($this->em, $this->parameters);
            $input   = new ArrayInput(array());
            $output  = new NullOutput;
            $command->run($input, $output);
        });
        $this->addFlash('Soldes', 'Modification du solde a été fait avec succès' );

        return $this->redirectToRoute('indexregle');
    }
    /**
     * @Route("/indexRegle", name="indexregle", methods={"GET", "POST"})
     */
    public function indexx(): Response {

        return $this->render('DIRECTEUR/regle/index.html.twig');
    }

}
