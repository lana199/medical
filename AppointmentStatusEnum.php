<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Pending()
 * @method static static Confirmed()
 * @method static static Canceled()
 */
final class AppointmentStatusEnum extends Enum
{
    const Pending =   1;
    const Confirmed =   2;
    const Canceled = 3;
}
