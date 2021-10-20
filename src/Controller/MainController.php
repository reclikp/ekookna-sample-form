<?php

namespace App\Controller;

/* nie przemyślałem nazewnictwa, przez co musiałem zmienić nazwę ;D */
use App\Entity\Request as RequestEntity;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints as Assert;

class MainController extends AbstractController {
    /**
     * @Route("/")
     * @Method({"GET", "POST"})
     */
    public function index(Request $req) {
        $request = new RequestEntity;

        $form = $this->createFormBuilder($request)
            ->add('address', TextType::class, array(
                'label' => 'Adres przystanku',
                'error_bubbling' => true,
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Podaj adres'
                )
            ))
            ->add('description', TextareaType::class, array(
                'label' => 'Opis zgłoszenia',
                'error_bubbling' => true,
                'attr' => array(
                    'placeholder' => 'Podaj opis zgłoszenia. Maksymalna ilość znaków wynosi: 300.'
                )
            ))
            ->add('attachment', FileType::class, array(
                'label' => 'Załączniki',
                'mapped' => false,
                'multiple' => true,
                'required' => false,
                'error_bubbling' => true,
                'constraints' => array(
                    new Count(array(
                        'max' => 3,
                        'maxMessage' => 'Wybrano za dużą ilość plików. Proszę wybrać maksymalnie 3 pliki.'
                    )),
                    new All(array(
                        new File(array(
                            'mimeTypes' => array('image/jpeg', 'image/png'),
                            'mimeTypesMessage' => 'Wybrano nieobsługiwane typy plików. Obsługiwane typy plików: jpg, jpeg, png.'
                        ))
                    ))
                ),
                'attr' => array(
                    'accept' => '.jpg, .jpeg, .png'
                )
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Wyślij zgłoszenie'
            ))
            ->getForm()
        ;

        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $request = $form->getData();
            $destination = $this->getParameter('kernel.project_dir').'\\public\\uploads\\';

            if ($attachments = $form['attachment']->getData()) {
                $tempAttachments = array();

                foreach ($attachments as $attachment) {
                    $newFilename = uniqid().'.'.$attachment->guessExtension();
                    $attachment->move($destination, $newFilename);
                    array_push($tempAttachments, $newFilename);
                }

                $request->setAttachments($tempAttachments);
            }
            
            $em->persist($request);
            $em->flush();

            $this->addFlash('success', 'Zgłoszenie zostało poprawnie wysłane!');

            return $this->redirect("/");
        }

        return $this->render('form.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/admin")
     * @Method({"GET", "POST"})
     */
    public function admin() {
        $requests = $this->getDoctrine()->getRepository(RequestEntity::class)->findAll();

        return $this->render('panel.html.twig', array('requests' => $requests));
    }

    /**
     * @Route("/admin/request/{id}")
     */
    public function request(Request $req, $id) {
        $request = $this->getDoctrine()->getRepository(RequestEntity::class)->find($id); 

        if($request->getStatus() == 'new'){
            $request->setStatus('open');

            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }


        return $this->render('request.html.twig', array(
            'request' => $request
        ));
    }
}