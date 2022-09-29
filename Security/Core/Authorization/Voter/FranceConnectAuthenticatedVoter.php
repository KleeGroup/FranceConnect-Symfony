<?php

namespace KleeGroup\FranceConnectBundle\Security\Core\Authorization\Voter;

use KleeGroup\FranceConnectBundle\Security\Core\Authentication\Token\FranceConnectToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class FranceConnectAuthenticatedVoter implements VoterInterface
{
    public const IS_FRANCE_CONNECT_AUTHENTICATED = 'IS_FRANCE_CONNECT_AUTHENTICATED';
    
    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }
            
            return $token instanceof FranceConnectToken ?
                VoterInterface::ACCESS_GRANTED :
                VoterInterface::ACCESS_DENIED;
        }
        
        return VoterInterface::ACCESS_ABSTAIN;
    }
    
    public function supportsAttribute($attribute): bool
    {
        return static::IS_FRANCE_CONNECT_AUTHENTICATED === $attribute;
    }
    
}