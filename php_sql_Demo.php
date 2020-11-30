<?php

class DataDemo {

    const DB_HOST = 'localhost';
    const DB_NAME = 'classicmodels';
    const DB_USER = 'root';
    const DB_PASSWORD = '';

    private $pdo = null;

    public function __construct() {
        $conStr = sprintf("mysql:host=%s;dbname=%s", self::DB_HOST, self::DB_NAME);
        echo "Connected to ". self::DB_NAME ." at ".self::DB_HOST." successfully.<br>";
        try {
            $this->pdo = new PDO($conStr, self::DB_USER, self::DB_PASSWORD);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function __destruct() {
        $this->pdo = null;
        echo "<br>Object destroyed!<br>";
    }

    // Insert into database 
    public function insert() {
        $sql = "INSERT INTO tasks (
                      subject,
                      description,
                      start_date,
                      end_date
                  )
                  VALUES (
                      'Learn PHP MySQL Insert Dat',
                      'PHP MySQL Insert data into a table',
                      '2013-01-01',
                      '2013-01-01'
                  )";

        return $this->pdo->exec($sql); 
    }

    function insertSingleRow($subject, $description, $startDate, $endDate, $thoughts) {
        $task = array(':subject' => $subject,
            ':description' => $description,
            ':start_date' => $startDate,
            ':end_date' => $endDate,
            ':thoughts' => $thoughts);

        $sql = 'INSERT INTO tasks (
                      subject,
                      description,
                      start_date,
                      end_date,
                      thoughts
                  )
                  VALUES (
                      :subject,
                      :description,
                      :start_date,
                      :end_date,
                      :thoughts
                  );';

        $q = $this->pdo->prepare($sql);

        return $q->execute($task);
    }

    public function update($id, $subject, $description, $thoughts, $startDate, $endDate) {
        $task = [
            ':taskid' => $id,
            ':subject' => $subject,
            ':description' => $description,
            ':start_date' => $startDate,
            ':end_date' => $endDate,
            ':thoughts' => $thoughts
            ];


        $sql = 'UPDATE tasks
                    SET subject      = :subject,
                         start_date  = :start_date,
                         end_date    = :end_date,
                         description = :description,
                         thoughts    = :thoughts
                  WHERE task_id = :taskid';

        $q = $this->pdo->prepare($sql);

        return $q->execute($task);
    }

    public function delete($id) {

        $sql = 'DELETE FROM tasks
                WHERE task_id = :task_id';

        $q = $this->pdo->prepare($sql);

        return $q->execute([':task_id' => $id]);
    }

    public function deleteAll(){
        $sql = 'DELETE FROM tasks';
        return $this->pdo->exec($sql);
    }

    
    public function truncateTable() {
        $sql = '
        CREATE TABLE IF NOT EXISTS tasks;
        TRUNCATE TABLE tasks;        
        ';
        return $this->pdo->exec($sql);
    }
    

    public function createTaskTable() {
        echo "<br>Object was created<br>";
        $sql = <<<EOSQL
            CREATE TABLE IF NOT EXISTS tasks (
                task_id     INT AUTO_INCREMENT PRIMARY KEY,
                subject     VARCHAR (255)        DEFAULT NULL,
                start_date  DATE                 DEFAULT NULL,
                end_date    DATE                 DEFAULT NULL,
                description VARCHAR (400)        DEFAULT NULL,
                thoughts VARCHAR (50)        DEFAULT NULL
            );
        EOSQL;
        return $this->pdo->exec($sql);
    }

    public function selectThought($thoughts) {

        $sql = 'SELECT task_id
                FROM tasks
                WHERE thoughts LIKE :thoughts
                LIMIT 1';


        $q = $this->pdo->prepare($sql);
        $q->execute([':thoughts' => $thoughts]);
        $q->setFetchMode(PDO::FETCH_ASSOC);

        while ($row = $q->fetch()) {
            return $row['task_id'];
        }
        return 0;

    }

    public function insertMultiple($myarray){
  
        $sql = 'INSERT INTO tasks (
                      subject,
                      description,
                      thoughts,
                      start_date,
                      end_date
                  )
                  VALUES (?, ?, ?, ?, ?);';

