<?php

namespace App\Messenger\OpenGraphImage;

final readonly class GenerateOpenGraphImageMessage
{
    public function __construct(
        public string $title,
        public string $backgroundImage,
    ){
    }
}