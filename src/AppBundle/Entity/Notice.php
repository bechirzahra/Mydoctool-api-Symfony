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
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NoticeRepository")
 */
class Notice extends Item
{
    public function __construct($name = 'New Item', $text = '', $order = 0)
    {
        parent::__construct($name, $text, $order);
        $this->type = self::TYPE_NOTICE;
    }

    /**
     * Getter for type
     * @return mixed
     */
    public function getType()
    {
        return self::TYPE_NOTICE;
    }

    /**
    * @VirtualProperty
    */
    public function printableType()
    {
        return 'notice';
    }
}