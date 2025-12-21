<?php

namespace App;

enum Service: string
{
    case GAS = 'gas';
    case ELECTRICITY = 'electricity';
    case HEATING = 'heating';
    case TERRITORY = 'territory';
    case WATER = 'water';
}
