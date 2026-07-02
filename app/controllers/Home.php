<?php

class Home extends Controller
{
  public function index()
  {
    $role = $_SESSION['user']['role'] ?? null;
    if (in_array($role, ['admin', 'reviewer'])) {
      $this->redirect('admin/home');
    }

    $this->view('home');
  }
}
