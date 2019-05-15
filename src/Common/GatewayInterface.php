<?php
/**
 * Payment gateway interface
 */

namespace PHPAccounting\Common;

/**
 * Payment gateway interface
 *
 * This interface class defines the standard functions that any
 * Omnipay gateway needs to define.
 *
 *
 * @method \PHPAccounting\Common\Message\RequestInterface entity(array $options = array())              (Required method)
 *         Delete a stored card
 *  * @method \PHPAccounting\Common\Message\RequestInterface create(array $options = array())              (Optional method)
 *         Delete a stored card
 *  * @method \PHPAccounting\Common\Message\RequestInterface update(array $options = array())              (Optional method)
 *         Delete a stored card
 *  * @method \PHPAccounting\Common\Message\RequestInterface get(array $options = array())              (Optional method)
 *         Delete a stored card
 *  * @method \PHPAccounting\Common\Message\RequestInterface delete(array $options = array())              (Optional method)
 *         Delete a stored card
 *
 */
interface GatewayInterface
{
    /**
     * Get gateway display name
     *
     * This can be used by carts to get the display name for each gateway.
     * @return string
     */
    public function getName();

    /**
     * Get gateway short name
     *
     * This name can be used with GatewayFactory as an alias of the gateway class,
     * to create new instances of this gateway.
     * @return string
     */
    public function getShortName();

    /**
     * Define gateway parameters, in the following format:
     *
     * array(
     *     'username' => '', // string variable
     *     'testMode' => false, // boolean variable
     *     'landingPage' => array('billing', 'login'), // enum variable, first item is default
     * );
     * @return array
     */
    public function getDefaultParameters();

    /**
     * Initialize gateway with parameters
     * @return $this
     */
    public function initialize(array $parameters = array());

    /**
     * Get all gateway parameters
     * @return array
     */
    public function getParameters();

}
