<?php

namespace Linkxtr\FilamentQr\Enums;

enum GradientType: string
{
    case Vertical = 'vertical';
    case Horizontal = 'horizontal';
    case Diagonal = 'diagonal';
    case InverseDiagonal = 'inverse_diagonal';
    case Radial = 'radial';
}
