<?php

declare(strict_types=1);

namespace Laradom\ORM\Util\Inflector;

class EnglishInflector implements InflectorInterface
{
    public function singularize(string $plural): string
    {
        $irregulars = [
            'children' => 'child',
            'people' => 'person',
            'men' => 'man',
            'women' => 'woman',
            'teeth' => 'tooth',
            'feet' => 'foot',
            'geese' => 'goose',
            'mice' => 'mouse',
            'categories' => 'category',
            'queries' => 'query',
            'abilities' => 'ability',
            'agencies' => 'agency',
            'movies' => 'movie',
            'archives' => 'archive',
        ];

        if (isset($irregulars[$plural])) {
            return $irregulars[$plural];
        }

        if (str_ends_with($plural, 'ies')) {
            return substr($plural, 0, -3) . 'y';
        }

        if (str_ends_with($plural, 'ives')) {
            return substr($plural, 0, -4) . 'ife';
        }

        if (str_ends_with($plural, 'ves')) {
            return substr($plural, 0, -3) . 'f';
        }

        if (str_ends_with($plural, 'xes') || str_ends_with($plural, 'ses') || str_ends_with($plural, 'zes') || str_ends_with($plural, 'ches') || str_ends_with($plural, 'shes')) {
            return substr($plural, 0, -2);
        }

        if (str_ends_with($plural, 's') && !str_ends_with($plural, 'ss')) {
            return substr($plural, 0, -1);
        }

        return $plural;
    }

    public function pluralize(string $singular): string
    {
        if (preg_match('/(es|ies|ves|[^s]s)$/i', $singular)) {
            return $singular;
        }

        $irregulars = [
            'child' => 'children',
            'person' => 'people',
            'man' => 'men',
            'woman' => 'women',
            'tooth' => 'teeth',
            'foot' => 'feet',
            'goose' => 'geese',
            'mouse' => 'mice',
            'category' => 'categories',
            'query' => 'queries',
            'ability' => 'abilities',
            'agency' => 'agencies',
            'movie' => 'movies',
            'archive' => 'archives',
        ];

        if (isset($irregulars[$singular])) {
            return $irregulars[$singular];
        }

        if (str_ends_with($singular, 'y') && !preg_match('/[aeiou]y$/i', $singular)) {
            return substr($singular, 0, -1) . 'ies';
        }

        if (str_ends_with($singular, 'ife')) {
            return substr($singular, 0, -3) . 'ives';
        }

        if (str_ends_with($singular, 'f')) {
            return substr($singular, 0, -1) . 'ves';
        }

        if (str_ends_with($singular, 'x') || str_ends_with($singular, 's') || str_ends_with($singular, 'z') || str_ends_with($singular, 'ch') || str_ends_with($singular, 'sh')) {
            return $singular . 'es';
        }

        return $singular . 's';
    }
}
