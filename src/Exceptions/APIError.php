<?php

namespace CreamIO\UserBundle\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * Basic class definition handling the struture of an API error to handle as exception.
 */
class APIError
{
    const INVALID_CONTENT_TYPE = 'Invalid content type, please send application/json content';
    const INVALID_UUID_ERROR = 'Invalid id, format must be uuid';
    const RESOURCE_NOT_FOUND = "The resource you have requested can't be found";
    const VALIDATION_ERROR = 'Error while validating ressource insertion/update';
    const UNAUTHORIZED_ACCESS = 'You must authenticate to access to this ressource';

    /**
     * Response status code.
     *
     * @var int
     */
    private $statusCode;

    /**
     * Error type, some are defined in constants above.
     *
     * @var string
     */
    private $type;

    /**
     * Error title, converted to text from the status code through Response::$statusTexts, basic HTTP response code description.
     *
     * @var string
     */
    private $title;

    /**
     * Optionnal additionnal informations to transmit to the client.
     *
     * @var array
     */
    private $extraData = [];

    /**
     * APIError constructor.
     *
     * @param int    $statusCode
     * @param string $type
     */
    public function __construct(int $statusCode, string $type = 'Unknown error type')
    {
        $this->statusCode = $statusCode;
        $title = Response::$statusTexts[$statusCode] ?? 'Unknown status code';
        $this->type = $type;
        $this->title = $title;
    }

    /**
     * Converts APIError datas to an array for later serialization.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(
            [
                'status' => 'error',
                'code' => $this->statusCode,
                'type' => $this->title,
                'reason' => $this->type,
                'additional-informations' => $this->extraData,
            ]
        );
    }

    /**
     * Sets additional data.
     *
     * @param string $name  ExtraData array key
     * @param mixed  $value Content
     *
     * @return APIError
     */
    public function set(string $name, $value): self
    {
        $this->extraData[$name] = $value;

        return $this;
    }

    /**
     * StatusCode getter.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * StatusCode setter.
     *
     * @param int $statusCode
     *
     * @return APIError
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Title getter.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Title setter.
     *
     * @param string $title
     *
     * @return APIError
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Type setter.
     *
     * @param string $type
     *
     * @return APIError
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Type getter.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
