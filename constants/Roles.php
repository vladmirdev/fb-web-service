<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 08.09.17
 * Time: 14:29
 */

namespace app\constants;

class Roles
{
    const USER                  = 'user';

    const FREE                  = 'free';
    const NOTES                 = 'notes';
    const HERBS                 = 'herbs';
    const FORMULAS              = 'formulas';
    const ACUPUNCTURE           = 'acupuncture';

    const EDITOR                = 'editor';
    const MODERATOR             = 'moderator';
    const ADMINISTRATOR         = 'administrator';

    const DEFAULT_ROLE = self::FREE;
}
