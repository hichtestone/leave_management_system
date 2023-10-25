<?php

namespace App\Controller\TO;


use App\Entity\AdministrativeDemand;
use App\Entity\Advance;
use App\Entity\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/administratif")
 */

class AdministratifController extends AbstractController
{
    /**
     * @Route("/", name="administratif")
     */
    public function index(): Response
    {
        return $this->render('TO/administratif/index.html.twig', [
            'controller_name' => 'AdministratifController',
        ]);
    }

    /**
     * @Route("/historique", name="historique")
     */
    public  function historique(): Response
    {
        $attestations= $this->getDoctrine()->getManager()->getRepository(AdministrativeDemand::class)->findBy(['user'=>$this->getUser()]);
        return $this->render("TO/administratif/historique.html.twig",[
            'attestations'=>$attestations,
        ]);
    }

    /**
     * @Route("/advanced", name="advanced")
     */
    public function demandeavance(): Response
    {
        return $this->render('TO/advanced/add.html.twig', [
            'controller_name' => 'AdministratifController',
        ]);
    }

    /**
     * @Route("/newadministrative", name="new_administrative_demande", methods={"GET","POST"})
     */
    public function new_demande(Request $request): Response
    {
        $ref="Ad-";
       // dd($request->get('observation'));
        $dateDepot=new \DateTime('now');
        $number=$this->getDoctrine()->getRepository(AdministrativeDemand ::class)->findAll();
        if( $number!=[]){
            $length=str_pad(sizeof($number)+1,5,"0",STR_PAD_LEFT);
            $ref=$ref.$dateDepot->format("Y").$dateDepot->format("m").$dateDepot->format("d")."-".$length;
             //dd($ref);
        }else{
            $length=str_pad("1",5,"0",STR_PAD_LEFT);
            $ref=$ref.$dateDepot->format("Y").$dateDepot->format("m").$dateDepot->format("d")."-".$length;

        }
        $administrativedde= new AdministrativeDemand();
        $administrativedde->setUser($this->getUser());
        $administrativedde->setStatus(1);
        $administrativedde->setRef($ref);
        //$a=((int)$request->get('type'));
        $administrativedde->setType($request->get('type'));
        $administrativedde->setObservation($request->get('observation'));
        $administrativedde->setFilingAt(new \DateTime('now'));
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($administrativedde);
        $notification = new Notification();
        $notification->setAdministrativeDemand($administrativedde);

        $notification->setMessage(8);
        $notification->setIsRead(false);
        $notification->setCreatedAt(new \DateTime('now'));
        $entityManager->persist($notification);
        $entityManager->flush();
        $this->addFlash('attestationadd', 'Votre demande d\'attestation a été ajoutée avec succès ');

        $advances= $this->getDoctrine()->getManager()->getRepository(Advance::class)->findLastByuser($this->getUser());
        $attestation= $this->getDoctrine()->getManager()->getRepository(AdministrativeDemand::class)->findLastByuser($this->getUser());

        return $this->render('TO/administratif/hist.html.twig',[
            'advanced' => $advances,
            'attest' => $attestation,
        ]);
    }
  
     /**
         * @Route ("/advanced_list", name="advanced_list", methods={"GET"})
     */
    public function getadvanced(): Response{
        $advances= $this->getDoctrine()->getManager()->getRepository(Advance::class)->findLastByuser($this->getUser());
        $attestation= $this->getDoctrine()->getManager()->getRepository(AdministrativeDemand::class)->findLastByuser($this->getUser());

        return $this->render('TO/advanced/index.html.twig',[
            'advanced' => $advances,
            'attest' => $attestation,
        ]);
    }
    /**
     * @Route ("/hist_list", name="hist_list", methods={"GET"})
     */
    public function hist_list(): Response{
        $advances= $this->getDoctrine()->getManager()->getRepository(Advance::class)->findLastByuser($this->getUser());
        $attestation= $this->getDoctrine()->getManager()->getRepository(AdministrativeDemand::class)->findLastByuser($this->getUser());

        return $this->render('TO/administratif/hist.html.twig',[
            'advanced' => $advances,
            'attest' => $attestation,
        ]);
    }
    /**
     * @Route ("/new_advanced", name="new_advanced", methods={"GET", "POST"})
     */
     public function addAdnvance(Request $request):Response
     {
         $ref="Av-";
         $dateDepot=new \DateTime('now');
         $number=$this->getDoctrine()->getRepository(Advance::class)->findAll();
         if( $number!=[]){
             $length=str_pad(sizeof($number)+1,5,"0",STR_PAD_LEFT);
             $ref=$ref.$dateDepot->format("Y").$dateDepot->format("m").$dateDepot->format("d")."-".$length;
          //    dd($ref);
         }else{
             $length=str_pad("1",5,"0",STR_PAD_LEFT);
             $ref=$ref.$dateDepot->format("Y").$dateDepot->format("m").$dateDepot->format("d")."-".$length;

         }
       $entityManager=$this->getDoctrine()->getManager();
       $advance= new Advance();
       $advance->setStatus(1);
       $advance->setRef($ref);
       $advance->setFilingAt(new \DateTime('now'));
       $advance->setAmount($request->get('amount'));
       $advance->setUser($this->getUser());
       $entityManager->persist($advance);
       $notification = new Notification();
       $notification->setAdvance($advance);
       $notification->setMessage(6);
       $notification->setIsRead(false);
       $notification->setCreatedAt(new \DateTime('now'));
       $entityManager->persist($notification);
       $entityManager->flush();
       $this->addFlash('advancedadd', 'Votre demande d\'avance a été envoyer avec succès');

       return $this->redirectToRoute('advanced_list');




     }

}
