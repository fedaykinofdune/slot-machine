<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Slot
 *
 * @author vadim24816
 */

require_once 'Appconfig.php';

class Slot {
  //make it singletone
  protected static $slot;
  public static $user;
  private function __construct(){}
  private function __clone(){} 
  private function __wakeup(){} 
  public static function get_instance(){
    if (is_null(self::$slot)){
      self::$slot = new Slot();
      //if user not exist it will create him!
      self::$user = new User();
      self::$user->auth();
      //if (self::$user->get_from_db())
      //self::$slot->slot_filling();
      return self::$slot;
    }
    return self::$slot;
  }
  
  //last payline == current payline, reels is array of reel objects
  protected $last_payline;//, $reels = array(3);
  public $reel1,$reel2,$reel3, $reels;
  public $currentBet, $currentUserBalance, $lastBet, $state;

  //validate client's bet 
  public function isValidBet($betFromClient){
    //not a number
    if (!is_numeric($betFromClient)){
      return false;
    }
    self::$user->update_from_db();
    if ($betFromClient > 0 && $betFromClient <= self::$user->money_balance){
      return true;
    }
    else {
      return false;
    }
  }

  //make spin
  public function spin($betFromClient){
    if (!$this->isValidBet($betFromClient)){
      echo '[Bet <= 0 or Bet not number.]';
      return false;
    }
    
    //already started
    if ($this->state == 'started'){
      //console.log('[Slot started already. Wait while it have stoped! ]');
      echo '[Slot started already. Wait while it have stoped! ]';
      return false;
    }
    //slot started
    //$this->getStateStarted();
    $this->state = 'started';
    //$this->getStateStop();
    $this->state = 'stop';
    $this->currentBet = $betFromClient;
    //bet was 
    $this->lastBet = $this->currentBet;
    self::$user->money_balance -= $this->currentBet;
    $this->currentBet = 0;
    $new_payline = $this->get_new_payline();
    $this->last_payline = $new_payline;
    $s = self::$user->save_in_db();
    self::$user->update_from_db();
    return json_encode($new_payline);
    
    //todo: save the last showed symbols
  }

  //return new randomly generated payline
  public function get_new_payline(){
    for ($i = 0; $i < 3; $i++){
      $syms[$i] = $this->reels[$i]->get_new_randomly_choosed_symbol();
    }
    $new_payline = new Payline($syms[0], $syms[1], $syms[2]);
    return $new_payline;
  }
  
}

class WeightTable{
  //make it singletone
  protected static $weight_table;
  private function __construct(){}
  private function __clone(){} 
  private function __wakeup(){} 
  public static function get_instance(){
    if (is_null(self::$weight_table)){
      self::$weight_table = new WeightTable();
      self::$weight_table->total_weight_table_filling();
      return self::$weight_table;
    }
    return self::$weight_table;
  }
  
