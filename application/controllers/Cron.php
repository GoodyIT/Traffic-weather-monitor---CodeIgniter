<?php if (! defined('BASEPATH')) {
 exit('No direct script access allowed');
}
class Cron extends MY_Controller
{

 public function __construct()
 {
   parent::__construct();
   if (!$this->input->is_cli_request()) {
   show_error('Direct access is not allowed');
   }
 }

 public function run()
 {
    $this->load->library('CronRunner');
    $cron = new CronRunner();
    $cron->run();
 }
}