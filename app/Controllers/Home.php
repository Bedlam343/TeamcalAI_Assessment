<?php

namespace App\Controllers;

class Home extends BaseController
{
  public function index(): string {
    return view('templates/header')
        . view('pages/home');
  }

  public function extract_availability() {
    $url = $this->request->getPost('calendly_url');
    
    $escaped_url = escapeshellarg($url);

    // call the node.js script to scrape calendly page
    $script_path = realpath(FCPATH . '../scrapers/scrape-calendly.js');
    $command = "node " . escapeshellarg($script_path) ." {$escaped_url} 2>&1";
    $avail_data = shell_exec($command);

    $parsed_data = json_decode($avail_data, true);
    $data["avail_data"] = $parsed_data;

    return view('templates/header')
    . view('pages/home', $data);
  }
}
