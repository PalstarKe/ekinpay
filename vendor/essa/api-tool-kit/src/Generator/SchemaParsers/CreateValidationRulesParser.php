<?php

namespace Essa\APIToolKit\Generator\SchemaParsers;

use Essa\APIToolKit\Generator\ColumnDefinition;
use Essa\APIToolKit\Generator\Guessers\ValidationRuleGuesserInterface;
use Essa\APIToolKit\Generator\SchemaDefinition;

class CreateValidationRulesParser extends SchemaParser
{
    protected function getParsedSchema(SchemaDefinition $schemaDefinition): string
    {
        return collect($schemaDefinition->getColumns())
            ->map(function (ColumnDefinition $definition): string {
                $guesser = new ValidationRuleGuesserInterface($definition, ['required']);

                return "'{$definition->getName()}' => [{$guesser->guess()}],";
            })
            ->implode(PHP_EOL . "\t\t\t");
    }
}
