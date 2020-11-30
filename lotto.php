<!DOCTYPE html>
<html>
<head>
    <title> LOTTERY </title>
</head>
<body>
<?php

session_start();

if (!isset($_SESSION['winrow'])) {
    $allnumbers = range(1,40);
    shuffle($allnumbers);
    $_SESSION['winrow'] = array_slice($allnumbers, 0, 7);
    }

function printArray($arr, $info=""){
    sort($arr);
	if($info != ""){echo $info;}
    foreach($arr as $elem){echo $elem." ";}
    echo "<br>";
    }

printArray($_SESSION['winrow'], "(A little bit of cheating)<br>Winning row: ");

// Create array for checkboxs names
$cboxs = [];
for($i=1;$i<41;$i++){
    array_push($cboxs, "c_".$i);
}

$numberErr = "";
$numbers = $submit="";
$selected_numbers = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
     
    foreach($cboxs as $iter){
        if(isset($_POST[$iter])){
            array_push($selected_numbers, $_POST[$iter]);
        }
    }
    
    if(count($selected_numbers) != 7){
        $numberErr = "Only 7 selected numbers are accepted";
    }
    

    if(isset($_POST["submit"])){
      $submit = true;
    }
 
  } 

function Checkboxs(){
    $num = 1;
    for($i = 0; $i < 4; $i++){
        for($j=0; $j < 10; $j++){
            echo "<input type='checkbox' name='c_".$num."' value='".$num."'>".$num." ";
            $num++;
        }
        echo "<br>";
    }
}  
 

?>

<h1>Lottery</h1>
<form method="post"
      action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"
    >
    Select 7 numbers for winning! <br>
    <br>
    <?php Checkboxs();?>
    <br><br>
    <input type="submit" name="submit" value="Confirm"> <br><br>
    <span class="error"> <?php echo $numberErr;?></span>
    </form>

<?php 

if($submit and $numberErr===""){
  
  printArray($selected_numbers, "Selected numbers: ");
  
  $correct = array_intersect($selected_numbers, $_SESSION['winrow']);
  
  if(count($correct)>0){
    echo count($correct)."/".count($selected_numbers);  
    if(count($correct)>1){
        printArray($correct, " were correct, and the correct numbers were ");}
    else{printArray($correct, " was correct, and it was ");}
  
  }else{
      echo "None of your selected number was correct <br>";
  }
  
  if(!array_diff($_SESSION['winrow'], $selected_numbers)){
        echo "WIN!!!!! <br>";
  }else{
        echo "<br> You lost! :( <br>";
    }
}

phpinfo();

?>    
    
</body>
</html>