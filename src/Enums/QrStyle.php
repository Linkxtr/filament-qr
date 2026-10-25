<?php

declare(strict_types=1);

namespace Linkxtr\FilamentQr\Enums;

enum QrStyle: string
{
    case Square = 'square';
    case Dot = 'dot';
    case Round = 'round';
}
