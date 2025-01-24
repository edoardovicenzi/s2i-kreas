<?php

include_once("config.php");
require_once(__DIR__ . "/../database/IDatabase.php");

class Product
{
    private $conn;
    private $table_name = "tblProduct";

    public $id;
    public $name;
    public $saved_co2;

    public function __construct(IDatabase $db)
    {
        $this->conn = $db->getConnection();
    }

    //Create
    public function addNew()
    {
        //Sanitize input

        $this->name = htmlspecialchars($this->name);
        $this->saved_co2 = htmlspecialchars($this->saved_co2);

        //prepare query
        $query = "INSERT INTO " . $this->table_name . " (name, saved_co2) VALUES (:name, :saved_co2)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam('name', $this->name);
        $stmt->bindParam('saved_co2', $this->saved_co2);
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
        $this->id = $id;
        //prepare query
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam('id', $this->id);
        $stmt->execute();

        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($item) {
            return new DTOProduct($item);
        } else {
            return null;
        }
    }

    //Update

    public function updateEntry()
    {
        //Sanitize input

        $this->id = htmlspecialchars($this->id);
        $this->name = htmlspecialchars($this->name);
        $this->saved_co2 = htmlspecialchars($this->saved_co2);


        $query = "UPDATE " . $this->table_name . " SET name = :name, saved_co2 = :saved_co2 WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam('id', $this->id);
        $stmt->bindParam('name', $this->name);
        $stmt->bindParam('saved_co2', $this->saved_co2);
        $stmt->execute();

        return $this->getById($this->id);
    }
    //Delete
    public function deleteById($id)
    {
        $item = $this->getById($id);
        if ($item == false) {
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
