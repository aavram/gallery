<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Gallery;
use AppBundle\Entity\Photo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Photo controller.
 *
 * @Route("photo")
 */
class PhotoController extends Controller
{


    /**
     * Creates a new photo entity.
     *
     * @Route("/uploadToGallery/{id}", name="photo_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, Gallery $gallery)
    {
        $photo = new Photo();
        $form = $this->createForm('AppBundle\Form\PhotoType', $photo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $image = $photo->getPhotoURL();
            $directory = $this->getParameter('images_directory');
            $imageName = '/uploads/photos/'.$this->generateUniqueFileName().'.'.$image->guessExtension();

            $image->move(
                $this->getParameter('images_directory'),
                $imageName
            );

            $photo->setPhotoURL($imageName);
            $photo->setGallery($gallery);

            $em = $this->getDoctrine()->getManager();
            $em->persist($photo);
            $em->flush();

            //return $this->redirect($this->generateUrl('photo_show', array('id' => $photo->getId())));
            return $this->redirectToRoute('photo_show', array('id' => $photo->getId()));
        }

        return $this->render('photo/new.html.twig', array(
            'photo' => $photo,
            'gallery'=>$gallery,
            'form' => $form->createView(),
        ));
    }

    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

        /**
     * Finds and displays a photo entity.
     *
     * @Route("/{id}", name="photo_show")
     * @Method("GET")
     */
    public function showAction(Photo $photo)
    {
        $deleteForm = $this->createDeleteForm($photo);

        return $this->render('photo/show.html.twig', array(
            'photo' => $photo,
            'gallery' => $photo->getGallery(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing photo entity.
     *
     * @Route("/{id}/edit", name="photo_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Photo $photo)
    {
        $deleteForm = $this->createDeleteForm($photo);


        return $this->render('photo/edit.html.twig', array(
            'photo' => $photo,
            'gallery' => $photo->getGallery(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a photo entity.
     *
     * @Route("/{id}", name="photo_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Photo $photo)
    {
        $form = $this->createDeleteForm($photo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($photo);
            $em->flush();
        }

        return $this->redirectToRoute('gallery_show', ['id' => $photo->getGallery()->getId()]);
    }

    /**
     * Creates a form to delete a photo entity.
     *
     * @param Photo $photo The photo entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Photo $photo)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('photo_delete', array('id' => $photo->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}