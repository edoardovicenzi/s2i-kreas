<?php
class DTOOrderProduct
{
    public $order_id;
    public $destination_country;
    public $sold_on;
    public $products = array();

    //utility properties
    public $product_id;
    public $saved_co2;
    public $quantity;
    public $product_name;

    public function __construct($PDOOrderProduct = null)
    {
        if (!$PDOOrderProduct == null) {
            $this->order_id = $PDOOrderProduct['order_id'];
            $this->sold_on = $PDOOrderProduct['sold_on'];
            $this->destination_country = $PDOOrderProduct['destination_country'];
            $this->product_id = $PDOOrderProduct['product_id'];
            $this->saved_co2 = $PDOOrderProduct['saved_co2'];
            $this->quantity = $PDOOrderProduct['quantity'];
            $this->product_name = $PDOOrderProduct['product_name'];
        }
    }
    public function addProduct(DTOProduct $product)
    {
        array_push($this->products, $product);
    }
}
