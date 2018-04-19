<?php

use CreamIO\UserBundle\Entity\BUser;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Symfony\Component\HttpKernel\KernelInterface;

class FeatureContext extends RawMinkContext
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    use KernelDictionary;

    /**
     * FeatureContext constructor.
     *
     * @param $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @Given the user table is empty
     */
    public function theUserTableIsEmpty(): void
    {
        $em = $this->kernel->getContainer()->get('doctrine')->getManager();
        $em->createQuery('DELETE FROM App:BackOffice\BUser')->execute();
    }

    /**
     * @Given I load a predictable user in database and get it's id
     */
    public function createPredictableUser(): string
    {
        $em = $this->kernel->getContainer()->get('doctrine')->getManager();
        $user = new BUser();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setPlainPassword('testPassword');
        $user->setDescription('Test decription');
        $user->setEmail('user_get_test@test.com');
        $user->setJob('Test Job');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setUsername('testUserName');
        $user->setPassword('$2y$15$sSWkwOrLZ5TSxUNJruGToObnz.p3XsbdBYrvVHZAAmJ2YeIJAil5S');
        $em->persist($user);
        $em->flush();

        return $user->getId();
    }
}
