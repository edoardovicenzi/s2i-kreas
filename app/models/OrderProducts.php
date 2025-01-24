<?php

require_once(__DIR__ . "/../DTO/DTOOrderProduct.php");
require_once(__DIR__ . "/../DTO/DTOProduct.php");

class OrderProducts
{
    private $conn;
    private $table_name = "tblOrderProducts";
    //This view expands all the information of the link table
    private $view_name = "viewOrderProducts";

    public $order_id;
    public $product_id;
    public $quantity;

    public function __construct(IDatabase $db)
    {
        $this->conn = $db->getConnection();
    }

    //Create
    public function addNew()
    {
        //Sanitize input

        $this->order_id = htmlspecialchars($this->order_id);
        $this->product_id = htmlspecialchars($this->product_id);
        $this->quantity = htmlspecialchars($this->quantity);

        //prepare query
        $query = "INSERT INTO " . $this->table_name . " (order_id, product_id, quantity) VALUES (:order_id, :product_id, :quantity)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam('order_id', $this->order_id);
        $stmt->bindParam('product_id', $this->product_id);
        $stmt->bindParam('quantity', $this->quantity);
        $stmt->execute();

        $id = $this->conn->lastInsertId();
        return $this->getByOrderId($id);
    }

    //Read
    public function getAll()
    {
        $query = "SELECT * FROM " . $this->view_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $results = array();

        if ($items) {
            foreach ($items as $row) {
                array_push($results, new DTOOrderProduct($row));
            }
        }
        return $results;
    }

    public function getByOrderId($id)
    {
        $this->order_id = $id;
        //prepare query
        $query = "SELECT * FROM " . $this->view_name . " WHERE order_id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam('id', $this->order_id);
        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $results = new DTOOrderProduct();

        if ($items) {

            unset($results->product_id);
            unset($results->saved_co2);
            unset($results->quantity);
            unset($results->product_name);

            $results->order_id = $items[0]['order_id'];
            $results->destination_country = $items[0]['destination_country'];
            $results->sold_on = $items[0]['sold_on'];

            foreach ($items as $row) {
                $product = new DTOProduct();
                $product->product_id = $row['product_id'];
                $product->product_name = $row['product_name'];
                $product->quantity = $row['quantity'];
                $product->saved_co2 = $row['saved_co2'];

                $results->addProduct($product);
            }
            return $results;
        }
        return null;
    }

    public function getByOrderIdAndProductId()
    {

        $query = "SELECT * FROM " . $this->view_name . " WHERE order_id = :order_id AND product_id = :product_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam('order_id', $this->order_id);
        $stmt->bindParam('product_id', $this->product_id);
        $stmt->execute();

        $items = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($items) {
            $results = new DTOOrderProduct($items);
            //Prepare the prduct
            $product = new DTOProduct();

            $product->product_id = $results->product_id;
            $product->saved_co2 = $results->saved_co2;
            $product->quantity = $results->quantity;
            $product->product_name = $results->product_name;

            //add the product
            $results->addProduct($product);

            unset($results->product_id);
            unset($results->saved_co2);
            unset($results->quantity);
            unset($results->product_name);

            return $results;
        } else {
            return null;
        }
    }

    public function updateEntry()
    {
        //Sanitize input

        $this->order_id = htmlspecialchars($this->order_id);
        $this->product_id = htmlspecialchars($this->product_id);
        $this->quantity = htmlspecialchars($this->quantity);


        $query = "UPDATE " . $this->table_name . " SET quantity = :quantity WHERE order_id = :order_id AND product_id = :product_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam('order_id', $this->order_id);
        $stmt->bindParam('product_id', $this->product_id);
        $stmt->bindParam('quantity', $this->quantity);
        $stmt->execute();

        return $this->getByOrderId($this->order_id);
    }
    //Delete
    public function deleteById($id)
    {
        //this method is not needed since we an order is removed then it cascades the changes to the tblOrderProducts
        throw new Exception("Not yet implemented!");
    }

    //Utility
    //Formatting the json output
    public static function formatGetAll($queryResults)
    {
        //The result must be formatted since it is more semantically correct to produce an array of objects (orders)
        //each of which holds general information about the order and each product information
        //We need to do this since everything is stored on a single row
        //Group by order id
        $groupedList = array();
        foreach ($queryResults as $row) {
            $groupedList[$row->order_id][] = $row;
        }

        $formattedList = array();

        foreach ($groupedList as $order_id => $products_details) {

            $dtoOrderProduct = new DTOOrderProduct();

            //Get information from the first OrderProduct
            $order_info = $products_details[0];
            $dtoOrderProduct->order_id = $order_info->order_id;
            $dtoOrderProduct->destination_country = $order_info->destination_country;
            $dtoOrderProduct->sold_on = $order_info->sold_on;



            //Add products to the order in formatted list
            foreach ($products_details as $product) {
                //extract Products information from the each item
                $dtoProduct = new DTOProduct();
                $dtoProduct->product_id = $order_info->product_id;
                $dtoProduct->saved_co2 = $order_info->saved_co2;
                $dtoProduct->quantity = $order_info->quantity;
                $dtoProduct->product_name = $order_info->product_name;

                $dtoOrderProduct->addProduct($dtoProduct);
            }

            //Remove unnecessary properties
            unset($dtoOrderProduct->product_id);
            unset($dtoOrderProduct->saved_co2);
            unset($dtoOrderProduct->quantity);
            unset($dtoOrderProduct->product_name);
            //push the order information to the formatted list
            array_push($formattedList, $dtoOrderProduct);
        }

        return $formattedList;
    }
    public function getLastInsertId()
    {
        return $this->conn->lastInsertId();
    }
}
