<?php

namespace App\Enums;

enum MessageCode: string
{
    case ADM_005_1 = "RECORD_FLAT_EXIST";
    case ADM_005_2 = "RECORD_SLIDE_EXIST";
    case ADM_005_3 = "CONTRACT_WITH_MAX_AMOUNT_0_EXIST";
}