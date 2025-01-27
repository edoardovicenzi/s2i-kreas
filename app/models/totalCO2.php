<?php

require_once(__DIR__ . "/" . "../DTO/DTOOrderProduct.php");
require_once(__DIR__ . "/" . "../DTO/DTOProduct.php");
require_once(__DIR__ . "/" . "../utility/CustomExceptions.php");

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
    public function getTotal()
    {
        $query = "SELECT SUM(saved_co2) as saved_co2 FROM " . $this->view_name;

        $existingParameters = parseQueryStringParameters();
        $parametersToSQL = array();
        $bindParameter = array();

        if (count($existingParameters) > 0) {

            foreach ($existingParameters as $key => $value) {
                switch ($key) {
                    case 'from':
                        array_push($parametersToSQL, "sold_on > :$key");
                        $bindParameter[$key] = $value;
                        break;
                    case 'to':
                        array_push($parametersToSQL, "sold_on < :$key");
                        $bindParameter[$key] = $value;
                        break;
                    case 'country':
                        array_push($parametersToSQL, "destination_country = :$key");
                        $bindParameter[$key] = $value;
                        break;
                    case 'pid':
                        array_push($parametersToSQL, "product_id = :$key");
                        $bindParameter[$key] = $value;
                        break;

                    default:
                        //Not recognized = skip to next key
                        continue 2;
                        break;
                }
            }
        }

        if (count($bindParameter) > 0) {
            $query = $query . " WHERE ";
            $query = $query . implode(" AND ", $parametersToSQL);
            $stmt = $this->conn->prepare($query);
            foreach ($bindParameter as $key => $value) {
                $stmt->bindValue(":$key", "$value");
            }
        } else {
            $stmt = $this->conn->prepare($query);
        }
        try {
            $stmt->execute();
        } catch (Exception $e) {
            throw new CustomHttpException("Request malformed please retry. Follow The documentation if needed.", 400);
        }

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
