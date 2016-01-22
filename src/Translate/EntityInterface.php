<?php

namespace Sixx\Translate;

interface EntityInterface
{
    public function listLanguages();

    public function translate($label, $locale);
}