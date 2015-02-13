<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Plugin_Name
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Your Name or Company Name
 */
?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<form method="post" action="options.php"> 
    <?php 
    settings_fields( 'lex-option-group' );
    do_settings_sections( 'lex-option-group' );
            $args = array (
                'post_type'         => 'page',
                'posts_per_page'    => '-1',
                'order'             => 'desc'
            );
            $pageslist = get_posts( $args );
            $isempty = empty(esc_attr( get_option('lexicon_page_id') )) ? 'selected' : null;
            $is_auto_detect =  esc_attr(get_option('auto-detection'));
            $checked = $is_auto_detect == "1"? ' checked="checked"': '';
    ?>
    <table class="form-table">
      <tr valign="top">
        <th scope="row">Default page url for glossary</th>
        <td>
          <select name="lexicon_page_id" id="page_url_input_list" class="chosen-select">
            <option disabled <?php echo $isempty; ?> ><?php _e('Choose a page in the list below', 'lex'); ?></option>
            <option value=""><?php _e('None', 'lex'); ?></option>
            <?php
            foreach ( $pageslist as $page ) : setup_postdata( $page );
              $selected = $page->ID == esc_attr( get_option('lexicon_page_id') ) ? 'selected' : null;
              echo '<option value="'.$page->ID.'" '.$selected.' >' . $page->post_title . '</option>';
            endforeach;
            wp_reset_postdata();
            ?>
            Contact Form
          </select>
        </td>

      </tr>
      <tr>
        <br/>
        <input id="auto-detection" type="checkbox" name="auto-detection" value="<?php echo $is_auto_detect;?>" <?php echo $checked;?>/> Auto detection
      </tr>


      <!--<tr valign="top">
      <th scope="row">Some Other Option</th>
      <td><input type="text" name="some_other_option" value="<?php echo esc_attr( get_option('some_other_option') ); ?>" /></td>
      </tr>

      <tr valign="top">
      <th scope="row">Options, Etc.</th>
      <td><input type="text" name="option_etc" value="<?php echo esc_attr( get_option('option_etc') ); ?>" /></td>
      </tr>-->
    </table>
    <?php
      submit_button();
    ?>
  </form>
</div>
