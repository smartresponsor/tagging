<?php

declare(strict_types=1);

namespace App\Tagging\Value\Form\Config;

final class TaggingPolicyConfigData
{
    public string $maxLength = '64';

    public string $maxTagsPerEntity = '20';

    public string $lowercaseNormalize = '1';

    public string $collapseSpaces = '1';

    public string $stripSymbols = '1';

    public string $defaultLocale = 'en-US';

    public string $allowedLocales = 'en-US,uk-UA,ru-RU';
}
