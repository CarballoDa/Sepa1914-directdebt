<?php
class Customer {
    public int $id = 0;
    public string $full_name = '';
    public string $identification_number = '';
    public string $address = '';
    public string $zip_code = '';
    public string $city = '';
    public string $province = '';

    public object $bank = null;
    public object $debit = null;

    /**
     *
     * Constructor
     *
     * Assing values to new Customer object instance
     * @id int
     * @full_name string
     * @identification_number string
     * @address string
     * @zip_code string
     * @city string
     * @province string
     * @return none
     */

    public function __construct(int $id, string $full_name, string $identification_number, string $address, string $zip_code, string $city, string $province)
    {
        $this->customer_id = ($id > 0) ? $id : throw new Exception('Customer id must be greater than zero');
        $this->full_name = (!empty($full_name)) ? $full_name : throw new Exception('Full Name is required');
        $this->identification_number = (!empty($identification_number)) ? $identification_number : throw new Exception('Full Name is required');
        $this->address = (!empty($address)) ? $address : throw new Exception('Full Name is required');
        $this->zip_code = (!empty($zip_code)) ? $zip_code : throw new Exception('Full Name is required');
        $this->city = (!empty($city)) ? $city : throw new Exception('Full Name is required');
        $this->province = (!empty($province)) ? $province : throw new Exception('Full Name is required');
    }

    /**
    *
    * setBank
    *
    * Assing value from var bank to $this->bank
    * @bank object
    * @return none
    */

    public function setBank(object $bank): void
    {
        $this->bank = ($bank !== null) ? $bank : throw new Exception('Bank must be a valid object ');
    }

    /**
     *
     * setDebit
     *
     * Assing value from var bank to $this->bank
     * @debit object
     * @return none
     */

    public function setDebit(object $debit): void
    {
        $this->debit = ($debit !== null) ? $debit : throw new Exception('Debit must be a valid object ');
    }

    /**
     *
     * getBank
     *
     * Return value from var $this->bank
     * @return object
     */

    public function getBank(): object
    {
        return $this->bank;
    }

    /**
     *
     * getDebit
     *
     * Return value from var $this->debit
     * @return object
     */

    public function getDebit(): object
    {
        return $this->debit;
    }

}
?>
