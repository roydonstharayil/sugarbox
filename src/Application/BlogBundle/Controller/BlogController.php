<?php

namespace Application\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller as Controller;
use Symfony\Component\Form\Form;
use Application\BlogBundle\Form\BlogForm;
use Application\BlogBundle\Entity\Blog;
use Symfony\Component\EventDispatcher\Event;

class BlogController extends Controller
{
   
    
    public function indexAction()
    {
       
        return $this->render('BlogBundle:Blog:index');

        
    }
    public function newAction()
    {
        $blog = new Blog();

        $form = new BlogForm('blog', $blog, $this->container->getValidatorService());
        //TODO
       
        return $this->render('BlogBundle:Blog:new', array(
            'form' => $form
        ));
        
    }
    protected function getEntityManager()
    
    
}
    