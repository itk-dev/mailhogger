<?php

/*
 * This file is part of rimi-itk/mailhogger.
 *
 * (c) 2018–2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class EmailListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            static function (array $list = null) {
                return implode(PHP_EOL, $list ?: []);
            },
            static function ($list) {
                return array_map('trim', explode(PHP_EOL, $list));
            }
        ));
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }
}
