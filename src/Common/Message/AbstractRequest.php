<?php
/**
 * Abstract Request
 */

namespace PhpAccounting\Common\Message;

use PhpAccounting\Common\Exception\InvalidRequestException;
use PhpAccounting\Common\Exception\RuntimeException;
use PhpAccounting\Common\Helper;
use PhpAccounting\Common\Http\Client;
use PhpAccounting\Common\Http\ClientInterface;
use PhpAccounting\Common\ItemBag;
use PhpAccounting\Common\ParametersTrait;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * Abstract Request
 *
 * This abstract class implements RequestInterface and defines a basic
 * set of functions that all Omnipay Requests are intended to include.
 *
 * Requests of this class are usually created using the createRequest
 * function of the gateway and then actioned using methods within this
 * class or a class that extends this class.
 *
 * Example -- creating a request:
 *
 * <code>
 *   class MyRequest extends \PhpAccounting\Common\Message\AbstractRequest {};
 *
 *   class MyGateway extends \PhpAccounting\Common\AbstractGateway {
 *     function myRequest($parameters) {
 *       $this->createRequest('MyRequest', $parameters);
 *     }
 *   }
 *
 *   // Create the gateway object
 *   $gw = Omnipay::create('MyGateway');
 *
 *   // Create the request object
 *   $myRequest = $gw->myRequest($someParameters);
 * </code>
 *
 * Example -- validating and sending a request:
 *
 * <code>
 *   try {
 *     $myRequest->validate();
 *     $myResponse = $myRequest->send();
 *   } catch (InvalidRequestException $e) {
 *     print "Something went wrong: " . $e->getMessage() . "\n";
 *   }
 *   // now do something with the $myResponse object, test for success, etc.
 * </code>
 *
 */
abstract class AbstractRequest implements RequestInterface
{
    use ParametersTrait {
        setParameter as traitSetParameter;
    }

    /**
     * The request client.
     *
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * The HTTP request object.
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $httpRequest;

    /**
     * An associated ResponseInterface.
     *
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var bool
     */
    protected $zeroAmountAllowed = true;

    /**
     * @var bool
     */
    protected $negativeAmountAllowed = false;

    /**
     * Create a new Request
     *
     * @param ClientInterface $httpClient  A HTTP client to make API calls with
     * @param HttpRequest     $httpRequest A Symfony HTTP request object
     */
    public function __construct(ClientInterface $httpClient, HttpRequest $httpRequest)
    {
        $this->httpClient = $httpClient;
        $this->httpRequest = $httpRequest;
        $this->initialize();
    }

    /**
     * Initialize the object with parameters.
     *
     * If any unknown parameters passed, they will be ignored.
     *
     * @param array $parameters An associative array of parameters
     *
     * @return $this
     * @throws RuntimeException
     */
    public function initialize(array $parameters = array())
    {
        if (null !== $this->response) {
            throw new RuntimeException('Request cannot be modified after it has been sent!');
        }

        $this->parameters = new ParameterBag;

        Helper::initialize($this, $parameters);

        return $this;
    }

    /**
     * Set a single parameter
     *
     * @param string $key The parameter key
     * @param mixed $value The value to set
     * @return $this
     * @throws RuntimeException if a request parameter is modified after the request has been sent.
     */
    protected function setParameter($key, $value)
    {
        if (null !== $this->response) {
            throw new RuntimeException('Request cannot be modified after it has been sent!');
        }

        return $this->traitSetParameter($key, $value);
    }

    /**
     * Gets the test mode of the request from the gateway.
     *
     * @return boolean
     */
    public function getTestMode()
    {
        return $this->getParameter('testMode');
    }

    /**
     * Sets the test mode of the request.
     *
     * @param boolean $value True for test mode on.
     * @return $this
     */
    public function setTestMode($value)
    {
        return $this->setParameter('testMode', $value);
    }

    /**
     * Get the request description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getParameter('description');
    }

    /**
     * Sets the request description.
     *
     * @param string $value
     * @return $this
     */
    public function setDescription($value)
    {
        return $this->setParameter('description', $value);
    }

