<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public website languages
    |--------------------------------------------------------------------------
    | The most widely spoken languages among Hindus in India. English is the source language;
    | the others are served from lang/{code}.json. `font` is the Google "Noto Sans" family that
    | carries that script (loaded only when that language is selected).
    */
    'locales' => [
        'en' => ['native' => 'English',   'font' => null],
        'hi' => ['native' => 'हिन्दी',      'font' => 'Noto Sans Devanagari'],
        'te' => ['native' => 'తెలుగు',     'font' => 'Noto Sans Telugu'],
        'ta' => ['native' => 'தமிழ்',      'font' => 'Noto Sans Tamil'],
        'kn' => ['native' => 'ಕನ್ನಡ',      'font' => 'Noto Sans Kannada'],
        'ml' => ['native' => 'മലയാളം',    'font' => 'Noto Sans Malayalam'],
        'mr' => ['native' => 'मराठी',      'font' => 'Noto Sans Devanagari'],
        'bn' => ['native' => 'বাংলা',      'font' => 'Noto Sans Bengali'],
        'gu' => ['native' => 'ગુજરાતી',    'font' => 'Noto Sans Gujarati'],
        'or' => ['native' => 'ଓଡ଼ିଆ',      'font' => 'Noto Sans Oriya'],
        'pa' => ['native' => 'ਪੰਜਾਬੀ',     'font' => 'Noto Sans Gurmukhi'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Organisation copy — single source of truth
    |--------------------------------------------------------------------------
    | Used by the home page, the About page and the mobile API. The Flutter app keeps its own copy
    | of these sentences; tests/Feature/OrganizationCopyTest.php fails if the two ever drift apart.
    | `:guru` is replaced with the (bold) name of the Rajaguru.
    */
    'copy' => [
        'guru' => 'Sri Sri Sri Subrahmanneswara Swamy Garu',

        'origin_1' => 'The Akhanda Bharata Viswa Hindu Parirakshana Samithi (ABVHPS) was founded in 2023 and is registered under Registration No. 20/2023. Guided by Rajaguru :guru, it works to preserve and revive Sanatana Dharma.',
        'origin_2' => 'This charitable trust is dedicated to uplifting people mentally, morally and physically. It strives to beautify chosen villages and to nurture spiritual awareness, the wellbeing of our temples, and a deep love for the nation.',
        'blessing' => "Our main objective is to protect Hindu Sanathana Dharma, construct new temples, expand Goushalas, distribute daily meals under Annapurna, and support children's literacy across every Grama Panchayat.",

        'vision'  => 'To see Sanatana Dharma flourish in every village — with temples restored and newly built as living centres of prayer, learning and seva, and with every family, whatever their means, treated with dignity, equality and love.',
        'mission' => 'To gather willing hearts as members and volunteers and turn devotion into service — offering Annapurna meals to the hungry, education to children, relief to the poor and medical aid to the sick, with humility and without expectation.',
        'goal'    => 'To protect our sacred traditions, rituals and festivals and hand them down, unbroken, to the next generation — building a united family of devotees, strong in brotherhood and working together, from every village to every corner of the world.',

        'footer_about' => 'Dedicated to preserving and promoting Hindu culture and values worldwide under the guidance of Rajaguru Sri Sri Sri Subrahmanneswara Swamy Garu.',
    ],
];
