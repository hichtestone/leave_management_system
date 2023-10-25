<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Entity\Department;
use App\Entity\User;
use App\Form\DepartmentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


/**
 * @Route ("/ADMIN/department")
 * @IsGranted ("ROLE_ADMIN", message="vous n'etes pas autoriser dans ce espace")
 */

class DepartmentDepartmentController extends AbstractController
{

    /**
     * @Route("/", name="department")
     */
    public function index(Request $request): Response
    {
        $departments= $this->getDoctrine()->getManager()->getRepository(Department::class)->getAllDepByCopany($request->getSession()->get('company'));
        return $this->render('Admin/department/index.html.twig', [
            'departments' => $departments,
        ]);
    }

    /**
     * @Route("/new_department",name="new_departement", methods={"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        $department = new Department();
        $session = $request->getSession()->get('company');
        $form = $this->createForm(DepartmentType::class, $department, [
            'session' => $session
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($department);
            $entityManager->flush();
            return $this->redirectToRoute('department');
        }

        return $this->render('Admin/department/create.html.twig',[
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit_department",name="edit_departement", methods={"GET", "POST"})
     */
    public function edit(Request $request, Department $department): Response
    {
        $session = $request->getSession()->get('company');
        $form = $this->createForm(DepartmentType::class, $department, [
            'session' => $session
        ]);        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($department);
            $entityManager->flush();
            return $this->redirectToRoute('department');
        }

        return $this->render('Admin/department/create.html.twig',[
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="department_delete", methods={"GET", "POST"})
     */
    public function delete(Department $department): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $users=$this->getDoctrine()->getManager()->getRepository(User::class)->findBy(['department' => $department ]);
        foreach ($users as $user) {
            $user->setDepartment(null);
        }

        $entityManager->remove($department);
        $entityManager->flush();

        return $this->redirectToRoute('department');

    }

    /**
     * @Route("/{id}/show_departement", name="departement_show", methods={"GET"})
     */
    public function show(Department $department): Response
    {
        $users=$this->getDoctrine()->getManager()->getRepository(User::class)->findBy(['department' => $department , 'isSuperAdmin'=> null]);
        //dd($users);
        $responsable1=[];
        $data= [];
        $responsable=[];
            foreach ($users as $user){
                if($user->getUser() != null && $user->getUser()->getDepartment() == $department)
                    if($user->getUser()->getIsSuperAdmin()==null)
                    $responsable1[]=$user->getUser();
            }
            $responsable1= array_unique($responsable1, SORT_REGULAR);
            $restest=[];
            $existe=true;
        while ($existe) {
                $restest=array_merge($responsable1);
               // dd($restest);
                $existe=true;
                if(count($restest)>1){
                    for($key=0;$key<count($restest)-1 ;$key++) {
                        if ($restest[$key] == $restest[$key+1]->getUser())
                        {$responsable[] = $restest[$key];
                            $existe=false;
                        }
                        elseif ($restest[$key]->getUser() == $restest[$key+1])
                        {$responsable[] = $restest[$key+1];
                            $existe=false;
                        }

                    }
                }
                else{
                    $existe = false;
                }
            }

        $allusers=$this->getDoctrine()->getManager()->getRepository(User::class)->findBy(['isSuperAdmin'=> null]);

        return $this->render('Admin/department/show.html.twig', [
            'department' => $department,
            'users'      => $users,
            'allusers'   => $allusers,
            'responsable'=>$responsable,
            'responsables'=>array_unique($responsable1,SORT_REGULAR ),
        ]);
    }

    /**
     * @Route("/{id}/associateuser_departement", name="departement_associateuser", methods={"POST"})
     */
    public function associateuser(Request $request, Department $department): Response
    {

       foreach ($request->request->all() as $id){
           $entityManager = $this->getDoctrine()->getManager();
           $user=$this->getDoctrine()->getManager()->getRepository(User::class)->findOneBy(['id'=>$id]);

           $user->setDepartment($department);
           $entityManager->persist($user);
           $entityManager->flush();
       }

      return  $this->redirectToRoute('department');
    }

    /**
     * @Route("/{department}/detacheUser_departement/{user}", name="departement_detacheUser", requirements={"department"="\d+", "user"="\d+"})
     * @param Department $department
     * @param User $user
     */
    public function detacheUser(User $user): Response
    {
            $entityManager = $this->getDoctrine()->getManager();
            $user->setDepartment(null);
            $entityManager->persist($user);
            $entityManager->flush();

        return  $this->redirectToRoute('department');
    }

    /**
     * @Route("/{id}/desociate", name="desociate", requirements={"id"="\d+"})
     */
    public function desociate(User $user) : Response {

        $depid = $user->getDepartment()->getId();
        $user->setDepartment(null);
        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('departement_show', [
            'id' => $depid,
        ]);
    }

}
