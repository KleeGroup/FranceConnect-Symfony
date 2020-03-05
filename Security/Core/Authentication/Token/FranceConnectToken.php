<?php
/**
 * Class FranceConnectToken
 *
 * User: tveron
 * Date: 08/12/2016
 * Time: 15:01
 *
 * @package   KleeGroup\FranceConnectBundle\Security\Core\Authentication\Token
 * @author    tveron
 * @copyright 2016 Klee Group
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @link      https://github.com/KleeGroup/FranceConnect-Symfony
 */

namespace KleeGroup\FranceConnectBundle\Security\Core\Authentication\Token;


use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class FranceConnectToken extends AbstractToken
{
    /**
     * @var string
     */
    private $fcIdentity;

    /**
     * @param array $identity
     * @param array $roles
     */
    public function __construct(array $identity, array $roles = [])
    {
        parent::__construct($roles);
        $this->setAuthenticated(count($this->getRoles()) > 0);
        $this->fcIdentity = $identity;
        $this->setUser('anon.');
    }

    /**
     * @return string
     */
    public function getIdentity()
    {
        return $this->fcIdentity;
    }

    /**
     * @return void
     */
    public function getCredentials()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize([$this->fcIdentity, parent::serialize()]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($str)
    {
        list($this->fcIdentity, $parentStr) = unserialize($str);
        parent::unserialize($parentStr);
    }

    /**
     * {@inheritdoc}
     */
    public function __serialize(): array
    {
        return [$this->fcIdentity, parent::__serialize()];
    }

    /**
     * {@inheritdoc}
     */
    public function __unserialize(array $data): void
    {
        [$this->fcIdentity, $parentData] = $data;
        parent::__unserialize($parentData);
    }
}
