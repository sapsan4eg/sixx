<?php

namespace Sixx\Translate;

class Translator
{
    /**
     * @var EntityInterface
     */
    protected $entity;
    protected $languages = [];
    protected $defaultLanguage = 'en';
    protected $currentLanguage = 'en';
    protected $dictionary = [];

    /**
     * Translator constructor.
     * @param EntityInterface $entity
     */
    public function __construct(EntityInterface $entity)
    {
        $this->entity = $entity;
        $this->setLanguages();
    }

    /**
     * Sets available languages
     */
    protected function setLanguages()
    {
        foreach ($this->entity->listLanguages() as $language) {
            if (is_string($language) && false === array_search($language, $this->languages)) {
                $this->languages[] = $language;
            }
        }
    }

    /**
     * Names of supported languages
     * @return array
     */
    public function listLanguages()
    {
        return $this->languages;
    }

    /**
     * Translate label
     *
     * @access public
     * @param string
     * @param string
     * @return string
     */
    public function get($key, $language = null)
    {
        if (empty($language)) {
            $language = $this->currentLanguage;
        } elseif (false === array_search($language, $this->listLanguages())) {
            $language = $this->currentLanguage;
        }

        if (empty($this->dictionary[$language][$key])) {
            $this->dictionary[$language][$key] = $this->translate($key, $language);
            if ($key === $this->dictionary[$language][$key]) {
                if ($language !== $this->currentLanguage) {
                    $this->dictionary[$language][$key] = $this->translate($key, $this->currentLanguage);
                }
                if ($key === $this->dictionary[$language][$key] && $language !== $this->defaultLanguage) {
                    $this->dictionary[$language][$key] = $this->translate($key, $this->defaultLanguage);
                }
            }
        }

        return $this->dictionary[$language][$key];
    }

    /**
     * Translate label from db
     *
     * @access protected
     * @param string
     * @param string
     * @return string
     */
    protected function translate($key, $locale)
    {
        $label = $this->entity->translate($key, $locale);

        if (!empty($label)) {
            return (string)$label;
        }

        return $key;
    }

    /**
     * Set current language used in system
     * @param $language
     * @return bool
     */
    public function setLanguage($language)
    {
        if (false !== array_search($language, $this->listLanguages())) {
            $this->currentLanguage = $language;
            return true;
        }

        return false;
    }

    /**
     * Current language used in system
     * @return string
     */
    public function getLanguage()
    {
        return $this->currentLanguage;
    }
}
