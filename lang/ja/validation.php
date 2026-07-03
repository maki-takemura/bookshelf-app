<?php

return [
    'required' => ':attributeは必須項目です。',
    'string' => ':attributeには、文字列を指定してください。',
    'email' => ':attributeは、有効なメールアドレス形式で指定してください。',
    'max' => [
        'string' => ':attributeの文字数は、:max文字以下である必要があります。',
    ],
    'min' => [
        'string' => ':attributeの文字数は、:min文字以上である必要があります。',
    ],
    'unique' => '指定の:attributeは既に使用されています。',
    'confirmed' => ':attributeと:attribute確認が一致しません。',

    'attributes' => [
        'name' => '名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => 'パスワード確認',
    ],
];
