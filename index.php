<!DOCTYPE html>
<html>
 <head>
  <meta charset="utf-8">
  <title>Календарь</title>
 </head>
 <body>
  <form action="index.php" method="post">
   <p>Выберите дату: <input type="date" name="calendar">
   <input type="submit" value="Отправить"></p>
  </form>
 </body>
</html>

<?php
class rbc{    
	//конструктор класса, который позволяет задать дату
    public function __construct ($date = null){
	
        if ($date == null){
            $date = date("d/m/Y", strtotime($_POST['calendar']));
        }
        $this -> date = $date;
    }
    	// функция выявления курса на заданную дату
      public function curs(){
      	$date=$this -> date;

      $link = "http://www.cbr.ru/scripts/XML_daily.asp?date_req=$date"; // Ссылка на XML-файл с курсами валют
      $content = file_get_contents($link); // Скачиваем содержимое страницы
      $dom = new domDocument("1.0", "cp1251"); // Создаём DOM
      $dom->loadXML($content); // Загружаем в DOM XML-документ
      $root = $dom->documentElement; // Берём корневой элемент
      $childs = $root->childNodes; // Получаем список дочерних элементов
      $data = array(); // Набор данных
        for ($i = 0; $i < $childs->length; $i++) {
             $childs_new = $childs->item($i)->childNodes; // Берём дочерние узлы
   
         for ($j = 0; $j < $childs_new->length; $j++) {
      /* Ищем интересующие нас валюты */
             $el = $childs_new->item($j);
               $code = $el->nodeValue;
              if (($code == "USD") || ($code == "EUR")) $data[] = $childs_new; // Добавляем необходимые валюты в массив
           }
         }

        return $data;
    }
}

$day = new rbc(); //Курс на заданный день
$day_before = new rbc(date("d/m/Y", strtotime($_POST['calendar']."- 1 day")));  // курс на предыдущий день

$x=$day->curs();
$y=$day_before->curs();

for ($i = 0; $i < count ($x); $i++) {
    $list = $x[$i];
    $list_b = $y[$i];
    for ($j = 0; $j < $list->length; $j++) {
      $el1 = $list->item($j);
      $el1_b = $list_b->item($j);
      /* Выводим курсы валют */
      
      if ($el1->nodeName == "Name") echo $el1->nodeValue." - ";  // меняем разделитель с запятой на точку для корректной операции вычитания
      elseif ($el1->nodeName == "Value"){ $value=$el1->nodeValue;
      $value=str_replace(",",".",$value);
      echo $value." рублей";
      }

      if($el1_b->nodeName == "Value"){
        $value_b=$el1_b->nodeValue;
        $value_b=str_replace(",",".",$value_b);
        $mod=$value - $value_b;  // расчет изменения курса
      if($mod>0) echo " &uarr;".$mod;
    else if($mod<0) echo " &darr;".$mod;
    else echo " Курс не изменился с предыдущего дня";
    echo "<br>";
      }     
    }
  }
?>