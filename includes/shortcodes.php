<?php
add_shortcode( 'lexicon', 'register_shortcode');

function register_shortcode(){

  $return = null;

  $path = Lex::lex_check_path('list');

  ob_start();
  include( $path );
  $return .= ob_get_contents();
  ob_end_clean();

  return $return;
}
