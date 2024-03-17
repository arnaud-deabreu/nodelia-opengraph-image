<?php

namespace App\Messenger\OpenGraphImage;

use App\Service\OpenGraphImageManager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GenerateOpenGraphImageMessageHandler
{
    public function __construct(
        private OpenGraphImageManager $imageManager,
    ){
    }

    public function __invoke(GenerateOpenGraphImageMessage $message): void
    {
        $this->imageManager->generate(
            $message->title,
            $message->backgroundImage
        );
    }
}