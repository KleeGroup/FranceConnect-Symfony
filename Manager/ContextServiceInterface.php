<?php
/**
 * Created by PhpStorm.
 * User: tveron
 * Date: 29/07/2016
 * Time: 17:22
 */

namespace KleeGroup\FranceConnectBundle\Manager;

/**
 * Interface ContextServiceInterface
 *
 * @package KleeGroup\FranceConnectBundle\Service
 */
interface ContextServiceInterface
{
    /**
     *
     * Get Authorization URL with query string.
     *
     * @return string
     */
    public function generateAuthorizationURL();
    
    /**
     *
     * Returns data provided by FranceConnect.
     *
     * @param array $params query string parameters
     *
     * @return string json
     */
    public function getUserInfo(array $params);
    
    /**
     *
     * Get Logout URL with query string.
     *
     * @return string
     */
    public function generateLogoutURL();
}