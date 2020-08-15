<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Header;

use PhpSpec\ObjectBehavior;

class AcceptSpec extends ObjectBehavior
{
	protected const TCHAR = <<<'TCHAR'
!#$%&'*+-.^_`|~0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ
TCHAR;
	protected const SP = " \t";
	protected const QDTEXT = " \t\x21\x23\x24\x25\x26\x27\x28\x29\x2A\x2B\x2C\x2D\x2E\x2F\x30\x31\x32\x33\x34\x35\x36"
	."\x37\x38\x39\x3A\x3B\x3C\x3D\x3E\x3F\x40\x41\x42\x43\x44\x45\x46\x47\x48\x49\x4A\x4B\x4C\x4D\x4E\x4F\x50\x51"
	."\x52\x53\x54\x55\x56\x57\x58\x59\x5A\x5B\x5D\x5E\x5F\x60\x61\x62\x63\x64\x65\x66\x67\x68\x69\x6A\x6B\x6C\x6D"
	."\x6E\x6F\x70\x71\x72\x73\x74\x75\x76\x77\x78\x79\x7A\x7B\x7C\x7D\x7E\x80\x81\x82\x83\x84\x85\x86\x87\x88\x89"
	."\x8A\x8B\x8C\x8D\x8E\x8F\x90\x91\x92\x93\x94\x95\x96\x97\x98\x99\x9A\x9B\x9C\x9D\x9E\x9F\xA0\xA1\xA2\xA3\xA4"
	."\xA5\xA6\xA7\xA8\xA9\xAA\xAB\xAC\xAD\xAE\xAF\xB0\xB1\xB2\xB3\xB4\xB5\xB6\xB7\xB8\xB9\xBA\xBB\xBC\xBD\xBE\xBF"
	."\xC0\xC1\xC2\xC3\xC4\xC5\xC6\xC7\xC8\xC9\xCA\xCB\xCC\xCD\xCE\xCF\xD0\xD1\xD2\xD3\xD4\xD5\xD6\xD7\xD8\xD9\xDA"
	."\xDB\xDC\xDD\xDE\xDF\xE0\xE1\xE2\xE3\xE4\xE5\xE6\xE7\xE8\xE9\xEA\xEB\xEC\xED\xEE\xEF\xF0\xF1\xF2\xF3\xF4\xF5"
	."\xF6\xF7\xF8\xF9\xFA\xFB\xFC\xFD\xFE\xFF"
	;

