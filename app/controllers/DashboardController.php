<?php 
namespace app\controllers;

use core\template\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return $this->view('admin/dashboard');
    }
}