<?php

/*
 * This file is part of Symfony MailHogger.
 *
 * (c) 2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class EmailListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new CallbackTransformer(
            function (array $list = null) {
                return implode(PHP_EOL, $list ? $list : []);
            },
            function ($list) {
                return array_map('trim', explode(PHP_EOL, $list));
            }
        ));
    }

    public function getParent()
    {
        return TextareaType::class;
    }
}
