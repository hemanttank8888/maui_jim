<?php

class DatabaseHandler
{
    private $conn;

    public function __construct($servername, $username, $password, $database)
    {
        $this->conn = new mysqli($servername, $username, $password, $database);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS sunglasses (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            productName VARCHAR(255) NOT NULL,
            lensMaterial VARCHAR(255) NOT NULL,
            mauiEvolution VARCHAR(255) NOT NULL,
            polycarbonate VARCHAR(255),
            mauiBrilliant VARCHAR(255) NOT NULL,
            styleCode VARCHAR(255) NOT NULL,
            frame VARCHAR(255) NOT NULL,
            lens VARCHAR(255) NOT NULL,
            price VARCHAR(255) NOT NULL
        )";

        if ($this->conn->query($sql) === TRUE) {
            echo "Table created successfully<br>";
        } else {
            echo "Error creating table: " . $this->conn->error;
        }
    }

    public function insertData($dataList)
    {
        foreach ($dataList as $data) {
            $productName = $this->sanitize($data['productName']);
            $lensMaterial = $this->sanitize($data['lensMaterial']);
            $mauiEvolution = $this->sanitize($data['mauiEvolution']);
            $polycarbonate = $this->sanitize($data['polycarbonate']);
            $mauiBrilliant = $this->sanitize($data['mauiBrilliant']);
    
            foreach ($data['productVariants'] as $variant) {
                $styleCode = $this->sanitize($variant['styleCode']);
                $frame = $this->sanitize($variant['frame']);
                $lens = $this->sanitize($variant['lens']);
                $price = $this->sanitize($variant['price']);
    
                // Use prepared statement for SELECT query
                $stmtSelect = $this->conn->prepare("SELECT * FROM sunglasses WHERE productName = ? AND styleCode = ?");
                $stmtSelect->bind_param("ss", $productName, $styleCode);
                $stmtSelect->execute();
                $existingRecord = $stmtSelect->get_result()->fetch_assoc();
                $stmtSelect->close();
    
                if ($existingRecord) {
                    // Update existing record
                    $stmtUpdate = $this->conn->prepare("
                        UPDATE sunglasses
                        SET lensMaterial = ?, mauiEvolution = ?, polycarbonate = ?, mauiBrilliant = ?, frame = ?, lens = ?, price = ?
                        WHERE productName = ? AND styleCode = ?
                    ");
                    $stmtUpdate->bind_param("sssssssss", $lensMaterial, $mauiEvolution, $polycarbonate, $mauiBrilliant, $frame, $lens, $price, $productName, $styleCode);
                    $stmtUpdate->execute();
                    $stmtUpdate->close();
                } else {
                    // Insert new record
                    $stmtInsert = $this->conn->prepare("
                        INSERT INTO sunglasses (productName, lensMaterial, mauiEvolution, polycarbonate, mauiBrilliant, styleCode, frame, lens, price) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmtInsert->bind_param("sssssssss", $productName, $lensMaterial, $mauiEvolution, $polycarbonate, $mauiBrilliant, $styleCode, $frame, $lens, $price);
                    $stmtInsert->execute();
                    $stmtInsert->close();
                }
            }
        }
    }

    private function sanitize($input)
    {
        // Implement your own data sanitization logic here if needed
        return $input;
    }

    public function closeConnection()
    {
        $this->conn->close();
    }
}


?>