	public function it_throws_on_empty_string()
	{
		$exception = new \InvalidArgumentException('Invalid media range: empty type');
		$this->beConstructedWith('');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_if_string_starts_with_non_tchar_symbol()
	{
		$exception = new \InvalidArgumentException(\sprintf('Position 0: unexpected symbol code %s', \ord(' ')));;
		$this->beConstructedWith(' ');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_on_non_tchar_symbol_in_type()
	{
		$exception = new \InvalidArgumentException(\sprintf('Position 1: unexpected symbol code %s', \ord(' ')));;
		$this->beConstructedWith('a ');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_if_string_contains_only_type()
	{
		$exception = new \InvalidArgumentException('Invalid media range: no subtype');
		$this->beConstructedWith(self::TCHAR);
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_on_empty_subtype()
	{
		$exception = new \InvalidArgumentException('Invalid media range: empty subtype');
		$this->beConstructedWith('a/');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_if_subtype_starts_with_non_tchar_symbol()
	{
		$exception = new \InvalidArgumentException(\sprintf('Position 2: unexpected symbol code %s', \ord(' ')));;
		$this->beConstructedWith('a/ ');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_if_subtype_contains_non_tchar_symbol()
	{
		$exception = new \InvalidArgumentException(\sprintf('Position 3: unexpected symbol code %s', \ord('@')));;
		$this->beConstructedWith('a/b@');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_parses_media_range_without_parameters()
	{
		$this->beConstructedWith(self::TCHAR . '/' . self::TCHAR);
		$this->getMediaRanges()->shouldBe([[self::TCHAR, self::TCHAR, []]]);
	}

	public function it_throws_if_string_ends_with_whitespace_after_subtype()
	{
		$exception = new \InvalidArgumentException('Invalid header: ended with whitespace after subtype');
		$this->beConstructedWith('a/b ');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_if_string_ends_with_non_delimiter_after_subtype_and_whitespace()
	{
		$exception = new \InvalidArgumentException(\sprintf('Position 4: unexpected symbol code %s', \ord('@')));;
		$this->beConstructedWith('a/b @');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_if_string_ends_with_semicolon()
	{
		$exception = new \InvalidArgumentException('Invalid media range: no parameter');
		$this->beConstructedWith('a/b;');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_if_string_ends_with_comma()
	{
		$exception = new \InvalidArgumentException('Invalid header: no media range');
		$this->beConstructedWith('a/b,');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_if_string_ends_with_whitespace_after_semicolon()
	{
		$exception = new \InvalidArgumentException('Invalid media range: no parameter');
		$this->beConstructedWith('a/b ; ');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_if_string_ends_with_whitespace_after_comma()
	{
		$exception = new \InvalidArgumentException('Invalid header: no media range');
		$this->beConstructedWith('a/b , ');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_if_parameter_name_starts_with_non_tchar_symbol()
	{
		$exception = new \InvalidArgumentException(\sprintf('Position 6: unexpected symbol code %s', \ord('@')));;
		$this->beConstructedWith('a/b ; @');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_if_parameter_name_contains_non_tchar_symbol()
	{
		$exception = new \InvalidArgumentException(\sprintf('Position 7: unexpected symbol code %s', \ord('@')));;
		$this->beConstructedWith('a/b ; c@');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_if_string_ends_with_parameter_name()
	{
		$exception = new \InvalidArgumentException('Invalid media range parameter: no value');
		$this->beConstructedWith('a/b ; c');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_on_empty_unquoted_parameter_value()
	{
		$exception = new \InvalidArgumentException('Invalid media range parameter: empty unquoted value');
		$this->beConstructedWith('a/b; c=');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_if_unquoted_parameter_value_starts_with_non_tchar_symbol()
	{
		$exception = new \InvalidArgumentException(\sprintf('Position 7: unexpected symbol code %s', \ord('@')));;
		$this->beConstructedWith('a/b; c=@');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_if_unquoted_parameter_value_contains_non_tchar_symbol()
	{
		$exception = new \InvalidArgumentException(\sprintf('Position 8: unexpected symbol code %s', \ord('@')));;
		$this->beConstructedWith('a/b; c=d@');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_parses_media_range_with_unquoted_parameter()
	{
		$this->beConstructedWith(self::TCHAR . '/' . self::TCHAR . self::SP . ';' . self::SP . self::TCHAR . '=' . self::TCHAR);
		$this->getMediaRanges()->shouldBe([[self::TCHAR, self::TCHAR, [[self::TCHAR, self::TCHAR]]]]);
	}

	public function it_throws_if_string_ends_with_whitespace_after_unquoted_parameter()
	{
		$exception = new \InvalidArgumentException('Invalid header: ended with whitespace after parameter value');
		$this->beConstructedWith('a/b; c=d ');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_if_string_ends_with_non_delimiter_after_unquoted_parameter_and_whitespace()
	{
		$exception = new \InvalidArgumentException(\sprintf('Position 9: unexpected symbol code %s', \ord('@')));;
		$this->beConstructedWith('a/b; c=d @');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_on_no_closing_quote_for_empty_parameter_value()
	{
		$exception = new \InvalidArgumentException('Invalid media range parameter: no closing quote for value');
		$this->beConstructedWith('a/b; c="');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_parses_media_range_with_empty_quoted_parameter()
	{
		$this->beConstructedWith(self::TCHAR . '/' . self::TCHAR . self::SP . ';' . self::SP . self::TCHAR . '=""');
		$this->getMediaRanges()->shouldBe([[self::TCHAR, self::TCHAR, [[self::TCHAR, '']]]]);
	}

	public function it_throws_on_no_closing_quote_for_non_empty_parameter_value()
	{
		$exception = new \InvalidArgumentException('Invalid media range parameter: no closing quote for value');
		$this->beConstructedWith('a/b; c="d');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_on_non_qdtext_symbol_in_quoted_parameter_value()
	{
		$exception = new \InvalidArgumentException(\sprintf('Position 8: unexpected symbol code %s', \ord("\n")));;
		$this->beConstructedWith("a/b; c=\"\n\"");
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_on_excessively_escaped_symbol_in_quoted_parameter_value()
	{
		$exception = new \InvalidArgumentException(\sprintf('Position 9: unexpected symbol code %s', \ord('d')));;
		$this->beConstructedWith('a/b; c="\\d"');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_parses_media_range_with_quoted_parameter()
	{
		$header = self::TCHAR . '/' . self::TCHAR . self::SP . ';' . self::SP . self::TCHAR . '="'. self::QDTEXT .'\\\\\\""';
		$this->beConstructedWith($header);
		$this->getMediaRanges()->shouldBe([[self::TCHAR, self::TCHAR, [[self::TCHAR, self::QDTEXT . '\\"']]]]);
	}

	public function it_throws_if_string_ends_with_escape_symbol_in_quoted_parameter()
	{
		$exception = new \InvalidArgumentException('Invalid media range parameter quoted value: no symbol to escape');
		$this->beConstructedWith('a/b; c="\\');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_if_string_ends_with_whitespace_after_quoted_parameter()
	{
		$exception = new \InvalidArgumentException('Invalid header: ended with whitespace after parameter value');
		$this->beConstructedWith('a/b; c="d" ');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_on_neither_delimiter_not_whitespace_after_quoted_parameter()
	{
		$exception = new \InvalidArgumentException(\sprintf('Position 10: unexpected symbol code %s', \ord('@')));;
		$this->beConstructedWith('a/b; c="d"@');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_if_string_ends_with_non_delimiter_after_quoted_parameter_and_whitespace()
	{
		$exception = new \InvalidArgumentException(\sprintf('Position 11: unexpected symbol code %s', \ord('@')));;
		$this->beConstructedWith('a/b; c="d" @');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_parses_media_range_with_several_parameters()
	{
		$this->beConstructedWith('a/b;c=d;e="f";g=h');
		$this->getMediaRanges()->shouldBe([['a', 'b', [['c', 'd'], ['e', 'f'], ['g', 'h']]]]);
	}

	public function it_parses_media_range_with_several_parameters_that_have_same_name()
	{
		$this->beConstructedWith('a/b  ;  c="d"  ;  c=e  ;  c="f"');
		$this->getMediaRanges()->shouldBe([['a', 'b', [['c', 'd'], ['c', 'e'], ['c', 'f']]]]);
	}

	public function it_throws_if_type_starts_with_non_tchar_symbol()
	{
		$exception = new \InvalidArgumentException(\sprintf('Position 6: unexpected symbol code %s', \ord('@')));;
		$this->beConstructedWith('a/b , @');
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_parses_several_media_ranges_without_parameters()
	{
		$this->beConstructedWith('a/b,c/d , e/f  ,  g/h');
		$this->getMediaRanges()->shouldBe([['a', 'b', []], ['c', 'd', []], ['e', 'f', []], ['g', 'h', []]]);
	}

	public function it_parses_several_media_ranges_with_parameters()
	{
		$this->beConstructedWith('a/b;c=d,e/f;g="h", i/j;k=l , m/n;o="p" , q/r;s=t  ,  u/v;w="x"');
		$this->getMediaRanges()->shouldBe([
			['a', 'b', [['c', 'd']]],
			['e', 'f', [['g', 'h']]],
			['i', 'j', [['k', 'l']]],
			['m', 'n', [['o', 'p']]],
			['q', 'r', [['s', 't']]],
			['u', 'v', [['w', 'x']]],
		]);
	}

	public function it_matches_with_any_type_any_subtype_media_range()
	{
		$this->beConstructedWith('*/*; test=123');
		$this->match('abc/def')->shouldBe(true);
		$this->match('abc/def; ghi=jkl')->shouldBe(true);
		$this->match('abc/def ; ghi=jkl')->shouldBe(true);
		$this->match("abc/def\t; ghi=jkl")->shouldBe(true);
		$this->match('abc/uvw')->shouldBe(true);
		$this->match('abc/uvw; ghi=jkl')->shouldBe(true);
		$this->match('abc/uvw ; ghi=jkl')->shouldBe(true);
		$this->match("abc/uvw\t; ghi=jkl")->shouldBe(true);
		$this->match('xyz/uvw')->shouldBe(true);
		$this->match('xyz/uvw; rst=opq')->shouldBe(true);
		$this->match('xyz/uvw ; rst=opq')->shouldBe(true);
		$this->match("xyz/uvw\t; rst=opq")->shouldBe(true);
	}

	public function it_matches_with_fixed_type_any_subtype_media_range()
	{
		$this->beConstructedWith('abc/*; test=123');
		$this->match('abc/def')->shouldBe(true);
		$this->match('abc/def; ghi=jkl')->shouldBe(true);
		$this->match('abc/def ; ghi=jkl')->shouldBe(true);
		$this->match("abc/def\t; ghi=jkl")->shouldBe(true);
		$this->match('abc/uvw')->shouldBe(true);
		$this->match('abc/uvw; ghi=jkl')->shouldBe(true);
		$this->match('abc/uvw ; ghi=jkl')->shouldBe(true);
		$this->match("abc/uvw\t; ghi=jkl")->shouldBe(true);
		$this->match('xyz/uvw')->shouldBe(false);
		$this->match('xyz/uvw; rst=opq')->shouldBe(false);
		$this->match('xyz/uvw ; rst=opq')->shouldBe(false);
		$this->match("xyz/uvw\t; rst=opq")->shouldBe(false);
	}

	public function it_matches_with_fixed_type_fixed_subtype_media_range()
	{
		$this->beConstructedWith('abc/def; test=123');
		$this->match('abc/def')->shouldBe(true);
		$this->match('abc/def; ghi=jkl')->shouldBe(true);
		$this->match('abc/def ; ghi=jkl')->shouldBe(true);
		$this->match("abc/def\t; ghi=jkl")->shouldBe(true);
		$this->match('abc/uvw')->shouldBe(false);
		$this->match('abc/uvw; ghi=jkl')->shouldBe(false);
		$this->match('abc/uvw ; ghi=jkl')->shouldBe(false);
		$this->match("abc/uvw\t; ghi=jkl")->shouldBe(false);
		$this->match('xyz/uvw')->shouldBe(false);
		$this->match('xyz/uvw; rst=opq')->shouldBe(false);
		$this->match('xyz/uvw ; rst=opq')->shouldBe(false);
		$this->match("xyz/uvw\t; rst=opq")->shouldBe(false);
	}

	public function it_matches_with_several_media_ranges()
	{
		$this->beConstructedWith('abc/def; qwer=123, xyz/uvw; asdf=456');
		$this->match('abc/def')->shouldBe(true);
		$this->match('abc/def; ghi=jkl')->shouldBe(true);
		$this->match('abc/def ; ghi=jkl')->shouldBe(true);
		$this->match("abc/def\t; ghi=jkl")->shouldBe(true);
		$this->match('abc/uvw')->shouldBe(false);
		$this->match('abc/uvw; ghi=jkl')->shouldBe(false);
		$this->match('abc/uvw ; ghi=jkl')->shouldBe(false);
		$this->match("abc/uvw\t; ghi=jkl")->shouldBe(false);
		$this->match('xyz/uvw')->shouldBe(true);
		$this->match('xyz/uvw; rst=opq')->shouldBe(true);
		$this->match('xyz/uvw ; rst=opq')->shouldBe(true);
		$this->match("xyz/uvw\t; rst=opq")->shouldBe(true);
	}
}
