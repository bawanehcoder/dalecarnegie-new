<?php

namespace App\Traits;

trait AvatarTrait
{
    public function avatar(): string
    {
        return $this->getFirstMediaUrl($this->collectionName, 'small') != '' ?
            $this->getFirstMediaUrl($this->collectionName, 'small') : asset('assets/img/logo.svg');
    }
}
