<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("user")
 */
class UserController extends AbstractController

{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('TO/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user, $form->get('password')->getData()
            ));
            $user->setRoles(['ROLE_USER']);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('TO/user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/profile", name="user_show")
     */
    public function show(Request $request, User $user, ValidatorInterface $validator): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ( $form->isValid()){
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', 'image a été modifié avec succès');}
            else{
                $this->addFlash('warning', 'merci de saisir un format valide');
            }
            return $this->redirectToRoute('user_show', [
                'id' => $user->getId(),

            ]);
        }
        return $this->render('TO/user/show.html.twig', [
            'user' => $user,
            'form' => $form->createView(),

        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('TO/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"POST"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }
    /**
     * @Route("/{id}/updatepassword", name="user_updatepassword", methods={"GET","POST"})
     */
    public function updatepassword(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        $entityManager = $this->getDoctrine()->getManager();
        //$encoderService = $this->container->get('security.password_encoder');

        $oldpwd=$request->get('oldpassword');

        $match = $this->passwordEncoder->isPasswordValid($user, $oldpwd);

        if (! $match ) {
            $this->addFlash('error', 'Ancien mot de passe saisi érroné');
           return $this->redirectToRoute('user_show', [
                'id' => $user->getId(),
            ]);
             }
        if (strcmp($request->get('oldpassword'), $request->get('password')) == 0) {
            $this->addFlash('error', 'Nouveau mot de passe ne peut pas être le même que votre mot de passe actuel. Veuillez choisir un mot de passe différent');
            return $this->redirectToRoute('user_show', [
                'id' => $user->getId(),

            ]);

        }
        if(strcmp($request->get('c_password'), $request->get('password')) != 0){
            $this->addFlash('error', 'Ces mots de passe ne correspondent pas. Veuillez réessayer');
            return $this->redirectToRoute('user_show', [
                'id' => $user->getId(),

            ]);
        }
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user, $request->get('password')
        ));

       $entityManager->persist($user);
        $entityManager->flush();
        $this->addFlash('success2', 'mot du passe modifié avec succes');
        return $this->redirectToRoute('user_show', [
            'id' => $user->getId(),

        ]);


    }

}
