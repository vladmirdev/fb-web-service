<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 04.10.17
 * Time: 16:50
 */

return [
    [
        'class' => 'yii\rest\UrlRule',
        'controller' => [

            'v1/user',

            'v1/channel',
            'v1/category',
            'v1/source',
            'v1/flavour',
            'v1/nature',
            'v1/specie',
            'v1/cultivation',
            'v1/english-common',
            'v1/latin-name',

            'v1/sync',

            'v1/element',
            'v1/activity',
            'v1/feedback',
            'v1/note',
            'v1/device',

            'v1/formula',
            'v1/formula-category',
            'v1/formula-herb',
            'v1/formula-preparation',
            'v1/formula-source',
            'v1/formula-note',
            'v1/formula-action',
            'v1/formula-symptom',

            'v1/herb',
            'v1/herb-category',
            'v1/herb-caution',
            'v1/herb-channel',
            'v1/herb-cultivation',
            'v1/herb-englishcommon',
            'v1/herb-flavour',
            'v1/herb-formula',
            'v1/herb-latinname',
            'v1/herb-nature',
            'v1/herb-preparation',
            'v1/herb-source',
            'v1/herb-species',
            'v1/herb-note',
            'v1/herb-alternate',
            'v1/herb-action',
            'v1/herb-symptom',

            'v1/caution',
            'v1/caution-category',

            'v1/symptom',
            'v1/symptom-category',

            'v1/preparation',
            'v1/preparation-category',

            'v1/action',
            'v1/action-category',

            'v1/book',
            'v1/book-chapter',
            'v1/book-page',

            'v1/templates',
            'v1/country',
            'v1/continents',
            'v1/language',
            'v1/role',

            'v1/reference',
            'v1/favorite'

        ],
        'extraPatterns' => [

            'GET {category-formulas}' => 'formulas',
            'GET {category-herbs}' => 'herbs',
            'GET {category-actions}' => 'actions',
            'GET {category-preparation}' => 'preparations',
            'GET {category-symptoms}' => 'symptoms',
            'GET {category-cautions}' => 'cautions',
            'GET {category-search}' => 'search',

            'GET {formula-activities}' => 'activities',

            'GET {formula-categories}' => 'categories',
            'POST {formula-categories}' => 'categories',

            'GET {formula-herbs}' => 'herbs',
            'POST {formula-herbs}' => 'herbs',

            'GET {formula-sources}' => 'sources',
            'GET {formula-preparations}' => 'preparations',
            'GET {formula-notes}' => 'notes',
            'GET {formula-actions}' => 'actions',
            'GET {formula-symptoms}' => 'symptoms',
            'GET {formula-search}' => 'search',
            'GET {formula-searchbydaterange}' => 'searchbydaterange',

            'POST {formula-import}' => 'import',
            'OPTIONS {formula-import}' => 'options',

            'GET {herb-activities}' => 'activities',

            'GET {herb-categories}' => 'categories',
            'POST {herb-categories}' => 'categories',

            'GET {herb-cautions}' => 'cautions',
            'GET {herb-channels}' => 'channels',
            'GET {herb-cultivations}' => 'cultivations',
            'GET {herb-flavours}' => 'flavours',

            'GET {herb-formulas}' => 'formulas',
            'POST {herb-formulas}' => 'formulas',

            'GET {herb-natures}' => 'natures',
            'GET {herb-preparations}' => 'preparations',
            'GET {herb-sources}' => 'sources',
            'GET {herb-species}' => 'species',
            'GET {herb-englishcommons}' => 'englishcommons',
            'GET {herb-latinnames}' => 'latinnames',
            'GET {herb-searchbydaterange}' => 'searchbydaterange',
            'GET {herb-notes}' => 'notes',
            'GET {herb-alternates}' => 'alternates',
            'GET {herb-actions}' => 'actions',
            'GET {herb-symptoms}' => 'symptoms',

            'POST {herb-import}' => 'import',
            'OPTIONS {herb-import}' => 'options',

            'POST {user-login}' => 'login',
            'POST {user-logout}' => 'logout',
            'POST {user-sendresetpasswordemail}' => 'sendresetpasswordemail',
            'POST {user-verifyresetpassword}' => 'verifyresetpassword',
            'POST {user-resetpassword}' => 'resetpassword',
            'POST {user-changepassword}' => 'changepassword',
            'GET {user-sendverifyemail}' => 'sendverifyemail',
            'POST {user-verifyaccount}' => 'verifyaccount',

            'GET {user-login-history}' => 'login-history',
            'GET {user-devices}' => 'devices',
            'GET {user-activity}' => 'activity',
            'GET {user-search}' => 'search',
            'DELETE {user-data}' => 'data',
            'OPTIONS {user-data}' => 'options',

            'POST {sync-pushlastsync}' => 'pushlastsync',
            'POST {sync-pullchanges}' => 'pullchanges',

            'GET {book-search}' => 'search',
            'GET {book-chapters}' => 'chapters',
            'GET {book-chapter}' => 'chapter',
            'GET {book-pages}' => 'pages',
            'GET {book-chapters-search}' => 'search',
            'GET {book-pages-search}' => 'search',

            'POST {feedback-reply}' => 'reply',
            'POST {feedback-forward}' => 'forward',

            'GET {continents}' => 'continents',

            'GET {action-categories}' => 'categories',
            'GET {symptom-categories}' => 'categories',
            'GET {caution-categories}' => 'categories',
            'GET {preparation-categories}' => 'categories',

            'POST {favorite-remove}' => 'remove',

            'POST {action-import}' => 'import',
            'OPTIONS {action-import}' => 'options',

        ],
        'tokens' => [

            '{id}' => '<id:\\d+>',

            '{category-formulas}' => '<id:\\d+>/<category-formulas:formulas>',
            '{category-herbs}' => '<id:\\d+>/<category-herbs:herbs>',
            '{category-actions}' => '<id:\\d+>/<category-actions:actions>',
            '{category-symptoms}' => '<id:\\d+>/<category-symptoms:symptoms>',
            '{category-preparations}' => '<id:\\d+>/<category-preparations:preparations>',
            '{category-cautions}' => '<id:\\d+>/<category-cautions:cautions>',

            '{formula-activities}' => '<id:\\d+>/<formula-activities:activities>',
            '{formula-categories}' => '<id:\\d+>/<formula-categories:categories>',
            '{formula-herbs}' => '<id:\\d+>/<formula-herbs:herbs>',
            '{formula-sources}' => '<id:\\d+>/<formula-sources:sources>',
            '{formula-preparations}' => '<id:\\d+>/<formula-preparations:preparations>',
            '{formula-search}' => '<formula-search:search>',
            '{formula-notes}' => '<id:\\d+>/<formula-notes:notes>',
            '{formula-actions}' => '<id:\\d+>/<formula-actions:actions>',
            '{formula-symptoms}' => '<id:\\d+>/<formula-symptoms:symptoms>',
            '{formula-searchbydaterange}' => '<formula-search:searchbydaterange>',
            '{formula-import}' => '<formula:import>',

            '{action-categories}' => '<id:\\d+>/<action-categories:categories>',
            '{symptom-categories}' => '<id:\\d+>/<symptom-categories:categories>',
            '{caution-categories}' => '<id:\\d+>/<caution-categories:categories>',
            '{preparation-categories}' => '<id:\\d+>/<preparation-categories:categories>',

            '{herb-activities}' => '<id:\\d+>/<herb-activities:activities>',
            '{herb-categories}' => '<id:\\d+>/<herb-categories:categories>',
            '{herb-cautions}' => '<id:\\d+>/<herb-cautions:cautions>',
            '{herb-channels}' => '<id:\\d+>/<herb-channels:channels>',
            '{herb-cultivations}' => '<id:\\d+>/<herb-cultivations:cultivations>',
            '{herb-flavours}' => '<id:\\d+>/<herb-flavours:flavours>',
            '{herb-formulas}' => '<id:\\d+>/<herb-formulas:formulas>',
            '{herb-natures}' => '<id:\\d+>/<herb-natures:natures>',
            '{herb-preparations}' => '<id:\\d+>/<herb-preparations:preparations>',
            '{herb-sources}' => '<id:\\d+>/<herb-sources:sources>',
            '{herb-species}' => '<id:\\d+>/<herb-species:species>',
            '{herb-englishcommons}' => '<id:\\d+>/<herb-englishcommons:englishcommons>',
            '{herb-latinnames}' => '<id:\\d+>/<herb-latinnames:latinnames>',
            '{herb-notes}' => '<id:\\d+>/<herb-notes:notes>',
            '{herb-actions}' => '<id:\\d+>/<herb-actions:actions>',
            '{herb-symptoms}' => '<id:\\d+>/<herb-symptoms:symptoms>',
            '{herb-searchbydaterange}' => '<herb-search:searchbydaterange>',
            '{herb-alternates}' => '<id:\\d+>/<herb-alternates:alternates>',
            '{herb-import}' => '<herb:import>',

            '{user-login}' => '<users:login>',
            '{user-logout}' => '<users:logout>',

            '{user-sendresetpasswordemail}' => '<users:sendresetpasswordemail>',
            '{user-verifyresetpassword}' => '<users:verifyresetpassword>',
            '{user-resetpassword}' => '<users:resetpassword>',
            '{user-changepassword}' => '<users:changepassword>',
            '{user-sendverifyemail}' => '<users:sendverifyemail>',
            '{user-verifyaccount}' => '<users:verifyaccount>',

            '{user-login-history}' => '<id:\\d+>/<users:login-history>',
            '{user-activity}' => '<id:\\d+>/<users:activity>',
            '{user-devices}' => '<id:\\d+>/<users:devices>',
            '{user-data}' => '<id:\\d+>/<users:data>',
            '{user-search}' => '<users:search>',

            '{sync-pushlastsync}' => '<sync:pushlastsync>',
            '{sync-pullchanges}' => '<sync:pullchanges>',

            '{book-search}' => '<book:search>',
            '{book-chapters}' => '<id:\\d+>/<book-chapters:chapters>',
            '{book-chapter}' => '<id:\\d+>/<books:chapter>/<chapter_id:\\d+>',
            '{book-pages}' => '<id:\\d+>/<book-pages:pages>',
            '{book-chapters-search}' => '<book-chapters:search>',
            '{book-pages-search}' => '<book-pages:search>',

            '{feedback-reply}' => '<feedback:reply>',
            '{feedback-forward}' => '<feedback:forward>',

            '{continents}' => '<countries:continents>',

            '{favorite-remove}' => '<favorite:remove>',
        ]
    ]
];