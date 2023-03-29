<?php
use GraphQL\Type\Definition\CustomScalarType;

$queries = [
    'hello' => function ($root, $args, $context) {
        return 'hello my name is Emil';
    }
];

$mutations = [
    'send' => function ($root, $args, $context) {
        dbg('+++ resolver', $root, $args, $context);
        if (!$root['is_authorized']) {
            throw new \GraphQL\Error\UserError('Authorization failed');
        }
        $api = $root['app']->make(api\email::class);
        dbg('+++ api', $api);
        return $api->_send_gql($args['template'], $args['recipient'], $args['data']);
    }
];

return [
    'EmilData' => new CustomScalarType([
        'name' => 'EmilData',
        'serialize' => function ($val) {
            return $val;
        },
    ]),
    //  'Room'     => $roomType,
    'Query' => $queries,
    'Mutation' => $mutations,
];