        $q = $this->pdo->prepare($sql);
        
        try {
            $this->pdo->beginTransaction();
            foreach ($myarray as $row)
            {   
                $q->execute($row);
            }
            $this->pdo->commit();

        }catch (PDOException $e){
            $this->pdo->rollback();
            echo "Multiple insert error! <br>";
            throw $e;
        }
    }

    public function insertMultiple2($myarray2) {

        $sql = 'INSERT INTO tasks (
            subject,
            description,
            thoughts,
            start_date,
            end_date
        )
        VALUES ( 
            :subject,
            :description,
            :thoughts,
            :start_date,
            :end_date)';

        $q = $this->pdo->prepare($sql);

        try {
            $this->pdo->beginTransaction();

            // each record has 5 columns total 25 elements
            for ($iter = 0; $iter < count($myarray2); $iter += 5 )  {
                $q->bindParam(':subject', $myarray2[$iter]);
                $q->bindParam(':description', $myarray2[$iter+1]);
                $q->bindParam(':thoughts', $myarray2[$iter+2]);
                $q->bindParam(':start_date', $myarray2[$iter+3]);
                $q->bindParam(':end_date', $myarray2[$iter+4]);
                $q->execute();
            }

            $this->pdo->commit();
        }
        catch (PDOException $e) {
            $this->pdo->rollback();
            echo "multiple insert error! <br>";
            throw $e;
        }

    }

    public function UpdateMultiple($myarray){        
        try {
            $this->pdo->beginTransaction();
            foreach ($myarray as $row)
            {   
                $this->update($row[0], $row[1], $row[2], $row[3], $row[4], $row[5]);
            }
            $this->pdo->commit();
        }catch (PDOException $e){
            $this->pdo->rollback();
            echo "Multiple update error! <br>";
            throw $e;
        }
    }

    // Using JOIN and SELECT
    public function selectCustomerNoOrder(){
        $sql = 'SELECT
                    c.customerNumber,
                    c.customerName,
                    o.orderNumber,
                    o.status
                FROM
                    customers c
                LEFT JOIN orders o
                ON c.customerNumber = o.customerNumber
                WHERE orderNumber IS NULL;';
        
        $q = $this->pdo->query($sql);
        $q->setFetchMode(PDO::FETCH_ASSOC);

        $customerslist = [];

        while ($customer = $q->fetch()) {
            array_push($customerslist, $customer);
        }

        return $customerslist;
    }

    // Stored Procedure
    public function getCreditLimit(){
        $sql = 'CALL GetCustomers();';

        $q = $this->pdo->query($sql);
        $q->setFetchMode(PDO::FETCH_ASSOC);

        $limitarray = [];

        while ($customer = $q->fetch()) {
            array_push($limitarray, $customer);
        }

        foreach($limitarray as $elem){
            echo $elem['customerName']. " $".$elem['creditlimit']."<br>";
        }
    }

    // Stored Procedure with output parameter
    public function getCustomerLevel(int $id){
    try{

        $sql = 'CALL GetCustomerLevel(:id,@level)';
        $q = $this->pdo->prepare($sql);

        $q->bindParam(':id', $id, PDO::PARAM_INT);
        $q->execute();
        $q->closeCursor();

        $row = $this->pdo->query("SELECT @level AS level")->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $row !== false ? $row['level'] : null;
            echo "Customer ".$id." is ".$row['level']."<br>";
        }
    } catch (PDOException $e) {
        die("Error occurred:" . $e->getMessage());
    }

    }

    // Call Views:
    public function getLuxuryItems(){
        $sql = 'SELECT * FROM luxuryItems'; 

        $q = $this->pdo->query($sql);
        $q->setFetchMode(PDO::FETCH_ASSOC);

        $dummyarray = [];

        while ($item = $q->fetch()) {
            array_push($dummyarray, $item);
        }
        foreach($dummyarray as $row){
            echo $row['name']." ".$row['price']."<br>";
        }

    }

    public function getAllOffices(){
        $sql = 'SELECT * FROM offices;';

        $q = $this->pdo->query($sql);
        $q->setFetchMode(PDO::FETCH_ASSOC);

        $offices = [];

        while ($office = $q->fetch()) {
            array_push($offices, $office);
        }

        echo "<br>All Offices: <br> *****************************************<br>";
        foreach($offices as $office){
            echo $office['city'].", ".$office['country']."<br>";
            echo "\t"."Phone: ".$office['phone']."<br>";
            echo "\t"."Address: ".$office['addressLine1']." ".$office['addressLine2']."<br>";
            echo "*****************************************<br>";
        }

    }

    public function getOffice($officeCode){
        $sql = 'SELECT * FROM offices
                WHERE officeCode = ?;';
        
        $q = $this->pdo->prepare($sql);
        $q->execute([$officeCode]);
        $q->setFetchMode(PDO::FETCH_ASSOC);

        if(!$q->rowCount()){
            echo "<br>*************************************<br>";
            echo "No office with the office code ".$officeCode." found";
            echo "<br>*************************************<br>";
        }else{
            echo "<br>Office Information<br>";
            while ($office = $q->fetch()) {
                foreach($office as $colname  => $col_value){
                    echo ucfirst($colname).": ".$col_value."<br>";
                }
                echo "---------------------------------------------<br>";
            }
        }
    
    }
    
    // Stored procedure with input and output parameters
    public function getOffice2($codeid) {
        try {
            $sql = 'CALL GetOffice(:id,@city,@country)';
            $q = $this->pdo->prepare($sql);
            $q->bindParam(':id', $codeid, PDO::PARAM_INT);
            $q->execute();
            $q->closeCursor();

            $row = $this->pdo->query("SELECT @city AS city, @country AS country")->fetch(PDO::FETCH_ASSOC);
            if($row){
                echo "Office " . $codeid . " is " . $row['city'] . " " . $row['country'];
            }
        } catch (PDOException $e) {
            die("Error occurred:" . $e->getMessage());
            }
        }
    

} 

