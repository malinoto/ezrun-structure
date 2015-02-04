<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * test
 *
 * @ORM\Table(name="ezr_test")
 * @ORM\Entity
 */
class test
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var string
     *
     * @ORM\Column(name="test", type="string", length=16)
     */
    private $test;

    /**
     * @var string
     *
     * @ORM\Column(name="proba", type="string", nullable=false, columnDefinition="ENUM('enum1', 'enum2')")
     */
    private $proba;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return test
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set test
     *
     * @param string $test
     * @return test
     */
    public function setTest($test)
    {
        $this->test = $test;

        return $this;
    }

    /**
     * Get test
     *
     * @return string 
     */
    public function getTest()
    {
        return $this->test;
    }

    /**
     * Set proba
     *
     * @param string $proba
     * @return test
     */
    public function setProba($proba)
    {
        $this->proba = $proba;

        return $this;
    }

    /**
     * Get proba
     *
     * @return string 
     */
    public function getProba()
    {
        return $this->proba;
    }
}
