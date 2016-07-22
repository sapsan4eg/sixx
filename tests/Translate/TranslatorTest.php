<?php

class TranslatorTest extends PHPUnit_Framework_TestCase
{
    public function listLanguagesEntity()
    {
        return [
            [
                ['ru', 'en', 'fr'],
                ['ru', 'en', 'fr']
            ],
            [
                ['ru', 22, 'fr'],
                ['ru', 'fr']
            ],
            [
                ['ru', 'ru', 'fr', 'ru'],
                ['ru', 'fr']
            ],
        ];
    }

    public function getEntity()
    {
        return [
            [
                ['ru', 'en', 'fr'],
                [['some', 'en', 'current translate value']],
                'some',
                null,
                'current translate value',
            ],
            [
                ['ru', 'en', 'fr'],
                [['some', 'en', 'current translate value'], ['some', 'ru', 'something new']],
                'some',
                'ru',
                'something new',
            ],
            [
                ['ru', 'en', 'fr'],
                [['some', 'en', 'current translate value'],['some', 'fr', 'something new']],
                'some',
                'ru',
                'current translate value',
            ],
        ];
    }

    /**
     * @dataProvider listLanguagesEntity
     * @covers \Sixx\Translate\Translator::listLanguages()
     */
    public function testListLanguages($list, $expected)
    {
        $entity = $this->getMockBuilder('\Sixx\Translate\EntityInterface')->getMock();
        $entity->method('listLanguages')->willReturn($list);

        $translator = new \Sixx\Translate\Translator($entity);
        $this->assertEquals($expected, $translator->listLanguages());
    }

    /**
     * @dataProvider getEntity
     * @covers \Sixx\Translate\Translator::get()
     */
    public function testGet($list, $map, $label, $language, $expected)
    {
        $entity = $this->getMockBuilder('\Sixx\Translate\EntityInterface')->getMock();
        $entity->method('listLanguages')->willReturn($list);
        $entity->method('translate')->will($this->returnValueMap($map));

        $translator = new \Sixx\Translate\Translator($entity);
        $this->assertEquals($expected, $translator->get($label, $language));
    }

    /**
     * @covers \Sixx\Translate\Translator::getLanguage()
     * @covers \Sixx\Translate\Translator::setLanguage()
     */
    public function testCurrnetLanguage()
    {
        $entity = $this->getMockBuilder('\Sixx\Translate\EntityInterface')->getMock();
        $entity->method('listLanguages')->willReturn(['ru', 'en', 'fr']);
        $translator = new \Sixx\Translate\Translator($entity);
        $this->assertEquals('en', $translator->getLanguage());
        $translator->setLanguage('fr');
        $this->assertEquals('fr', $translator->getLanguage());
        $translator->setLanguage('ja');
        $this->assertEquals('fr', $translator->getLanguage());
    }
}
