<?php
namespace App\Controller\DIRECTEUR;

use App\Entity\AdministrativeDemand;
use App\Entity\Advance;
use App\Entity\Demand;
use App\Entity\Notification;
use App\Entity\User;
use App\Repository\UserRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;



/**
 * @Route ("/DIRECTEUR/administratif")
 * @IsGranted ("ROLE_DIRECTEUR", message="vous n'etes pas autoriser dans ce espace")
 */
class AdministartifController extends AbstractController
{
    /**
     * @Route("/excelAdministrative", name="excelAdministrative")
     * * @param Request $request
     */
    public function excelAdministrative(Request $request,UserRepository $userRepository)
    {

        $file = $request->files->get('file'); // get the file from the sent request
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
            $type = $Row['D'];
            $fillingDate = $Row['E'];
            $status=$Row['C'];


            $user = $userRepository->findUserByUserName($username) ;
            $user_id=$user->getId();
            $demand = new AdministrativeDemand();
            $demand->setUser($user);
            $demand->setStatus($status);
            $demand->setType($type);
            $demand->setFilingAt(new \DateTime($fillingDate));

//dd($demand);

            $entityManager->persist($demand);
            $entityManager->flush();
            // here Doctrine checks all the fields of all fetched data and make a transaction to the database.

        }
        $users = $this->getDoctrine()->getManager()->getRepository(User::class)->getUserNotAdmin();
        $administratif = $this->getDoctrine()->getManager()->getRepository(AdministrativeDemand::class)->findallDemandAdministartifNotAdmin($users);
        return $this->render('DIRECTEUR/advanced/list.html.twig', [
            'administratif' => $administratif
        ]);
    }
    /**
     * @Route("/excelAdvance", name="excelAdvance")
     * * @param Request $request
     */
    public function excelAdvance(Request $request,UserRepository $userRepository)
    {

        $file = $request->files->get('file'); // get the file from the sent request
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
            $amount = $Row['E'];
            $fillingDate = $Row['D'];
            $status=$Row['C'];


            $user = $userRepository->findUserByUserName($username) ;
            $user_id=$user->getId();
            $advance = new Advance();
            $advance->setUser($user);
            $advance->setStatus($status);
            $advance->setAmount($amount);
            $advance->setFilingAt(new \DateTime($fillingDate));

//dd($demand);

            $entityManager->persist($advance);
            $entityManager->flush();
            // here Doctrine checks all the fields of all fetched data and make a transaction to the database.

        }
        $advanced= $this->getDoctrine()->getManager()->getRepository(Advance::class)->findBy(['status'=>'1']);
        return $this->render('DIRECTEUR/advanced/index.html.twig',[
            'advanced' => $advanced,
        ]);
    }
    /**
     * @Route("/historiqueAdvance", name="historiqueAdvance")
     */
    public function historiqueAdvance(): Response
    {
        $advanced= $this->getDoctrine()->getManager()->getRepository(Advance::class)->findBy(['status'=>'2']);
        return $this->render('DIRECTEUR/advanced/historique.html.twig',[
            'advanced' => $advanced,
        ]);
    }
    /**
     * @Route("/list", name="advanced_admin")
     */
    public function getadvanced(): Response
    {
        $advanced= $this->getDoctrine()->getManager()->getRepository(Advance::class)->findBy(['status'=>'1']);
        return $this->render('DIRECTEUR/advanced/index.html.twig',[
            'advanced' => $advanced,
        ]);
    }
    /**
     * @Route("/uploadAdvance", name="uploadAdvance")
     */
    public function uploadAdvance()
    {
        return $this->render('DIRECTEUR/advanced/file.html.twig', []);
    }
    /**
     * @Route("/uploadAdministrative", name="uploadAdministrative")
     */
    public function uploadAdministrative()
    {
        return $this->render('DIRECTEUR/advanced/fileadministrative.html.twig', []);
    }
    /**
     * @Route ("/{id}/accept_advanced", name="accept_advanced", methods = {"GET", "POST"})
     */
    public function accept_advanced(Request $request, Advance $advance)
    {

        $today=date("Y-m-d");
        $advance->setResponseDate(new \DateTime($today));
//dd($today);
        $entityManager= $this->getDoctrine()->getManager();
        $advance->setStatus(2);
        $notif = new Notification();
        $notif->setAdvance($advance);
        $notif->setCreatedAt(new \DateTime('now'));
        $notif->setIsRead(false);
        $notif->setMessage(9);
        $entityManager->persist($notif);
        $entityManager->persist($advance);
        $entityManager->flush();
        $this->addFlash('success', "Demande d'avance accordée");
        return $this->redirectToRoute('advanced_admin');
    }

    /**
     *@Route("/send", name="administratif_sended", methods={"GET"})
     */
    public function getAllAdministratif():Response {
        $users = $this->getDoctrine()->getManager()->getRepository(User::class)->getUserNotAdmin();
        $administratif = $this->getDoctrine()->getManager()->getRepository(AdministrativeDemand::class)->findallDemandAdministartifNotAdmin($users);
        return $this->render('DIRECTEUR/advanced/list.html.twig', [
            'administratif' => $administratif
        ]);
    }
    /**
     *@Route("/historiqueAdministrative", name="historiqueAdministrative", methods={"GET"})
     */
    public function historiqueAdministrative():Response {
        $users = $this->getDoctrine()->getManager()->getRepository(User::class)->getUserNotAdmin();
        $administratif = $this->getDoctrine()->getManager()->getRepository(AdministrativeDemand::class)->findBy(['status'=>'2']);
        return $this->render('DIRECTEUR/advanced/histadministrative.html.twig', [
            'administratif' => $administratif
        ]);
    }
     /**
      * @Route("ajax", name="adm_update", methods={"GET", "POST"})
      */
     public function accept(Request $request){
         if ($request->isXmlHttpRequest()){
             $today=date("Y-m-d");

             $id = $request->get('id');
             $administratif = $this->getDoctrine()->getManager()->getRepository(AdministrativeDemand::class)->findOneBy(['id' => $id]);
             $administratif->setStatus(2);
             $administratif->setResponseDate(new \DateTime($today));
             $notif = new Notification();
             $notif->setAdministrativeDemand($administratif);
             $notif->setMessage(9);
             $notif->setCreatedAt(new \DateTime('now'));
             $notif->setIsRead(false);
             $this->getDoctrine()->getManager()->persist($administratif);
             $this->getDoctrine()->getManager()->persist($notif);
             $this->getDoctrine()->getManager()->flush();
         }
     }

}

