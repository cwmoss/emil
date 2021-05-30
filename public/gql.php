<?php

use Siler\GraphQL;
use Siler\Http\Request;
use Siler\Http\Response;

require __DIR__ . '/../vendor/autoload.php';
/*
use GraphQL\Type\Definition\CustomScalarType;

$emailType = new CustomScalarType([
    'name' => 'Email',
    'serialize' => function ($value) {return $value;},
    'parseValue' => function ($value) {return '-email-' . $value;},
    'parseLiteral' => function ($valueNode, array $variables = null) {return $valueNode->value;},
]);
*/
// Enable CORS
Response\cors();

// Respond only for POST requests
if (Request\method_is('post')) {
    // Retrive the Schema
    $schema = include __DIR__ . '/../src/schema.php';

    // Give it to siler
    GraphQL\init($schema);
}
