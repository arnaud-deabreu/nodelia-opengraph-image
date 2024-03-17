<?php

namespace App\Controller;

use App\Messenger\OpenGraphImage\GenerateOpenGraphImageMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

final class PreviewImageController extends AbstractController
{
    #[Route(
        path: 'generate_async',
        name: 'generate_async',
        methods: [Request::METHOD_GET]
    )]
    public function testGeneration(MessageBusInterface $bus): Response
    {
        $bus->dispatch(
            new GenerateOpenGraphImageMessage(
                title: 'This is my title '.uniqid(),
                backgroundImage: 'images/amp-min.jpg'
            )
        );

        return new Response();
    }
}