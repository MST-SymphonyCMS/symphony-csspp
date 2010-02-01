<?php

Class extension_csspp extends Extension
{
  public $workspace_position = NULL;
  
  public function about()
  {
    return array(
      'name' => 'CSSPP',
      'version' => '0.1',
      'release-date' => '2010-02-21',
      'author' => array(
        'name' => 'Jeremy Boles',
        'email' => 'jeremy@jeremyboles.com',
      )
    );
  }
  
  public function getSubscribedDelegates()
  {
    return array(
      array(
        'callback' => 'replace_css',
        'delegate' => 'FrontendOutputPostGenerate',
        'page' => '/frontend/'
      )
    );
  }
  
  public function replace_css(&$context)
  {
    $context['output'] = preg_replace_callback('/^media\/css\/(.*\.css)$/', array(&$this, 'replace_css'), $context['output']);
  }
}