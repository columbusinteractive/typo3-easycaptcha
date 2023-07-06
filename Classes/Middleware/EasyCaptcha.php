<?php

namespace ColumbusInteractive\EasyCaptcha\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Routing\PageArguments;
use ColumbusInteractive\EasyCaptcha\Service\Captcha;

use Laminas\Captcha\Image;
use RuntimeException;
use TYPO3\CMS\Core\Core\Environment;
use Laminas\Session\Container;
use Laminas\Session\SessionManager;

class EasyCaptcha implements MiddlewareInterface
{
    private const PUBLIC_CAPTCHA_DIRECTORY = 'captcha';

    protected ResponseFactoryInterface $responseFactory;

    public function __construct(
        ResponseFactoryInterface $responseFactory
    ) {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(
        ServerRequestInterface  $request,
        RequestHandlerInterface $handler
    ): ResponseInterface
    {
        // get url
        $normalizedParams = $request->getAttribute('normalizedParams');
        $requestUri = $normalizedParams->getRequestUri();

        $segments = explode('/', $requestUri);

        if ($segments[1] != 'easycaptcha') {
            // call next middleware
            return $handler->handle($request);
        }

        if (str_starts_with($segments[2], 'image')) {
            $captchaService = new Captcha();
            $captcha = $captchaService->getCaptcha(true);

            $captchaPath = $captchaService->getPublicCaptchaPath();

            // Generate captcha
            $path = $captchaPath . '/' . $captcha->generate() . $captcha->getSuffix();

            $im = imagecreatefrompng($path);
            $stream = fopen('php://memory','r+');
            imagepng($im, $stream);
            rewind($stream);

            // render captcha image
            $response = $this->responseFactory->createResponse()->withHeader('Content-Type', 'image/png');
            $response->getBody()->write(stream_get_contents($stream));

            return $response;
        }

        if ($segments[2] == 'tts') {
            $captchaService = new Captcha();
            $captcha = $captchaService->getCaptcha();

            $word = $captcha->getWord();
            $json = json_encode([
                'word' => $word !== null
                    ? implode(' ', str_split($word))
                    : null,
            ]);

            $response = $this->responseFactory->createResponse()->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write($json);

            return $response;
        }
    }
}
