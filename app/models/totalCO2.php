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

        $existingParameters = parseQueryStringParameters();

        If (count($existingParameters) > 0){
        $query = $query . " WHERE ";

        $parametersToSQL = array();
        foreach ($existingParameters as $key => $value){
            switch ($key) {
            case 'from':
                array_push($parametersToSQL, "sold_on > :$key");
                break;
            case 'to':
                array_push($parametersToSQL, "sold_on < :$key");
                break;
            case 'country':
                array_push($parametersToSQL, "destination_country = :$key");
                break;
            case 'pid':
                array_push($parametersToSQL, "product_id = :$key");
                break;

            default:
                //Not recognized = skip to next key
                continue 2;
                break;
            }
        }
        $query = $query . implode(" AND ", $parametersToSQL);
        }

        $stmt = $this->conn->prepare($query);

        foreach ($existingParameters as $key => $value){
            $stmt->bindValue(":$key", "$value");
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
