<?php

namespace KleeGroup\FranceConnectBundle\Security\Core\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class FranceConnectToken extends AbstractToken
{
    private array $fcIdentity;

    public function __construct(array $identity, array $roles = [])
    {
        parent::__construct($roles);
        $this->setAuthenticated(count($this->getRoleNames()) > 0);
        $this->fcIdentity = $identity;
        $this->setUser('anon.');
    }

    /**
     * @return array
     */
    public function getIdentity()
    {
        return $this->fcIdentity;
    }

    public function getCredentials()
    {
        return '';
    }

    public function __unserialize(array $data): void
    {
        [$this->fcIdentity, $parentData] = $data;
        $parentData = \is_array($parentData) ? $parentData : unserialize($parentData);
        parent::__unserialize($parentData);
    }

    public function __serialize(): array
    {
        return [$this->fcIdentity, parent::__serialize()];
    }
}
