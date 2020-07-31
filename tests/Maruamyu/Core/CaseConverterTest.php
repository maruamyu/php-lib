<?php

namespace Maruamyu\Core;

class CaseConverterTest extends \PHPUnit\Framework\TestCase
{
    public function test_toCamelCase_snake2camel()
    {
        $namco = CaseConverter::toCamelCase('namco');
        $this->assertEquals('namco', $namco);

        $bandaiNamco = CaseConverter::toCamelCase('bandai_namco');
        $this->assertEquals('bandaiNamco', $bandaiNamco);

        $bandaiNamcoEntertainment = CaseConverter::toCamelCase('bandai_namco_entertainment');
        $this->assertEquals('bandaiNamcoEntertainment', $bandaiNamcoEntertainment);
    }

    public function test_toCamelCase_kebab2camel()
    {
        $namco = CaseConverter::toCamelCase('namco');
        $this->assertEquals('namco', $namco);

        $bandaiNamco = CaseConverter::toCamelCase('bandai-namco');
        $this->assertEquals('bandaiNamco', $bandaiNamco);

        $bandaiNamcoEntertainment = CaseConverter::toCamelCase('bandai-namco-entertainment');
        $this->assertEquals('bandaiNamcoEntertainment', $bandaiNamcoEntertainment);
    }

    public function test_toCamelCase_pascal2camel()
    {
        $namco = CaseConverter::toCamelCase('Namco');
        $this->assertEquals('namco', $namco);

        $bandaiNamco = CaseConverter::toCamelCase('BandaiNamco');
        $this->assertEquals('bandaiNamco', $bandaiNamco);

        $bandaiNamcoEntertainment = CaseConverter::toCamelCase('BandaiNamcoEntertainment');
        $this->assertEquals('bandaiNamcoEntertainment', $bandaiNamcoEntertainment);
    }

    public function test_toPascalCase_snake2pascal()
    {
        $Namco = CaseConverter::toPascalCase('namco');
        $this->assertEquals('Namco', $Namco);

        $BandaiNamco = CaseConverter::toPascalCase('bandai_namco');
        $this->assertEquals('BandaiNamco', $BandaiNamco);

        $BandaiNamcoEntertainment = CaseConverter::toPascalCase('bandai_namco_entertainment');
        $this->assertEquals('BandaiNamcoEntertainment', $BandaiNamcoEntertainment);
    }

    public function test_toPascalCase_kebab2pascal()
    {
        $Namco = CaseConverter::toPascalCase('namco');
        $this->assertEquals('Namco', $Namco);

        $BandaiNamco = CaseConverter::toPascalCase('bandai-namco');
        $this->assertEquals('BandaiNamco', $BandaiNamco);

        $BandaiNamcoEntertainment = CaseConverter::toPascalCase('bandai-namco-entertainment');
        $this->assertEquals('BandaiNamcoEntertainment', $BandaiNamcoEntertainment);
    }

    public function test_toPascalCase_camel2pascal()
    {
        $Namco = CaseConverter::toPascalCase('namco');
        $this->assertEquals('Namco', $Namco);

        $BandaiNamco = CaseConverter::toPascalCase('bandaiNamco');
        $this->assertEquals('BandaiNamco', $BandaiNamco);

        $BandaiNamcoEntertainment = CaseConverter::toPascalCase('bandaiNamcoEntertainment');
        $this->assertEquals('BandaiNamcoEntertainment', $BandaiNamcoEntertainment);
    }

    public function test_to_snake_case_pascal2snake()
    {
        $namco = CaseConverter::to_snake_case('Namco');
        $this->assertEquals('namco', $namco);

        $bandai_namco = CaseConverter::to_snake_case('BandaiNamco');
        $this->assertEquals('bandai_namco', $bandai_namco);

        $bandai_namco_entertainment = CaseConverter::to_snake_case('BandaiNamcoEntertainment');
        $this->assertEquals('bandai_namco_entertainment', $bandai_namco_entertainment);
    }

    public function test_to_snake_case_camel2snake()
    {
        $namco = CaseConverter::to_snake_case('namco');
        $this->assertEquals('namco', $namco);

        $bandai_namco = CaseConverter::to_snake_case('bandaiNamco');
        $this->assertEquals('bandai_namco', $bandai_namco);

        $bandai_namco_entertainment = CaseConverter::to_snake_case('bandaiNamcoEntertainment');
        $this->assertEquals('bandai_namco_entertainment', $bandai_namco_entertainment);
    }

    public function test_to_snake_case_kebab2snake()
    {
        $namco = CaseConverter::to_snake_case('namco');
        $this->assertEquals('namco', $namco);

        $bandai_namco = CaseConverter::to_snake_case('bandai-namco');
        $this->assertEquals('bandai_namco', $bandai_namco);

        $bandai_namco_entertainment = CaseConverter::to_snake_case('bandai-namco-entertainment');
        $this->assertEquals('bandai_namco_entertainment', $bandai_namco_entertainment);
    }

    public function test_to_snake_case_pascal2kebab()
    {
        $namco = CaseConverter::toKebabCase('Namco');
        $this->assertEquals('namco', $namco);

        $bandai_namco = CaseConverter::toKebabCase('BandaiNamco');
        $this->assertEquals('bandai-namco', $bandai_namco);

        $bandai_namco_entertainment = CaseConverter::toKebabCase('BandaiNamcoEntertainment');
        $this->assertEquals('bandai-namco-entertainment', $bandai_namco_entertainment);
    }

    public function test_to_snake_case_camel2kebab()
    {
        $namco = CaseConverter::toKebabCase('namco');
        $this->assertEquals('namco', $namco);

        $bandai_namco = CaseConverter::toKebabCase('bandaiNamco');
        $this->assertEquals('bandai-namco', $bandai_namco);

        $bandai_namco_entertainment = CaseConverter::toKebabCase('bandaiNamcoEntertainment');
        $this->assertEquals('bandai-namco-entertainment', $bandai_namco_entertainment);
    }

    public function test_to_snake_case_snake2kebab()
    {
        $namco = CaseConverter::toKebabCase('namco');
        $this->assertEquals('namco', $namco);

        $bandai_namco = CaseConverter::toKebabCase('bandai_namco');
        $this->assertEquals('bandai-namco', $bandai_namco);

        $bandai_namco_entertainment = CaseConverter::toKebabCase('bandai_namco_entertainment');
        $this->assertEquals('bandai-namco-entertainment', $bandai_namco_entertainment);
    }
}
