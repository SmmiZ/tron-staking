<?php

namespace App\Enums;

enum Statuses: int
{
    case new = 1;
    case pending = 2;
    case completed = 3;
    case declined = 4;
}
