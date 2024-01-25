<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Pole :attribute musí být přijato.',
    'accepted_if' => ':attribute musí být přijato pokud :other je :value.',
    'active_url' => 'Pole :attribute musí být platná adresa URL.',
    'after' => 'Pole :attribute musí být datum po :date.',
    'after_or_equal' => 'Pole :attribute musí být datum po nebo rovno :date.',
    'alpha' => 'Pole :attribute musí obsahovat pouze písmena.',
    'alpha_dash' => 'Pole :attribute musí obsahovat pouze písmena, číslice, pomlčky a podtržítka.',
    'alpha_num' => 'Pole :attribute musí obsahovat pouze písmena a číslice.',
    'array' => 'Pole :attribute musí být pole.',
    'ascii' => 'Pole :attribute musí obsahovat pouze jednobytové alfanumerické znaky a symboly.',
    'before' => 'Pole :attribute musí být datum před :date.',
    'before_or_equal' => 'Pole :attribute musí být datum před nebo rovno :date.',
    'between' => [
        'array' => 'Pole :attribute musí mít mezi :min a :max položkami.',
        'file' => ':attribute musí být v rozmezí :min až :max kilobajtů.',
        'numeric' => 'Pole :attribute musí být mezi :min a :max.',
        'string' => 'Pole :attribute musí být v rozmezí :min a :max znaků.',
    ],
    'boolean' => ':attribute musí být pravda nebo nepravda.',
    'can' => ':attribute obsahuje neautorizovanou hodnotu.',
    'confirmed' => 'Potvrzení :attribute se neshoduje.',
    'current_password' => 'Nesprávné heslo.',
    'date' => 'Pole :attribute musí být platné datum.',
    'date_equals' => 'Pole :attribute musí být stejné jako :date.',
    'date_format' => 'Pole :attribute musí odpovídat formátu :format.',
    'decimal' => 'Pole :attribute musí mít :decimal desetinná místa.',
    'declined' => 'Pole :attribute musí být odmítnuto.',
    'declined_if' => ':attribute musí být odmítnut, pokud :other je :value.',
    'different' => ':attribute a :other se musí lišit.',
    'digits' => 'Pole :attribute musí mít :digits číslic.',
    'digits_between' => 'Pole :attribute musí být v rozmezí :min a :max číslic.',
    'dimensions' => 'Pole :attribute má neplatné rozměry obrázku.',
    'distinct' => 'Pole :attribute má duplicitní hodnotu.',
    'doesnt_end_with' => 'Pole :attribute nesmí končit jedním z následujících: hodnot.',
    'doesnt_start_with' => 'Pole :attribute nesmí začínat jedním z následujících: :values.',
    'email' => 'Pole :attribute musí být platná e-mailová adresa.',
    'ends_with' => 'Pole :attribute musí končit jedním z těchto znaků: hodnot.',
    'enum' => 'Vybraný :attribute je neplatný.',
    'exists' => 'Vybraný :attribute je neplatný.',
    'extensions' => ':attribute musí mít jedno z následujících přípon: :values.',
    'file' => 'Pole :attribute musí být soubor.',
    'filled' => 'Pole :attribute musí mít hodnotu.',
    'gt' => [
        'array' => ':attribute musí obsahovat více než :value položek.',
        'file' => ':attribute musí být větší než :value kilobajtů.',
        'numeric' => ':attribute musí být větší než :value.',
        'string' => ':attribute musí být větší než :value znaků.',
    ],
    'gte' => [
        'array' => 'Pole :attribute musí mít :value položky nebo více.',
        'file' => ':attribute musí být větší nebo rovno :value kilobajtů.',
        'numeric' => ':attribute musí být větší nebo rovno :value.',
        'string' => ':attribute musí být větší nebo roven :value znaků.',
    ],
    'hex_color' => 'Pole :attribute musí být platná hexadecimální barva.',
    'image' => 'Pole :attribute musí být obrázek.',
    'in' => 'Vybraný :attribute je neplatný.',
    'in_array' => 'Pole :attribute musí existovat v :other.',
    'integer' => 'Pole :attribute musí být celé číslo.',
    'ip' => 'Pole :attribute musí být platná IP adresa.',
    'ipv4' => 'Pole :attribute musí být platná IPv4 adresa.',
    'ipv6' => 'Pole :attribute musí být platná adresa IPv6.',
    'json' => 'Pole :attribute musí být platný řetězec JSON.',
    'lowercase' => ':attribute musí b?t mal?.',
    'lt' => [
        'array' => ':attribute musí obsahovat méně než :value položek.',
        'file' => ':attribute musí být menší než :value kilobajtů.',
        'numeric' => ':attribute musí být menší než :value.',
        'string' => ':attribute musí být menší než :value znaků.',
    ],
    'lte' => [
        'array' => 'Pole :attribute nesmí obsahovat více než :value položek.',
        'file' => ':attribute musí být menší nebo rovno :value kilobajtů.',
        'numeric' => ':attribute musí být menší nebo rovno :value.',
        'string' => 'Pole :attribute musí být menší nebo rovno :value znaků.',
    ],
    'mac_address' => 'Pole :attribute musí být platná MAC adresa.',
    'max' => [
        'array' => 'Pole :attribute nesmí obsahovat více než :max položek.',
        'file' => ':attribute nesmí být větší než :max kilobajtů.',
        'numeric' => ':attribute nesmí být větší než :max.',
        'string' => 'Pole :attribute nesmí být větší než :max znaků.',
    ],
    'max_digits' => 'Pole :attribute nesmí obsahovat více než :max číslic.',
    'mimes' => 'Pole :attribute musí být soubor typu: :values.',
    'mimetypes' => 'Pole :attribute musí být soubor typu: :values.',
    'min' => [
        'array' => 'Pole :attribute musí obsahovat alespoň :min položek.',
        'file' => 'Pole :attribute musí být alespoň :min kilobajtů.',
        'numeric' => 'Pole :attribute musí být alespoň :min.',
        'string' => 'Pole :attribute musí mít alespoň :min znaků.',
    ],
    'min_digits' => 'Pole :attribute musí mít alespoň :min číslic.',
    'missing' => ':attribute musí být vyplněno.',
    'missing_if' => ':attribute musí být vyplněno, pokud :other je :value.',
    'missing_unless' => ':attribute musí být vyplněno pokud :other je :value.',
    'missing_with' => ':attribute musí být vyplněno, pokud je k dispozici :values.',
    'missing_with_all' => ':attribute musí být vyplněno, pokud je k dispozici :values.',
    'multiple_of' => 'Pole :attribute musí být násobkem :value.',
    'not_in' => 'Vybraný :attribute je neplatný.',
    'not_regex' => 'Formát pole :attribute je neplatný.',
    'numeric' => 'Pole :attribute musí být číslo.',
    'password' => [
        'letters' => 'Pole :attribute musí obsahovat alespoň jedno písmeno.',
        'mixed' => ':attribute musí obsahovat alespoň jedno velké písmeno a jedno malé písmeno.',
        'numbers' => ':attribute musí obsahovat alespoň jedno číslo.',
        'symbols' => ':attribute musí obsahovat alespoň jeden symbol.',
        'uncompromised' => 'Uvedený :attribute se objevil v úniku dat. Zvolte prosím jiný :attribute atribut.',
    ],
    'present' => ':attribute musí být vyplněno.',
    'present_if' => ':attribute musí být vyplněno, pokud :other je :value.',
    'present_unless' => ':attribute musí být vyplněno, pokud :other je :value.',
    'present_with' => ':attribute musí být vyplněno, pokud je k dispozici :values.',
    'present_with_all' => ':attribute musí být vyplněno, pokud je k dispozici :values.',
    'prohibited' => 'Pole :attribute je zakázáno.',
    'prohibited_if' => ':attribute je zakázáno pokud :other je :value.',
    'prohibited_unless' => ':attribute je zakázáno pokud :other není v :values.',
    'prohibits' => 'Pole :attribute zakazuje :other být přítomno.',
    'regex' => 'Formát pole :attribute je neplatný.',
    'required' => 'Pole :attribute je povinné.',
    'required_array_keys' => ':attribute musí obsahovat záznamy pro: :values.',
    'required_if' => ':attribute je vyžadováno pokud :other je :value.',
    'required_if_accepted' => ':attribute je vyžadováno pokud :other je přijato.',
    'required_unless' => ':attribute je vyžadováno pokud :other není v :values.',
    'required_with' => 'Pole :attribute je vyžadováno, pokud je zvoleno :values.',
    'required_with_all' => 'Pole :attribute je vyžadováno, pokud je k dispozici :values.',
    'required_without' => 'Pole :attribute je vyžadováno, pokud :values není k dispozici.',
    'required_without_all' => 'Pole :attribute je vyžadováno, pokud není k dispozici žádná z :values.',
    'same' => ':attribute se musí shodovat s :other.',
    'size' => [
        'array' => 'Pole :attribute musí obsahovat :size položek.',
        'file' => 'Pole :attribute musí mít :size kilobajtů.',
        'numeric' => 'Pole :attribute musí být :size.',
        'string' => 'Pole :attribute musí mít :size znaků.',
    ],
    'starts_with' => 'Pole :attribute musí začínat jedním z následujících znaků: :values.',
    'string' => 'Pole :attribute musí být řetězec.',
    'timezone' => 'Pole :attribute musí být platné časové pásmo.',
    'unique' => ':attribute již byl použit.',
    'uploaded' => ':attribute se nepodařilo nahrát.',
    'uppercase' => ':attribute musí b?t velká písmena.',
    'url' => 'Pole :attribute musí být platná adresa URL.',
    'ulid' => 'Pole :attribute musí být platné ULID.',
    'uuid' => 'Pole :attribute musí být platné UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'vlastní zpráva',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
