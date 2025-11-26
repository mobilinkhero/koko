<?php

namespace App\Enum\Tenant;

enum Languages: string
{
    case AFRIKAANS = 'afrikaans';
    case ARABIC = 'arabic';
    case ARMENIAN = 'armenian';
    case AZERBAIJANI = 'azerbaijani';
    case BELARUSIAN = 'belarusian';
    case BOSNIAN = 'bosnian';
    case BULGARIAN = 'bulgarian';
    case CATALAN = 'catalan';
    case CHINESE = 'chinese';
    case CROATIAN = 'croatian';
    case CZECH = 'czech';
    case DANISH = 'danish';
    case DUTCH = 'dutch';
    case ENGLISH = 'english';
    case ESTONIAN = 'estonian';
    case FINNISH = 'finnish';
    case FRENCH = 'french';
    case GALICIAN = 'galician';
    case GERMAN = 'german';
    case GREEK = 'greek';
    case GUJARATI = 'gujarati';
    case HINDI = 'hindi';
    case HEBREW = 'hebrew';
    case HUNGARIAN = 'hungarian';
    case ICELANDIC = 'icelandic';
    case INDONESIAN = 'indonesian';
    case ITALIAN = 'italian';
    case JAPANESE = 'japanese';
    case KANNADA = 'kannada';
    case KAZAKH = 'kazakh';
    case KOREAN = 'korean';
    case LATVIAN = 'latvian';
    case LITHUANIAN = 'lithuanian';
    case MACEDONIAN = 'macedonian';
    case MALAY = 'malay';
    case MARATHI = 'marathi';
    case MAORI = 'maori';
    case NEPALI = 'nepali';
    case NORWEGIAN = 'norwegian';
    case PERSIAN = 'persian';
    case POLISH = 'polish';
    case PORTUGUESE = 'portuguese';
    case ROMANIAN = 'romanian';
    case RUSSIAN = 'russian';
    case SERBIAN = 'serbian';
    case SLOVAK = 'slovak';
    case SLOVENIAN = 'slovenian';
    case SPANISH = 'spanish';
    case SWAHILI = 'swahili';
    case SWEDISH = 'swedish';
    case TAGALOG = 'tagalog';
    case TAMIL = 'tamil';
    case THAI = 'thai';
    case TURKISH = 'turkish';
    case UKRAINIAN = 'ukrainian';
    case URDU = 'urdu';
    case VIETNAMESE = 'vietnamese';
    case WELSH = 'welsh';

    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }
}
