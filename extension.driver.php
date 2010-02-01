<?php

require(dirname(__FILE__) . '/csspp/csspp.php');

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
    // <link type="text/css" rel="stylesheet" media="screen" href="/media/css/baseline.reset.css"/>
    preg_match_all('#<link\s[^>]*href=\"\/workspace\/media\/css\/([^\"]*).css\"[^>]*>#', $context['output'], $found);
    foreach ($found[1] as $i => $file)
    {
      $csspp = new CSSPP("$file.css", DOCROOT . '/workspace/media/css/');
      file_put_contents(DOCROOT . "/workspace/media/css/$file-processed.css" , $csspp->process());
      $new = str_replace($file, "$file-processed", $found[0][$i]);
      $context['output'] = str_replace($found[0][$i], $new, $context['output']);
    }
  }
}