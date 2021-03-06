<?php

declare(strict_types=1);

namespace Api\User\Entity;

use Api\App\Common\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Laminas\Stdlib\ArraySerializableInterface;

/**
 * Class UserAvatarEntity
 * @ORM\Entity()
 * @ORM\Table(name="user_avatar")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\EntityListeners({Api\User\EventListener\UserAvatarEventListener::class})
 * @package Api\User\Entity
 */
class UserAvatarEntity extends AbstractEntity implements ArraySerializableInterface
{
    /**
     * @ORM\OneToOne(targetEntity="UserEntity", inversedBy="avatar")
     * @ORM\JoinColumn(name="userUuid", referencedColumnName="uuid", nullable=false)
     * @var UserEntity $user
     */
    protected $user;

    /**
     * @ORM\Column(name="name", type="string", length=191, nullable=false)
     * @var $name
     */
    protected $name;

    /** @var string $url */
    protected $url;

    /**
     * UserAvatarEntity constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return UserEntity
     */
    public function getUser(): UserEntity
    {
        return $this->user;
    }

    /**
     * @param UserEntity $user
     * @return $this
     */
    public function setUser(UserEntity $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Helper methods
     */

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Return an array representation of the object
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return [
            'uuid' => $this->getUuid()->toString(),
            'url' => $this->getUrl(),
            'created' => $this->getCreated(),
            'updated' => $this->getUpdated()
        ];
    }
}