  //protected
          public $reel1,$reel2,$reel3;
  public $symbol_weight_reel1,$symbol_weight_reel2,$symbol_weight_reel3;
  public function total_weight_table_filling(){
    //the symbol weight on reelN
    $this->symbol_weight_reel1[Symbol::$pyramid] = 4;
    $this->symbol_weight_reel1[Symbol::$bitcoin] = 5;
    $this->symbol_weight_reel1[Symbol::$anonymous] = 6;
    $this->symbol_weight_reel1[Symbol::$onion] = 6;
    $this->symbol_weight_reel1[Symbol::$anarchy] = 7;
    $this->symbol_weight_reel1[Symbol::$peace] = 8;
    $this->symbol_weight_reel1[Symbol::$blank] = 28;
    
    $this->symbol_weight_reel2[Symbol::$pyramid] = 3;
    $this->symbol_weight_reel2[Symbol::$bitcoin] = 4;
    $this->symbol_weight_reel2[Symbol::$anonymous] = 4;
    $this->symbol_weight_reel2[Symbol::$onion] = 5;
    $this->symbol_weight_reel2[Symbol::$anarchy] = 5;
    $this->symbol_weight_reel2[Symbol::$peace] = 6;
    $this->symbol_weight_reel2[Symbol::$blank] = 37;
    
    $this->symbol_weight_reel3[Symbol::$pyramid] = 1;
    $this->symbol_weight_reel3[Symbol::$bitcoin] = 2;
    $this->symbol_weight_reel3[Symbol::$anonymous] = 3;
    $this->symbol_weight_reel3[Symbol::$onion] = 4;
    $this->symbol_weight_reel3[Symbol::$anarchy] = 6;
    $this->symbol_weight_reel3[Symbol::$peace] = 6;
    $this->symbol_weight_reel3[Symbol::$blank] = 42;
    //total: 64 for every reel
    
    $slot = Slot::get_instance();
    $this->reel1 = new Reel('reel1');
    $this->reel1->reel_line = $this->get_symbols_reel_line($this->symbol_weight_reel1);
    $this->reel2 = new Reel('reel2');
    $this->reel2->reel_line = $this->get_symbols_reel_line($this->symbol_weight_reel2);
    $this->reel3 = new Reel('reel3');
    $this->reel3->reel_line = $this->get_symbols_reel_line($this->symbol_weight_reel3);
    
    $slot->reels[0] = $this->reel1;
    $slot->reels[1] = $this->reel2;
    $slot->reels[2] = $this->reel3;
    
    //weight table filling, not total weight
    $this->weight_table_filling();
    /*
    $this->reel1_line = $this->get_symbols_reel_line($this->reel1);
    $this->reel2_line = $this->get_symbols_reel_line($this->reel2);
    $this->reel3_line = $this->get_symbols_reel_line($this->reel3);
    */
  }
  
