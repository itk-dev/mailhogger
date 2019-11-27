<?php

/*
 * This file is part of rimi-itk/mailhogger.
 *
 * (c) 2018–2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Entity\Website;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{name}", name="website_")
 */
class WebsiteController extends AbstractController
{
    /**
     * @var string
     */
    private $assetsPath;

    /**
     * @var string
     */
    private $templatesPath;

    /** @var ParameterBagInterface */
    private $parameters;

    /**
     * @var Client
     */
    private $client;

    public function __construct(ParameterBagInterface $parameters)
    {
        $this->parameters = $parameters;
        $this->assetsPath = __DIR__.'/../../Resources/mailhog/MailHog-UI/assets';
        $this->templatesPath = $this->assetsPath.'/templates';
    }

    /**
     * @Route("/", name="show")
     */
    public function showAction(Website $website): Response
    {
        $layout = file_get_contents($this->templatesPath.'/layout.html');
        $content = file_get_contents($this->templatesPath.'/index.html');
        $tokens = [
            '[: .Name :]' => $website->getName(),
            '[: .Content :]' => $content,
            '[: .APIHost :]' => $this->generateUrl('website_show', ['name' => $website->getName()]),
        ];

        return new Response(str_replace(array_keys($tokens), array_values($tokens), $layout));
    }

    /**
     * @Route("/{type}/{asset}", name="asset")
     *
     * @param mixed $type
     * @param mixed $asset
     *
     * @return BinaryFileResponse|RedirectResponse
     */
    public function assetAction(Website $website, $type, $asset)
    {
        $path = $type.'/'.$asset;

        if (('images/hog.png' === $path) && null !== $website->getLogoUrl()) {
            return $this->redirect($website->getLogoUrl());
        }

        $assetPath = $this->assetsPath.'/'.$path;
        if (!file_exists($assetPath)) {
            throw new NotFoundHttpException($path);
        }

        $file = new File($assetPath);

        return new BinaryFileResponse($file->getPathname(), 200, [
          'content-type' => $this->getMimeType($file),
        ]);
    }

    /**
     * @Route("/api/{path}", name="api", requirements={"path"=".+"})
     *
     * @param mixed $path
     *
     * @throws GuzzleException
     */
    public function apiAction(Request $request, Website $website, $path): Response
    {
        $method = $request->getMethod();
        $uri = '/api/'.$path;
        $query = $request->query->all();
        $headers = $request->headers->all();
        $content = $request->getContent();

        if ('DELETE' === $method && '/api/v1/messages' === $uri) {
            return $this->deleteAllMessages($request, $website);
        }

        list($body, $status, $headers) = $this->apiCall($method, $uri, $query, $headers, $content);

        if (preg_match('@^/api/v2/(messages|search)@', $uri)) {
            $body = $this->filterMessages($website, $body);
        }

        return new Response($body, $status, $headers);
    }

    private function filterMessages(Website $website, $body)
    {
        $data = json_decode((string) $body, false);

        $allowedSenders = $website->getSenderEmails();
        if (!empty($allowedSenders)) {
            $data->items = array_filter(
                $data->items,
                static function ($item) use ($allowedSenders) {
                    return isset($item->Raw->From) && \in_array(
                        $item->Raw->From,
                        $allowedSenders,
                        true
                    );
                }
            );
        }

        // Sort descending by date
        usort($data->items, static function ($a, $b) {
            return -strcmp($a->Created ?? null, $b->Created ?? null);
        });

        $data->total = $data->count = \count($data->items);

        return json_encode($data);
    }

    /**
     * @return null|string
     */
    private function getMimeType(File $file): string
    {
        switch ($file->getExtension()) {
            case 'css':
                return 'text/css';
            case 'js':
                return 'application/javascript';
            case 'png':
                return 'image/png';
        }

        return $file->getMimeType();
    }

    private function deleteAllMessages(Request $request, Website $website): Response
    {
        list($body, $status, $headers) = $this->apiCall('GET', '/api/v2/messages');
        $body = $this->filterMessages($website, $body);
        $data = json_decode($body, false);

        foreach ($data->items as $item) {
            $this->apiCall('DELETE', '/api/v1/messages/'.$item->ID);
        }

        return new Response();
    }

    /**
     * @param $method
     * @param $uri
     * @param array $query
     * @param array $headers
     * @param null  $content
     *
     * @throws GuzzleException
     */
    private function apiCall($method, $uri, $query = [], $headers = [], $content = null): array
    {
        $response = null;

        try {
            $response = $this->client()->request($method, $uri, [
                'headers' => $headers,
                'query' => $query,
                'body' => $content,
            ]);
        } catch (ClientException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            }
        }

        if (empty($response)) {
            throw new NotFoundHttpException();
        }

        $headers = array_filter($response->getHeaders(), static function ($header) {
            return !(0 === strcasecmp('transfer-encoding', $header));
        }, ARRAY_FILTER_USE_KEY);
        $body = $response->getBody();
        $status = $response->getStatusCode();

        return [$body, $status, $headers];
    }

    private function client(): Client
    {
        if (null === $this->client) {
            $this->client = new Client([
                'base_uri' => $this->parameters->get('mailhog_url'),
            ]);
        }

        return $this->client;
    }
}
