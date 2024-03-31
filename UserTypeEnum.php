<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Admin()
 * @method static static Doctor()
 * @method static static Patient()
 */
final class UserTypeEnum extends Enum
{
    const Admin = 1;
    const Doctor = 2;
    const Patient = 3;
}
