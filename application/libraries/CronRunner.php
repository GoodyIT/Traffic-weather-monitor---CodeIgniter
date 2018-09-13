<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CronRunner
{
 private $CI;

 public function __construct()
 {
    $this->CI =& get_instance();
 }

 private function calculateNextRun($obj)
 {
    return (time() + $obj->interval_sec);
 }

 public function run()
 {
   $query = $this->CI->db->where('is_active', 1)->where('now() >= next_run_at OR next_run_at IS NULL', '', false)->from('cron')->get();
   if ($query->num_rows() > 0) {
     $env = getenv('CI_ENV');
       foreach ($query->result() as $row) {
        //  $cmd = "export CI_ENV={$env} && {$row->command}";
         $this->CI->db->set('next_run_at', 'FROM_UNIXTIME('.$this->calculateNextRun($row).')', false)->where('id', $row->id)->update('cron');
        //  $output = shell_exec($cmd);
        // fetch weather data and update db
        $weather = json_decode(file_get_contents('https://api.darksky.net/forecast/b3fa3ecdf4e7167c9fee494ee87b2de0/1.452768,103.769179'), true);
        $temperature = ($weather['currently']['temperature'] - 32) * 5 / 9;
        $sql = 'insert into weather (summary, temperature) values("' . $weather['currently']['summary'] . '", "' . $temperature . '")';
        $query = $this->db->query(sql);
 
        $this->CI->db->set('last_run_at', 'now()', false)->where('id', $row->id)->update('cron');
       }
    } 
  }
}