  public function weight_table_filling(){
    //for reel1 
    $this->reel1->filling_by_given_symbol_specifin_number_of_cells(Symbol::$bitcoin, 3);
    $this->reel1->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 2);
    $this->reel1->filling_by_given_symbol_specifin_number_of_cells(Symbol::$anonymous, 3);
    $this->reel1->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 2);

    $this->reel1->filling_by_given_symbol_specifin_number_of_cells(Symbol::$onion, 3);
    $this->reel1->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 2);
    $this->reel1->filling_by_given_symbol_specifin_number_of_cells(Symbol::$anarchy, 4);
    $this->reel1->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 2);
    
    $this->reel1->filling_by_given_symbol_specifin_number_of_cells(Symbol::$peace, 4);
    $this->reel1->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 5);
    $this->reel1->filling_by_given_symbol_specifin_number_of_cells(Symbol::$pyramid, 4);
    $this->reel1->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 5);
    
    $this->reel1->filling_by_given_symbol_specifin_number_of_cells(Symbol::$bitcoin, 2);
    $this->reel1->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 2);
    $this->reel1->filling_by_given_symbol_specifin_number_of_cells(Symbol::$anonymous, 3);
    $this->reel1->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 2);
    
    $this->reel1->filling_by_given_symbol_specifin_number_of_cells(Symbol::$onion, 3);
    $this->reel1->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 2);
    $this->reel1->filling_by_given_symbol_specifin_number_of_cells(Symbol::$anarchy, 3);
    $this->reel1->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 2);
    
    $this->reel1->filling_by_given_symbol_specifin_number_of_cells(Symbol::$peace, 4);
    $this->reel1->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 2);
    
    //for reel2
    $this->reel2->filling_by_given_symbol_specifin_number_of_cells(Symbol::$bitcoin, 2);
    $this->reel2->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 3);
    $this->reel2->filling_by_given_symbol_specifin_number_of_cells(Symbol::$anonymous, 2);
    $this->reel2->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 3);

    $this->reel2->filling_by_given_symbol_specifin_number_of_cells(Symbol::$onion, 3);
    $this->reel2->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 3);
    $this->reel2->filling_by_given_symbol_specifin_number_of_cells(Symbol::$anarchy, 3);
    $this->reel2->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 3);
    
    $this->reel2->filling_by_given_symbol_specifin_number_of_cells(Symbol::$peace, 3);
    $this->reel2->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 5);
    $this->reel2->filling_by_given_symbol_specifin_number_of_cells(Symbol::$pyramid, 3);
    $this->reel2->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 5);
    
    $this->reel2->filling_by_given_symbol_specifin_number_of_cells(Symbol::$bitcoin, 2);
    $this->reel2->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 3);
    $this->reel2->filling_by_given_symbol_specifin_number_of_cells(Symbol::$anonymous, 2);
    $this->reel2->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 3);
    
    $this->reel2->filling_by_given_symbol_specifin_number_of_cells(Symbol::$onion, 2);
    $this->reel2->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 3);
    $this->reel2->filling_by_given_symbol_specifin_number_of_cells(Symbol::$anarchy, 2);
    $this->reel2->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 3);
    
    $this->reel2->filling_by_given_symbol_specifin_number_of_cells(Symbol::$peace, 3);
    $this->reel2->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 3);
    
    //for reel3
    $this->reel3->filling_by_given_symbol_specifin_number_of_cells(Symbol::$bitcoin, 1);
    $this->reel3->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 3);
    $this->reel3->filling_by_given_symbol_specifin_number_of_cells(Symbol::$anonymous, 2);
    $this->reel3->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 3);

    $this->reel3->filling_by_given_symbol_specifin_number_of_cells(Symbol::$onion, 2);
    $this->reel3->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 3);
    $this->reel3->filling_by_given_symbol_specifin_number_of_cells(Symbol::$anarchy, 3);
    $this->reel3->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 3);
    
    $this->reel3->filling_by_given_symbol_specifin_number_of_cells(Symbol::$peace, 3);
    $this->reel3->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 8);
    $this->reel3->filling_by_given_symbol_specifin_number_of_cells(Symbol::$pyramid, 1);
    $this->reel3->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 7);
    
    $this->reel3->filling_by_given_symbol_specifin_number_of_cells(Symbol::$bitcoin, 1);
    $this->reel3->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 3);
    $this->reel3->filling_by_given_symbol_specifin_number_of_cells(Symbol::$anonymous, 1);
    $this->reel3->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 3);
    
    $this->reel3->filling_by_given_symbol_specifin_number_of_cells(Symbol::$onion, 2);
    $this->reel3->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 3);
    $this->reel3->filling_by_given_symbol_specifin_number_of_cells(Symbol::$anarchy, 3);
    $this->reel3->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 3);
    
    $this->reel3->filling_by_given_symbol_specifin_number_of_cells(Symbol::$peace, 3);
    $this->reel3->filling_by_given_symbol_specifin_number_of_cells(Symbol::$blank, 3);
    
    
  }

  //$this->reel1
  //return the filled line (array) that consists of 64 symbols with considering the weight table
  function get_symbols_reel_line($reel){
    //weight_arr == reel line
    $weight_arr = array(64);
    $current_num_in_weight_arr = 0;//counter in weight_arr
    //for every symbol
    foreach ($reel as $key => $value) {
      for ($i = 0; $i < $reel[$key]; $i++){
        $weight_arr[$current_num_in_weight_arr] = $key;
        //total number of processed cells
        $current_num_in_weight_arr++;
      }
    }
    //echo $current_num_in_weight_arr;
    return $weight_arr;
  }
}

$w1 = WeightTable::get_instance();
/*
echo $w1->reel1->get_new_randomly_choosed_symbol();
echo ' ';
echo $w1->reel2->get_new_randomly_choosed_symbol();
echo ' ';
echo $w1->reel3->get_new_randomly_choosed_symbol();
echo ' ';
 * 
 */

