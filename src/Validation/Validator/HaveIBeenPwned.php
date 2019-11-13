<?php

declare(strict_types=1);

/*
 * Password Validation Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2019, terminal42 gmbh
 * @author     terminal42 <https://terminal42.ch>
 * @license    MIT
 * @link       http://github.com/terminal42/contao-password-validation
 */

namespace Terminal42\PasswordValidationBundle\Validation\Validator;

use Contao\System;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Terminal42\PasswordValidationBundle\Exception\PasswordValidatorException;
use Terminal42\PasswordValidationBundle\Validation\PasswordValidatorInterface;
use Terminal42\PasswordValidationBundle\Validation\ValidationConfiguration;
use Terminal42\PasswordValidationBundle\Validation\ValidationContext;

final class HaveIBeenPwned implements PasswordValidatorInterface
{
    private $configuration;

    public function __construct(ValidationConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function validate(ValidationContext $context): bool
    {
        if (false === $this->configuration->hasConfiguration($context->getUserEntity())) {
            return true;
        }

        $configuration   = $this->configuration->getConfiguration($context->getUserEntity());
        $minDataBreaches = $configuration['haveibeenpwned'];
        if (!$minDataBreaches) {
            return true;
        }

        $password = $context->getPassword()->getString();
        $hash     = strtoupper(sha1($password));
        $hash05   = substr($hash, 0, 5);
        $client   = new Client();

        try {
            $res = $client->request('GET', 'https://api.pwnedpasswords.com/range/'.$hash05);
        } catch (GuzzleException $e) {
            return true;
        }

        $breaches = array_reduce(
            preg_split("/\r\n|\n|\r/", (string) $res->getBody()),
            static function ($carry, $item) use ($hash05) {
                [$hash35, $quantity] = explode(':', $item);
                $carry[$hash05.$hash35] = $quantity;

                return $carry;
            },
            []
        );

        if (\array_key_exists($hash, $breaches) && ($quantity = $breaches[$hash]) && $quantity > $minDataBreaches) {
            throw new PasswordValidatorException(sprintf($this->translate('haveibeenpwned'), $quantity));
        }

        return true;
    }

    private function translate(string $key)
    {
        System::loadLanguageFile('exception');

        return $GLOBALS['TL_LANG']['XPT']['passwordValidation'][$key];
    }
}
