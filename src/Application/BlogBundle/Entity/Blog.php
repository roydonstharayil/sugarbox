<?php
/* 
 * This is the part of SiMBiOSis
 * coyright  Roydon roydon@don.com
 */


declare(ENCODING = 'utf-8');
namespace Application\BlogBundle\Entity;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints;

/**
 * @Entity(repositoryClass="Bundle\BlogBundle\Entity\BlogRepository")
 * @Table(name="posts")
 * @HasLifecycleCallbacks
 */


class Blog
{
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('title', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('title', new Constraints\MinLength(5));
        $metadata->addGetterConstraint('description', new Constraints\NotNull());
    }

        /**
         * @Id @Column(type="integer")
         * @GeneratedValue(strategy="AUTO")
         */
        private $id;
	/**
	 * 
	 * @Column(type="string", length=250)
	 * 
	 */
	protected $title;

	/**
	 * 
	 *
	 * @column(type="string", length=250)
	 */
	protected $description;

	

	public function getId()
        {
            return $this->id;
        }

	
	public function setTitle($title) {
		$this->title = $title;
	}

	
	public function getTitle() {
		return $this->title;
	}

	
	public function setDescription($description) {
		$this->description = $description;
	}

	
	public function getDescription() {
		return $this->description;
	}

	

}