function show_generated_total_weight_table() {
  echo 'generated the number  of appearances table';
  $w1 = WeightTable::get_instance();
  echo '<table border="1px" style="border-collapse: collapse;">';
  echo '<tr>';
    
    echo '<td>';
    echo '---';
    echo '</td>';
    echo '<td>';
    echo Symbol::$pyramid;
    echo '</td>';
    echo '<td>';
    echo Symbol::$bitcoin;
    echo '</td>';
    echo '<td>';
    echo Symbol::$anonymous;
    echo '</td>';
    echo '<td>';
    echo Symbol::$onion;
    echo '</td>';
    echo '<td>';
    echo Symbol::$anarchy;
    echo '</td>';
    echo '<td>';
    echo Symbol::$peace;
    echo '</td>';
    echo '<td>';
    echo Symbol::$blank;
    echo '</td>';
    echo '<td>';
    echo 'Sum';
    echo '</td>';
    echo '</tr>';
  $number_of_symbol = array();
  for ($i = 0; $i < 3; $i++){
    $number_of_symbol[$i][Symbol::$pyramid] = 0;
    $number_of_symbol[$i][Symbol::$bitcoin] = 0;
    $number_of_symbol[$i][Symbol::$anonymous] = 0;
    $number_of_symbol[$i][Symbol::$onion] = 0;
    $number_of_symbol[$i][Symbol::$anarchy] = 0;
    $number_of_symbol[$i][Symbol::$peace] = 0;
    $number_of_symbol[$i][Symbol::$blank] = 0;
  }
  for($reel_num = 0; $reel_num < 3; $reel_num++){
    echo '<tr>';
    for ($i = 0; $i < 64; $i++){
      if ($reel_num == 0){
        $cur_sym = $w1->reel1->get_new_randomly_choosed_symbol();
      }
      if ($reel_num == 1){
        $cur_sym = $w1->reel2->get_new_randomly_choosed_symbol();
      }
      if ($reel_num == 2){
        $cur_sym = $w1->reel3->get_new_randomly_choosed_symbol();
      }
      foreach ($number_of_symbol[$reel_num] as $key => $value) {
        //e.g.: if (Symbol::$pyramid == $cur_sym)
        if ($key == $cur_sym){
          //e.g.: $number_of_symbol[$reel_num][Symbol::$bitcoin]++;
          $number_of_symbol[$reel_num][$key]++;
        }
      }
    }
    echo '<td>';
    echo 'reel #'.$reel_num;
    echo '</td>';
    
    $sum = 0;
    foreach ($number_of_symbol[$reel_num] as $key => $value) {
      //count total weight
      $sum += $value;
      echo '<td>'.$value;
      echo '</td>';
      /*
      *the same as e.g.:
      *echo '<td>';
      *echo $number_of_symbol[$reel_num][Symbol::$onion];
      *echo '</td>';
      * 
      */
    }
    echo '<td>';
    echo $sum;
    echo '</td>';
    echo '</tr>';
  }
  //dump_it($number_of_symbol);
  echo '</table>';
}

