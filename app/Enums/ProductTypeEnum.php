<?php

namespace App\Enums;

use Filament\Support\Enums\Enum;

enum ProductTypeEnum: string
{
    case DELIVERABLE = 'deliverable';
    case DOWNLOADABLE = 'downloadable';
}
