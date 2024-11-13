<?php
class Debit {
    public int $customer_id = 0;
    public float $amount = 0.00;
    public string $subject = 'Description of services';

    /**
     *
     * Constructor
     *
     * Assing values to new Debit object instance
     * @customer_id int
     * @amount float
     * @subject string
     * @return none
     */

    public function __construct(int $customer_id, float $amount, string $subject = '')
    {
        $this->customer_id = ($customer_id > 0) ? $customer_id : throw new Exception('Customer id must be greater than zero');
        $this->amount = ($amount > 0) ? $amount : throw new Exception('Amount must be greater than zero');
        $this->subject = $subject;
    }

    /**
     *
     * setSubject
     *
     * Assing value from var str to this->subject
     * @string str
     * @return none
     */

    public function setSubject(string $str): void
    {
        $this->subject = (!empty($str)) ? $str : throw new Exception('Subject must contains valid characters ');
    }
}
?>
