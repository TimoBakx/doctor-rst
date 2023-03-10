<?php

declare(strict_types=1);

/*
 * This file is part of DOCtor-RST.
 *
 * (c) Oskar Stark <oskarstark@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rule;

use App\Rule\ReplaceCodeBlockTypes;
use App\Tests\RstSample;
use App\Value\NullViolation;
use App\Value\Violation;
use App\Value\ViolationInterface;

final class ReplaceCodeBlockTypesTest extends \App\Tests\UnitTestCase
{
    /**
     * @test
     *
     * @dataProvider checkProvider
     */
    public function check(ViolationInterface $expected, RstSample $sample): void
    {
        $configuredRules = [];
        foreach (ReplaceCodeBlockTypes::getList() as $search => $message) {
            $configuredRules[] = (new ReplaceCodeBlockTypes())->configure($search, $message);
        }

        $violations = [];
        foreach ($configuredRules as $rule) {
            $violation = $rule->check($sample->lines(), $sample->lineNumber(), 'filename');
            if (!$violation->isNull()) {
                $violations[] = $violation;
            }
        }

        if ($expected->isNull()) {
            static::assertCount(0, $violations);
        } else {
            static::assertCount(1, $violations);
            static::assertEquals($expected, $violations[0]);
        }
    }

    public function checkProvider(): \Generator
    {
        yield 'valid' => [
            NullViolation::create(),
            new RstSample([
                '.. code-block:: twig',
            ]),
        ];

        yield 'invalid jinja' => [
            Violation::from(
                'Please do not use type "jinja" for code-block, use "twig" instead',
                'filename',
                1,
                '.. code-block:: jinja'
            ),
            new RstSample([
                '.. code-block:: jinja',
            ]),
        ];

        yield 'invalid html jinja' => [
            Violation::from(
                'Please do not use type "html+jinja" for code-block, use "html+twig" instead',
                'filename',
                1,
                '.. code-block:: html+jinja'
            ),
            new RstSample([
                '.. code-block:: html+jinja',
            ]),
        ];

        yield 'invalid js' => [
            Violation::from(
                'Please do not use type "js" for code-block, use "javascript" instead',
                'filename',
                1,
                '.. code-block:: js'
            ),
            new RstSample([
                '.. code-block:: js',
            ]),
        ];
    }
}