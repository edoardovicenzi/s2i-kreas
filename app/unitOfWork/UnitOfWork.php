<?php
include_once("config.php");
require_once(__DIR__ . "/../UnitOfWork/IUnitOfWork.php");
//Import repositories (called models)
require_once(__DIR__ . "/../models/Product.php");
require_once(__DIR__ . "/../models/Order.php");
require_once(__DIR__ . "/../models/OrderProducts.php");
require_once(__DIR__ . "/../models/totalCO2.php");

//Import database
require_once(__DIR__ . "/../database/IDatabase.php");
require_once(__DIR__ . "/../database/MySQLDatabase.php");

class UnitOfWork implements IUnitOfWork
{
    private static $instance;
    private static $db;
    public static $order;
    public static $orderProducts;
    public static $product;
    public static $totalCO2;

    private final function __construct()
    {
        self::$db = new MySQLDatabase();
        self::$order = new Order(self::$db);
        self::$product = new Product(self::$db);
        self::$orderProducts = new OrderProducts(self::$db);
        self::$totalCO2 = new TotalCO2(self::$db);
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new UnitOfWork();
        }
        return self::$instance;
    }
}
