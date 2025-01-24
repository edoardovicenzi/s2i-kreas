<?php

require_once(__DIR__ . "/../DTO/DTOOrderProduct.php");
require_once(__DIR__ . "/../DTO/DTOProduct.php");

class TotalCO2
{
    private $conn;
    private $table_name = "tblOrderProducts";
    //This view expands all the information of the link table
    private $view_name = "viewOrderProducts";

    public function __construct(IDatabase $db)
    {
        $this->conn = $db->getConnection();
    }


    //Read
    public function getAll()
    {
        $query = "SELECT SUM(saved_co2) as saved_co2 FROM " . $this->view_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
