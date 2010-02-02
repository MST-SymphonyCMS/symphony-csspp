<?php

require(dirname(__FILE__) . '/csspp/csspp.php');

Class extension_csspp extends Extension
{
  public $workspace_position = NULL;
  
  public function about()
  {
    return array(
      'name' => 'CSSPP',
      'version' => '0.2',
      'release-date' => '2010-02-21',
      'author' => array(
        'name' => 'Jeremy Boles',
        'email' => 'jeremy@jeremyboles.com',
      )
    );
  }
  
  public function appendPreferences($context)
  {
    $group = new XMLElement('fieldset');
    $group->setAttribute('class', 'settings');
    $group->appendChild(new XMLElement('legend', __('CSSPP')));			

    $label = Widget::Label();
    $input = Widget::Input('settings[csspp][strip_comments]', 'yes', 'checkbox');
    if($this->_Parent->Configuration->get('strip_comments', 'csspp') == 'yes') $input->setAttribute('checked', 'checked');
    $label->setValue($input->generate() . ' ' . __('Strip comments from CSS output'));
    $group->appendChild($label);
    
    $label = Widget::Label();
    $input = Widget::Input('settings[csspp][minify]', 'yes', 'checkbox');
    if($this->_Parent->Configuration->get('minify', 'csspp') == 'yes') $input->setAttribute('checked', 'checked');
    $label->setValue($input->generate() . ' ' . __('Minify the CSS ouput'));
    $group->appendChild($label);

    $group->appendChild(new XMLElement('p', __('By minifying the CSS, you are stripping out all of the non-essential line breaks and whitespace from the processed code.'), array('class' => 'help')));

    $context['wrapper']->appendChild($group);
  }
  
  public function getSubscribedDelegates()
  {
    return array(
      array(
        'callback' => 'replaceCSS',
        'delegate' => 'FrontendOutputPostGenerate',
        'page' => '/frontend/'
      ), array(
        'callback' => 'appendPreferences',
        'delegate' => 'AddCustomPreferenceFieldsets',
        'page' => '/system/preferences/'
      ), array(
        'callback' => 'savePreferences',
        'delegate' => 'Save',
        'page' => '/system/preferences/'
      )
    );
  }
  
  public function replaceCSS($context)
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
        // See if the user wants the CSS to be minified
        if ($this->_Parent->Configuration->get('minify', 'csspp') != 'yes')
        {
          $csspp->setOption('minify', FALSE);
        }
        // See if the user wants the comments stripped out of the CSS
        if ($this->_Parent->Configuration->get('strip_comments', 'csspp') != 'yes')
        {
          $csspp->setOption('comments', TRUE);
        }
        file_put_contents($new_filename, $csspp->process());
      }
      
      // Change all of the link tags
      $new = str_replace("$file.css", "$file-processed.css?" . filemtime($new_filename), $found[0][$i]);
      $context['output'] = str_replace($found[0][$i], $new, $context['output']);
    }
  }
  
  public function savePreferences($context)
  {
    // Delete all of the exsisting processed files
    foreach (glob(DOCROOT . '/workspace/media/css/*-processed.css') as $file)
    {
      unlink($file);
    }
    
    // Save the settings in the various states they may be in
    if (!is_array($context['settings']))
    {
      $context['settings'] = array('csspp' => array(
        'minify' => 'no',
        'strip_comments' => 'no'
      ));
    }
    elseif (!isset($context['settings']['csspp']))
    {
      $context['settings'] = array('csspp' => array(
        'minify' => 'no',
        'strip_comments' => 'no'
      ));
    }
    else
    {
      if (!isset($context['settings']['csspp']['minify']))
      {
        $context['settings']['csspp']['minify'] = 'no';
      }
      if (!isset($context['settings']['csspp']['strip_comments']))
      {
        $context['settings']['csspp']['strip_comments'] = 'no';
      }
    }
  }
}