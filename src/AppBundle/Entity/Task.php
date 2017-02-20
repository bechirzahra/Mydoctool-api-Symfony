<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;

use AppBundle\Entity\Item;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\QuestionRepository")
 */
class Task extends Item
{

    /**
    * @ORM\Column(type="boolean")
    * @Expose
    */
    private $repeated;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     * @Expose
    */
    private $updatedAt;

    public function __construct($name = 'New Item', $text = '', $order = 0, $repeated = false)
    {
        parent::__construct($name, $text, $order);
        $this->type = self::TYPE_TASK;
        $this->repeated = $repeated;
    }

    /**
     * Getter for type
     * @return mixed
     */
    public function getType()
    {
        return self::TYPE_TASK;
    }

    /**
    * @VirtualProperty
    */
    public function printableType()
    {
        return 'task';
    }

    /**
     * Getter for repeated
     * @return mixed
     */
    public function getRepeated()
    {
        return $this->repeated;
    }

    /**
     * Setter for repeated
     * @param mixed $repeated Value to set
     * @return self
     */
    public function setRepeated($repeated)
    {
        $this->repeated = $repeated;
        return $this;
    }


    public function isRepeated()
    {
        return $this->repeated;
    }

    /**
     * Getter for updatedAt
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Setter for updatedAt
     * @param mixed $updatedAt Value to set
     * @return self
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}