function possible_combinations(){
  $w1 = WeightTable::get_instance();
  $slot = Slot::get_instance();
  $paytable = Paytable::get_instance();
  
  $number_of_win_lines[Symbol::$pyramid] = 0;
  $number_of_win_lines[Symbol::$bitcoin] = 0;
  $number_of_win_lines[Symbol::$anonymous] = 0;
  $number_of_win_lines[Symbol::$onion] = 0;
  $number_of_win_lines[Symbol::$anarchy] = 0;
  $number_of_win_lines[Symbol::$peace] = 0;
  $number_of_win_lines[Symbol::$blank] = 0;
  $number_of_win_lines['bitcoin_2'] = 0;
  $number_of_win_lines['bitcoin_1'] = 0;
  $number_of_win_lines['lose'] = 0;
  $N = 262144;
  //$N = 1000000;
  echo '<br /><table border="1px" style="border-collapse: collapse;">';
  for($i = 0; $i < $N; $i++){
    $new_payline = $slot->get_new_payline();
    $result = $paytable->paylines_matching_with_wins($new_payline);
    //unset($new_payline);
    switch ($result){
      case 'pyramid_3': 
        $number_of_win_lines[Symbol::$pyramid]++;
        break;
      case 'bitcoin_3': 
        $number_of_win_lines[Symbol::$bitcoin]++;
        break;
      case 'anonymous_3': 
        $number_of_win_lines[Symbol::$anonymous]++;
        break;
      case 'onion_3': 
        $number_of_win_lines[Symbol::$onion]++;
        break;
      case 'anarchy_3': 
        $number_of_win_lines[Symbol::$anarchy]++;
        break;
      case 'peace_3': 
        $number_of_win_lines[Symbol::$peace]++;
        break;
      case 'blank_3': 
        $number_of_win_lines[Symbol::$blank]++;
        break;
      case 'bitcoin_2': 
        $number_of_win_lines['bitcoin_2']++;
        break;
      case 'bitcoin_1': 
        $number_of_win_lines['bitcoin_1']++;
        break;
      case 'lose': 
        $number_of_win_lines['lose']++;
        break;
    }
  }
  echo 'Table of amount of wins for '.$N.' stops (the most combinations (excepts bitcoin_2 - any 2 are bitcoins, bitcoin_1 - any 1 is bitcoin and lose) for 3 symbols )';
  echo '<tr>';
  echo '<td>';
  echo 'win combination';
  echo '</td>';
  echo '<td>';
  echo 'combination occurrence';
  echo '</td>';
  echo '<td>';
  echo 'probability of appear';
  echo '</td>';
  echo '<td>';
  echo 'money ( probability * payoff = )';
  echo '</td>';
  echo '<td>';
  echo 'probability of money returning (to player)';
  echo '</td>';
  echo '<td>';
  echo 'money (occurrence * payoff - occurrence * min spin cost)';
  echo '</td>';
  echo '</tr>';
  $min_spin_cost = 1;
  $total_sum = 0;
  $total_probability_of_apear = 0;
  $total_money = 0;
  $probability_of_occur = 0;
  foreach ($number_of_win_lines as $combination_name => $occurrence) {
    echo '<tr>';
    echo '<td>'.$combination_name.'</td>';
    echo '<td>'.$occurrence.'</td>'; //$number_of_win_lines[$combination_name];
    $probability_of_occur = $occurrence/$N;
    echo '<td>'.$occurrence.' / ' .$N. ' = ' .$probability_of_occur.'</td>';
    $paytable = Paytable::get_instance();
    //get payoff for given combination name (key_...)
    if ($combination_name == 'bitcoin_2' || $combination_name == 'bitcoin_1'){
      $total_probability_of_apear += $probability_of_occur;
      $payoff = $paytable->payoff_value_by_name($combination_name);
    }
    elseif ($combination_name == 'lose'){
      $payoff = 0;
    }
    elseif ($combination_name == 'blank'){
      $payoff = 0;
    }
    else{
      $total_probability_of_apear += $probability_of_occur;
      $payoff = $paytable->payoff_value_by_name($combination_name.'_3');
    }
    
    $res = $probability_of_occur * $payoff;
    if ($res > 0)
      $total_sum += $res;
    //echo '<td>'.$occurrence.' * '.$payoff.' = '.$res.'</td>';
    echo '<td>'.$probability_of_occur.' * '.$payoff.' = </td>';
    echo '<td>'.$res.'</td>';
    $money = $occurrence * $payoff - $occurrence * $min_spin_cost;
    $total_money += $money;
    echo '<td>'.$occurrence .' * '.$payoff.' - '.$occurrence.' * '.$min_spin_cost.'  = '.$money.'</td>';
    echo '</tr>';
   
  }
  echo '<tr>';
  echo '<td>TOTAL:</td>';
  echo '<td>'.$N.'</td>';
  echo '<td>'.$total_probability_of_apear.'</td>';
  echo '<td>sum=</td>';
  echo '<td>'.$total_sum.'</td>';
  echo '<td> paid - deposited = '.$total_money.'</td>';
  echo '</tr>';
  echo '</table>';
}

//show tables
//show_generated_total_weight_table();
//possible_combinations();
?>