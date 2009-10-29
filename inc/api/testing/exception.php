<?php
/**
 * Exception used by the testing framework to test redirection.
 * 
 * When you set exception handler configuration this exception
 * should be rethrown.
 *
 * Example:
 * \code
 * exceptionhandler:
 *     api_testing_exception: rethrow
 * \endcode
 */
class api_testing_exception extends api_exception {
}
