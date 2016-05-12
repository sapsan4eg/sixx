<?php

namespace Sixx\Protection;

/**
 * Sixx\Protection\Protector
 *
 * @package    Sixx
 * @subpackage Protection
 * @category   Library
 * @author     Yuri Nasyrov <sapsan4eg@ya.ru>
 * @copyright  Copyright (c) 2014 - 2016, Yuri Nasyrov.
 * @license	   http://six-x.org/guide/license.html
 * @link       http://six-x.org
 * @since      Version 1.0.0.0
 */
Class Protector
{
    /**
     * Character set
     *
     * @var	string
     */
    public $charset = 'UTF-8';

    /**
     * List of never allowed strings
     *
     * @var	array
     */
    protected $neverAllowedStr =	[
        'document.cookie'	=> '[removed]',
        'document.write'	=> '[removed]',
        '.parentNode'		=> '[removed]',
        '.innerHTML'		=> '[removed]',
        '-moz-binding'		=> '[removed]',
        '<!--'				=> '&lt;!--',
        '-->'				=> '--&gt;',
        '<![CDATA['			=> '&lt;![CDATA[',
        '<comment>'			=> '&lt;comment&gt;'
    ];

    /**
     * List of never allowed regex replacements
     *
     * @var	array
     */
    protected $neverAllowedRegex = [
        'javascript\s*:',
        '(document|(document\.)?window)\.(location|on\w*)',
        'expression\s*(\(|&\#40;)', // CSS and IE
        'vbscript\s*:', // IE, surprise!
        'wscript\s*:', // IE
        'jscript\s*:', // IE
        'vbs\s*:', // IE
        'Redirect\s+30\d',
        "([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?"
    ];

    /**
     * XSS Clean
     *
     * Sanitizes data so that Cross Site Scripting Hacks can be
     * prevented.  This method does a fair amount of work but
     * it is extremely thorough, designed to prevent even the
     * most obscure XSS attempts.  Nothing is ever 100% foolproof,
     * of course, but I haven't been able to get anything passed
     * the filter.
     *
     * Note: Should only be used to deal with data upon submission.
     *	 It's not something that should be used for general
     *	 runtime processing.
     *
     * @link	http://channel.bitflux.ch/wiki/XSS_Prevention
     * 		Based in part on some code and ideas from Bitflux.
     *
     * @link	http://ha.ckers.org/xss.html
     * 		To help develop this script I used this great list of
     *		vulnerabilities along with a few other hacks I've
     *		harvested from examining vulnerabilities in other programs.
     *
     * @param	string|string[]	$str		Input data
     * @param 	bool		$is_image	Whether the input is an image
     * @return	string
     */
    public function xssClean($str, $is_image = false)
    {
        // Is the string an array?
        if (is_array($str)) {
            foreach ($str as $key => $value) {
                $str[$key] = $this->xssClean($value);
            }

            return $str;
        }

        $str = $this->removeInvisibleCharacters($str);

        do {
            $str = rawurldecode($str);
        } while (preg_match('/%[0-9a-f]{2,}/i', $str));

        $str = preg_replace_callback("/[^a-z0-9>]+[a-z0-9]+=([\'\"]).*?\\1/si", '$this->convertAttribute', $str);
        $str = preg_replace_callback('/<\w+.*/si', '$this->decodeEntity', $str);

        $str = $this->removeInvisibleCharacters($str);
        $str = str_replace("\t", ' ', $str);

        $converted_string = $str;
        $str = $this->doNeverAllowed($str);

        if ($is_image === true) {
            $str = preg_replace('/<\?(php)/i', '&lt;?\\1', $str);
        } else {
            $str = str_replace(['<?', '?'.'>'], ['&lt;?', '?&gt;'], $str);
        }

        $words = [
            'javascript', 'expression', 'vbscript', 'jscript', 'wscript',
            'vbs', 'script', 'base64', 'applet', 'alert', 'document',
            'write', 'cookie', 'window', 'confirm', 'prompt'
        ];

        foreach ($words as $word) {
            $word = implode('\s*', str_split($word)).'\s*';
            $str = preg_replace_callback('#('.substr($word, 0, -3).')(\W)#is', '$this->compactExplodedWords', $str);
        }

        do {
            $original = $str;

            if (preg_match('/<a/i', $str)) {
                $str = preg_replace_callback('#<a[^a-z0-9>]+([^>]*?)(?:>|$)#si', '$this->jsLinkRemoval', $str);
            }

            if (preg_match('/<img/i', $str)) {
                $str = preg_replace_callback('#<img[^a-z0-9]+([^>]*?)(?:\s?/?>|$)#si', '$this->jsImgRemoval', $str);
            }

            if (preg_match('/script|xss/i', $str)) {
                $str = preg_replace('#</*(?:script|xss).*?>#si', '[removed]', $str);
            }
        } while ($original !== $str);

        unset($original);

        $str = $this->removeEvilAttributes($str, $is_image);
        $naughty = 'alert|prompt|confirm|applet|audio|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|button|select|isindex|layer|link|meta|keygen|object|plaintext|style|script|textarea|title|math|video|svg|xml|xss';
        $str = preg_replace_callback('#<(/*\s*)('.$naughty.')([^><]*)([><]*)#is', '$this->sanitizeNaughtyHtml', $str);
        $str = preg_replace('#(alert|prompt|confirm|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si',
            '\\1\\2&#40;\\3&#41;',
            $str);

        $str = $this->doNeverAllowed($str);

        if ($is_image === true) {
            return ($str === $converted_string);
        }

        return $str;
    }

    /**
     * Remove Invisible Characters
     *
     * This prevents sandwiching null characters
     * between ascii characters, like Java\0script.
     *
     * @param    string
     * @param    bool
     * @return    string
     */
    public function removeInvisibleCharacters($str, $url_encoded = TRUE)
    {
        $non_displayables = [];

        if ($url_encoded) {
            $non_displayables[] = '/%0[0-8bcef]/';    // url encoded 00-08, 11, 12, 14, 15
            $non_displayables[] = '/%1[0-9a-f]/';    // url encoded 16-31
        }

        $non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';    // 00-08, 11, 12, 14-31, 127

        do {
            $str = preg_replace($non_displayables, '', $str, -1, $count);
        } while ($count);

        return $str;
    }

    /**
     * Attribute Conversion
     *
     * @used-by	CI_Security::xss_clean()
     * @param	array	$match
     * @return	string
     */
    protected  function convertAttribute($match)
    {
        return str_replace(array('>', '<', '\\'), array('&gt;', '&lt;', '\\\\'), $match[0]);
    }

    /**
     * HTML Entity Decode Callback
     *
     * @param	array	$match
     * @return	string
     */
    protected function decodeEntity($match)
    {
        return $this->entityDecode($match[0], $this->charset);
    }

    /**
     * HTML Entities Decode
     *
     * A replacement for html_entity_decode()
     *
     * The reason we are not using html_entity_decode() by itself is because
     * while it is not technically correct to leave out the semicolon
     * at the end of an entity most browsers will still interpret the entity
     * correctly. html_entity_decode() does not convert entities without
     * semicolons, so we are left with our own little solution here. Bummer.
     *
     * @link	http://php.net/html-entity-decode
     *
     * @param	string	$str		Input
     * @param	string	$charset	Character set
     * @return	string
     */
    public function entityDecode($str, $charset = null)
    {
        if (strpos($str, '&') === false) {
            return $str;
        }

        $entities = null;

        isset($charset) || $charset = $this->charset;
        $flag = version_compare(phpversion(), '5.4.0', '=')
            ? ENT_COMPAT | ENT_HTML5
            : ENT_COMPAT;

        do {
            $str_compare = $str;

            if (preg_match_all('/&[a-z]{2,}(?![a-z;])/i', $str, $matches)) {
                if (! isset($entities)) {
                    $entities = array_map(
                        'strtolower',
                        version_compare(phpversion(), '5.4.0', '=')
                            ? get_html_translation_table(HTML_ENTITIES, $flag, $charset)
                            : get_html_translation_table(HTML_ENTITIES, $flag)
                    );

                    if ($flag === ENT_COMPAT) {
                        $entities[':'] = '&colon;';
                        $entities['('] = '&lpar;';
                        $entities[')'] = '&rpar;';
                        $entities["\n"] = '&newline;';
                        $entities["\t"] = '&tab;';
                    }
                }

                $replace = [];
                $matches = array_unique(array_map('strtolower', $matches[0]));
                foreach ($matches as &$match) {
                    if (($char = array_search($match.';', $entities, true)) !== false) {
                        $replace[$match] = $char;
                    }
                }

                $str = str_ireplace(array_keys($replace), array_values($replace), $str);
            }

            $str = html_entity_decode(
                preg_replace('/(&#(?:x0*[0-9a-f]{2,5}(?![0-9a-f;])|(?:0*\d{2,4}(?![0-9;]))))/iS', '$1;', $str),
                $flag,
                $charset
            );
        } while ($str_compare !== $str);

        return $str;
    }

    /**
     * Do Never Allowed
     *
     * @used-by	CI_Security::xss_clean()
     * @param 	string
     * @return 	string
     */
    protected function doNeverAllowed($str)
    {
        $str = str_replace(array_keys($this->neverAllowedStr), $this->neverAllowedStr, $str);

        foreach ($this->neverAllowedRegex as $regex) {
            $str = preg_replace('#'.$regex.'#is', '[removed]', $str);
        }

        return $str;
    }

    /**
     * Compact Exploded Words
     *
     * Callback method for xss_clean() to remove whitespace from
     * things like 'j a v a s c r i p t'.
     *
     * @used-by	CI_Security::xss_clean()
     * @param	array	$matches
     * @return	string
     */
    protected function compactExplodedWords($matches)
    {
        return preg_replace('/\s+/s', '', $matches[1]).$matches[2];
    }

    /**
     * JS Link Removal
     *
     * Callback method for xss_clean() to sanitize links.
     *
     * This limits the PCRE backtracks, making it more performance friendly
     * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
     * PHP 5.2+ on link-heavy strings.
     *
     * @param	array	$match
     * @return	string
     */
    protected function jsLinkRemoval($match)
    {
        return str_replace($match[1],
            preg_replace('#href=.*?(?:(?:alert|prompt|confirm)(?:\(|&\#40;)|javascript:|livescript:|mocha:|charset=|window\.|document\.|\.cookie|<script|<xss|data\s*:)#si',
                '',
                $this->filterAttributes(str_replace(array('<', '>'), '', $match[1]))
            ),
            $match[0]);
    }

    /**
     * JS Image Removal
     *
     * Callback method for xss_clean() to sanitize image tags.
     *
     * This limits the PCRE backtracks, making it more performance friendly
     * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
     * PHP 5.2+ on image tag heavy strings.
     *
     * @param	array	$match
     * @return	string
     */
    protected function jsImgRemoval($match)
    {
        return str_replace($match[1],
            preg_replace('#src=.*?(?:(?:alert|prompt|confirm)(?:\(|&\#40;)|javascript:|livescript:|mocha:|charset=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si',
                '',
                $this->filterAttributes(str_replace(array('<', '>'), '', $match[1]))
            ),
            $match[0]);
    }

    /**
     * Filter Attributes
     *
     * Filters tag attributes for consistency and safety.
     *
     * @param	string	$str
     * @return	string
     */
    protected function filterAttributes($str)
    {
        $out = '';
        if (preg_match_all('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $str, $matches)) {
            foreach ($matches[0] as $match) {
                $out .= preg_replace('#/\*.*?\*/#s', '', $match);
            }
        }

        return $out;
    }

    /**
     * Remove Evil HTML Attributes (like event handlers and style)
     *
     * It removes the evil attribute and either:
     *
     *  - Everything up until a space. For example, everything between the pipes:
     *
     *	<code>
     *		<a |style=document.write('hello');alert('world');| class=link>
     *	</code>
     *
     *  - Everything inside the quotes. For example, everything between the pipes:
     *
     *	<code>
     *		<a |style="document.write('hello'); alert('world');"| class="link">
     *	</code>
     *
     * @param	string	$str		The string to check
     * @param	bool	$is_image	Whether the input is an image
     * @return	string	The string with the evil attributes removed
     */
    protected function removeEvilAttributes($str, $is_image)
    {
        $evil_attributes = ['on\w*', 'style', 'xmlns', 'formaction', 'form', 'xlink:href', 'FSCommand', 'seekSegmentTime'];

        if ($is_image === true) {
            unset($evil_attributes[array_search('xmlns', $evil_attributes)]);
        }

        do {
            $count = $temp_count = 0;

            $str = preg_replace('/(<[^>]+)(?<!\w)('.implode('|', $evil_attributes).')\s*=\s*(\042|\047)([^\\2]*?)(\\2)/is', '$1[removed]', $str, -1, $temp_count);
            $count += $temp_count;

            $str = preg_replace('/(<[^>]+)(?<!\w)('.implode('|', $evil_attributes).')\s*=\s*([^\s>]*)/is', '$1[removed]', $str, -1, $temp_count);
            $count += $temp_count;
        } while ($count);

        return $str;
    }

    /**
     * Sanitize Naughty HTML
     *
     * Callback method for xss_clean() to remove naughty HTML elements.
     *
     * @used-by	CI_Security::xss_clean()
     * @param	array	$matches
     * @return	string
     */
    protected function sanitizeNaughtyHtml($matches)
    {
        return '&lt;'.$matches[1].$matches[2].$matches[3]
        .str_replace(array('>', '<'), array('&gt;', '&lt;'), $matches[4]);
    }

    /**
     * Hiding all simbols to ?
     *
     *
     * @param	string	$string
     * @return	string
     */
    public function escape($string)
    {
        return str_repeat("?", strlen($string));
    }

    /**
     * Clening all injections
     *
     * @param	string	$string
     * @return	string
     */
    public function injectionClear($string)
    {
        $search = strtoupper($string);
        $array = ['SET','INSERT','UPDATE','DELETE','REPLACE','CREATE','DROP','TRUNCATE','LOAD','COPY','ALTER','RENAME','GRANT','REVOKE','LOCK','UNLOCK','REINDEX', '"', ';', '\''];
        foreach ($array as $value) {
            if (stripos($search , $value) !== false) {
                $temp = substr($string, 0, stripos($search , $value));
                $string = $temp . "[CORRUPTED]" . substr($string, stripos($search , $value) + strlen($value));
            }
        }
        return $string;
    }

    /**
     * Cleaning upper path in filename
     *
     * @param	string	$string
     * @return	string
     */
    public function fileUp($string)
    {
        return str_replace('../', '[BADPATH]', $string);
    }

    /**
     * @param string $text
     * @return string
     */
    public function clean($text = '')
    {
        return trim($this->xssClean($this->stripHtml($text)));
    }

    /**
     * @param string $string
     * @return string
     */
    public function stripHtml($string)
    {
        $search = ['@<script[^>]*?>.*?</script>@si',  // Strip out javascript
            '@<[\/\!]*?[^<>]*?>@si',                  // Strip out HTML tags
            '@<style[^>]*?>.*?</style>@siU',          // Strip style tags properly
            '@<![\s\S]*?--[ \t\n\r]*>@'               // Strip multi-line comments including CDATA
        ];

        $string = preg_replace($search, ' ', $string);
        $string = str_replace([PHP_EOL, '	', '\r\n', '\n', '\r', '\v', '\t', '\e', '\f', '&nbsp;', '&copy;', '&mdash;', '0x0A', '0x0D', '0x09', '0x0B', ' 0x1B ', '&#160;', '\'', '\\' ], ' ', $string);

        while (true) {
            if (strpos($string, '  ') === false) {
                break;
            }

            $string = str_replace('  ', ' ', $string);
        }

        return $string;
    }
}
