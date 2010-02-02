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
    // Find all of the link tags
    preg_match_all('#<link\s[^>]*href=\"\/workspace\/media\/css\/([^\"]*).css\"[^>]*>#', $context['output'], $found);
    foreach ($found[1] as $i => $file)
    {
      // Construct the filenames for the css files
      $original_filename = DOCROOT . "/workspace/media/css/$file.css";
      $new_filename = DOCROOT . "/workspace/media/css/$file-processed.css";
      
      // Find when the files where last edited
      $original_time = filemtime($original_filename);
      $new_time = file_exists($new_filename) ? filemtime($new_filename) : 0;
      // If the unprocessed CSS has been edited since our processed one, reprocessed it
      if ($original_time > $new_time)
      {
        // Remove the old file
        if ($new_time) { unlink($new_filename); }
        // Process the CSSP file
        $csspp = new CSSPP(basename($original_filename), dirname($original_filename) . '/');
        file_put_contents($new_filename, $csspp->process());
      }
      
      // Change all of the link tags
      $new = str_replace("$file.css", "$file-processed.css?" . filemtime($new_filename), $found[0][$i]);
      $context['output'] = str_replace($found[0][$i], $new, $context['output']);
    }
  }
}