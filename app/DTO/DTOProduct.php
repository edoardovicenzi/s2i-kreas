<?php
class DTOProduct
{
    public $product_id;
    public $product_name;
    public $quantity;
    public $saved_co2;

    public function __construct($PDOProduct = null)
    {
        if (!$PDOProduct == null) {
            $this->product_id = $PDOProduct['id'];
            $this->product_name = $PDOProduct['name'];
            $this->saved_co2 = $PDOProduct['saved_co2'];
            if (isset($PDOProduct['quantity'])) {
                $this->quantity = $PDOProduct['quantity'];
            } else {
                $this->quantity = null;
            }
        }
    }
}
