<?php

namespace ipl\Orm;

class Str
{
    /**
     * @param   string  $subject
     *
     * @return  string
     */
    public static function camel($subject)
    {
        $normalized = str_replace(['-', '_'], ' ', $subject);

        if ($normalized === $subject) {
            return $subject;
        }

        return lcfirst(str_replace(' ', '', ucwords(strtolower($normalized))));
    }
}
