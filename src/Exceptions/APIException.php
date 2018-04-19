<?php

namespace CreamIO\UserBundle\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * The exception handling all API errors.
 */
class APIException extends HttpException
{
    /**
     * Associated APIError object.
     *
     * @var APIError
     */
    private $APIError;

    /**
     * APIException constructor.
     *
     * @param APIError        $APIError Associated APIError object
     * @param \Exception|null $previous
     * @param array           $headers
     * @param int             $code
     */
    public function __construct(APIError $APIError, \Exception $previous = null, array $headers = [], $code = 0)
    {
        $this->APIError = $APIError;
        $statusCode = $APIError->getStatusCode();
        $message = $APIError->getTitle();
        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }

    /**
     * APIError getter.
     *
     * @return APIError
     */
    public function getAPIError(): APIError
    {
        return $this->APIError;
    }
}
