<?php

namespace App\Handlers;

use App\Handlers\ErrorHandler as HttpErrorHandler;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\ResponseEmitter;

/**
 * https://www.slimframework.com/docs/v4/objects/application.html#advanced-shutdown-handler
 */
class ShutdownHandler
{
    private HttpErrorHandler $errorHandler;


    public function __construct(HttpErrorHandler $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    public function __invoke()
    {
        $error = error_get_last();
        if (!$error) {
            return;
        }

        $errorFile = $error['file'];
        $errorLine = $error['line'];
        $errorMessage = $error['message'];
        $errorType = $error['type'];
        $message = 'An error while processing your request. Please try again later.';

        if ($this->errorHandler->getDisplayErrorDetails()) {
            switch ($errorType) {
                case E_USER_ERROR:
                    $message = "FATAL ERROR: {$errorMessage}. ";
                    $message .= " on line {$errorLine} in file {$errorFile}.";
                    break;

                case E_USER_WARNING:
                    $message = "WARNING: {$errorMessage}";
                    break;

                case E_USER_NOTICE:
                    $message = "NOTICE: {$errorMessage}";
                    break;

                default:
                    $message = "ERROR: {$errorMessage}";
                    $message .= " on line {$errorLine} in file {$errorFile}.";
                    break;
            }
        }

        $exception = new HttpInternalServerErrorException($this->errorHandler->getRequest(), $message);
        $response = $this->errorHandler->__invoke($this->errorHandler->getRequest(), $exception, $this->errorHandler->getDisplayErrorDetails(), false, false);

        if (ob_get_length()) {
            ob_clean();
        }

        $responseEmitter = new ResponseEmitter();
        $responseEmitter->emit($response);
    }
}