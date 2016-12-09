<?php
/**
 * Class FranceConnectAuthenticatedVoter
 *
 * User: tveron
 * Date: 08/12/2016
 * Time: 15:00
 *
 * @package   KleeGroup\FranceConnectBundle\Security\Core\Authorization\Voter
 * @author    tveron
 * @copyright 2016 Klee Group
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @link      https://github.com/KleeGroup/FranceConnect-Symfony
 */

namespace KleeGroup\FranceConnectBundle\Security\Core\Authorization\Voter;


use KleeGroup\FranceConnectBundle\Security\Core\Authentication\Token\FranceConnectToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class FranceConnectAuthenticatedVoter implements VoterInterface
{
    const IS_FRANCE_CONNECT_AUTHENTICATED = 'IS_FRANCE_CONNECT_AUTHENTICATED';
    
    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return true;
    }
    
    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $object, array $attributes)
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
    
    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return null !== $attribute && static::IS_FRANCE_CONNECT_AUTHENTICATED === $attribute;
    }
    
}