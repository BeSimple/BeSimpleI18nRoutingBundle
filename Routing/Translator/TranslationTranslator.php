<?php

namespace BeSimple\I18nRoutingBundle\Routing\Translator;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Using a TranslatorInterface for translating route parameters.
 *
 * Selects the domain as concatenation of "route"_"attribute".
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class TranslationTranslator implements AttributeTranslatorInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function reverseTranslate($route, $locale, $attribute, $originalValue)
    {
        $domain = $route . "_" . $attribute;
        return $this->translator->trans($originalValue, array(), $domain, $locale);
    }

    public function translate($route, $locale, $attribute, $localizedValue)
    {
        $domain = $route . "_" . $attribute;
        return $this->translator->trans($localizedValue, array(), $domain, $locale);
    }
}
