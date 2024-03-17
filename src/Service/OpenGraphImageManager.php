<?php

namespace App\Service;

use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\DriverInterface;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Typography\FontFactory;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(
    bind: [
        '$imageDriver' => '@og_image_driver',
        '$imagesStorage' => '@storage.og_image',
        '$projectDir' => '%kernel.project_dir%',
    ]
)]
final readonly class OpenGraphImageManager
{
    private const string IMAGE_EXTENSION = 'webp';
    private const int IMAGE_WIDTH = 1200;
    private const int IMAGE_HEIGHT = 600;

    private const array TITLE_SETTINGS = [
        'x' => 20,
        'y' => 50,
        'offsetWidth' => 20,
        'size' => 52,
        'color' => '#FFF',
        'position' => 'top',
        'font' => 'MonaspaceArgon-Regular.woff',
    ];

    private ImageManager $imageManager;

    private ImageInterface $image;

    public function __construct(
        private DriverInterface $imageDriver,
        private FilesystemOperator $imagesStorage,
        private string $projectDir,
    )
    {
        $this->imageManager = new ImageManager($this->imageDriver);
    }

    /**
     * @throws FilesystemException
     * @throws \Exception
     */
    public function get($title): string
    {
        $filename = $this->getFilename($title);

        if($this->imagesStorage->fileExists($filename)){
            return $this->imagesStorage->read($filename);
        }

        // Return a temporary static image here or implement your own logic.
        throw new \Exception('The image is not generated');
    }

    /**
     * @throws FilesystemException
     */
    public function generate(
        string $title,
        ?string $backgroundImage = null,
    ): void
    {
        $this->image = $this->imageManager->create(width: self::IMAGE_WIDTH, height: self::IMAGE_HEIGHT);

        if($backgroundImage !== null){
            $this->placeBackground($backgroundImage);
        }
        $this->setTitle($title);

        $this->encodeAndSave($title);
    }

    private function placeBackground(string $backgroundImage): void
    {
        $backgroundFile = sprintf(
            "%s/assets/%s",
            $this->projectDir,
            $backgroundImage
        );

        if(file_exists($backgroundFile) === false){
            return;
        }

        $this->image->place($backgroundFile);
    }

    private function setTitle(string $title): void
    {
        $this
            ->image
            ->text(
                text: $title,
                x: self::TITLE_SETTINGS['x'],
                y: self::TITLE_SETTINGS['y'],
                font: fn (FontFactory $font) =>
                    $font
                        ->filename(sprintf('%s/assets/fonts/%s', $this->projectDir, self::TITLE_SETTINGS['font']))
                        ->size(self::TITLE_SETTINGS['size'])
                        ->color(self::TITLE_SETTINGS['color'])
                        ->wrap(self::IMAGE_WIDTH - self::TITLE_SETTINGS['offsetWidth'])
                        ->valign(self::TITLE_SETTINGS['position'])
            );
        ;
    }

    /**
     * @throws FilesystemException
     */
    private function encodeAndSave(string $title): void
    {
        $filename = $this->getFilename($title);
        $image = $this->image->encodeByPath($filename)->toString();

        $this->imagesStorage->write(
            $filename,
            $image
        );
    }

    private function getFilename(string $title): string
    {
        return sprintf('%s.%s', md5($title), self::IMAGE_EXTENSION);
    }
}