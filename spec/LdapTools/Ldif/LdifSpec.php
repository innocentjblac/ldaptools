<?php
/**
 * This file is part of the LdapTools package.
 *
 * (c) Chad Sikorra <Chad.Sikorra@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\LdapTools\Ldif;

use LdapTools\Ldif\Entry\LdifEntryAdd;
use LdapTools\Ldif\Entry\LdifEntryDelete;
use LdapTools\Ldif\Entry\LdifEntryModify;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LdifSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('LdapTools\Ldif\Ldif');
    }

    function it_should_set_the_ldif_version()
    {
        $this->setVersion(1)->shouldReturnAnInstanceOf('LdapTools\Ldif\Ldif');
    }

    function it_should_get_a_ldif_entry_builder()
    {
        $this->entry()->shouldReturnAnInstanceOf('LdapTools\Ldif\LdifEntryBuilder');
    }

    function it_should_add_a_comment()
    {
        $this->addComment('test')->shouldReturnAnInstanceOf('LdapTools\Ldif\Ldif');
        $this->getComments()->shouldHaveCount(1);

        $this->addComment('foo', 'bar');
        $this->getComments()->shouldHaveCount(3);

        $this->getComments()->shouldBeEqualTo(['test', 'foo', 'bar']);
    }

    function it_should_add_an_entry()
    {
        $delete = new LdifEntryDelete('dc=foo,dc=bar');
        $add = new LdifEntryAdd('dc=foo,dc=bar', ['foo' => 'bar']);
        $modify = new LdifEntryModify('dc=foo,dc=bar');

        $this->addEntry($delete)->shouldReturnAnInstanceOf('LdapTools\Ldif\Ldif');
        $this->getEntries()->shouldHaveCount(1);

        $this->addEntry($add, $modify)->getEntries()->shouldHaveCount(3);
        $this->getEntries()->shouldBeEqualTo([$delete, $add, $modify]);
    }

    function it_should_get_the_ldif_string()
    {
        $delete = new LdifEntryDelete('dc=foo,dc=bar');
        $add = new LdifEntryAdd('dc=foo,dc=bar', ['foo' => 'bar']);
        $this->addEntry($delete, $add);
        $this->addComment('foo');

        $ldif =
              "# foo\r\n"
            . "version: 1\r\n"
            . "\r\n"
            . "dn: dc=foo,dc=bar\r\n"
            . "changetype: delete\r\n"
            . "\r\n"
            . "dn: dc=foo,dc=bar\r\n"
            . "changetype: add\r\n"
            . "foo: bar\r\n";

        $this->toString()->shouldBeEqualTo($ldif);
    }

    function it_should_get_the_operations_for_the_ldif_entries()
    {
        $delete = new LdifEntryDelete('dc=foo,dc=bar');
        $add = new LdifEntryAdd('dc=foo,dc=bar', ['foo' => 'bar']);
        $this->addEntry($delete, $add);

        $this->toOperations()->shouldBeLike([$delete->toOperation(), $add->toOperation()]);
    }
}