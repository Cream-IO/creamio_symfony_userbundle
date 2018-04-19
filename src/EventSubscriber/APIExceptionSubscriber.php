<?php

namespace CreamIO\UserBundle\EventSubscriber;

use CreamIO\UserBundle\Exceptions\APIError;
use CreamIO\UserBundle\Exceptions\APIException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Simple event subscriber that converts exceptions to JSON format.
 */
class APIExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * Actually converts the exception to JSON format and sets the response accordingly.
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $e = $event->getException();
        if ($e instanceof APIException) {
            $APIError = $e->getAPIError();
        } else {
            $statusCode = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
            $APIError = new APIError(
                $statusCode,
                $e->getMessage()
            );
        }

        // Shitty fix for fucked up code from Symfony returning a 500 error on unauthenticated call to API (WTF ?)
        if ((false !== mb_strpos($e->getFile(), 'Firewall/ExceptionListener.php')) && ('Full authentication is required to access this resource.' === $e->getMessage())) {
            $APIError->setStatusCode(Response::HTTP_UNAUTHORIZED);
            $APIError->setType(APIError::UNAUTHORIZED_ACCESS);
            $APIError->setTitle(Response::$statusTexts[Response::HTTP_UNAUTHORIZED]);
        }

        $response = new JsonResponse(
            $APIError->toArray(),
            $APIError->getStatusCode()
        );
        $response->headers->set('Content-Type', 'application/problem+json');
        $event->setResponse($response);
    }

    /**
     * Kernel function to specify the event we listen to.
     *
     * @codeCoverageIgnore
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