$obj = new DataDemo();

$new_tasks = array(
    array('Tasks example row 1', 'Example', '2020-11-01', '2020-11-24', 'Very nice'),
    array('Tasks example row 2', 'Example', '2020-11-23', '2020-11-24', 'OK')
);

foreach($new_tasks as $task){
$obj->insertSingleRow($task[0], $task[1], $task[2],$task[3],$task[4]);
}

$myarray = [['arraytest 1', 'multiple demo', 'something', '2020-01-01', '2020-01-03'],
            ['arraytest 2', 'multiple demo', 'nothing', '2020-02-01', '2020-02-03'],
            ['arraytest 3', 'multiple demo', 'anything', '2020-03-01', '2020-03-03'],
            ['arraytest 4', 'multiple demo', 'everything', '2020-04-01', '2020-04-03'],
            ['arraytest 5', 'multiple demo', 'something', '2020-05-01', '2020-05-03'] 
            ];


$obj->insertMultiple($myarray);



$myarray2 =[
    [1, 'Update 1', 'desc 1', 'no comment', '1900-01-01', '1900-02-01'],
    [2, 'Update 2', 'desc 2', 'no comment', '1900-01-01', '1900-02-01']
];

$obj->UpdateMultiple($myarray2);

$customers = $obj->selectCustomerNoOrder();

echo "<br> Customers with no orders: <br><br>";
foreach($customers as $cust){
    foreach($cust as $c){
        echo $c." ";
    }
    echo "<br>";
}

echo "<br><br>Credit Limit<br><br>";
$obj->getCreditLimit();

$obj->getCustomerLevel(130);
$obj->getCustomerLevel(103);
$obj->getCustomerLevel(328);
$obj->getCustomerLevel(144);


echo "<br>Luxury items: <br>";
$obj->getLuxuryItems();

$obj->getAllOffices();

$officeCode = [2, 3, 10, 100];
foreach($officeCode as $oc){
    $obj->getOffice($oc);
}

$obj->getOffice2(3);

?>