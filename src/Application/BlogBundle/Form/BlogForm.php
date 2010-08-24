<?php


namespace Application\BlogBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\TextField;
use Symfony\Component\Form\TextareaField;

class BlogForm extends Form
{
    public function configure()
    {
        $this->add(new TextField('title'));
        $this->add(new TextareaField('description'));
    }
}

