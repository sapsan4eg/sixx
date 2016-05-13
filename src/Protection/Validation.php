<?php

namespace Sixx\Protection;

/**
 * Sixx\Protection\Validation
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
class Validation
{
    protected $currentError = '';

    /**
     * Verify the applicable data rules
     *
     * This function does all the work.
     *
     * @access public
     * @param string $data
     * @param string $rules
     * @return bool
     */
    public function valid($data, $rules = '')
    {
        $this->currentError = '';

        if ((!is_string($data) && !is_numeric($data)) || !is_string($rules) || strlen($rules) == 0 || strpos($rules,'isset') !== false) {
            $this->currentError = 'validation_no_data';
            return false;
        }

        if (strlen($data) == 0 && (strpos($data, 'required') !== false)) {
            $this->currentError = 'validation_required';
            return false;
        }

        $rules = explode('|', $rules);

        foreach ($rules as $rule) {

            $rule = trim($rule);
            $param = false;

            if (preg_match("/(.*?)\[(.*)\]/", $rule, $match)) {
                $rule	= $match[1];
                $param	= $match[2];
            }

            if (!method_exists($this, $rule)) {
                if (function_exists($rule)) {
                    $result = $rule($data);

                    if (!is_bool($result)) {
                        $data = $result;
                        continue;
                    }

                    if ($result === false) {
                        $this->currentError = 'validation_native' . ' ' . $rule;
                        return false;
                    }
                } else {
                    trigger_error('Error: could not find the function - ' . $rule . '() assigned in rules list!');
                }

                continue;
            }

            $result = $this->$rule($data, $param);

            if (!is_bool($result)) {
                $data = $result;
                continue;
            }

            if ($result === false) {
                $this->currentError = str_replace('{param}', $param, 'validation_' . $rule);
                return false;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function getCurrentError()
    {
        return $this->currentError;
    }

    /**
     * Required
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function required($str)
    {
        if (!is_array($str)) {
            return (trim($str) == '') ? false : true;
        } else {
            return (!empty($str));
        }
    }

    /**
     * Performs a Regular Expression match test.
     *
     * @access	public
     * @param	string
     * @param	regex
     * @return	bool
     */
    public function regexMatch($str, $regex)
    {
        if (!preg_match($regex, $str)) {
            return false;
        }

        return true;
    }

    /**
     * Match one field to another
     *
     * @access	public
     * @param	string
     * @param	string
     * @return	bool
     */
    public function matches($str, $data)
    {
        return ('' . $str !== $data) ? false : true;
    }

    /**
     * Minimum Length
     *
     * @access	public
     * @param	string
     * @param	value
     * @return	bool
     */
    public function minLength($str, $val)
    {
        if (preg_match("/[^0-9]/", $val)) {
            return false;
        }

        if (function_exists('mb_strlen')) {
            return (mb_strlen($str) < $val) ? false : true;
        }

        return (strlen($str) < $val) ? false : true;
    }

    /**
     * Max Length
     *
     * @access	public
     * @param	string
     * @param	value
     * @return	bool
     */
    public function maxLength($str, $val)
    {
        if (preg_match("/[^0-9]/", $val)) {
            return false;
        }

        if (function_exists('mb_strlen')) {
            return (mb_strlen($str) > $val) ? false : true;
        }

        return (strlen($str) > $val) ? false : true;
    }

    /**
     * Exact Length
     *
     * @access	public
     * @param	string
     * @param	value
     * @return	bool
     */
    public function exactLength($str, $val)
    {
        if (preg_match("/[^0-9]/", $val)) {
            return false;
        }

        if (function_exists('mb_strlen')) {
            return (mb_strlen($str) != $val) ? false : true;
        }

        return (strlen($str) != $val) ? false : true;
    }

    /**
     * Valid Email
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function validEmail($str)
    {
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? false : true;
    }

    /**
     * Valid Emails
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function validEmails($str)
    {
        if (strpos($str, ',') === false) {
            return $this->validEmail(trim($str));
        }

        foreach (explode(',', $str) as $email) {
            if (trim($email) != '' && $this->validEmail(trim($email)) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Alpha
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function alpha($str)
    {
        return (!preg_match("/^([a-z])+$/i", $str)) ? false : true;
    }

    /**
     * Alpha
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function alphaSpace($str)
    {
        return  strlen($str) > 0 ? (( !preg_match("/^([a-z ])+$/i", $str)) ? false : true) : true;
    }

    /**
     * Alpha-numeric
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function alphaNumeric($str)
    {
        return (!preg_match("/^([a-z0-9])+$/i", $str)) ? false : true;
    }

    /**
     * Alpha-numeric with underscores and dashes
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function alphaDash($str)
    {
        return (!preg_match("/^([-a-z0-9_-])+$/i", $str)) ? false : true;
    }

    /**
     * Numeric
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function numeric($str)
    {
        return (bool)preg_match( '/^[\-+]?[0-9]*\.?[0-9]+$/', $str);

    }

    /**
     * Is Numeric
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function isNumeric($str)
    {
        return (!is_numeric($str)) ? false : true;
    }

    /**
     * Integer
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function integer($str)
    {
        return (bool)preg_match('/^[\-+]?[0-9]+$/', $str);
    }

    /**
     * Decimal number
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function decimal($str)
    {
        return (bool)preg_match('/^[\-+]?[0-9]+\.[0-9]+$/', $str);
    }

    /**
     * Greather than
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function greaterThan($str, $min)
    {
        if (!is_numeric($str)) {
            return false;
        }
        return $str > $min;
    }

    /**
     * Less than
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function lessThan($str, $max)
    {
        if (!is_numeric($str)) {
            return false;
        }
        return $str < $max;
    }

    /**
     * Is a Natural number  (0,1,2,3, etc.)
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function isNatural($str)
    {
        return (bool)preg_match( '/^[0-9]+$/', $str);
    }

    /**
     * Is a Natural number, but not a zero  (1,2,3, etc.)
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function isNaturalNoZero($str)
    {
        if (!preg_match( '/^[0-9]+$/', $str)) {
            return false;
        }

        if ($str == 0) {
            return false;
        }

        return true;
    }

    /**
     * Checking dates in format MM / YY
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function monthYear($str)
    {
        $str = str_replace(' ', '', $str);
        $date = explode('/', $str);

        if (count($date) == 2 && $this->isNatural($date[0]) && $this->isNatural($date[1])) {
            if (strlen($date[1]) == 2) {
                $current_cent = date("Y");
                $current_cent = substr($current_cent, 0, 2);
                $date[1] = $current_cent . $date[1];
            }

            return checkdate($date[0], 1, $date[1]);
        }

        return false;
    }

    /**
     * Checking dates in format MM / YY greater than now
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function datesGreaterNow($str)
    {
        if ($this->monthYear($str)) {
            $str = str_replace(' ', '', $str);
            $date = explode('/', $str);
            $year = date("Y");
            $month = date("m");

            if (strlen($date[1]) == 2) {
                $current_cent = substr($year, 0, 2);
                $date[1] = $current_cent . $date[1];
            }

            if (($year == $date[1] && $month <= $date[0]) OR $year < $date[1]) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checking card number
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function cardNumber($str)
    {
        $str = $this->clearSpace($str);

        if ($this->isNaturalNoZero($str)) {
            $cards = $this->cards();

            foreach ($cards As $card) {
                if ($this->regexMatch($str, $card['mask'])) {
                    if ((is_array($card['length']) ? in_array(strlen($str), $card['length']) : $card['length'] == strlen($str))
                        && ($card['luhn'] === false || $this->luhnTest($str))) {
                        return true;
                    }
                }
            }

            return false;
        }

        return false;
    }

    /**
     * list of cards
     *
     * @access	protected
     * @return	array
     */
    protected function cards()
    {
        $cards = array();

        $cards[] = array('name' => 'visaelectron', 'mask' => '/^4(026|17500|405|508|844|91[37])/', 'length' => 16, 'cvc_length' => 3, 'luhn' => TRUE);
        $cards[] = array('name' => 'maestro', 'mask' => '/^(5(018|0[23]|[68])|6(39|7))/', 'length' => array(12, 13, 14, 15, 16, 17, 18, 19), 'cvc_length' => 3, 'luhn' => TRUE);
        $cards[] = array('name' => 'forbrugsforeningen', 'mask' => '/^600/', 'length' => 16, 'cvc_length' => 3, 'luhn' => TRUE);
        $cards[] = array('name' => 'dankort', 'mask' => '/^5019/', 'length' => 16, 'cvc_length' => 3, 'luhn' => TRUE);
        $cards[] = array('name' => 'visa', 'mask' => '/^4/', 'length' => array(13, 16), 'cvc_length' => 3, 'luhn' => TRUE);
        $cards[] = array('name' => 'mastercard', 'mask' => '/^5[0-5]/', 'length' => 16, 'cvc_length' => 3, 'luhn' => TRUE);
        $cards[] = array('name' => 'amex', 'mask' => '/^3[47]/', 'length' => 15, 'cvc_length' =>  array(3, 4), 'luhn' => TRUE);
        $cards[] = array('name' => 'dinersclub', 'mask' => '/^3[0689]/', 'length' => 14, 'cvc_length' => 3, 'luhn' => TRUE);
        $cards[] = array('name' => 'discover', 'mask' => '/^6([045]|22)/', 'length' => 16, 'cvc_length' => 3, 'luhn' => TRUE);
        $cards[] = array('name' => 'unionpay', 'mask' => '/^(62|88)/', 'length' => array(16, 17, 18, 19), 'cvc_length' => 3, 'luhn' => FALSE);
        $cards[] = array('name' => 'jcb', 'mask' => '/^35/', 'length' => 16, 'cvc_length' => 3, 'luhn' => TRUE);

        return $cards;
    }

    /**
     * Check luhn
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function luhnTest($str)
    {
        $len = strlen($str);
        $sum = 0;
        $ord = true;

        for ($i = $len - 1; $i >= 0; $i--) {
            $digit = (int)$len[$i];

            if (($ord = !$ord)) {
                $digit *= 2;
            }

            if ($digit > 9) {
                $digit -= 9;
            }

            $sum += $digit;
        }

        return $sum % 10 === 0;
    }

    /**
     * Check is null
     *
     * @access	public
     * @param	string
     * @return	bool
     */
    public function notnull($str)
    {
        return is_null($str) ? false : true;
    }

    /**
     * Remove all spaces in string
     *
     * @access	public
     * @param	string
     * @return	string
     */
    public function clearSpace($str)
    {
        return str_replace(' ', '', $str);
    }
}
