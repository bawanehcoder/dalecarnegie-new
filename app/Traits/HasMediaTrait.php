<?php

namespace App\Traits;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\UrlGenerator\UrlGeneratorFactory;
trait HasMediaTrait
{
    use InteractsWithMedia;

    public function getFirstOrDefaultMediaUrl(string $collectionName = 'default', string $conversionName = ''): string
    {
        $url = $this->getFirstMediaUrl($collectionName, $conversionName);
        return $url ? $url : $this::$defaultImage ?? '/images/notFound.png';
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $width = $media->getCustomProperty('width') ?? 100;
        $height = $media->getCustomProperty('height') ?? 100;
        $defaulSmalltWidth  = 100;
        $defaultSmallHeight = 100;
        $defaultMediumWidth  = 500;
        $defaultMediumHeight = 500;
        $defaultLargeWidth  = 1000;
        $defaultLargeHeight = 1000;

        $this->addMediaConversion('small')
            ->width($width < $defaulSmalltWidth ? $width : $defaulSmalltWidth)
            ->height($height < $defaultSmallHeight ? $height : $defaultSmallHeight)
            ->keepOriginalImageFormat()
            ->sharpen(10)->nonQueued(); // By default, a conversion will be added to the queue

        $this->addMediaConversion('medium')
            ->width($width < $defaultMediumWidth ? $width : $defaultMediumWidth)
            ->height($height < $defaultMediumHeight ? $height : $defaultMediumHeight)
            ->keepOriginalImageFormat()
            ->sharpen(10)->nonQueued(); // By default, a conversion will be added to the queue

        $this->addMediaConversion('large')
            ->width($width < $defaultLargeWidth ? $width : $defaultLargeWidth)
            ->height($height < $defaultLargeHeight ? $height : $defaultLargeHeight)
            ->keepOriginalImageFormat()
            ->sharpen(10)->nonQueued(); // By default, a conversion will be added to the queue

    }

    public function getUrls()
    {

        $type = ($this->getTypeFromMime());
        $urls = [
            'type' => $type,
            'mime_type' => $this->extension,
            'url' => $this->getUrl(),
        ];

        $urls['thumbs']['small'] = $this->hasGeneratedConversion('small') ? $this->getStaticUrl('small') : null;
        $urls['thumbs']['medium'] = $this->hasGeneratedConversion('medium') ? $this->getStaticUrl('medium') : null;
        $urls['thumbs']['large'] = $this->hasGeneratedConversion('large') ? $this->getStaticUrl('large') : null;
        $urls['thumbs']['original'] = $this->getStaticUrl('original');

        return $urls;
    }
}
