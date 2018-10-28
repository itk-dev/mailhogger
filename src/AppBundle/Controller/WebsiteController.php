<?php

/*
 * This file is part of Symfony MailHogger.
 *
 * (c) 2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Website;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/site/{name}")
 */
class WebsiteController extends Controller
{
    /**
     * @var array
     */
    private $configuration;

    private $assetsPath;
    private $templatesPath;

    public function __construct()
    {
        $this->assetsPath = __DIR__.'/../../../app/Resources/mailhog/MailHog-UI/assets';
        $this->templatesPath = $this->assetsPath.'/templates';
    }

    /**
     * @Route("/", name="website_show")
     */
    public function showAction(Website $website)
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
     * @Route("/{type}/{asset}", name="website_asset")
     *
     * @param mixed $type
     * @param mixed $asset
     */
    public function assetAction(Website $website, $type, $asset)
    {
        $path = $type.'/'.$asset;

        if ('images/hog.png' === $path) {
            if (null !== $website->getLogoUrl()) {
                return $this->redirect($website->getLogoUrl());
            }
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
     * @Route("/api/{path}", name="website_api", requirements={"path"=".+"})
     *
     * @param mixed $path
     */
    public function apiAction(Request $request, Website $website, $path)
    {
        $client = new Client([
          'base_uri' => $this->getParameter('mailhog_url'),
        ]);

        $method = $request->getMethod();
        $uri = '/api/'.$path;
        $query = $request->query->all();
        $headers = $request->headers->all();
        $content = $request->getContent();

        $response = null;

        try {
            $response = $client->request($method, $uri, [
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

        $headers = array_filter($response->getHeaders(), function ($header) {
            if (0 === strcasecmp('transfer-encoding', $header)) {
                return false;
            }

            return true;
        }, ARRAY_FILTER_USE_KEY);
        $body = $response->getBody();
        $status = $response->getStatusCode();

        if (preg_match('@^/api/v2/(messages|search)@', $uri)) {
            $body = $this->filterMessages($website, $body);
        }

        return new Response($body, $status, $headers);
    }

    private function filterMessages(Website $website, $body)
    {
        $data = \json_decode((string) $body);

        $allowedSenders = $website->getSenderEmails();
        if (!empty($allowedSenders)) {
            $data->items = array_filter(
                $data->items,
                function ($item) use ($allowedSenders) {
                    return isset($item->Raw->From) && \in_array(
                            $item->Raw->From,
                            $allowedSenders,
                            true
                        );
                }
            );
        }

        // Sort descending by date
        usort($data->items, function ($a, $b) {
            return -strcmp(
                isset($a->Created) ? $a->Created : null,
                isset($b->Created) ? $b->Created : null
            );
        });

        $data->total = $data->count = \count($data->items);

        return \json_encode($data);
    }

    private function getMimeType(File $file)
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
}
