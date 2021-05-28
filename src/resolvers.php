<?php
use GraphQL\Type\Definition\CustomScalarType;

$queries = [
    'hello' => function ($root, $args, $context) {
        dbg('+++ rsolver', $root, $args, $context);
        $name = $args['name'];
        $data = $args['data'];
        return [
            ['type' => 'happy', 'body' => 'hallohallo!', 'data' => $data],
            ['type' => 'good mood', 'body' => 'nice to see you,' . $name, 'data' => $args]
        ];
    }
];

return [
    'Email' => new CustomScalarType([
        'name' => 'Email',
        'serialize' => function ($val) {
            return '--email--' . $val;
        },
    ]),
    //  'Room'     => $roomType,
    'Query' => $queries,
    //  'Mutation' => $mutationType,
];
