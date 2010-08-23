<?php

namespace Application\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller as Controller;
//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Form\Form;
use Application\BlogBundle\Form\BlogForm;
use Application\BlogBundle\Entity\Blog;
use Symfony\Component\EventDispatcher\Event;
//use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogController extends Controller
{
   
    
    public function indexAction()
    {
        $request = $_REQUEST;

        return $this->render('BlogBundle:Blog:index');

        // render a Twig template instead
        // return $this->render('HelloBundle:Hello:index:twig', array('name' => $name));
    }
    public function newAction()
    {
        $blog = new Blog();
//        $request = $this->getRequest();
        $form = new BlogForm('blog', $blog, $this->container->getValidatorService());
//        TODO
        if('POST' === $this->getRequest()) {
            $form->bind($this->get('blog'));
//        if ($this->getRequest()->getMethod() == 'POST') {
//            $form->bind('blog');
            if($form->isValid()) {
                $this->getEntityManager()->persist($blog);
                $this->getEntityManager()->flush();
//
////                $this->container->getEventDispatcherService()->notify(new Event($project, 'miam.project.create'));
////
////                $this->container->getSessionService()->setFlash('project_create', array('project' => $project));
////                return $this->redirect($this->generateUrl('projects'));
//            }
//        }
        


    }
        }
        return $this->render('BlogBundle:Blog:new', array(
            'form' => $form
        ));
        
    }
    protected function getEntityManager()
    {
        return $this->container->getDoctrine_ORM_EntityManagerService();

    }
    protected function getRequest()
    {
        return 'POST';
    }
        protected function get($bloarr)
        {
           $blogarray = array('title' => '', 'description' => '');
           return $blogarray;
        }
   
    
}
    