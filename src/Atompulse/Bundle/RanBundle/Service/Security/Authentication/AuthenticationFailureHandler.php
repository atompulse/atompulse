<?php
namespace Atompulse\Bundle\RanBundle\Service\Security\Authentication;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Custom authentication handler
 * Class AuthenticationHandler
 * @package  Atompulse\Bundle\RanBundle\Services\Authentication
 *
 * @author Ionut Pisla <ionut.tudorel@gmail.com>
 */
class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    /**
     * @var CsrfTokenManagerInterface
     */
    protected $tokenManager;

    public function __construct(HttpKernelInterface $httpKernel, HttpUtils $httpUtils, array $options, LoggerInterface $logger = null)
    {
        parent::__construct($httpKernel, $httpUtils, $options, $logger);
    }

    /**
     * @param CsrfTokenManagerInterface $tokenManager
     */
    public function setTokenManager(CsrfTokenManagerInterface $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->isXmlHttpRequest()) {

            $token = $this->tokenManager->getToken('authenticate');
            if (!$this->tokenManager->isTokenValid($token)) {
                $token = $this->tokenManager->refreshToken('authenticate');
            }

            $arrHeaders = [];
            // add refreshed token to headers
            $arrHeaders['Ran-Auth-Token'] = $token->getValue();

            $response = new JsonResponse(
                [
                    'status' => false,
                    'msg' => $exception->getMessage(),
                    'data' => [
                        'exception' => [
                            'message' => $exception->getMessage(),
//                            'file' => "{$exception->getFile()}:{$exception->getLine()}",
//                            'trace' => $exception->getTraceAsString()
                        ]
                    ]
                ],
                301,
                $arrHeaders
            );
        } else {
            $response = parent::onAuthenticationFailure($request, $exception);
        }

        return $response;
    }
}
