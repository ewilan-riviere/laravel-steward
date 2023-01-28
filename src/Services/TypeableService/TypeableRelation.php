<?php

namespace Kiwilan\Steward\Services\TypeableService;

use ReflectionMethod;

class TypeableRelation
{
    public function __construct(
        public string $name,
        public bool $isArray = false,
        public ?string $typeNative = null,
        public ?string $type = null,
    ) {
    }

    /**
     * @return array<string,TypeableRelation>
     */
    public static function make(TypeableModel $model): array
    {
        $reflector = $model->class->reflector;
        $relations = [];

        foreach ($reflector->getMethods() as $method) {
            $isRelation = str_contains($method->getReturnType(), 'Illuminate\Database\Eloquent\Relations');

            if (! $isRelation) {
                continue;
            }

            $relation = TypeableRelation::setRelation($method);
            $relations[$relation->name] = $relation;
        }

        return $relations;
    }

    private static function setRelation(ReflectionMethod $method): self
    {
        $relation = new TypeableRelation(
            name: $method->getName(),
            isArray: str_contains($method->getReturnType(), 'Many'),
        );
        $return_line = $method->getEndLine() - 2;

        $lines = file($method->getFileName());
        $return_line_content = $lines[$return_line];

        $regex = '/\w+::class/';

        if (preg_match($regex, $return_line_content, $matches)) {
            $type = $matches[0];
            $type = str_replace('::class', '', $type);
            $relation->typeNative = $type;
        }

        $relation->type = $relation->isArray
            ? "{$relation->typeNative}[]"
            : $relation->typeNative;

        return $relation;
    }
}
