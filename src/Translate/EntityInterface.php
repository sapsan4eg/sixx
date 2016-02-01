<?php

namespace Sixx\Translate;

interface EntityInterface
{
    /**
     * List of the available languages
     * @return array|bool
     */
    public function listLanguages();

    /**
     * Translate label by locale
     *
     * @param string $label
     * @param string $locale
     * @return string|bool
     */
    public function translate($label, $locale);

    /**
     * Add label to database
     *
     * @param array $label
     * @return bool
     */
    public function setLabel(array $label);
}