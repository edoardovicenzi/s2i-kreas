<?php

include_once("config.php");
require_once(__DIR__ . "/" . "../database/IDatabase.php");
require_once(__DIR__ . "/" . "../utility/CustomExceptions.php");
require_once(__DIR__ . "/" . "../DTO/DTOOrder.php");

class Order
{
    private $conn;
    private $table_name = "tblOrder";

    public $id;
    public $destination_country;
    public $sold_on; //Defaults to current timestamp

    public function __construct(IDatabase $db)
    {
        $this->conn = $db->getConnection();
    }

    //Create
    public function addNew()
    {
        //Sanitize input

        $this->destination_country = htmlspecialchars($this->destination_country);

        //prepare query
        $query = "INSERT INTO " . $this->table_name . " (destination_country) VALUES (:destination_country)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam('destination_country', $this->destination_country);
        $stmt->execute();
        $id = $this->conn->lastInsertId();

        return $this->getById($id);
    }

    //Read
    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $results = array();

        if ($items) {
            foreach ($items as $row) {
                array_push($results, new DTOProduct($row));
            }
        }
        return $results;
    }

    public function getById($id)
    {
        //prepare query
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam('id', $id);
        $stmt->execute();

        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($item) {
            $result = new DTOOrder($item);
            return $result;
        } else {
            return null;
        }
    }

    //Update

    public function updateEntry()
    {
        //Sanitize input

        $this->id = htmlspecialchars($this->id);
        $this->destination_country = htmlspecialchars($this->destination_country);
        $this->sold_on = htmlspecialchars($this->sold_on);

        $query = "UPDATE " . $this->table_name . " SET destination_country = :destination_country, sold_on = :sold_on WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam('id', $this->id);
        $stmt->bindParam('destination_country', $this->destination_country);
        $stmt->bindParam('sold_on', $this->sold_on);
        $stmt->execute();

        return $this->getById($this->id);
    }
    //Delete
    public function deleteById($id)
    {
        $item = $this->getById($id);
        if ($item == null) {
            throw new CustomHttpException("Error Processing Request: Item with id " . $id . " not found.", 404);
        } else {
            //prepare query
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam('id', $id);
            $stmt->execute();

            return $item;
        }
    }
}