    /**
     * Get the transaction ID.
     *
     * The transaction ID is the identifier generated by the merchant website.
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->getParameter('transactionId');
    }

    /**
     * Sets the transaction ID.
     *
     * @param string $value
     * @return $this
     */
    public function setTransactionId($value)
    {
        return $this->setParameter('transactionId', $value);
    }

    /**
     * Get the transaction reference.
     *
     * The transaction reference is the identifier generated by the remote
     * payment gateway.
     *
     * @return string
     */
    public function getTransactionReference()
    {
        return $this->getParameter('transactionReference');
    }

    /**
     * Sets the transaction reference.
     *
     * @param string $value
     * @return $this
     */
    public function setTransactionReference($value)
    {
        return $this->setParameter('transactionReference', $value);
    }

    /**
     * A list of items in this order
     *
     * @return ItemBag|null A bag containing items in this order
     */
    public function getItems()
    {
        return $this->getParameter('items');
    }

    /**
     * Set the items in this order
     *
     * @param ItemBag|array $items An array of items in this order
     * @return $this
     */
    public function setItems($items)
    {
        if ($items && !$items instanceof ItemBag) {
            $items = new ItemBag($items);
        }

        return $this->setParameter('items', $items);
    }

    /**
     * Get the client IP address.
     *
     * @return string
     */
    public function getClientIp()
    {
        return $this->getParameter('clientIp');
    }

    /**
     * Sets the client IP address.
     *
     * @param string $value
     * @return $this
     */
    public function setClientIp($value)
    {
        return $this->setParameter('clientIp', $value);
    }

    /**
     * Get the request return URL.
     *
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->getParameter('returnUrl');
    }

    /**
     * Sets the request return URL.
     *
     * @param string $value
     * @return $this
     */
    public function setReturnUrl($value)
    {
        return $this->setParameter('returnUrl', $value);
    }

    /**
     * Get the request cancel URL.
     *
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->getParameter('cancelUrl');
    }

    /**
     * Sets the request cancel URL.
     *
     * @param string $value
     * @return $this
     */
    public function setCancelUrl($value)
    {
        return $this->setParameter('cancelUrl', $value);
    }

    /**
     * Get the request notify URL.
     *
     * @return string
     */
    public function getNotifyUrl()
    {
        return $this->getParameter('notifyUrl');
    }

    /**
     * Sets the request notify URL.
     *
     * @param string $value
     * @return $this
     */
    public function setNotifyUrl($value)
    {
        return $this->setParameter('notifyUrl', $value);
    }

    /**
     * Get the payment issuer.
     *
     * This field is used by some European gateways, and normally represents
     * the bank where an account is held (separate from the card brand).
     *
     * @return string
     */
    public function getIssuer()
    {
        return $this->getParameter('issuer');
    }

    /**
     * Set the payment issuer.
     *
     * This field is used by some European gateways, and normally represents
     * the bank where an account is held (separate from the card brand).
     *
     * @param string $value
     * @return $this
     */
    public function setIssuer($value)
    {
        return $this->setParameter('issuer', $value);
    }

    /**
     * Get the payment issuer.
     *
     * This field is used by some European gateways, which support
     * multiple payment providers with a single API.
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->getParameter('paymentMethod');
    }

    /**
     * Set the payment method.
     *
     * This field is used by some European gateways, which support
     * multiple payment providers with a single API.
     *
     * @param string $value
     * @return $this
     */
    public function setPaymentMethod($value)
    {
        return $this->setParameter('paymentMethod', $value);
    }

    /**
     * Send the request
     *
     * @return ResponseInterface
     */
    public function send()
    {
        $data = $this->getData();

        return $this->sendData($data);
    }

    /**
     * Get the associated Response.
     *
     * @return ResponseInterface
     */
    public function getResponse()
    {
        if (null === $this->response) {
            throw new RuntimeException('You must call send() before accessing the Response!');
        }

        return $this->response;
    }
}