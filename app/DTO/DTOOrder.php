<?php
class DTOOrder
{
    public $order_id;
    public $destination_country;
    public $sold_on;

    public function __construct($PDOOrder = null)
    {
        if (!$PDOOrder == null) {
            $this->order_id = $PDOOrder['id'];
            $this->sold_on = $PDOOrder['sold_on'];
            $this->destination_country = $PDOOrder['destination_country'];
        }
    }
}
