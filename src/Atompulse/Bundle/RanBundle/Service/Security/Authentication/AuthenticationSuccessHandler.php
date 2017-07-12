<?php
namespace Atompulse\RanBundle\Service\Security\Authentication;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * Custom authentication handler
 * Class AuthenticationHandler
 * @package  Atompulse\RanBundle\Services\Authentication
 *
 * @author Ionut Pisla <ionut.tudorel@gmail.com>
 */
class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /**
     * @param HttpUtils $httpUtils
     * @param array $options
     */
    public function __construct(HttpUtils $httpUtils, array $options)
    {
        parent::__construct($httpUtils, $options);
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if ($request->isXmlHttpRequest()) {
            $response = new JsonResponse(
                [
                    'status' => true,
                    'msg' => 'Authenticated',
                    'data' => [
                        'username' => $token->getUsername()
                    ]
                ]
            );
        } else {
            $response = parent::onAuthenticationSuccess($request, $token);
        }

        return $response;
    }
}