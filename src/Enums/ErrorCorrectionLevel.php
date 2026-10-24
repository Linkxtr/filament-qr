<?php

namespace Linkxtr\FilamentQr\Enums;

enum ErrorCorrectionLevel: string
{
    case Low = 'L';
    case Medium = 'M';
    case Quartile = 'Q';
    case High = 'H';
}
