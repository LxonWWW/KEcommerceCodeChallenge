<?php

// Check if we run from the CLI or otherwise we inform the user about it and quit out of the program
if(php_sapi_name() !== "cli") {
    echo "You need to run this program via the CLI!";
    exit;
}

// Requires
require_once(pathinfo(__FILE__)['dirname'] ."/dependencies/CliComponent.php");
require_once(pathinfo(__FILE__)['dirname'] ."/dependencies/DatabaseComponent.php");
require_once(pathinfo(__FILE__)['dirname'] ."/dependencies/XmlComponent.php");

try {
    $CliComponent = new CliComponent();
    $CliComponent->println("CLI test passed!", $CliComponent->getColor("green"));

    // Initiate database connection
    $databaseComponent = new DatabaseComponent();
    $databaseConnectionResult = $databaseComponent->initConnection();
    $database = null;

    // Check if the database connection was successful
    if($databaseConnectionResult === true) {
        $database = $databaseComponent->getDatabase();
    }else{
        $CliComponent->println("Connection to database failed: ".$databaseConnectionResult, $CliComponent->getColor("red"), "error");
    }

    // Checking if the file is specified by the user at execution
    $getfilePathResult = $CliComponent->getFilePath($argc, $argv);
    if(!is_file($getfilePathResult)) {
        $CliComponent->println($getfilePathResult, $CliComponent->getColor("red"), "error");
    }

    $CliComponent->println("Found file: ".$getfilePathResult."!", $CliComponent->getColor("green"));

    // Check if the file content is xml and valid, then parse it
    $XmlComponent = New XmlComponent();
    $XmlParsedResult = $XmlComponent->parseXML($getfilePathResult);
    if(!is_array($XmlParsedResult)) {
        $CliComponent->println($XmlParsedResult, $CliComponent->getColor("red"), "error");
    }

    $CliComponent->println("File is valid XML.", $CliComponent->getColor("green"));

    // Check if the table already exists, if not create it
    $databaseTablesQuery = $database->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name='catalog';");
    $databaseTablesQuery->execute();

    if(count($databaseTablesQuery->fetchAll()) === 0) {
        $query = $database->exec("
        CREATE TABLE 'catalog'
        
        (
            entity_id INTEGER, 
            CategoryName TEXT, 
            sku TEXT, 
            name TEXT, 
            description TEXT, 
            shortdesc TEXT, 
            price TEXT, 
            link TEXT, 
            image TEXT, 
            Brand TEXT, 
            Rating INTEGER, 
            CaffeineType TEXT, 
            Count INTEGER, 
            Flavored BOOLEAN, 
            Seasonal BOOLEAN, 
            Instock BOOLEAN, 
            Facebook BOOLEAN, 
            IsKCup BOOLEAN
        );
        ");
        $CliComponent->println("Tables doesn't exists; created.", $CliComponent->getColor("yellow"));
    }else{
        $CliComponent->println("Table exists, continuing with inserting data.", $CliComponent->getColor("green"));
    }

    $CliComponent->println("Inserting data, please wait... (This may take a moment)", $CliComponent->getColor("green"));

    // Going through every item from our XML input file
    foreach($XmlParsedResult['item'] as $item_index => $item) {

        // Because of our conversion method empty values are equal to an empty array(), so we replace these values with "" values for better handling
        foreach($item as $attribute_name => $attribute_value) {
            if(is_array($attribute_value))
                $item[$attribute_name] = "";
        }

        // Create a hash form the items attributes so we can compare against the database later
        $itemXmlHash = hash('sha256', implode('', $item));

        // Check if the item already exists in the database
        $getItemQuery = $database->prepare("
        SELECT  entity_id,
                CategoryName,
                sku, 
                name, 
                description, 
                shortdesc, 
                price, 
                link, 
                image, 
                Brand, 
                Rating, 
                CaffeineType, 
                Count, 
                Flavored, 
                Seasonal, 
                Instock, 
                Facebook, 
                IsKCup 
        
        FROM catalog
                                            
        WHERE entity_id = :entity_id
        ");
        $getItemQuery->execute(['entity_id' => $item['entity_id']]);
        $getItemQuery = $getItemQuery->fetchAll();

        if(count($getItemQuery) !== 0)
            $getItemQuery = json_decode(json_encode($getItemQuery), true)[0];

        if(count($getItemQuery) !== 0) {
            // If item already exists choose depening on the users inputmode if we update it or skip it
            if($CliComponent->getUpdateAllowed($argc, $argv) === true) {
                $itemSqliteHash = hash('sha256', implode('', $getItemQuery));

                if($itemXmlHash !== $itemSqliteHash) {
                    $updateItemQuery = $database->prepare("
                    UPDATE catalog
                    SET
                    
                    CategoryName = :CategoryName, 
                    sku = :sku, 
                    name = :name,
                    description = :description, 
                    shortdesc = :shortdesc, 
                    price = :price, 
                    link = :link, 
                    image = :image, 
                    Brand = :Brand, 
                    Rating = :Rating, 
                    CaffeineType = :CaffeineType, 
                    Count = :Count, 
                    Flavored = :Flavored, 
                    Seasonal = :Seasonal, 
                    Instock = :Instock, 
                    Facebook = :Facebook, 
                    IsKCup = :IsKCup
                    
                    WHERE entity_id = :entity_id
                    ");

                    $updateItemQuery->execute(
                        [
                            'entity_id' => $item['entity_id'],
                            'CategoryName' => $item['CategoryName'],
                            'sku' => $item['sku'],
                            'name' => $item['name'],
                            'description' => $item['description'],
                            'shortdesc' => $item['shortdesc'],
                            'price' => $item['price'],
                            'link' => $item['link'],
                            'image' => $item['image'],
                            'Brand' => $item['Brand'],
                            'Rating' => $item['Rating'],
                            'CaffeineType' => $item['CaffeineType'],
                            'Count' => $item['Count'],
                            'Flavored' => $item['Flavored'],
                            'Seasonal' => $item['Seasonal'],
                            'Instock' => $item['Instock'],
                            'Facebook' => $item['Facebook'],
                            'IsKCup' => $item['IsKCup'],
                        ]
                    );
        
                    $CliComponent->println("Item updated: ".$item['entity_id']."!", $CliComponent->getColor("green"));
                }else{
                    $CliComponent->println("Item data is the same! Skipping. (entity_id: ".$item['entity_id'].")", $CliComponent->getColor("yellow"), "warning");
                }
            }else{
                $CliComponent->println("Item already exists! Skipping. (entity_id: ".$item['entity_id'].")", $CliComponent->getColor("yellow"), "warning");
            }
        }else{
            // If item not exists create it in the database
            $insertDataQuery = $database->prepare("
            INSERT INTO catalog (
                entity_id,
                CategoryName,
                sku,
                name,
                description,
                shortdesc,
                price,
                link,
                image,
                Brand,
                Rating, 
                CaffeineType,
                Count,
                Flavored,
                Seasonal,
                Instock,
                Facebook,
                IsKCup
            )

            VALUES 

            (
                :entity_id,
                :CategoryName,
                :sku,
                :name,
                :description,
                :shortdesc,
                :price,
                :link,
                :image,
                :Brand,
                :Rating, 
                :CaffeineType,
                :Count,
                :Flavored,
                :Seasonal,
                :Instock,
                :Facebook,
                :IsKCup
            )
            ");

            $insertDataQuery->execute(
                [
                    'entity_id' => $item['entity_id'],
                    'CategoryName' => $item['CategoryName'],
                    'sku' => $item['sku'],
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'shortdesc' => $item['shortdesc'],
                    'price' => $item['price'],
                    'link' => $item['link'],
                    'image' => $item['image'],
                    'Brand' => $item['Brand'],
                    'Rating' => $item['Rating'],
                    'CaffeineType' => $item['CaffeineType'],
                    'Count' => $item['Count'],
                    'Flavored' => $item['Flavored'],
                    'Seasonal' => $item['Seasonal'],
                    'Instock' => $item['Instock'],
                    'Facebook' => $item['Facebook'],
                    'IsKCup' => $item['IsKCup'],
                ]
            );

            $CliComponent->println("Item added: ".$item['entity_id']."!", $CliComponent->getColor("green"));
        }
    }

    $CliComponent->println("End of script reached; exiting.", $CliComponent->getColor("green"));

} catch(Exception $e) {
    $CliComponent->println($e->getMessage(), $CliComponent->getColor("red"), "error");
}

?>